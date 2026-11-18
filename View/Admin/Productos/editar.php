<?php
include_once __DIR__ . '/../../layoutInterno.php';

// el panel solo lo usa el administrador
ValidarRol('Administrador');

include_once __DIR__ . '/../../../Controller/InicioController.php';
include_once __DIR__ . '/../../../Controller/ProductoController.php';

$idProducto = (int) ($_GET['id'] ?? $_POST['idProducto'] ?? 0);
$categorias = ConsultarCategoriasAdmin();
$producto = ConsultarProducto($idProducto);

// si el producto no existe vuelve al listado
if (!$producto) {
    header("Location: listado.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS('Editar producto'); ?>

  <body>

    <?php MostrarHeader(false); ?>
    <?php MostrarMenuAdmin('productos'); ?>

    <main class="container py-4">

      <h2 class="mb-3">Editar producto</h2>

      <?php MostrarMensaje(); ?>

      <form action="editar.php?id=<?php echo $idProducto; ?>" method="POST" enctype="multipart/form-data" id="formProducto" data-imagen-obligatoria="0">

        <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">

        <?php include __DIR__ . '/formulario.php'; ?>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="listado.php" class="btn btn-outline-secondary">Volver</a>
          <button type="submit" name="btnActualizarProducto" class="btn btn-verde">Guardar cambios</button>
        </div>
      </form>

    </main>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

    <script src="<?php echo Asset('js/admin.js'); ?>"></script>

  </body>
</html>
