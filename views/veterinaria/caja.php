<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja & Reportes — Veterinaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__.'/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__.'/../../public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --vet:#84cc16; --vet-dark:#65a30d; --vet-light:rgba(132,204,22,.1); }
        .htabs { display:flex;gap:4px;padding:0 24px;border-bottom:1px solid var(--border);background:var(--surface); }
        .htab  { padding:14px 20px;border:none;background:none;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-secondary);border-bottom:2px solid transparent;margin-bottom:-1px;transition:.15s;font-family:inherit; }
        .htab.active { color:var(--vet-dark);border-bottom-color:var(--vet); }
        .htab-pane { display:none;padding:24px; }
        .htab-pane.active { display:block; }

        .stats-row { display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:12px;margin-bottom:24px; }
        .stat-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px; }
        .stat-icon { width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
        .stat-info .stat-label { font-size:11px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin:0 0 2px; }
        .stat-info .stat-value { font-size:22px;font-weight:800;color:var(--text-primary);margin:0; }

        .date-nav { display:flex;align-items:center;gap:10px; }
        .date-nav button { width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;color:var(--text-secondary); }
        .date-nav button:hover { background:var(--vet-light);color:var(--vet-dark); }
        .date-nav input { border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;background:var(--surface);color:var(--text-primary); }

        .p-btn { padding:7px 14px;border-radius:20px;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:13px;font-family:inherit;transition:.15s; }
        .p-btn.active,.p-btn:hover { background:var(--vet);color:#fff;border-color:var(--vet); }

        .agenda-tabla { width:100%;border-collapse:collapse;font-size:13px; }
        .agenda-tabla th { padding:10px 14px;background:var(--background);color:var(--text-secondary);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);text-align:left; }
        .agenda-tabla td { padding:11px 14px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        .agenda-tabla tr:last-child td { border-bottom:none; }

        .tipo-chip { display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700; }
        .tipo-consulta    { background:rgba(99,102,241,.12);color:#4f46e5; }
        .tipo-vacuna      { background:rgba(15,209,134,.12);color:#059669; }
        .tipo-cirugia     { background:rgba(239,68,68,.12);color:#dc2626; }
        .tipo-bano,.tipo-grooming { background:rgba(251,191,36,.12);color:#b45309; }
        .tipo-control     { background:rgba(20,184,166,.12);color:#0d9488; }
        .tipo-urgencia    { background:rgba(239,68,68,.2);color:#dc2626; }

        .badge-s { display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700; }
        .badge-pendiente { background:rgba(251,191,36,.12);color:#b45309; }
        .badge-atendido  { background:rgba(15,209,134,.12);color:#059669; }
        .badge-cancelado { background:rgba(156,163,175,.12);color:#6b7280; }

        .rep-kpis { display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:24px; }
        .kpi-card { background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px; }
        .kpi-card .kpi-label { font-size:11px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin:0 0 4px; }
        .kpi-card .kpi-val { font-size:20px;font-weight:800;color:var(--text-primary);margin:0; }
        .kpi-card .kpi-sub { font-size:12px;color:var(--text-secondary);margin:2px 0 0; }

        .charts-grid { display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px; }
        .charts-grid2 { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px; }
        .chart-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px; }
        .chart-card h4 { margin:0 0 14px;font-size:14px;font-weight:700;color:var(--text-primary); }

        .alert-banner { display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px; }
        .alert-warn { background:rgba(251,191,36,.12);color:#b45309;border:1px solid rgba(251,191,36,.3); }
        .alert-ok   { background:rgba(15,209,134,.1);color:#059669;border:1px solid rgba(15,209,134,.3); }

        .metodo-chip { display:inline-block;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700;background:var(--background); }

        @media (max-width:700px) { .charts-grid,.charts-grid2 { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="app-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>

<div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;background:var(--surface);">
    <div>
        <h1 style="margin:0;font-size:20px;font-weight:700;"><i class="fas fa-cash-register" style="color:var(--vet);margin-right:8px;"></i>Caja & Reportes</h1>
        <p style="margin:0;font-size:12px;color:var(--text-secondary);">Veterinaria — movimientos y análisis</p>
    </div>
</div>

<div class="htabs">
    <button class="htab active" data-tab="caja" onclick="setTab('caja',this)"><i class="fas fa-calendar-day" style="margin-right:6px;"></i>Caja del Día</button>
    <button class="htab" data-tab="rep"  onclick="setTab('rep',this)"><i class="fas fa-chart-bar" style="margin-right:6px;"></i>Reportes</button>
</div>

<!-- ── CAJA DEL DÍA ─────────────────────────────────────────────────────────── -->
<div class="htab-pane active" id="pane-caja">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
        <div class="date-nav">
            <button onclick="cambiarFecha(-1)"><i class="fas fa-chevron-left"></i></button>
            <input type="date" id="fechaCaja" onchange="cargarCaja()">
            <button onclick="cambiarFecha(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
        <button class="p-btn" onclick="document.getElementById('fechaCaja').value=hoyStr();cargarCaja()">Hoy</button>
    </div>

    <div class="stats-row" id="cajaSt"></div>
    <div id="cajaAlertas"></div>

    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Consultas del día</h3>
            <div style="display:flex;gap:6px;" id="filtroEstados">
                <button class="p-btn active" onclick="setFiltroEstado('',this)">Todas</button>
                <button class="p-btn" onclick="setFiltroEstado('pendiente',this)">Pendientes</button>
                <button class="p-btn" onclick="setFiltroEstado('atendido',this)">Atendidas</button>
            </div>
        </div>
        <div class="card-body" style="padding:0;overflow-x:auto;">
            <table class="agenda-tabla" id="agendaTabla"></table>
        </div>
    </div>
</div>

<!-- ── REPORTES ───────────────────────────────────────────────────────────────── -->
<div class="htab-pane" id="pane-rep">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:20px;">
        <button class="p-btn" onclick="setPeriodo(7,this)">7 días</button>
        <button class="p-btn active" onclick="setPeriodo(30,this)">30 días</button>
        <button class="p-btn" onclick="setPeriodo(90,this)">90 días</button>
        <input type="date" id="rDesde" style="border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;background:var(--surface);color:var(--text-primary);">
        <span style="color:var(--text-secondary);">→</span>
        <input type="date" id="rHasta" style="border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;background:var(--surface);color:var(--text-primary);">
        <button class="p-btn" onclick="cargarReportes()"><i class="fas fa-search"></i> Buscar</button>
    </div>

    <div class="rep-kpis" id="repKpis"></div>

    <div class="charts-grid">
        <div class="chart-card">
            <h4><i class="fas fa-chart-bar" style="color:var(--vet);margin-right:6px;"></i>Ingresos por día</h4>
            <div style="height:220px;"><canvas id="cDia"></canvas></div>
        </div>
        <div class="chart-card">
            <h4><i class="fas fa-chart-pie" style="color:var(--vet);margin-right:6px;"></i>Por tipo de consulta</h4>
            <div style="height:220px;"><canvas id="cTipo"></canvas></div>
        </div>
    </div>
    <div class="charts-grid2">
        <div class="chart-card">
            <h4><i class="fas fa-paw" style="color:var(--vet);margin-right:6px;"></i>Consultas por especie</h4>
            <div style="height:200px;"><canvas id="cEspecie"></canvas></div>
        </div>
        <div class="chart-card">
            <h4><i class="fas fa-calendar-week" style="color:var(--vet);margin-right:6px;"></i>Consultas por día de semana</h4>
            <div style="height:200px;"><canvas id="cSemana"></canvas></div>
        </div>
    </div>
</div>

</div><!-- /main-content -->
</div><!-- /app-layout -->
<script>
const API_CAJA = '../../api/veterinaria/caja.php';
let agendaData = [];
let filtroEstado = '';
let charts = {};
let repCargado = false;

function hoyStr() { return new Date().toISOString().slice(0,10); }

document.getElementById('fechaCaja').value = hoyStr();
const _d30 = new Date(); _d30.setDate(_d30.getDate()-29);
document.getElementById('rDesde').value = _d30.toISOString().slice(0,10);
document.getElementById('rHasta').value = hoyStr();

cargarCaja();

function setTab(t, btn) {
    document.querySelectorAll('.htab').forEach(b => b.classList.toggle('active', b.dataset.tab===t));
    document.querySelectorAll('.htab-pane').forEach(p => p.classList.toggle('active', p.id==='pane-'+t));
    if (t==='rep' && !repCargado) cargarReportes();
}

function cambiarFecha(d) {
    const f = document.getElementById('fechaCaja');
    const dt = new Date(f.value+'T00:00:00'); dt.setDate(dt.getDate()+d);
    f.value = dt.toISOString().slice(0,10);
    cargarCaja();
}

async function cargarCaja() {
    const fecha = document.getElementById('fechaCaja').value;
    try {
        const [res, agenda] = await Promise.all([
            fetch(`${API_CAJA}?tipo=resumen&fecha=${fecha}`).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=agenda&fecha=${fecha}`).then(r=>r.json()),
        ]);
        if (res.success)   renderStats(res.data);
        if (agenda.success){ agendaData = agenda.data; renderAgenda(); }
    } catch(e) { console.error(e); }
}

function renderStats(d) {
    const fmt = n => '$'+Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0});
    document.getElementById('cajaSt').innerHTML = `
        <div class="stat-card"><div class="stat-icon" style="background:rgba(132,204,22,.12);color:var(--vet-dark)"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><p class="stat-label">Facturado hoy</p><h3 class="stat-value">${fmt(d.facturado)}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(15,209,134,.12);color:#059669"><i class="fas fa-check-circle"></i></div><div class="stat-info"><p class="stat-label">Atendidas</p><h3 class="stat-value">${d.atendidos}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(251,191,36,.12);color:#b45309"><i class="fas fa-clock"></i></div><div class="stat-info"><p class="stat-label">Pendientes</p><h3 class="stat-value">${d.pendientes}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(99,102,241,.12);color:#4f46e5"><i class="fas fa-money-bill"></i></div><div class="stat-info"><p class="stat-label">Efectivo</p><h3 class="stat-value">${fmt(d.efectivo)}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(20,184,166,.12);color:#0d9488"><i class="fas fa-credit-card"></i></div><div class="stat-info"><p class="stat-label">Digital</p><h3 class="stat-value">${fmt(d.digital)}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(168,85,247,.12);color:#9333ea"><i class="fas fa-paw"></i></div><div class="stat-info"><p class="stat-label">Pacientes nuevos</p><h3 class="stat-value">${d.pacientes_nuevos}</h3></div></div>
    `;
    // Alertas
    let alertas = '';
    if (d.alertas_vacunas > 0) alertas += `<div class="alert-banner alert-warn"><i class="fas fa-syringe"></i> ${d.alertas_vacunas} vacuna${d.alertas_vacunas>1?'s':''} vence${d.alertas_vacunas===1?'':'n'} en los próximos 7 días. <a href="pacientes.php" style="margin-left:8px;font-weight:700;">Ver pacientes →</a></div>`;
    if (d.alertas_stock > 0)   alertas += `<div class="alert-banner alert-warn"><i class="fas fa-box-open"></i> ${d.alertas_stock} producto${d.alertas_stock>1?'s':''} bajo stock mínimo. <a href="stock.php" style="margin-left:8px;font-weight:700;">Ver stock →</a></div>`;
    document.getElementById('cajaAlertas').innerHTML = alertas;
}

function setFiltroEstado(estado, btn) {
    filtroEstado = estado;
    document.querySelectorAll('#filtroEstados .p-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    renderAgenda();
}

function renderAgenda() {
    const rows = filtroEstado ? agendaData.filter(c=>c.estado===filtroEstado) : agendaData;
    const t = document.getElementById('agendaTabla');
    if (!rows.length) {
        t.innerHTML = `<tr><td colspan="7" style="padding:30px;text-align:center;color:var(--text-secondary);">Sin consultas para esta fecha${filtroEstado?' ('+filtroEstado+')':''}</td></tr>`;
        return;
    }
    const ESPECIE = {perro:'🐶',gato:'🐱',ave:'🐦',conejo:'🐰',reptil:'🦎'};
    t.innerHTML = `<thead><tr>
        <th>Hora</th><th>Paciente</th><th>Dueño</th><th>Tipo</th><th>Motivo</th><th>Estado</th><th>Monto</th>
    </tr></thead><tbody>` + rows.map(c => {
        const emoji = ESPECIE[c.especie] || '🐾';
        const monto = c.monto > 0 ? '$'+Number(c.monto).toLocaleString('es-AR',{minimumFractionDigits:0}) : '—';
        return `<tr>
            <td style="font-weight:700;">${(c.hora||'').slice(0,5)}</td>
            <td>${emoji} <strong>${esc(c.pac_nombre)}</strong><br><small style="color:var(--text-secondary);">${esc(c.especie||'')}</small></td>
            <td>${esc(c.duenio_nombre)}</td>
            <td><span class="tipo-chip tipo-${c.tipo||'consulta'}">${tipoLbl(c.tipo)}</span></td>
            <td style="color:var(--text-secondary);max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.motivo||'—')}</td>
            <td><span class="badge-s badge-${c.estado}">${estadoLbl(c.estado)}</span></td>
            <td style="font-weight:700;">${monto}</td>
        </tr>`;
    }).join('') + '</tbody>';
}

// ── REPORTES ──────────────────────────────────────────────────────────────────
function setPeriodo(dias, btn) {
    document.querySelectorAll('[onclick^="setPeriodo"]').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const h = new Date(), d = new Date(); d.setDate(d.getDate()-(dias-1));
    document.getElementById('rDesde').value = d.toISOString().slice(0,10);
    document.getElementById('rHasta').value = h.toISOString().slice(0,10);
    if (document.getElementById('pane-rep').classList.contains('active')) cargarReportes();
}

async function cargarReportes() {
    repCargado = true;
    const desde = document.getElementById('rDesde').value;
    const hasta = document.getElementById('rHasta').value;
    const qs = `&desde=${desde}&hasta=${hasta}`;
    try {
        const [dia, tipo, especie, sem] = await Promise.all([
            fetch(`${API_CAJA}?tipo=ingresos_dia${qs}`).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=por_tipo${qs}`).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=por_especie${qs}`).then(r=>r.json()),
            fetch(`${API_CAJA}?tipo=dias_semana${qs}`).then(r=>r.json()),
        ]);
        if (dia.success)    { renderKPIs(dia.data); renderDia(dia.data); }
        if (tipo.success)   renderTipo(tipo.data);
        if (especie.success) renderEspecie(especie.data);
        if (sem.success)    renderSemana(sem.data);
    } catch(e) { console.error(e); }
}

function renderKPIs(rows) {
    const ingTotal  = rows.reduce((s,r)=>s+parseFloat(r.ingresos||0),0);
    const consTotal = rows.reduce((s,r)=>s+parseInt(r.consultas||0),0);
    const atTotal   = rows.reduce((s,r)=>s+parseInt(r.atendidas||0),0);
    const ticket    = atTotal > 0 ? ingTotal/atTotal : 0;
    document.getElementById('repKpis').innerHTML = `
        <div class="kpi-card"><p class="kpi-label">Ingresos totales</p><h3 class="kpi-val">$${Math.round(ingTotal).toLocaleString('es-AR')}</h3></div>
        <div class="kpi-card"><p class="kpi-label">Consultas</p><h3 class="kpi-val">${consTotal}</h3><p class="kpi-sub">${atTotal} atendidas</p></div>
        <div class="kpi-card"><p class="kpi-label">Ticket promedio</p><h3 class="kpi-val">$${Math.round(ticket).toLocaleString('es-AR')}</h3></div>
        <div class="kpi-card"><p class="kpi-label">Promedio/día</p><h3 class="kpi-val">$${rows.length>0?Math.round(ingTotal/rows.length).toLocaleString('es-AR'):0}</h3></div>
    `;
}

function mkChart(id, type, data, opts={}) {
    if (charts[id]) charts[id].destroy();
    charts[id] = new Chart(document.getElementById(id).getContext('2d'), {
        type, data,
        options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, ...opts }
    });
}

function renderDia(rows) {
    mkChart('cDia','bar',{
        labels: rows.map(r=>{ const[,m,d]=r.fecha.split('-'); return `${d}/${m}`; }),
        datasets:[{ data:rows.map(r=>parseFloat(r.ingresos||0)), backgroundColor:'rgba(132,204,22,.75)', borderRadius:5 }]
    },{ plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'$'+Math.round(c.raw).toLocaleString('es-AR')}}},
        scales:{y:{ticks:{callback:v=>'$'+v.toLocaleString('es-AR')}}}});
}

function renderTipo(rows) {
    const COLORS=['#84cc16','#6366f1','#ef4444','#f59e0b','#0d9488','#8b5cf6','#06b6d4'];
    const TIPO_LBL = {consulta:'Consulta',vacuna:'Vacuna',cirugia:'Cirugía',bano:'Baño',grooming:'Grooming',control:'Control',urgencia:'Urgencia'};
    if(!rows.length) return;
    mkChart('cTipo','doughnut',{
        labels: rows.map(r=>TIPO_LBL[r.tipo]||r.tipo),
        datasets:[{data:rows.map(r=>parseInt(r.total||0)),backgroundColor:COLORS.slice(0,rows.length),borderWidth:2}]
    },{ plugins:{legend:{display:true,position:'bottom',labels:{font:{size:11},padding:10}}},cutout:'60%'});
}

function renderEspecie(rows) {
    const ESPECIE_COLOR = {perro:'#84cc16',gato:'#6366f1',ave:'#f59e0b',conejo:'#0d9488',reptil:'#8b5cf6'};
    const ESPECIE_EMOJI = {perro:'🐶',gato:'🐱',ave:'🐦',conejo:'🐰',reptil:'🦎'};
    mkChart('cEspecie','bar',{
        labels: rows.map(r=>(ESPECIE_EMOJI[r.especie]||'🐾')+' '+r.especie),
        datasets:[{data:rows.map(r=>parseInt(r.consultas||0)),backgroundColor:rows.map(r=>ESPECIE_COLOR[r.especie]||'#94a3b8'),borderRadius:6}]
    },{ indexAxis:'y', plugins:{legend:{display:false}} });
}

function renderSemana(rows) {
    const data = Array(7).fill(0);
    rows.forEach(r=>{ data[parseInt(r.dia)] = parseInt(r.consultas||0); });
    mkChart('cSemana','bar',{
        labels:['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
        datasets:[{data,backgroundColor:'rgba(132,204,22,.75)',borderRadius:5}]
    });
}

function esc(s)       { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function tipoLbl(t)   { return {consulta:'Consulta',vacuna:'Vacuna',cirugia:'Cirugía',bano:'Baño',grooming:'Grooming',control:'Control',urgencia:'Urgencia'}[t]||t; }
function estadoLbl(e) { return {pendiente:'Pendiente',atendido:'Atendido',cancelado:'Cancelado'}[e]||e; }
</script>
</body>
</html>
