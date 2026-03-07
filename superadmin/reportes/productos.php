<?php
session_start();
require_once __DIR__ . '/../_auth.php';
sa_check_auth();

$db = sa_db();

// Filtros
$rubroFiltro = (int)($_GET['rubro_id'] ?? 0);
$limite      = max(5, min(50, (int)($_GET['limite'] ?? 10)));
$periodo     = $_GET['periodo'] ?? '30';
$dias        = in_array($periodo, ['7','30','90','365']) ? (int)$periodo : 30;

// Rubros disponibles
$rubros = $db->query("SELECT id, nombre, icono FROM rubros ORDER BY nombre ASC")->fetchAll();

// ── Productos más vendidos por rubro ──────────────────────────────────────────
// Agrupa por rubro_id del negocio, suma cantidades de detalle_ventas
$rubroParam  = $rubroFiltro > 0 ? "AND n.rubro_id = $rubroFiltro" : '';

$stmt = $db->prepare("
    SELECT
        r.id          AS rubro_id,
        r.nombre      AS rubro_nombre,
        r.icono       AS rubro_icono,
        p.id          AS producto_id,
        p.nombre      AS producto_nombre,
        p.precio_venta,
        p.stock,
        COALESCE(SUM(dv.cantidad), 0)                     AS total_vendido,
        COALESCE(SUM(dv.subtotal), 0)                     AS total_facturado,
        COUNT(DISTINCT dv.venta_id)                        AS num_ventas,
        n.nombre                                           AS negocio_nombre
    FROM productos p
    INNER JOIN negocios n  ON n.id = p.negocio_id
    INNER JOIN rubros   r  ON r.id = n.rubro_id
    LEFT JOIN detalle_ventas dv ON dv.producto_id = p.id
    LEFT JOIN ventas         v  ON v.id = dv.venta_id
                                AND v.estado = 'completada'
                                AND v.fecha_venta >= DATE_SUB(NOW(), INTERVAL :dias DAY)
    WHERE p.activo = 1 $rubroParam
    GROUP BY p.id, r.id
    ORDER BY r.nombre ASC, total_vendido DESC
");
$stmt->execute([':dias' => $dias]);
$rows = $stmt->fetchAll();

// Agrupar por rubro
$porRubro = [];
foreach ($rows as $row) {
    $rid = $row['rubro_id'];
    if (!isset($porRubro[$rid])) {
        $porRubro[$rid] = [
            'nombre' => $row['rubro_nombre'],
            'icono'  => $row['rubro_icono'] ?? 'fa-tag',
            'items'  => [],
        ];
    }
    if (count($porRubro[$rid]['items']) < $limite) {
        $porRubro[$rid]['items'][] = $row;
    }
}

// Stats globales
$totalProductos  = $db->query("SELECT COUNT(*) FROM productos WHERE activo=1")->fetchColumn();
$totalVendidos   = $db->prepare("SELECT COALESCE(SUM(dv.cantidad),0) FROM detalle_ventas dv INNER JOIN ventas v ON v.id=dv.venta_id WHERE v.fecha_venta >= DATE_SUB(NOW(), INTERVAL :d DAY) AND v.estado='completada'");
$totalVendidos->execute([':d' => $dias]);
$totalVendidos   = $totalVendidos->fetchColumn();
$totalFacturado  = $db->prepare("SELECT COALESCE(SUM(v.total),0) FROM ventas v WHERE v.fecha_venta >= DATE_SUB(NOW(), INTERVAL :d DAY) AND v.estado='completada'");
$totalFacturado->execute([':d' => $dias]);
$totalFacturado  = $totalFacturado->fetchColumn();

require_once __DIR__ . '/../_layout.php';
sa_layout_start('Productos más vendidos por rubro', 'reportes_productos');

function fmt($n) {
    return '$' . number_format($n, 0, ',', '.');
}
?>

<style>
.rubro-section { margin-bottom: 36px; }
.rubro-header {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 14px; padding-bottom: 12px;
    border-bottom: 1px solid var(--border, #e5e7eb);
}
.rubro-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.rubro-title { font-size: 16px; font-weight: 700; }
.rubro-count { font-size: 12px; color: var(--muted, #6b7280); }

.prod-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.prod-table th {
    text-align: left; padding: 8px 12px;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--muted, #6b7280);
    border-bottom: 1px solid var(--border, #e5e7eb);
    background: var(--bg-soft, #f9fafb);
}
.prod-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border-light, #f3f4f6);
    vertical-align: middle;
}
.prod-table tr:last-child td { border-bottom: none; }
.prod-table tr:hover td { background: var(--bg-hover, #f9fafb); }

.rank-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    font-size: 11px; font-weight: 800;
}
.rank-1 { background: #FEF3C7; color: #D97706; }
.rank-2 { background: #F3F4F6; color: #374151; }
.rank-3 { background: #FEE2E2; color: #DC2626; }
.rank-n { background: #EFF6FF; color: #3B82F6; }

.bar-wrap {
    background: #f3f4f6; border-radius: 99px;
    height: 6px; width: 100px; overflow: hidden;
}
.bar-fill { height: 100%; border-radius: 99px; background: var(--primary, #0FD186); }

.filter-row {
    display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;
    margin-bottom: 24px;
}
.filter-row .form-group { margin: 0; }
.filter-row label { font-size: 12px; font-weight: 600; display: block; margin-bottom: 4px; }
.filter-row select, .filter-row input {
    padding: 8px 12px; border-radius: 8px;
    border: 1px solid var(--border, #e5e7eb);
    font-size: 13px; background: #fff; cursor: pointer;
}
.empty-rubro {
    text-align: center; padding: 30px; color: var(--muted, #9ca3af);
    font-size: 13px;
}
</style>

<div class="sa-page-header">
    <div>
        <h1 class="sa-page-title"><i class="fas fa-fire"></i> Productos más vendidos por rubro</h1>
        <p class="sa-page-sub">Ranking de productos con más unidades vendidas, agrupado por rubro de negocio.</p>
    </div>
</div>

<!-- Stats rápidas -->
<div class="sa-stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
    <div class="sa-stat-card">
        <div class="sa-stat-icon" style="background:#ecfdf5;color:#10b981;"><i class="fas fa-box-open"></i></div>
        <div>
            <div class="sa-stat-label">Productos activos</div>
            <div class="sa-stat-value"><?= number_format($totalProductos) ?></div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-shopping-cart"></i></div>
        <div>
            <div class="sa-stat-label">Unidades vendidas (<?= $dias ?>d)</div>
            <div class="sa-stat-value"><?= number_format($totalVendidos) ?></div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-dollar-sign"></i></div>
        <div>
            <div class="sa-stat-label">Facturado (<?= $dias ?>d)</div>
            <div class="sa-stat-value"><?= fmt($totalFacturado) ?></div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="sa-card" style="margin-bottom:28px;">
    <div class="sa-card-body">
        <form method="GET" class="filter-row">
            <div class="form-group">
                <label>Rubro</label>
                <select name="rubro_id" onchange="this.form.submit()">
                    <option value="0">— Todos los rubros —</option>
                    <?php foreach ($rubros as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $rubroFiltro == $r['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Período</label>
                <select name="periodo" onchange="this.form.submit()">
                    <option value="7"  <?= $periodo=='7'  ? 'selected':'' ?>>Últimos 7 días</option>
                    <option value="30" <?= $periodo=='30' ? 'selected':'' ?>>Últimos 30 días</option>
                    <option value="90" <?= $periodo=='90' ? 'selected':'' ?>>Últimos 90 días</option>
                    <option value="365"<?= $periodo=='365'? 'selected':'' ?>>Último año</option>
                </select>
            </div>
            <div class="form-group">
                <label>Mostrar top</label>
                <select name="limite" onchange="this.form.submit()">
                    <option value="5"  <?= $limite==5  ? 'selected':'' ?>>Top 5</option>
                    <option value="10" <?= $limite==10 ? 'selected':'' ?>>Top 10</option>
                    <option value="20" <?= $limite==20 ? 'selected':'' ?>>Top 20</option>
                    <option value="50" <?= $limite==50 ? 'selected':'' ?>>Top 50</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Tabla por rubro -->
<?php if (empty($porRubro)): ?>
    <div class="sa-card">
        <div class="empty-rubro">
            <i class="fas fa-box-open" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i>
            No hay datos de ventas para el período seleccionado.
        </div>
    </div>
<?php else: ?>
    <?php
    $colors = ['#0FD186','#6366f1','#f59e0b','#ef4444','#3b82f6','#10b981','#ec4899','#14b8a6','#8b5cf6','#f97316'];
    $ci = 0;
    foreach ($porRubro as $rid => $rubro):
        $color = $colors[$ci % count($colors)];
        $ci++;
        $maxVendido = max(1, max(array_column($rubro['items'], 'total_vendido')));
    ?>
    <div class="sa-card rubro-section">
        <div class="sa-card-body">
            <div class="rubro-header">
                <div class="rubro-icon" style="background:<?= $color ?>22;color:<?= $color ?>;">
                    <i class="fas <?= htmlspecialchars($rubro['icono']) ?>"></i>
                </div>
                <div>
                    <div class="rubro-title"><?= htmlspecialchars($rubro['nombre']) ?></div>
                    <div class="rubro-count"><?= count($rubro['items']) ?> productos · top <?= $limite ?> en <?= $dias ?>d</div>
                </div>
            </div>

            <?php if (empty($rubro['items'])): ?>
                <div class="empty-rubro">Sin ventas en este período.</div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="prod-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Producto</th>
                                <th>Negocio</th>
                                <th style="text-align:right;">Precio</th>
                                <th style="text-align:right;">Unidades</th>
                                <th style="text-align:right;">Facturado</th>
                                <th style="text-align:right;">Stock</th>
                                <th style="width:120px;">Tendencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rubro['items'] as $i => $prod):
                                $rank = $i + 1;
                                $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-n'));
                                $pct = $maxVendido > 0 ? round(($prod['total_vendido'] / $maxVendido) * 100) : 0;
                                $stockColor = $prod['stock'] <= 0 ? '#ef4444' : ($prod['stock'] <= 5 ? '#f59e0b' : '#10b981');
                            ?>
                            <tr>
                                <td><span class="rank-badge <?= $rankClass ?>"><?= $rank ?></span></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($prod['producto_nombre']) ?></td>
                                <td style="color:var(--muted,#6b7280);font-size:12px;"><?= htmlspecialchars($prod['negocio_nombre']) ?></td>
                                <td style="text-align:right;"><?= fmt($prod['precio_venta']) ?></td>
                                <td style="text-align:right;font-weight:700;color:<?= $color ?>;">
                                    <?= number_format($prod['total_vendido']) ?>
                                </td>
                                <td style="text-align:right;font-weight:600;"><?= fmt($prod['total_facturado']) ?></td>
                                <td style="text-align:right;">
                                    <span style="color:<?= $stockColor ?>;font-weight:600;">
                                        <?= number_format($prod['stock']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="bar-wrap">
                                        <div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php sa_layout_end(); ?>
