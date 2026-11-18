<?php
include_once __DIR__ . '/../../layoutInterno.php';
ValidarRol('Administrador');
include_once __DIR__ . '/../../../Controller/InicioController.php';
include_once __DIR__ . '/../../../Controller/PedidoController.php';

// siguiente estado permitido (el trigger de la base tambien lo revisa)
$siguiente = ['En espera' => 'Recibido', 'Recibido' => 'En preparación', 'En preparación' => 'En camino', 'En camino' => 'Entregado'];

if (isset($_POST['btnEstadoPedido'])) {
    $ok = CambiarEstadoPedidoModel((int) $_SESSION['idUsuario'], (int) $_POST['idPedido'], $_POST['estado']);
    $_POST["mensaje"] = $ok ? "Pedido #" . (int) $_POST['idPedido'] . " ahora está: " . $_POST['estado'] : ($_POST["errorBD"] ?? "No se pudo cambiar el estado");
    $_POST["tipoMensaje"] = $ok ? "success" : "danger";
}
$estado = $_GET['estado'] ?? '';
$pedidos = ConsultarPedidosAdminModel($estado, null, null);
?>
<!DOCTYPE html>
<html lang="es">
  <?php IncludeCSS('Pedidos'); ?>
  <body>
    <?php MostrarHeader(false); MostrarMenuAdmin('pedidos'); ?>
    <main class="container-fluid py-4">
      <h2 class="mb-3">Pedidos</h2>
      <div class="mb-3">
        <?php foreach (['' => 'Todos', 'En espera' => 'En espera (SINPE)', 'Recibido' => 'Recibidos', 'En preparación' => 'En preparación', 'En camino' => 'En camino', 'Entregado' => 'Entregados', 'Cancelado' => 'Cancelados'] as $v => $t) { ?>
          <a href="?estado=<?php echo urlencode($v); ?>" class="btn btn-sm <?php echo $estado == $v ? 'btn-verde' : 'btn-outline-secondary'; ?> mb-1"><?php echo $t; ?></a>
        <?php } ?>
      </div>
      <?php MostrarMensaje(); ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Pago</th><th class="text-end">Total</th><th>Estado</th><th class="text-center">Acciones</th></tr></thead>
          <tbody>
            <?php if (count($pedidos) == 0) { ?><tr><td colspan="7" class="text-center">No hay pedidos</td></tr><?php } ?>
            <?php foreach ($pedidos as $p) { $id = (int) $p['id_pedido']; ?>
              <tr>
                <td><i class="bi bi-cart3"></i> <?php echo $id; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?></td>
                <td><?php echo Texto($p['cliente']); ?><br><small class="text-muted"><?php echo Texto($p['correo']); ?></small></td>
                <td><?php echo Texto($p['metodo_pago']); ?>
                  <?php if ($p['metodo_pago'] == 'SINPE') { ?><br><a href="comprobante.php?id=<?php echo $id; ?>" target="_blank" rel="noopener">Ver comprobante</a><?php } ?></td>
                <td class="text-end"><?php echo FormatoMoneda($p['total']); ?></td>
                <td><?php echo EtiquetaEstado($p['estado']); ?></td>
                <td class="text-center text-nowrap">
                  <?php if (isset($siguiente[$p['estado']])) { ?>
                    <form method="POST" class="d-inline confirmar" data-pregunta="¿Pasar el pedido #<?php echo $id; ?> a <?php echo $siguiente[$p['estado']]; ?>?">
                      <input type="hidden" name="idPedido" value="<?php echo $id; ?>"><input type="hidden" name="estado" value="<?php echo $siguiente[$p['estado']]; ?>">
                      <button name="btnEstadoPedido" class="btn btn-sm btn-outline-success"><?php echo $p['estado'] == 'En espera' ? 'Aprobar pago' : $siguiente[$p['estado']]; ?></button>
                    </form>
                    <form method="POST" class="d-inline confirmar" data-pregunta="¿<?php echo $p['estado'] == 'En espera' ? 'Rechazar el pago y cancelar' : 'Cancelar'; ?> el pedido #<?php echo $id; ?>? El stock se devuelve.">
                      <input type="hidden" name="idPedido" value="<?php echo $id; ?>"><input type="hidden" name="estado" value="Cancelado">
                      <button name="btnEstadoPedido" class="btn btn-sm btn-outline-danger"><?php echo $p['estado'] == 'En espera' ? 'Rechazar' : 'Cancelar'; ?></button>
                    </form>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </main>
    <?php MostrarFooter(); IncludeJS(); ?>
    <script src="<?php echo Asset('js/admin.js'); ?>"></script>
  </body>
</html>
