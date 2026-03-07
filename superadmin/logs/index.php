<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db    = sa_db();
$page  = max(1, intval($_GET['page'] ?? 1));
$perPage = 30;
$offset = ($page-1) * $perPage;

$negId  = intval($_GET['negocio'] ?? 0);
$accion = trim($_GET['accion'] ?? '');

$where = ['1=1']; $params = [];
if ($negId) { $where[] = 'l.negocio_id = ?'; $params[] = $negId; }
if ($accion){ $where[] = 'l.accion LIKE ?';  $params[] = "%$accion%"; }
$whereStr = implode(' AND ', $where);

$total  = $db->prepare("SELECT COUNT(*) FROM logs_actividad l WHERE $whereStr");
$total->execute($params);
$total  = $total->fetchColumn();
$totPag = ceil($total / $perPage);

$stmt = $db->prepare("
    SELECT l.*, n.nombre as negocio_nombre
    FROM logs_actividad l
    LEFT JOIN negocios n ON n.id = l.negocio_id
    WHERE $whereStr
    ORDER BY l.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$negocios = $db->query("SELECT id, nombre FROM negocios ORDER BY nombre")->fetchAll();

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Logs de Actividad', 'logs');
?>

<div class="sa-filter-bar">
    <select class="sa-filter-select" id="negocioF">
        <option value="0">Todos los negocios</option>
        <?php foreach ($negocios as $n): ?>
        <option value="<?= $n['id'] ?>" <?= $negId==$n['id']?'selected':'' ?>><?= htmlspecialchars($n['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="sa-search" style="max-width:280px">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="accionF" placeholder="Filtrar por acción..." value="<?= htmlspecialchars($accion) ?>"
               onkeydown="if(event.key==='Enter')applyF()">
    </div>
    <a href="#" onclick="applyF()" class="sa-btn secondary sm"><i class="fas fa-filter"></i> Filtrar</a>
    <a href="?" class="sa-btn ghost sm"><i class="fas fa-rotate-left"></i> Limpiar</a>
    <span style="margin-left:auto;color:var(--sa-muted);font-size:12px"><?= number_format($total) ?> registros</span>
</div>

<div class="sa-panel">
    <div class="sa-panel-header">
        <h3><i class="fas fa-scroll" style="color:var(--sa-warning);margin-right:8px"></i>Actividad del Sistema</h3>
    </div>
    <div class="sa-table-wrap">
        <table class="sa-table">
            <thead>
                <tr><th>Fecha</th><th>Acción</th><th>Negocio</th><th>Detalle</th><th>IP</th></tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
            <tr><td colspan="5"><div class="sa-empty"><i class="fas fa-scroll"></i><p>Sin logs</p></div></td></tr>
            <?php else: ?>
            <?php foreach ($logs as $log):
                $actionColors = [
                    'login'       => 'blue',
                    'logout'      => 'gray',
                    'bloquear'    => 'red',
                    'desbloquear' => 'green',
                    'renovar'     => 'green',
                    'crear'       => 'purple',
                    'editar'      => 'blue',
                ];
                $colorKey = 'gray';
                foreach ($actionColors as $k => $v) {
                    if (str_contains($log['accion'], $k)) { $colorKey = $v; break; }
                }
            ?>
            <tr>
                <td style="font-size:11px;color:var(--sa-muted);white-space:nowrap">
                    <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                </td>
                <td><span class="sa-pill <?= $colorKey ?>"><?= htmlspecialchars($log['accion']) ?></span></td>
                <td style="font-size:12px">
                    <?php if ($log['negocio_id']): ?>
                    <a href="../negocios/edit.php?id=<?= $log['negocio_id'] ?>" style="color:var(--sa-info)">
                        <?= htmlspecialchars($log['negocio_nombre'] ?? '#'.$log['negocio_id']) ?>
                    </a>
                    <?php else: ?>
                    <span style="color:var(--sa-muted)">Sistema</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--sa-muted);max-width:300px">
                    <?= htmlspecialchars(mb_substr($log['detalle'] ?? '', 0, 120)) ?>
                </td>
                <td style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars($log['ip'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totPag > 1): ?>
    <div class="sa-pagination">
        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&negocio=<?= $negId ?>&accion=<?= urlencode($accion) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
        <?php for ($i=max(1,$page-2);$i<=min($totPag,$page+2);$i++): ?>
        <a href="?page=<?= $i ?>&negocio=<?= $negId ?>&accion=<?= urlencode($accion) ?>" class="<?= $i===$page?'current':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totPag): ?><a href="?page=<?= $page+1 ?>&negocio=<?= $negId ?>&accion=<?= urlencode($accion) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script src="../assets/sa.js"></script>
<script>
function applyF() {
    const n = document.getElementById('negocioF').value;
    const a = document.getElementById('accionF').value;
    window.location.href = `?negocio=${n}&accion=${encodeURIComponent(a)}&page=1`;
}
</script>

<?php sa_layout_end(); ?>
