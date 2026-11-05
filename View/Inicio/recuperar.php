
<?php
include_once __DIR__ . '/../layoutExterno.php';

// Determinar la pantalla que se debe mostrar
$paso = $_GET['paso'] ?? 'solicitar';

if (!in_array($paso, ['solicitar', 'nueva'], true)) {
    $paso = 'solicitar';
}

// Obtener el token enviado en el enlace de recuperación
$token = $_GET['token'] ?? '';
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

            <!-- El envío real del enlace se integrará posteriormente -->
            <form action="recuperar.php" method="POST"
                  id="formRecuperar" novalidate>

                <label class="campo">
                    <i class="bi bi-envelope"></i>

                    <input
                        type="email"
                        id="txtEmail"
                        name="email"
                        placeholder="Correo electrónico"
                        autocomplete="email"
                        required
                    >
                </label>

                <small class="mensaje-error">
                    Ingrese un correo electrónico válido.
                </small>

                <button type="submit" class="btn-principal ancho">
                    Enviar enlace de recuperación
                </button>

            </form>

            <a href="login.php" class="enlace-secundario">
                Volver al inicio de sesión
            </a>

        <?php else: ?>

            <h2 class="titulo-seccion">Nueva contraseña</h2>

            <p class="descripcion">
                Ingrese su nueva contraseña y confírmela para recuperar
                el acceso a su cuenta.
            </p>

            <!-- La validación real del token se integrará con la BD -->
            <form action="login.php" method="POST"
                  id="formNueva" novalidate
                  data-exito="Su contraseña se actualizó exitosamente"
                  data-destino="login.php">

                <input type="hidden" name="token"
                       value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <label class="campo">
                    <i class="bi bi-lock"></i>

                    <input
                        type="password"
                        id="txtContrasenna"
                        name="contrasenna"
                        placeholder="Contraseña nueva"
                        autocomplete="new-password"
                        minlength="8"
                        pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}"
                        required
                    >

                    <button type="button" class="ver-contrasena"
                            aria-label="Mostrar contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </label>

                <small class="mensaje-error">
                    La contraseña debe tener al menos 8 caracteres,
                    incluyendo letras y números.
                </small>

                <label class="campo">
                    <i class="bi bi-lock-fill"></i>

                    <input
                        type="password"
                        id="txtConfirmar"
                        name="confirmarContrasenna"
                        placeholder="Confirmar contraseña"
                        autocomplete="new-password"
                        minlength="8"
                        data-igual-a="txtContrasenna"
                        required
                    >

                    <button type="button" class="ver-contrasena"
                            aria-label="Mostrar contraseña">
                        <i class="bi bi-eye"></i>
                    </button>
                </label>

                <small class="mensaje-error">
                    Las contraseñas no coinciden.
                </small>

                <button type="submit" class="btn-principal ancho">
                    Guardar contraseña
                </button>

            </form>

        <?php endif; ?>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS(); ?>

</body>
</html>
