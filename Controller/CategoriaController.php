<?php
include_once __DIR__ . '/../Model/CategoriaModel.php';
include_once __DIR__ . '/SeguridadController.php';

// todo este controlador es del panel admin
ValidarRol('Administrador');

// crear o editar categoria (si viene idCategoria es editar)
if (isset($_POST['btnGuardarCategoria'])) {
    $idCategoria = (int) ($_POST['idCategoria'] ?? 0);
    $nombre = trim($_POST['txtNombre'] ?? '');
    $descripcion = trim($_POST['txtDescripcion'] ?? '');

    if ($nombre == '') {
        $_POST["mensaje"] = "El nombre de la categoría es obligatorio";
        $_POST["tipoMensaje"] = "danger";
    }
    else {
        $response = $idCategoria > 0
            ? ActualizarCategoriaModel($idCategoria, $nombre, $descripcion)
            : RegistrarCategoriaModel($nombre, $descripcion);

        if ($response) {
            $_POST = [];
            $_POST["mensaje"] = $idCategoria > 0
                ? "La categoría se ha actualizado correctamente"
                : "La categoría se ha registrado correctamente";
            $_POST["tipoMensaje"] = "success";
        }
        else {
            // la base no deja nombres repetidos
            $_POST["mensaje"] = $_POST["errorBD"] ?? "La categoría no se ha podido guardar";
            $_POST["tipoMensaje"] = "danger";
        }
    }
}

// activar o desactivar
if (isset($_POST['btnCambiarEstadoCategoria'])) {
    $idCategoria = (int) $_POST['idCategoria'];
    $activo = (int) $_POST['activo'] == 1 ? 1 : 0;

    if (CambiarEstadoCategoriaModel($idCategoria, $activo)) {
        $_POST = [];
        $_POST["mensaje"] = $activo == 1 ? "La categoría se ha activado" : "La categoría se ha desactivado";
        $_POST["tipoMensaje"] = "success";
    }
    else {
        $_POST["mensaje"] = "No se ha podido cambiar el estado de la categoría";
        $_POST["tipoMensaje"] = "danger";
    }
}

// lista para la tabla
function ConsultarCategoriasAdmin()
{
    return ConsultarCategoriasAdminModel();
}
