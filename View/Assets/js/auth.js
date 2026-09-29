// JavaScript de login, registro y recuperar contrasenna

document.addEventListener('DOMContentLoaded', function () {

    // Ojito para mostrar u ocultar la contrasenna
    document.querySelectorAll('.ver-contrasena').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const input = boton.parentElement.querySelector('input');
            const icono = boton.querySelector('i');
            const mostrar = input.type === 'password';

            input.type = mostrar ? 'text' : 'password';
            icono.classList.toggle('bi-eye', !mostrar);
            icono.classList.toggle('bi-eye-slash', mostrar);
            boton.setAttribute('aria-label', mostrar ? 'Ocultar contrasenna' : 'Mostrar contrasenna');
        });
    });

    // Confirmar contrasenna: tiene que ser igual a la primera
    document.querySelectorAll('[data-igual-a]').forEach(function (confirmar) {
        const original = document.getElementById(confirmar.dataset.igualA);
        function revisar() {
            confirmar.setCustomValidity(confirmar.value === original.value ? '' : 'No coinciden');
        }
        confirmar.addEventListener('input', revisar);
        original.addEventListener('input', revisar);
    });

    // Cajitas del codigo: pasa sola a la siguiente y deja pegar el codigo completo
    const cajas = Array.from(document.querySelectorAll('.codigo input'));
    if (cajas.length) cajas[0].focus();

    cajas.forEach(function (caja, i) {
        caja.addEventListener('input', function () {
            caja.value = caja.value.replace(/\D/g, '').slice(-1);
            if (caja.value && cajas[i + 1]) cajas[i + 1].focus();
        });

        caja.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !caja.value && cajas[i - 1]) cajas[i - 1].focus();
        });

        caja.addEventListener('paste', function (e) {
            const texto = e.clipboardData.getData('text').replace(/\D/g, '');
            if (!texto) return;
            e.preventDefault();
            cajas.forEach(function (c, j) { c.value = texto[j] || ''; });
            cajas[Math.min(texto.length, cajas.length) - 1].focus();
        });
    });

    // Marco en rojo el campo que esta mal y muestro el mensaje de abajo
    function marcarCampo(input) {
        const campo = input.closest('.campo, .codigo');
        if (!campo) return;
        const malo = campo.classList.contains('codigo')
            ? Array.from(campo.querySelectorAll('input')).some(function (c) { return !c.checkValidity(); })
            : !input.checkValidity();
        campo.classList.toggle('invalido', malo);
    }

    document.querySelectorAll('form[novalidate]').forEach(function (form) {

        form.addEventListener('input', function (e) {
            if (form.classList.contains('revisado')) marcarCampo(e.target);
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

            // Si la pantalla tiene tarjeta verde en el Figma, la muestro y después cambio de página
            if (form.dataset.exito) {
                e.preventDefault();
                document.getElementById('mensajeExito').textContent = form.dataset.exito;
                document.getElementById('tarjetaExito').hidden = false;
                setTimeout(function () {
                    window.location.href = form.dataset.destino;
                }, 2000);
            }
        });
    });

    // Los botones de Google, Apple y Facebook todavia no funcionan
    const avisoSocial = document.getElementById('avisoSocial');
    document.querySelectorAll('.btn-social').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (avisoSocial) avisoSocial.hidden = false;
        });
    });
});
