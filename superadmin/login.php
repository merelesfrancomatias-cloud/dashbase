<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (!empty($_SESSION['sa_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/_auth.php';

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password) {
        try {
            $db   = sa_db();
            $stmt = $db->prepare("SELECT * FROM superadmin_users WHERE email = ? AND activo = 1 LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['sa_id']     = $admin['id'];
                $_SESSION['sa_nombre'] = $admin['nombre'];
                $_SESSION['sa_email']  = $admin['email'];

                // Actualizar último login
                $db->prepare("UPDATE superadmin_users SET ultimo_login = NOW() WHERE id = ?")
                   ->execute([$admin['id']]);

                // Log
                sa_log('login_superadmin', 'Login exitoso desde ' . ($_SERVER['REMOTE_ADDR'] ?? ''));

                header('Location: index.php');
                exit;
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (Exception $e) {
            $error = 'Error de conexión. Verificá que XAMPP esté corriendo.';
        }
    } else {
        $error = 'Completá todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — DASH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/sa.css">
    <style>
        .sa-login-card { animation: fadeUp .4s ease; }
        @keyframes fadeUp { from { transform: translateY(20px); opacity: 0; } to { transform: none; opacity: 1; } }
        .pwd-wrap { position: relative; }
        .pwd-wrap .sa-input { padding-right: 40px; }
        .pwd-toggle {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: var(--sa-muted); cursor: pointer;
            padding: 4px; font-size: 13px;
        }
        .pwd-toggle:hover { color: var(--sa-text); }
    </style>
</head>
<body>
<div class="sa-login-page">
    <div class="sa-login-card">
        <div class="logo-area">
            <img src="assets/dashlogo.png" alt="DASH" style="height:56px;width:auto;object-fit:contain;filter:brightness(0) invert(1);display:block;margin:0 auto 16px;">
            <p style="font-size:12px;color:var(--sa-muted);margin-top:2px">Panel de Super Administración</p>
        </div>

        <?php if ($error): ?>
        <div class="sa-error-msg show">
            <i class="fas fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="sa-form-group">
                <label class="sa-label">Email</label>
                <div style="position:relative">
                    <i class="fas fa-envelope" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--sa-muted);font-size:13px;pointer-events:none"></i>
                    <input type="email" name="email" class="sa-input" style="padding-left:34px" 
                           placeholder="admin@dashbase.com" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required autofocus>
                </div>
            </div>

            <div class="sa-form-group">
                <label class="sa-label">Contraseña</label>
                <div class="pwd-wrap">
                    <input type="password" name="password" id="pwdInput" class="sa-input" 
                           placeholder="••••••••" required>
                    <button type="button" class="pwd-toggle" onclick="togglePwd()">
                        <i class="fas fa-eye" id="pwdIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="sa-btn primary" style="width:100%;justify-content:center;padding:12px;font-size:14px;margin-top:8px">
                <i class="fas fa-right-to-bracket"></i> Ingresar al Panel
            </button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:11px;color:var(--sa-muted)">
            Acceso restringido — Solo personal autorizado
        </p>
    </div>
</div>
<script>
function togglePwd() {
    const inp  = document.getElementById('pwdInput');
    const icon = document.getElementById('pwdIcon');
    const show = inp.type === 'password';
    inp.type  = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
</body>
</html>
