<?php
include_once __DIR__ . '/../../../Controller/PedidoController.php';
ValidarRol('Administrador');
$p = ConsultarPedidoAdminModel((int) ($_GET['id'] ?? 0));
$ruta = $p && $p['comprobante'] ? CarpetaComprobantes() . basename($p['comprobante']) : '';
if (!$ruta || !is_file($ruta)) { http_response_code(404); exit('Comprobante no encontrado'); }
header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE)) -> file($ruta));
header('X-Content-Type-Options: nosniff');
readfile($ruta);
