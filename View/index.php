<?php include_once 'layoutInterno.php'; ?>

<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS('Supermercado'); ?>

  <body>

    <?php MostrarHeader(); ?>

    <!-- Banner principal -->
    <section class="py-3">
      <div class="container-fluid">
        <div class="banner-ad bg-info" style="background-image: url('Assets/images/background-pattern.jpg'); background-size: cover;">
          <div class="swiper main-swiper">
            <div class="swiper-wrapper">

              <div class="swiper-slide">
                <div class="row banner-content p-5 align-items-center">
                  <div class="content-wrapper col-md-7">
                    <div class="categories my-3">100% natural</div>
                    <h2 class="display-4">Frescura para tu hogar todos los días</h2>
                    <p>Frutas, verduras, carnes y abarrotes a precios justos, siempre cerca de vos.</p>
                    <a href="#" class="btn btn-verde btn-lg mt-3">Comprar ahora</a>
                  </div>
                  <div class="img-wrapper col-md-5 text-center">
                    <img src="Assets/images/product-thumb-1.png" class="img-fluid" alt="Botella de jugo natural">
                  </div>
                </div>
              </div>

              <div class="swiper-slide">
                <div class="row banner-content p-5 align-items-center">
                  <div class="content-wrapper col-md-7">
                    <div class="categories my-3">Ofertas de la semana</div>
                    <h2 class="display-4">2x1 en productos seleccionados</h2>
                    <p>Aprovechá los descuentos en limpieza, lácteos y bebidas mientras duren las existencias.</p>
                    <a href="#" class="btn btn-verde btn-lg mt-3">Ver ofertas</a>
                  </div>
                  <div class="img-wrapper col-md-5 text-center">
                    <img src="Assets/images/product-thumb-1.png" class="img-fluid" alt="Botella de jugo natural">
                  </div>
                </div>
              </div>

            </div>
            <div class="swiper-pagination"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Promoción de registro -->
    <section class="py-5 promo">
      <div class="container-fluid">
        <div class="bg-secondary py-5 rounded-5">
          <div class="container py-4">
            <div class="row align-items-center">
              <div class="col-md-7 p-4">
                <h2 class="section-title display-5">Obtené un <span>25% de descuento</span> en tu primera compra</h2>
                <p class="mb-0">Creá tu cuenta en Los Jaulares y recibí ofertas exclusivas, promociones 2x1 y descuentos todas las semanas.</p>
              </div>
              <div class="col-md-5 p-4 text-md-end">
                <a href="registro.php" class="btn btn-verde btn-lg me-2 mb-2">Crear cuenta</a>
                <a href="login.php" class="btn btn-contorno btn-lg mb-2">Ya tengo cuenta</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lo más buscado -->
    <section class="py-5">
      <div class="container-fluid">
        <h2 class="mb-4">Lo más buscado</h2>
        <a href="#" class="chip">Arroz</a>
        <a href="#" class="chip">Frijoles</a>
        <a href="#" class="chip">Café</a>
        <a href="#" class="chip">Leche</a>
        <a href="#" class="chip">Huevos</a>
        <a href="#" class="chip">Pollo</a>
        <a href="#" class="chip">Cereal</a>
        <a href="#" class="chip">Yogurt</a>
        <a href="#" class="chip">Detergente</a>
        <a href="#" class="chip">Suavizante</a>
        <a href="#" class="chip">Cloro</a>
        <a href="#" class="chip">Shampoo</a>
        <a href="#" class="chip">Papel higiénico</a>
        <a href="#" class="chip">Pasta dental</a>
      </div>
    </section>

    <!-- Beneficios -->
    <section class="py-5">
      <div class="container-fluid">
        <div class="row row-cols-1 row-cols-sm-3 row-cols-lg-5">
          <div class="col">
            <div class="card mb-3 border-0 beneficio">
              <div class="row">
                <div class="col-md-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.5 15a3 3 0 0 0-1.9-2.78l1.87-7a1 1 0 0 0-.18-.87A1 1 0 0 0 20.5 4H6.8l-.33-1.26A1 1 0 0 0 5.5 2h-2v2h1.23l2.48 9.26a1 1 0 0 0 1 .74H18.5a1 1 0 0 1 0 2h-13a1 1 0 0 0 0 2h1.18a3 3 0 1 0 5.64 0h2.36a3 3 0 1 0 5.82 1a2.94 2.94 0 0 0-.4-1.47A3 3 0 0 0 21.5 15Zm-3.91-3H9L7.34 6H19.2ZM9.5 20a1 1 0 1 1 1-1a1 1 0 0 1-1 1Zm8 0a1 1 0 1 1 1-1a1 1 0 0 1-1 1Z"/></svg>
                </div>
                <div class="col-md-10">
                  <div class="card-body p-0">
                    <h5>Entrega a domicilio</h5>
                    <p class="card-text">Recibí tus compras en la puerta de tu casa.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card mb-3 border-0 beneficio">
              <div class="row">
                <div class="col-md-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19.63 3.65a1 1 0 0 0-.84-.2a8 8 0 0 1-6.22-1.27a1 1 0 0 0-1.14 0a8 8 0 0 1-6.22 1.27a1 1 0 0 0-.84.2a1 1 0 0 0-.37.78v7.45a9 9 0 0 0 3.77 7.33l3.65 2.6a1 1 0 0 0 1.16 0l3.65-2.6A9 9 0 0 0 20 11.88V4.43a1 1 0 0 0-.37-.78ZM18 11.88a7 7 0 0 1-2.93 5.7L12 19.77l-3.07-2.19A7 7 0 0 1 6 11.88v-6.3a10 10 0 0 0 6-1.39a10 10 0 0 0 6 1.39Zm-4.46-2.29l-2.69 2.7l-.89-.9a1 1 0 0 0-1.42 1.42l1.6 1.6a1 1 0 0 0 1.42 0L15 11a1 1 0 0 0-1.42-1.42Z"/></svg>
                </div>
                <div class="col-md-10">
                  <div class="card-body p-0">
                    <h5>Pago 100% seguro</h5>
                    <p class="card-text">Tus datos y pagos siempre protegidos.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card mb-3 border-0 beneficio">
              <div class="row">
                <div class="col-md-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M22 5H2a1 1 0 0 0-1 1v4a3 3 0 0 0 2 2.82V22a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-9.18A3 3 0 0 0 23 10V6a1 1 0 0 0-1-1Zm-7 2h2v3a1 1 0 0 1-2 0Zm-4 0h2v3a1 1 0 0 1-2 0ZM7 7h2v3a1 1 0 0 1-2 0Zm-3 4a1 1 0 0 1-1-1V7h2v3a1 1 0 0 1-1 1Zm10 10h-4v-2a2 2 0 0 1 4 0Zm5 0h-3v-2a4 4 0 0 0-8 0v2H5v-8.18a3.17 3.17 0 0 0 1-.6a3 3 0 0 0 4 0a3 3 0 0 0 4 0a3 3 0 0 0 4 0a3.17 3.17 0 0 0 1 .6Zm2-11a1 1 0 0 1-2 0V7h2ZM4.3 3H20a1 1 0 0 0 0-2H4.3a1 1 0 0 0 0 2Z"/></svg>
                </div>
                <div class="col-md-10">
                  <div class="card-body p-0">
                    <h5>Calidad garantizada</h5>
                    <p class="card-text">Productos frescos y seleccionados cada día.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card mb-3 border-0 beneficio">
              <div class="row">
                <div class="col-md-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 8.35a3.07 3.07 0 0 0-3.54.53a3 3 0 0 0 0 4.24L11.29 16a1 1 0 0 0 1.42 0l2.83-2.83a3 3 0 0 0 0-4.24A3.07 3.07 0 0 0 12 8.35Zm2.12 3.36L12 13.83l-2.12-2.12a1 1 0 0 1 0-1.42a1 1 0 0 1 1.41 0a1 1 0 0 0 1.42 0a1 1 0 0 1 1.41 0a1 1 0 0 1 0 1.42ZM12 2A10 10 0 0 0 2 12a9.89 9.89 0 0 0 2.26 6.33l-2 2a1 1 0 0 0-.21 1.09A1 1 0 0 0 3 22h9a10 10 0 0 0 0-20Zm0 18H5.41l.93-.93a1 1 0 0 0 0-1.41A8 8 0 1 1 12 20Z"/></svg>
                </div>
                <div class="col-md-10">
                  <div class="card-body p-0">
                    <h5>Ahorro garantizado</h5>
                    <p class="card-text">Precios bajos y promociones 2x1 cada semana.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card mb-3 border-0 beneficio">
              <div class="row">
                <div class="col-md-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M18 7h-.35A3.45 3.45 0 0 0 18 5.5a3.49 3.49 0 0 0-6-2.44A3.49 3.49 0 0 0 6 5.5A3.45 3.45 0 0 0 6.35 7H6a3 3 0 0 0-3 3v2a1 1 0 0 0 1 1h1v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6h1a1 1 0 0 0 1-1v-2a3 3 0 0 0-3-3Zm-7 13H8a1 1 0 0 1-1-1v-6h4Zm0-9H5v-1a1 1 0 0 1 1-1h5Zm0-4H9.5A1.5 1.5 0 1 1 11 5.5Zm2-1.5A1.5 1.5 0 1 1 14.5 7H13ZM17 19a1 1 0 0 1-1 1h-3v-7h4Zm2-8h-6V9h5a1 1 0 0 1 1 1Z"/></svg>
                </div>
                <div class="col-md-10">
                  <div class="card-body p-0">
                    <h5>Ofertas diarias</h5>
                    <p class="card-text">Descuentos nuevos en distintas categorías.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

  </body>
</html>
