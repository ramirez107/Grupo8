<?php
include_once __DIR__ . '/../Model/ConfigModel.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// devuelve "../" o "../../" segun que tan adentro de View este la pagina
// asi los enlaces funcionan desde View/Inicio y desde View/Admin/Productos
function RutaView()
{
    $carpetaView = realpath(__DIR__ . '/../View');
    $carpetaPagina = realpath(dirname($_SERVER['SCRIPT_FILENAME']));

    $relativa = substr($carpetaPagina, strlen($carpetaView));
    $niveles = substr_count(str_replace('\\', '/', $relativa), '/');

    return str_repeat('../', max($niveles, 1));
}

// ruta de un css o js de Assets con ?v=fecha del archivo
// asi el navegador descarga la version nueva cada vez que se modifica (no usa la vieja de cache)
function Asset($archivo)
{
    $ruta = __DIR__ . '/../View/Assets/' . $archivo;
    $version = is_file($ruta) ? filemtime($ruta) : 0;

    return RutaView() . 'Assets/' . $archivo . '?v=' . $version;
}

// ruta de la imagen del producto, si el archivo no existe usa la imagen por defecto
function RutaImagen($imagen)
{
    if ($imagen != '' && is_file(__DIR__ . '/../View/Assets/images/' . $imagen)) {
        return RutaView() . 'Assets/images/' . $imagen;
    }

    return RutaView() . 'Assets/images/product-thumb-1.png';
}

// monto en colones, ejemplo 1250 -> ₡1 250,00
function FormatoMoneda($monto)
{
    return '₡' . number_format((float) $monto, 2, ',', ' ');
}

// escapa el texto antes de imprimirlo en la pagina
function Texto($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// envia un correo con phpmailer, igual que en clase
// si no hay contrasenna configurada se simula el envio
function EnviarCorreoElectronico($asunto, $contenido, $destinatario)
{
    try {
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';

        $correoSalida = "@gmail.com";
        $contrasennaSalida = "";

        if ($contrasennaSalida == "") {
            return false; // no se envio, la pagina muestra el enlace en pantalla
        }

        $mail = new PHPMailer();
        $mail -> CharSet = 'UTF-8';

        $mail -> IsSMTP();
        $mail -> IsHTML(true);
        $mail -> Host = 'smtp.gmail.com';
        $mail -> SMTPSecure = 'tls';
        $mail -> Port = 587;
        $mail -> SMTPAuth = true;
        $mail -> Username = $correoSalida;
        $mail -> Password = $contrasennaSalida;

        $mail -> SetFrom($correoSalida);
        $mail -> Subject = $asunto;
        $mail -> MsgHTML($contenido);
        $mail -> AddAddress($destinatario);

        return $mail -> send();
    }
    catch (Exception $exc) {
        RegistrarError($exc, "EnviarCorreoElectronico");
        return false;
    }
}
