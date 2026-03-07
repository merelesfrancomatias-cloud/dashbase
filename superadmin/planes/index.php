<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db = sa_db();

$error = '';
$ok    = false;

// Guardar plan editado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid    = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre_display'] ?? '');
    $desc   = trim($_POST['descripcion']    ?? '');
    $pm     = floatval($_POST['precio_mensual'] ?? 0);
    $pa     = floatval($_POST['precio_anual']   ?? 0);
    $dias   = intval($_POST['dias_gratis']      ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $color  = trim($_POST['color'] ?? '#0FD186');
    $orden  = intval($_POST['orden'] ?? 0);

    if (!$nombre) { $error = 'El nombre es obligatorio.'; }
    else {
        if ($pid) {
            $db->prepare("UPDATE planes SET nombre_display=?,descripcion=?,precio_mensual=?,precio_anual=?,dias_gratis=?,activo=?,color=?,orden=? WHERE id=?")
               ->execute([$nombre,$desc,$pm,$pa,$dias,$activo,$color,$orden,$pid]);
        } else {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/','_',$nombre));
            $db->prepare("INSERT INTO planes (nombre,nombre_display,descripcion,precio_mensual,precio_anual,dias_gratis,activo,color,orden) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$slug,$nombre,$desc,$pm,$pa,$dias,$activo,$color,$orden]);
        }
        sa_log($pid ? 'plan_editado' : 'plan_creado', "Plan ID $pid / '$nombre'");
        $ok = true;
    }
}

$planes = $db->query("SELECT p.*, (SELECT COUNT(*) FROM negocios n WHERE n.plan_id = p.id) as negocios_count FROM planes p ORDER BY p.orden, p.nombre_display")->fetchAll();
$editPlan = null;
if (isset($_GET['editar'])) {
    $editPlan = $db->prepare("SELECT * FROM planes WHERE id = ?");
    $editPlan->execute([intval($_GET['editar'])]);
    $editPlan = $editPlan->fetch();
}

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Planes', 'planes');
?>

<?php if ($ok): ?>
<div style="background:rgba(15,209,134,.1);border:1px solid rgba(15,209,134,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-primary);display:flex;align-items:center;gap:8px">
    <i class="fas fa-check-circle"></i> Plan guardado correctamente.
</div>
<?php endif; ?>
<?php if ($error): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-danger);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:24px;align-items:start">

    <!-- ── Lista de planes ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-layer-group" style="color:var(--sa-primary);margin-right:8px"></i>Planes Disponibles</h3>
            <a href="?nuevo=1" class="sa-btn primary sm"><i class="fas fa-plus"></i> Nuevo</a>
        </div>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead><tr><th>Plan</th><th>Precio/mes</th><th>Días gratis</th><th>Negocios</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($planes as $pl): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:10px;height:10px;border-radius:50%;background:<?= htmlspecialchars($pl['color'] ?? '#0FD186') ?>"></div>
                            <strong><?= htmlspecialchars($pl['nombre_display']) ?></strong>
                        </div>
                        <div style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars(mb_substr($pl['descripcion'] ?? '', 0, 50)) ?></div>
                    </td>
                    <td style="font-weight:700;color:var(--sa-primary)">
                        <?= $pl['precio_mensual'] > 0 ? sa_format_money($pl['precio_mensual']) : '<span style="color:var(--sa-muted)">Gratis</span>' ?>
                    </td>
                    <td>
                        <?php if ($pl['dias_gratis'] > 0): ?>
                        <span class="sa-pill green"><?= $pl['dias_gratis'] ?>d</span>
                        <?php else: ?>
                        <span style="color:var(--sa-muted)">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;font-weight:600"><?= $pl['negocios_count'] ?></td>
                    <td>
                        <span class="sa-pill <?= $pl['activo'] ? 'green' : 'gray' ?>">
                            <?= $pl['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <a href="?editar=<?= $pl['id'] ?>" class="sa-btn ghost sm"><i class="fas fa-pen"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Formulario editar/crear ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3>
                <?php if ($editPlan): ?>
                <i class="fas fa-pen" style="color:var(--sa-info);margin-right:8px"></i>Editar Plan
                <?php elseif (isset($_GET['nuevo'])): ?>
                <i class="fas fa-plus" style="color:var(--sa-primary);margin-right:8px"></i>Nuevo Plan
                <?php else: ?>
                <i class="fas fa-layer-group" style="color:var(--sa-muted);margin-right:8px"></i>Seleccioná un plan
                <?php endif; ?>
            </h3>
        </div>
        <div style="padding:20px">
        <?php if ($editPlan || isset($_GET['nuevo'])): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editPlan['id'] ?? 0 ?>">
            <div class="sa-form-group">
                <label class="sa-label">Nombre del Plan *</label>
                <input type="text" name="nombre_display" class="sa-input" value="<?= htmlspecialchars($editPlan['nombre_display'] ?? '') ?>" placeholder="Ej: Pro Mensual" required>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Descripción</label>
                <textarea name="descripcion" class="sa-textarea" style="min-height:60px"><?= htmlspecialchars($editPlan['descripcion'] ?? '') ?></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Precio Mensual</label>
                    <input type="number" name="precio_mensual" class="sa-input" value="<?= $editPlan['precio_mensual'] ?? 0 ?>" step="0.01" min="0">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Precio Anual</label>
                    <input type="number" name="precio_anual" class="sa-input" value="<?= $editPlan['precio_anual'] ?? 0 ?>" step="0.01" min="0">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Días Gratis</label>
                    <input type="number" name="dias_gratis" class="sa-input" value="<?= $editPlan['dias_gratis'] ?? 0 ?>" min="0">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Orden</label>
                    <input type="number" name="orden" class="sa-input" value="<?= $editPlan['orden'] ?? 0 ?>" min="0">
                </div>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Color</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="color" name="color" value="<?= $editPlan['color'] ?? '#0FD186' ?>" style="width:40px;height:38px;border-radius:8px;border:1px solid var(--sa-border);background:var(--sa-surface2);cursor:pointer;padding:2px">
                    <input type="text" id="colorText" class="sa-input" value="<?= $editPlan['color'] ?? '#0FD186' ?>" style="flex:1" oninput="document.querySelector('[type=color]').value=this.value">
                </div>
            </div>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:16px">
                <input type="checkbox" name="activo" <?= ($editPlan['activo'] ?? 1) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--sa-primary)">
                <span style="font-size:13px;font-weight:600">Plan activo (visible para asignar)</span>
            </label>
            <button type="submit" class="sa-btn primary" style="width:100%;justify-content:center">
                <i class="fas fa-save"></i> Guardar Plan
            </button>
        </form>
        <?php else: ?>
        <div class="sa-empty" style="padding:30px 0"><i class="fas fa-layer-group"></i><p>Hacé clic en <i class="fas fa-pen"></i> para editar un plan o en <strong>Nuevo</strong> para crear uno.</p></div>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php sa_layout_end(); ?>
