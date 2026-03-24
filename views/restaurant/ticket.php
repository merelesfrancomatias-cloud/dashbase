<?php
/**
 * Ticket de impresión para Restaurant
 * ?tipo=comanda&id=X  → ticket de cocina / comanda
 * ?tipo=recibo&id=X   → recibo del cliente (por comanda_id)
 */
session_start();
if (!isset($_SESSION['negocio_id'])) { header('Location: ../auth/login.php'); exit; }

require_once __DIR__ . '/../../config/database.php';
$pdo = (new Database())->getConnection();
$nid = (int)$_SESSION['negocio_id'];

$tipo = $_GET['tipo'] ?? 'comanda';
$id   = (int)($_GET['id'] ?? 0);
if (!$id) die('ID requerido');

// ── Datos del negocio ─────────────────────────────────────────
$neg = $pdo->prepare("SELECT nombre, logo, direccion, telefono, mostrar_logo_ticket FROM negocios WHERE id=?");
$neg->execute([$nid]);
$negocio = $neg->fetch(PDO::FETCH_ASSOC);
if (!$negocio) die('Negocio no encontrado');

$base = '/DASHBASE';

// ── Datos según tipo ─────────────────────────────────────────
if ($tipo === 'comanda') {
    // Ticket de cocina: items pendientes/en prep de una comanda
    $stmt = $pdo->prepare("
        SELECT c.id, c.numero, c.personas, c.abierta_at, c.observaciones,
               m.numero AS mesa_numero, s.nombre AS sector_nombre,
               u.nombre AS mozo_nombre
        FROM restaurant_comandas c
        JOIN restaurant_mesas m ON m.id = c.mesa_id
        LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
        LEFT JOIN usuarios u ON u.id = c.mozo_id
        WHERE c.id=? AND c.negocio_id=?
    ");
    $stmt->execute([$id, $nid]);
    $comanda = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$comanda) die('Comanda no encontrada');

    $items = $pdo->prepare("
        SELECT ci.nombre_item, ci.cantidad, ci.observaciones, ci.sector_cocina, ci.precio_unit, ci.subtotal
        FROM restaurant_comanda_items ci
        WHERE ci.comanda_id=? AND ci.estado_cocina != 'cancelado'
        ORDER BY ci.sector_cocina, ci.id
    ");
    $items->execute([$id]);
    $items = $items->fetchAll(PDO::FETCH_ASSOC);

} elseif ($tipo === 'recibo') {
    // Recibo del cliente: buscamos la venta asociada a la comanda
    $stmt = $pdo->prepare("
        SELECT c.id, c.numero, c.personas, c.cerrada_at, c.descuento,
               c.subtotal, c.total, c.venta_id,
               m.numero AS mesa_numero, s.nombre AS sector_nombre,
               u.nombre AS mozo_nombre
        FROM restaurant_comandas c
        JOIN restaurant_mesas m ON m.id = c.mesa_id
        LEFT JOIN restaurant_sectores s ON s.id = m.sector_id
        LEFT JOIN usuarios u ON u.id = c.mozo_id
        WHERE c.id=? AND c.negocio_id=?
    ");
    $stmt->execute([$id, $nid]);
    $comanda = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$comanda) die('Comanda no encontrada');

    // Obtener método de pago de la venta
    $metodoPago = 'efectivo';
    if ($comanda['venta_id']) {
        $v = $pdo->prepare("SELECT metodo_pago, cliente_nombre FROM ventas WHERE id=? AND negocio_id=?");
        $v->execute([$comanda['venta_id'], $nid]);
        $venta = $v->fetch(PDO::FETCH_ASSOC);
        if ($venta) $metodoPago = $venta['metodo_pago'];
    }

    $items = $pdo->prepare("
        SELECT ci.nombre_item, ci.cantidad, ci.precio_unit, ci.subtotal
        FROM restaurant_comanda_items ci
        WHERE ci.comanda_id=? AND ci.estado_cocina != 'cancelado'
        ORDER BY ci.id
    ");
    $items->execute([$id]);
    $items = $items->fetchAll(PDO::FETCH_ASSOC);

    // Split info (pasado por GET desde el JS)
    $splitPartes = [];
    if (!empty($_GET['split']) && !empty($_GET['n'])) {
        $n = (int)$_GET['n'];
        for ($i = 0; $i < $n; $i++) {
            $splitPartes[] = [
                'monto'  => (float)($_GET["p{$i}"] ?? 0),
                'metodo' => htmlspecialchars($_GET["m{$i}"] ?? 'efectivo'),
            ];
        }
    }
}

function fmt($n) { return '$' . number_format((float)$n, 0, ',', '.'); }
function fmtFecha($dt) { return $dt ? date('d/m/Y H:i', strtotime($dt)) : '—'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tipo === 'comanda' ? 'Comanda' : 'Recibo' ?> #<?= $comanda['numero'] ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #000;
    background: #fff;
    width: 80mm;
    margin: 0 auto;
    padding: 8px;
}
.center { text-align: center; }
.bold   { font-weight: bold; }
.big    { font-size: 16px; }
.small  { font-size: 11px; }
.sep    { border: none; border-top: 1px dashed #000; margin: 6px 0; }
.sep-solid { border: none; border-top: 1px solid #000; margin: 6px 0; }
.row    { display: flex; justify-content: space-between; margin: 3px 0; }
.row-cant { display: flex; gap: 4px; margin: 3px 0; }
.cant   { min-width: 22px; font-weight: bold; }
.item-nombre { flex: 1; }
.item-precio { min-width: 52px; text-align: right; }
.obs    { font-size: 11px; color: #555; margin: 1px 0 4px 26px; font-style: italic; }
.sector-header { font-weight: bold; text-transform: uppercase; font-size: 11px; margin: 8px 0 3px; border-bottom: 1px solid #000; }
.logo-img { max-width: 140px; max-height: 60px; display: block; margin: 0 auto 6px; }
.total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin: 4px 0; }
.metodo { display: inline-block; font-size: 11px; border: 1px solid #000; padding: 1px 6px; border-radius: 3px; margin-top: 4px; }

/* Ocultar todo al imprimir excepto el ticket */
@media screen {
    body { box-shadow: 0 0 12px rgba(0,0,0,.15); margin: 20px auto; }
    .btn-print { display: flex; justify-content: center; gap: 8px; margin: 16px 0 8px; }
    .btn-print button {
        padding: 8px 20px; border: none; border-radius: 6px; cursor: pointer;
        font-size: 13px; font-weight: 700;
    }
    .btn-impr { background: #0f172a; color: #fff; }
    .btn-cerr { background: #f1f5f9; color: #334155; }
}
@media print {
    .btn-print { display: none !important; }
    body { width: 100%; margin: 0; padding: 4px; box-shadow: none; }
}
</style>
</head>
<body>

<div class="btn-print">
    <button class="btn-impr" onclick="window.print()">🖨 Imprimir</button>
    <button class="btn-cerr" onclick="window.close()">✕ Cerrar</button>
</div>

<!-- Logo -->
<?php if (!empty($negocio['logo']) && $negocio['mostrar_logo_ticket']): ?>
    <img src="<?= $base ?>/<?= htmlspecialchars($negocio['logo']) ?>" class="logo-img" alt="">
<?php endif; ?>

<!-- Encabezado -->
<div class="center">
    <div class="bold big"><?= htmlspecialchars($negocio['nombre']) ?></div>
    <?php if (!empty($negocio['direccion'])): ?>
        <div class="small"><?= htmlspecialchars($negocio['direccion']) ?></div>
    <?php endif; ?>
    <?php if (!empty($negocio['telefono'])): ?>
        <div class="small">Tel: <?= htmlspecialchars($negocio['telefono']) ?></div>
    <?php endif; ?>
</div>
<hr class="sep-solid">

<?php if ($tipo === 'comanda'): ?>
<!-- ══ TICKET DE COMANDA ══ -->
<div class="center bold">*** COMANDA ***</div>
<div class="sep"></div>
<div class="row">
    <span>Mesa:</span>
    <span class="bold big"><?= htmlspecialchars(
        ($comanda['sector_nombre'] ? $comanda['sector_nombre'].' ' : '') . $comanda['mesa_numero']
    ) ?></span>
</div>
<div class="row"><span>Comanda #:</span><span class="bold"><?= $comanda['numero'] ?></span></div>
<div class="row"><span>Hora:</span><span><?= fmtFecha($comanda['abierta_at']) ?></span></div>
<?php if (!empty($comanda['mozo_nombre'])): ?>
<div class="row"><span>Mozo:</span><span><?= htmlspecialchars($comanda['mozo_nombre']) ?></span></div>
<?php endif; ?>
<?php if ($comanda['personas'] > 1): ?>
<div class="row"><span>Personas:</span><span><?= $comanda['personas'] ?></span></div>
<?php endif; ?>
<hr class="sep-solid">

<?php
// Agrupar por sector de cocina
$sectores = [];
foreach ($items as $item) {
    $sec = $item['sector_cocina'] ?? 'principal';
    $sectores[$sec][] = $item;
}
$hayVariosSectores = count($sectores) > 1;
foreach ($sectores as $sec => $its):
?>
    <?php if ($hayVariosSectores): ?>
        <div class="sector-header">— <?= htmlspecialchars(strtoupper($sec)) ?> —</div>
    <?php endif; ?>
    <?php foreach ($its as $it): ?>
        <div class="row-cant">
            <span class="cant"><?= (int)$it['cantidad'] ?>x</span>
            <span class="item-nombre"><?= htmlspecialchars($it['nombre_item']) ?></span>
        </div>
        <?php if (!empty($it['observaciones'])): ?>
            <div class="obs">↳ <?= htmlspecialchars($it['observaciones']) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endforeach; ?>

<?php if (!empty($comanda['observaciones'])): ?>
<hr class="sep">
<div class="small"><strong>Obs:</strong> <?= htmlspecialchars($comanda['observaciones']) ?></div>
<?php endif; ?>
<hr class="sep-solid">
<div class="center small"><?= date('d/m/Y H:i:s') ?></div>

<?php else: ?>
<!-- ══ RECIBO DEL CLIENTE ══ -->
<div class="center bold">*** RECIBO ***</div>
<div class="sep"></div>
<div class="row"><span>Mesa:</span>
    <span class="bold"><?= htmlspecialchars(
        ($comanda['sector_nombre'] ? $comanda['sector_nombre'].' ' : '') . $comanda['mesa_numero']
    ) ?></span>
</div>
<div class="row"><span>Comanda #:</span><span><?= $comanda['numero'] ?></span></div>
<?php if (!empty($comanda['mozo_nombre'])): ?>
<div class="row"><span>Atendido por:</span><span><?= htmlspecialchars($comanda['mozo_nombre']) ?></span></div>
<?php endif; ?>
<div class="row"><span>Fecha:</span><span><?= fmtFecha($comanda['cerrada_at']) ?></span></div>
<hr class="sep-solid">

<!-- Items -->
<div class="row small bold">
    <span>Descripción</span>
    <span>Importe</span>
</div>
<hr class="sep">
<?php foreach ($items as $it): ?>
    <div class="row-cant">
        <span class="cant"><?= (int)$it['cantidad'] ?>x</span>
        <span class="item-nombre"><?= htmlspecialchars($it['nombre_item']) ?></span>
        <span class="item-precio"><?= fmt($it['subtotal']) ?></span>
    </div>
    <div class="obs small"><?= fmt($it['precio_unit']) ?> c/u</div>
<?php endforeach; ?>
<hr class="sep-solid">

<div class="row"><span>Subtotal:</span><span><?= fmt($comanda['subtotal']) ?></span></div>
<?php if ((float)$comanda['descuento'] > 0): ?>
<div class="row"><span>Descuento:</span><span>- <?= fmt($comanda['descuento']) ?></span></div>
<?php endif; ?>
<div class="total-row"><span>TOTAL:</span><span><?= fmt($comanda['total']) ?></span></div>
<?php if (!empty($splitPartes)): ?>
<hr class="sep">
<div style="text-align:center;font-size:11px;font-weight:bold;margin:4px 0;">— DIVISIÓN DE CUENTA —</div>
<?php foreach ($splitPartes as $i => $p): ?>
<div class="row">
    <span>Parte <?= $i+1 ?></span>
    <span><?= fmt($p['monto']) ?> <small style="font-size:10px;">[<?= strtoupper($p['metodo']) ?>]</small></span>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="center">
    <span class="metodo"><?= strtoupper(htmlspecialchars($metodoPago)) ?></span>
</div>
<?php endif; ?>
<hr class="sep-solid">
<div class="center small">¡Gracias por su visita!</div>
<div class="center small"><?= date('d/m/Y H:i:s') ?></div>
<?php endif; ?>

<script>
// Auto-print si viene con ?autoprint=1
<?php if (!empty($_GET['autoprint'])): ?>
window.addEventListener('load', () => window.print());
<?php endif; ?>
</script>
</body>
</html>
