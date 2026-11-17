<?php
include_once __DIR__ . '/../../layoutInterno.php';
include_once __DIR__ . '/../../../Controller/InicioController.php';
include_once __DIR__ . '/../../../Controller/ProductoController.php';

$categorias = ConsultarCategoriasAdmin();
$producto = [];
?>
<!DOCTYPE html>
<html lang="es">

  <?php IncludeCSS('Nuevo producto'); ?>

  <body>

    <?php MostrarHeader(false); ?>
    <?php MostrarMenuAdmin('productos'); ?>

    <main class="container py-4">

      <h2 class="mb-3">Nuevo producto</h2>

      <?php MostrarMensaje(); ?>

      <!-- multipart para poder subir la imagen -->
      <form action="" method="POST" enctype="multipart/form-data" id="formProducto" data-imagen-obligatoria="1">

        <?php include __DIR__ . '/formulario.php'; ?>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="listado.php" class="btn btn-outline-secondary">Volver</a>
          <button type="submit" name="btnRegistrarProducto" class="btn btn-verde">Registrar</button>
        </div>
      </form>

    </main>

    <?php MostrarFooter(); ?>

    <?php IncludeJS(); ?>

    <script src="../../Assets/js/admin.js"></script>

  </body>
</html>
