<?php
include_once __DIR__ . '/../layoutExterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Inicio de sesión'); ?>

<body>

    <?php MostrarEncabezado('home.php'); ?>

    <main class="contenido">

        <nav class="pestanas">
            <a href="registro.php" class="pestana">Registro</a>
            <a href="login.php" class="pestana activa" aria-current="page">Inicio de sesión</a>
        </nav>

        <?php MostrarMensaje(); ?>

        <!-- se envia a la misma pagina y lo procesa InicioController -->
        <form action="" method="POST" id="formLogin" novalidate>

            <label class="campo">
                <i class="bi bi-envelope"></i>
                <input type="email" id="txtEmail" name="txtEmail"
                       placeholder="Correo electrónico"
                       autocomplete="username"
                       value="<?php echo isset($_POST['txtEmail']) ? htmlspecialchars($_POST['txtEmail'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            </label>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtContrasenna" name="txtContrasenna"
                       placeholder="Contraseña"
                       autocomplete="current-password" required>
                <button type="button" class="ver-contrasena"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </label>

            <a href="recuperar.php" class="olvido">Contraseña olvidada</a>

            <button type="submit" name="btnInicioSesion" class="btn-principal">Iniciar sesión</button>

        </form>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS('login.js'); ?>

</body>
</html>
