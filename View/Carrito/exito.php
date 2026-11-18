<?php
include_once __DIR__ . '/../layoutInterno.php';
ValidarSesion();
include_once __DIR__ . '/../../Controller/InicioController.php';
include_once __DIR__ . '/../../Controller/PedidoController.php';
$pedido = ConsultarPedidoCliente((int) ($_GET['id'] ?? 0));
if (!$pedido) { header("Location: ../Inicio/home.php"); exit(); }
$espera = $pedido['estado'] == 'En espera';
?>
<!DOCTYPE html>
<html lang="es">
  <?php IncludeCSS('Compra realizada'); ?>
  <body>
    <?php MostrarHeader(false); ?>
    <main class="container py-5 text-center" style="max-width:600px">
      <i class="bi <?php echo $espera ? 'bi-hourglass-split text-warning' : 'bi-check-circle-fill text-success'; ?>" style="font-size:4.5rem"></i>
      <h2 class="mt-3"><?php echo $espera ? 'Pedido en espera de aprobación' : '¡Compra exitosa!'; ?></h2>
      <p class="fs-5">Pedido <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong> · Total <strong><?php echo FormatoMoneda($pedido['total']); ?></strong></p>
      <p><?php echo $espera ? 'Revisaremos tu comprobante de SINPE y te avisaremos cuando se apruebe.' : 'Entrega: ' . Texto($pedido['fecha_entrega'] . ' ' . $pedido['franja_horaria']); ?></p>
      <a href="../Catalogo/catalogo.php" class="btn btn-verde mt-3">Seguir comprando</a>
    </main>
    <?php MostrarFooter(); IncludeJS(); ?>
  </body>
</html>
