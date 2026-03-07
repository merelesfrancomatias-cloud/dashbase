<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db     = sa_db();
$msgOk  = '';
$msgErr = '';

// ----- POST: registrar pago manual -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'registrar_pago') {
    $pNegId   = intval($_POST['p_negocio_id'] ?? 0);
    $pPlanId  = intval($_POST['p_plan_id']    ?? 0);
    $pMonto   = floatval(str_replace(',','.',trim($_POST['p_monto'] ?? '')));
    $pMetodo  = trim($_POST['p_metodo']       ?? 'efectivo');
    $pRef     = trim($_POST['p_referencia']   ?? '');
    $pFecha   = trim($_POST['p_fecha']        ?? date('Y-m-d'));
    $pDesde   = trim($_POST['p_desde']        ?? date('Y-m-d'));
    $pHasta   = trim($_POST['p_hasta']        ?? '');
    $pNotas   = trim($_POST['p_notas']        ?? '');

    if (!$pNegId)   $msgErr = 'Seleccioná un negocio.';
    elseif (!$pPlanId) $msgErr = 'Seleccioná un plan.';
    elseif ($pMonto <= 0) $msgErr = 'El monto debe ser mayor a 0.';
    elseif (!$pHasta)    $msgErr = 'Ingresá la fecha de vencimiento.';
    else {
        try {
            $db->prepare("
                INSERT INTO pagos (negocio_id, plan_id, monto, metodo_pago, referencia,
                                   fecha_pago, fecha_desde, fecha_hasta, notas)
                VALUES (?,?,?,?,?,?,?,?,?)
            ")->execute([$pNegId,$pPlanId,$pMonto,$pMetodo,$pRef,$pFecha,$pDesde,$pHasta,$pNotas]);

            // Actualizar negocio con el nuevo plan y vencimiento
            $db->prepare("UPDATE negocios SET plan_id=?, fecha_vencimiento=?, estado_suscripcion='activa', activo=1, bloqueado=0 WHERE id=?")
               ->execute([$pPlanId, $pHasta, $pNegId]);

            $negRow = $db->prepare("SELECT nombre FROM negocios WHERE id=?");
            $negRow->execute([$pNegId]);
            $negNom = $negRow->fetchColumn();
            sa_log('pago_registrado', "Pago \${$pMonto} via $pMetodo — negocio '$negNom' hasta $pHasta", $pNegId);
            $msgOk = "Pago registrado y vencimiento actualizado hasta " . date('d/m/Y', strtotime($pHasta)) . ".";
        } catch (Exception $e) {
            $msgErr = 'Error: ' . $e->getMessage();
        }
    }
}

$page   = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$negId  = intval($_GET['negocio'] ?? 0);

$where  = $negId ? "WHERE pg.negocio_id = $negId" : '';
$total  = $db->query("SELECT COUNT(*) FROM pagos pg $where")->fetchColumn();
$totPag = ceil($total / $perPage);
$offset = ($page-1) * $perPage;

$pagos  = $db->query("
    SELECT pg.*, n.nombre as negocio_nombre, p.nombre_display as plan_nombre
    FROM pagos pg
    LEFT JOIN negocios n ON n.id = pg.negocio_id
    LEFT JOIN planes p   ON p.id = pg.plan_id
    $where
    ORDER BY pg.created_at DESC
    LIMIT $perPage OFFSET $offset
")->fetchAll();

$totalIngresos = $db->query("SELECT COALESCE(SUM(monto),0) FROM pagos")->fetchColumn();
$ingresosMes   = $db->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE YEAR(fecha_pago)=YEAR(CURDATE()) AND MONTH(fecha_pago)=MONTH(CURDATE())")->fetchColumn();
$negocios      = $db->query("SELECT id, nombre FROM negocios ORDER BY nombre")->fetchAll();
$planesAll     = $db->query("SELECT id, nombre_display FROM planes WHERE activo=1 ORDER BY orden, nombre_display")->fetchAll();

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Pagos', 'pagos');
?>

<div class="sa-stats-grid" style="margin-bottom:24px">
    <div class="sa-stat-card">
        <div class="sa-stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= sa_format_money((float)$ingresosMes) ?></div>
            <div class="label">Ingresos del Mes</div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon purple"><i class="fas fa-wallet"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= sa_format_money((float)$totalIngresos) ?></div>
            <div class="label">Total Histórico</div>
        </div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-icon blue"><i class="fas fa-receipt"></i></div>
        <div class="sa-stat-body">
            <div class="value"><?= $total ?></div>
            <div class="label">Pagos Registrados</div>
        </div>
    </div>
</div>

<?php if ($msgOk): ?>
<div style="background:rgba(15,209,134,.1);border:1px solid rgba(15,209,134,.3);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:var(--sa-primary);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-check"></i> <?= htmlspecialchars($msgOk) ?>
</div>
<?php endif; ?>
<?php if ($msgErr): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:12px 16px;margin-bottom:16px;color:var(--sa-danger);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($msgErr) ?>
</div>
<?php endif; ?>

<div class="sa-panel">
    <div class="sa-panel-header">
        <h3><i class="fas fa-dollar-sign" style="color:var(--sa-primary);margin-right:8px"></i>Historial de Pagos</h3>
        <div class="sa-panel-actions">
            <select class="sa-filter-select" onchange="location.href='?negocio='+this.value">
                <option value="0">Todos los negocios</option>
                <?php foreach ($negocios as $n): ?>
                <option value="<?= $n['id'] ?>" <?= $negId==$n['id']?'selected':'' ?>>
                    <?= htmlspecialchars($n['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button class="sa-btn primary sm" onclick="document.getElementById('modalPago').style.display='flex'">
                <i class="fas fa-plus"></i> Registrar Pago
            </button>
        </div>
    </div>
    <div class="sa-table-wrap">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Negocio</th>
                    <th>Plan</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th>Fecha Pago</th>
                    <th>Vigencia</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($pagos)): ?>
            <tr><td colspan="8">
                <div class="sa-empty"><i class="fas fa-receipt"></i><p>Sin pagos registrados</p></div>
            </td></tr>
            <?php else: ?>
            <?php foreach ($pagos as $pg): ?>
            <tr>
                <td style="color:var(--sa-muted);font-size:11px"><?= $pg['id'] ?></td>
                <td style="font-weight:600"><?= htmlspecialchars($pg['negocio_nombre'] ?? '—') ?></td>
                <td><span class="sa-pill gray"><?= htmlspecialchars($pg['plan_nombre'] ?? '—') ?></span></td>
                <td style="font-weight:700;color:var(--sa-primary)"><?= sa_format_money($pg['monto']) ?></td>
                <td>
                    <?php
                    $metodoBadge = ['efectivo'=>'green','transferencia'=>'blue','mercadopago'=>'purple','otro'=>'gray'];
                    $clase = $metodoBadge[$pg['metodo_pago']] ?? 'gray';
                    ?>
                    <span class="sa-pill <?= $clase ?>"><?= htmlspecialchars($pg['metodo_pago']) ?></span>
                </td>
                <td style="font-size:12px;color:var(--sa-muted)"><?= htmlspecialchars($pg['referencia'] ?? '—') ?></td>
                <td style="font-size:12px"><?= date('d/m/Y', strtotime($pg['fecha_pago'])) ?></td>
                <td style="font-size:11px;color:var(--sa-muted)">
                    <?= date('d/m', strtotime($pg['fecha_desde'])) ?> → <?= date('d/m/Y', strtotime($pg['fecha_hasta'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totPag > 1): ?>
    <div class="sa-pagination">
        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&negocio=<?= $negId ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totPag,$page+2); $i++): ?>
        <a href="?page=<?= $i ?>&negocio=<?= $negId ?>" class="<?= $i===$page?'current':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totPag): ?><a href="?page=<?= $page+1 ?>&negocio=<?= $negId ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Registrar Pago Manual -->
<div id="modalPago" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--sa-surface);border:1px solid var(--sa-border);border-radius:14px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;margin:16px">
        <div style="padding:20px 24px;border-bottom:1px solid var(--sa-border);display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:16px"><i class="fas fa-dollar-sign" style="color:var(--sa-primary);margin-right:8px"></i>Registrar Pago Manual</h3>
            <button onclick="document.getElementById('modalPago').style.display='none'" style="background:none;border:none;color:var(--sa-muted);cursor:pointer;font-size:18px"><i class="fas fa-xmark"></i></button>
        </div>
        <form method="POST" style="padding:24px">
            <input type="hidden" name="action" value="registrar_pago">
            <div class="sa-form-group">
                <label class="sa-label">Negocio *</label>
                <select name="p_negocio_id" class="sa-select" required id="p_neg_sel">
                    <option value="">— Seleccioná —</option>
                    <?php foreach ($negocios as $n): ?>
                    <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Plan *</label>
                    <select name="p_plan_id" class="sa-select" required>
                        <option value="">— Seleccioná —</option>
                        <?php foreach ($planesAll as $pl): ?>
                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nombre_display']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Monto ($) *</label>
                    <input type="number" name="p_monto" class="sa-input" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Método de Pago</label>
                    <select name="p_metodo" class="sa-select">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="mercadopago">MercadoPago</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Referencia / Comprobante</label>
                    <input type="text" name="p_referencia" class="sa-input" placeholder="Nro. de transferencia...">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Fecha de Pago</label>
                    <input type="date" name="p_fecha" class="sa-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Vigencia Desde</label>
                    <input type="date" name="p_desde" class="sa-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Vence el *</label>
                    <input type="date" name="p_hasta" id="p_hasta" class="sa-input" required>
                </div>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Notas internas</label>
                <textarea name="p_notas" class="sa-textarea" style="min-height:50px" placeholder="Observaciones..."></textarea>
            </div>
            <div style="background:var(--sa-surface2);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:var(--sa-muted)">
                <i class="fas fa-info-circle" style="color:var(--sa-primary)"></i>
                Al guardar se actualizará el plan y la fecha de vencimiento del negocio automáticamente.
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="sa-btn ghost" onclick="document.getElementById('modalPago').style.display='none'">Cancelar</button>
                <button type="submit" class="sa-btn primary"><i class="fas fa-save"></i> Guardar Pago</button>
            </div>
        </form>
    </div>
</div>
<script>
// Cerrar modal con Escape
document.addEventListener('keydown', e => { if(e.key==='Escape') document.getElementById('modalPago').style.display='none'; });
// Auto-calcular vencimiento +30 días al elegir negocio
document.getElementById('p_neg_sel') && document.getElementById('p_neg_sel').addEventListener('change', function() {
    if (!document.getElementById('p_hasta').value) {
        const d = new Date(); d.setDate(d.getDate()+30);
        document.getElementById('p_hasta').value = d.toISOString().slice(0,10);
    }
});
<?php if ($msgErr && strpos($msgErr,'Error') === false): ?>
// Reabrir modal si hubo error de validación
window.addEventListener('DOMContentLoaded', () => document.getElementById('modalPago').style.display='flex');
<?php endif; ?>
</script>

<?php sa_layout_end(); ?>
