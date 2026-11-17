<?php
// partes que se repiten en login, registro y recuperar contrasenna
include_once __DIR__ . '/../Controller/ConfigController.php';

function IncludeCSS($titulo)
{
    echo '
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . ' | Los Jaulares</title>

            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Delius&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <link rel="stylesheet" href="../Assets/css/auth.css">
        </head>
    ';
}

function MostrarEncabezado($volver = 'home.php')
{
    echo '
        <header class="encabezado">
            <a href="' . htmlspecialchars($volver, ENT_QUOTES, 'UTF-8') . '" class="btn-volver" aria-label="Volver">
                <i class="bi bi-arrow-left-circle"></i>
            </a>
            <h1><a href="home.php">Los Jaulares</a></h1>
        </header>
    ';
}

// Tarjeta verde con el check que sale en el Figma cuando algo se hace bien
// sale cuando el controlador deja $_POST["exito"] y despues manda a $_POST["destino"]
function MostrarTarjetaExito()
{
    $mensaje = isset($_POST["exito"]) ? $_POST["exito"] : '';
    $destino = isset($_POST["destino"]) ? $_POST["destino"] : '';
    $oculto = $mensaje == '' ? 'hidden' : '';

    echo '
        <div class="modal-exito" id="tarjetaExito" role="dialog" aria-live="polite"
             data-destino="' . htmlspecialchars($destino, ENT_QUOTES, 'UTF-8') . '" ' . $oculto . '>
            <div class="tarjeta">
                <svg viewBox="0 0 120 90" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="8,48 40,80 112,8" />
                </svg>
                <p id="mensajeExito">' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>
            </div>
        </div>
    ';
}

// alerta de bootstrap con el mensaje que deja el controlador (success, warning o danger)
// el mensaje lo arma el controlador y ya viene escapado
function MostrarMensaje()
{
    if (isset($_POST["mensaje"])) {
        echo '
            <div class="alert alert-' . Texto($_POST["tipoMensaje"] ?? 'secondary') . '" role="alert">
                ' . $_POST["mensaje"] . '
            </div>
        ';
    }
}

// jquery y jquery validate como en clase, $archivoJs es la validacion de cada pagina
function IncludeJS($archivoJs = '')
{
    echo '
        <script src="https://cdn.jsdelivr.net/npm/jquery@4.0.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
        <script src="../Assets/js/auth.js"></script>
    ';

    if ($archivoJs != '') {
        echo '<script src="../Assets/js/' . Texto($archivoJs) . '"></script>';
    }
}
