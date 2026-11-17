<?php
include_once __DIR__ . '/../layoutExterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';

// determina la pantalla que se debe mostrar
$paso = $_GET['paso'] ?? 'solicitar';

if (!in_array($paso, ['solicitar', 'nueva'], true)) {
    $paso = 'solicitar';
}

// token que viene en el enlace de recuperacion
$token = $_GET['token'] ?? ($_POST['token'] ?? '');

// si el enlace ya no sirve no se muestra el formulario de contrasenna nueva
$tokenValido = $paso === 'nueva' && !isset($_POST["exito"]) ? TokenValido($token) : true;
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Recuperar contraseña'); ?>

<body>

    <?php MostrarEncabezado($paso === 'solicitar' ? 'login.php' : 'recuperar.php'); ?>

    <main class="contenido">

        <?php if ($paso === 'solicitar'): ?>

            <h2 class="titulo-seccion">Recuperar contraseña</h2>

            <p class="descripcion">
                Ingrese su correo electrónico y recibirá un enlace para
                restablecer su contraseña.
            </p>

            <?php MostrarMensaje(); ?>

            <form action="" method="POST"
                  id="formRecuperar" novalidate>

                <label class="campo">
                    <i class="bi bi-envelope"></i>

                    <input
                        type="email"
                        id="txtEmail"
                        name="txtEmail"
                        placeholder="Correo electrónico"
                        autocomplete="email"
                        value="<?php echo isset($_POST['txtEmail']) ? htmlspecialchars($_POST['txtEmail'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        required
                    >
                </label>


                <button type="submit" name="btnRecuperarContrasenna" class="btn-principal ancho">
                    Enviar enlace de recuperación
                </button>

            </form>

            <a href="login.php" class="enlace-secundario">
                Volver al inicio de sesión
            </a>

        <?php elseif (!$tokenValido): ?>

            <h2 class="titulo-seccion">Enlace no válido</h2>

            <div class="alert alert-warning" role="alert">
                El enlace venció o ya fue usado. Solicite uno nuevo.
            </div>

            <a href="recuperar.php" class="enlace-secundario">
                Solicitar otro enlace
            </a>

        <?php else: ?>

            <h2 class="titulo-seccion">Nueva contraseña</h2>

            <p class="descripcion">
                Ingrese su nueva contraseña y confírmela para recuperar
                el acceso a su cuenta.
            </p>

            <?php MostrarMensaje(); ?>

            <form action="recuperar.php?paso=nueva" method="POST"
                  id="formNueva" novalidate>

                <input type="hidden" name="token"
                       value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <label class="campo">
                    <i class="bi bi-lock"></i>

                    <input
                        type="password"
                        id="txtContrasenna"
                        name="txtContrasenna"
                        placeholder="Contraseña nueva"
                        autocomplete="new-password"
                        required
                    >

                    <button type="button" class="ver-contrasena"
                            aria-label="Mostrar contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </label>


                <label class="campo">
                    <i class="bi bi-lock-fill"></i>

                    <input
                        type="password"
                        id="txtConfirmar"
                        name="txtConfirmarContrasenna"
                        placeholder="Confirmar contraseña"
                        autocomplete="new-password"
                        required
                    >

                    <button type="button" class="ver-contrasena"
                            aria-label="Mostrar contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </label>


                <button type="submit" name="btnGuardarContrasenna" class="btn-principal ancho">
                    Guardar contraseña
                </button>

            </form>

        <?php endif; ?>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS('recuperar.js'); ?>

</body>
</html>
