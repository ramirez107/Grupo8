<?php
include_once __DIR__ . '/../../layoutInterno.php';

// el panel solo lo usa el administrador
ValidarRol('Administrador');

include_once __DIR__ . '/../../../Controller/InicioController.php';
include_once __DIR__ . '/../../../Controller/ProductoController.php';

$productos = ConsultarProductosAdmin();
?>
<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS('Productos'); ?>

  <body>

    <?php MostrarHeader(false); ?>
    <?php MostrarMenuAdmin('productos'); ?>

    <main class="container-fluid py-4">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Gestión de productos</h2>
        <a href="registro.php" class="btn btn-verde"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
      </div>

      <?php MostrarMensaje(); ?>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th class="text-end">Precio</th>
              <th class="text-center">Stock</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($productos) == 0) { ?>
              <tr><td colspan="7" class="text-center">No hay productos registrados</td></tr>
            <?php } ?>

            <?php foreach ($productos as $producto) { ?>
              <tr>
                <td><img src="<?php echo Texto(RutaImagen($producto['imagen'])); ?>" class="imagen-tabla" alt="<?php echo Texto($producto['nombre']); ?>"></td>
                <td><?php echo Texto($producto['nombre']); ?></td>
                <td><?php echo Texto($producto['categoria']); ?></td>
                <td class="text-end"><?php echo FormatoMoneda($producto['precio']); ?></td>
                <td class="text-center">
                  <?php echo (int) $producto['stock']; ?>
                  <?php if ($producto['stock_bajo'] == 1) { ?>
                    <span class="badge text-bg-warning">Bajo</span>
                  <?php } ?>
                </td>
                <td class="text-center">
                  <?php if ($producto['activo'] == 1) { ?>
                    <span class="badge text-bg-success">Activo</span>
                  <?php } else { ?>
                    <span class="badge text-bg-secondary">Inactivo</span>
                  <?php } ?>
                </td>
                <td class="text-center text-nowrap">
                  <a href="editar.php?id=<?php echo (int) $producto['id_producto']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>

                  <form action="" method="POST" class="d-inline confirmar"
                        data-pregunta="<?php echo $producto['activo'] == 1 ? '¿Desea desactivar este producto?' : '¿Desea activar este producto?'; ?>">
                    <input type="hidden" name="idProducto" value="<?php echo (int) $producto['id_producto']; ?>">
                    <input type="hidden" name="activo" value="<?php echo $producto['activo'] == 1 ? 0 : 1; ?>">
                    <button type="submit" name="btnCambiarEstadoProducto"
                            class="btn btn-sm <?php echo $producto['activo'] == 1 ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                      <?php echo $producto['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

    </main>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

    <script src="<?php echo Asset('js/admin.js'); ?>"></script>

  </body>
</html>
