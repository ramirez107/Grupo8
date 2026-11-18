<?php
include_once __DIR__ . '/ConfigModel.php';

// promociones de 2x1 y descuento, todo es del panel admin

// todas las promociones con la cantidad de productos y si estan vigentes
function ConsultarPromocionesAdminModel()
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_promocion_listar()");
        $sentencia -> execute();
        $response = $sentencia -> get_result();

        $data = [];
        while ($row = $response -> fetch_assoc()) {
            $data[] = $row;
        }

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarPromocionesAdminModel");
        return [];
    }
}

// productos que tiene una promocion
function ConsultarProductosPromocionModel($idPromocion)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_promocion_productos(?)");
        $sentencia -> bind_param("i", $idPromocion);
        $sentencia -> execute();
        $response = $sentencia -> get_result();

        $data = [];
        while ($row = $response -> fetch_assoc()) {
            $data[] = $row;
        }

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarProductosPromocionModel");
        return [];
    }
}

// crea la promocion, la base revisa fechas y porcentaje (trigger)
function RegistrarPromocionModel($nombre, $tipo, $descuento, $fechaInicio, $fechaFin)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_promocion_insertar(?, ?, ?, ?, ?)");
        $sentencia -> bind_param("ssdss", $nombre, $tipo, $descuento, $fechaInicio, $fechaFin);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "RegistrarPromocionModel");
        return false;
    }
}

function ActualizarPromocionModel($idPromocion, $nombre, $tipo, $descuento, $fechaInicio, $fechaFin)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_promocion_actualizar(?, ?, ?, ?, ?, ?)");
        $sentencia -> bind_param("issdss", $idPromocion, $nombre, $tipo, $descuento, $fechaInicio, $fechaFin);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ActualizarPromocionModel");
        return false;
    }
}

// activa (1) o desactiva (0) una promocion
function CambiarEstadoPromocionModel($idPromocion, $activo)
{
    try {
        $context = ConectarAdmin();

        $procedimiento = $activo == 1 ? "CALL sp_promocion_activar(?)" : "CALL sp_promocion_desactivar(?)";
        $sentencia = $context -> prepare($procedimiento);
        $sentencia -> bind_param("i", $idPromocion);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "CambiarEstadoPromocionModel");
        return false;
    }
}

function AsignarProductoPromocionModel($idPromocion, $idProducto)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_promocion_asignar_producto(?, ?)");
        $sentencia -> bind_param("ii", $idPromocion, $idProducto);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "AsignarProductoPromocionModel");
        return false;
    }
}

function QuitarProductoPromocionModel($idPromocion, $idProducto)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_promocion_quitar_producto(?, ?)");
        $sentencia -> bind_param("ii", $idPromocion, $idProducto);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "QuitarProductoPromocionModel");
        return false;
    }
}
