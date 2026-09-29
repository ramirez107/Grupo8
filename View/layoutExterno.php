<?php
// Partes que se repiten en login, registro y recuperar contrasenna

function IncludeCSS($titulo)
{
    echo '
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $titulo . ' | Los Jaulares</title>

            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Delius&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <link rel="stylesheet" href="Assets/css/auth.css">
        </head>
    ';
}

function MostrarEncabezado($volver = 'index.php')
{
    echo '
        <header class="encabezado">
            <a href="' . $volver . '" class="btn-volver" aria-label="Volver">
                <i class="bi bi-arrow-left-circle"></i>
            </a>
            <h1><a href="index.php">Los Jaulares</a></h1>
        </header>
    ';
}

// Tarjeta verde con el check que sale en el Figma cuando algo se hace bien
function MostrarTarjetaExito()
{
    echo '
        <div class="modal-exito" id="tarjetaExito" role="dialog" aria-live="polite" hidden>
            <div class="tarjeta">
                <svg viewBox="0 0 120 90" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="8,48 40,80 112,8" />
                </svg>
                <p id="mensajeExito"></p>
            </div>
        </div>
    ';
}

function IncludeJS()
{
    echo '
        <script src="Assets/js/auth.js"></script>
    ';
}
