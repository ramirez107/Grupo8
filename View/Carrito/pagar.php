<?php
include_once __DIR__ . '/../layoutInterno.php';
ValidarSesion();
include_once __DIR__ . '/../../Controller/InicioController.php';
include_once __DIR__ . '/../../Controller/PedidoController.php';

$carrito = ConsultarCarrito();
if (count($carrito) == 0) { header("Location: ../Catalogo/catalogo.php"); exit(); }
$direcciones = ConsultarDirecciones();
$metodo = $_POST['metodoPago'] ?? 'Efectivo';
?>
<!DOCTYPE html>
<html lang="es">
  <?php IncludeCSS('Finalizar compra'); ?>
  <body>
    <?php MostrarHeader(false); ?>
    <main class="container py-4" style="max-width:900px">
      <h2 class="mb-3">Finalizar compra</h2>
      <?php MostrarMensaje(); ?>
      <form method="POST" enctype="multipart/form-data">
        <div class="card p-4 mb-3">
          <h3 class="h5">Resumen</h3>
          <?php foreach ($carrito as $i) { ?>
            <div class="d-flex justify-content-between"><span><?php echo (int) $i['cantidad']; ?> × <?php echo Texto($i['nombre']); ?></span><span><?php echo FormatoMoneda($i['subtotal']); ?></span></div>
          <?php } ?>
          <hr><div class="d-flex justify-content-between fs-5"><strong>Total</strong><strong><?php echo FormatoMoneda($carrito[0]['total']); ?></strong></div>
        </div>

        <div class="card p-4 mb-3">
          <h3 class="h5">Entrega</h3>
          <select name="idDireccion" class="form-select mb-2">
            <?php foreach ($direcciones as $d) { ?>
              <option value="<?php echo (int) $d['id_direccion']; ?>"><?php echo Texto($d['provincia'] . ', ' . $d['canton'] . ', ' . $d['distrito'] . ' - ' . $d['detalle']); ?></option>
            <?php } ?>
            <option value="0" <?php echo count($direcciones) == 0 ? 'selected' : ''; ?>>Nueva dirección</option>
          </select>
          <div class="row g-2 mb-2">
            <div class="col-md-3"><select name="ddlProvincia" class="form-select"><option value="">Provincia</option><?php foreach (['San José','Alajuela','Cartago','Heredia','Guanacaste','Puntarenas','Limón'] as $p) echo '<option>' . $p . '</option>'; ?></select></div>
            <div class="col-md-3"><input name="txtCanton" class="form-control" placeholder="Cantón"></div>
            <div class="col-md-3"><input name="txtDistrito" class="form-control" placeholder="Distrito"></div>
            <div class="col-md-3"><input name="txtDetalle" class="form-control" placeholder="Otras señas"></div>
          </div>
          <small class="text-muted d-block mb-2">Los campos de dirección solo se usan si elige "Nueva dirección".</small>
          <div class="row g-2">
            <div class="col-md-6"><input type="date" name="txtFechaEntrega" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"></div>
            <div class="col-md-6"><select name="ddlFranja" class="form-select"><option>08:00-12:00</option><option>12:00-16:00</option><option>16:00-20:00</option></select></div>
          </div>
        </div>

        <div class="card p-4 mb-3">
          <h3 class="h5">Pago</h3>
          <?php foreach (['Efectivo' => 'bi-cash', 'Tarjeta' => 'bi-credit-card', 'SINPE' => 'bi-phone'] as $m => $ic) { ?>
            <div class="form-check"><input class="form-check-input metodo" type="radio" name="metodoPago" id="m<?php echo $m; ?>" value="<?php echo $m; ?>" <?php echo $metodo == $m ? 'checked' : ''; ?>>
              <label class="form-check-label" for="m<?php echo $m; ?>"><i class="bi <?php echo $ic; ?>"></i> <?php echo $m == 'SINPE' ? 'SINPE Móvil' : $m; ?></label></div>
          <?php } ?>
          <div id="pagoTarjeta" class="row g-2 mt-2">
            <div class="col-12"><input name="txtTitular" class="form-control" placeholder="Nombre del dueño de la tarjeta" autocomplete="cc-name"></div>
            <div class="col-md-6"><input name="txtNumeroTarjeta" class="form-control" placeholder="Número de tarjeta" inputmode="numeric" autocomplete="cc-number"></div>
            <div class="col-md-3"><input name="txtVence" class="form-control" placeholder="MM/AA" autocomplete="cc-exp"></div>
            <div class="col-md-3"><input name="txtCvv" class="form-control" placeholder="CVV" inputmode="numeric" autocomplete="cc-csc"></div>
          </div>
          <div id="pagoSinpe" class="mt-2">
            <p class="mb-2">Envíe <strong><?php echo FormatoMoneda($carrito[0]['total']); ?></strong> al SINPE <strong><?php echo SINPE_TIENDA; ?></strong> (<?php echo SINPE_NOMBRE; ?>) y adjunte el comprobante. El pedido queda en espera hasta que lo aprobemos.</p>
            <input type="file" name="fileComprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
          </div>
        </div>
        <button type="submit" name="btnFinalizarCompra" class="btn btn-verde btn-lg w-100">Confirmar compra</button>
      </form>
    </main>
    <?php MostrarFooter(); IncludeJS(); ?>
    <script>
      function verPago() {
        var m = document.querySelector('.metodo:checked').value;
        document.getElementById('pagoTarjeta').style.display = m === 'Tarjeta' ? '' : 'none';
        document.getElementById('pagoSinpe').style.display = m === 'SINPE' ? '' : 'none';
      }
      document.querySelectorAll('.metodo').forEach(function (r) { r.addEventListener('change', verPago); });
      verPago();
    </script>
  </body>
</html>
