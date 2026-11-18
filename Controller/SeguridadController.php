<?php
include_once __DIR__ . '/ConfigController.php';

// revisa que haya sesion, si no manda al login
function ValidarSesion()
{
    if (!isset($_SESSION['idUsuario'])) {
        // el login muestra este aviso al llegar
        $_SESSION['aviso'] = 'Debe iniciar sesión para acceder a esta sección';
        header("Location: " . RutaView() . "Inicio/login.php");
        exit();
    }
}

// revisa que el usuario tenga el rol, si es cliente lo manda al catalogo
function ValidarRol($rol)
{
    ValidarSesion();

    if ($_SESSION['rol'] != $rol) {
        header("Location: " . RutaView() . "Catalogo/catalogo.php");
        exit();
    }
}

// true si el usuario conectado es administrador, para mostrar el menu del panel
function EsAdministrador()
{
    return isset($_SESSION['rol']) && $_SESSION['rol'] == 'Administrador';
}
