<?php

// abre la conexion de la pagina del cliente con la base supermercado_jaulares
function Conectar()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return new mysqli("127.0.0.1:3307","client_jaulares","Client_Jaulares2026!","supermercado_jaulares");
}

// abre la conexion del panel admin con la base supermercado_jaulares
function ConectarAdmin()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return new mysqli("127.0.0.1:3307","admin_jaulares","Admin_Jaulares2026!","supermercado_jaulares");
}

// cierra la conexion
function Cerrar($context)
{
    $context -> close();
}

// guarda en la tabla errores lo que atrapa el catch, igual que en clase
function RegistrarError($exc, $accion)
{
    try {
        $context = Conectar();

        $error = mb_substr($exc -> getMessage(), 0, 4000);
        $idUsuario = isset($_SESSION['idUsuario']) ? (int) $_SESSION['idUsuario'] : 0;

        $sentencia = $context -> prepare("CALL sp_error_registrar(?, ?, ?)");
        $sentencia -> bind_param("ssi", $error, $accion, $idUsuario);
        $sentencia -> execute();

        Cerrar($context);
    }
    catch (Exception $e) {
        // si la base no responde no se puede guardar, la pagina sigue igual
    }

    // los errores que lanza la base con signal (codigo 1644) se pueden mostrar al usuario
    if ($exc -> getCode() == 1644) {
        $_POST["errorBD"] = $exc -> getMessage();
    }
}
