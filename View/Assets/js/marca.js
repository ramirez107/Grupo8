// nombre "Los Jaulares": cada letra se levanta y se inclina cuando el cursor se acerca
(function () {
  'use strict';

  var RADIO = 140;      // px alrededor del cursor donde las letras reaccionan
  var ALTURA = 16;      // cuanto sube como maximo una letra
  var GIRO = 12;        // grados que se inclina como maximo

  document.addEventListener('DOMContentLoaded', function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var letras = [];

    // separa el texto de cada logo en <span> por letra (el lector de pantalla lee el aria-label)
    document.querySelectorAll('.marca a').forEach(function (enlace) {
      var texto = enlace.textContent.trim();
      enlace.setAttribute('aria-label', texto + ', ir al inicio');
      enlace.textContent = '';

      texto.split('').forEach(function (caracter) {
        var span = document.createElement('span');
        span.setAttribute('aria-hidden', 'true');
        if (caracter === ' ') {
          span.className = 'espacio';
          span.textContent = '\u00A0'; // espacio real, no depende del css
        } else {
          span.className = 'letra';
          span.textContent = caracter;
          letras.push(span);
        }
        enlace.appendChild(span);
      });
    });

    if (letras.length === 0) return;

    var x = -9999, y = -9999, pendiente = false;

    function mover() {
      pendiente = false;

      letras.forEach(function (letra) {
        var caja = letra.getBoundingClientRect();
        var dx = x - (caja.left + caja.width / 2);
        var dy = y - (caja.top + caja.height / 2);
        var distancia = Math.sqrt(dx * dx + dy * dy);

        if (distancia > RADIO) {
          letra.style.transform = '';
          return;
        }

        // mas cerca = mas se mueve; se inclina alejandose del cursor
        var fuerza = 1 - distancia / RADIO;
        var lado = dx > 0 ? -1 : 1;
        letra.style.transform =
          'translateY(' + (-ALTURA * fuerza).toFixed(1) + 'px) ' +
          'rotate(' + (GIRO * fuerza * lado).toFixed(1) + 'deg) ' +
          'scale(' + (1 + 0.18 * fuerza).toFixed(3) + ')';
      });
    }

    function pedirMovimiento() {
      if (!pendiente) {
        pendiente = true;
        requestAnimationFrame(mover);
      }
    }

    document.addEventListener('mousemove', function (e) {
      x = e.clientX;
      y = e.clientY;
      pedirMovimiento();
    });

    // al salir de la ventana o hacer scroll se recalcula para que no queden levantadas
    document.addEventListener('mouseleave', function () {
      x = -9999; y = -9999;
      pedirMovimiento();
    });
    window.addEventListener('scroll', pedirMovimiento, { passive: true });
  });
})();
