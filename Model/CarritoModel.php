<?php
include_once __DIR__ . '/ConfigModel.php';

// llama un procedimiento y devuelve todas las filas (o [] si falla)
// $admin = true usa la conexion del panel admin
function LlamarLista($sql, $tipos = '', $parametros = [], $accion = '', $admin = false)
{
    try {
        $context = $admin ? ConectarAdmin() : Conectar();

        $sentencia = $context -> prepare($sql);
        if ($tipos != '') {
            $sentencia -> bind_param($tipos, ...$parametros);
        }
        $sentencia -> execute();
        $response = $sentencia -> get_result();

        $data = [];
        if ($response) {
            while ($row = $response -> fetch_assoc()) {
                $data[] = $row;
            }
        }

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, $accion);
        return [];
    }
}

// llama un procedimiento que cambia datos, devuelve la primera fila, true o false si falla
function LlamarAccion($sql, $tipos, $parametros, $accion, $admin = false)
{
    try {
        $context = $admin ? ConectarAdmin() : Conectar();

        $sentencia = $context -> prepare($sql);
        $sentencia -> bind_param($tipos, ...$parametros);
        $sentencia -> execute();
        $response = $sentencia -> get_result();
        $fila = $response ? $response -> fetch_assoc() : null;

        Cerrar($context);
        return $fila ?: true;
    }
    catch (Exception $exc) {
        RegistrarError($exc, $accion);
        return false;
    }
}

function ConsultarCarritoModel($idUsuario)
{
    return LlamarLista("CALL sp_carrito_listar(?)", "i", [$idUsuario], "ConsultarCarritoModel");
}

function ContarCarritoModel($idUsuario)
{
    $filas = LlamarLista("CALL sp_carrito_contar(?)", "i", [$idUsuario], "ContarCarritoModel");
    return (int) ($filas[0]['articulos'] ?? 0);
}

// la base revisa que el producto este activo y que haya stock (trigger)
function AgregarCarritoModel($idUsuario, $idProducto, $cantidad)
{
    return LlamarAccion("CALL sp_carrito_agregar(?, ?, ?)", "iii",
        [$idUsuario, $idProducto, $cantidad], "AgregarCarritoModel");
}

function ActualizarCantidadCarritoModel($idUsuario, $idDetalle, $cantidad)
{
    return LlamarAccion("CALL sp_carrito_actualizar_cantidad(?, ?, ?)", "iii",
        [$idUsuario, $idDetalle, $cantidad], "ActualizarCantidadCarritoModel");
}

function QuitarCarritoModel($idUsuario, $idDetalle)
{
    return LlamarAccion("CALL sp_carrito_eliminar_item(?, ?)", "ii",
        [$idUsuario, $idDetalle], "QuitarCarritoModel");
}

// antes de pagar: ajusta las cantidades al stock y devuelve lo que cambio
function RevalidarCarritoModel($idUsuario)
{
    return LlamarLista("CALL sp_carrito_revalidar(?)", "i", [$idUsuario], "RevalidarCarritoModel");
}
