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
})();
