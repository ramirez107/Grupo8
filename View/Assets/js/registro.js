// validacion del registro de usuario
$(function () {
    $('#formRegistro').validate({
        rules: {
            txtNombre: { required: true, minlength: 2 },
            txtApellidos: { required: true, minlength: 2 },
            txtEmail: { required: true, email: true },
            txtTelefono: { required: true, telefonoCR: true },
            txtContrasenna: { required: true, contrasennaSegura: true },
            txtConfirmarContrasenna: { required: true, equalTo: '#txtContrasenna' }
        },
        messages: {
            txtNombre: {
                required: 'El nombre es obligatorio',
                minlength: 'Ingrese al menos 2 letras'
            },
            txtApellidos: {
                required: 'Los apellidos son obligatorios',
                minlength: 'Ingrese al menos 2 letras'
            },
            txtEmail: {
                required: 'El correo electrónico es obligatorio',
                email: 'Ingrese un correo electrónico válido'
            },
            txtTelefono: {
                required: 'El teléfono es obligatorio',
                telefonoCR: 'Ingrese un teléfono de 8 números, por ejemplo 8888-8888'
            },
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
