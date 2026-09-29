<?php
// Partes que se repiten en las paginas de la tienda (encabezado, pie de pagina, CSS y JS)

function IncludeCSS($titulo)
{
    echo '
        <head>
          <title>' . $titulo . ' | Los Jaulares</title>
          <meta charset="utf-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta name="format-detection" content="telephone=no">
          <meta name="description" content="Los Jaulares: supermercado con abarrotes, carnes, limpieza y ofertas todos los días.">

          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Delius&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

          <link rel="stylesheet" href="Assets/css/style.css">
        </head>
    ';
}

function MostrarHeader()
{
    echo '
        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
          <defs>
            <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 24 24">
              <path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/>
            </symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="search" viewBox="0 0 24 24">
              <path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/>
            </symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="user" viewBox="0 0 24 24">
              <path fill="currentColor" d="M15.71 12.71a6 6 0 1 0-7.42 0a10 10 0 0 0-6.22 8.18a1 1 0 0 0 2 .22a8 8 0 0 1 15.9 0a1 1 0 0 0 1 .89h.11a1 1 0 0 0 .88-1.1a10 10 0 0 0-6.25-8.19ZM12 12a4 4 0 1 1 4-4a4 4 0 0 1-4 4Z"/>
            </symbol>
          </defs>
        </svg>

        <div class="preloader-wrapper">
          <div class="preloader"></div>
        </div>

        <!-- Carrito -->
        <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasCart" aria-labelledby="tituloCarrito">
          <div class="offcanvas-header justify-content-center">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
          </div>
          <div class="offcanvas-body">
            <h4 id="tituloCarrito" class="d-flex justify-content-between align-items-center mb-3">
              <span>Tu carrito</span>
              <span class="badge bg-primary rounded-pill">0</span>
            </h4>
            <p class="mb-4">Todavía no agregaste productos.</p>
            <button class="w-100 btn btn-verde" type="button" data-bs-dismiss="offcanvas">Seguir comprando</button>
          </div>
        </div>

        <!-- Encabezado -->
        <header class="encabezado-principal">
          <div class="container-fluid">
            <h1 class="marca"><a href="index.php">Los Jaulares</a></h1>

            <div class="encabezado-barra">
              <form class="buscador" action="index.php" method="get" role="search">
                <input type="search" name="buscar" placeholder="Barra de búsqueda" aria-label="Buscar productos">
                <button type="submit" aria-label="Buscar">
                  <svg width="22" height="22" viewBox="0 0 24 24"><use xlink:href="#search"></use></svg>
                </button>
              </form>

              <a href="login.php" class="accion-encabezado">
                <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#user"></use></svg>
                <span class="texto">Perfil</span>
              </a>

              <button class="accion-encabezado" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                <span class="icono-carrito">
                  <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#cart"></use></svg>
                  <span class="contador-carrito">0</span>
                </span>
                <span class="texto">Carrito</span>
              </button>
            </div>
          </div>
        </header>

        <nav class="filtros" aria-label="Filtros de productos">
          <a href="#" class="filtro activo" aria-current="page">General</a>
          <a href="#" class="filtro">2X1</a>
          <a href="#" class="filtro">Descuentos</a>
        </nav>
    ';
}

function MostrarFooter()
{
    echo '
        <!-- Pie de página -->
        <footer class="pie">
          <div class="container-fluid">
            <div class="row g-4">

              <div class="col-lg-4 col-md-6">
                <p class="marca">Los Jaulares</p>
                <p>Tu supermercado de confianza, con productos frescos y ofertas todos los días.</p>
                <ul class="redes d-flex list-unstyled gap-2 mt-3">
                  <li>
                    <a href="#" aria-label="Facebook">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M15.12 5.32H17V2.14A26.11 26.11 0 0 0 14.26 2c-2.72 0-4.58 1.66-4.58 4.7v2.62H6.61v3.56h3.07V22h3.68v-9.12h3.06l.46-3.56h-3.52V7.05c0-1.05.28-1.73 1.76-1.73Z"/></svg>
                    </a>
                  </li>
                  <li>
                    <a href="#" aria-label="Instagram">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M17.34 5.46a1.2 1.2 0 1 0 1.2 1.2a1.2 1.2 0 0 0-1.2-1.2Zm4.6 2.42a7.59 7.59 0 0 0-.46-2.43a4.94 4.94 0 0 0-1.16-1.77a4.7 4.7 0 0 0-1.77-1.15a7.3 7.3 0 0 0-2.43-.47C15.06 2 14.72 2 12 2s-3.06 0-4.12.06a7.3 7.3 0 0 0-2.43.47a4.78 4.78 0 0 0-1.77 1.15a4.7 4.7 0 0 0-1.15 1.77a7.3 7.3 0 0 0-.47 2.43C2 8.94 2 9.28 2 12s0 3.06.06 4.12a7.3 7.3 0 0 0 .47 2.43a4.7 4.7 0 0 0 1.15 1.77a4.78 4.78 0 0 0 1.77 1.15a7.3 7.3 0 0 0 2.43.47C8.94 22 9.28 22 12 22s3.06 0 4.12-.06a7.3 7.3 0 0 0 2.43-.47a4.7 4.7 0 0 0 1.77-1.15a4.85 4.85 0 0 0 1.16-1.77a7.59 7.59 0 0 0 .46-2.43c0-1.06.06-1.4.06-4.12s0-3.06-.06-4.12ZM20.14 16a5.61 5.61 0 0 1-.34 1.86a3.06 3.06 0 0 1-.75 1.15a3.19 3.19 0 0 1-1.15.75a5.61 5.61 0 0 1-1.86.34c-1 .05-1.37.06-4 .06s-3 0-4-.06a5.73 5.73 0 0 1-1.94-.3a3.27 3.27 0 0 1-1.1-.75a3 3 0 0 1-.74-1.15a5.54 5.54 0 0 1-.4-1.9c0-1-.06-1.37-.06-4s0-3 .06-4a5.54 5.54 0 0 1 .35-1.9A3 3 0 0 1 5 5a3.14 3.14 0 0 1 1.1-.8A5.73 5.73 0 0 1 8 3.86c1 0 1.37-.06 4-.06s3 0 4 .06a5.61 5.61 0 0 1 1.86.34a3.06 3.06 0 0 1 1.19.8a3.06 3.06 0 0 1 .75 1.1a5.61 5.61 0 0 1 .34 1.9c.05 1 .06 1.37.06 4s-.01 3-.06 4ZM12 6.87A5.13 5.13 0 1 0 17.14 12A5.12 5.12 0 0 0 12 6.87Zm0 8.46A3.33 3.33 0 1 1 15.33 12A3.33 3.33 0 0 1 12 15.33Z"/></svg>
                    </a>
                  </li>
                </ul>
              </div>

              <div class="col-lg-2 col-md-3 col-6">
                <h5>Los Jaulares</h5>
                <ul class="menu-list list-unstyled">
                  <li><a href="#">Sobre nosotros</a></li>
                  <li><a href="#">Nuestras sucursales</a></li>
                  <li><a href="#">Trabajá con nosotros</a></li>
                </ul>
              </div>

              <div class="col-lg-3 col-md-3 col-6">
                <h5>Servicio al cliente</h5>
                <ul class="menu-list list-unstyled">
                  <li><a href="#">Preguntas frecuentes</a></li>
                  <li><a href="#">Contacto</a></li>
                  <li><a href="#">Política de privacidad</a></li>
                  <li><a href="#">Devoluciones</a></li>
                </ul>
              </div>

              <div class="col-lg-3 col-md-6">
                <h5>Mi cuenta</h5>
                <ul class="menu-list list-unstyled">
                  <li><a href="login.php">Iniciar sesión</a></li>
                  <li><a href="registro.php">Crear cuenta</a></li>
                  <li><a href="recuperar.php">Recuperar acceso</a></li>
                </ul>
              </div>

            </div>

            <div class="pie-inferior d-md-flex justify-content-between">
              <p class="mb-1">© ' . date('Y') . ' Los Jaulares. Todos los derechos reservados.</p>
              <p class="mb-0">Plantilla base: <a href="https://templatesjungle.com/">TemplatesJungle</a></p>
            </div>
          </div>
        </footer>
    ';
}

function IncludeJS()
{
    echo '
        <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
        <script src="Assets/js/script.js"></script>
    ';
}
