<?php
// ── Detectar rubro ANTES de renderizar el HTML ──────────────────────────────
$esRestaurant   = false;
$esferreteria   = false;
$esSupermercado = false;
$esPeluqueria   = false;
$esGimnasio     = false;
$esCanchas      = false;
$esFarmacia     = false;
$esHospedaje    = false;
$esVeterinaria  = false;
$esOptica       = false;
$esTecnologia       = false;
$esElectrodomesticos= false;
$slugSB             = '';

if (isset($_SESSION['negocio_id'])) {
    try {
        $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_NAME'] ?? 'dashbase_local';
        $user   = $_ENV['DB_USER'] ?? 'root';
        $pass   = $_ENV['DB_PASS'] ?? '';
        if (empty($dbname)) { $dbname = 'dashbase_local'; }
        $_pdoSB = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
        $stmtSB = $_pdoSB->prepare("SELECT r.slug FROM negocios n LEFT JOIN rubros r ON r.id = n.rubro_id WHERE n.id = ?");
        $stmtSB->execute([(int)$_SESSION['negocio_id']]);
        $slugSB = $stmtSB->fetchColumn() ?: '';
        $esRestaurant   = in_array($slugSB, ['gastronomia','bar','restaurant','cafeteria','panaderia','comida_rapida']);
        $esferreteria   = in_array($slugSB, ['ferreteria','construccion','otro']);
        $esSupermercado = in_array($slugSB, ['supermercado','almacen','libreria','jugueteria','floristeria','deportes','indumentaria']);
        $esPeluqueria   = in_array($slugSB, ['peluqueria']);
        $esGimnasio     = in_array($slugSB, ['gimnasio']);
        $esCanchas      = in_array($slugSB, ['canchas']);
        $esFarmacia     = in_array($slugSB, ['farmacia']);
        $esHospedaje    = in_array($slugSB, ['hospedaje']);
        $esVeterinaria  = in_array($slugSB, ['veterinaria']);
        $esOptica       = in_array($slugSB, ['optica']);
        $esTecnologia        = in_array($slugSB, ['tecnologia']);
        $esElectrodomesticos = in_array($slugSB, ['electrodomesticos']);
    } catch (Exception $e) {}
}
?>
<aside class="sidebar">
    <div class="sidebar-header">

        <div class="sidebar-logo" style="width:65%;height:auto;background:transparent;border:none;box-shadow:none;border-radius:0;padding:10px 0;margin:0 auto;">
            <img src="../../public/img/DASHLOGOSF.png" alt="DASH Logo" style="filter:none;padding:0;width:100%;height:auto;">
        </div>

        <?php
        // Badge del tipo de negocio
        if (isset($_SESSION['negocio_id'])) {
            try {
                $host_nb   = $_ENV['DB_HOST'] ?? '127.0.0.1';
                $dbname_nb = $_ENV['DB_NAME'] ?? 'dashbase_local';
                $user_nb   = $_ENV['DB_USER'] ?? 'root';
                $pass_nb   = $_ENV['DB_PASS'] ?? '';
                if (empty($dbname_nb)) $dbname_nb = 'dashbase_local';
                $_pdoNB = new PDO("mysql:host={$host_nb};dbname={$dbname_nb};charset=utf8mb4", $user_nb, $pass_nb,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
                $stmtNB = $_pdoNB->prepare("SELECT n.nombre as negocio_nombre, r.nombre as rubro_nombre, r.slug as rubro_slug FROM negocios n LEFT JOIN rubros r ON r.id = n.rubro_id WHERE n.id = ?");
                $stmtNB->execute([(int)$_SESSION['negocio_id']]);
                $rowNB = $stmtNB->fetch(PDO::FETCH_ASSOC);
                if ($rowNB) {
                    $rubroSlugNB = $rowNB['rubro_slug'] ?? '';
                    $rubroIcons = [
                        'gastronomia'      => ['icon' => 'fa-utensils',       'color' => '#FF7A30', 'label' => 'Restaurant'],
                        'bar'              => ['icon' => 'fa-beer-mug-empty',  'color' => '#f59e0b', 'label' => 'Bar'],
                        'restaurant'       => ['icon' => 'fa-utensils',        'color' => '#FF7A30', 'label' => 'Restaurant'],
                        'cafeteria'        => ['icon' => 'fa-mug-hot',         'color' => '#b45309', 'label' => 'Cafetería'],
                        'panaderia'        => ['icon' => 'fa-bread-slice',     'color' => '#d97706', 'label' => 'Panadería'],
                        'comida_rapida'    => ['icon' => 'fa-burger',          'color' => '#ef4444', 'label' => 'Comida Rápida'],
                        'ferreteria'       => ['icon' => 'fa-tools',           'color' => '#f59e0b', 'label' => 'Ferretería'],
                        'construccion'     => ['icon' => 'fa-helmet-safety',   'color' => '#78716c', 'label' => 'Construcción'],
                        'tecnologia'       => ['icon' => 'fa-laptop',          'color' => '#6366f1', 'label' => 'Tecnología'],
                        'electrodomesticos'=> ['icon' => 'fa-plug',            'color' => '#0ea5e9', 'label' => 'Electrodomésticos'],
                        'indumentaria'     => ['icon' => 'fa-shirt',           'color' => '#ec4899', 'label' => 'Indumentaria'],
                        'farmacia'         => ['icon' => 'fa-pills',           'color' => '#10b981', 'label' => 'Farmacia'],
                        'supermercado'     => ['icon' => 'fa-store',           'color' => '#0FD186', 'label' => 'Supermercado'],
                        'almacen'          => ['icon' => 'fa-store',           'color' => '#0FD186', 'label' => 'Almacén'],
                        'peluqueria'       => ['icon' => 'fa-scissors',        'color' => '#8b5cf6', 'label' => 'Peluquería'],
                        'gimnasio'         => ['icon' => 'fa-dumbbell',        'color' => '#f97316', 'label' => 'Gimnasio'],
                        'canchas'          => ['icon' => 'fa-futbol',          'color' => '#16a34a', 'label' => 'Canchas'],
                        'hospedaje'        => ['icon' => 'fa-bed',             'color' => '#6366f1', 'label' => 'Hospedaje'],
                        'veterinaria'      => ['icon' => 'fa-paw',             'color' => '#84cc16', 'label' => 'Veterinaria'],
                        'optica'           => ['icon' => 'fa-eye',             'color' => '#0ea5e9', 'label' => 'Óptica'],
                        'otro'             => ['icon' => 'fa-briefcase',       'color' => '#64748b', 'label' => 'Negocio'],
                    ];
                    $ri = $rubroIcons[$rubroSlugNB] ?? ['icon' => 'fa-store', 'color' => '#0FD186', 'label' => htmlspecialchars($rowNB['rubro_nombre'] ?? 'Negocio')];
                    echo '<div class="sidebar-rubro-badge" style="display:inline-flex;align-items:center;gap:7px;margin:0 auto 10px auto;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.35);border-radius:24px;padding:6px 12px 6px 6px;max-width:calc(100% - 24px);backdrop-filter:blur(4px);box-shadow:0 2px 10px rgba(0,0,0,.12);">';
                    echo '<div style="width:24px;height:24px;border-radius:50%;flex-shrink:0;background:rgba(255,255,255,.9);display:flex;align-items:center;justify-content:center;color:' . $ri['color'] . ';font-size:12px;box-shadow:0 1px 4px rgba(0,0,0,.15);"><i class="fas ' . $ri['icon'] . '"></i></div>';
                    echo '<div style="font-size:11px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;text-shadow:0 1px 3px rgba(0,0,0,.2);">' . $ri['label'] . '</div>';
                    echo '<div style="font-size:10px;color:rgba(255,255,255,.75);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:90px;border-left:1px solid rgba(255,255,255,.3);padding-left:7px;">' . htmlspecialchars($rowNB['negocio_nombre'] ?? '') . '</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {}
        }
        ?>
    </div><!-- /sidebar-header -->

    <nav class="sidebar-menu">

        <!-- ── Principal ── -->
        <div class="menu-section">
            <h3 class="menu-section-title">Principal</h3>
            <a href="../dashboard/index.php" class="menu-item" data-page="dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
        </div>

        <!-- ── Inventario ── -->
        <div class="menu-section">
            <h3 class="menu-section-title">Inventario</h3>
            <a href="../productos/index.php" class="menu-item" data-page="productos">
                <i class="fas fa-box"></i><span>Productos</span>
            </a>
            <a href="../categorias/index.php" class="menu-item admin-only" data-page="categorias">
                <i class="fas fa-tags"></i><span>Categorías</span>
            </a>
        </div>

        <!-- ── Ventas ── -->
        <div class="menu-section">
            <h3 class="menu-section-title">Ventas</h3>
            <a href="../ventas/index.php" class="menu-item" data-page="ventas">
                <i class="fas fa-shopping-cart"></i><span>Punto de Venta</span>
            </a>
            <a href="../ventas/historial.php" class="menu-item" data-page="historial">
                <i class="fas fa-receipt"></i><span>Historial</span>
            </a>
            <a href="../caja/index.php" class="menu-item" data-page="caja">
                <i class="fas fa-cash-register"></i><span>Caja</span>
            </a>
            <a href="javascript:void(0)" class="menu-item" data-page="pedidos" onclick="showComingSoon('Pedidos')">
                <i class="fas fa-clipboard-list"></i><span>Pedidos</span>
                <span class="badge">0</span>
            </a>
        </div>

        <!-- ── Gestión ── -->
        <div class="menu-section">
            <h3 class="menu-section-title">Gestión</h3>
            <a href="../gastos/index.php" class="menu-item" data-page="gastos">
                <i class="fas fa-money-bill-wave"></i><span>Gastos</span>
            </a>
            <a href="../empleados/index.php" class="menu-item admin-only" data-page="empleados">
                <i class="fas fa-users"></i><span>Empleados</span>
            </a>
            <a href="../reportes/index.php" class="menu-item admin-only" data-page="reportes">
                <i class="fas fa-chart-line"></i><span>Reportes</span>
            </a>
        </div>

        <!-- ── Restaurant ── -->
        <?php if ($esRestaurant): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Restaurant</h3>
            <a href="../restaurant/mesas.php" class="menu-item" data-page="mesas">
                <i class="fas fa-chair"></i><span>Salón / Reservas</span>
            </a>
            <a href="../restaurant/cocina.php" class="menu-item" data-page="cocina" target="_blank">
                <i class="fas fa-fire"></i>
                <span>Pantalla Cocina</span>
                <span style="font-size:9px;background:var(--warning);color:white;padding:1px 5px;border-radius:4px;margin-left:4px;">KDS</span>
            </a>
            <a href="../restaurant/carta.php" class="menu-item" data-page="carta">
                <i class="fas fa-book-open"></i><span>Carta / Menú</span>
            </a>
            <a href="../restaurant/almacen.php" class="menu-item" data-page="almacen">
                <i class="fas fa-boxes"></i><span>Almacén</span>
            </a>
            <a href="../restaurant/reportes.php" class="menu-item" data-page="reportes-rest">
                <i class="fas fa-chart-line"></i><span>Reportes</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Ferretería ── -->
        <?php if ($esferreteria): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Ferretería</h3>
            <a href="../presupuestos/index.php" class="menu-item" data-page="presupuestos">
                <i class="fas fa-file-invoice-dollar"></i><span>Presupuestos</span>
            </a>
            <a href="../ferreteria/proveedores.php" class="menu-item" data-page="proveedores-ferreteria">
                <i class="fas fa-truck"></i><span>Proveedores</span>
            </a>
            <a href="../ferreteria/ordenes.php" class="menu-item" data-page="ordenes-ferreteria">
                <i class="fas fa-clipboard-list"></i><span>Órdenes de Compra</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Supermercado ── -->
        <?php if ($esSupermercado): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Supermercado</h3>
            <a href="../supermercado/proveedores.php" class="menu-item" data-page="proveedores">
                <i class="fas fa-truck"></i><span>Proveedores</span>
            </a>
            <a href="../supermercado/stock.php" class="menu-item" data-page="stock">
                <i class="fas fa-boxes-stacked"></i><span>Control de Stock</span>
            </a>
            <a href="../supermercado/ordenes.php" class="menu-item" data-page="ordenes">
                <i class="fas fa-clipboard-list"></i><span>Órdenes de Compra</span>
            </a>
            <a href="../supermercado/etiquetas.php" class="menu-item" data-page="etiquetas">
                <i class="fas fa-tag"></i><span>Etiquetas / Balanza</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Peluquería ── -->
        <?php if ($esPeluqueria): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Peluquería</h3>
            <a href="../peluqueria/agenda.php" class="menu-item" data-page="agenda">
                <i class="fas fa-calendar-alt"></i><span>Agenda de Turnos</span>
            </a>
            <a href="../peluqueria/servicios.php" class="menu-item" data-page="servicios-pelu">
                <i class="fas fa-scissors"></i><span>Servicios</span>
            </a>
            <a href="../peluqueria/clientes.php" class="menu-item" data-page="clientes-pelu">
                <i class="fas fa-address-book"></i><span>Clientes</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Gimnasio ── -->
        <?php if ($esGimnasio): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Gimnasio</h3>
            <a href="../gym/socios.php" class="menu-item" data-page="socios-gym">
                <i class="fas fa-id-card"></i><span>Socios</span>
            </a>
            <a href="../gym/clases.php" class="menu-item" data-page="clases-gym">
                <i class="fas fa-calendar-week"></i><span>Horario de Clases</span>
            </a>
            <a href="../gym/asistencias.php" class="menu-item" data-page="asistencias-gym">
                <i class="fas fa-fingerprint"></i><span>Asistencias</span>
            </a>
            <a href="../gym/pagos.php" class="menu-item" data-page="pagos-gym">
                <i class="fas fa-dollar-sign"></i><span>Pagos / Cuotas</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Canchas ── -->
        <?php if ($esCanchas): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Canchas</h3>
            <a href="../canchas/reservas.php" class="menu-item" data-page="reservas-canchas">
                <i class="fas fa-calendar-check"></i><span>Reservas</span>
            </a>
            <a href="../canchas/canchas.php" class="menu-item" data-page="canchas">
                <i class="fas fa-futbol"></i><span>Mis Canchas</span>
            </a>
            <a href="../canchas/clientes.php" class="menu-item" data-page="clientes-canchas">
                <i class="fas fa-users"></i><span>Clientes</span>
            </a>
            <a href="../canchas/caja.php" class="menu-item" data-page="caja-canchas">
                <i class="fas fa-cash-register"></i><span>Caja del Día</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Hospedaje ── -->
        <?php if ($esHospedaje): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Hospedaje</h3>
            <a href="../hospedaje/habitaciones.php" class="menu-item" data-page="habitaciones-hosp">
                <i class="fas fa-bed"></i><span>Habitaciones</span>
            </a>
            <a href="../hospedaje/reservas.php" class="menu-item" data-page="reservas-hosp">
                <i class="fas fa-calendar-check"></i><span>Reservas</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Veterinaria ── -->
        <?php if ($esVeterinaria): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Veterinaria</h3>
            <a href="../veterinaria/pacientes.php" class="menu-item" data-page="pac-vet">
                <i class="fas fa-paw"></i><span>Pacientes</span>
            </a>
            <a href="../veterinaria/agenda.php" class="menu-item" data-page="agenda-vet">
                <i class="fas fa-calendar-alt"></i><span>Agenda del Día</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Farmacia ── -->
        <?php if ($esFarmacia): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Farmacia</h3>
            <a href="../farmacia/recetas.php" class="menu-item" data-page="recetas-farm">
                <i class="fas fa-prescription"></i><span>Recetas</span>
            </a>
            <a href="../farmacia/vencimientos.php" class="menu-item" data-page="vencimientos-farm">
                <i class="fas fa-calendar-times"></i><span>Vencimientos</span>
            </a>
            <a href="../farmacia/stock.php" class="menu-item" data-page="stock-farm">
                <i class="fas fa-boxes-stacked"></i><span>Control de Stock</span>
            </a>
            <a href="../farmacia/clientes.php" class="menu-item" data-page="clientes-farm">
                <i class="fas fa-users"></i><span>Clientes</span>
            </a>
            <a href="../farmacia/proveedores.php" class="menu-item" data-page="proveedores-farm">
                <i class="fas fa-truck"></i><span>Proveedores</span>
            </a>
            <a href="../farmacia/laboratorios.php" class="menu-item" data-page="laboratorios-farm">
                <i class="fas fa-flask"></i><span>Laboratorios</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Óptica ── -->
        <?php if ($esOptica): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Óptica</h3>
            <a href="../optica/clientes.php" class="menu-item" data-page="cli-opt">
                <i class="fas fa-users"></i><span>Clientes</span>
            </a>
            <a href="../optica/pedidos.php" class="menu-item" data-page="ped-opt">
                <i class="fas fa-glasses"></i><span>Pedidos</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($esTecnologia): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Tecnología</h3>
            <a href="../tecnologia/clientes.php" class="menu-item" data-page="cli-tec">
                <i class="fas fa-users"></i><span>Clientes</span>
            </a>
            <a href="../tecnologia/ordenes.php" class="menu-item" data-page="ord-tec">
                <i class="fas fa-tools"></i><span>Órdenes de Servicio</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($esElectrodomesticos): ?>
        <div class="menu-section">
            <h3 class="menu-section-title">Electrodomésticos</h3>
            <a href="../electrodomesticos/clientes.php" class="menu-item" data-page="cli-elec">
                <i class="fas fa-users"></i><span>Clientes</span>
            </a>
            <a href="../electrodomesticos/servicios.php" class="menu-item" data-page="srv-elec">
                <i class="fas fa-screwdriver-wrench"></i><span>Órdenes de Servicio</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ── Configuración ── -->
        <div class="menu-section">
            <h3 class="menu-section-title">Configuración</h3>
            <a href="../perfil/index.php" class="menu-item admin-only" data-page="perfil">
                <i class="fas fa-building"></i><span>Perfil del Negocio</span>
            </a>
        </div>

    </nav>
</aside>

<!-- BOTTOM NAVIGATION móvil -->
<nav class="bottom-nav">
    <a href="../dashboard/index.php" class="bottom-nav-item" data-page="dashboard">
        <i class="fas fa-home"></i><span>Inicio</span>
    </a>
    <a href="../productos/index.php" class="bottom-nav-item" data-page="productos">
        <i class="fas fa-box"></i><span>Productos</span>
    </a>
    <a href="../ventas/index.php" class="bottom-nav-item" data-page="ventas">
        <i class="fas fa-shopping-cart"></i><span>Ventas</span>
    </a>
    <a href="../caja/index.php" class="bottom-nav-item" data-page="caja">
        <i class="fas fa-cash-register"></i><span>Caja</span>
    </a>
    <a href="../menu/index.php" class="bottom-nav-item" data-page="menu">
        <i class="fas fa-th"></i><span>Menú</span>
    </a>
</nav>

<script>
// Restaurar scroll del sidebar lo antes posible (antes de DOMContentLoaded)
(function restoreSidebarScrollEarly() {
    const nav = document.querySelector('.sidebar-menu');
    if (!nav) return;
    const saved = parseInt(sessionStorage.getItem('sidebar_scroll') || '0', 10);
    if (!Number.isNaN(saved) && saved >= 0) {
        nav.scrollTop = saved;
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const nav     = document.querySelector('.sidebar-menu');

    // ── Navegación suave entre páginas (evita parpadeo) ───────────────────
    const prefetched = new Set();

    const isInternalNavigableLink = (anchor) => {
        if (!anchor) return false;
        const href = anchor.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return false;
        if (anchor.target && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download')) return false;
        try {
            const url = new URL(href, window.location.href);
            return url.origin === window.location.origin;
        } catch (_) {
            return false;
        }
    };

    const prefetchLink = (anchor) => {
        if (!isInternalNavigableLink(anchor)) return;
        const url = new URL(anchor.getAttribute('href'), window.location.href);
        if (prefetched.has(url.href)) return;
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url.href;
        document.head.appendChild(link);
        prefetched.add(url.href);
    };

    document.querySelectorAll('a[href]').forEach((a) => {
        a.addEventListener('mouseenter', () => prefetchLink(a), { passive: true });
        a.addEventListener('touchstart', () => prefetchLink(a), { passive: true });
    });

    // Guardar scroll justo al iniciar navegación en links internos
    document.addEventListener('pointerdown', (event) => {
        const anchor = event.target.closest('a[href]');
        if (!isInternalNavigableLink(anchor)) return;
        if (nav) sessionStorage.setItem('sidebar_scroll', String(nav.scrollTop));
    }, { capture: true });

    // ── Restaurar scroll del sidebar ──────────────────────────────────────
    const SCROLL_KEY = 'sidebar_scroll';
    if (nav) {
        const saved = parseInt(sessionStorage.getItem(SCROLL_KEY) || '0', 10);
        if (!Number.isNaN(saved) && saved >= 0) {
            nav.scrollTop = saved;
        }

        const persistSidebarScroll = () => {
            sessionStorage.setItem(SCROLL_KEY, String(nav.scrollTop));
        };

        window.addEventListener('pagehide', persistSidebarScroll);
        window.addEventListener('beforeunload', persistSidebarScroll);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') persistSidebarScroll();
        });
    }

    // ── Marcar ítem activo sin provocar saltos de scroll ──────────────────
    const normalizePath = (pathname) => {
        if (!pathname) return '/';
        let p = pathname.replace(/\/+$/, '');
        if (p === '') p = '/';
        return p;
    };

    const currentPath = normalizePath(window.location.pathname);

    const markActive = (selector) => {
        const items = Array.from(document.querySelectorAll(selector));
        let bestItem = null;
        let bestScore = -1;

        items.forEach((item) => {
            const href = item.getAttribute('href');
            if (!href || href === 'javascript:void(0)') return;
            try {
                const resolved = normalizePath(new URL(href, window.location.href).pathname);
                let score = -1;
                if (resolved === currentPath) {
                    score = resolved.length + 1000;
                } else if (currentPath.startsWith(resolved + '/')) {
                    score = resolved.length;
                }

                if (score > bestScore) {
                    bestScore = score;
                    bestItem = item;
                }
            } catch (e) {}
        });

        if (bestItem) bestItem.classList.add('active');
        return bestItem;
    };

    const activeSidebarItem = markActive('.menu-item');
    markActive('.bottom-nav-item');

    // No auto-scroll al item activo para evitar saltos visuales entre páginas.

    // ── Permisos de rol ───────────────────────────────────────────────────
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.rol !== 'admin') {
        document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
    }
});

function showComingSoon(modulo) {
    const d = document.createElement('div');
    d.className = 'alert alert-info';
    d.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;';
    d.innerHTML = '<i class="fas fa-info-circle"></i> <span>' + modulo + ' estará disponible próximamente</span>';
    document.body.appendChild(d);
    setTimeout(() => d.remove(), 3000);
}

// ── Swipe para cerrar sidebar en móvil ────────────────────────────────────
(function() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;
    let touchStartX = 0;
    let touchStartY = 0;
    let isDragging  = false;

    // Swipe izquierda sobre el sidebar → cerrar
    sidebar.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isDragging  = false;
    }, { passive: true });

    sidebar.addEventListener('touchmove', e => {
        if (!sidebar.classList.contains('active')) return;
        const dx = e.touches[0].clientX - touchStartX;
        const dy = Math.abs(e.touches[0].clientY - touchStartY);
        if (Math.abs(dx) > dy && Math.abs(dx) > 8) {
            isDragging = true;
            if (dx < 0) {
                // Arrastrar hacia la izquierda: mostrar progreso visual
                const pct = Math.max(0, 1 + dx / sidebar.offsetWidth);
                sidebar.style.transform = `translateX(${Math.min(0, dx)}px)`;
                sidebar.style.transition = 'none';
                const ov = document.getElementById('sidebarOverlay');
                if (ov) ov.style.opacity = pct;
            }
        }
    }, { passive: true });

    sidebar.addEventListener('touchend', e => {
        sidebar.style.transition = '';
        sidebar.style.transform  = '';
        const ov = document.getElementById('sidebarOverlay');
        if (ov) ov.style.opacity = '';

        if (!isDragging) return;
        const dx = e.changedTouches[0].clientX - touchStartX;
        // Si arrastró >60px a la izquierda → cerrar
        if (dx < -60 && typeof toggleSidebar === 'function') {
            toggleSidebar(false);
        }
        isDragging = false;
    }, { passive: true });

    // Swipe derecha desde el borde izquierdo de la pantalla → abrir
    let _edgeStartX = 0;
    let _edgeStartY = 0;

    document.addEventListener('touchstart', e => {
        if (e.touches[0].clientX < 20) {
            _edgeStartX = e.touches[0].clientX;
            _edgeStartY = e.touches[0].clientY;
        } else {
            _edgeStartX = 0;
            _edgeStartY = 0;
        }
    }, { passive: true });

    document.addEventListener('touchend', e => {
        if (_edgeStartX === 0) return; // no empezó desde el borde
        const dx = e.changedTouches[0].clientX - _edgeStartX;
        const dy = Math.abs(e.changedTouches[0].clientY - _edgeStartY);
        // Solo abrir si: swipe derecha > 60px Y el movimiento horizontal
        // es al menos 2x mayor que el vertical (evita scroll vertical)
        if (dx > 60 && dx > dy * 2 && !sidebar.classList.contains('active')) {
            if (typeof toggleSidebar === 'function') toggleSidebar(true);
        }
        _edgeStartX = 0;
        _edgeStartY = 0;
    }, { passive: true });
})();
</script>
