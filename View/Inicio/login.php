
<?php include_once __DIR__ . '/../layoutExterno.php'; ?>

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

        <!-- Por ahora se validan los campos; posteriormente se conectará con la base de datos -->
        <form action="home.php" method="POST" id="formLogin" novalidate
              data-exito="Su inicio de sesión se realizó exitosamente"
              data-destino="home.php">

            <label class="campo">
                <i class="bi bi-envelope"></i>
                <input type="email" id="txtEmail" name="email"
                       placeholder="Correo electrónico"
                       autocomplete="username" required>
            </label>
            <small class="mensaje-error">Ingrese un correo electrónico válido.</small>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtContrasenna" name="contrasenna"
                       placeholder="Contraseña"
                       autocomplete="current-password" required>
                <button type="button" class="ver-contrasena"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </label>
            <small class="mensaje-error">Ingrese su contraseña.</small>

            <a href="recuperar.php" class="olvido">Contraseña olvidada</a>

            <button type="submit" class="btn-principal">Iniciar sesión</button>

        </form>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS(); ?>

</body>
</html>
