<?php
include_once __DIR__ . '/../layoutInterno.php';
ValidarSesion();
include_once __DIR__ . '/../../Controller/InicioController.php';
include_once __DIR__ . '/../../Controller/PedidoController.php';
$u = ConsultarUsuario();
$pedidos = ConsultarPedidosCliente();
$pasos = PasosPedido();
?>
<!DOCTYPE html>
<html lang="es">
  <?php IncludeCSS('Mi cuenta'); ?>
  <body>
    <?php MostrarHeader(false); ?>
    <main class="container py-4" style="max-width:900px">
      <div class="card p-4 mb-4">
        <h2 class="h4 mb-3"><i class="bi bi-person-circle"></i> <?php echo Texto($u['nombre'] . ' ' . $u['apellidos']); ?></h2>
        <p class="mb-1"><i class="bi bi-envelope"></i> <?php echo Texto($u['correo']); ?></p>
        <p class="mb-0"><i class="bi bi-telephone"></i> <?php echo Texto($u['telefono']); ?></p>
      </div>
      <h3 class="h4 mb-3">Mis pedidos</h3>
      <?php if (count($pedidos) == 0) { ?><p class="text-muted">Todavía no tenés pedidos.</p><?php } ?>
      <?php foreach ($pedidos as $p) {
        $activo = !in_array($p['estado'], ['Entregado', 'Cancelado']);
        $paso = array_search($p['estado'], $pasos); ?>
        <div class="card p-3 mb-3 <?php echo $activo ? 'border-success' : ''; ?>">
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-cart3 fs-1"></i>
            <div class="flex-grow-1">
              <strong>Pedido #<?php echo (int) $p['id_pedido']; ?></strong> <?php echo EtiquetaEstado($p['estado']); ?>
              <div class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?> · <?php echo Texto($p['metodo_pago']); ?> · Entrega <?php echo Texto($p['fecha_entrega'] . ' ' . $p['franja_horaria']); ?></div>
            </div>
            <strong class="fs-5"><?php echo FormatoMoneda($p['total']); ?></strong>
          </div>
          <?php if ($activo) { ?>
            <?php if ($p['estado'] == 'En espera') { ?>
              <div class="alert alert-warning mt-3 mb-0 py-2">Estamos revisando tu comprobante de SINPE.</div>
            <?php } else { ?>
              <div class="progress mt-3" style="height:8px"><div class="progress-bar bg-success" style="width:<?php echo (($paso + 1) / count($pasos)) * 100; ?>%"></div></div>
              <div class="d-flex justify-content-between small mt-1"><?php foreach ($pasos as $i => $nombre) echo '<span class="' . ($i <= $paso ? 'fw-bold text-success' : 'text-muted') . '">' . $nombre . '</span>'; ?></div>
            <?php } ?>
          <?php } ?>
          <details class="mt-2"><summary>Ver productos</summary>
            <?php foreach (ConsultarDetallePedido($p['id_pedido']) as $d) { ?>
              <div class="d-flex justify-content-between small"><span><?php echo (int) $d['cantidad']; ?> × <?php echo Texto($d['nombre']); ?></span><span><?php echo FormatoMoneda($d['subtotal']); ?></span></div>
            <?php } ?>
          </details>
        </div>
      <?php } ?>
    </main>
    <?php MostrarFooter(); IncludeJS(); ?>
  </body>
</html>
