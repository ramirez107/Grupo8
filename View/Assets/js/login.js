// validacion del inicio de sesion
$(function () {
    $('#formLogin').validate({
        rules: {
            txtEmail: { required: true, email: true },
            txtContrasenna: { required: true }
        },
        messages: {
            txtEmail: {
                required: 'El correo electrónico es obligatorio',
                email: 'Ingrese un correo electrónico válido'
            },
            txtContrasenna: { required: 'La contraseña es obligatoria' }
        }
    });
});
