
// JavaScript de login, registro y recuperar contrasenna

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

    // Confirmar que ambas contrasennas sean iguales
    document.querySelectorAll('[data-igual-a]').forEach(function (confirmar) {
        const original = document.getElementById(confirmar.dataset.igualA);

        if (!original) return;

        function revisar() {
            confirmar.setCustomValidity(
                confirmar.value === original.value ? '' : 'No coinciden'
            );
        }

        confirmar.addEventListener('input', revisar);
        original.addEventListener('input', revisar);
    });

    // Codigo de recuperacion: avanzar entre casillas y permitir pegar
    const cajas = Array.from(document.querySelectorAll('.codigo input'));

    if (cajas.length) cajas[0].focus();

    cajas.forEach(function (caja, i) {
        caja.addEventListener('input', function () {
            caja.value = caja.value.replace(/\D/g, '').slice(-1);

            if (caja.value && cajas[i + 1]) {
                cajas[i + 1].focus();
            }
        });

        caja.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !caja.value && cajas[i - 1]) {
                cajas[i - 1].focus();
            }
        });

        caja.addEventListener('paste', function (e) {
            const texto = e.clipboardData.getData('text').replace(/\D/g, '');

            if (!texto) return;

            e.preventDefault();

            cajas.forEach(function (c, j) {
                c.value = texto[j] || '';
            });

            if (texto.length) {
                cajas[Math.min(texto.length, cajas.length) - 1].focus();
            }
        });
    });

    // Marcar campos incorrectos
    function marcarCampo(input) {
        const campo = input.closest('.campo, .codigo');

        if (!campo) return;

        const malo = campo.classList.contains('codigo')
            ? Array.from(campo.querySelectorAll('input')).some(function (c) {
                return !c.checkValidity();
            })
            : !input.checkValidity();

        campo.classList.toggle('invalido', malo);
    }

    // Validacion de formularios
    document.querySelectorAll('form[novalidate]').forEach(function (form) {

        form.addEventListener('input', function (e) {
            if (form.classList.contains('revisado')) {
                marcarCampo(e.target);
            }
        });

        form.addEventListener('submit', function (e) {
            form.classList.add('revisado');

            form.querySelectorAll('input:not([type=hidden])').forEach(marcarCampo);

            if (!form.checkValidity()) {
                e.preventDefault();

                const primero = form.querySelector('input:invalid');

                if (primero) primero.focus();

                return;
            }

            // Mostrar mensaje de exito cuando corresponda
            if (form.dataset.exito) {
                e.preventDefault();

                const mensaje = document.getElementById('mensajeExito');
                const tarjeta = document.getElementById('tarjetaExito');

                if (mensaje && tarjeta) {
                    mensaje.textContent = form.dataset.exito;
                    tarjeta.hidden = false;

                    setTimeout(function () {
                        window.location.href = form.dataset.destino;
                    }, 2000);
                }
            }
        });
    });
});
