<?php
// campos del formulario de producto, lo usan registro.php y editar.php
// $producto trae los valores (vacio en registro) y $categorias la lista para el select
if (!isset($categorias)) {
    exit();
}

// valor del campo: lo que el usuario escribio o lo que viene de la base
function ValorCampo($campo, $valorBase)
{
    return Texto($_POST[$campo] ?? $valorBase);
}
?>
<div class="row g-3">
  <div class="col-md-6">
    <label for="txtNombre" class="form-label">Nombre</label>
    <input type="text" id="txtNombre" name="txtNombre" class="form-control"
           value="<?php echo ValorCampo('txtNombre', $producto['nombre'] ?? ''); ?>">
  </div>

  <div class="col-md-6">
    <label for="ddlCategoria" class="form-label">Categoría</label>
    <select id="ddlCategoria" name="ddlCategoria" class="form-select">
      <option value="">Seleccione una categoría</option>
      <?php
        $categoriaElegida = $_POST['ddlCategoria'] ?? ($producto['id_categoria'] ?? '');
        foreach ($categorias as $categoria) {
      ?>
        <option value="<?php echo (int) $categoria['id_categoria']; ?>"
          <?php echo $categoriaElegida == $categoria['id_categoria'] ? 'selected' : ''; ?>>
          <?php echo Texto($categoria['nombre']); ?><?php echo $categoria['activo'] == 1 ? '' : ' (inactiva)'; ?>
        </option>
      <?php } ?>
    </select>
  </div>

  <div class="col-12">
    <label for="txtDescripcion" class="form-label">Descripción</label>
    <textarea id="txtDescripcion" name="txtDescripcion" class="form-control" rows="3"><?php echo ValorCampo('txtDescripcion', $producto['descripcion'] ?? ''); ?></textarea>
  </div>

  <div class="col-md-4">
    <label for="txtPrecio" class="form-label">Precio (₡)</label>
    <input type="number" id="txtPrecio" name="txtPrecio" class="form-control" step="0.01"
           value="<?php echo ValorCampo('txtPrecio', $producto['precio'] ?? ''); ?>">
  </div>

  <div class="col-md-4">
    <label for="txtStock" class="form-label">Stock</label>
    <input type="number" id="txtStock" name="txtStock" class="form-control"
           value="<?php echo ValorCampo('txtStock', $producto['stock'] ?? ''); ?>">
  </div>

  <div class="col-md-4">
    <label for="txtStockMinimo" class="form-label">Stock mínimo</label>
    <input type="number" id="txtStockMinimo" name="txtStockMinimo" class="form-control"
           value="<?php echo ValorCampo('txtStockMinimo', $producto['stock_minimo'] ?? '5'); ?>">
  </div>

  <div class="col-md-8">
    <label for="fileImagen" class="form-label">Imagen (JPG o PNG, máximo 2 MB)</label>
    <input type="file" id="fileImagen" name="fileImagen" class="form-control" accept=".jpg,.jpeg,.png">
  </div>

  <?php if (isset($producto['imagen'])) { ?>
    <div class="col-md-4">
      <p class="form-label">Imagen actual</p>
      <img src="<?php echo Texto(RutaImagen($producto['imagen'])); ?>" class="imagen-tabla" alt="Imagen actual del producto">
    </div>
  <?php } ?>
</div>
