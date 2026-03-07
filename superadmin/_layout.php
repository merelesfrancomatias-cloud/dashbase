<?php
// ============================================================
// Super Admin — Layout header (incluir al inicio de cada vista)
// Uso: sa_layout_start('Título de Página', 'nav-key');
// ============================================================
function sa_layout_start(string $title, string $activeKey = '') {
    $adminName = $_SESSION['sa_nombre'] ?? 'Super Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — DASH Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= sa_asset('sa.css') ?>">
</head>
<body>
<div class="sa-overlay" id="saOverlay"></div>
<div class="sa-wrapper">

<!-- ── Sidebar ────────────────────────────────────────────── -->
<aside class="sa-sidebar">
    <div class="sa-sidebar-logo">
        <img src="<?= sa_asset('dashlogo.png') ?>" alt="DASH" style="height:36px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
        <div>
            <div class="logo-sub" style="margin-top:2px">Panel de Control</div>
        </div>
    </div>

    <nav class="sa-nav">
        <div class="sa-nav-section">
            <div class="sa-nav-title">Principal</div>
            <a href="<?= sa_url('index.php') ?>" class="sa-nav-item <?= $activeKey==='dashboard'?'active':'' ?>">
                <i class="fas fa-gauge-high"></i><span>Dashboard</span>
            </a>
        </div>
        <div class="sa-nav-section">
            <div class="sa-nav-title">Gestión</div>
            <a href="<?= sa_url('negocios/index.php') ?>" class="sa-nav-item <?= $activeKey==='negocios'?'active':'' ?>">
                <i class="fas fa-store"></i><span>Negocios</span>
            </a>
            <a href="<?= sa_url('usuarios/index.php') ?>" class="sa-nav-item <?= $activeKey==='usuarios'?'active':'' ?>">
                <i class="fas fa-users"></i><span>Usuarios</span>
            </a>
            <a href="<?= sa_url('pagos/index.php') ?>" class="sa-nav-item <?= $activeKey==='pagos'?'active':'' ?>">
                <i class="fas fa-dollar-sign"></i><span>Pagos</span>
            </a>
        </div>
        <div class="sa-nav-section">
            <div class="sa-nav-title">Configuración</div>
            <a href="<?= sa_url('planes/index.php') ?>" class="sa-nav-item <?= $activeKey==='planes'?'active':'' ?>">
                <i class="fas fa-layer-group"></i><span>Planes</span>
            </a>
            <a href="<?= sa_url('rubros/index.php') ?>" class="sa-nav-item <?= $activeKey==='rubros'?'active':'' ?>">
                <i class="fas fa-tags"></i><span>Rubros</span>
            </a>
        </div>
        <div class="sa-nav-section">
            <div class="sa-nav-title">Reportes</div>
            <a href="<?= sa_url('reportes/productos.php') ?>" class="sa-nav-item <?= $activeKey==='reportes_productos'?'active':'' ?>">
                <i class="fas fa-fire"></i><span>Productos vendidos</span>
            </a>
        </div>
        <div class="sa-nav-section">
            <div class="sa-nav-title">Sistema</div>
            <a href="<?= sa_url('logs/index.php') ?>" class="sa-nav-item <?= $activeKey==='logs'?'active':'' ?>">
                <i class="fas fa-scroll"></i><span>Logs</span>
            </a>
            <a href="<?= sa_url('perfil/index.php') ?>" class="sa-nav-item <?= $activeKey==='perfil'?'active':'' ?>">
                <i class="fas fa-user-circle"></i><span>Mi Perfil</span>
            </a>
        </div>
    </nav>

    <div class="sa-sidebar-footer">
        <div class="sa-admin-chip">
            <div class="sa-admin-avatar"><i class="fas fa-user-shield"></i></div>
            <div class="sa-admin-info">
                <div class="name"><?= htmlspecialchars($adminName) ?></div>
                <div class="role">Super Admin</div>
            </div>
            <button class="sa-logout-btn" onclick="location.href='<?= sa_url('logout.php') ?>'" title="Cerrar sesión">
                <i class="fas fa-right-from-bracket"></i>
            </button>
        </div>
    </div>
</aside>

<!-- ── Main ───────────────────────────────────────────────── -->
<main class="sa-main">
    <div class="sa-topbar">
        <div class="sa-flex sa-gap">
            <button class="sa-hamburger" id="saHam"><i class="fas fa-bars"></i></button>
            <div class="sa-topbar-title">
                <?= htmlspecialchars($title) ?>
                <small><?= date('d/m/Y') ?></small>
            </div>
        </div>
        <div class="sa-topbar-actions">
            <img src="<?= sa_asset('dashlogo.png') ?>" alt="DASH" style="height:28px;width:auto;object-fit:contain;filter:brightness(0) invert(1);opacity:.6">
            <a href="<?= sa_url('negocios/nuevo.php') ?>" class="sa-btn primary sm">
                <i class="fas fa-plus"></i> Nuevo Negocio
            </a>
        </div>
    </div>
    <div class="sa-content">
<?php
}

function sa_layout_end() {
?>
    </div><!-- /sa-content -->
</main>
</div><!-- /sa-wrapper -->
<script src="<?= sa_asset('sa.js') ?>"></script>
</body>
</html>
<?php
}

function sa_url(string $path): string {
    // Construye URL relativa desde cualquier subdirectorio del superadmin
    $depth = substr_count(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/superadmin/');
    $prefix = str_repeat('../', $depth);
    // Detectar si estamos dentro de una subcarpeta del superadmin
    $script = str_replace('\\','/', $_SERVER['SCRIPT_NAME']);
    $saPos  = strpos($script, '/superadmin/');
    if ($saPos !== false) {
        $subPath = substr($script, $saPos + strlen('/superadmin/'));
        $levels  = substr_count(dirname($subPath), '/') + (dirname($subPath) === '.' ? 0 : 1);
        $up      = str_repeat('../', $levels);
        return $up . $path;
    }
    return $path;
}

function sa_asset(string $file): string {
    return sa_url("assets/{$file}");
}
