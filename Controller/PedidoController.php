<?php
include_once __DIR__ . '/../Model/PedidoModel.php';
include_once __DIR__ . '/SeguridadController.php';

// numero de SINPE Movil de la tienda que se muestra al pagar (cambiarlo por el real)
const SINPE_TIENDA = '8888-0000';
const SINPE_NOMBRE = 'Supermercado Los Jaulares';

// carpeta privada de los comprobantes (tiene .htaccess que no deja abrirlos por URL)
function CarpetaComprobantes()
{
    return __DIR__ . '/../Uploads/comprobantes/';
}

// estados del pedido en orden, para la barra de progreso del cliente
function PasosPedido()
{
    return ['Recibido', 'En preparación', 'En camino', 'Entregado'];
}

// numero de tarjeta valido con el algoritmo de Luhn (el que usan todas las tarjetas)
function TarjetaValida($numero)
{
    if (!preg_match('/^[0-9]{13,19}$/', $numero)) {
        return false;
    }

    $suma = 0;
    $doble = false;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $digito = (int) $numero[$i];
        if ($doble) {
            $digito *= 2;
            if ($digito > 9) {
                $digito -= 9;
            }
        }
        $suma += $digito;
        $doble = !$doble;
    }

    return $suma % 10 == 0;
}

// MM/AA que todavia no haya vencido
function VencimientoValido($vence)
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $vence, $partes)) {
        return false;
    }
    $ultimoDia = strtotime('20' . $partes[2] . '-' . $partes[1] . '-01 +1 month');
    return $ultimoDia > time();
}

// guarda el comprobante de SINPE (jpg, png o pdf hasta 3 MB) con un nombre al azar
function GuardarComprobante()
{
    if (!isset($_FILES['fileComprobante']) || $_FILES['fileComprobante']['error'] != UPLOAD_ERR_OK) {
        return false;
    }

    $archivo = $_FILES['fileComprobante'];
    if ($archivo['size'] > 3 * 1024 * 1024) {
        return false;
    }

    // se revisa el contenido real del archivo, no solo la extension
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
    $tipo = (new finfo(FILEINFO_MIME_TYPE)) -> file($archivo['tmp_name']);
    if (!isset($tipos[$tipo])) {
        return false;
    }

    if (!is_dir(CarpetaComprobantes())) {
        mkdir(CarpetaComprobantes(), 0755, true);
    }

    $nombre = 'sinpe_' . (int) $_SESSION['idUsuario'] . '_' . bin2hex(random_bytes(12)) . '.' . $tipos[$tipo];
    if (!move_uploaded_file($archivo['tmp_name'], CarpetaComprobantes() . $nombre)) {
        return false;
    }

    return $nombre;
}

// finalizar compra
if (isset($_POST['btnFinalizarCompra'])) {
    ValidarSesion();
    $idUsuario = (int) $_SESSION['idUsuario'];

    $metodo = $_POST['metodoPago'] ?? '';
    $idDireccion = (int) ($_POST['idDireccion'] ?? 0);
    $fechaEntrega = $_POST['txtFechaEntrega'] ?? '';
    $franja = $_POST['ddlFranja'] ?? '';
    $ultimos4 = null;
    $titular = null;
    $comprobante = null;
    $error = '';

    // 1. si algo del carrito ya no tiene stock, se ajusta y se le avisa antes de cobrar
    $ajustes = RevalidarCarritoModel($idUsuario);
    if (count($ajustes) > 0) {
        $nombres = array_map(function ($a) {
            return $a['producto'] . ($a['cantidad_nueva'] == 0 ? ' (agotado)' : ' (quedan ' . $a['cantidad_nueva'] . ')');
        }, $ajustes);
        $error = 'Actualizamos tu carrito porque cambió el stock: ' . implode(', ', $nombres) . '. Revisá el total y volvé a confirmar.';
    }

    // 2. entrega
    if ($error == '' && $idDireccion == 0) {
        $provincia = trim($_POST['ddlProvincia'] ?? '');
        $canton = trim($_POST['txtCanton'] ?? '');
        $distrito = trim($_POST['txtDistrito'] ?? '');
        $detalle = trim($_POST['txtDetalle'] ?? '');

        if ($provincia == '' || $canton == '' || $distrito == '' || $detalle == '') {
            $error = 'Complete la dirección de entrega';
        }
    }
    if ($error == '' && (!strtotime($fechaEntrega) || $fechaEntrega < date('Y-m-d'))) {
        $error = 'Seleccione una fecha de entrega válida';
    }
    if ($error == '' && !in_array($franja, ['08:00-12:00', '12:00-16:00', '16:00-20:00'], true)) {
        $error = 'Seleccione la hora de entrega';
    }

    // 3. pago: de la tarjeta solo se guardan los ultimos 4 y el titular, nunca el numero ni el cvv
    if ($error == '') {
        if ($metodo == 'Tarjeta') {
            $numero = preg_replace('/\D/', '', $_POST['txtNumeroTarjeta'] ?? '');
            $titular = trim($_POST['txtTitular'] ?? '');

            if ($titular == '') {
                $error = 'Escriba el nombre del dueño de la tarjeta';
            }
            else if (!TarjetaValida($numero)) {
                $error = 'El número de tarjeta no es válido';
            }
            else if (!VencimientoValido(trim($_POST['txtVence'] ?? ''))) {
                $error = 'La fecha de vencimiento no es válida o la tarjeta está vencida';
            }
            else if (!preg_match('/^[0-9]{3,4}$/', $_POST['txtCvv'] ?? '')) {
                $error = 'El código de seguridad (CVV) no es válido';
            }
            else {
                $ultimos4 = substr($numero, -4);
            }
        }
        else if ($metodo == 'SINPE') {
            $comprobante = GuardarComprobante();
            if ($comprobante === false) {
                $comprobante = null;
                $error = 'Adjunte el comprobante del SINPE (JPG, PNG o PDF de máximo 3 MB)';
            }
        }
        else if ($metodo != 'Efectivo') {
            $error = 'Seleccione el método de pago';
        }
    }

    // 4. direccion nueva (queda guardada para la proxima compra)
    if ($error == '' && $idDireccion == 0) {
        $idDireccion = RegistrarDireccionModel($idUsuario, $provincia, $canton, $distrito, $detalle);
        if ($idDireccion == 0) {
            $error = $_POST['errorBD'] ?? 'No se pudo guardar la dirección';
        }
    }

    // 5. pedido: la base baja el stock, agenda la entrega y vacia el carrito en una sola transaccion
    if ($error == '') {
        $pedido = CrearPedidoModel($idUsuario, $idDireccion, $metodo, $ultimos4, $titular, $comprobante, $fechaEntrega, $franja);

        if (is_array($pedido)) {
            $_SESSION['pedidoExito'] = (int) $pedido['id_pedido'];
            header("Location: exito.php?id=" . (int) $pedido['id_pedido']);
            exit();
        }
        $error = $_POST['errorBD'] ?? 'No se pudo completar la compra. Intente de nuevo';
    }

    // si fallo, el comprobante subido ya no sirve
    if ($comprobante && is_file(CarpetaComprobantes() . $comprobante)) {
        unlink(CarpetaComprobantes() . $comprobante);
    }

    $_POST["mensaje"] = $error;
    $_POST["tipoMensaje"] = "danger";
}

function ConsultarDirecciones()
{
    return ConsultarDireccionesModel((int) $_SESSION['idUsuario']);
}

function ConsultarUsuario()
{
    return ConsultarUsuarioModel((int) $_SESSION['idUsuario']);
}

function ConsultarPedidosCliente()
{
    return ConsultarPedidosClienteModel((int) $_SESSION['idUsuario']);
}

function ConsultarDetallePedido($idPedido)
{
    return ConsultarDetallePedidoModel((int) $_SESSION['idUsuario'], (int) $idPedido);
}

// pedido del usuario conectado (null si no es suyo)
function ConsultarPedidoCliente($idPedido)
{
    foreach (ConsultarPedidosCliente() as $pedido) {
        if ($pedido['id_pedido'] == $idPedido) {
            return $pedido;
        }
    }
    return null;
}

// color e icono de cada estado
function EtiquetaEstado($estado)
{
    $estilos = [
        'En espera'      => ['warning', 'bi-hourglass-split'],
        'Recibido'       => ['info', 'bi-receipt'],
        'En preparación' => ['primary', 'bi-basket'],
        'En camino'      => ['primary', 'bi-truck'],
        'Entregado'      => ['success', 'bi-check-circle'],
        'Cancelado'      => ['secondary', 'bi-x-circle'],
    ];
    [$color, $icono] = $estilos[$estado] ?? ['secondary', 'bi-circle'];

    return '<span class="badge text-bg-' . $color . '"><i class="bi ' . $icono . '"></i> ' . Texto($estado) . '</span>';
}
