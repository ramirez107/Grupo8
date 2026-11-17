<?php
include_once __DIR__ . '/../../layoutInterno.php';
include_once __DIR__ . '/../../../Controller/InicioController.php';
include_once __DIR__ . '/../../../Controller/CategoriaController.php';

$categorias = ConsultarCategoriasAdmin();

// si viene ?id= se carga la categoria en el formulario para editarla
$idEditar = (int) ($_GET['id'] ?? 0);
$categoriaEditar = null;
foreach ($categorias as $categoria) {
    if ($categoria['id_categoria'] == $idEditar) {
        $categoriaEditar = $categoria;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS('Categorías'); ?>

  <body>

    <?php MostrarHeader(false); ?>
    <?php MostrarMenuAdmin('categorias'); ?>

    <main class="container-fluid py-4">

      <h2 class="mb-3">Gestión de categorías</h2>

      <?php MostrarMensaje(); ?>

      <div class="row g-4">

        <div class="col-lg-4">
          <div class="card p-4">
            <h3 class="h5 mb-3"><?php echo $categoriaEditar ? 'Editar categoría' : 'Nueva categoría'; ?></h3>

            <form action="listado.php" method="POST" id="formCategoria">
              <input type="hidden" name="idCategoria" value="<?php echo $categoriaEditar ? (int) $categoriaEditar['id_categoria'] : 0; ?>">

              <div class="mb-3">
                <label for="txtNombre" class="form-label">Nombre</label>
                <input type="text" id="txtNombre" name="txtNombre" class="form-control"
                       value="<?php echo Texto($_POST['txtNombre'] ?? ($categoriaEditar['nombre'] ?? '')); ?>">
              </div>

              <div class="mb-3">
                <label for="txtDescripcion" class="form-label">Descripción</label>
                <textarea id="txtDescripcion" name="txtDescripcion" class="form-control" rows="3"><?php echo Texto($_POST['txtDescripcion'] ?? ($categoriaEditar['descripcion'] ?? '')); ?></textarea>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <?php if ($categoriaEditar) { ?>
                  <a href="listado.php" class="btn btn-outline-secondary">Cancelar</a>
                <?php } ?>
                <button type="submit" name="btnGuardarCategoria" class="btn btn-verde">Guardar</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th class="text-center">Productos</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categorias as $categoria) { ?>
                  <tr>
                    <td><?php echo Texto($categoria['nombre']); ?></td>
                    <td><?php echo Texto($categoria['descripcion']); ?></td>
                    <td class="text-center"><?php echo (int) $categoria['total_productos']; ?></td>
                    <td class="text-center">
                      <?php if ($categoria['activo'] == 1) { ?>
                        <span class="badge text-bg-success">Activa</span>
                      <?php } else { ?>
                        <span class="badge text-bg-secondary">Inactiva</span>
                      <?php } ?>
                    </td>
                    <td class="text-center text-nowrap">
                      <a href="listado.php?id=<?php echo (int) $categoria['id_categoria']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>

                      <form action="listado.php" method="POST" class="d-inline confirmar"
                            data-pregunta="<?php echo $categoria['activo'] == 1 ? '¿Desea desactivar esta categoría?' : '¿Desea activar esta categoría?'; ?>">
                        <input type="hidden" name="idCategoria" value="<?php echo (int) $categoria['id_categoria']; ?>">
                        <input type="hidden" name="activo" value="<?php echo $categoria['activo'] == 1 ? 0 : 1; ?>">
                        <button type="submit" name="btnCambiarEstadoCategoria"
                                class="btn btn-sm <?php echo $categoria['activo'] == 1 ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                          <?php echo $categoria['activo'] == 1 ? 'Desactivar' : 'Activar'; ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </main>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

    <script src="../../Assets/js/admin.js"></script>

  </body>
</html>
