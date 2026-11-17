<?php
include_once __DIR__ . '/../Model/ProductoModel.php';
include_once __DIR__ . '/../Model/CategoriaModel.php';
include_once __DIR__ . '/SeguridadController.php';

// todo este controlador es del panel admin
ValidarRol('Administrador');

// registrar producto nuevo con su imagen
if (isset($_POST['btnRegistrarProducto'])) {
    $datos = LeerFormularioProducto();
    $error = ValidarFormularioProducto($datos, true);

    if ($error == '') {
        $imagen = GuardarImagenProducto();

        if ($imagen === false) {
            $error = "La imagen debe ser JPG o PNG y pesar menos de 2 MB";
        }
    }

    if ($error != '') {
        $_POST["mensaje"] = $error;
        $_POST["tipoMensaje"] = "danger";
    }
    else {
        $response = RegistrarProductoModel($_SESSION['idUsuario'], $datos['idCategoria'], $datos['nombre'],
            $datos['descripcion'], $datos['precio'], $imagen, $datos['stock'], $datos['stockMinimo']);

        if ($response) {
            $_POST = [];
            $_POST["mensaje"] = "El producto se ha registrado correctamente";
            $_POST["tipoMensaje"] = "success";
        }
        else {
            $_POST["mensaje"] = $_POST["errorBD"] ?? "El producto no se ha podido registrar";
            $_POST["tipoMensaje"] = "danger";
        }
    }
}

// editar producto, la imagen es opcional
if (isset($_POST['btnActualizarProducto'])) {
    $idProducto = (int) $_POST['idProducto'];
    $datos = LeerFormularioProducto();
    $error = ValidarFormularioProducto($datos, false);
    $imagen = null;

    if ($error == '' && isset($_FILES['fileImagen']) && $_FILES['fileImagen']['error'] != UPLOAD_ERR_NO_FILE) {
        $imagen = GuardarImagenProducto();

        if ($imagen === false) {
            $error = "La imagen debe ser JPG o PNG y pesar menos de 2 MB";
        }
    }

    if ($error != '') {
        $_POST["mensaje"] = $error;
        $_POST["tipoMensaje"] = "danger";
    }
    else {
        $response = ActualizarProductoModel($idProducto, $datos['idCategoria'], $datos['nombre'],
            $datos['descripcion'], $datos['precio'], $imagen);

        // el stock va aparte porque solo el admin lo cambia y queda en la bitacora
        if ($response) {
            $response = ActualizarInventarioModel($_SESSION['idUsuario'], $idProducto,
                $datos['stock'], $datos['stockMinimo']);
        }

        if ($response) {
            $_POST["mensaje"] = "El producto se ha actualizado correctamente";
            $_POST["tipoMensaje"] = "success";
        }
        else {
            $_POST["mensaje"] = $_POST["errorBD"] ?? "El producto no se ha podido actualizar";
            $_POST["tipoMensaje"] = "danger";
        }
    }
}

// activar o desactivar desde el listado
if (isset($_POST['btnCambiarEstadoProducto'])) {
    $idProducto = (int) $_POST['idProducto'];
    $activo = (int) $_POST['activo'] == 1 ? 1 : 0;

    if (CambiarEstadoProductoModel($idProducto, $activo)) {
        $_POST["mensaje"] = $activo == 1 ? "El producto se ha activado" : "El producto se ha desactivado";
        $_POST["tipoMensaje"] = "success";
    }
    else {
        $_POST["mensaje"] = "No se ha podido cambiar el estado del producto";
        $_POST["tipoMensaje"] = "danger";
    }
}

// lee los campos del formulario de producto
function LeerFormularioProducto()
{
    return [
        'nombre' => trim($_POST['txtNombre'] ?? ''),
        'descripcion' => trim($_POST['txtDescripcion'] ?? ''),
        'idCategoria' => (int) ($_POST['ddlCategoria'] ?? 0),
        'precio' => (float) ($_POST['txtPrecio'] ?? 0),
        'stock' => (int) ($_POST['txtStock'] ?? -1),
        'stockMinimo' => (int) ($_POST['txtStockMinimo'] ?? -1)
    ];
}

// misma validacion que jquery validate, devuelve el mensaje o vacio
function ValidarFormularioProducto($datos, $imagenObligatoria)
{
    if ($datos['nombre'] == '' || $datos['idCategoria'] <= 0) {
        return "Complete el nombre y la categoría";
    }
    if ($datos['precio'] <= 0) {
        return "El precio debe ser mayor a cero";
    }
    if ($datos['stock'] < 0 || $datos['stockMinimo'] < 0) {
        return "El stock no puede ser negativo";
    }
    if ($imagenObligatoria && (!isset($_FILES['fileImagen']) || $_FILES['fileImagen']['error'] == UPLOAD_ERR_NO_FILE)) {
        return "Seleccione la imagen del producto";
    }
    return '';
}

// guarda la imagen en View/Assets/images/productos y devuelve la ruta para la base
function GuardarImagenProducto()
{
    $archivo = $_FILES['fileImagen'];

    if ($archivo['error'] != UPLOAD_ERR_OK || $archivo['size'] > 2 * 1024 * 1024) {
        return false;
    }

    // se revisa el contenido real del archivo, no solo la extension
    $info = getimagesize($archivo['tmp_name']);
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    if ($info === false || !isset($tipos[$info['mime']])) {
        return false;
    }

    $carpeta = __DIR__ . '/../View/Assets/images/productos/';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }

    $nombreArchivo = 'producto_' . bin2hex(random_bytes(8)) . '.' . $tipos[$info['mime']];

    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombreArchivo)) {
        return false;
    }

    return 'productos/' . $nombreArchivo;
}

// listas para las vistas
function ConsultarProductosAdmin()
{
    return ConsultarProductosAdminModel();
}

function ConsultarProducto($idProducto)
{
    return ConsultarProductoModel($idProducto);
}

function ConsultarCategoriasAdmin()
{
    return ConsultarCategoriasAdminModel();
}
