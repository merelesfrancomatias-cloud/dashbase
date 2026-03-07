<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        /* ---- HERO ---- */
        .rep-hero {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }
        .rep-hero::before {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(var(--primary-rgb, 59,130,246),.07) 0%, transparent 70%);
            top: -80px; right: -40px;
            border-radius: 50%;
            pointer-events: none;
        }
        .rep-hero h1 {
            margin: 0; font-size: 24px; font-weight: 800;
            letter-spacing: -.3px; color: var(--text-primary);
            position: relative; z-index: 1;
        }
        .rep-hero h1 i { color: var(--primary); }
        .rep-hero p  {
            margin: 5px 0 0; font-size: 13px;
            color: var(--text-secondary); position: relative; z-index: 1;
        }
        .rep-hero .hero-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; position:relative; z-index:1; }
        .period-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--background); border: 1px solid var(--border);
            border-radius: 20px; padding: 6px 14px; font-size: 12px; font-weight: 700;
            color: var(--text-secondary);
        }
        .btn-export {
            padding: 9px 18px; background: var(--primary); color: #fff;
            border: none; border-radius: 10px;
            cursor: pointer; font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 7px;
            transition: opacity .2s;
        }
        .btn-export:hover { opacity: .88; }

        /* ---- FILTROS ---- */
        .rep-filters {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 26px;
            display: flex;
            gap: 14px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .rep-filters .fg { display: flex; flex-direction: column; gap: 5px; }
        .rep-filters label { font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .5px; }
        .rep-filters select, .rep-filters input[type="date"] {
            padding: 9px 14px; border: 1px solid var(--border);
            border-radius: 10px; background: var(--background);
            color: var(--text-primary); font-size: 13px; min-width: 150px;
        }
        .rep-filters select:focus, .rep-filters input[type="date"]:focus {
            outline: none; border-color: var(--primary);
        }

        /* ---- KPI GRID ---- */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,.1); }
        .kpi-card .kpi-accent {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 4px;
            border-radius: 16px 16px 0 0;
        }
        .kpi-card .kpi-icon {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; margin-bottom: 14px;
        }
        .kpi-card .kpi-label {
            font-size: 11px; font-weight: 700; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px;
        }
        .kpi-card .kpi-value { font-size: 28px; font-weight: 800; color: var(--text-primary); line-height: 1; margin-bottom: 8px; }
        .kpi-card .kpi-trend { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        .trend-up   { color: #10b981; }
        .trend-down { color: #ef4444; }
        .trend-flat { color: var(--text-secondary); }

        /* ---- CHART LAYOUTS ---- */
        .charts-main {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 28px;
        }
        @media(max-width:960px) {
            .charts-main, .charts-row { grid-template-columns: 1fr; }
        }
        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
        }
        .chart-card h3 {
            margin: 0 0 18px;
            font-size: 15px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
        }
        .chart-card h3 i { color: var(--primary); font-size: 14px; }
        .chart-subtitle {
            margin-left: auto; font-size: 11px; font-weight: 600;
            color: var(--text-secondary);
        }

        /* ---- TOP PRODUCTOS TABLE ---- */
        .top-table { width: 100%; border-collapse: collapse; }
        .top-table th {
            font-size: 11px; font-weight: 700; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: .5px;
            padding: 8px 10px; border-bottom: 2px solid var(--border); text-align: left;
        }
        .top-table td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .top-table tr:last-child td { border-bottom: none; }
        .top-table tr:hover td { background: var(--background); border-radius: 8px; }
        .rank-badge {
            width: 26px; height: 26px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800; flex-shrink: 0;
        }
        .rank-1 { background: linear-gradient(135deg,#fbbf24,#f59e0b); color: #fff; }
        .rank-2 { background: linear-gradient(135deg,#94a3b8,#64748b); color: #fff; }
        .rank-3 { background: linear-gradient(135deg,#fb923c,#ea580c); color: #fff; }
        .rank-n { background: var(--border); color: var(--text-secondary); }
        .prod-thumb {
            width: 34px; height: 34px; border-radius: 8px;
            object-fit: cover; border: 1px solid var(--border);
        }
        .prod-thumb-placeholder {
            width: 34px; height: 34px; border-radius: 8px;
            background: var(--border); display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); font-size: 14px;
        }
        .bar-row { display:flex; align-items:center; gap:8px; }
        .bar-wrap { flex:1; height:6px; background:var(--border); border-radius:10px; overflow:hidden; }
        .bar-fill  { height:100%; border-radius:10px; background: linear-gradient(90deg,#10b981,#06b6d4); transition: width .8s ease; }

        /* ---- MÉTODOS LEGEND ---- */
        .metodo-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 0; border-bottom: 1px solid var(--border); font-size: 13px;
        }
        .metodo-row:last-child { border-bottom: none; }
        .metodo-left { display: flex; align-items: center; gap: 8px; }
        .metodo-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .metodo-pct { font-size: 11px; font-weight: 700; color: var(--text-secondary); }

        /* ---- QUICK REPORTS ---- */
        .quick-reports {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 32px;
        }
        .quick-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 18px;
            display: flex; flex-direction: column; gap: 8px;
            cursor: pointer; transition: all .18s;
        }
        .quick-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); border-color: var(--primary); }
        .quick-card .qc-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .quick-card h4 { margin: 0; font-size: 13px; font-weight: 700; }
        .quick-card p  { margin: 0; font-size: 11px; color: var(--text-secondary); }

        /* ---- LOADING ---- */
        .rep-loading { text-align: center; padding: 50px 20px; color: var(--text-secondary); }
        .rep-loading .spin {
            width: 36px; height: 36px;
            border: 3px solid var(--border); border-top-color: var(--primary);
            border-radius: 50%; animation: rep-spin .8s linear infinite; margin: 0 auto 14px;
        }
        @keyframes rep-spin { to { transform: rotate(360deg); } }
        .rep-empty { text-align:center; padding:40px 20px; color:var(--text-secondary); font-size:13px; }
        .rep-empty i { font-size:36px; display:block; margin-bottom:10px; opacity:.4; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <div class="container">

            <!-- ===== HERO ===== -->
            <div class="rep-hero">
                <div>
                    <h1><i class="fas fa-chart-line" style="margin-right:10px;opacity:.9;"></i>Reportes y Análisis</h1>
                    <p>Monitorea el rendimiento de tu negocio en tiempo real</p>
                </div>
                <div class="hero-actions">
                    <span class="period-badge" id="heroPeriodo">
                        <i class="fas fa-calendar-alt"></i> <span id="heroPeriodoText">Este Mes</span>
                    </span>
                    <button class="btn-export" onclick="reportesModule.exportarPDF()">
                        <i class="fas fa-download"></i> Exportar PDF
                    </button>
                </div>
            </div>

            <!-- ===== FILTROS ===== -->
            <div class="rep-filters">
                <div class="fg">
                    <label><i class="fas fa-calendar"></i> Período</label>
                    <select id="filtroPeriodo">
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="semana">Esta Semana</option>
                        <option value="mes" selected>Este Mes</option>
                        <option value="mes_anterior">Mes Anterior</option>
                        <option value="trimestre">Este Trimestre</option>
                        <option value="anio">Este Año</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>
                <div class="fg" id="wrapFechaDesde" style="display:none;">
                    <label>Desde</label>
                    <input type="date" id="fechaDesde">
                </div>
                <div class="fg" id="wrapFechaHasta" style="display:none;">
                    <label>Hasta</label>
                    <input type="date" id="fechaHasta">
                </div>
                <div class="fg">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary" id="btnActualizar" style="padding:9px 20px;display:flex;align-items:center;gap:7px;">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div class="fg" style="margin-left:auto;">
                    <label>&nbsp;</label>
                    <span id="lastUpdated" style="font-size:11px;color:var(--text-secondary);padding:10px 0;display:block;"></span>
                </div>
            </div>

            <!-- ===== KPI CARDS ===== -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#10b981,#059669);"></div>
                    <div class="kpi-icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="kpi-label">Ventas Totales</div>
                    <div class="kpi-value" id="kpiTotalVentas">—</div>
                    <div class="kpi-trend" id="kpiTrendVentas"></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#3b82f6,#2563eb);"></div>
                    <div class="kpi-icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="kpi-label">Ganancia Neta</div>
                    <div class="kpi-value" id="kpiGanancia">—</div>
                    <div class="kpi-trend" id="kpiTrendGanancia"></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#f59e0b,#d97706);"></div>
                    <div class="kpi-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="kpi-label">Tickets Vendidos</div>
                    <div class="kpi-value" id="kpiTickets">—</div>
                    <div class="kpi-trend" id="kpiTrendTickets"></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-accent" style="background:linear-gradient(90deg,#8b5cf6,#7c3aed);"></div>
                    <div class="kpi-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="kpi-label">Ticket Promedio</div>
                    <div class="kpi-value" id="kpiPromedio">—</div>
                    <div class="kpi-trend" id="kpiTrendPromedio"></div>
                </div>
            </div>

            <!-- ===== FILA 1: Evolución ventas + Métodos de pago ===== -->
            <div class="charts-main">
                <div class="chart-card">
                    <h3>
                        <i class="fas fa-chart-area"></i> Evolución de Ventas
                        <span class="chart-subtitle" id="labelEvolucion"></span>
                    </h3>
                    <canvas id="chartVentas" height="110"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-wallet"></i> Métodos de Pago</h3>
                    <canvas id="chartMetodosPago" height="190"></canvas>
                    <div id="metodosLegend" style="margin-top:16px;"></div>
                </div>
            </div>

            <!-- ===== FILA 2: Categorías + Top productos ===== -->
            <div class="charts-row">
                <div class="chart-card">
                    <h3><i class="fas fa-tags"></i> Ventas por Categoría</h3>
                    <canvas id="chartCategorias" height="240"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-trophy"></i> Top Productos Vendidos</h3>
                    <div id="topProductosContainer">
                        <div class="rep-loading">
                            <div class="spin"></div>
                            <p>Cargando datos...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== ACCESOS RÁPIDOS ===== -->
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-th-large" style="color:var(--primary);"></i> Accesos Rápidos
            </h3>
            <div class="quick-reports">
                <div class="quick-card" onclick="reportesModule.verReporte('ventas')">
                    <div class="qc-icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Detalle Ventas</h4>
                    <p>Por período, producto y categoría</p>
                </div>
                <div class="quick-card" onclick="reportesModule.verReporte('gastos')">
                    <div class="qc-icon" style="background:rgba(239,68,68,.12);color:#ef4444;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h4>Gastos</h4>
                    <p>Tendencias y comparativas</p>
                </div>
                <div class="quick-card" onclick="reportesModule.verReporte('rentabilidad')">
                    <div class="qc-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h4>Rentabilidad</h4>
                    <p>Márgenes y análisis financiero</p>
                </div>
                <div class="quick-card" onclick="reportesModule.verReporte('inventario')">
                    <div class="qc-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h4>Inventario</h4>
                    <p>Stock y valorización</p>
                </div>
                <div class="quick-card" onclick="reportesModule.verReporte('caja')">
                    <div class="qc-icon" style="background:rgba(6,182,212,.12);color:#06b6d4;">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h4>Caja</h4>
                    <p>Historial y arqueos</p>
                </div>
            </div>

        </div><!-- .container -->
    </main>

    <script>
        window.APP_BASE = '<?php echo rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/'); ?>';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../../public/js/reportes.js?v=<?php echo time(); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.reportesModule = new ReportesModule();
        });
    </script>
</body>
</html>
