<?php
// partes que se repiten en las paginas de la tienda (encabezado, pie de pagina, css y js)
include_once __DIR__ . '/../Controller/SeguridadController.php';
// procesa agregar / cambiar cantidad / quitar del carrito antes de mostrar la pagina
include_once __DIR__ . '/../Controller/CarritoController.php';

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
          <link href="https://fonts.googleapis.com/css2?family=Delius&family=Fredoka:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
          <link rel="stylesheet" href="' . Asset('css/style.css') . '">
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
    $avisoSesion = '';
    $destinoCarrito = '#offcanvasCart';

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

        // el cliente entra a su cuenta (datos y pedidos) desde el saludo
        $saludo = '
                <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#user"></use></svg>
                <span class="texto">Hola, ' . Texto($_SESSION['nombre']) . '</span>';
        $perfil = $panel . (EsAdministrador()
            ? '<span class="accion-encabezado">' . $saludo . '</span>'
            : '<a href="' . $ruta . 'Cuenta/perfil.php" class="accion-encabezado" title="Mi cuenta y pedidos">' . $saludo . '</a>') . '

              <form action="" method="POST" class="m-0">
                <button type="submit" name="btnCerrarSesion" class="accion-encabezado" aria-label="Cerrar sesión">
                  <i class="bi bi-box-arrow-right icono-panel"></i>
                  <span class="texto">Cerrar sesión</span>
                </button>
              </form>
        ';
    }
    else {
        // aviso para el visitante sin sesion: lo abren el carrito, el catalogo y la busqueda (script.js)
        $avisoSesion = '
        <div class="modal fade" id="modalSesion" tabindex="-1" aria-labelledby="tituloModalSesion" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-3">
              <div class="modal-header border-0 pb-0">
                <h5 class="modal-title w-100" id="tituloModalSesion">
                  <i class="bi bi-lock-fill me-1"></i> Debe iniciar sesión
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <p class="mb-0">Para acceder al catálogo, al carrito y a las compras debe iniciar sesión o crear una cuenta.</p>
              </div>
              <div class="modal-footer border-0 justify-content-center">
                <a href="' . $ruta . 'Inicio/login.php" class="btn btn-verde">Iniciar sesión</a>
                <a href="' . $ruta . 'Inicio/registro.php" class="btn btn-outline-secondary">Crear cuenta</a>
              </div>
            </div>
          </div>
        </div>
        ';

        $destinoCarrito = '#modalSesion';

        $perfil = '
              <a href="' . $ruta . 'Inicio/login.php" class="accion-encabezado">
                <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#user"></use></svg>
                <span class="texto">Perfil</span>
              </a>
        ';
    }

    // el administrador no compra, no se le muestra el carrito
    $botonCarrito = '';
    $panelCarrito = '';
    if (!EsAdministrador()) {
        $carrito = ConsultarCarrito();
        $articulos = array_sum(array_column($carrito, 'cantidad'));

        $botonCarrito = '
              <button class="accion-encabezado" type="button" data-bs-toggle="' . ($avisoSesion == '' ? 'offcanvas' : 'modal') . '" data-bs-target="' . $destinoCarrito . '" aria-controls="' . substr($destinoCarrito, 1) . '">
                <span class="icono-carrito">
                  <svg width="30" height="30" viewBox="0 0 24 24"><use xlink:href="#cart"></use></svg>
                  <span class="contador-carrito">' . $articulos . '</span>
                </span>
                <span class="texto">Carrito</span>
              </button>
        ';

        if ($avisoSesion == '') {
            $abrir = !empty($_SESSION['abrirCarrito']) ? ' data-abrir="1"' : '';
            unset($_SESSION['abrirCarrito']);

            $panelCarrito = '
        <!-- Carrito -->
        <div class="offcanvas offcanvas-end panel-carrito" tabindex="-1" id="offcanvasCart" aria-labelledby="tituloCarrito"' . $abrir . '>
          <div class="offcanvas-header">
            <h4 id="tituloCarrito" class="offcanvas-title mb-0">
              <i class="bi bi-cart3"></i> Tu carrito
              <span class="badge rounded-pill bg-verde ms-1">' . $articulos . '</span>
            </h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
          </div>
          <div class="offcanvas-body d-flex flex-column">
            ' . HtmlCarrito($carrito, $ruta) . '
          </div>
        </div>
            ';
        }
    }

    // aviso corto despues de agregar al carrito u otra accion
    $toast = '';
    if (isset($_SESSION['avisoTienda'])) {
        $aviso = $_SESSION['avisoTienda'];
        unset($_SESSION['avisoTienda']);
        $icono = $aviso['tipo'] == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        $toast = '
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
          <div class="toast aviso-tienda aviso-' . Texto($aviso['tipo']) . '" role="status" aria-live="polite" data-bs-delay="3500">
            <div class="d-flex">
              <div class="toast-body"><i class="bi ' . $icono . ' me-2"></i>' . Texto($aviso['mensaje']) . '</div>
              <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
          </div>
        </div>';
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

' . $avisoSesion . '

' . $panelCarrito . '
' . $toast . '

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
' . $botonCarrito . '
            </div>
          </div>
        </header>

    ' . $filtros . '
    ';
}

// contenido del carrito: productos con + / -, total y finalizar compra
function HtmlCarrito($carrito, $ruta)
{
    if (count($carrito) == 0) {
        return '
            <div class="text-center my-auto py-5 carrito-vacio">
              <i class="bi bi-cart-x"></i>
              <p class="mt-3 mb-4">Todavía no agregaste productos.</p>
              <a href="' . $ruta . 'Catalogo/catalogo.php" class="btn btn-verde">Ver catálogo</a>
            </div>';
    }

    $lineas = '';
    foreach ($carrito as $item) {
        $id = (int) $item['id_detalle_carrito'];
        $cantidad = (int) $item['cantidad'];
        $tope = $cantidad >= (int) $item['stock'];
        $ahorro = $item['descuento_2x1'] > 0
            ? '<small class="d-block text-success">2x1: ahorrás ' . FormatoMoneda($item['descuento_2x1']) . '</small>'
            : '';

        $lineas .= '
            <li class="item-carrito">
              <img src="' . Texto(RutaImagen($item['imagen'])) . '" alt="">
              <div class="flex-grow-1">
                <p class="mb-1 fw-semibold">' . Texto($item['nombre']) . '</p>
                <small class="text-muted">' . FormatoMoneda($item['precio_unitario']) . ' c/u</small>
                ' . $ahorro . '
                <div class="d-flex align-items-center justify-content-between mt-2">
                  <form method="POST" class="cantidad-control">
                    <input type="hidden" name="idDetalle" value="' . $id . '">
                    <button type="submit" name="btnCantidadCarrito" value="1" class="btn-cantidad"
                            onclick="this.form.cantidad.value=' . ($cantidad - 1) . '" aria-label="Quitar uno">
                      <i class="bi ' . ($cantidad == 1 ? 'bi-trash3' : 'bi-dash') . '"></i>
                    </button>
                    <input type="hidden" name="cantidad" value="' . $cantidad . '">
                    <span class="valor">' . $cantidad . '</span>
                    <button type="submit" name="btnCantidadCarrito" value="1" class="btn-cantidad"
                            onclick="this.form.cantidad.value=' . ($cantidad + 1) . '" aria-label="Agregar uno"' . ($tope ? ' disabled title="No hay más en stock"' : '') . '>
                      <i class="bi bi-plus"></i>
                    </button>
                  </form>
                  <strong>' . FormatoMoneda($item['subtotal']) . '</strong>
                </div>
              </div>
              <form method="POST" class="ms-1">
                <input type="hidden" name="idDetalle" value="' . $id . '">
                <button type="submit" name="btnQuitarCarrito" class="btn-quitar" aria-label="Quitar ' . Texto($item['nombre']) . '">
                  <i class="bi bi-x-lg"></i>
                </button>
              </form>
            </li>';
    }

    return '
            <ul class="list-unstyled mb-3 lista-carrito">' . $lineas . '</ul>
            <div class="mt-auto pt-3 border-top">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fs-5">Total</span>
                <strong class="fs-4">' . FormatoMoneda($carrito[0]['total']) . '</strong>
              </div>
              <a href="' . $ruta . 'Carrito/pagar.php" class="w-100 btn btn-verde btn-lg">Finalizar compra</a>
              <button class="w-100 btn btn-link mt-2 text-muted" type="button" data-bs-dismiss="offcanvas">Seguir comprando</button>
            </div>';
}

// menu del panel admin, $activo = productos, categorias, 2x1, descuento, pedidos o ventas
function MostrarMenuAdmin($activo)
{
    $ruta = RutaView();

    echo '
        <nav class="filtros" aria-label="Menú del panel">
          <a href="' . $ruta . 'Admin/Productos/listado.php" class="filtro' . ($activo == 'productos' ? ' activo' : '') . '">Productos</a>
          <a href="' . $ruta . 'Admin/Categorias/listado.php" class="filtro' . ($activo == 'categorias' ? ' activo' : '') . '">Categorías</a>
          <a href="' . $ruta . 'Admin/Promociones/listado.php?tipo=2x1" class="filtro' . ($activo == '2x1' ? ' activo' : '') . '">2X1</a>
          <a href="' . $ruta . 'Admin/Promociones/listado.php?tipo=descuento" class="filtro' . ($activo == 'descuento' ? ' activo' : '') . '">Descuentos</a>
          <a href="' . $ruta . 'Admin/Pedidos/listado.php" class="filtro' . ($activo == 'pedidos' ? ' activo' : '') . '">Pedidos</a>
          <a href="' . $ruta . 'Admin/Ventas/reporte.php" class="filtro' . ($activo == 'ventas' ? ' activo' : '') . '">Ventas</a>
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

    // parte de abajo: el cliente elige cantidad y agrega, el visitante ve el aviso de sesion
    $id = (int) $producto['id_producto'];
    $stock = isset($producto['stock']) ? (int) $producto['stock'] : 99;

    if (EsAdministrador()) {
        $acciones = '
            <a href="' . RutaView() . 'Admin/Productos/editar.php?id=' . $id . '" class="btn btn-sm btn-outline-secondary w-100">
              <i class="bi bi-pencil"></i> Editar producto
            </a>';
    }
    else if ($stock <= 0) {
        $acciones = '<span class="badge text-bg-secondary w-100 py-2">Agotado</span>';
    }
    else if (!isset($_SESSION['idUsuario'])) {
        $acciones = '
            <button type="button" class="btn btn-verde btn-agregar w-100" data-bs-toggle="modal" data-bs-target="#modalSesion">
              <i class="bi bi-cart-plus"></i> Agregar
            </button>';
    }
    else {
        $acciones = '
            <form method="POST" class="agregar-carrito">
              <input type="hidden" name="idProducto" value="' . $id . '">
              <input type="hidden" name="nombreProducto" value="' . Texto($producto['nombre']) . '">
              <div class="cantidad-control" data-max="' . $stock . '">
                <button type="button" class="btn-cantidad" data-paso="-1" aria-label="Menos"><i class="bi bi-dash"></i></button>
                <input type="number" name="cantidad" value="1" min="1" max="' . $stock . '" aria-label="Cantidad de ' . Texto($producto['nombre']) . '">
                <button type="button" class="btn-cantidad" data-paso="1" aria-label="Más"><i class="bi bi-plus"></i></button>
              </div>
              <button type="submit" name="btnAgregarCarrito" class="btn btn-verde btn-agregar" aria-label="Agregar ' . Texto($producto['nombre']) . ' al carrito">
                <i class="bi bi-cart-plus"></i><span class="d-none d-xl-inline"> Agregar</span>
              </button>
            </form>';
    }

    echo '
        <div class="card h-100 tarjeta-producto">
          ' . $etiqueta . '
          <img src="' . Texto(RutaImagen($producto['imagen'])) . '" class="card-img-top" alt="' . Texto($producto['nombre']) . '">
          <div class="card-body d-flex flex-column">
            <h3 class="card-title h6">' . Texto($producto['nombre']) . '</h3>
            <p class="card-text mb-3">' . $precio . '</p>
            <div class="mt-auto">' . $acciones . '</div>
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
                <p class="marca"><a href="' . $ruta . 'Inicio/home.php">Los Jaulares</a></p>
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
        <script src="' . Asset('js/script.js') . '"></script>
        <script src="' . Asset('js/marca.js') . '"></script>
    ';
}
