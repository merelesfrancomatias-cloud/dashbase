<?php
// Detectar rubro para el header
$_hRubroLabel = '';
$_hRubroIcon  = 'fa-store';
$_hRubroColor = '#0FD186';
if (isset($_SESSION['negocio_id'])) {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $_pdoH = (new Database())->getConnection();
        $stmtH = $_pdoH->prepare(
            "SELECT r.nombre AS rubro, r.slug AS slug
             FROM negocios n
             LEFT JOIN rubros r ON r.id = n.rubro_id
             WHERE n.id = ?"
        );
        $stmtH->execute([$_SESSION['negocio_id']]);
        $rowH = $stmtH->fetch(PDO::FETCH_ASSOC);
        if ($rowH) {
            $hSlug = $rowH['slug'] ?? '';
            $hIcons  = ['gastronomia'=>'fa-utensils','bar'=>'fa-beer','restaurant'=>'fa-utensils','cafeteria'=>'fa-coffee','panaderia'=>'fa-bread-slice','comida_rapida'=>'fa-hamburger','ferreteria'=>'fa-wrench','construccion'=>'fa-hard-hat','tecnologia'=>'fa-laptop','electrodomesticos'=>'fa-plug','indumentaria'=>'fa-tshirt','farmacia'=>'fa-pills','supermercado'=>'fa-store','otro'=>'fa-briefcase'];
            $hColors = ['gastronomia'=>'#f97316','bar'=>'#8b5cf6','restaurant'=>'#ef4444','cafeteria'=>'#a16207','panaderia'=>'#d97706','comida_rapida'=>'#dc2626','ferreteria'=>'#f59e0b','construccion'=>'#0284c7','tecnologia'=>'#6366f1','electrodomesticos'=>'#0891b2','indumentaria'=>'#ec4899','farmacia'=>'#10b981','supermercado'=>'#16a34a','otro'=>'#64748b'];
            $hLabels = ['gastronomia'=>'Gastronomía','bar'=>'Bar','restaurant'=>'Restaurant','cafeteria'=>'Cafetería','panaderia'=>'Panadería','comida_rapida'=>'Comida Rápida','ferreteria'=>'Ferretería','construccion'=>'Construcción','tecnologia'=>'Tecnología','electrodomesticos'=>'Electrodomésticos','indumentaria'=>'Indumentaria','farmacia'=>'Farmacia','supermercado'=>'Supermercado','otro'=>'Negocio'];
            $_hRubroLabel = $hLabels[$hSlug] ?? ucfirst($rowH['rubro'] ?? '');
            $_hRubroIcon  = $hIcons[$hSlug]  ?? 'fa-store';
            $_hRubroColor = $hColors[$hSlug] ?? '#0FD186';
        }
    } catch (Exception $e) { /* silencioso */ }
}
?>
<header class="header">
    <div class="header-left">
        <!-- Botón hamburguesa — solo en tablet/móvil -->
        <button id="sidebarToggle" title="Menú" style="display:none;background:none;border:none;cursor:pointer;padding:6px 8px;border-radius:8px;color:var(--text-primary);font-size:20px;line-height:1;transition:.15s;" onmouseover="this.style.background='var(--background)'" onmouseout="this.style.background='none'">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-logo">
            <i class="fas fa-chart-line"></i>
        </div>
        <h2 class="header-title" id="pageTitle">Dashboard</h2>
    </div>
    <div class="header-right">
        <!-- Badge de rubro -->
        <?php if ($_hRubroLabel): ?>
        <div class="header-rubro-badge" title="Tipo de negocio" style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:5px 12px 5px 6px;cursor:default;">
            <span style="width:24px;height:24px;border-radius:50%;background:<?= $_hRubroColor ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas <?= $_hRubroIcon ?>" style="font-size:11px;color:#fff;"></i>
            </span>
            <span style="font-size:12px;font-weight:600;color:<?= $_hRubroColor ?>;letter-spacing:.3px;"><?= htmlspecialchars($_hRubroLabel) ?></span>
        </div>
        <?php endif; ?>

        <!-- Botón de cambio de tema -->
        <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
            <i class="fas fa-moon"></i>
        </button>
        
        <div class="header-user">
            <div class="user-avatar" id="userAvatar">AD</div>
            <div class="user-info">
                <div class="user-name" id="userName">Cargando...</div>
                <div class="user-role" id="userRole">...</div>
            </div>
        </div>
        <button class="btn-logout" id="btnLogout" title="Cerrar sesión" onclick="abrirModalLogout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Salir</span>
        </button>
    </div>
</header>

<!-- ── Modal Cerrar Sesión ── -->
<div id="modalLogout" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(4px);">
    <div style="background:var(--surface);border-radius:20px;width:100%;max-width:360px;box-shadow:0 24px 64px rgba(0,0,0,.25);overflow:hidden;animation:popIn .2s ease;">
        <div style="padding:28px 28px 20px;text-align:center;">
            <div style="width:60px;height:60px;border-radius:50%;background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-sign-out-alt" style="font-size:24px;color:#ef4444;"></i>
            </div>
            <h3 style="margin:0 0 8px;font-size:18px;font-weight:700;color:var(--text-primary);">¿Cerrás sesión?</h3>
            <p style="margin:0;font-size:14px;color:var(--text-secondary);line-height:1.5;">Vas a salir de tu cuenta. Podés volver a ingresar cuando quieras.</p>
        </div>
        <div style="padding:0 28px 24px;display:flex;gap:10px;">
            <button onclick="cerrarModalLogout()" style="flex:1;padding:11px;border-radius:12px;border:1.5px solid var(--border);background:var(--background);color:var(--text-primary);font-size:14px;font-weight:600;cursor:pointer;transition:.15s;" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--background)'">
                Cancelar
            </button>
            <button onclick="confirmarLogout()" id="btnConfirmarLogout" style="flex:1;padding:11px;border-radius:12px;border:none;background:#ef4444;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:.15s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                <i class="fas fa-sign-out-alt"></i> Salir
            </button>
        </div>
    </div>
</div>
<style>
@keyframes popIn { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }
</style>

<script>
// Cargar información del usuario
document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (user.nombre) {
        document.getElementById('userName').textContent = user.nombre;
        document.getElementById('userRole').textContent = user.rol === 'admin' ? 'Administrador' : 'Empleado';
        
        const iniciales = user.nombre.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        document.getElementById('userAvatar').textContent = iniciales;
    }
    
    // Actualizar título de la página
    const pathParts = window.location.pathname.split('/');
    const page = pathParts[pathParts.length - 2];
    const titles = {
        'dashboard': 'Dashboard',
        'productos': 'Productos',
        'categorias': 'Categorías',
        'ventas': 'Punto de Venta',
        'caja': 'Caja',
        'pedidos': 'Pedidos',
        'gastos': 'Gastos',
        'empleados': 'Empleados',
        'reportes': 'Reportes',
        'perfil': 'Perfil',
        'menu': 'Menú'
    };
    
    const icons = {
        'dashboard': 'fa-home',
        'productos': 'fa-box',
        'categorias': 'fa-tags',
        'ventas': 'fa-shopping-cart',
        'caja': 'fa-cash-register',
        'pedidos': 'fa-clipboard-list',
        'gastos': 'fa-money-bill-wave',
        'empleados': 'fa-users',
        'reportes': 'fa-chart-line',
        'perfil': 'fa-building',
        'menu': 'fa-th'
    };
    
    if (titles[page]) {
        document.getElementById('pageTitle').textContent = titles[page];
        const logoIcon = document.querySelector('.header-logo i');
        if (logoIcon && icons[page]) {
            logoIcon.className = `fas ${icons[page]}`;
        }
    }
    
    // Cargar tema guardado
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        updateThemeIcon(true);
    }
});

// Toggle de tema (Dark Mode)
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeIcon(isDark);
    });
}

function updateThemeIcon(isDark) {
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Logout modal
function abrirModalLogout() {
    const m = document.getElementById('modalLogout');
    m.style.display = 'flex';
}
function cerrarModalLogout() {
    document.getElementById('modalLogout').style.display = 'none';
}
async function confirmarLogout() {
    const btn = document.getElementById('btnConfirmarLogout');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saliendo…';
    try {
        // Construir la URL base de forma segura, sin depender de API_URL
        const _base = (window.APP_BASE !== undefined && window.APP_BASE !== '')
            ? window.APP_BASE
            : (window.BASE !== undefined ? window.BASE : '');
        await fetch(_base + '/api/auth/logout.php', { method:'POST', credentials:'include' });
    } catch(e) {}
    localStorage.removeItem('user');
    sessionStorage.setItem('just_logged_out', '1');
    window.location.href = '../../index.php';
}
// Cerrar con Esc o clic en fondo
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModalLogout(); });
document.getElementById('modalLogout').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalLogout();
});

// ── Sidebar hamburguesa ────────────────────────────────────────────────────
(function() {
    // Mostrar/ocultar botón hamburguesa según ancho
    const btn = document.getElementById('sidebarToggle');
    function checkBtn() {
        if (!btn) return;
        btn.style.display = window.innerWidth <= 1024 ? 'flex' : 'none';
        btn.style.alignItems = 'center';
    }
    checkBtn();
    window.addEventListener('resize', checkBtn);

    // Usar click para desktop y touchend para móvil
    // touchend verifica que no fue un swipe accidental
    if (btn) {
        let _btnTouchStartX = 0, _btnTouchStartY = 0;
        btn.addEventListener('touchstart', e => {
            _btnTouchStartX = e.touches[0].clientX;
            _btnTouchStartY = e.touches[0].clientY;
        }, { passive: true });
        btn.addEventListener('touchend', e => {
            const dx = Math.abs(e.changedTouches[0].clientX - _btnTouchStartX);
            const dy = Math.abs(e.changedTouches[0].clientY - _btnTouchStartY);
            // Solo activar si fue un toque quieto (no un swipe)
            if (dx < 10 && dy < 10) {
                e.preventDefault();
                toggleSidebar();
            }
        }, { passive: false });
        // Click para mouse/desktop
        btn.addEventListener('click', e => {
            // En touch, el click se dispara después del touchend — lo ignoramos
            if (e.detail === 0) return; // sintético, ignorar
            toggleSidebar();
        });
    }

    // Crear overlay si no existe
    if (!document.getElementById('sidebarOverlay')) {
        const ov = document.createElement('div');
        ov.id = 'sidebarOverlay';
        ov.onclick = () => toggleSidebar(false);
        ov.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;backdrop-filter:blur(2px);touch-action:none;';
        document.body.appendChild(ov);
    }
})();

// Guarda la posición del scroll para restaurarla al cerrar el sidebar
let _sidebarScrollY = 0;

function toggleSidebar(force) {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar) return;
    const isOpen = sidebar.classList.contains('active');
    const open = force !== undefined ? force : !isOpen;
    if (open) {
        sidebar.classList.add('active');
        if (overlay) overlay.style.display = 'block';
        // iOS fix: position:fixed evita que el scroll bounce abra el sidebar
        _sidebarScrollY = window.scrollY;
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${_sidebarScrollY}px`;
        document.body.style.width = '100%';
    } else {
        sidebar.classList.remove('active');
        if (overlay) overlay.style.display = 'none';
        // Restaurar scroll
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        window.scrollTo(0, _sidebarScrollY);
    }
}

// ── Service Worker ─────────────────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .catch(() => {});
}

// ── PWA: inyectar manifest + theme-color en <head> si no están ─────────────
(function() {
    if (!document.querySelector('link[rel="manifest"]')) {
        const l = document.createElement('link');
        l.rel = 'manifest'; l.href = '/manifest.json';
        document.head.appendChild(l);
    }
    if (!document.querySelector('meta[name="theme-color"]')) {
        const m = document.createElement('meta');
        m.name = 'theme-color'; m.content = '#0FD186';
        document.head.appendChild(m);
    }
})();
</script>
