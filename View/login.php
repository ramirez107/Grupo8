<?php include_once 'layoutExterno.php'; ?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Inicio de sesión'); ?>

<body>

    <?php MostrarEncabezado('index.php'); ?>

    <main class="contenido">

        <nav class="pestanas">
            <a href="registro.php" class="pestana">Registro</a>
            <a href="login.php" class="pestana activa" aria-current="page">Inicio de sesión</a>
        </nav>

        <!-- Por ahora solo reviso los campos y paso a la página principal, después lo conecto a la base de datos -->
        <form action="index.php" method="GET" id="formLogin" novalidate
              data-exito="Su inicio de sesión se realizó exitosamente" data-destino="index.php">

            <label class="campo">
                <i class="bi bi-person"></i>
                <input type="text" id="txtUsuario" placeholder="Usuario" autocomplete="username" required>
            </label>
            <small class="mensaje-error">Ingrese su usuario.</small>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtContrasenna" placeholder="Contraseña" autocomplete="current-password" required>
                <button type="button" class="ver-contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
            </label>
            <small class="mensaje-error">Ingrese su contraseña.</small>

            <a href="recuperar.php" class="olvido">Contraseña olvidada</a>

            <button type="submit" class="btn-principal">Iniciar sesión</button>
        </form>

        <section class="social">
            <h2>O iniciar sesión con</h2>
            <div class="social-botones">
                <button type="button" class="btn-social"><i class="bi bi-google"></i> Google</button>
                <button type="button" class="btn-social"><i class="bi bi-apple"></i> Apple</button>
                <button type="button" class="btn-social"><i class="bi bi-facebook"></i> Facebook</button>
            </div>
            <p id="avisoSocial" class="aviso" hidden>El inicio de sesión con redes sociales estará disponible pronto.</p>
        </section>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS(); ?>

</body>

</html>
