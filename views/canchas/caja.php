<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Caja & Reportes — Canchas</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
<link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ── Tabs ─────────────────────────────────────────────────────────────────── */
.tab-bar { display:flex;gap:4px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:4px;margin-bottom:22px;width:fit-content; }
.tab-btn { padding:8px 20px;border-radius:8px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;color:var(--text-secondary);transition:.15s;font-family:inherit;display:flex;align-items:center;gap:7px; }
.tab-btn.active { background:#16a34a;color:#fff; }
.tab-btn:not(.active):hover { background:var(--background);color:var(--text-primary); }
.tab-pane { display:none; }
.tab-pane.active { display:block; }

/* ── Page header ──────────────────────────────────────────────────────────── */
.pg-header { background:var(--surface);padding:20px 24px;border-radius:14px;margin-bottom:20px;border:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px; }
.pg-header h1 { margin:0;font-size:20px;font-weight:700;color:var(--text-primary); }
.pg-sub { font-size:12px;color:var(--text-secondary);margin-top:3px; }

/* ── Caja: controles fecha ────────────────────────────────────────────────── */
.date-nav { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.date-nav button { width:34px;height:34px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;color:var(--text-primary);font-size:13px;transition:.15s; }
.date-nav button:hover { background:#16a34a;color:#fff;border-color:#16a34a; }
.date-nav input[type=date] { padding:7px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text-primary); }
.btn-hoy { width:auto !important;padding:0 12px !important;font-size:12px;font-weight:700; }

/* ── Stats / KPI grid ─────────────────────────────────────────────────────── */
.stats-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px; }
.kpi-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px 18px;position:relative;overflow:hidden; }
.kpi-card::before { content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--kc,#16a34a); }
.kpi-lbl { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin-bottom:5px; }
.kpi-val { font-size:1.7rem;font-weight:800;color:var(--text-primary);line-height:1.1; }
.kpi-sub { font-size:11px;color:var(--text-secondary);margin-top:4px; }
.up { color:#22c55e; } .dn { color:#ef4444; } .neu { color:var(--text-secondary); }

/* ── Cards ────────────────────────────────────────────────────────────────── */
.two-col { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px; }
@media(max-width:768px) { .two-col { grid-template-columns:1fr; } }
.sec-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:16px; }
.sec-head { padding:14px 18px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;justify-content:space-between;gap:8px; }
.sec-head i { color:#16a34a; }

/* ── Métodos pago ─────────────────────────────────────────────────────────── */
.metodos-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:14px 16px; }
.met-card { background:var(--background);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center; }
.met-monto { font-size:16px;font-weight:700;margin-bottom:3px; }
.met-lbl { font-size:11px;color:var(--text-secondary); }
.metodo-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600; }
.metodo-efectivo      { background:rgba(22,163,74,.12);color:#16a34a; }
.metodo-transferencia { background:rgba(59,130,246,.12);color:#3b82f6; }
.metodo-tarjeta       { background:rgba(139,92,246,.12);color:#8b5cf6; }

/* ── Por cancha (caja) ────────────────────────────────────────────────────── */
.cancha-row { display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--border); }
.cancha-row:last-child { border-bottom:none; }
.prog-bar { height:5px;border-radius:3px;background:var(--border);overflow:hidden;margin-top:4px; }
.prog-fill { height:100%;background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:3px; }

/* ── Mini bar chart (últimos 7d) ──────────────────────────────────────────── */
.bar-chart { display:flex;align-items:flex-end;gap:6px;height:72px;padding:8px 16px 0; }
.bar-col { flex:1;display:flex;flex-direction:column;align-items:center;gap:3px; }
.bar-rect { width:100%;border-radius:4px 4px 0 0;background:linear-gradient(180deg,#16a34a,#22c55e);min-height:2px; }
.bar-date { font-size:10px;color:var(--text-secondary); }

/* ── Tabla detalle ────────────────────────────────────────────────────────── */
.rep-table { width:100%;border-collapse:collapse;font-size:13px; }
.rep-table th { padding:9px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);border-bottom:1px solid var(--border);background:var(--background); }
.rep-table td { padding:11px 14px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
.rep-table tr:last-child td { border-bottom:none; }
.rep-table tr:hover td { background:var(--background); }
.rank { display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;font-size:10px;font-weight:800;background:var(--border);color:var(--text-secondary); }
.r1{background:#fbbf24;color:#7c4a00;} .r2{background:#94a3b8;color:#fff;} .r3{background:#b45309;color:#fff;}
.bar-mini { height:4px;border-radius:2px;background:#16a34a;opacity:.6;margin-top:3px; }
.sport-badge { display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:600;background:rgba(22,163,74,.1);color:#16a34a; }

/* ── Reportes: filtros período ────────────────────────────────────────────── */
.rep-filters { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.rep-filters input[type=date] { padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-primary);font-size:13px; }
.p-btn { padding:5px 12px;border:1px solid var(--border);border-radius:20px;background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:12px;font-weight:600;transition:.15s;font-family:inherit; }
.p-btn:hover,.p-btn.active { background:#16a34a;color:#fff;border-color:#16a34a; }
.btn-aplicar { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:600;background:#16a34a;color:#fff;transition:.15s;font-family:inherit; }
.btn-aplicar:hover { background:#15803d; }

/* ── Charts ───────────────────────────────────────────────────────────────── */
.charts-grid { display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px; }
@media(max-width:768px) { .charts-grid { grid-template-columns:1fr; } }
.chart-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px; }
.chart-title { font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:12px;display:flex;align-items:center;gap:7px; }
.chart-title i { color:#16a34a; }
canvas { max-height:220px; }
.pago-row { display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid var(--border); }
.pago-row:last-child { border-bottom:none; }
.pago-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }

/* ── Ocupación horaria ────────────────────────────────────────────────────── */
.hora-bar { height:16px;border-radius:3px;background:#16a34a;opacity:.7;min-width:2px; }

/* ── Misc ─────────────────────────────────────────────────────────────────── */
.empty { text-align:center;padding:28px;color:var(--text-secondary);font-size:13px; }
.empty i { display:block;font-size:1.8rem;margin-bottom:8px;opacity:.3; }
.spin { animation:spin .8s linear infinite; } @keyframes spin { to { transform:rotate(360deg); } }
</style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Header -->
            <div class="pg-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:11px;background:rgba(22,163,74,.12);display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:17px;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <h1>Caja <span style="color:#16a34a">&amp;</span> Reportes</h1>
                        <div class="pg-sub" id="pgSub">Canchas</div>
                    </div>
                </div>
                <!-- controles dinámicos según pestaña -->
                <div id="ctrlCaja" class="date-nav">
                    <button onclick="cambiarDia(-1)"><i class="fas fa-chevron-left"></i></button>
                    <input type="date" id="inputFecha" onchange="cargarCaja()">
                    <button onclick="cambiarDia(1)"><i class="fas fa-chevron-right"></i></button>
                    <button class="btn-hoy" onclick="irHoy()">Hoy</button>
                </div>
                <div id="ctrlRep" class="rep-filters" style="display:none;">
                    <div style="display:flex;gap:5px;">
                        <button class="p-btn active" data-d="7">7d</button>
                        <button class="p-btn" data-d="30">30d</button>
                        <button class="p-btn" data-d="90">90d</button>
                    </div>
                    <input type="date" id="rDesde">
                    <input type="date" id="rHasta">
                    <button class="btn-aplicar" onclick="cargarReportes()">
                        <i class="fas fa-sync-alt" id="icoRef"></i> Aplicar
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tab-bar">
                <button class="tab-btn active" onclick="setTab('caja')">
                    <i class="fas fa-cash-register"></i> Caja del Día
                </button>
                <button class="tab-btn" onclick="setTab('rep')">
                    <i class="fas fa-chart-line"></i> Reportes
                </button>
            </div>

            <!-- ══════════════════════════════════════
                 PESTAÑA 1: CAJA DEL DÍA
            ══════════════════════════════════════ -->
            <div class="tab-pane active" id="pane-caja">

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="kpi-card" style="--kc:#16a34a"><div class="kpi-lbl">Ingresos</div><div class="kpi-val" id="cIngresos">$0</div><div class="kpi-sub" id="cFechaLbl">Hoy</div></div>
                    <div class="kpi-card" style="--kc:#0ea5e9"><div class="kpi-lbl">Total Reservas</div><div class="kpi-val" id="cTotal">0</div></div>
                    <div class="kpi-card" style="--kc:#22c55e"><div class="kpi-lbl">Confirmadas</div><div class="kpi-val" id="cConf">0</div></div>
                    <div class="kpi-card" style="--kc:#f59e0b"><div class="kpi-lbl">Pendientes</div><div class="kpi-val" id="cPend">0</div></div>
                </div>

                <div class="two-col">
                    <!-- Métodos de pago -->
                    <div class="sec-card" style="margin-bottom:0">
                        <div class="sec-head"><span><i class="fas fa-wallet"></i> Métodos de Pago</span></div>
                        <div class="metodos-grid">
                            <div class="met-card"><div class="met-monto" style="color:#16a34a" id="mEfectivo">$0</div><div class="met-lbl"><i class="fas fa-money-bill-wave"></i> Efectivo</div></div>
                            <div class="met-card"><div class="met-monto" style="color:#3b82f6" id="mTransferencia">$0</div><div class="met-lbl"><i class="fas fa-mobile-alt"></i> Transferencia</div></div>
                            <div class="met-card"><div class="met-monto" style="color:#8b5cf6" id="mTarjeta">$0</div><div class="met-lbl"><i class="fas fa-credit-card"></i> Tarjeta</div></div>
                        </div>
                    </div>

                    <!-- Por cancha -->
                    <div class="sec-card" style="margin-bottom:0">
                        <div class="sec-head"><span><i class="fas fa-futbol"></i> Por Cancha</span></div>
                        <div id="porCanchaBody"><div class="empty"><i class="fas fa-futbol"></i>Sin datos</div></div>
                    </div>
                </div>

                <!-- Mini chart 7 días -->
                <div class="sec-card">
                    <div class="sec-head"><span><i class="fas fa-chart-bar"></i> Últimos 7 días</span></div>
                    <div class="bar-chart" id="barChart" style="padding-bottom:10px;"><p style="color:var(--text-secondary);margin:auto;font-size:13px;">Cargando...</p></div>
                </div>

                <!-- Detalle reservas -->
                <div class="sec-card">
                    <div class="sec-head">
                        <span><i class="fas fa-list"></i> Detalle de Reservas</span>
                        <span id="badgeDetalle" style="font-size:11px;color:var(--text-secondary);font-weight:500;"></span>
                    </div>
                    <div id="detalleBody"><div class="empty"><i class="fas fa-receipt"></i>Sin reservas confirmadas</div></div>
                </div>

            </div><!-- /pane-caja -->

            <!-- ══════════════════════════════════════
                 PESTAÑA 2: REPORTES
            ══════════════════════════════════════ -->
            <div class="tab-pane" id="pane-rep">

                <!-- KPIs -->
                <div class="stats-grid" id="rKpiGrid">
                    <div class="kpi-card"><div class="kpi-lbl">Cargando...</div></div>
                </div>

                <!-- Ingresos por día + métodos pago -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-title"><i class="fas fa-chart-area"></i> Ingresos por día</div>
                        <canvas id="rChartDia"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title"><i class="fas fa-credit-card"></i> Métodos de pago</div>
                        <canvas id="rChartPagos"></canvas>
                        <div id="rPagosList" style="margin-top:10px;"></div>
                    </div>
                </div>

                <div class="two-col">
                    <!-- Por cancha -->
                    <div class="sec-card" style="margin-bottom:0">
                        <div class="sec-head"><span><i class="fas fa-futbol"></i> Por cancha</span></div>
                        <table class="rep-table">
                            <thead><tr><th>#</th><th>Cancha</th><th>Reservas</th><th>Horas</th><th>Ingresos</th></tr></thead>
                            <tbody id="rTbCanchas"><tr><td colspan="5" class="empty"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                        </table>
                    </div>

                    <!-- Ocupación horaria -->
                    <div class="sec-card" style="margin-bottom:0">
                        <div class="sec-head"><span><i class="fas fa-clock"></i> Ocupación por hora</span></div>
                        <div id="rHorasWrap" style="padding:12px 16px;"></div>
                    </div>
                </div>

                <!-- Días de la semana -->
                <div class="sec-card" style="margin-top:16px;">
                    <div class="sec-head"><span><i class="fas fa-calendar-week"></i> Actividad por día de la semana</span></div>
                    <div style="padding:12px 16px 16px;">
                        <canvas id="rChartSemana" style="max-height:180px;"></canvas>
                    </div>
                </div>

            </div><!-- /pane-rep -->

        </div>
    </div>
</div>

<script>
const fmtPeso = new Intl.NumberFormat('es-AR', { style:'currency', currency:'ARS', maximumFractionDigits:0 });
const fmt     = n => fmtPeso.format(Number(n) || 0);

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ─── Tab switching ───────────────────────────────────────────────────────────
let repCargado = false;

function setTab(t) {
    document.querySelectorAll('.tab-btn').forEach((b, i) =>
        b.classList.toggle('active', (i === 0 ? 'caja' : 'rep') === t));
    document.getElementById('pane-caja').classList.toggle('active', t === 'caja');
    document.getElementById('pane-rep').classList.toggle('active',  t === 'rep');
    document.getElementById('ctrlCaja').style.display = t === 'caja' ? '' : 'none';
    document.getElementById('ctrlRep').style.display  = t === 'rep'  ? '' : 'none';

    if (t === 'rep' && !repCargado) { repCargado = true; cargarReportes(); }
}

// ════════════════════════════════════════════════════════
//  PESTAÑA 1: CAJA
// ════════════════════════════════════════════════════════
function hoyStr() {
    const h = new Date();
    return h.getFullYear() + '-' + String(h.getMonth()+1).padStart(2,'0') + '-' + String(h.getDate()).padStart(2,'0');
}

function irHoy() {
    document.getElementById('inputFecha').value = hoyStr();
    cargarCaja();
}

function cambiarDia(d) {
    const inp = document.getElementById('inputFecha');
    const dt  = new Date(inp.value + 'T00:00:00');
    dt.setDate(dt.getDate() + d);
    inp.value = dt.getFullYear() + '-' + String(dt.getMonth()+1).padStart(2,'0') + '-' + String(dt.getDate()).padStart(2,'0');
    cargarCaja();
}

async function cargarCaja() {
    const fecha = document.getElementById('inputFecha').value;
    const r = await fetch(`../../api/canchas/caja.php?fecha=${fecha}`);
    const j = await r.json();
    if (!j.success) return;
    const { totales, porCancha, detalle, semana } = j.data;

    const dt = new Date(fecha + 'T00:00:00');
    document.getElementById('cFechaLbl').textContent =
        dt.toLocaleDateString('es-AR', {weekday:'short', day:'numeric', month:'short'});
    document.getElementById('pgSub').textContent =
        dt.toLocaleDateString('es-AR', {weekday:'long', day:'numeric', month:'long'});

    document.getElementById('cIngresos').textContent = fmt(totales.ingresos_total);
    document.getElementById('cTotal').textContent    = totales.total_reservas || 0;
    document.getElementById('cConf').textContent     = totales.confirmadas    || 0;
    document.getElementById('cPend').textContent     = totales.pendientes     || 0;

    document.getElementById('mEfectivo').textContent      = fmt(totales.efectivo);
    document.getElementById('mTransferencia').textContent = fmt(totales.transferencia);
    document.getElementById('mTarjeta').textContent       = fmt(totales.tarjeta);

    // Por cancha
    const maxC = Math.max(...porCancha.map(c => parseFloat(c.ingresos||0)), 1);
    document.getElementById('porCanchaBody').innerHTML = porCancha.length
        ? porCancha.map(c => {
            const pct = Math.round(parseFloat(c.ingresos||0) / maxC * 100);
            return `<div class="cancha-row">
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:13px">${esc(c.cancha_nombre)}</div>
                    <div style="font-size:11px;color:var(--text-secondary)">${esc(c.deporte)} · ${c.reservas} res.</div>
                    <div class="prog-bar"><div class="prog-fill" style="width:${pct}%"></div></div>
                </div>
                <div style="margin-left:14px;font-weight:700;color:${parseFloat(c.ingresos)>0?'#16a34a':'var(--text-secondary)'};">${fmt(c.ingresos)}</div>
            </div>`;
        }).join('')
        : '<div class="empty"><i class="fas fa-futbol"></i>Sin canchas</div>';

    // Mini chart
    const maxB = Math.max(...semana.map(s => parseFloat(s.ingresos||0)), 1);
    document.getElementById('barChart').innerHTML = semana.length
        ? semana.map(s => {
            const h = Math.max(Math.round(parseFloat(s.ingresos||0) / maxB * 60), 2);
            const esHoy = s.fecha === hoyStr();
            const lbl = new Date(s.fecha + 'T00:00:00').toLocaleDateString('es-AR', {day:'2-digit', month:'2-digit'});
            return `<div class="bar-col">
                <div class="bar-rect" style="height:${h}px;opacity:${esHoy?1:.5};${esHoy?'box-shadow:0 2px 8px rgba(22,163,74,.4)':''}"></div>
                <div class="bar-date" style="font-weight:${esHoy?700:400};color:${esHoy?'#16a34a':''}">${lbl}</div>
            </div>`;
        }).join('')
        : '<p style="color:var(--text-secondary);margin:auto;font-size:13px;">Sin datos</p>';

    // Detalle
    document.getElementById('badgeDetalle').textContent =
        detalle.length + ' confirmada' + (detalle.length !== 1 ? 's' : '');
    document.getElementById('detalleBody').innerHTML = detalle.length
        ? `<div style="overflow-x:auto;"><table class="rep-table">
            <thead><tr><th>Cancha</th><th>Horario</th><th>Cliente</th><th>Teléfono</th><th>Monto</th><th>Método</th></tr></thead>
            <tbody>${detalle.map(r => `
                <tr>
                    <td><strong>${esc(r.cancha_nombre)}</strong><br><span style="font-size:11px;color:var(--text-secondary)">${esc(r.deporte)}</span></td>
                    <td style="white-space:nowrap">${r.hora_inicio.slice(0,5)} – ${r.hora_fin.slice(0,5)}</td>
                    <td>${esc(r.cliente_nombre||'—')}</td>
                    <td style="color:var(--text-secondary)">${esc(r.cliente_telefono||'—')}</td>
                    <td style="font-weight:700;color:#16a34a">${fmt(r.monto)}</td>
                    <td><span class="metodo-badge metodo-${r.metodo_pago}">${esc(r.metodo_pago)}</span></td>
                </tr>`).join('')}
            </tbody>
            <tfoot><tr style="background:var(--background)">
                <td colspan="4" style="font-weight:700;padding:11px 14px">Total</td>
                <td style="font-weight:700;color:#16a34a;padding:11px 14px">${fmt(detalle.reduce((s,r)=>s+parseFloat(r.monto||0),0))}</td>
                <td></td>
            </tr></tfoot>
           </table></div>`
        : '<div class="empty"><i class="fas fa-receipt"></i>Sin reservas confirmadas este día</div>';
}

// ════════════════════════════════════════════════════════
//  PESTAÑA 2: REPORTES
// ════════════════════════════════════════════════════════
const REP_API = '../../api/canchas/reportes.php';
let rChartDia = null, rChartPagos = null, rChartSemana = null;
const PAGO_COL = { efectivo:'#16a34a', transferencia:'#0ea5e9', tarjeta:'#8b5cf6', otro:'#94a3b8' };
const PAGO_LBL = { efectivo:'Efectivo', transferencia:'Transferencia', tarjeta:'Tarjeta', otro:'Otro' };
const DEPORTE_ICONS = { futbol:'fa-futbol', tenis:'fa-baseball-bat-ball', padel:'fa-table-tennis-paddle-ball',
    basquet:'fa-basketball', voley:'fa-volleyball', otro:'fa-dumbbell' };
const DIA_ES = { Sunday:'Dom', Monday:'Lun', Tuesday:'Mar', Wednesday:'Mié',
    Thursday:'Jue', Friday:'Vie', Saturday:'Sáb' };

const hoy = new Date();
function fmtDate(d) { return d.toISOString().split('T')[0]; }

// Período inicial
document.getElementById('rHasta').value = fmtDate(hoy);
document.getElementById('rDesde').value = fmtDate(new Date(hoy - 6*864e5));

document.querySelectorAll('.p-btn').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.p-btn').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    const d = parseInt(b.dataset.d);
    document.getElementById('rDesde').value = fmtDate(new Date(hoy - (d-1)*864e5));
    document.getElementById('rHasta').value = fmtDate(hoy);
    cargarReportes();
}));

async function cargarReportes() {
    const desde = document.getElementById('rDesde').value;
    const hasta = document.getElementById('rHasta').value;
    document.getElementById('icoRef').className = 'fas fa-sync-alt spin';
    const qs = `desde=${desde}&hasta=${hasta}`;

    const [res, dias, canchas, horas, semana] = await Promise.all([
        fetch(`${REP_API}?tipo=resumen&${qs}`).then(r => r.json()),
        fetch(`${REP_API}?tipo=ingresos_dia&${qs}`).then(r => r.json()),
        fetch(`${REP_API}?tipo=por_cancha&${qs}`).then(r => r.json()),
        fetch(`${REP_API}?tipo=ocupacion_horaria&${qs}`).then(r => r.json()),
        fetch(`${REP_API}?tipo=dias_semana&${qs}`).then(r => r.json()),
    ]);

    document.getElementById('icoRef').className = 'fas fa-sync-alt';

    if (res.success)     renderRKPIs(res.data);
    if (dias.success)    renderRDia(dias.data, desde, hasta);
    if (res.success)     renderRPagos(res.data.metodos_pago);
    if (canchas.success) renderRCanchas(canchas.data);
    if (horas.success)   renderRHoras(horas.data);
    if (semana.success)  renderRSemana(semana.data);
}

function renderRKPIs(d) {
    const varC = d.var_ingresos;
    const varHtml = varC === null
        ? '<span class="neu">Sin período ant.</span>'
        : varC >= 0 ? `<span class="up">▲ ${varC}%</span>` : `<span class="dn">▼ ${Math.abs(varC)}%</span>`;
    const tasa = d.total_reservas > 0 ? Math.round(d.confirmadas / d.total_reservas * 100) : 0;
    document.getElementById('rKpiGrid').innerHTML = `
        <div class="kpi-card" style="--kc:#16a34a"><div class="kpi-lbl">Ingresos</div><div class="kpi-val">${fmt(d.ingresos)}</div><div class="kpi-sub">${varHtml}</div></div>
        <div class="kpi-card" style="--kc:#0ea5e9"><div class="kpi-lbl">Total Reservas</div><div class="kpi-val">${d.total_reservas}</div><div class="kpi-sub">En ${d.periodo_dias} días</div></div>
        <div class="kpi-card" style="--kc:#22c55e"><div class="kpi-lbl">Confirmadas</div><div class="kpi-val">${d.confirmadas}</div><div class="kpi-sub">Tasa: ${tasa}%</div></div>
        <div class="kpi-card" style="--kc:#f59e0b"><div class="kpi-lbl">Pendientes</div><div class="kpi-val">${d.pendientes}</div><div class="kpi-sub">${d.canceladas} canceladas</div></div>
        <div class="kpi-card" style="--kc:#6366f1"><div class="kpi-lbl">Ticket Promedio</div><div class="kpi-val">${fmt(d.ticket_promedio)}</div><div class="kpi-sub">Por reserva</div></div>
        <div class="kpi-card" style="--kc:#14b8a6"><div class="kpi-lbl">Horas Reservadas</div><div class="kpi-val">${d.horas_reservadas}h</div><div class="kpi-sub">Tiempo en cancha</div></div>
    `;
}

function renderRDia(rows, desde, hasta) {
    const mapa = {};
    rows.forEach(r => mapa[r.fecha] = parseFloat(r.total));
    const labels = [], data = [];
    const d = new Date(desde), fin = new Date(hasta);
    while (d <= fin) {
        const k = d.toISOString().split('T')[0];
        labels.push(d.toLocaleDateString('es-AR', {day:'numeric', month:'short'}));
        data.push(mapa[k] ?? 0);
        d.setDate(d.getDate() + 1);
    }
    const ctx = document.getElementById('rChartDia').getContext('2d');
    if (rChartDia) rChartDia.destroy();
    rChartDia = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor:'rgba(22,163,74,.6)', borderColor:'#16a34a', borderWidth:1, borderRadius:4 }] },
        options: { responsive:true, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:c => fmt(c.parsed.y) } } },
            scales:{ x:{ grid:{display:false}, ticks:{maxRotation:0, font:{size:10}} }, y:{ beginAtZero:true, ticks:{ callback:v => fmt(v), font:{size:10} } } } }
    });
}

function renderRPagos(pagos) {
    const ctx = document.getElementById('rChartPagos').getContext('2d');
    if (rChartPagos) rChartPagos.destroy();
    if (!pagos.length) {
        document.getElementById('rPagosList').innerHTML = '<div class="empty"><i class="fas fa-credit-card"></i>Sin datos</div>';
        return;
    }
    const total = pagos.reduce((s,p) => s + parseFloat(p.total), 0);
    rChartPagos = new Chart(ctx, {
        type:'doughnut',
        data:{ labels: pagos.map(p => PAGO_LBL[p.metodo_pago]??p.metodo_pago),
            datasets:[{ data: pagos.map(p => parseFloat(p.total)), backgroundColor: pagos.map(p => PAGO_COL[p.metodo_pago]??'#94a3b8'), borderWidth:0 }] },
        options:{ cutout:'65%', plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:c => fmt(c.parsed) } } } }
    });
    document.getElementById('rPagosList').innerHTML = pagos.map(p => {
        const pct = total > 0 ? Math.round(parseFloat(p.total)/total*100) : 0;
        return `<div class="pago-row">
            <div class="pago-dot" style="background:${PAGO_COL[p.metodo_pago]??'#94a3b8'}"></div>
            <div style="flex:1;font-size:12px">${PAGO_LBL[p.metodo_pago]??p.metodo_pago}</div>
            <div style="font-size:12px;font-weight:700">${fmt(parseFloat(p.total))}</div>
            <div style="font-size:11px;color:var(--text-secondary);min-width:30px;text-align:right">${pct}%</div>
        </div>`;
    }).join('');
}

function renderRCanchas(rows) {
    if (!rows.length) {
        document.getElementById('rTbCanchas').innerHTML = '<tr><td colspan="5" class="empty"><i class="fas fa-futbol"></i>Sin datos</td></tr>';
        return;
    }
    const maxI = Math.max(...rows.map(r => r.ingresos), 1);
    document.getElementById('rTbCanchas').innerHTML = rows.map((r, i) => {
        const rc = i===0?'r1':i===1?'r2':i===2?'r3':'';
        const icon = DEPORTE_ICONS[r.deporte?.toLowerCase()]??'fa-dumbbell';
        return `<tr>
            <td><span class="rank ${rc}">${i+1}</span></td>
            <td><div style="font-weight:600">${esc(r.cancha)}</div><span class="sport-badge"><i class="fas ${icon}" style="font-size:9px"></i> ${esc(r.deporte??'—')}</span></td>
            <td>${r.confirmadas}<span style="color:var(--text-secondary);font-size:11px"> /${r.total_reservas}</span></td>
            <td>${r.horas_ocupadas}h</td>
            <td><div style="font-weight:700">${fmt(r.ingresos)}</div><div class="bar-mini" style="width:${Math.round(r.ingresos/maxI*100)}%"></div></td>
        </tr>`;
    }).join('');
}

function renderRHoras(rows) {
    if (!rows.length) {
        document.getElementById('rHorasWrap').innerHTML = '<div class="empty"><i class="fas fa-clock"></i>Sin datos</div>';
        return;
    }
    const maxR = Math.max(...rows.map(r => parseInt(r.reservas)), 1);
    document.getElementById('rHorasWrap').innerHTML = rows.map(r => {
        const pct = Math.round(parseInt(r.reservas)/maxR*100);
        return `<div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
            <div style="width:38px;font-size:11px;color:var(--text-secondary);text-align:right;flex-shrink:0">${String(r.hora).padStart(2,'0')}:00</div>
            <div class="hora-bar" style="width:${Math.max(pct,2)}%"></div>
            <div style="font-size:12px;font-weight:600;min-width:18px">${r.reservas}</div>
        </div>`;
    }).join('');
}

function renderRSemana(rows) {
    const ctx = document.getElementById('rChartSemana').getContext('2d');
    if (rChartSemana) rChartSemana.destroy();
    if (!rows.length) return;
    rChartSemana = new Chart(ctx, {
        type:'bar',
        data:{ labels: rows.map(r => DIA_ES[r.dia_nombre]??r.dia_nombre),
            datasets:[
                { label:'Reservas', data: rows.map(r=>parseInt(r.reservas)), backgroundColor:'rgba(22,163,74,.6)', borderColor:'#16a34a', borderWidth:1, borderRadius:3, yAxisID:'y' },
                { label:'Ingresos', data: rows.map(r=>parseFloat(r.ingresos)), type:'line', borderColor:'#0ea5e9', backgroundColor:'rgba(14,165,233,.1)', borderWidth:2, tension:.3, fill:true, yAxisID:'y2', pointRadius:3 }
            ]},
        options:{ responsive:true,
            plugins:{ legend:{ display:true, labels:{ font:{size:11}, boxWidth:12 } },
                tooltip:{ callbacks:{ label:c => c.datasetIndex===1 ? fmt(c.parsed.y) : `${c.parsed.y} reservas` } } },
            scales:{
                x:{ grid:{display:false} },
                y:{ beginAtZero:true, position:'left', ticks:{stepSize:1,font:{size:10}}, grid:{display:false} },
                y2:{ beginAtZero:true, position:'right', ticks:{ callback:v=>fmt(v), font:{size:10} }, grid:{color:'rgba(0,0,0,.04)'} }
            }}
    });
}

// ── Init ─────────────────────────────────────────────────────────────────────
document.getElementById('inputFecha').value = hoyStr();
cargarCaja();
</script>
</body>
</html>
