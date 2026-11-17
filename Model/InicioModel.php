<?php
include_once __DIR__ . '/ConfigModel.php';

// registra el usuario con la contrasenna ya encriptada (rol cliente)
function RegistroModel($nombre, $apellidos, $correo, $telefono, $contrasennaHash)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_usuario_registrar(?, ?, ?, ?, ?)");
        $sentencia -> bind_param("sssss", $nombre, $apellidos, $correo, $telefono, $contrasennaHash);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "RegistroModel");
        return false;
    }
}

// revisa si el correo ya esta registrado
function ConsultarCorreoModel($correo)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_usuario_consultar_correo(?)");
        $sentencia -> bind_param("s", $correo);
        $sentencia -> execute();
        $row = $sentencia -> get_result() -> fetch_assoc();

        Cerrar($context);
        return $row['existe'] == 1;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ConsultarCorreoModel");
        return false;
    }
}

// trae los datos del usuario para validar la contrasenna en php
function InicioSesionModel($correo)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_usuario_login(?)");
        $sentencia -> bind_param("s", $correo);
        $sentencia -> execute();
        $row = $sentencia -> get_result() -> fetch_assoc();

        Cerrar($context);
        return $row;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "InicioSesionModel");
        return null;
    }
}

// suma un intento fallido, a los 3 se bloquea 15 minutos
function InicioSesionFallidoModel($correo)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_usuario_login_fallido(?)");
        $sentencia -> bind_param("s", $correo);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "InicioSesionFallidoModel");
        return false;
    }
}

// limpia los intentos fallidos cuando entra bien
function InicioSesionExitosoModel($idUsuario)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_usuario_login_exitoso(?)");
        $sentencia -> bind_param("i", $idUsuario);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "InicioSesionExitosoModel");
        return false;
    }
}

// crea el token de recuperacion, devuelve la fila o null si el correo no existe
function CrearRecuperacionModel($correo, $token)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_recuperacion_crear(?, ?)");
        $sentencia -> bind_param("ss", $correo, $token);
        $sentencia -> execute();

        // si el correo no existe el procedimiento no devuelve tabla
        $row = null;
        $resultado = $sentencia -> get_result();
        if ($resultado) {
            $row = $resultado -> fetch_assoc();
        }

        Cerrar($context);
        return $row;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "CrearRecuperacionModel");
        return null;
    }
}

// revisa que el token exista, no este usado y no haya vencido
function ValidarRecuperacionModel($token)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_recuperacion_validar(?)");
        $sentencia -> bind_param("s", $token);
        $sentencia -> execute();
        $row = $sentencia -> get_result() -> fetch_assoc();

        Cerrar($context);
        return $row;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "ValidarRecuperacionModel");
        return null;
    }
}

// cambia la contrasenna con el token y lo marca como usado
function UsarRecuperacionModel($token, $contrasennaHash)
{
    try {
        $context = Conectar();

        $sentencia = $context -> prepare("CALL sp_recuperacion_usar(?, ?)");
        $sentencia -> bind_param("ss", $token, $contrasennaHash);
        $response = $sentencia -> execute();

        Cerrar($context);
        return $response;
    }
    catch (Exception $exc) {
        RegistrarError($exc, "UsarRecuperacionModel");
        return false;
    }
}
