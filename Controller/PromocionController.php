<?php
include_once __DIR__ . '/../Model/PromocionModel.php';
include_once __DIR__ . '/../Model/ProductoModel.php';
include_once __DIR__ . '/SeguridadController.php';

// todo este controlador es del panel admin
ValidarRol('Administrador');

// tipo de la pagina: ?tipo=2x1 o ?tipo=descuento (en la base se llama Porcentaje)
function TipoPromocionPagina()
{
    return ($_GET['tipo'] ?? '') == '2x1' ? '2x1' : 'Porcentaje';
}

// crear o editar promocion (si viene idPromocion es editar)
if (isset($_POST['btnGuardarPromocion'])) {
    $idPromocion = (int) ($_POST['idPromocion'] ?? 0);
    $tipo = TipoPromocionPagina();
    $nombre = trim($_POST['txtNombre'] ?? '');
    $descuento = $tipo == '2x1' ? 0 : (float) ($_POST['txtDescuento'] ?? 0);
    $fechaInicio = $_POST['txtFechaInicio'] ?? '';
    $fechaFin = $_POST['txtFechaFin'] ?? '';

    $error = '';
    if ($nombre == '') {
        $error = "El nombre de la promoción es obligatorio";
    }
    else if ($tipo == 'Porcentaje' && ($descuento < 1 || $descuento > 99)) {
        $error = "El descuento debe estar entre 1 y 99 %";
    }
    else if (!strtotime($fechaInicio) || !strtotime($fechaFin)) {
        $error = "Las fechas son obligatorias";
    }
    else if ($fechaFin < $fechaInicio) {
        $error = "La fecha final no puede ser antes de la inicial";
    }

    if ($error != '') {
        $_POST["mensaje"] = $error;
        $_POST["tipoMensaje"] = "danger";
    }
    else {
        $response = $idPromocion > 0
            ? ActualizarPromocionModel($idPromocion, $nombre, $tipo, $descuento, $fechaInicio, $fechaFin)
            : RegistrarPromocionModel($nombre, $tipo, $descuento, $fechaInicio, $fechaFin);

        if ($response) {
            $_POST = [];
            $_POST["mensaje"] = $idPromocion > 0
                ? "La promoción se ha actualizado correctamente"
                : "La promoción se ha registrado correctamente";
            $_POST["tipoMensaje"] = "success";
        }
        else {
            $_POST["mensaje"] = $_POST["errorBD"] ?? "La promoción no se ha podido guardar";
            $_POST["tipoMensaje"] = "danger";
        }
    }
}

// activar o desactivar
if (isset($_POST['btnCambiarEstadoPromocion'])) {
    $idPromocion = (int) $_POST['idPromocion'];
    $activo = (int) $_POST['activo'] == 1 ? 1 : 0;

    if (CambiarEstadoPromocionModel($idPromocion, $activo)) {
        $_POST = [];
        $_POST["mensaje"] = $activo == 1 ? "La promoción se ha activado" : "La promoción se ha desactivado";
        $_POST["tipoMensaje"] = "success";
    }
    else {
        $_POST["mensaje"] = "No se ha podido cambiar el estado de la promoción";
        $_POST["tipoMensaje"] = "danger";
    }
}

// agregar un producto a la promocion
if (isset($_POST['btnAgregarProducto'])) {
    $idPromocion = (int) $_POST['idPromocion'];
    $idProducto = (int) ($_POST['ddlProducto'] ?? 0);

    if ($idProducto <= 0) {
        $_POST["mensaje"] = "Seleccione un producto";
        $_POST["tipoMensaje"] = "danger";
    }
    else if (AsignarProductoPromocionModel($idPromocion, $idProducto)) {
        $_POST = [];
        $_POST["mensaje"] = "El producto se agregó a la promoción";
        $_POST["tipoMensaje"] = "success";
    }
    else {
        $_POST["mensaje"] = "No se ha podido agregar el producto";
        $_POST["tipoMensaje"] = "danger";
    }
}

// quitar un producto de la promocion
if (isset($_POST['btnQuitarProducto'])) {
    $idPromocion = (int) $_POST['idPromocion'];
    $idProducto = (int) $_POST['idProducto'];

    if (QuitarProductoPromocionModel($idPromocion, $idProducto)) {
        $_POST = [];
        $_POST["mensaje"] = "El producto se quitó de la promoción";
        $_POST["tipoMensaje"] = "success";
    }
    else {
        $_POST["mensaje"] = "No se ha podido quitar el producto";
        $_POST["tipoMensaje"] = "danger";
    }
}

// promociones del tipo de la pagina (2x1 o Porcentaje)
function ConsultarPromocionesAdmin($tipo)
{
    return array_values(array_filter(ConsultarPromocionesAdminModel(), function ($p) use ($tipo) {
        return $p['tipo'] == $tipo;
    }));
}

function ConsultarProductosPromocion($idPromocion)
{
    return ConsultarProductosPromocionModel($idPromocion);
}

// productos para el select de agregar
function ConsultarProductosParaPromocion()
{
    return ConsultarProductosAdminModel();
}
