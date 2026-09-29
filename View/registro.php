<?php include_once 'layoutExterno.php'; ?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Registro'); ?>

<body>

    <?php MostrarEncabezado('index.php'); ?>

    <main class="contenido">

        <nav class="pestanas">
            <a href="registro.php" class="pestana activa" aria-current="page">Registro</a>
            <a href="login.php" class="pestana">Inicio de sesión</a>
        </nav>

        <!-- Por ahora solo reviso los campos y paso al inicio de sesión, después guardo el usuario en la base de datos -->
        <form action="login.php" method="GET" id="formRegistro" novalidate
              data-exito="Su registro se realizó exitosamente" data-destino="login.php">

            <label class="campo">
                <i class="bi bi-person"></i>
                <input type="text" id="txtUsuario" placeholder="Usuario" autocomplete="username" minlength="3" required>
            </label>
            <small class="mensaje-error">El usuario debe tener al menos 3 caracteres.</small>

            <label class="campo">
                <i class="bi bi-envelope"></i>
                <input type="email" id="txtEmail" placeholder="Email" autocomplete="email" required>
            </label>
            <small class="mensaje-error">Ingrese un email válido.</small>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtContrasenna" placeholder="Contraseña" autocomplete="new-password" minlength="6" required>
                <button type="button" class="ver-contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
            </label>
            <small class="mensaje-error">La contraseña debe tener al menos 6 caracteres.</small>

            <label class="campo">
                <i class="bi bi-telephone"></i>
                <input type="tel" id="txtTelefono" placeholder="Teléfono" autocomplete="tel" pattern="[0-9]{4}-?[0-9]{4}" required>
            </label>
            <small class="mensaje-error">Ingrese un teléfono de 8 números, por ejemplo 8888-8888.</small>

            <button type="submit" class="btn-principal">Registrarse</button>
        </form>

        <section class="social">
            <h2>O registrarse con</h2>
            <div class="social-botones">
                <button type="button" class="btn-social"><i class="bi bi-google"></i> Google</button>
                <button type="button" class="btn-social"><i class="bi bi-apple"></i> iCloud</button>
                <button type="button" class="btn-social"><i class="bi bi-facebook"></i> Facebook</button>
            </div>
            <p id="avisoSocial" class="aviso" hidden>El registro con redes sociales estará disponible pronto.</p>
        </section>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS(); ?>

</body>

</html>
