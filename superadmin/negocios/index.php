<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db = sa_db();

// Filtros
$filtro  = $_GET['filtro']  ?? 'todos';
$buscar  = trim($_GET['q']  ?? '');
$plan_f  = intval($_GET['plan'] ?? 0);
$rubro_f = intval($_GET['rubro'] ?? 0);
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

// Construir WHERE
$where = ['1=1'];
$params = [];

if ($filtro === 'activos')   { $where[] = 'n.activo = 1 AND n.bloqueado = 0'; }
if ($filtro === 'inactivos') { $where[] = 'n.activo = 0'; }
if ($filtro === 'bloqueados'){ $where[] = 'n.bloqueado = 1'; }
if ($filtro === 'vencen')    { $where[] = 'n.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)'; }
if ($filtro === 'vencidos')  { $where[] = 'n.fecha_vencimiento < CURDATE() AND n.activo = 1'; }

if ($buscar) {
    $where[] = '(n.nombre LIKE ? OR n.email LIKE ? OR n.telefono LIKE ?)';
    $params = array_merge($params, ["%$buscar%", "%$buscar%", "%$buscar%"]);
}
if ($plan_f)  { $where[] = 'n.plan_id = ?';  $params[] = $plan_f; }
if ($rubro_f) { $where[] = 'n.rubro_id = ?'; $params[] = $rubro_f; }

$whereStr = implode(' AND ', $where);

// Total
$totalStmt = $db->prepare("SELECT COUNT(*) FROM negocios n WHERE $whereStr");
$totalStmt->execute($params);
$total = $totalStmt->fetchColumn();
$totalPag = ceil($total / $perPage);

// Datos
$offset = ($page - 1) * $perPage;
$stmt = $db->prepare("
    SELECT n.id, n.nombre, n.email, n.telefono, n.ciudad, n.activo, n.bloqueado, n.bloqueado_motivo,
           n.fecha_vencimiento, n.fecha_registro, n.notas_admin,
           p.nombre_display as plan_nombre, p.color as plan_color, p.id as plan_id,
           r.nombre as rubro_nombre, r.slug as rubro_slug,
           (SELECT COUNT(*) FROM usuarios u WHERE u.negocio_id = n.id) as usuarios_count
    FROM negocios n
    LEFT JOIN planes p ON p.id = n.plan_id
    LEFT JOIN rubros r ON r.id = n.rubro_id
    WHERE $whereStr
    ORDER BY n.fecha_registro DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$negocios = $stmt->fetchAll();

// Para filtros: planes y rubros
$planes = $db->query("SELECT id, nombre_display FROM planes ORDER BY orden, nombre_display")->fetchAll();
$rubros = $db->query("SELECT id, nombre FROM rubros ORDER BY nombre")->fetchAll();

// Contadores del filtro
$counts = [];
foreach (['todos'=>'1=1','activos'=>'activo=1 AND bloqueado=0','bloqueados'=>'bloqueado=1','vencen'=>'fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY)','vencidos'=>'fecha_vencimiento < CURDATE() AND activo=1'] as $k=>$w) {
    $counts[$k] = $db->query("SELECT COUNT(*) FROM negocios WHERE $w")->fetchColumn();
}

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Negocios', 'negocios');
?>

<!-- ── Filtros rápidos ───────────────────────────────────── -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php
    $tabs = [
        'todos'     => ['label'=>'Todos', 'icon'=>'fa-store'],
        'activos'   => ['label'=>'Activos', 'icon'=>'fa-circle-check'],
        'inactivos' => ['label'=>'Inactivos', 'icon'=>'fa-circle-xmark'],
        'bloqueados'=> ['label'=>'Bloqueados', 'icon'=>'fa-ban'],
        'vencen'    => ['label'=>'Vencen 7d', 'icon'=>'fa-clock'],
        'vencidos'  => ['label'=>'Vencidos', 'icon'=>'fa-calendar-times'],
    ];
    foreach ($tabs as $k => $t):
        $active = $filtro === $k;
        $cnt = $counts[$k] ?? '';
    ?>
    <a href="?filtro=<?= $k ?>&q=<?= urlencode($buscar) ?>&plan=<?= $plan_f ?>&rubro=<?= $rubro_f ?>"
       class="sa-btn <?= $active ? 'primary' : 'ghost' ?> sm">
        <i class="fas <?= $t['icon'] ?>"></i>
        <?= $t['label'] ?>
        <?php if ($cnt !== ''): ?><span style="opacity:.7">(<?= $cnt ?>)</span><?php endif; ?>
    </a>
    <?php endforeach; ?>

    <a href="nuevo.php" class="sa-btn primary sm" style="margin-left:auto">
        <i class="fas fa-plus"></i> Nuevo negocio
    </a>
</div>

<!-- ── Buscador y filtros ────────────────────────────────── -->
<div class="sa-filter-bar" style="margin-bottom:20px">
    <div class="sa-search" style="max-width:320px">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Buscar negocio, email..." 
               value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <select class="sa-filter-select" id="planFilter">
        <option value="0">Todos los planes</option>
        <?php foreach ($planes as $pl): ?>
        <option value="<?= $pl['id'] ?>" <?= $plan_f==$pl['id']?'selected':'' ?>>
            <?= htmlspecialchars($pl['nombre_display']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <select class="sa-filter-select" id="rubroFilter">
        <option value="0">Todos los rubros</option>
        <?php foreach ($rubros as $rb): ?>
        <option value="<?= $rb['id'] ?>" <?= $rubro_f==$rb['id']?'selected':'' ?>>
            <?= htmlspecialchars($rb['nombre']) ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- ── Tabla de negocios ─────────────────────────────────── -->
<div class="sa-panel">
    <div class="sa-panel-header">
        <h3>
            <?= number_format($total) ?> negocios
            <?php if ($filtro !== 'todos'): ?>
            <span style="font-weight:400;color:var(--sa-muted);font-size:13px"> · filtro: <?= $filtro ?></span>
            <?php endif; ?>
        </h3>
    </div>
    <div class="sa-table-wrap">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Negocio</th>
                    <th>Rubro</th>
                    <th>Plan</th>
                    <th>Usuarios</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($negocios)): ?>
            <tr><td colspan="8">
                <div class="sa-empty"><i class="fas fa-store"></i><p>No se encontraron negocios con estos filtros</p></div>
            </td></tr>
            <?php else: ?>
            <?php foreach ($negocios as $neg):
                $statusClass = $neg['bloqueado'] ? 'red' : ($neg['activo'] ? 'green' : 'gray');
                $statusLabel = $neg['bloqueado'] ? 'Bloqueado' : ($neg['activo'] ? 'Activo' : 'Inactivo');
                $dias = $neg['fecha_vencimiento'] ? sa_dias_restantes($neg['fecha_vencimiento']) : null;
                $chipClass = is_null($dias) ? '' : ($dias < 0 ? 'exp' : ($dias <= 5 ? 'warn' : 'ok'));
                $iniciales = mb_strtoupper(mb_substr($neg['nombre'], 0, 2));
            ?>
            <tr>
                <td style="color:var(--sa-muted);font-size:11px"><?= $neg['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="sa-negocio-avatar"><?= $iniciales ?></div>
                        <div>
                            <div style="font-weight:600;color:var(--sa-text)"><?= htmlspecialchars($neg['nombre']) ?></div>
                            <div style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars($neg['email'] ?? '') ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px;color:var(--sa-muted)"><?= htmlspecialchars($neg['rubro_nombre'] ?? '—') ?></td>
                <td>
                    <span class="sa-pill gray" style="<?= $neg['plan_color'] ? 'border-color:'.htmlspecialchars($neg['plan_color']).'50;color:'.htmlspecialchars($neg['plan_color']) : '' ?>">
                        <?= htmlspecialchars($neg['plan_nombre'] ?? 'Sin plan') ?>
                    </span>
                </td>
                <td style="text-align:center;color:var(--sa-muted)"><?= $neg['usuarios_count'] ?></td>
                <td>
                    <?php if ($neg['fecha_vencimiento']): ?>
                    <div style="font-size:12px"><?= date('d/m/Y', strtotime($neg['fecha_vencimiento'])) ?></div>
                    <?php if (!is_null($dias)): ?>
                    <span class="dias-chip <?= $chipClass ?>">
                        <?= $dias >= 0 ? "Vence en {$dias}d" : 'Vencido' ?>
                    </span>
                    <?php endif; ?>
                    <?php else: ?>
                    <span style="color:var(--sa-muted)">—</span>
                    <?php endif; ?>
                </td>
                <td><span class="sa-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                        <a href="edit.php?id=<?= $neg['id'] ?>" class="sa-btn ghost sm" title="Editar">
                            <i class="fas fa-pen"></i>
                        </a>
                        <?php if (!$neg['bloqueado']): ?>
                        <button class="sa-btn danger sm" title="Bloquear"
                                onclick="bloquear(<?= $neg['id'] ?>, '<?= htmlspecialchars(addslashes($neg['nombre'])) ?>')">
                            <i class="fas fa-ban"></i>
                        </button>
                        <?php else: ?>
                        <button class="sa-btn secondary sm" title="Desbloquear"
                                onclick="toggleActivo(<?= $neg['id'] ?>, 1, '<?= htmlspecialchars(addslashes($neg['nombre'])) ?>')">
                            <i class="fas fa-lock-open"></i>
                        </button>
                        <?php endif; ?>
                        <button class="sa-btn secondary sm" title="Renovar"
                                onclick="renovar(<?= $neg['id'] ?>, '<?= htmlspecialchars(addslashes($neg['nombre'])) ?>', <?= $neg['plan_id'] ?? 0 ?>)">
                            <i class="fas fa-rotate"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if ($totalPag > 1): ?>
    <div class="sa-pagination">
        <?php if ($page > 1): ?>
        <a href="?filtro=<?= $filtro ?>&q=<?= urlencode($buscar) ?>&plan=<?= $plan_f ?>&rubro=<?= $rubro_f ?>&page=<?= $page-1 ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totalPag,$page+2); $i++): ?>
        <a href="?filtro=<?= $filtro ?>&q=<?= urlencode($buscar) ?>&plan=<?= $plan_f ?>&rubro=<?= $rubro_f ?>&page=<?= $i ?>"
           class="<?= $i===$page?'current':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPag): ?>
        <a href="?filtro=<?= $filtro ?>&q=<?= urlencode($buscar) ?>&plan=<?= $plan_f ?>&rubro=<?= $rubro_f ?>&page=<?= $page+1 ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── Modal Bloquear ────────────────────────────────────── -->
<div class="sa-modal-backdrop" id="modalBloquear">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <h4><i class="fas fa-ban" style="color:var(--sa-danger);margin-right:8px"></i>Bloquear Negocio</h4>
            <button class="sa-modal-close" onclick="sa_closeModal('modalBloquear')"><i class="fas fa-times"></i></button>
        </div>
        <div class="sa-modal-body">
            <p style="color:var(--sa-muted);margin-bottom:16px">Bloqueando: <strong id="bloquearNombre" style="color:var(--sa-text)"></strong></p>
            <div class="sa-form-group">
                <label class="sa-label">Motivo del bloqueo</label>
                <textarea class="sa-textarea" id="bloquearMotivo" placeholder="Ej: Pago pendiente, contrato vencido..."></textarea>
            </div>
        </div>
        <div class="sa-modal-footer">
            <button class="sa-btn ghost" onclick="sa_closeModal('modalBloquear')">Cancelar</button>
            <button class="sa-btn danger" onclick="confirmarBloquear()">
                <i class="fas fa-ban"></i> Bloquear
            </button>
        </div>
    </div>
</div>

<!-- ── Modal Renovar ─────────────────────────────────────── -->
<div class="sa-modal-backdrop" id="modalRenovar">
    <div class="sa-modal">
        <div class="sa-modal-header">
            <h4><i class="fas fa-rotate" style="color:var(--sa-primary);margin-right:8px"></i>Renovar Suscripción</h4>
            <button class="sa-modal-close" onclick="sa_closeModal('modalRenovar')"><i class="fas fa-times"></i></button>
        </div>
        <div class="sa-modal-body">
            <p style="color:var(--sa-muted);margin-bottom:16px">Renovando: <strong id="renovarNombre" style="color:var(--sa-text)"></strong></p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Plan</label>
                    <select class="sa-select" id="renovarPlan">
                        <?php foreach ($planes as $pl): ?>
                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nombre_display']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Meses a agregar</label>
                    <select class="sa-select" id="renovarMeses">
                        <option value="1">1 mes</option>
                        <option value="3">3 meses</option>
                        <option value="6">6 meses</option>
                        <option value="12">12 meses (anual)</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Monto cobrado</label>
                    <input type="number" class="sa-input" id="renovarMonto" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Método de pago</label>
                    <select class="sa-select" id="renovarMetodo">
                        <option value="transferencia">Transferencia</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="mercadopago">MercadoPago</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Referencia / N° comprobante</label>
                <input type="text" class="sa-input" id="renovarRef" placeholder="Ej: CVU, N° transferencia...">
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Notas</label>
                <textarea class="sa-textarea" id="renovarNotas" placeholder="Notas adicionales..." style="min-height:60px"></textarea>
            </div>
        </div>
        <div class="sa-modal-footer">
            <button class="sa-btn ghost" onclick="sa_closeModal('modalRenovar')">Cancelar</button>
            <button class="sa-btn primary" onclick="confirmarRenovar()">
                <i class="fas fa-rotate"></i> Renovar
            </button>
        </div>
    </div>
</div>

<script src="../assets/sa.js"></script>
<script>
let currentNegocioId = null;
let searchTimer = null;

// Búsqueda en tiempo real con debounce 400ms
document.getElementById('searchInput').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') { clearTimeout(searchTimer); applyFilters(); }
});
document.getElementById('planFilter').addEventListener('change', applyFilters);
document.getElementById('rubroFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const q     = document.getElementById('searchInput').value;
    const plan  = document.getElementById('planFilter').value;
    const rubro = document.getElementById('rubroFilter').value;
    const url   = new URL(window.location.href);
    url.searchParams.set('q', q);
    url.searchParams.set('plan', plan);
    url.searchParams.set('rubro', rubro);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

// ── Bloquear ─────────────────────────────────────────────
function bloquear(id, nombre) {
    currentNegocioId = id;
    document.getElementById('bloquearNombre').textContent = nombre;
    document.getElementById('bloquearMotivo').value = '';
    sa_openModal('modalBloquear');
}

async function confirmarBloquear() {
    const motivo = document.getElementById('bloquearMotivo').value.trim();
    if (!motivo) { sa_toast('Escribí un motivo', 'warning'); return; }
    const r = await sa_fetch('../api/negocio_action.php', { id: currentNegocioId, action: 'bloquear', motivo });
    if (r.ok) { sa_toast('Negocio bloqueado'); sa_closeModal('modalBloquear'); setTimeout(()=>location.reload(), 900); }
    else sa_toast(r.error || 'Error', 'error');
}

// ── Toggle activo (desbloquear) ──────────────────────────
async function toggleActivo(id, activo, nombre) {
    sa_confirm(`¿Desbloquear <strong>${nombre}</strong>?`, async () => {
        const r = await sa_fetch('../api/negocio_action.php', { id, action: 'desbloquear' });
        if (r.ok) { sa_toast('Negocio desbloqueado'); setTimeout(()=>location.reload(), 900); }
        else sa_toast(r.error || 'Error', 'error');
    });
}

// ── Renovar ──────────────────────────────────────────────
function renovar(id, nombre, planId) {
    currentNegocioId = id;
    document.getElementById('renovarNombre').textContent = nombre;
    document.getElementById('renovarPlan').value = planId || '';
    document.getElementById('renovarMonto').value = '';
    document.getElementById('renovarNotas').value = '';
    sa_openModal('modalRenovar');
}

async function confirmarRenovar() {
    const data = {
        id:      currentNegocioId,
        action:  'renovar',
        plan_id: document.getElementById('renovarPlan').value,
        meses:   document.getElementById('renovarMeses').value,
        monto:   document.getElementById('renovarMonto').value,
        metodo:  document.getElementById('renovarMetodo').value,
        ref:     document.getElementById('renovarRef').value,
        notas:   document.getElementById('renovarNotas').value,
    };
    const r = await sa_fetch('../api/negocio_action.php', data);
    if (r.ok) { sa_toast('Suscripción renovada ✓'); sa_closeModal('modalRenovar'); setTimeout(()=>location.reload(), 900); }
    else sa_toast(r.error || 'Error', 'error');
}
</script>

<?php sa_layout_end(); ?>
