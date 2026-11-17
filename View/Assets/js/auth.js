
// javascript comun de login, registro y recuperar contrasenna
// la validacion de cada formulario esta en login.js, registro.js y recuperar.js (jquery validate)

document.addEventListener('DOMContentLoaded', function () {

    // Mostrar u ocultar la contrasenna
    document.querySelectorAll('.ver-contrasena').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const input = boton.parentElement.querySelector('input');
            const icono = boton.querySelector('i');
            const mostrar = input.type === 'password';

            input.type = mostrar ? 'text' : 'password';
            icono.classList.toggle('bi-eye', !mostrar);
            icono.classList.toggle('bi-eye-slash', mostrar);
            boton.setAttribute(
                'aria-label',
                mostrar ? 'Ocultar contrasenna' : 'Mostrar contrasenna'
            );
        });
    });

    // si el controlador mostro la tarjeta de exito, pasa a la siguiente pagina
    const tarjeta = document.getElementById('tarjetaExito');

    if (tarjeta && !tarjeta.hidden && tarjeta.dataset.destino) {
        setTimeout(function () {
            window.location.href = tarjeta.dataset.destino;
        }, 2000);
    }
});

// configuracion comun de jquery validate para los formularios de cuenta
if (window.jQuery && $.validator) {

    // contrasenna de al menos 8 caracteres con letras y numeros
    $.validator.addMethod('contrasennaSegura', function (valor, elemento) {
        return this.optional(elemento) || /^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/.test(valor);
    });

    // telefono de 8 numeros, con o sin guion: 8888-8888
    $.validator.addMethod('telefonoCR', function (valor, elemento) {
        return this.optional(elemento) || /^[0-9]{4}-?[0-9]{4}$/.test(valor);
    });

    $.validator.setDefaults({
        // no se valida al salir del campo: si el mensaje desaparece justo al dar clic
        // el boton se mueve y el clic se pierde; se valida al escribir y al enviar
        onfocusout: false,
        errorElement: 'small',
        errorClass: 'error-campo',
        errorPlacement: function (error, elemento) {
            error.insertAfter(elemento.closest('.campo'));
        },
        highlight: function (elemento) {
            $(elemento).closest('.campo').addClass('invalido');
        },
        unhighlight: function (elemento) {
            $(elemento).closest('.campo').removeClass('invalido');
        }
    });
}
