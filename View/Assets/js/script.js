// JavaScript de la página principal
(function () {
  'use strict';

  // Quito la pantalla de carga cuando la página ya cargó
  window.addEventListener('load', function () {
    var preloader = document.querySelector('.preloader-wrapper');
    if (!preloader) return;
    preloader.classList.add('oculto');
    setTimeout(function () { preloader.remove(); }, 400);
  });

  // Carrusel del banner
  document.addEventListener('DOMContentLoaded', function () {
    if (window.Swiper && document.querySelector('.main-swiper')) {
      new Swiper('.main-swiper', {
        speed: 500,
        loop: true,
        autoplay: { delay: 6000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true }
      });
    }
  });

  // visitante sin sesion: el catalogo y la busqueda muestran el aviso en vez de navegar
  // (el servidor igual lo manda al login si entra directo por la URL)
  document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalSesion');
    if (!modal || !window.bootstrap) return;

    var aviso = bootstrap.Modal.getOrCreateInstance(modal);

    document.addEventListener('click', function (e) {
      var enlace = e.target.closest('a[href*="Catalogo/"], a[href*="Admin/"]');
      if (!enlace) return;
      e.preventDefault();
      aviso.show();
    });

    document.querySelectorAll('form.buscador').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        aviso.show();
      });
    });
  });

  // carrito: + / - de las tarjetas, aviso (toast) y volver a abrir el carrito despues de cambiarlo
  document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.agregar-carrito .cantidad-control').forEach(function (control) {
      var input = control.querySelector('input');
      var max = parseInt(control.dataset.max, 10) || 99;

      function ajustar(valor) {
        valor = parseInt(valor, 10);
        if (isNaN(valor) || valor < 1) valor = 1;
        if (valor > max) valor = max;
        input.value = valor;
      }

      control.querySelectorAll('.btn-cantidad').forEach(function (boton) {
        boton.addEventListener('click', function () {
          ajustar(parseInt(input.value, 10) + parseInt(boton.dataset.paso, 10));
        });
      });
      input.addEventListener('change', function () { ajustar(input.value); });
    });

    if (!window.bootstrap) return;

    var panel = document.getElementById('offcanvasCart');
    if (panel && panel.dataset.abrir === '1') {
      bootstrap.Offcanvas.getOrCreateInstance(panel).show();
    }

    document.querySelectorAll('.aviso-tienda').forEach(function (toast) {
      bootstrap.Toast.getOrCreateInstance(toast).show();
    });
  });
})();
