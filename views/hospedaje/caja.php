<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '',
    str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja & Reportes — Hospedaje</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__,2).'/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__,2).'/public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --hosp:#6366f1; --hosp-light:rgba(99,102,241,.1); }

        /* Tabs */
        .htabs { display:flex; gap:4px; padding:0 24px; border-bottom:1px solid var(--border); background:var(--surface); }
        .htab  { padding:14px 20px; border:none; background:none; cursor:pointer; font-size:14px;
                 font-weight:600; color:var(--text-secondary); border-bottom:2px solid transparent;
                 margin-bottom:-1px; transition:.15s; font-family:inherit; }
        .htab.active { color:var(--hosp); border-bottom-color:var(--hosp); }
        .htab-pane { display:none; padding:24px; }
        .htab-pane.active { display:block; }

        /* Stats */
        .stats-row  { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:24px; }
        .stat-card  { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .stat-icon  { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .stat-info .stat-label { font-size:11px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin:0 0 2px; }
        .stat-info .stat-value { font-size:22px; font-weight:800; color:var(--text-primary); margin:0; }

        /* Habitaciones mini-grid */
        .hab-mini-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(80px,1fr)); gap:8px; margin-bottom:24px; }
        .hab-mini      { border-radius:10px; padding:10px 8px; text-align:center; border:1px solid var(--border); cursor:default; }
        .hab-mini .num { font-size:14px; font-weight:800; }
        .hab-mini .tip { font-size:10px; opacity:.7; }
        .hab-libre     { background:rgba(15,209,134,.1); border-color:rgba(15,209,134,.3); }
        .hab-ocupada   { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); }
        .hab-limpieza  { background:rgba(251,191,36,.1); border-color:rgba(251,191,36,.3); }
        .hab-mant      { background:rgba(156,163,175,.1); border-color:rgba(156,163,175,.3); }

        /* Movimientos tabla */
        .mov-table { width:100%; border-collapse:collapse; font-size:13px; }
        .mov-table th { padding:10px 14px; background:var(--background); color:var(--text-secondary); font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); text-align:left; }
        .mov-table td { padding:11px 14px; border-bottom:1px solid var(--border); color:var(--text-primary); }
        .mov-table tr:last-child td { border-bottom:none; }

        /* Reportes */
        .rep-controls { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
        .p-btn  { padding:7px 14px; border-radius:20px; border:1px solid var(--border); background:var(--surface); color:var(--text-secondary); cursor:pointer; font-size:13px; font-family:inherit; transition:.15s; }
        .p-btn.active, .p-btn:hover { background:var(--hosp); color:#fff; border-color:var(--hosp); }
        .rep-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:24px; }
        .kpi-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px; }
        .kpi-card .kpi-label { font-size:11px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin:0 0 4px; }
        .kpi-card .kpi-val   { font-size:20px; font-weight:800; color:var(--text-primary); margin:0; }
        .kpi-card .kpi-sub   { font-size:12px; color:var(--text-secondary); margin:2px 0 0; }
        .charts-grid { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px; }
        .chart-card  { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:18px; }
        .chart-card h4 { margin:0 0 14px; font-size:14px; font-weight:700; color:var(--text-primary); }
        .chart-wrap  { position:relative; }

        /* Barra de fecha */
        .date-nav { display:flex; align-items:center; gap:10px; }
        .date-nav button { width:32px; height:32px; border-radius:8px; border:1px solid var(--border); background:var(--surface); cursor:pointer; color:var(--text-secondary); font-size:14px; }
        .date-nav button:hover { background:var(--hosp-light); color:var(--hosp); }
        .date-nav input { border:1px solid var(--border); border-radius:8px; padding:6px 10px; font-size:13px; background:var(--surface); color:var(--text-primary); }

        .badge-s { display:inline-block; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-checkin  { background:rgba(15,209,134,.12); color:#059669; }
        .badge-checkout { background:rgba(99,102,241,.12); color:#4f46e5; }
        .badge-reservada { background:rgba(251,191,36,.12); color:#b45309; }

        @media (max-width:700px) { .charts-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
<?php include '../includes/header.php'; ?>

<!-- Toolbar -->
<div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;background:var(--surface);">
    <div>
        <h1 style="margin:0;font-size:20px;font-weight:700;"><i class="fas fa-cash-register" style="color:var(--hosp);margin-right:8px;"></i>Caja & Reportes</h1>
        <p style="margin:0;font-size:12px;color:var(--text-secondary);">Hospedaje — movimientos y análisis financiero</p>
    </div>
</div>

<!-- Tabs -->
<div class="htabs">
    <button class="htab active" data-tab="caja" onclick="setTab('caja',this)">
        <i class="fas fa-calendar-day" style="margin-right:6px;"></i>Caja del Día
    </button>
    <button class="htab" data-tab="rep" onclick="setTab('rep',this)">
        <i class="fas fa-chart-bar" style="margin-right:6px;"></i>Reportes
    </button>
</div>

<!-- ── CAJA DEL DÍA ─────────────────────────────────────────────────────────── -->
<div class="htab-pane active" id="pane-caja">

    <!-- Navegación de fecha -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
        <div class="date-nav">
            <button onclick="cambiarFecha(-1)"><i class="fas fa-chevron-left"></i></button>
            <input type="date" id="fechaCaja" onchange="cargarCaja()">
            <button onclick="cambiarFecha(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
        <button class="p-btn" onclick="document.getElementById('fechaCaja').value=hoyStr();cargarCaja();">Hoy</button>
    </div>

    <!-- KPIs -->
    <div class="stats-row" id="cajaSt"></div>

    <!-- Mapa de habitaciones -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h3 class="card-title">Estado de habitaciones</h3></div>
        <div class="card-body">
            <div class="hab-mini-grid" id="habGrid"></div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;font-size:12px;">
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:rgba(15,209,134,.3);display:inline-block;"></span> Libre</span>
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:rgba(239,68,68,.3);display:inline-block;"></span> Ocupada</span>
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:rgba(251,191,36,.3);display:inline-block;"></span> Limpieza</span>
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:rgba(156,163,175,.3);display:inline-block;"></span> Mantenimiento</span>
            </div>
        </div>
    </div>

    <!-- Movimientos del día -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Movimientos del día</h3>
            <div style="display:flex;gap:6px;">
                <button class="p-btn active" id="mvTabCI" onclick="setMvTab('ci')">Check-ins <span id="mvCICount" style="font-weight:800;"></span></button>
                <button class="p-btn" id="mvTabCO" onclick="setMvTab('co')">Check-outs <span id="mvCOCount" style="font-weight:800;"></span></button>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            <div style="overflow-x:auto;">
                <table class="mov-table" id="movTabla"></table>
            </div>
        </div>
    </div>

</div>

<!-- ── REPORTES ───────────────────────────────────────────────────────────────── -->
<div class="htab-pane" id="pane-rep">

    <div class="rep-controls">
        <button class="p-btn" onclick="setPeriodo(7,this)">7 días</button>
        <button class="p-btn active" onclick="setPeriodo(30,this)">30 días</button>
        <button class="p-btn" onclick="setPeriodo(90,this)">90 días</button>
        <input type="date" id="rDesde" style="border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;background:var(--surface);color:var(--text-primary);">
        <span style="color:var(--text-secondary);font-size:13px;">→</span>
        <input type="date" id="rHasta" style="border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;background:var(--surface);color:var(--text-primary);">
        <button class="p-btn" onclick="cargarReportes()"><i class="fas fa-search"></i> Buscar</button>
    </div>

    <!-- KPIs del período -->
    <div class="rep-kpis" id="repKpis"></div>

    <!-- Gráficos row 1 -->
    <div class="charts-grid">
        <div class="chart-card">
            <h4><i class="fas fa-chart-bar" style="color:var(--hosp);margin-right:6px;"></i>Ingresos por día</h4>
            <div class="chart-wrap" style="height:220px;"><canvas id="cDia"></canvas></div>
        </div>
        <div class="chart-card">
            <h4><i class="fas fa-chart-pie" style="color:var(--hosp);margin-right:6px;"></i>Por tipo de habitación</h4>
            <div class="chart-wrap" style="height:220px;"><canvas id="cTipo"></canvas></div>
        </div>
    </div>

    <!-- Gráficos row 2 -->
    <div class="charts-grid">
        <div class="chart-card">
            <h4><i class="fas fa-percent" style="color:var(--hosp);margin-right:6px;"></i>Ocupación diaria</h4>
            <div class="chart-wrap" style="height:200px;"><canvas id="cOcup"></canvas></div>
        </div>
        <div class="chart-card">
            <h4><i class="fas fa-calendar-week" style="color:var(--hosp);margin-right:6px;"></i>Reservas por día de semana</h4>
            <div class="chart-wrap" style="height:200px;"><canvas id="cSemana"></canvas></div>
        </div>
    </div>

</div>

</div><!-- /main-content -->
</div><!-- /dashboard-layout -->

<script>
const BASE     = '<?= $base ?>';
const API_CAJA = BASE + '/api/hospedaje/caja.php';
const API_HAB  = BASE + '/api/hospedaje/habitaciones.php';

// ── Estado ────────────────────────────────────────────────────────────────────
let mvMode  = 'ci'; // 'ci' o 'co'
let mvData  = { checkins:[], checkouts:[] };
let charts  = {};
let repCargado = false;

// ── Init ──────────────────────────────────────────────────────────────────────
function hoyStr() { return new Date().toISOString().slice(0,10); }

document.getElementById('fechaCaja').value = hoyStr();
// Período por defecto: últimos 30 días
const _d30 = new Date(); _d30.setDate(_d30.getDate()-29);
document.getElementById('rDesde').value = _d30.toISOString().slice(0,10);
document.getElementById('rHasta').value = hoyStr();

cargarCaja();

// ── Tabs ──────────────────────────────────────────────────────────────────────
function setTab(t, btn) {
    document.querySelectorAll('.htab').forEach(b => b.classList.toggle('active', b.dataset.tab === t));
    document.querySelectorAll('.htab-pane').forEach(p => p.classList.toggle('active', p.id === 'pane-'+t));
    if (t === 'rep' && !repCargado) { cargarReportes(); }
}

function cambiarFecha(d) {
    const f = document.getElementById('fechaCaja');
    const dt = new Date(f.value + 'T00:00:00'); dt.setDate(dt.getDate()+d);
    f.value = dt.toISOString().slice(0,10);
    cargarCaja();
}

// ── CAJA ──────────────────────────────────────────────────────────────────────
async function cargarCaja() {
    const fecha = document.getElementById('fechaCaja').value;
    try {
        const [resumen, movs, habs] = await Promise.all([
            fetch(`${API_CAJA}?tipo=resumen&fecha=${fecha}`, {credentials:'include'}).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=movimientos&fecha=${fecha}`, {credentials:'include'}).then(r=>r.json()),
            fetch(`${API_HAB}`, {credentials:'include'}).then(r=>r.json()),
        ]);

        if (resumen.success) renderCajaStats(resumen.data);
        if (movs.success)    { mvData = movs.data; renderMovimientos(); }
        if (habs.success)    renderHabGrid(habs.data.habitaciones || []);
    } catch(e) { console.error(e); }
}

function renderCajaStats(d) {
    const ing = '$' + Number(d.ingresos_hoy||0).toLocaleString('es-AR',{minimumFractionDigits:0});
    const ext = '$' + Number(d.extras_hoy||0).toLocaleString('es-AR',{minimumFractionDigits:0});
    document.getElementById('cajaSt').innerHTML = `
        <div class="stat-card"><div class="stat-icon" style="background:rgba(15,209,134,.12);color:#059669"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><p class="stat-label">Ingresos del día</p><h3 class="stat-value">${ing}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(99,102,241,.12);color:var(--hosp)"><i class="fas fa-bed"></i></div><div class="stat-info"><p class="stat-label">Ocupación</p><h3 class="stat-value">${d.ocupacion_pct}%</h3><p class="stat-label" style="margin:2px 0 0;">${d.hab_ocupadas} de ${d.hab_total} hab.</p></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(251,191,36,.12);color:#b45309"><i class="fas fa-sign-in-alt"></i></div><div class="stat-info"><p class="stat-label">Check-ins</p><h3 class="stat-value">${d.checkins_hoy}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(239,68,68,.12);color:#dc2626"><i class="fas fa-sign-out-alt"></i></div><div class="stat-info"><p class="stat-label">Check-outs</p><h3 class="stat-value">${d.checkouts_hoy}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(168,85,247,.12);color:#9333ea"><i class="fas fa-concierge-bell"></i></div><div class="stat-info"><p class="stat-label">Extras</p><h3 class="stat-value">${ext}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(20,184,166,.12);color:#0d9488"><i class="fas fa-calendar-check"></i></div><div class="stat-info"><p class="stat-label">Reservas futuras</p><h3 class="stat-value">${d.reservas_futuras}</h3></div></div>
    `;
}

function renderHabGrid(habs) {
    const grid = document.getElementById('habGrid');
    if (!habs.length) { grid.innerHTML = '<p style="color:var(--text-secondary);font-size:13px;">Sin habitaciones registradas.</p>'; return; }
    grid.innerHTML = habs.map(h => {
        const cls = h.estado === 'libre' ? 'hab-libre' : h.estado === 'ocupada' ? 'hab-ocupada' : h.estado === 'limpieza' ? 'hab-limpieza' : 'hab-mant';
        return `<div class="hab-mini ${cls}"><div class="num">${esc(h.numero)}</div><div class="tip">${esc(h.tipo||'')}</div></div>`;
    }).join('');
}

function setMvTab(mode) {
    mvMode = mode;
    document.getElementById('mvTabCI').classList.toggle('active', mode==='ci');
    document.getElementById('mvTabCO').classList.toggle('active', mode==='co');
    renderMovimientos();
}

function renderMovimientos() {
    const rows = mvMode === 'ci' ? mvData.checkins : mvData.checkouts;
    document.getElementById('mvCICount').textContent = mvData.checkins.length || '';
    document.getElementById('mvCOCount').textContent = mvData.checkouts.length || '';
    const t = document.getElementById('movTabla');
    if (!rows || !rows.length) {
        t.innerHTML = `<tr><td colspan="6" style="padding:30px;text-align:center;color:var(--text-secondary);">Sin ${mvMode==='ci'?'check-ins':'check-outs'} para esta fecha.</td></tr>`;
        return;
    }
    const hora_col = mvMode === 'ci' ? 'checkin_hora' : 'checkout_hora';
    t.innerHTML = `<thead><tr>
        <th>Hab.</th><th>Huésped</th><th>Hora</th><th>Estadía</th><th>Total</th><th>Estado</th>
    </tr></thead><tbody>` + rows.map(r => {
        const lbl = estadoLbl(r.estado);
        const total = '$' + Number(r.total||0).toLocaleString('es-AR',{minimumFractionDigits:0});
        return `<tr>
            <td><strong>${esc(r.hab_numero)}</strong></td>
            <td>${esc(r.huesped_nombre)}</td>
            <td>${(r[hora_col]||'').slice(0,5)}</td>
            <td>${r.noches} × ${tipoLbl(r.tipo_estadia)}</td>
            <td style="font-weight:700;">${total}</td>
            <td><span class="badge-s badge-${r.estado}">${lbl}</span></td>
        </tr>`;
    }).join('') + '</tbody>';
}

// ── REPORTES ──────────────────────────────────────────────────────────────────
function setPeriodo(dias, btn) {
    document.querySelectorAll('.rep-controls .p-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const hasta = new Date();
    const desde = new Date(); desde.setDate(desde.getDate()-(dias-1));
    document.getElementById('rDesde').value = desde.toISOString().slice(0,10);
    document.getElementById('rHasta').value = hasta.toISOString().slice(0,10);
    if (document.getElementById('pane-rep').classList.contains('active')) cargarReportes();
}

async function cargarReportes() {
    repCargado = true;
    const desde = document.getElementById('rDesde').value;
    const hasta = document.getElementById('rHasta').value;
    const qs    = `&desde=${desde}&hasta=${hasta}`;
    try {
        const [dia, tipo, ocup, sem] = await Promise.all([
            fetch(`${API_CAJA}?tipo=ingresos_dia${qs}`,      {credentials:'include'}).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=por_tipo${qs}`,           {credentials:'include'}).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=ocupacion_diaria${qs}`,   {credentials:'include'}).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=dias_semana${qs}`,        {credentials:'include'}).then(r=>r.json()),
        ]);

        if (dia.success)  renderKPIs(dia.data);
        if (dia.success)  renderDia(dia.data);
        if (tipo.success) renderTipo(tipo.data);
        if (ocup.success) renderOcup(ocup.data);
        if (sem.success)  renderSemana(sem.data);
    } catch(e) { console.error(e); }
}

function renderKPIs(rows) {
    const totalIng    = rows.reduce((s,r)=>s+parseFloat(r.ingresos||0),0);
    const totalRes    = rows.reduce((s,r)=>s+parseInt(r.reservas||0),0);
    const ticketProm  = totalRes > 0 ? totalIng/totalRes : 0;
    const dias        = rows.length;
    const ingProm     = dias > 0 ? totalIng/dias : 0;
    document.getElementById('repKpis').innerHTML = `
        <div class="kpi-card"><p class="kpi-label">Ingresos totales</p><h3 class="kpi-val">$${Math.round(totalIng).toLocaleString('es-AR')}</h3></div>
        <div class="kpi-card"><p class="kpi-label">Check-outs</p><h3 class="kpi-val">${totalRes}</h3><p class="kpi-sub">en el período</p></div>
        <div class="kpi-card"><p class="kpi-label">Ingreso promedio/día</p><h3 class="kpi-val">$${Math.round(ingProm).toLocaleString('es-AR')}</h3></div>
        <div class="kpi-card"><p class="kpi-label">Ticket promedio</p><h3 class="kpi-val">$${Math.round(ticketProm).toLocaleString('es-AR')}</h3></div>
    `;
}

function mkChart(id, type, data, options={}) {
    if (charts[id]) charts[id].destroy();
    const ctx = document.getElementById(id).getContext('2d');
    charts[id] = new Chart(ctx, { type, data, options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, ...options }});
}

function renderDia(rows) {
    const labels = rows.map(r => { const [,m,d]=r.fecha.split('-'); return `${d}/${m}`; });
    mkChart('cDia', 'bar', {
        labels,
        datasets:[{
            label:'Ingresos',
            data: rows.map(r=>parseFloat(r.ingresos||0)),
            backgroundColor:'rgba(99,102,241,.7)',
            borderRadius:5,
        }]
    }, { plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: c=>'$'+Math.round(c.raw).toLocaleString('es-AR') }}}, scales:{ y:{ ticks:{ callback:v=>'$'+v.toLocaleString('es-AR') }}}} );
}

function renderTipo(rows) {
    const COLORS = ['#6366f1','#0fd186','#f59e0b','#ef4444','#8b5cf6','#06b6d4'];
    if (!rows.length) { document.getElementById('cTipo').closest('.chart-card').innerHTML += '<p style="text-align:center;color:var(--text-secondary);padding:40px 0">Sin datos</p>'; return; }
    const tipoLabel = {simple:'Simple',doble:'Doble',triple:'Triple',suite:'Suite',cabaña:'Cabaña',otro:'Otro'};
    mkChart('cTipo','doughnut',{
        labels: rows.map(r=>tipoLabel[r.tipo]||r.tipo),
        datasets:[{ data:rows.map(r=>parseFloat(r.ingresos||0)), backgroundColor:COLORS.slice(0,rows.length), borderWidth:2 }]
    }, { plugins:{ legend:{ display:true, position:'bottom', labels:{ font:{size:11}, padding:10 }}}, cutout:'65%' });
}

function renderOcup(d) {
    const rows = d.rows || [];
    mkChart('cOcup','line',{
        labels: rows.map(r=>{ const[,m,d2]=r.fecha.split('-'); return `${d2}/${m}`; }),
        datasets:[{
            label:'Ocupación %',
            data: rows.map(r=>parseInt(r.pct||0)),
            borderColor:'#6366f1',
            backgroundColor:'rgba(99,102,241,.1)',
            fill:true,
            tension:.3,
            pointRadius:2,
        }]
    }, { plugins:{ legend:{display:false}}, scales:{ y:{ min:0, max:100, ticks:{ callback:v=>v+'%' }}}});
}

function renderSemana(rows) {
    const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    const data = Array(7).fill(0);
    rows.forEach(r => { data[parseInt(r.dia)] = parseInt(r.reservas||0); });
    mkChart('cSemana','bar',{
        labels: dias,
        datasets:[{ label:'Reservas', data, backgroundColor:'rgba(99,102,241,.7)', borderRadius:5 }]
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function esc(s)         { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function tipoLbl(t)     { return {noche:'Noche',hora:'Hora',semana:'Semana'}[t]||t; }
function estadoLbl(e)   { return {reservada:'Reservada',checkin:'En hotel',checkout:'Checkout',cancelada:'Cancelada'}[e]||e; }
</script>
</body>
</html>
