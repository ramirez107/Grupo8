<?php
include_once __DIR__ . '/../Model/InicioModel.php';
include_once __DIR__ . '/../Model/CategoriaModel.php';
include_once __DIR__ . '/../Model/ProductoModel.php';
include_once __DIR__ . '/SeguridadController.php';

// registro de usuario
if (isset($_POST['btnRegistro'])) {
    $nombre = trim($_POST['txtNombre']);
    $apellidos = trim($_POST['txtApellidos']);
    $correo = strtolower(trim($_POST['txtEmail']));
    $telefono = trim($_POST['txtTelefono']);
    $contrasenna = $_POST['txtContrasenna'];
    $confirmar = $_POST['txtConfirmarContrasenna'];

    // se valida otra vez en php por si alguien se salta el javascript
    if ($nombre == '' || $apellidos == '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || !preg_match('/^[0-9]{4}-?[0-9]{4}$/', $telefono)) {
        $_POST["mensaje"] = "Complete todos los campos correctamente";
        $_POST["tipoMensaje"] = "danger";
    }
    else if (!ContrasennaValida($contrasenna)) {
        $_POST["mensaje"] = "La contraseña debe tener al menos 8 caracteres, incluyendo letras y números";
        $_POST["tipoMensaje"] = "danger";
    }
    else if ($contrasenna !== $confirmar) {
        $_POST["mensaje"] = "Las contraseñas no coinciden";
        $_POST["tipoMensaje"] = "danger";
    }
    else if (ConsultarCorreoModel($correo)) {
        // igual que en la practica: se busca antes de insertar
        $_POST["mensaje"] = "Ya existe una cuenta registrada con el correo " . htmlspecialchars($correo, ENT_QUOTES, 'UTF-8');
        $_POST["tipoMensaje"] = "warning";
    }
    else {
        // la contrasenna nunca se guarda tal cual, se guarda el hash
        $telefono = str_replace('-', '', $telefono);
        $hash = password_hash($contrasenna, PASSWORD_DEFAULT);

        $response = RegistroModel($nombre, $apellidos, $correo, $telefono, $hash);

        if ($response) {
            $_POST["exito"] = "Su registro se realizó exitosamente";
            $_POST["destino"] = "login.php";
        }
        else {
            $_POST["mensaje"] = $_POST["errorBD"] ?? "Su información no se ha podido registrar correctamente";
            $_POST["tipoMensaje"] = "danger";
        }
    }
}

// inicio de sesion
if (isset($_POST['btnInicioSesion'])) {
    $correo = strtolower(trim($_POST['txtEmail']));
    $contrasenna = $_POST['txtContrasenna'];

    $usuario = InicioSesionModel($correo);

    // mensaje generico para no decir si el correo existe
    if (!$usuario) {
        $_POST["mensaje"] = "Correo o contraseña incorrectos";
        $_POST["tipoMensaje"] = "danger";
    }
    else if ($usuario['bloqueado'] == 1) {
        $_POST["mensaje"] = "Su cuenta está bloqueada por 3 intentos fallidos. Intente de nuevo en 15 minutos";
        $_POST["tipoMensaje"] = "warning";
    }
    else if ($usuario['activo'] != 1) {
        $_POST["mensaje"] = "Su cuenta está inactiva";
        $_POST["tipoMensaje"] = "warning";
    }
    else if (!password_verify($contrasenna, $usuario['password_hash'])) {
        InicioSesionFallidoModel($correo);

        $_POST["mensaje"] = "Correo o contraseña incorrectos";
        $_POST["tipoMensaje"] = "danger";
    }
    else {
        InicioSesionExitosoModel($usuario['id_usuario']);

        // nueva sesion para que no se reuse el id viejo
        session_regenerate_id(true);
        $_SESSION['idUsuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // el admin va al panel y el cliente al catalogo
        $_POST["exito"] = "Su inicio de sesión se realizó exitosamente";
        $_POST["destino"] = EsAdministrador()
            ? RutaView() . "Admin/Productos/listado.php"
            : RutaView() . "Catalogo/catalogo.php";
    }
}

// recuperar acceso: paso 1, pedir el enlace
if (isset($_POST['btnRecuperarContrasenna'])) {
    $correo = strtolower(trim($_POST['txtEmail']));

    $token = bin2hex(random_bytes(32));
    $response = CrearRecuperacionModel($correo, $token);

    if ($response) {
        $enlace = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . dirname($_SERVER['SCRIPT_NAME']) . '/recuperar.php?paso=nueva&token=' . $token;

        $plantilla = file_get_contents(__DIR__ . '/../View/template/RecuperarAcceso.html');
        $plantilla = str_replace("{{NOMBRE}}", Texto($response['nombre']), $plantilla);
        $plantilla = str_replace("{{ENLACE}}", Texto($enlace), $plantilla);

        if (EnviarCorreoElectronico("Recuperación de acceso", $plantilla, $response['correo'])) {
            $_POST["mensaje"] = "Le enviamos un enlace a su correo para restablecer la contraseña (vence en 30 minutos)";
        }
        else {
            // para la demo, si no hay correo configurado el enlace se muestra en pantalla
            $_POST["mensaje"] = "Enlace de recuperación (vence en 30 minutos): "
                . "<a href=\"recuperar.php?paso=nueva&token=" . $token . "\">restablecer contraseña</a>";
        }
        $_POST["tipoMensaje"] = "success";
    }
    else {
        $_POST["mensaje"] = "No se ha podido recuperar su acceso al sistema";
        $_POST["tipoMensaje"] = "danger";
    }
}

// recuperar acceso: paso 2, guardar la contrasenna nueva
if (isset($_POST['btnGuardarContrasenna'])) {
    $token = $_POST['token'];
    $contrasenna = $_POST['txtContrasenna'];
    $confirmar = $_POST['txtConfirmarContrasenna'];

    if (!ContrasennaValida($contrasenna)) {
        $_POST["mensaje"] = "La contraseña debe tener al menos 8 caracteres, incluyendo letras y números";
        $_POST["tipoMensaje"] = "danger";
    }
    else if ($contrasenna !== $confirmar) {
        $_POST["mensaje"] = "Las contraseñas no coinciden";
        $_POST["tipoMensaje"] = "danger";
    }
    else {
        $hash = password_hash($contrasenna, PASSWORD_DEFAULT);

        $response = UsarRecuperacionModel($token, $hash);

        if ($response) {
            $_POST["exito"] = "Su contraseña se actualizó exitosamente";
            $_POST["destino"] = "login.php";
        }
        else {
            $_POST["mensaje"] = "El enlace venció o ya fue usado. Solicite uno nuevo";
            $_POST["tipoMensaje"] = "danger";
        }
    }
}

// cerrar sesion, vuelve al inicio sin sesion
if (isset($_POST['btnCerrarSesion'])) {
    session_unset();
    session_destroy();

    header("Location: " . RutaView() . "Inicio/home.php");
    exit();
}

// datos de la pagina de inicio: promociones, categorias y productos destacados
function ConsultarPromociones()
{
    return ConsultarPromocionesVigentesModel();
}

function ConsultarCategorias()
{
    return ConsultarCategoriasActivasModel();
}

function ConsultarDestacados()
{
    return ConsultarProductosDestacadosModel(8);
}

// revisa el enlace de recuperacion antes de mostrar el formulario
function TokenValido($token)
{
    if ($token == '') {
        return false;
    }

    return ValidarRecuperacionModel($token) ? true : false;
}

// misma regla que el formulario: 8 caracteres con letras y numeros
function ContrasennaValida($contrasenna)
{
    return strlen($contrasenna) >= 8
        && preg_match('/[A-Za-z]/', $contrasenna)
        && preg_match('/[0-9]/', $contrasenna);
}
