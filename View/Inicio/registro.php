
<?php include_once __DIR__ . '/../layoutExterno.php'; ?>

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

        <!-- Formulario de registro: el almacenamiento se integrará posteriormente con la BD -->
        <form action="login.php" method="GET" id="formRegistro" novalidate
              data-exito="Su registro se realizó exitosamente"
              data-destino="login.php">

            <label class="campo">
                <i class="bi bi-person"></i>
                <input type="text" id="txtNombre" name="nombre"
                       placeholder="Nombre" autocomplete="given-name"
                       minlength="2" required>
            </label>
            <small class="mensaje-error">Ingrese su nombre.</small>

            <label class="campo">
                <i class="bi bi-person"></i>
                <input type="text" id="txtApellidos" name="apellidos"
                       placeholder="Apellidos" autocomplete="family-name"
                       minlength="2" required>
            </label>
            <small class="mensaje-error">Ingrese sus apellidos.</small>

            <label class="campo">
                <i class="bi bi-envelope"></i>
                <input type="email" id="txtEmail" name="email"
                       placeholder="Correo electrónico" autocomplete="email"
                       required>
            </label>
            <small class="mensaje-error">Ingrese un correo electrónico válido.</small>

            <label class="campo">
                <i class="bi bi-telephone"></i>
                <input type="tel" id="txtTelefono" name="telefono"
                       placeholder="Teléfono" autocomplete="tel"
                       pattern="[0-9]{4}-?[0-9]{4}" required>
            </label>
            <small class="mensaje-error">Ingrese un teléfono de 8 números, por ejemplo 8888-8888.</small>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtContrasenna" name="contrasenna"
                       placeholder="Contraseña" autocomplete="new-password"
                       minlength="8" pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}"
                       required>
                <button type="button" class="ver-contrasena"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </label>
            <small class="mensaje-error">La contraseña debe tener al menos 8 caracteres, incluyendo letras y números.</small>

            <label class="campo">
                <i class="bi bi-lock"></i>
                <input type="password" id="txtConfirmarContrasenna"
                       name="confirmarContrasenna"
                       placeholder="Confirmar contraseña"
                       autocomplete="new-password" minlength="8"
data-igual-a="txtContrasenna" required>
                <button type="button" class="ver-contrasena"
                        aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                </button>
            </label>
            <small class="mensaje-error">Confirme su contraseña.</small>

            <button type="submit" class="btn-principal">Registrarse</button>

        </form>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS(); ?>

</body>
</html>
