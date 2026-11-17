<?php
include_once __DIR__ . '/ConfigModel.php';

// categorias activas para el menu y el catalogo del cliente
function ConsultarCategoriasActivasModel()
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_categoria_listar_activas()");
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
        RegistrarError($exc, "ConsultarCategoriasActivasModel");
        return [];
    }
}

// todas las categorias con la cantidad de productos, para el admin
function ConsultarCategoriasAdminModel()
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_categoria_listar()");
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
        RegistrarError($exc, "ConsultarCategoriasAdminModel");
        return [];
    }
}

// crea una categoria, la base no deja nombres repetidos
function RegistrarCategoriaModel($nombre, $descripcion)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_categoria_insertar(?, ?)");
        $sentencia -> bind_param("ss", $nombre, $descripcion);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "RegistrarCategoriaModel");
        return false;
    }
}

// cambia nombre y descripcion de una categoria
function ActualizarCategoriaModel($idCategoria, $nombre, $descripcion)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_categoria_actualizar(?, ?, ?)");
        $sentencia -> bind_param("iss", $idCategoria, $nombre, $descripcion);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ActualizarCategoriaModel");
        return false;
    }
}

// activa (1) o desactiva (0) una categoria
function CambiarEstadoCategoriaModel($idCategoria, $activo)
{
    try {
        $context = ConectarAdmin();

        $sentencia = $context -> prepare("CALL sp_categoria_cambiar_estado(?, ?)");
        $sentencia -> bind_param("ii", $idCategoria, $activo);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "CambiarEstadoCategoriaModel");
        return false;
    }
}
