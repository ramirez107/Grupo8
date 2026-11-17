<?php
include_once __DIR__ . '/ConfigModel.php';

// recorre el resultado y devuelve la lista completa
function LeerLista($sentencia)
{
    $response = $sentencia -> get_result();

    $data = [];
    while ($row = $response -> fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

// productos activos de una categoria
function ConsultarProductosCategoriaModel($idCategoria)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_producto_por_categoria(?)");
        $sentencia -> bind_param("i", $idCategoria);
        $sentencia -> execute();
        $data = LeerLista($sentencia);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarProductosCategoriaModel");
        return [];
    }
}

// buscador: texto, categoria (0 = todas) y orden por precio (ASC, DESC o vacio)
function BuscarProductosModel($texto, $idCategoria, $orden)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_producto_buscar(?, ?, ?)");
        $sentencia -> bind_param("sis", $texto, $idCategoria, $orden);
        $sentencia -> execute();
        $data = LeerLista($sentencia);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "BuscarProductosModel");
        return [];
    }
}

// los productos mas vendidos para la pagina de inicio
function ConsultarProductosDestacadosModel($limite)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_producto_destacados(?)");
        $sentencia -> bind_param("i", $limite);
        $sentencia -> execute();
        $data = LeerLista($sentencia);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarProductosDestacadosModel");
        return [];
    }
}

// promociones vigentes para los banners
function ConsultarPromocionesVigentesModel()
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_promocion_vigentes()");
        $sentencia -> execute();
        $data = LeerLista($sentencia);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarPromocionesVigentesModel");
        return [];
    }
}

// todos los productos con stock y estado, para el admin
function ConsultarProductosAdminModel()
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_producto_listar_admin()");
        $sentencia -> execute();
        $data = LeerLista($sentencia);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarProductosAdminModel");
        return [];
    }
}

// un producto con su inventario, para editar
function ConsultarProductoModel($idProducto)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_producto_obtener(?)");
        $sentencia -> bind_param("i", $idProducto);
        $sentencia -> execute();
        $row = $sentencia -> get_result() -> fetch_assoc();

        Cerrar($context);
        return $row;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarProductoModel");
        return null;
    }
}

// crea el producto y su inventario, la base revisa que el usuario sea admin
function RegistrarProductoModel($idAdmin, $idCategoria, $nombre, $descripcion, $precio, $imagen, $stock, $stockMinimo)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_producto_insertar(?, ?, ?, ?, ?, ?, ?, ?)");
        $sentencia -> bind_param("iissdsii", $idAdmin, $idCategoria, $nombre, $descripcion, $precio, $imagen, $stock, $stockMinimo);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "RegistrarProductoModel");
        return false;
    }
}

// actualiza los datos del producto, si la imagen viene null deja la que tenia
function ActualizarProductoModel($idProducto, $idCategoria, $nombre, $descripcion, $precio, $imagen)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_producto_actualizar(?, ?, ?, ?, ?, ?)");
        $sentencia -> bind_param("iissds", $idProducto, $idCategoria, $nombre, $descripcion, $precio, $imagen);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ActualizarProductoModel");
        return false;
    }
}

// cambia el stock, solo lo puede hacer un admin (queda en la bitacora)
function ActualizarInventarioModel($idAdmin, $idProducto, $stock, $stockMinimo)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_inventario_actualizar(?, ?, ?, ?)");
        $sentencia -> bind_param("iiii", $idAdmin, $idProducto, $stock, $stockMinimo);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ActualizarInventarioModel");
        return false;
    }
}

// activa (1) o desactiva (0) un producto
function CambiarEstadoProductoModel($idProducto, $activo)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_producto_cambiar_estado(?, ?)");
        $sentencia -> bind_param("ii", $idProducto, $activo);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "CambiarEstadoProductoModel");
        return false;
    }
}
