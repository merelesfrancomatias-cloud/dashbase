<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db    = sa_db();
$error = '';
$ok    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid    = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $slug   = trim($_POST['slug']   ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    if (!$nombre || !$slug) { $error = 'Nombre y slug son obligatorios.'; }
    else {
        try {
            if ($pid) {
                $db->prepare("UPDATE rubros SET nombre=?, slug=?, activo=? WHERE id=?")
                   ->execute([$nombre, $slug, $activo, $pid]);
            } else {
                $db->prepare("INSERT INTO rubros (nombre, slug, activo) VALUES (?,?,?)")
                   ->execute([$nombre, $slug, $activo]);
            }
            sa_log($pid ? 'rubro_editado' : 'rubro_creado', "Rubro '$nombre'");
            $ok = true;
        } catch (Exception $e) { $error = $e->getMessage(); }
    }
}

$rubros = $db->query("
    SELECT r.*, (SELECT COUNT(*) FROM negocios n WHERE n.rubro_id = r.id) as negocios_count
    FROM rubros r ORDER BY r.nombre
")->fetchAll();

$editRubro = null;
if (isset($_GET['editar'])) {
    $editRubro = $db->prepare("SELECT * FROM rubros WHERE id = ?");
    $editRubro->execute([intval($_GET['editar'])]);
    $editRubro = $editRubro->fetch();
}

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Rubros', 'rubros');
?>

<?php if ($ok): ?>
<div style="background:rgba(15,209,134,.1);border:1px solid rgba(15,209,134,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-primary);display:flex;align-items:center;gap:8px">
    <i class="fas fa-check-circle"></i> Rubro guardado.
</div>
<?php endif; ?>
<?php if ($error): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-danger);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:24px;align-items:start">
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-tags" style="color:var(--sa-primary);margin-right:8px"></i>Rubros del Sistema</h3>
            <a href="?nuevo=1" class="sa-btn primary sm"><i class="fas fa-plus"></i> Nuevo</a>
        </div>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead><tr><th>Nombre</th><th>Slug</th><th>Negocios</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rubros as $rb): ?>
                <tr>
                    <td style="font-weight:600"><?= htmlspecialchars($rb['nombre']) ?></td>
                    <td><code style="background:var(--sa-surface2);padding:2px 6px;border-radius:4px;font-size:11px"><?= htmlspecialchars($rb['slug']) ?></code></td>
                    <td style="text-align:center"><?= $rb['negocios_count'] ?></td>
                    <td><span class="sa-pill <?= $rb['activo']?'green':'gray' ?>"><?= $rb['activo']?'Activo':'Inactivo' ?></span></td>
                    <td><a href="?editar=<?= $rb['id'] ?>" class="sa-btn ghost sm"><i class="fas fa-pen"></i></a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><?= ($editRubro||isset($_GET['nuevo'])) ? '<i class="fas fa-pen" style="margin-right:8px;color:var(--sa-info)"></i>'.($editRubro?'Editar':'Nuevo').' Rubro' : '<i class="fas fa-tags" style="margin-right:8px"></i>Seleccionar' ?></h3>
        </div>
        <div style="padding:20px">
        <?php if ($editRubro || isset($_GET['nuevo'])): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editRubro['id'] ?? 0 ?>">
            <div class="sa-form-group">
                <label class="sa-label">Nombre *</label>
                <input type="text" name="nombre" class="sa-input" value="<?= htmlspecialchars($editRubro['nombre'] ?? '') ?>" required>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Slug * <small style="color:var(--sa-muted)">(minúsculas, sin espacios)</small></label>
                <input type="text" name="slug" class="sa-input" value="<?= htmlspecialchars($editRubro['slug'] ?? '') ?>" required
                       pattern="[a-z0-9_]+" title="Solo letras minúsculas, números y guiones bajos">
            </div>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:16px">
                <input type="checkbox" name="activo" <?= ($editRubro['activo'] ?? 1) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--sa-primary)">
                <span style="font-size:13px;font-weight:600">Rubro activo</span>
            </label>
            <button type="submit" class="sa-btn primary" style="width:100%;justify-content:center">
                <i class="fas fa-save"></i> Guardar
            </button>
        </form>
        <?php else: ?>
        <div class="sa-empty" style="padding:30px 0"><i class="fas fa-tags"></i><p>Hacé clic en <i class="fas fa-pen"></i> para editar o en <strong>Nuevo</strong>.</p></div>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php sa_layout_end(); ?>
