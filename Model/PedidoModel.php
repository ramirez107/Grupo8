<?php
include_once __DIR__ . '/CarritoModel.php';

// ---------- cliente ----------

function ConsultarUsuarioModel($idUsuario)
{
    $filas = LlamarLista("CALL sp_usuario_obtener(?)", "i", [$idUsuario], "ConsultarUsuarioModel");
    return $filas[0] ?? null;
}

function ConsultarDireccionesModel($idUsuario)
{
    return LlamarLista("CALL sp_direccion_listar(?)", "i", [$idUsuario], "ConsultarDireccionesModel");
}

function RegistrarDireccionModel($idUsuario, $provincia, $canton, $distrito, $detalle)
{
    $fila = LlamarAccion("CALL sp_direccion_insertar(?, ?, ?, ?, ?)", "issss",
        [$idUsuario, $provincia, $canton, $distrito, $detalle], "RegistrarDireccionModel");
    return is_array($fila) ? (int) $fila['id_direccion'] : 0;
}

// convierte el carrito en pedido; si es tarjeta solo llegan los ultimos 4 y el titular
function CrearPedidoModel($idUsuario, $idDireccion, $metodo, $ultimos4, $titular, $comprobante, $fechaEntrega, $franja)
{
    return LlamarAccion("CALL sp_pedido_crear(?, ?, ?, ?, ?, ?, ?, ?)", "iissssss",
        [$idUsuario, $idDireccion, $metodo, $ultimos4, $titular, $comprobante, $fechaEntrega, $franja],
        "CrearPedidoModel");
}

function ConsultarPedidosClienteModel($idUsuario)
{
    return LlamarLista("CALL sp_pedido_listar_cliente(?)", "i", [$idUsuario], "ConsultarPedidosClienteModel");
}

// el procedimiento solo devuelve lineas si el pedido es del usuario (o si es admin)
function ConsultarDetallePedidoModel($idUsuario, $idPedido, $admin = false)
{
    return LlamarLista("CALL sp_pedido_detalle_listar(?, ?)", "ii", [$idUsuario, $idPedido],
        "ConsultarDetallePedidoModel", $admin);
}

// ---------- admin ----------

function ConsultarPedidosAdminModel($estado, $desde, $hasta)
{
    return LlamarLista("CALL sp_pedido_listar_admin(?, ?, ?)", "sss", [$estado, $desde, $hasta],
        "ConsultarPedidosAdminModel", true);
}

function ConsultarPedidoAdminModel($idPedido)
{
    $filas = LlamarLista("CALL sp_pedido_obtener_admin(?)", "i", [$idPedido], "ConsultarPedidoAdminModel", true);
    return $filas[0] ?? null;
}

// la base revisa que el cambio de estado sea valido y si se cancela devuelve el stock
function CambiarEstadoPedidoModel($idAdmin, $idPedido, $estado)
{
    return LlamarAccion("CALL sp_pedido_cambiar_estado(?, ?, ?)", "iis", [$idAdmin, $idPedido, $estado],
        "CambiarEstadoPedidoModel", true);
}

function ConsultarResumenVentasModel($desde, $hasta)
{
    $filas = LlamarLista("CALL sp_reporte_resumen_rango(?, ?)", "ss", [$desde, $hasta], "ConsultarResumenVentasModel", true);
    return $filas[0] ?? ['pedidos' => 0, 'total_ventas' => 0, 'ticket_promedio' => 0, 'en_espera' => 0, 'cancelados' => 0];
}

function ConsultarVentasPorDiaModel($desde, $hasta)
{
    return LlamarLista("CALL sp_reporte_ventas_rango(?, ?)", "ss", [$desde, $hasta], "ConsultarVentasPorDiaModel", true);
}

function ConsultarVentasPorMetodoModel($desde, $hasta)
{
    return LlamarLista("CALL sp_reporte_ventas_metodo(?, ?)", "ss", [$desde, $hasta], "ConsultarVentasPorMetodoModel", true);
}

function ConsultarMasVendidosModel($desde, $hasta, $limite)
{
    return LlamarLista("CALL sp_reporte_mas_vendidos_rango(?, ?, ?)", "ssi", [$desde, $hasta, $limite],
        "ConsultarMasVendidosModel", true);
}
