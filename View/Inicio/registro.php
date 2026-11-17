<?php
include_once __DIR__ . '/../layoutExterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Registro'); ?>

<body>

    <?php MostrarEncabezado('home.php'); ?>

    <main class="contenido">

        <nav class="pestanas">
            <a href="registro.php" class="pestana activa" aria-current="page">Registro</a>
            <a href="login.php" class="pestana">Inicio de sesión</a>
        </nav>

        <?php MostrarMensaje(); ?>

        <!-- post para que la contrasenna no salga en la url -->
        <form action="" method="POST" id="formRegistro" novalidate>

            <label class="campo">
                <i class="bi bi-person"></i>
                <input type="text" id="txtNombre" name="txtNombre"
                       placeholder="Nombre" autocomplete="given-name"
                       value="<?php echo isset($_POST['txtNombre']) ? htmlspecialchars($_POST['txtNombre'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            </label>

            <label class="campo">
                <i class="bi bi-person"></i>
                <input type="text" id="txtApellidos" name="txtApellidos"
                       placeholder="Apellidos" autocomplete="family-name"
                       value="<?php echo isset($_POST['txtApellidos']) ? htmlspecialchars($_POST['txtApellidos'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            </label>

            <label class="campo">
                <i class="bi bi-envelope"></i>
                <input type="email" id="txtEmail" name="txtEmail"
                       placeholder="Correo electrónico" autocomplete="email"
                       value="<?php echo isset($_POST['txtEmail']) ? htmlspecialchars($_POST['txtEmail'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                       required>
            </label>

            <label class="campo">
                <i class="bi bi-telephone"></i>
                <input type="tel" id="txtTelefono" name="txtTelefono"
                       placeholder="Teléfono" autocomplete="tel"
                       value="<?php echo isset($_POST['txtTelefono']) ? htmlspecialchars($_POST['txtTelefono'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            </label>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtContrasenna" name="txtContrasenna"
                       placeholder="Contraseña" autocomplete="new-password"
                       required>
                <button type="button" class="ver-contrasena"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </label>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtConfirmarContrasenna"
                       name="txtConfirmarContrasenna"
                       placeholder="Confirmar contraseña"
                       autocomplete="new-password" required>
                <button type="button" class="ver-contrasena"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </label>

            <button type="submit" name="btnRegistro" class="btn-principal">Registrarse</button>

        </form>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS('registro.js'); ?>

</body>
</html>
