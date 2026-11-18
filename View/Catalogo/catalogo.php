<?php
include_once __DIR__ . '/../layoutInterno.php';

// el catalogo solo se ve con sesion iniciada
ValidarSesion();

include_once __DIR__ . '/../../Controller/InicioController.php';
include_once __DIR__ . '/../../Controller/CatalogoController.php';

$categorias = ConsultarCategoriasCatalogo();
$productos = ConsultarProductosCatalogo();

$buscar = $_GET['buscar'] ?? '';
$idCategoria = (int) ($_GET['categoria'] ?? 0);
$orden = $_GET['orden'] ?? '';
$oferta = $_GET['oferta'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS('Catálogo'); ?>

  <body>

    <?php MostrarHeader(); ?>

    <main class="container-fluid py-4">
      <div class="row g-4">

        <!-- Categorías activas (sp_categoria_listar_activas) -->
        <aside class="col-lg-3">
          <h2 class="h5 mb-3">Categorías</h2>
          <div class="list-group lista-categorias">
            <a href="catalogo.php" class="list-group-item list-group-item-action<?php echo $idCategoria == 0 ? ' active' : ''; ?>">Todas</a>
            <?php foreach ($categorias as $categoria) { ?>
              <a href="catalogo.php?categoria=<?php echo (int) $categoria['id_categoria']; ?>"
                 class="list-group-item list-group-item-action<?php echo $idCategoria == $categoria['id_categoria'] ? ' active' : ''; ?>">
                <?php echo Texto($categoria['nombre']); ?>
              </a>
            <?php } ?>
          </div>
        </aside>

        <section class="col-lg-9">

          <?php if (EsAdministrador() && ($oferta == '2x1' || $oferta == 'descuento')) { ?>
            <!-- el admin puede ir directo a modificar las promociones que esta viendo -->
            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2" role="alert">
              <span><i class="bi bi-gear"></i> Estás viendo los productos <?php echo $oferta == '2x1' ? 'en 2x1' : 'con descuento'; ?>.</span>
              <a href="../Admin/Promociones/listado.php?tipo=<?php echo $oferta; ?>" class="btn btn-sm btn-verde">
                Modificar <?php echo $oferta == '2x1' ? 'promociones 2x1' : 'descuentos'; ?>
              </a>
            </div>
          <?php } ?>

          <!-- Buscador: nombre, categoría y orden por precio (sp_producto_buscar) -->
          <form action="catalogo.php" method="GET" id="formBuscar" class="row g-2 mb-4">
            <?php if ($oferta != '') { ?>
              <input type="hidden" name="oferta" value="<?php echo Texto($oferta); ?>">
            <?php } ?>
            <div class="col-md-5">
              <label for="txtBuscar" class="form-label">Producto</label>
              <input type="search" id="txtBuscar" name="buscar" class="form-control"
                     placeholder="Buscar por nombre" value="<?php echo Texto($buscar); ?>">
            </div>
            <div class="col-md-3">
              <label for="ddlCategoria" class="form-label">Categoría</label>
              <select id="ddlCategoria" name="categoria" class="form-select">
                <option value="0">Todas</option>
                <?php foreach ($categorias as $categoria) { ?>
                  <option value="<?php echo (int) $categoria['id_categoria']; ?>"
                    <?php echo $idCategoria == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                    <?php echo Texto($categoria['nombre']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-2">
              <label for="ddlOrden" class="form-label">Precio</label>
              <select id="ddlOrden" name="orden" class="form-select">
                <option value="">Sin orden</option>
                <option value="ASC" <?php echo $orden == 'ASC' ? 'selected' : ''; ?>>Menor a mayor</option>
                <option value="DESC" <?php echo $orden == 'DESC' ? 'selected' : ''; ?>>Mayor a menor</option>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-verde w-100">Buscar</button>
            </div>
          </form>

          <?php if (count($productos) == 0) { ?>
            <div class="alert alert-warning" role="alert">No se encontraron productos</div>
          <?php } else { ?>
            <p class="text-muted"><?php echo count($productos); ?> productos</p>
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
              <?php foreach ($productos as $producto) { ?>
                <div class="col">
                  <?php MostrarTarjetaProducto($producto); ?>
                </div>
              <?php } ?>
            </div>
          <?php } ?>

        </section>
      </div>
    </main>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

    <script src="<?php echo Asset('js/catalogo.js'); ?>"></script>

  </body>
</html>
