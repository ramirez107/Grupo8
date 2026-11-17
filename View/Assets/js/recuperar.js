// validacion de recuperar contrasenna (pedir enlace y contrasenna nueva)
$(function () {
    $('#formRecuperar').validate({
        rules: {
            txtEmail: { required: true, email: true }
        },
        messages: {
            txtEmail: {
                required: 'El correo electrónico es obligatorio',
                email: 'Ingrese un correo electrónico válido'
            }
        }
    });

    $('#formNueva').validate({
        rules: {
            txtContrasenna: { required: true, contrasennaSegura: true },
            txtConfirmarContrasenna: { required: true, equalTo: '#txtContrasenna' }
        },
        messages: {
            txtContrasenna: {
                required: 'La contraseña es obligatoria',
                contrasennaSegura: 'La contraseña debe tener al menos 8 caracteres, incluyendo letras y números'
            },
            txtConfirmarContrasenna: {
                required: 'Confirme su contraseña',
                equalTo: 'Las contraseñas no coinciden'
            }
        }
    });
});
