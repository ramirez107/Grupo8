<?php
include_once __DIR__ . '/../../layoutInterno.php';

// el panel solo lo usa el administrador
ValidarRol('Administrador');

include_once __DIR__ . '/../../../Controller/InicioController.php';
include_once __DIR__ . '/../../../Controller/PromocionController.php';

// la misma pagina maneja los 2x1 (?tipo=2x1) y los descuentos (?tipo=descuento)
$tipo = TipoPromocionPagina();
$es2x1 = $tipo == '2x1';
$tipoUrl = $es2x1 ? '2x1' : 'descuento';
$titulo = $es2x1 ? 'Promociones 2x1' : 'Descuentos';

$promociones = ConsultarPromocionesAdmin($tipo);

// si viene ?id= se carga la promocion para editarla y ver sus productos
$idEditar = (int) ($_GET['id'] ?? 0);
$promocionEditar = null;
foreach ($promociones as $promocion) {
    if ($promocion['id_promocion'] == $idEditar) {
        $promocionEditar = $promocion;
    }
}

$productosPromocion = [];
$productosDisponibles = [];
if ($promocionEditar) {
    $productosPromocion = ConsultarProductosPromocion($idEditar);
    $idsEnPromocion = array_column($productosPromocion, 'id_producto');

    foreach (ConsultarProductosParaPromocion() as $producto) {
        if ($producto['activo'] == 1 && !in_array($producto['id_producto'], $idsEnPromocion)) {
            $productosDisponibles[] = $producto;
        }
    }
}

$urlPagina = 'listado.php?tipo=' . $tipoUrl;
$urlEditar = $urlPagina . ($promocionEditar ? '&id=' . $idEditar : '');

// valor del campo: lo que el usuario escribio o lo que viene de la base
function ValorPromocion($campo, $valorBase)
{
    return Texto($_POST[$campo] ?? $valorBase);
}
?>
<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS($titulo); ?>

  <body>

    <?php MostrarHeader(false); ?>
    <?php MostrarMenuAdmin($es2x1 ? '2x1' : 'descuento'); ?>

    <main class="container-fluid py-4">

      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">Gestión de <?php echo $es2x1 ? 'promociones 2x1' : 'descuentos'; ?></h2>
        <a href="../../Catalogo/catalogo.php?oferta=<?php echo $tipoUrl; ?>" class="btn btn-outline-secondary">
          <i class="bi bi-eye"></i> Ver en el catálogo
        </a>
      </div>

      <?php MostrarMensaje(); ?>

      <div class="row g-4">

        <div class="col-lg-4">
          <div class="card p-4">
            <h3 class="h5 mb-3"><?php echo $promocionEditar ? 'Editar promoción' : 'Nueva promoción'; ?></h3>

            <form action="<?php echo $urlEditar; ?>" method="POST" id="formPromocion">
              <input type="hidden" name="idPromocion" value="<?php echo $promocionEditar ? $idEditar : 0; ?>">

              <div class="mb-3">
                <label for="txtNombre" class="form-label">Nombre</label>
                <input type="text" id="txtNombre" name="txtNombre" class="form-control"
                       value="<?php echo ValorPromocion('txtNombre', $promocionEditar['nombre'] ?? ''); ?>">
              </div>

              <?php if (!$es2x1) { ?>
                <div class="mb-3">
                  <label for="txtDescuento" class="form-label">Descuento (%)</label>
                  <input type="number" id="txtDescuento" name="txtDescuento" class="form-control" min="1" max="99" step="0.01"
                         value="<?php echo ValorPromocion('txtDescuento', isset($promocionEditar['descuento']) ? (float) $promocionEditar['descuento'] : ''); ?>">
                </div>
              <?php } ?>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label for="txtFechaInicio" class="form-label">Inicio</label>
                  <input type="date" id="txtFechaInicio" name="txtFechaInicio" class="form-control"
                         value="<?php echo ValorPromocion('txtFechaInicio', $promocionEditar['fecha_inicio'] ?? date('Y-m-d')); ?>">
                </div>
                <div class="col-6">
                  <label for="txtFechaFin" class="form-label">Fin</label>
                  <input type="date" id="txtFechaFin" name="txtFechaFin" class="form-control"
                         value="<?php echo ValorPromocion('txtFechaFin', $promocionEditar['fecha_fin'] ?? ''); ?>">
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <?php if ($promocionEditar) { ?>
                  <a href="<?php echo $urlPagina; ?>" class="btn btn-outline-secondary">Cancelar</a>
                <?php } ?>
                <button type="submit" name="btnGuardarPromocion" class="btn btn-verde">Guardar</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <?php if (!$es2x1) { ?><th class="text-center">Descuento</th><?php } ?>
                  <th class="text-center">Vigencia</th>
                  <th class="text-center">Productos</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($promociones) == 0) { ?>
                  <tr><td colspan="6" class="text-center">No hay promociones registradas</td></tr>
                <?php } ?>

                <?php foreach ($promociones as $promocion) { ?>
                  <tr<?php echo $promocion['id_promocion'] == $idEditar ? ' class="table-active"' : ''; ?>>
                    <td><?php echo Texto($promocion['nombre']); ?></td>
                    <?php if (!$es2x1) { ?>
                      <td class="text-center"><?php echo (float) $promocion['descuento']; ?> %</td>
                    <?php } ?>
                    <td class="text-center text-nowrap">
                      <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio'])); ?> –
                      <?php echo date('d/m/Y', strtotime($promocion['fecha_fin'])); ?>
                    </td>
                    <td class="text-center"><?php echo (int) $promocion['productos']; ?></td>
                    <td class="text-center">
                      <?php if ($promocion['vigente'] == 1) { ?>
                        <span class="badge text-bg-success">Vigente</span>
                      <?php } else if ($promocion['activo'] == 1) { ?>
                        <span class="badge text-bg-warning">Fuera de fecha</span>
                      <?php } else { ?>
                        <span class="badge text-bg-secondary">Inactiva</span>
                      <?php } ?>
                    </td>
                    <td class="text-center text-nowrap">
                      <a href="<?php echo $urlPagina; ?>&id=<?php echo (int) $promocion['id_promocion']; ?>" class="btn btn-sm btn-outline-primary">Editar / productos</a>

                      <form action="<?php echo $urlPagina; ?>" method="POST" class="d-inline confirmar"
                            data-pregunta="<?php echo $promocion['activo'] == 1 ? '¿Desea desactivar esta promoción?' : '¿Desea activar esta promoción?'; ?>">
                        <input type="hidden" name="idPromocion" value="<?php echo (int) $promocion['id_promocion']; ?>">
                        <input type="hidden" name="activo" value="<?php echo $promocion['activo'] == 1 ? 0 : 1; ?>">
                        <button type="submit" name="btnCambiarEstadoPromocion"
                                class="btn btn-sm <?php echo $promocion['activo'] == 1 ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                          <?php echo $promocion['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

          <?php if ($promocionEditar) { ?>
            <!-- productos de la promocion seleccionada -->
            <div class="card p-4 mt-3">
              <h3 class="h5 mb-3">Productos en «<?php echo Texto($promocionEditar['nombre']); ?>»</h3>

              <form action="<?php echo $urlEditar; ?>" method="POST" class="row g-2 mb-3">
                <input type="hidden" name="idPromocion" value="<?php echo $idEditar; ?>">
                <div class="col-md-9">
                  <label for="ddlProducto" class="visually-hidden">Producto</label>
                  <select id="ddlProducto" name="ddlProducto" class="form-select">
                    <option value="">Seleccione un producto para agregar</option>
                    <?php foreach ($productosDisponibles as $producto) { ?>
                      <option value="<?php echo (int) $producto['id_producto']; ?>">
                        <?php echo Texto($producto['nombre']); ?> (<?php echo Texto($producto['categoria']); ?>)
                      </option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <button type="submit" name="btnAgregarProducto" class="btn btn-verde w-100">
                    <i class="bi bi-plus-lg"></i> Agregar
                  </button>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <tbody>
                    <?php if (count($productosPromocion) == 0) { ?>
                      <tr><td class="text-center text-muted">Esta promoción todavía no tiene productos</td></tr>
                    <?php } ?>

                    <?php foreach ($productosPromocion as $producto) { ?>
                      <tr>
                        <td><img src="<?php echo Texto(RutaImagen($producto['imagen'])); ?>" class="imagen-tabla" alt="<?php echo Texto($producto['nombre']); ?>"></td>
                        <td>
                          <?php echo Texto($producto['nombre']); ?>
                          <?php if ($producto['activo'] != 1) { ?>
                            <span class="badge text-bg-secondary">Inactivo</span>
                          <?php } ?>
                        </td>
                        <td><?php echo Texto($producto['categoria']); ?></td>
                        <td class="text-end"><?php echo FormatoMoneda($producto['precio']); ?></td>
                        <td class="text-end">
                          <form action="<?php echo $urlEditar; ?>" method="POST" class="d-inline confirmar"
                                data-pregunta="¿Desea quitar este producto de la promoción?">
                            <input type="hidden" name="idPromocion" value="<?php echo $idEditar; ?>">
                            <input type="hidden" name="idProducto" value="<?php echo (int) $producto['id_producto']; ?>">
                            <button type="submit" name="btnQuitarProducto" class="btn btn-sm btn-outline-danger">Quitar</button>
                          </form>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php } ?>
        </div>

      </div>

    </main>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

    <script src="<?php echo Asset('js/admin.js'); ?>"></script>

  </body>
</html>
