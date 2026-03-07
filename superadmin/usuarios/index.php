<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db    = sa_db();
$page  = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$negId = intval($_GET['negocio'] ?? 0);
$buscar = trim($_GET['q'] ?? '');

$where  = ['1=1'];
$params = [];
if ($negId) { $where[] = 'u.negocio_id = ?'; $params[] = $negId; }
if ($buscar) {
    $where[] = '(u.nombre LIKE ? OR u.email LIKE ?)';
    $params  = array_merge($params, ["%$buscar%", "%$buscar%"]);
}
$whereStr = implode(' AND ', $where);

$total   = $db->prepare("SELECT COUNT(*) FROM usuarios u WHERE $whereStr");
$total->execute($params);
$total   = $total->fetchColumn();
$totPag  = ceil($total / $perPage);
$offset  = ($page-1) * $perPage;

$stmt = $db->prepare("
    SELECT u.id, u.nombre, u.email, u.rol, u.activo, u.fecha_creacion as created_at,
           n.nombre as negocio_nombre, n.id as negocio_id
    FROM usuarios u
    LEFT JOIN negocios n ON n.id = u.negocio_id
    WHERE $whereStr
    ORDER BY u.fecha_creacion DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$negocios = $db->query("SELECT id, nombre FROM negocios ORDER BY nombre")->fetchAll();

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Usuarios', 'usuarios');
?>

<div class="sa-filter-bar">
    <div class="sa-search">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="searchQ" placeholder="Buscar usuario..." value="<?= htmlspecialchars($buscar) ?>"
               onkeydown="if(event.key==='Enter')applyF()">
    </div>
    <select class="sa-filter-select" id="negocioF" onchange="applyF()">
        <option value="0">Todos los negocios</option>
        <?php foreach ($negocios as $n): ?>
        <option value="<?= $n['id'] ?>" <?= $negId==$n['id']?'selected':'' ?>><?= htmlspecialchars($n['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <a href="#" onclick="applyF()" class="sa-btn secondary sm"><i class="fas fa-filter"></i> Filtrar</a>
</div>

<div class="sa-panel">
    <div class="sa-panel-header">
        <h3><i class="fas fa-users" style="color:var(--sa-primary);margin-right:8px"></i><?= number_format($total) ?> Usuarios</h3>
    </div>
    <div class="sa-table-wrap">
        <table class="sa-table">
            <thead>
                <tr><th>Usuario</th><th>Negocio</th><th>Rol</th><th>Estado</th><th>Creado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php if (empty($usuarios)): ?>
            <tr><td colspan="6"><div class="sa-empty"><i class="fas fa-user"></i><p>Sin usuarios</p></div></td></tr>
            <?php else: ?>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($u['nombre']) ?></div>
                    <div style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars($u['email']) ?></div>
                </td>
                <td>
                    <a href="../negocios/edit.php?id=<?= $u['negocio_id'] ?>" style="color:var(--sa-info);font-size:13px">
                        <?= htmlspecialchars($u['negocio_nombre'] ?? '—') ?>
                    </a>
                </td>
                <td><span class="sa-pill <?= $u['rol']==='admin'?'blue':'gray' ?>"><?= $u['rol'] === 'admin' ? 'Admin' : 'Empleado' ?></span></td>
                <td><span class="sa-pill <?= $u['activo']?'green':'gray' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                <td style="font-size:11px;color:var(--sa-muted)"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <button class="sa-btn <?= $u['activo']?'danger':'secondary' ?> sm icon-only"
                            title="<?= $u['activo']?'Desactivar':'Activar' ?>"
                            onclick="toggleUser(<?= $u['id'] ?>, <?= $u['activo']?0:1 ?>)">
                        <i class="fas <?= $u['activo']?'fa-user-slash':'fa-user-check' ?>"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totPag > 1): ?>
    <div class="sa-pagination">
        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&negocio=<?= $negId ?>&q=<?= urlencode($buscar) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
        <?php for ($i=max(1,$page-2);$i<=min($totPag,$page+2);$i++): ?>
        <a href="?page=<?= $i ?>&negocio=<?= $negId ?>&q=<?= urlencode($buscar) ?>" class="<?= $i===$page?'current':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totPag): ?><a href="?page=<?= $page+1 ?>&negocio=<?= $negId ?>&q=<?= urlencode($buscar) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script src="../assets/sa.js"></script>
<script>
function applyF() {
    const q = document.getElementById('searchQ').value;
    const n = document.getElementById('negocioF').value;
    window.location.href = `?q=${encodeURIComponent(q)}&negocio=${n}&page=1`;
}
async function toggleUser(id, activo) {
    const r = await sa_fetch('../api/negocio_action.php', { id, action: activo ? 'activar_usuario' : 'desactivar_usuario', usuario_id: id });
    // API simple inline
    const res = await fetch(`../api/usuario_action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, activo })
    });
    const data = await res.json();
    if (data.ok) { sa_toast(activo ? 'Usuario activado' : 'Usuario desactivado'); setTimeout(()=>location.reload(), 900); }
    else sa_toast(data.error || 'Error', 'error');
}
</script>

<?php sa_layout_end(); ?>
