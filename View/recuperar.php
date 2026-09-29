<?php
include_once 'layoutExterno.php';

// Esta página tiene 3 pantallas: recuperar contraseña, código de verificación y nueva contraseña
$paso = $_GET['paso'] ?? 'solicitar';
if (!in_array($paso, ['solicitar', 'codigo', 'nueva'])) {
    $paso = 'solicitar';
}
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Recuperar contraseña'); ?>

<body>

    <?php MostrarEncabezado($paso === 'solicitar' ? 'login.php' : 'recuperar.php'); ?>

    <main class="contenido">

        <?php if ($paso === 'solicitar'): ?>

            <h2 class="titulo-seccion">Recuperar contraseña</h2>
            <p class="descripcion">Recibirá un código de recuperación en su correo si su cuenta está registrada en el sistema.</p>

            <!-- Todavía no envío el correo, solo paso a la pantalla del código -->
            <form action="recuperar.php" method="GET" id="formRecuperar" novalidate>
                <input type="hidden" name="paso" value="codigo">

                <label class="campo">
                    <i class="bi bi-person"></i>
                    <input type="text" id="txtUsuario" placeholder="Usuario" autocomplete="username" required>
                </label>
                <small class="mensaje-error">Ingrese su usuario.</small>

                <label class="campo">
                    <i class="bi bi-envelope"></i>
                    <input type="email" id="txtEmail" placeholder="Email" autocomplete="email" required>
                </label>
                <small class="mensaje-error">Ingrese un email válido.</small>

                <button type="submit" class="btn-principal ancho">Enviar código de recuperación</button>
            </form>

            <a href="login.php" class="enlace-secundario">Volver al inicio de sesión</a>

        <?php elseif ($paso === 'codigo'): ?>

            <h2 class="titulo-seccion">Código de verificación</h2>
            <p class="descripcion">Por favor ingrese el código de verificación enviado al correo registrado en el sistema.</p>

            <form action="recuperar.php" method="GET" id="formCodigo" novalidate
                  data-exito="Su código ha sido verificado con éxito" data-destino="recuperar.php?paso=nueva">
                <input type="hidden" name="paso" value="nueva">

                <div class="codigo">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1"
                               aria-label="Número <?= $i ?> del código" autocomplete="off" required>
                    <?php endfor; ?>
                </div>
                <small class="mensaje-error centrado">Ingrese los 5 números del código.</small>

                <a href="recuperar.php?paso=codigo" class="btn-texto">Reenviar código</a>

                <button type="submit" class="btn-principal ancho">Verificar código</button>
            </form>

        <?php else: ?>

            <h2 class="titulo-seccion">Nueva contraseña</h2>
            <p class="descripcion">Cree una contraseña nueva para su cuenta.</p>

            <form action="login.php" method="GET" id="formNueva" novalidate
                  data-exito="Su contraseña se actualizó exitosamente" data-destino="login.php">

                <label class="campo">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="txtContrasenna" placeholder="Contraseña nueva" autocomplete="new-password" minlength="6" required>
                    <button type="button" class="ver-contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </label>
                <small class="mensaje-error">La contraseña debe tener al menos 6 caracteres.</small>

                <label class="campo">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" id="txtConfirmar" placeholder="Confirmar contraseña" autocomplete="new-password" data-igual-a="txtContrasenna" required>
                    <button type="button" class="ver-contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </label>
                <small class="mensaje-error">Las contraseñas no coinciden.</small>

                <button type="submit" class="btn-principal ancho">Guardar contraseña</button>
            </form>

        <?php endif; ?>

    </main>

    <?php MostrarTarjetaExito(); ?>

    <?php IncludeJS(); ?>

</body>

</html>
