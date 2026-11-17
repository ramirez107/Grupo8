<?php
// partes que se repiten en las paginas de la tienda (encabezado, pie de pagina, css y js)
include_once __DIR__ . '/../Controller/SeguridadController.php';

function IncludeCSS($titulo)
{
    $ruta = RutaView();

    echo '
        <head>
          <title>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . ' | Los Jaulares</title>
          <meta charset="utf-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta name="format-detection" content="telephone=no">
          <meta name="description" content="Los Jaulares: supermercado con abarrotes, carnes, limpieza y ofertas todos los días.">

          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Delius&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
          <link rel="stylesheet" href="' . $ruta . 'Assets/css/style.css">
        </head>
    ';
}


function MostrarHeader($menuTienda = true)
{
    $ruta = RutaView();
    $oferta = $_GET['oferta'] ?? '';

    // filtros de la tienda, en el panel admin no se muestran
    $filtros = '';
    if ($menuTienda) {
        $filtros = '
        <nav class="filtros" aria-label="Filtros de productos">
          <a href="' . $ruta . 'Catalogo/catalogo.php" class="filtro' . ($oferta == '' ? ' activo' : '') . '">General</a>
          <a href="' . $ruta . 'Catalogo/catalogo.php?oferta=2x1" class="filtro' . ($oferta == '2x1' ? ' activo' : '') . '">2X1</a>
          <a href="' . $ruta . 'Catalogo/catalogo.php?oferta=descuento" class="filtro' . ($oferta == 'descuento' ? ' activo' : '') . '">Descuentos</a>
        </nav>
        ';
    }


    // si hay sesion se saluda al usuario y se puede cerrar sesion, si no se manda al login
    if (isset($_SESSION['nombre'])) {
        $panel = '';

        // el menu del panel solo lo ve el administrador
        if (EsAdministrador()) {
            $panel = '
              <a href="' . $ruta . 'Admin/Productos/listado.php" class="accion-encabezado">
                <i class="bi bi-speedometer2 icono-panel"></i>
                <span class="texto">Panel</span>
              </a>
            ';
        }

        $perfil = $panel . '
              <span class="accion-encabezado">
                <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#user"></use></svg>
                <span class="texto">Hola, ' . htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8') . '</span>
              </span>

              <form action="" method="POST" class="m-0">
                <button type="submit" name="btnCerrarSesion" class="accion-encabezado" aria-label="Cerrar sesión">
                  <i class="bi bi-box-arrow-right icono-panel"></i>
                  <span class="texto">Cerrar sesión</span>
                </button>
              </form>
        ';
    }
    else {
        $perfil = '
              <a href="' . $ruta . 'Inicio/login.php" class="accion-encabezado">
                <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#user"></use></svg>
                <span class="texto">Perfil</span>
              </a>
        ';
    }

    echo '
        <svg xmlns="http://www.w3.org/2000/svg" class="iconos-ocultos">
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
            <h1 class="marca"><a href="' . $ruta . 'Inicio/home.php">Los Jaulares</a></h1>

            <div class="encabezado-barra">
              <form class="buscador" action="' . $ruta . 'Catalogo/catalogo.php" method="get" role="search">
                <input type="search" name="buscar" placeholder="Barra de búsqueda" aria-label="Buscar productos"
                       value="' . Texto($_GET['buscar'] ?? '') . '">
                <button type="submit" aria-label="Buscar">
                  <svg width="22" height="22" viewBox="0 0 24 24"><use xlink:href="#search"></use></svg>
                </button>
              </form>

' . $perfil . '
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

    ' . $filtros . '
    ';
}

// menu del panel admin, $activo = productos o categorias
function MostrarMenuAdmin($activo)
{
    $ruta = RutaView();

    echo '
        <nav class="filtros" aria-label="Menú del panel">
          <a href="' . $ruta . 'Admin/Productos/listado.php" class="filtro' . ($activo == 'productos' ? ' activo' : '') . '">Productos</a>
          <a href="' . $ruta . 'Admin/Categorias/listado.php" class="filtro' . ($activo == 'categorias' ? ' activo' : '') . '">Categorías</a>
        </nav>
    ';
}

// tarjeta de bootstrap de un producto: imagen, nombre, precio en colones y oferta
function MostrarTarjetaProducto($producto)
{
    $precio = '';
    if ($producto['precio_final'] < $producto['precio']) {
        $precio = '<del class="text-muted me-2">' . FormatoMoneda($producto['precio']) . '</del>';
    }
    $precio .= '<strong>' . FormatoMoneda($producto['precio_final']) . '</strong>';

    $etiqueta = '';
    if ($producto['es_2x1'] == 1) {
        $etiqueta = '<span class="badge etiqueta-oferta">2x1</span>';
    }
    else if ($producto['precio_final'] < $producto['precio']) {
        $etiqueta = '<span class="badge etiqueta-oferta">Descuento</span>';
    }

    echo '
        <div class="card h-100 tarjeta-producto">
          ' . $etiqueta . '
          <img src="' . Texto(RutaImagen($producto['imagen'])) . '" class="card-img-top" alt="' . Texto($producto['nombre']) . '">
          <div class="card-body">
            <h3 class="card-title h6">' . Texto($producto['nombre']) . '</h3>
            <p class="card-text mb-0">' . $precio . '</p>
          </div>
        </div>
    ';
}

// alerta de bootstrap con el mensaje que deja el controlador
function MostrarMensaje()
{
    if (isset($_POST["mensaje"])) {
        echo '
            <div class="alert alert-' . Texto($_POST["tipoMensaje"] ?? 'secondary') . '" role="alert">
                ' . Texto($_POST["mensaje"]) . '
            </div>
        ';
    }
}


function MostrarFooter()
{
    $ruta = RutaView();

    echo '
        <!-- Pie de página -->
        <footer class="pie">
          <div class="container-fluid">
            <div class="row g-4">

              <div class="col-lg-4 col-md-6">
                <p class="marca">Los Jaulares</p>
                <p>Tu supermercado de confianza, con productos frescos y ofertas todos los días.</p>
              </div>

              <div class="col-lg-4 col-md-6">
                <h5>Tienda</h5>
                <ul class="menu-list list-unstyled">
                  <li><a href="' . $ruta . 'Catalogo/catalogo.php">Catálogo</a></li>
                  <li><a href="' . $ruta . 'Catalogo/catalogo.php?oferta=2x1">Productos 2x1</a></li>
                  <li><a href="' . $ruta . 'Catalogo/catalogo.php?oferta=descuento">Descuentos</a></li>
                </ul>
              </div>

              <div class="col-lg-4 col-md-6">
                <h5>Mi cuenta</h5>
                <ul class="menu-list list-unstyled">
                  <li><a href="' . $ruta . 'Inicio/login.php">Iniciar sesión</a></li>
                  <li><a href="' . $ruta . 'Inicio/registro.php">Crear cuenta</a></li>
                  <li><a href="' . $ruta . 'Inicio/recuperar.php">Recuperar acceso</a></li>
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
    $ruta = RutaView();

    echo '
        <script src="https://cdn.jsdelivr.net/npm/jquery@4.0.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="' . $ruta . 'Assets/js/script.js"></script>
    ';
}
