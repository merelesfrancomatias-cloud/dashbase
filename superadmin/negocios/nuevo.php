<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

$db     = sa_db();
$error  = '';

$planes  = $db->query("SELECT id, nombre_display, dias_gratis FROM planes WHERE activo=1 ORDER BY orden, nombre_display")->fetchAll();
$rubros  = $db->query("SELECT id, nombre, slug FROM rubros ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $negNombre   = trim($_POST['neg_nombre']    ?? '');
    $negEmail    = trim($_POST['neg_email']     ?? '');
    $negTel      = trim($_POST['neg_telefono']  ?? '');
    $negCiudad   = trim($_POST['neg_ciudad']    ?? '');
    $negProvincia= trim($_POST['neg_provincia'] ?? '');
    $negRubroId  = intval($_POST['neg_rubro_id']?? 0);
    $negPlanId   = intval($_POST['neg_plan_id'] ?? 0);
    $negNotas    = trim($_POST['neg_notas']     ?? '');
    $uNombre     = trim($_POST['u_nombre']      ?? '');
    $uApellido   = trim($_POST['u_apellido']    ?? '');
    $uUsuario    = trim($_POST['u_usuario']     ?? '');
    $uEmail      = trim($_POST['u_email']       ?? '');
    $uPassword   = trim($_POST['u_password']    ?? '');

    if (!$negNombre)                          $error = 'El nombre del negocio es obligatorio.';
    elseif (!$negRubroId)                     $error = 'Seleccioná un rubro.';
    elseif (!$negPlanId)                      $error = 'Seleccioná un plan.';
    elseif (!$uNombre)                        $error = 'El nombre del administrador es obligatorio.';
    elseif (!$uUsuario)                       $error = 'El nombre de usuario es obligatorio.';
    elseif (!$uPassword || strlen($uPassword) < 6) $error = 'La contraseña debe tener al menos 6 caracteres.';
    else {
        $dup = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
        $dup->execute([$uUsuario]);
        if ($dup->fetchColumn() > 0) $error = "El usuario '$uUsuario' ya existe.";
    }

    if (!$error) {
        try {
            $db->beginTransaction();
            $plan = $db->prepare("SELECT dias_gratis FROM planes WHERE id = ?");
            $plan->execute([$negPlanId]);
            $plan = $plan->fetch();
            $diasGratis = intval($plan['dias_gratis'] ?? 0);
            $fechaAlta  = date('Y-m-d');
            $fechaVence = $diasGratis > 0 ? date('Y-m-d', strtotime("+{$diasGratis} days")) : null;
            $estadoSub  = $diasGratis > 0 ? 'trial' : 'activa';

            $rubroRow = $db->prepare("SELECT nombre FROM rubros WHERE id = ?");
            $rubroRow->execute([$negRubroId]);
            $rubroNombre = $rubroRow->fetchColumn() ?: '';

            $db->prepare("
                INSERT INTO negocios (nombre, email, telefono, ciudad, provincia, rubro_id, rubro,
                    activo, plan_id, fecha_alta, fecha_vencimiento, trial_hasta, estado_suscripcion, notas_admin)
                VALUES (?,?,?,?,?,?,?,1,?,?,?,?,?,?)
            ")->execute([$negNombre,$negEmail,$negTel,$negCiudad,$negProvincia,$negRubroId,$rubroNombre,
                         $negPlanId,$fechaAlta,$fechaVence,$fechaVence,$estadoSub,$negNotas]);
            $negocioId = $db->lastInsertId();

            $db->prepare("
                INSERT INTO usuarios (negocio_id, nombre, apellido, usuario, email, password, rol, activo)
                VALUES (?,?,?,?,?,?,?,1)
            ")->execute([$negocioId,$uNombre,$uApellido,$uUsuario,$uEmail,password_hash($uPassword,PASSWORD_BCRYPT),'admin']);

            $db->commit();
            sa_log('negocio_creado', "Negocio '$negNombre' (ID $negocioId) creado, usuario '$uUsuario'", $negocioId);
            header("Location: edit.php?id={$negocioId}&created=1");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error al crear: ' . $e->getMessage();
        }
    }
}

require_once dirname(__DIR__) . '/_layout.php';
sa_layout_start('Nuevo Negocio', 'negocios');
?>

<div style="margin-bottom:20px">
    <a href="index.php" class="sa-btn ghost sm"><i class="fas fa-arrow-left"></i> Volver a Negocios</a>
</div>

<?php if ($error): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;color:var(--sa-danger);display:flex;align-items:center;gap:8px">
    <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">

    <!-- Datos del Negocio -->
    <div class="sa-panel">
        <div class="sa-panel-header">
            <h3><i class="fas fa-store" style="color:var(--sa-primary);margin-right:8px"></i>Datos del Negocio</h3>
        </div>
        <div style="padding:20px">
            <div class="sa-form-group">
                <label class="sa-label">Nombre del Negocio *</label>
                <input type="text" name="neg_nombre" class="sa-input" value="<?= htmlspecialchars($_POST['neg_nombre'] ?? '') ?>"
                       placeholder="Ej: Farmacia Del Centro" required autofocus>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="sa-form-group">
                    <label class="sa-label">Email</label>
                    <input type="email" name="neg_email" class="sa-input" value="<?= htmlspecialchars($_POST['neg_email'] ?? '') ?>" placeholder="negocio@email.com">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Teléfono</label>
                    <input type="text" name="neg_telefono" class="sa-input" value="<?= htmlspecialchars($_POST['neg_telefono'] ?? '') ?>" placeholder="+54 9 ...">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Ciudad</label>
                    <input type="text" name="neg_ciudad" class="sa-input" value="<?= htmlspecialchars($_POST['neg_ciudad'] ?? '') ?>" placeholder="Córdoba">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Provincia</label>
                    <input type="text" name="neg_provincia" class="sa-input" value="<?= htmlspecialchars($_POST['neg_provincia'] ?? '') ?>" placeholder="Córdoba">
                </div>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Rubro *</label>
                <select name="neg_rubro_id" class="sa-select" required>
                    <option value="">— Seleccioná el rubro —</option>
                    <?php foreach ($rubros as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= ($_POST['neg_rubro_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Plan *</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">
                    <?php foreach ($planes as $pl):
                        $isFirst = $pl === $planes[0];
                        $checked = ($_POST['neg_plan_id'] ?? ($isFirst ? $pl['id'] : '')) == $pl['id'];
                    ?>
                    <label style="cursor:pointer">
                        <input type="radio" name="neg_plan_id" value="<?= $pl['id'] ?>" <?= $checked ? 'checked' : '' ?> style="display:none" class="plan-radio">
                        <div class="plan-card<?= $checked ? ' selected' : '' ?>" style="border:1.5px solid var(--sa-border);border-radius:10px;padding:10px;text-align:center;transition:.2s;background:var(--sa-surface2)">
                            <div style="font-size:12px;font-weight:700;color:var(--sa-text)"><?= htmlspecialchars($pl['nombre_display']) ?></div>
                            <?php if ($pl['dias_gratis'] > 0): ?>
                            <div style="font-size:10px;color:var(--sa-primary);margin-top:3px"><?= $pl['dias_gratis'] ?> días gratis</div>
                            <?php endif; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="sa-form-group">
                <label class="sa-label">Notas internas</label>
                <textarea name="neg_notas" class="sa-textarea" style="min-height:60px" placeholder="Observaciones..."><?= htmlspecialchars($_POST['neg_notas'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Usuario Admin + Botón -->
    <div>
        <div class="sa-panel" style="margin-bottom:20px">
            <div class="sa-panel-header">
                <h3><i class="fas fa-user-shield" style="color:var(--sa-info);margin-right:8px"></i>Usuario Administrador</h3>
            </div>
            <div style="padding:20px">
                <div style="background:var(--sa-primary-dim);border:1px solid rgba(15,209,134,.2);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:var(--sa-primary)">
                    <i class="fas fa-info-circle"></i> Este usuario tendrá acceso total al negocio.
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="sa-form-group">
                        <label class="sa-label">Nombre *</label>
                        <input type="text" name="u_nombre" class="sa-input" value="<?= htmlspecialchars($_POST['u_nombre'] ?? '') ?>" required>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-label">Apellido</label>
                        <input type="text" name="u_apellido" class="sa-input" value="<?= htmlspecialchars($_POST['u_apellido'] ?? '') ?>">
                    </div>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Nombre de Usuario * <small style="color:var(--sa-muted)">(para ingresar)</small></label>
                    <input type="text" name="u_usuario" id="uUsuario" class="sa-input"
                           value="<?= htmlspecialchars($_POST['u_usuario'] ?? '') ?>"
                           placeholder="ej: farmacia_centro"
                           oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9_]/g,'')"
                           required>
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Email</label>
                    <input type="email" name="u_email" class="sa-input" value="<?= htmlspecialchars($_POST['u_email'] ?? '') ?>">
                </div>
                <div class="sa-form-group">
                    <label class="sa-label">Contraseña * <small style="color:var(--sa-muted)">(mín. 6 caracteres)</small></label>
                    <div style="position:relative">
                        <input type="password" name="u_password" id="uPwd" class="sa-input"
                               value="<?= htmlspecialchars($_POST['u_password'] ?? '') ?>"
                               placeholder="••••••••" style="padding-right:40px" required minlength="6">
                        <button type="button" onclick="togglePwd()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--sa-muted);cursor:pointer;font-size:13px">
                            <i class="fas fa-eye" id="pwdIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="sa-btn ghost sm" onclick="genPassword()">
                    <i class="fas fa-dice"></i> Generar contraseña
                </button>
                <div id="pwdPreview" style="display:none;font-size:12px;color:var(--sa-warning);margin-top:8px;background:rgba(245,158,11,.08);padding:8px 12px;border-radius:6px;border:1px solid rgba(245,158,11,.2)">
                    <i class="fas fa-key"></i> Contraseña: <strong id="pwdText"></strong>
                    <span style="color:var(--sa-muted)"> — Guardala antes de continuar</span>
                </div>
            </div>
        </div>

        <button type="submit" class="sa-btn primary" style="width:100%;justify-content:center;padding:14px;font-size:15px">
            <i class="fas fa-plus-circle"></i> Crear Negocio y Usuario Admin
        </button>
        <p style="text-align:center;margin-top:10px;font-size:11px;color:var(--sa-muted)">
            El negocio quedará activo inmediatamente con el plan seleccionado.
        </p>
    </div>

</div>
</form>

<style>
.plan-card.selected { border-color:var(--sa-primary)!important; background:var(--sa-primary-dim)!important; }
.plan-card:hover    { border-color:var(--sa-primary)!important; }
</style>
<script src="../assets/sa.js"></script>
<script>
document.querySelectorAll('.plan-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
        radio.nextElementSibling.classList.add('selected');
    });
});
document.querySelector('[name=neg_nombre]').addEventListener('input', function() {
    const u = document.getElementById('uUsuario');
    if (!u.value) {
        u.value = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
            .replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'').slice(0,30);
    }
});
function togglePwd() {
    const inp = document.getElementById('uPwd'), icon = document.getElementById('pwdIcon');
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
function genPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#$';
    let pwd = '';
    for (let i=0; i<10; i++) pwd += chars[Math.floor(Math.random()*chars.length)];
    const inp = document.getElementById('uPwd');
    inp.value = pwd; inp.type = 'text';
    document.getElementById('pwdIcon').className = 'fas fa-eye-slash';
    document.getElementById('pwdText').textContent = pwd;
    document.getElementById('pwdPreview').style.display = 'block';
}
</script>
<?php sa_layout_end(); ?>
