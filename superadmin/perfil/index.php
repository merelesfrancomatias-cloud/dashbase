<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db      = sa_db();
$saId    = $_SESSION['sa_id'];
$msgOk   = '';
$msgErr  = '';

$sa = $db->prepare("SELECT * FROM superadmin_users WHERE id = ?");
$sa->execute([$saId]);
$sa = $sa->fetch();

// ---- POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'datos') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        if (!$nombre)           $msgErr = 'El nombre es obligatorio.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $msgErr = 'Email inválido.';
        else {
            $dup = $db->prepare("SELECT COUNT(*) FROM superadmin_users WHERE email=? AND id!=?");
            $dup->execute([$email, $saId]);
            if ($dup->fetchColumn() > 0) $msgErr = 'Ese email ya está en uso.';
            else {
                $db->prepare("UPDATE superadmin_users SET nombre=?, email=? WHERE id=?")
                   ->execute([$nombre, $email, $saId]);
                $_SESSION['sa_nombre'] = $nombre;
                $sa['nombre'] = $nombre;
                $sa['email']  = $email;
                sa_log('perfil_actualizado', "Datos personales actualizados");
                $msgOk = 'Datos actualizados correctamente.';
            }
        }
    }

    if ($action === 'password') {
        $actual  = $_POST['pwd_actual']  ?? '';
        $nueva   = $_POST['pwd_nueva']   ?? '';
        $confirm = $_POST['pwd_confirm'] ?? '';
        if (!password_verify($actual, $sa['password']))       $msgErr = 'La contraseña actual es incorrecta.';
        elseif (strlen($nueva) < 6)                           $msgErr = 'La nueva contraseña debe tener al menos 6 caracteres.';
        elseif ($nueva !== $confirm)                          $msgErr = 'Las contraseñas no coinciden.';
        else {
            $db->prepare("UPDATE superadmin_users SET password=? WHERE id=?")
               ->execute([password_hash($nueva, PASSWORD_BCRYPT), $saId]);
            sa_log('password_cambiado', "Contraseña actualizada");
            $msgOk = '¡Contraseña actualizada correctamente!';
        }
    }
}

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Mi Perfil', 'perfil');
?>

<div style="max-width:720px">

<?php if ($msgOk): ?>
<div style="background:rgba(15,209,134,.1);border:1px solid rgba(15,209,134,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-primary);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-check"></i> <?= htmlspecialchars($msgOk) ?>
</div>
<?php endif; ?>
<?php if ($msgErr): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-danger);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($msgErr) ?>
</div>
<?php endif; ?>

<!-- Avatar / info cabecera -->
<div class="sa-panel" style="margin-bottom:20px">
    <div style="padding:24px;display:flex;align-items:center;gap:20px">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--sa-primary),#0aa866);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#000;flex-shrink:0">
            <?= strtoupper(substr($sa['nombre'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
            <div style="font-size:20px;font-weight:700;color:var(--sa-text)"><?= htmlspecialchars($sa['nombre']) ?></div>
            <div style="font-size:13px;color:var(--sa-muted);margin-top:2px"><?= htmlspecialchars($sa['email']) ?></div>
            <div style="margin-top:6px"><span class="sa-pill green">Super Administrador</span></div>
        </div>
        <div style="margin-left:auto;text-align:right">
            <div style="font-size:11px;color:var(--sa-muted)">Miembro desde</div>
            <div style="font-size:13px;font-weight:600;color:var(--sa-text)">
                <?= !empty($sa['created_at']) ? date('d/m/Y', strtotime($sa['created_at'])) : '—' ?>
            </div>
            <div style="font-size:11px;color:var(--sa-muted);margin-top:4px">Último acceso</div>
            <div style="font-size:12px;color:var(--sa-text)">
                <?= !empty($sa['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($sa['ultimo_acceso'])) : '—' ?>
            </div>
        </div>
    </div>
</div>

<!-- Datos personales -->
<div class="sa-panel" style="margin-bottom:20px">
    <div class="sa-panel-header">
        <h3><i class="fas fa-user" style="color:var(--sa-primary);margin-right:8px"></i>Datos Personales</h3>
    </div>
    <div style="padding:24px">
        <form method="POST">
            <input type="hidden" name="action" value="datos">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="sa-form-group">
                    <label class="sa-label">Nombre completo *</label>
                    <input type="text" name="nombre" class="sa-input"
                           value="<?= htmlspecialchars($sa['nombre']) ?>" required>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Email *</label>
                    <input type="email" name="email" class="sa-input"
                           value="<?= htmlspecialchars($sa['email']) ?>" required>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="sa-btn primary">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cambiar contraseña -->
<div class="sa-panel">
    <div class="sa-panel-header">
        <h3><i class="fas fa-lock" style="color:var(--sa-warning);margin-right:8px"></i>Cambiar Contraseña</h3>
    </div>
    <div style="padding:24px">
        <form method="POST">
            <input type="hidden" name="action" value="password">
            <div class="sa-form-group">
                <label class="sa-label">Contraseña actual *</label>
                <div style="position:relative">
                    <input type="password" name="pwd_actual" id="pwdActual" class="sa-input" style="padding-right:40px" required>
                    <button type="button" onclick="toggleField('pwdActual','iconActual')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--sa-muted);cursor:pointer">
                        <i class="fas fa-eye" id="iconActual"></i>
                    </button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="sa-form-group">
                    <label class="sa-label">Nueva contraseña *</label>
                    <div style="position:relative">
                        <input type="password" name="pwd_nueva" id="pwdNueva" class="sa-input" style="padding-right:40px" minlength="6" required>
                        <button type="button" onclick="toggleField('pwdNueva','iconNueva')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--sa-muted);cursor:pointer">
                            <i class="fas fa-eye" id="iconNueva"></i>
                        </button>
                    </div>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Confirmar contraseña *</label>
                    <input type="password" name="pwd_confirm" class="sa-input" minlength="6" required>
                </div>
            </div>
            <div id="pwdStrength" style="height:4px;border-radius:2px;background:var(--sa-border);margin:-8px 0 16px;overflow:hidden">
                <div id="pwdStrengthBar" style="height:100%;width:0;transition:.3s;border-radius:2px"></div>
            </div>
            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="sa-btn warning">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>

</div><!-- /max-width -->

<script>
function toggleField(inputId, iconId) {
    const inp = document.getElementById(inputId), icon = document.getElementById(iconId);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
// Barra de fuerza de contraseña
document.querySelector('[name=pwd_nueva]').addEventListener('input', function() {
    const v = this.value, bar = document.getElementById('pwdStrengthBar');
    let score = 0;
    if (v.length >= 6)  score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const pct = (score/5)*100;
    const color = pct < 40 ? '#ef4444' : pct < 70 ? '#f59e0b' : '#0fd186';
    bar.style.width = pct + '%';
    bar.style.background = color;
});
</script>
<?php sa_layout_end(); ?>
