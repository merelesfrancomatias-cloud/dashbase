<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db  = sa_db();
$id  = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$neg = $db->prepare("
    SELECT n.*, p.id as plan_id_cur, r.nombre as rubro_nombre
    FROM negocios n
    LEFT JOIN planes p ON p.id = n.plan_id
    LEFT JOIN rubros r ON r.id = n.rubro_id
    WHERE n.id = ?
");
$neg->execute([$id]);
$neg = $neg->fetch();
if (!$neg) { header('Location: index.php'); exit; }

$planes = $db->query("SELECT id, nombre_display, color FROM planes ORDER BY orden, nombre_display")->fetchAll();
$rubros = $db->query("SELECT id, nombre, slug FROM rubros ORDER BY nombre")->fetchAll();
$usuarios = $db->prepare("SELECT id, nombre, email, rol, activo FROM usuarios WHERE negocio_id = ? ORDER BY nombre");
$usuarios->execute([$id]);
$usuarios = $usuarios->fetchAll();

$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';

    if ($action === 'update') {
        try {
            $db->prepare("
                UPDATE negocios SET
                    nombre = ?, email = ?, telefono = ?, ciudad = ?, 
                    activo = ?, plan_id = ?, fecha_vencimiento = ?,
                    bloqueado = ?, bloqueado_motivo = ?, notas_admin = ?
                WHERE id = ?
            ")->execute([
                trim($_POST['nombre'] ?? $neg['nombre']),
                trim($_POST['email']  ?? $neg['email']),
                trim($_POST['telefono'] ?? ''),
                trim($_POST['ciudad']   ?? ''),
                isset($_POST['activo']) ? 1 : 0,
                intval($_POST['plan_id']),
                $_POST['fecha_vencimiento'] ?: null,
                isset($_POST['bloqueado']) ? 1 : 0,
                trim($_POST['bloqueado_motivo'] ?? ''),
                trim($_POST['notas_admin'] ?? ''),
                $id,
            ]);
            sa_log('negocio_editado', "Negocio ID $id actualizado", $id);
            // Refrescar datos
            $stmt = $db->prepare("SELECT n.*, r.nombre as rubro_nombre FROM negocios n LEFT JOIN rubros r ON r.id = n.rubro_id WHERE n.id = ?");
            $stmt->execute([$id]);
            $neg   = $stmt->fetch();
            $saved = true;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Editar Negocio', 'negocios');
?>

<div style="margin-bottom:20px">
    <a href="index.php" class="sa-btn ghost sm"><i class="fas fa-arrow-left"></i> Volver a Negocios</a>
</div>

<?php if (!empty($_GET['created'])): ?>
<div style="background:rgba(15,209,134,.1);border:1px solid rgba(15,209,134,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;color:var(--sa-primary);display:flex;align-items:center;gap:10px">
    <i class="fas fa-party-horn" style="font-size:18px"></i>
    <div>
        <strong>¡Negocio creado exitosamente!</strong>
        <div style="font-size:12px;opacity:.8">El negocio y su usuario administrador están listos para usar.</div>
    </div>
</div>
<?php endif; ?>
<?php if ($saved): ?>
<div style="background:rgba(15,209,134,.1);border:1px solid rgba(15,209,134,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-primary);display:flex;align-items:center;gap:8px">
    <i class="fas fa-check-circle"></i> Cambios guardados correctamente.
</div>
<?php endif; ?>
<?php if ($error): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-danger);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px;align-items:start">

    <!-- ── Formulario principal ── -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-store" style="color:var(--sa-primary);margin-right:8px"></i><?= htmlspecialchars($neg['nombre']) ?></h3>
            <span class="sa-pill <?= $neg['bloqueado'] ? 'red' : ($neg['activo'] ? 'green' : 'gray') ?>">
                <?= $neg['bloqueado'] ? 'Bloqueado' : ($neg['activo'] ? 'Activo' : 'Inactivo') ?>
            </span>
        </div>
        <div style="padding:20px">
            <form method="POST">
                <input type="hidden" name="action" value="update">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="sa-form-group" style="grid-column:span 2">
                        <label class="sa-label">Nombre del Negocio</label>
                        <input type="text" name="nombre" class="sa-input" value="<?= htmlspecialchars($neg['nombre']) ?>" required>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-label">Email</label>
                        <input type="email" name="email" class="sa-input" value="<?= htmlspecialchars($neg['email'] ?? '') ?>">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-label">Teléfono</label>
                        <input type="text" name="telefono" class="sa-input" value="<?= htmlspecialchars($neg['telefono'] ?? '') ?>">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-label">Ciudad</label>
                        <input type="text" name="ciudad" class="sa-input" value="<?= htmlspecialchars($neg['ciudad'] ?? '') ?>">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-label">Rubro</label>
                        <input type="text" class="sa-input" value="<?= htmlspecialchars($neg['rubro_nombre'] ?? '—') ?>" disabled style="opacity:.5">
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid var(--sa-border);margin:16px 0">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="sa-form-group">
                        <label class="sa-label">Plan</label>
                        <select name="plan_id" class="sa-select">
                            <option value="">Sin plan</option>
                            <?php foreach ($planes as $pl): ?>
                            <option value="<?= $pl['id'] ?>" <?= $neg['plan_id']==$pl['id']?'selected':'' ?>>
                                <?= htmlspecialchars($pl['nombre_display']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-label">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="sa-input" 
                               value="<?= $neg['fecha_vencimiento'] ?? '' ?>">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:8px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;background:var(--sa-surface2);padding:10px 14px;border-radius:8px;border:1px solid var(--sa-border)">
                        <input type="checkbox" name="activo" <?= $neg['activo']?'checked':'' ?> style="width:16px;height:16px;accent-color:var(--sa-primary)">
                        <span style="font-size:13px;font-weight:600">Negocio activo</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;background:rgba(239,68,68,.05);padding:10px 14px;border-radius:8px;border:1px solid rgba(239,68,68,.2)">
                        <input type="checkbox" name="bloqueado" <?= $neg['bloqueado']?'checked':'' ?> style="width:16px;height:16px;accent-color:var(--sa-danger)">
                        <span style="font-size:13px;font-weight:600;color:var(--sa-danger)">Bloqueado</span>
                    </label>
                </div>

                <div class="sa-form-group">
                    <label class="sa-label">Motivo de bloqueo</label>
                    <input type="text" name="bloqueado_motivo" class="sa-input" 
                           placeholder="Razón del bloqueo..." 
                           value="<?= htmlspecialchars($neg['bloqueado_motivo'] ?? '') ?>">
                </div>

                <div class="sa-form-group">
                    <label class="sa-label">Notas internas (no visibles al cliente)</label>
                    <textarea name="notas_admin" class="sa-textarea"><?= htmlspecialchars($neg['notas_admin'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="sa-btn primary" style="width:100%;justify-content:center">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <!-- ── Info + Usuarios ── -->
    <div>
        <!-- Info -->
        <div class="sa-panel" style="margin-bottom:20px">
            <div class="sa-panel-header"><h3><i class="fas fa-info-circle" style="margin-right:8px;color:var(--sa-info)"></i>Información</h3></div>
            <div style="padding:16px 20px">
                <?php
                $infos = [
                    ['label'=>'ID Negocio',     'value'=> '#'.$neg['id']],
                    ['label'=>'Registrado',      'value'=> date('d/m/Y', strtotime($neg['fecha_registro']))],
                    ['label'=>'Fecha Alta',       'value'=> $neg['fecha_alta'] ? date('d/m/Y', strtotime($neg['fecha_alta'])) : '—'],
                    ['label'=>'Vencimiento',     'value'=> $neg['fecha_vencimiento'] ? date('d/m/Y', strtotime($neg['fecha_vencimiento'])) : '—'],
                    ['label'=>'Días restantes',  'value'=> $neg['fecha_vencimiento'] ? (sa_dias_restantes($neg['fecha_vencimiento']).'d') : '—'],
                ];
                foreach ($infos as $i):
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--sa-border)">
                    <span style="font-size:12px;color:var(--sa-muted)"><?= $i['label'] ?></span>
                    <span style="font-size:12px;font-weight:600;color:var(--sa-text)"><?= $i['value'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Usuarios del negocio -->
        <div class="sa-panel">
            <div class="sa-panel-header">
                <h3><i class="fas fa-users" style="margin-right:8px;color:var(--sa-info)"></i>Usuarios (<?= count($usuarios) ?>)</h3>
            </div>
            <?php if (empty($usuarios)): ?>
            <div class="sa-empty"><i class="fas fa-user"></i><p>Sin usuarios</p></div>
            <?php else: ?>
            <div class="sa-table-wrap">
                <table class="sa-table">
                    <thead><tr><th>Nombre</th><th>Rol</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($u['nombre']) ?></div>
                            <div style="font-size:11px;color:var(--sa-muted)"><?= htmlspecialchars($u['email']) ?></div>
                        </td>
                        <td>
                            <span class="sa-pill <?= $u['rol']==='admin'?'blue':'gray' ?>">
                                <?= $u['rol'] === 'admin' ? 'Admin' : 'Empleado' ?>
                            </span>
                        </td>
                        <td>
                            <span class="sa-pill <?= $u['activo']?'green':'gray' ?>">
                                <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php sa_layout_end(); ?>
