<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}

// Detectar rubro del negocio
$_menuSlug = '';
if (isset($_SESSION['negocio_id'])) {
    try {
        require_once dirname(dirname(dirname(__FILE__))) . '/config/database.php';
        $_pdoM = (new Database())->getConnection();
        $stmtM = $_pdoM->prepare("SELECT r.slug FROM negocios n LEFT JOIN rubros r ON r.id = n.rubro_id WHERE n.id = ?");
        $stmtM->execute([(int)$_SESSION['negocio_id']]);
        $_menuSlug = $stmtM->fetchColumn() ?: '';
    } catch (Exception $e) { $_menuSlug = ''; }
}

$esAdmin        = ($_SESSION['rol'] ?? '') === 'admin';
$esRestaurant   = in_array($_menuSlug, ['gastronomia','bar','restaurant','cafeteria','panaderia','comida_rapida']);
$esferreteria   = in_array($_menuSlug, ['ferreteria','construccion','tecnologia','electrodomesticos','otro']);
$esSupermercado = in_array($_menuSlug, ['supermercado','almacen']);
$esPeluqueria   = in_array($_menuSlug, ['peluqueria']);
$esGimnasio     = in_array($_menuSlug, ['gimnasio']);
$esCanchas      = in_array($_menuSlug, ['canchas']);
$esFarmacia     = in_array($_menuSlug, ['farmacia']);
$esHospedaje    = in_array($_menuSlug, ['hospedaje']);
$esVeterinaria  = in_array($_menuSlug, ['veterinaria']);
$esOptica       = in_array($_menuSlug, ['optica']);
$esTecnologia   = in_array($_menuSlug, ['tecnologia']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú — DASHBASE</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .menu-page { padding-bottom: 90px; }

        /* ── Etiqueta de sección ── */
        .menu-section-label {
            font-size: 11px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .8px; color: var(--text-secondary);
            padding: 0 2px; margin: 24px 0 10px;
        }
        .menu-section-label:first-child { margin-top: 0; }

        /* ── Grid ── */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        @media (max-width: 900px)  { .menu-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 600px)  { .menu-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; } }
        @media (max-width: 380px)  { .menu-grid { grid-template-columns: repeat(2, 1fr); } }

        /* ── Tarjeta ── */
        .menu-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 18px 10px 14px;
            text-align: center;
            text-decoration: none;
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 9px;
            transition: all .18s;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .menu-card:hover, .menu-card:focus {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,.10);
            border-color: var(--primary);
        }
        .menu-card:active { transform: scale(.95); }

        .mc-icon {
            width: 52px; height: 52px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff; flex-shrink: 0;
        }
        .mc-label {
            font-size: 12px; font-weight: 700; line-height: 1.3;
            color: var(--text-primary);
        }
        .mc-sub {
            font-size: 10px; color: var(--text-secondary); margin-top: -4px;
        }

        @media (max-width: 600px) {
            .mc-icon  { width: 44px; height: 44px; font-size: 18px; border-radius: 12px; }
            .mc-label { font-size: 11px; }
            .mc-sub   { display: none; }
            .menu-card { padding: 14px 8px 12px; gap: 7px; border-radius: 14px; }
        }

        /* ── Info card ── */
        .info-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 16px 20px; margin-top: 28px;
        }
        .info-row {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 13px; color: var(--text-secondary); padding: 6px 0;
            border-bottom: 1px solid var(--border);
        }
        .info-row:last-child { border-bottom: none; }
        .info-row strong { color: var(--text-primary); }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container menu-page">

            <div class="page-header" style="margin-bottom:16px;">
                <div>
                    <h1 style="font-size:20px;font-weight:800;margin:0;">
                        <i class="fas fa-th" style="margin-right:8px;color:var(--primary);"></i>Menú Principal
                    </h1>
                    <p style="font-size:13px;color:var(--text-secondary);margin:4px 0 0;">
                        Accedé a todas las funciones del sistema
                    </p>
                </div>
            </div>

            <!-- ── General ── -->
            <div class="menu-section-label">General</div>
            <div class="menu-grid">
                <a href="../dashboard/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0FD186,#059669);"><i class="fas fa-home"></i></div>
                    <div class="mc-label">Dashboard</div><div class="mc-sub">Panel</div>
                </a>
                <a href="../ventas/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);"><i class="fas fa-shopping-cart"></i></div>
                    <div class="mc-label">Ventas</div><div class="mc-sub">Punto de venta</div>
                </a>
                <a href="../ventas/historial.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);"><i class="fas fa-receipt"></i></div>
                    <div class="mc-label">Historial</div><div class="mc-sub">Ventas anteriores</div>
                </a>
                <a href="../caja/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-cash-register"></i></div>
                    <div class="mc-label">Caja</div><div class="mc-sub">Arqueo</div>
                </a>
                <a href="../productos/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2);"><i class="fas fa-box"></i></div>
                    <div class="mc-label">Productos</div><div class="mc-sub">Inventario</div>
                </a>
                <a href="../gastos/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f43f5e,#e11d48);"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="mc-label">Gastos</div><div class="mc-sub">Control</div>
                </a>
                <?php if ($esAdmin): ?>
                <a href="../categorias/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#ec4899,#db2777);"><i class="fas fa-tags"></i></div>
                    <div class="mc-label">Categorías</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../reportes/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);"><i class="fas fa-chart-line"></i></div>
                    <div class="mc-label">Reportes</div><div class="mc-sub">Estadísticas</div>
                </a>
                <a href="../empleados/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#64748b,#475569);"><i class="fas fa-users"></i></div>
                    <div class="mc-label">Empleados</div><div class="mc-sub">Usuarios</div>
                </a>
                <a href="../perfil/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-building"></i></div>
                    <div class="mc-label">Perfil Negocio</div><div class="mc-sub">Config</div>
                </a>
                <?php endif; ?>
            </div>

            <?php if ($esRestaurant): ?>
            <!-- ── Restaurant ── -->
            <div class="menu-section-label"><i class="fas fa-utensils" style="margin-right:5px;color:#FF7A30;"></i>Restaurant</div>
            <div class="menu-grid">
                <a href="../restaurant/mesas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#FF7A30,#ea580c);"><i class="fas fa-chair"></i></div>
                    <div class="mc-label">Mesas</div><div class="mc-sub">Salón</div>
                </a>
                <a href="../restaurant/reservas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-calendar-alt"></i></div>
                    <div class="mc-label">Reservas</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../restaurant/cocina.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c);"><i class="fas fa-fire"></i></div>
                    <div class="mc-label">Cocina</div><div class="mc-sub">KDS</div>
                </a>
                <a href="../restaurant/carta.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);"><i class="fas fa-book-open"></i></div>
                    <div class="mc-label">Carta / Menú</div><div class="mc-sub">Platos</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esSupermercado): ?>
            <!-- ── Supermercado ── -->
            <div class="menu-section-label"><i class="fas fa-store" style="margin-right:5px;color:#0FD186;"></i>Supermercado</div>
            <div class="menu-grid">
                <a href="../supermercado/proveedores.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0FD186,#059669);"><i class="fas fa-truck"></i></div>
                    <div class="mc-label">Proveedores</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../supermercado/stock.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);"><i class="fas fa-boxes-stacked"></i></div>
                    <div class="mc-label">Stock</div><div class="mc-sub">Control</div>
                </a>
                <a href="../supermercado/ordenes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-clipboard-list"></i></div>
                    <div class="mc-label">Órdenes</div><div class="mc-sub">Compra</div>
                </a>
                <a href="../supermercado/etiquetas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="fas fa-tag"></i></div>
                    <div class="mc-label">Etiquetas</div><div class="mc-sub">Balanza</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esferreteria): ?>
            <!-- ── Ferretería ── -->
            <div class="menu-section-label"><i class="fas fa-tools" style="margin-right:5px;color:#f59e0b;"></i>Ferretería</div>
            <div class="menu-grid">
                <a href="../presupuestos/index.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="mc-label">Presupuestos</div><div class="mc-sub">Cotizaciones</div>
                </a>
                <a href="../ferreteria/proveedores.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-truck"></i></div>
                    <div class="mc-label">Proveedores</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../ferreteria/ordenes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f43f5e,#e11d48);"><i class="fas fa-clipboard-list"></i></div>
                    <div class="mc-label">Órdenes</div><div class="mc-sub">Compra</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esPeluqueria): ?>
            <!-- ── Peluquería ── -->
            <div class="menu-section-label"><i class="fas fa-scissors" style="margin-right:5px;color:#8b5cf6;"></i>Peluquería</div>
            <div class="menu-grid">
                <a href="../peluqueria/agenda.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="fas fa-calendar-alt"></i></div>
                    <div class="mc-label">Agenda</div><div class="mc-sub">Turnos</div>
                </a>
                <a href="../peluqueria/servicios.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#ec4899,#db2777);"><i class="fas fa-scissors"></i></div>
                    <div class="mc-label">Servicios</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../peluqueria/clientes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2);"><i class="fas fa-address-book"></i></div>
                    <div class="mc-label">Clientes</div><div class="mc-sub">Historial</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esGimnasio): ?>
            <!-- ── Gimnasio ── -->
            <div class="menu-section-label"><i class="fas fa-dumbbell" style="margin-right:5px;color:#f97316;"></i>Gimnasio</div>
            <div class="menu-grid">
                <a href="../gym/socios.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f97316,#ea580c);"><i class="fas fa-id-card"></i></div>
                    <div class="mc-label">Socios</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../gym/clases.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);"><i class="fas fa-calendar-week"></i></div>
                    <div class="mc-label">Clases</div><div class="mc-sub">Horarios</div>
                </a>
                <a href="../gym/asistencias.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-fingerprint"></i></div>
                    <div class="mc-label">Asistencias</div><div class="mc-sub">Control</div>
                </a>
                <a href="../gym/pagos.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-dollar-sign"></i></div>
                    <div class="mc-label">Pagos</div><div class="mc-sub">Cuotas</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esCanchas): ?>
            <!-- ── Canchas ── -->
            <div class="menu-section-label"><i class="fas fa-futbol" style="margin-right:5px;color:#16a34a;"></i>Canchas</div>
            <div class="menu-grid">
                <a href="../canchas/reservas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#16a34a,#15803d);"><i class="fas fa-calendar-check"></i></div>
                    <div class="mc-label">Reservas</div><div class="mc-sub">Gestión</div>
                </a>
                <a href="../canchas/canchas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);"><i class="fas fa-futbol"></i></div>
                    <div class="mc-label">Mis Canchas</div><div class="mc-sub">Config</div>
                </a>
                <a href="../canchas/clientes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="fas fa-users"></i></div>
                    <div class="mc-label">Clientes</div><div class="mc-sub">Historial</div>
                </a>
                <a href="../canchas/caja.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-cash-register"></i></div>
                    <div class="mc-label">Caja del Día</div><div class="mc-sub">Arqueo</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esHospedaje): ?>
            <!-- ── Hospedaje ── -->
            <div class="menu-section-label"><i class="fas fa-bed" style="margin-right:5px;color:#6366f1;"></i>Hospedaje</div>
            <div class="menu-grid">
                <a href="../hospedaje/habitaciones.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-bed"></i></div>
                    <div class="mc-label">Habitaciones</div><div class="mc-sub">Panel</div>
                </a>
                <a href="../hospedaje/reservas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);"><i class="fas fa-calendar-check"></i></div>
                    <div class="mc-label">Reservas</div><div class="mc-sub">Gestión</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esVeterinaria): ?>
            <!-- ── Veterinaria ── -->
            <div class="menu-section-label"><i class="fas fa-paw" style="margin-right:5px;color:#84cc16;"></i>Veterinaria</div>
            <div class="menu-grid">
                <a href="../veterinaria/pacientes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#84cc16,#65a30d);"><i class="fas fa-paw"></i></div>
                    <div class="mc-label">Pacientes</div><div class="mc-sub">Fichas</div>
                </a>
                <a href="../veterinaria/agenda.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-calendar-alt"></i></div>
                    <div class="mc-label">Agenda</div><div class="mc-sub">Turnos del día</div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($esFarmacia): ?>
            <!-- ── Farmacia ── -->
            <div class="menu-section-label"><i class="fas fa-pills" style="margin-right:5px;color:#10b981;"></i>Farmacia</div>
            <div class="menu-grid">
                <a href="../farmacia/recetas.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-prescription"></i></div>
                    <div class="mc-label">Recetas</div><div class="mc-sub">Despacho</div>
                </a>
                <a href="../farmacia/vencimientos.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f43f5e,#e11d48);"><i class="fas fa-calendar-times"></i></div>
                    <div class="mc-label">Vencimientos</div><div class="mc-sub">Alertas</div>
                </a>
                <a href="../farmacia/stock.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);"><i class="fas fa-boxes-stacked"></i></div>
                    <div class="mc-label">Stock</div><div class="mc-sub">Control</div>
                </a>
                <a href="../farmacia/clientes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2);"><i class="fas fa-users"></i></div>
                    <div class="mc-label">Clientes</div><div class="mc-sub">Obra social</div>
                </a>
                <a href="../farmacia/proveedores.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-truck"></i></div>
                    <div class="mc-label">Proveedores</div><div class="mc-sub">Distribuidoras</div>
                </a>
                <a href="../farmacia/laboratorios.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="fas fa-flask"></i></div>
                    <div class="mc-label">Laboratorios</div><div class="mc-sub">Droguerías</div>
                </a>
            </div>
            <?php endif; ?>

            <!-- ── Óptica ── -->
            <?php if ($esOptica): ?>
            <div class="menu-section-label"><i class="fas fa-eye" style="margin-right:5px;color:#0ea5e9;"></i>Óptica</div>
            <div class="menu-grid">
                <a href="../optica/clientes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);"><i class="fas fa-users"></i></div>
                    <div class="mc-label">Clientes</div><div class="mc-sub">Fichas y recetas</div>
                </a>
                <a href="../optica/pedidos.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-glasses"></i></div>
                    <div class="mc-label">Pedidos</div><div class="mc-sub">Trabajos</div>
                </a>
            </div>
            <?php endif; ?>

            <!-- ── Tecnología ── -->
            <?php if ($esTecnologia): ?>
            <div class="menu-section-label"><i class="fas fa-laptop" style="margin-right:5px;color:#6366f1;"></i>Tecnología</div>
            <div class="menu-grid">
                <a href="../tecnologia/clientes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-users"></i></div>
                    <div class="mc-label">Clientes</div><div class="mc-sub">Historial y equipos</div>
                </a>
                <a href="../tecnologia/ordenes.php" class="menu-card">
                    <div class="mc-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-tools"></i></div>
                    <div class="mc-label">Órdenes</div><div class="mc-sub">Servicio técnico</div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Info del sistema -->
            <div class="info-card">
                <h3 style="margin:0 0 12px;font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;color:var(--text-primary);">
                    <i class="fas fa-info-circle" style="color:var(--primary);"></i>Información del sistema
                </h3>
                <div class="info-row">
                    <span><i class="fas fa-user" style="width:14px;text-align:center;margin-right:6px;"></i>Usuario</span>
                    <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['user_name'] ?? '—'); ?></strong>
                </div>
                <div class="info-row">
                    <span><i class="fas fa-shield-alt" style="width:14px;text-align:center;margin-right:6px;"></i>Rol</span>
                    <strong><?php echo ucfirst($_SESSION['rol'] ?? '—'); ?></strong>
                </div>
                <div class="info-row">
                    <span><i class="fas fa-store" style="width:14px;text-align:center;margin-right:6px;"></i>Rubro</span>
                    <strong><?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$_menuSlug ?: '—'))); ?></strong>
                </div>
                <div class="info-row">
                    <span><i class="fas fa-code-branch" style="width:14px;text-align:center;margin-right:6px;"></i>Versión</span>
                    <strong>DASHBASE v2.0</strong>
                </div>
            </div>

        </div><!-- .container -->
    </div><!-- .main-content -->
</div><!-- .app-layout -->
</body>
</html>
