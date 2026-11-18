<?php
include_once __DIR__ . '/../Model/CarritoModel.php';
include_once __DIR__ . '/SeguridadController.php';

// el carrito es solo del cliente con sesion (el admin no compra)
function PuedeComprar()
{
    return isset($_SESSION['idUsuario']) && !EsAdministrador();
}

// aviso corto (toast) que se muestra en la siguiente pagina
function GuardarAviso($mensaje, $tipo = 'success')
{
    $_SESSION['avisoTienda'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}

// despues de un POST se vuelve a la misma pagina con GET (asi F5 no repite la accion)
function VolverAPagina($abrirCarrito = false)
{
    if ($abrirCarrito) {
        $_SESSION['abrirCarrito'] = true;
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if (isset($_POST['btnAgregarCarrito']) || isset($_POST['btnCantidadCarrito']) || isset($_POST['btnQuitarCarrito'])) {

    if (!PuedeComprar()) {
        ValidarSesion();
        GuardarAviso('El administrador no puede comprar', 'warning');
        VolverAPagina();
    }

    $idUsuario = (int) $_SESSION['idUsuario'];

    // agregar desde el catalogo con la cantidad elegida
    if (isset($_POST['btnAgregarCarrito'])) {
        $idProducto = (int) ($_POST['idProducto'] ?? 0);
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));

        if (AgregarCarritoModel($idUsuario, $idProducto, $cantidad)) {
            $nombre = trim($_POST['nombreProducto'] ?? 'El producto');
            GuardarAviso($cantidad . ' × ' . $nombre . ' se agregó al carrito');
        }
        else {
            GuardarAviso($_POST['errorBD'] ?? 'No se pudo agregar el producto', 'danger');
        }
        VolverAPagina();
    }

    // + / - dentro del carrito; si llega a 0 se quita
    if (isset($_POST['btnCantidadCarrito'])) {
        $idDetalle = (int) ($_POST['idDetalle'] ?? 0);
        $cantidad = (int) ($_POST['cantidad'] ?? 0);

        $ok = $cantidad <= 0
            ? QuitarCarritoModel($idUsuario, $idDetalle)
            : ActualizarCantidadCarritoModel($idUsuario, $idDetalle, $cantidad);

        if (!$ok) {
            GuardarAviso($_POST['errorBD'] ?? 'No se pudo cambiar la cantidad', 'danger');
        }
        VolverAPagina(true);
    }

    if (isset($_POST['btnQuitarCarrito'])) {
        QuitarCarritoModel($idUsuario, (int) ($_POST['idDetalle'] ?? 0));
        VolverAPagina(true);
    }
}

// datos para el encabezado
function ConsultarCarrito()
{
    return PuedeComprar() ? ConsultarCarritoModel((int) $_SESSION['idUsuario']) : [];
}
