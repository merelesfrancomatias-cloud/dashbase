<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
$userName = $_SESSION['nombre'] ?? 'Usuario';
$hora = (int)date('H');
if ($hora < 12)       $saludo = 'Buenos días';
elseif ($hora < 19)   $saludo = 'Buenas tardes';
else                  $saludo = 'Buenas noches';
$hoy = strftime('%A %d de %B', time()); // fallback
$hoy = date('l d \d\e F', time());
$dias = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
$meses = ['January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril','May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto','September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'];
foreach($dias as $en=>$es) $hoy = str_replace($en,$es,$hoy);
foreach($meses as $en=>$es) $hoy = str_replace($en,$es,$hoy);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/splash.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        /* ── GREETING BANNER ── */
        .greeting-banner {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 22px 28px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }
        .greeting-banner::after {
            content: '';
            position: absolute;
            right: -30px; top: -30px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(var(--primary-rgb,59,130,246),.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .greeting-banner h2 { margin:0; font-size:20px; font-weight:800; color:var(--text-primary); }
        .greeting-banner h2 span { color:var(--primary); }
        .greeting-banner p  { margin:4px 0 0; font-size:13px; color:var(--text-secondary); }
        .greeting-right { display:flex; align-items:center; gap:12px; }
        .date-chip {
            background: var(--background); border:1px solid var(--border);
            border-radius:10px; padding:8px 14px;
            font-size:12px; font-weight:600; color:var(--text-secondary);
            display:flex; align-items:center; gap:7px;
        }
        .caja-chip {
            border-radius:10px; padding:8px 14px;
            font-size:12px; font-weight:700;
            display:flex; align-items:center; gap:7px;
        }
        .caja-open  { background:rgba(16,185,129,.12); color:#10b981; border:1px solid rgba(16,185,129,.25); }
        .caja-close { background:rgba(245,158,11,.12);  color:#f59e0b; border:1px solid rgba(245,158,11,.25); }

        /* ── KPI GRID ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform .18s, box-shadow .18s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        .kpi-accent { position:absolute; top:0; left:0; width:100%; height:4px; border-radius:16px 16px 0 0; }
        .kpi-icon {
            width:44px; height:44px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:18px; margin-bottom:12px;
        }
        .kpi-label { font-size:11px; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
        .kpi-value { font-size:26px; font-weight:800; color:var(--text-primary); line-height:1.1; }
        .kpi-sub   { font-size:11px; color:var(--text-secondary); margin-top:5px; display:flex; align-items:center; gap:4px; }
        .kpi-up    { color:#10b981; font-weight:700; }
        .kpi-down  { color:#ef4444; font-weight:700; }
        .kpi-badge {
            position:absolute; top:16px; right:14px;
            background:rgba(239,68,68,.12); color:#ef4444;
            border-radius:8px; padding:3px 8px; font-size:10px; font-weight:800;
        }

        /* ── MAIN GRID ── */
        .dash-main {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 18px;
            margin-bottom: 18px;
        }
        @media(max-width:960px) { .dash-main { grid-template-columns: 1fr; } }

        /* ── CHART CARD ── */
        .dash-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
        }
        .dash-card-title {
            font-size:14px; font-weight:700; margin:0 0 16px;
            display:flex; align-items:center; gap:8px;
        }
        .dash-card-title i { color:var(--primary); }

        /* ── ÚLTIMAS VENTAS ── */
        .venta-row {
            display:flex; align-items:center; gap:12px;
            padding:10px 0; border-bottom:1px solid var(--border);
        }
        .venta-row:last-child { border-bottom:none; padding-bottom:0; }
        .venta-icon {
            width:36px; height:36px; border-radius:10px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center; font-size:14px;
        }
        .venta-info { flex:1; min-width:0; }
        .venta-info .vi-top { font-size:13px; font-weight:600; color:var(--text-primary); }
        .venta-info .vi-bot { font-size:11px; color:var(--text-secondary); margin-top:2px; }
        .venta-total { font-size:14px; font-weight:800; color:var(--primary); white-space:nowrap; }
        .empty-state { text-align:center; padding:36px 20px; color:var(--text-secondary); }
        .empty-state i { font-size:36px; opacity:.3; display:block; margin-bottom:10px; }
        .empty-state p { font-size:13px; margin:0; }

        /* ── MÉTODOS DE PAGO ── */
        .metodo-item {
            display:flex; align-items:center; justify-content:space-between;
            padding:8px 0; border-bottom:1px solid var(--border); font-size:13px;
        }
        .metodo-item:last-child { border-bottom:none; }
        .metodo-left { display:flex; align-items:center; gap:8px; }
        .metodo-dot  { width:9px; height:9px; border-radius:50%; flex-shrink:0; }

        /* ── ALERTAS ── */
        .alert-row {
            display:flex; align-items:center; gap:10px;
            padding:10px 0; border-bottom:1px solid var(--border); font-size:13px;
        }
        .alert-row:last-child { border-bottom:none; }
        .alert-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }

        /* ── ACCESOS RÁPIDOS ── */
        .accesos-grid {
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(140px,1fr));
            gap:12px;
            margin-bottom:22px;
        }
        .acceso-btn {
            background:var(--surface); border:1px solid var(--border);
            border-radius:14px; padding:16px 12px;
            display:flex; flex-direction:column; align-items:center; gap:8px;
            cursor:pointer; text-decoration:none; color:var(--text-primary);
            transition:all .18s; text-align:center;
        }
        .acceso-btn:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,.08); border-color:var(--primary); }
        .acceso-btn .ab-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .acceso-btn span { font-size:12px; font-weight:700; }

        /* ── GANANCIA ── */
        .ganancia-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:8px 0; border-bottom:1px solid var(--border); font-size:13px;
        }
        .ganancia-row:last-child { border-bottom:none; }
        .ganancia-row .gr-label { color:var(--text-secondary); }
        .ganancia-row .gr-val   { font-weight:700; }
    </style>
</head>
<body>
    <!-- Splash Screen -->
    <div id="splashScreen" class="splash-screen">
        <div class="splash-content">
            <img src="../../public/img/Splash.png" alt="DASH" class="splash-logo">
            <div class="splash-loader"></div>
        </div>
    </div>

    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <div class="container">

            <!-- ── GREETING ── -->
            <div class="greeting-banner">
                <div>
                    <h2><?= $saludo ?>, <span id="greetName"><?= htmlspecialchars($userName) ?></span> 👋</h2>
                    <p>Aquí está el resumen de tu negocio para hoy</p>
                </div>
                <div class="greeting-right">
                    <div class="date-chip">
                        <i class="fas fa-calendar-alt"></i>
                        <?= ucfirst($hoy) ?>
                    </div>
                    <div class="caja-chip" id="cajaChip">
                        <i class="fas fa-cash-register"></i>
                        <span id="cajaStatus">Caja cerrada</span>
                    </div>
                </div>
            </div>

            <!-- ── KPI CARDS ── -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#10b981,#059669);"></div>
                    <div class="kpi-icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="kpi-label">Ventas de Hoy</div>
                    <div class="kpi-value" id="ventasHoy">$0</div>
                    <div class="kpi-sub" id="ventasHoySub">
                        <i class="fas fa-receipt"></i> <span id="cantVentasHoy">0</span> transacciones
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#3b82f6,#2563eb);"></div>
                    <div class="kpi-icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="kpi-label">Ganancia Neta Hoy</div>
                    <div class="kpi-value" id="gananciaNeta">$0</div>
                    <div class="kpi-sub" id="gastosSub">
                        <i class="fas fa-arrow-down" style="color:#ef4444;"></i> Gastos: <span id="gastosHoy">$0</span>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#f59e0b,#d97706);"></div>
                    <div class="kpi-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="kpi-label">Ventas del Mes</div>
                    <div class="kpi-value" id="ventasMes">$0</div>
                    <div class="kpi-sub">
                        <i class="fas fa-receipt"></i> <span id="cantVentasMes">0</span> transacciones
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#8b5cf6,#7c3aed);"></div>
                    <div class="kpi-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="kpi-label">Productos</div>
                    <div class="kpi-value" id="totalProductos">0</div>
                    <div class="kpi-sub">
                        <i class="fas fa-users"></i> Clientes: <span id="totalClientes">0</span>
                    </div>
                    <span class="kpi-badge" id="stockBajoBadge" style="display:none;"></span>
                </div>
            </div>

            <!-- ── ACCESOS RÁPIDOS ── -->
            <div style="font-size:13px;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:7px;color:var(--text-secondary);">
                <i class="fas fa-bolt" style="color:var(--primary);"></i> ACCESO RÁPIDO
            </div>
            <div class="accesos-grid" style="margin-bottom:22px;">
                <a href="../ventas/index.php" class="acceso-btn">
                    <div class="ab-icon" style="background:rgba(16,185,129,.12);color:#10b981;"><i class="fas fa-cash-register"></i></div>
                    <span>Punto de Venta</span>
                </a>
                <a href="../productos/index.php" class="acceso-btn">
                    <div class="ab-icon" style="background:rgba(59,130,246,.12);color:#3b82f6;"><i class="fas fa-box"></i></div>
                    <span>Productos</span>
                </a>
                <a href="../ventas/historial.php" class="acceso-btn">
                    <div class="ab-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;"><i class="fas fa-history"></i></div>
                    <span>Historial</span>
                </a>
                <a href="../gastos/index.php" class="acceso-btn">
                    <div class="ab-icon" style="background:rgba(239,68,68,.12);color:#ef4444;"><i class="fas fa-money-bill-wave"></i></div>
                    <span>Gastos</span>
                </a>
                <a href="../clientes/index.php" class="acceso-btn">
                    <div class="ab-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;"><i class="fas fa-users"></i></div>
                    <span>Clientes</span>
                </a>
                <a href="../reportes/index.php" class="acceso-btn">
                    <div class="ab-icon" style="background:rgba(6,182,212,.12);color:#06b6d4;"><i class="fas fa-chart-bar"></i></div>
                    <span>Reportes</span>
                </a>
            </div>

            <!-- ── MAIN GRID ── -->
            <div class="dash-main">

                <!-- Columna izquierda -->
                <div style="display:flex;flex-direction:column;gap:18px;">

                    <!-- Gráfico ventas por hora -->
                    <div class="dash-card">
                        <div class="dash-card-title">
                            <i class="fas fa-chart-area"></i> Ventas por Hora — Hoy
                            <span id="labelHoras" style="margin-left:auto;font-size:11px;font-weight:600;color:var(--text-secondary);"></span>
                        </div>
                        <canvas id="chartHoras" height="100"></canvas>
                    </div>

                    <!-- Últimas ventas del día -->
                    <div class="dash-card">
                        <div class="dash-card-title">
                            <i class="fas fa-clock"></i> Últimas Ventas de Hoy
                            <a href="../ventas/historial.php" style="margin-left:auto;font-size:11px;color:var(--primary);font-weight:600;text-decoration:none;">Ver todo →</a>
                        </div>
                        <div id="ultimasVentasList">
                            <div class="empty-state"><i class="fas fa-receipt"></i><p>Sin ventas hoy todavía</p></div>
                        </div>
                    </div>

                </div>

                <!-- Columna derecha -->
                <div style="display:flex;flex-direction:column;gap:18px;">

                    <!-- Resumen del día -->
                    <div class="dash-card">
                        <div class="dash-card-title"><i class="fas fa-sun"></i> Resumen del Día</div>
                        <div class="ganancia-row">
                            <span class="gr-label"><i class="fas fa-arrow-up" style="color:#10b981;margin-right:5px;"></i>Ventas brutas</span>
                            <span class="gr-val" id="resVentasBrutas">$0</span>
                        </div>
                        <div class="ganancia-row">
                            <span class="gr-label"><i class="fas fa-arrow-down" style="color:#ef4444;margin-right:5px;"></i>Gastos del día</span>
                            <span class="gr-val" style="color:#ef4444;" id="resGastos">$0</span>
                        </div>
                        <div class="ganancia-row">
                            <span class="gr-label"><i class="fas fa-percentage" style="color:#3b82f6;margin-right:5px;"></i>Ganancia neta</span>
                            <span class="gr-val" style="color:#10b981;" id="resGanancia">$0</span>
                        </div>
                        <div class="ganancia-row" style="border-top:2px solid var(--border);padding-top:10px;margin-top:4px;">
                            <span class="gr-label"><i class="fas fa-calendar-check" style="color:#8b5cf6;margin-right:5px;"></i>Ventas del mes</span>
                            <span class="gr-val" style="color:#8b5cf6;" id="resVentasMes">$0</span>
                        </div>
                        <div class="ganancia-row">
                            <span class="gr-label"><i class="fas fa-compare" style="color:#f59e0b;margin-right:5px;"></i>vs. ayer</span>
                            <span class="gr-val" id="resVsAyer">—</span>
                        </div>
                    </div>

                    <!-- Métodos de pago hoy -->
                    <div class="dash-card">
                        <div class="dash-card-title"><i class="fas fa-wallet"></i> Métodos de Pago — Hoy</div>
                        <div id="metodosPagoList">
                            <div class="empty-state" style="padding:24px 0;"><i class="fas fa-wallet"></i><p>Sin ventas hoy</p></div>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <div class="dash-card">
                        <div class="dash-card-title"><i class="fas fa-bell"></i> Alertas</div>
                        <div id="alertasList">
                            <div class="empty-state" style="padding:24px 0;"><i class="fas fa-check-circle" style="opacity:.3;color:#10b981;"></i><p>Todo en orden ✓</p></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <script>
        window.APP_BASE = '<?php echo rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/'); ?>';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../../public/js/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
