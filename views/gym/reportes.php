<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes — Gimnasio</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
<link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ── KPI Grid ── */
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:24px; }
.kpi-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:18px 20px; position:relative; overflow:hidden; }
.kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--kpi-clr,#f97316); }
.kpi-lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); margin-bottom:6px; }
.kpi-val { font-size:1.85rem; font-weight:800; color:var(--text-primary); line-height:1; }
.kpi-sub { font-size:12px; color:var(--text-secondary); margin-top:5px; }
.var-up   { color:#22c55e; } .var-down { color:#ef4444; } .var-neutral { color:var(--text-secondary); }

/* ── Charts ── */
.charts-grid { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:24px; }
@media(max-width:768px) { .charts-grid { grid-template-columns:1fr; } }
.chart-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; }
.chart-title { font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.chart-title i { color:#f97316; }
canvas { max-height:240px; }

/* ── Socios tables ── */
.two-col-cards { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px; }
@media(max-width:768px) { .two-col-cards { grid-template-columns:1fr; } }
.sec-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.sec-card-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.sec-card-title { font-size:13px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px; }
.sec-card-body { max-height:340px; overflow-y:auto; }
.socio-row { display:flex; align-items:center; gap:12px; padding:12px 20px; border-bottom:1px solid var(--border); }
.socio-row:last-child { border-bottom:none; }
.socio-row:hover { background:var(--background); }
.socio-avatar { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:#fff; flex-shrink:0; }
.socio-info { flex:1; min-width:0; }
.socio-nombre { font-size:13px; font-weight:600; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.socio-detalle { font-size:11px; color:var(--text-secondary); }
.dias-badge { font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; white-space:nowrap; }
.dias-ok   { background:rgba(34,197,94,.12); color:#16a34a; }
.dias-warn { background:rgba(249,115,22,.12); color:#f97316; }
.dias-venc { background:rgba(239,68,68,.12); color:#dc2626; }
.btn-cobrar { background:#f97316; color:#fff; border:none; border-radius:8px; padding:5px 12px; font-size:11px; font-weight:700; cursor:pointer; white-space:nowrap; }
.btn-cobrar:hover { background:#ea6c0a; }

/* ── Asistencias chart full ── */
.asist-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; margin-bottom:24px; }

/* ── Empty / loading ── */
.rep-empty { text-align:center; padding:40px 20px; color:var(--text-secondary); }
.rep-empty i { font-size:2rem; display:block; margin-bottom:10px; opacity:.35; }
.spin { animation:spin .8s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ── Modal cobro rápido ── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; padding:16px; }
.modal-overlay.open { display:flex; }
.modal-box { background:var(--surface); border-radius:16px; width:100%; max-width:420px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.25); }
.modal-header { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.modal-header h3 { margin:0; font-size:16px; font-weight:700; color:var(--text-primary); }
.modal-close { background:none; border:none; font-size:18px; cursor:pointer; color:var(--text-secondary); }
.modal-body { padding:20px 22px; }
.modal-footer { padding:14px 22px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); }
.form-g { margin-bottom:14px; }
.form-g label { display:block; font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:5px; }
.form-g input, .form-g select { width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); color:var(--text-primary); box-sizing:border-box; }
.form-g input:focus, .form-g select:focus { outline:none; border-color:#f97316; }
.form-2g { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
</style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Page header -->
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:12px;background:rgba(249,115,22,.12);display:flex;align-items:center;justify-content:center;color:#f97316;font-size:18px;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">Reportes del Gimnasio</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Ingresos, socios y asistencia</p>
                    </div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-grid" id="kpiGrid">
                <div class="kpi-card" style="grid-column:1/-1"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i><p>Cargando...</p></div></div>
            </div>

            <!-- Gráfico ingresos + plan donut -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-chart-bar"></i> Ingresos mensuales (últimos 12 meses)</div>
                    <canvas id="chartIngresos"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-layer-group"></i> Socios por plan</div>
                    <canvas id="chartPlanes"></canvas>
                    <div id="planesLegend" style="margin-top:14px;display:flex;flex-direction:column;gap:6px;"></div>
                </div>
            </div>

            <!-- Asistencias últimos 30 días -->
            <div class="asist-card">
                <div class="chart-title" style="margin-bottom:12px;"><i class="fas fa-calendar-check"></i> Asistencias diarias — últimos 30 días</div>
                <canvas id="chartAsistencias" style="max-height:180px;"></canvas>
            </div>

            <!-- Vencimientos próximos + Vencidos sin renovar -->
            <div class="two-col-cards">
                <div class="sec-card">
                    <div class="sec-card-header">
                        <div class="sec-card-title"><i class="fas fa-clock" style="color:#f59e0b;"></i> Vencen en los próximos 30 días</div>
                        <span id="cntProximos" style="font-size:12px;font-weight:700;background:rgba(249,115,22,.1);color:#f97316;padding:3px 10px;border-radius:20px;"></span>
                    </div>
                    <div class="sec-card-body" id="listaProximos">
                        <div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div>
                    </div>
                </div>
                <div class="sec-card">
                    <div class="sec-card-header">
                        <div class="sec-card-title"><i class="fas fa-exclamation-circle" style="color:#ef4444;"></i> Vencidos sin renovar</div>
                        <span id="cntVencidos" style="font-size:12px;font-weight:700;background:rgba(239,68,68,.1);color:#dc2626;padding:3px 10px;border-radius:20px;"></span>
                    </div>
                    <div class="sec-card-body" id="listaVencidos">
                        <div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal cobro rápido -->
<div class="modal-overlay" id="modalCobro">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-dollar-sign" style="color:#f97316;margin-right:6px;"></i> Cobrar cuota</h3>
            <button class="modal-close" onclick="cerrarCobro()">✕</button>
        </div>
        <div class="modal-body">
            <p id="cobroNombre" style="font-size:15px;font-weight:700;margin:0 0 14px;color:var(--text-primary);"></p>
            <input type="hidden" id="cobroSocioId">
            <input type="hidden" id="cobroPlanId">
            <div class="form-2g">
                <div class="form-g">
                    <label>Monto *</label>
                    <input type="number" id="cobroMonto" min="0" step="100">
                </div>
                <div class="form-g">
                    <label>Método</label>
                    <select id="cobroMetodo">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="debito">Débito</option>
                    </select>
                </div>
            </div>
            <div class="form-g">
                <label>Fecha</label>
                <input type="date" id="cobroFecha">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarCobro()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarCobro()"><i class="fas fa-save"></i> Registrar pago</button>
        </div>
    </div>
</div>

<script>
const API = '../../api/gym/reportes.php';
const API_PAGOS = '../../api/gym/pagos.php';
let chartIngresos = null, chartPlanes = null, chartAsist = null;

async function init() {
    const [rRes, rIng, rAsist, rProx, rVenc] = await Promise.all([
        fetch(`${API}?tipo=resumen`).then(r=>r.json()),
        fetch(`${API}?tipo=ingresos_mes`).then(r=>r.json()),
        fetch(`${API}?tipo=asistencias`).then(r=>r.json()),
        fetch(`${API}?tipo=proximos_vencimientos&dias=30`).then(r=>r.json()),
        fetch(`${API}?tipo=vencidos`).then(r=>r.json()),
    ]);

    if (rRes.success)  renderKPIs(rRes.data);
    if (rIng.success)  renderIngresos(rIng.data);
    if (rAsist.success) renderAsistencias(rAsist.data);
    if (rProx.success) renderProximos(rProx.data);
    if (rVenc.success) renderVencidos(rVenc.data);
    if (rRes.success)  renderPlanes(rRes.data.por_plan);
}

// ── KPIs ─────────────────────────────────────────────────
function renderKPIs(d) {
    const s = d.socios || {};
    const varPct = d.var_ingresos;
    let varHtml = '<span class="var-neutral">— sin datos prev.</span>';
    if (varPct !== null && varPct !== undefined) {
        const cls = varPct >= 0 ? 'var-up' : 'var-down';
        const ico = varPct >= 0 ? '↑' : '↓';
        varHtml = `<span class="${cls}">${ico} ${Math.abs(varPct)}% vs mes anterior</span>`;
    }
    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi-card" style="--kpi-clr:#22c55e">
            <div class="kpi-lbl">Ingresos del mes</div>
            <div class="kpi-val">${$m(d.mes_actual)}</div>
            <div class="kpi-sub">${varHtml}</div>
        </div>
        <div class="kpi-card" style="--kpi-clr:#3b82f6">
            <div class="kpi-lbl">Socios activos</div>
            <div class="kpi-val">${s.activos || 0}</div>
            <div class="kpi-sub"><span class="var-neutral">de ${s.total||0} totales</span></div>
        </div>
        <div class="kpi-card" style="--kpi-clr:#ef4444">
            <div class="kpi-lbl">Cuotas vencidas</div>
            <div class="kpi-val">${s.vencidos || 0}</div>
            <div class="kpi-sub"><span class="var-down">${s.vencidos > 0 ? 'Requieren atención' : 'Todo al día ✓'}</span></div>
        </div>
        <div class="kpi-card" style="--kpi-clr:#f59e0b">
            <div class="kpi-lbl">Vencen en 7 días</div>
            <div class="kpi-val">${s.por_vencer_7d || 0}</div>
            <div class="kpi-sub"><span class="var-neutral">${s.por_vencer_30d||0} en el mes</span></div>
        </div>
        <div class="kpi-card" style="--kpi-clr:#8b5cf6">
            <div class="kpi-lbl">Mes anterior</div>
            <div class="kpi-val">${$m(d.mes_anterior)}</div>
            <div class="kpi-sub"><span class="var-neutral">referencia</span></div>
        </div>
    `;
}

// ── Chart ingresos mensuales ──────────────────────────────
function renderIngresos(rows) {
    const labels = rows.map(r => {
        const [y,m] = r.mes.split('-');
        return new Date(y, m-1).toLocaleDateString('es-AR',{month:'short',year:'2-digit'});
    });
    const totales = rows.map(r => parseFloat(r.total));
    if (chartIngresos) chartIngresos.destroy();
    chartIngresos = new Chart(document.getElementById('chartIngresos').getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Ingresos ($)',
                data: totales,
                backgroundColor: totales.map((_,i) => i === totales.length-1 ? '#f97316' : 'rgba(249,115,22,.35)'),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display:false } },
            scales: {
                y: { ticks: { callback: v => '$'+v.toLocaleString('es-AR'), color:'#888', font:{size:11} }, grid:{color:'rgba(128,128,128,.12)'} },
                x: { ticks: { color:'#888', font:{size:11} }, grid:{display:false} }
            }
        }
    });
}

// ── Chart donut planes ────────────────────────────────────
function renderPlanes(planes) {
    if (!planes || !planes.length) {
        document.getElementById('chartPlanes').closest('.chart-card').innerHTML += `<div class="rep-empty" style="margin-top:-10px"><i class="fas fa-layer-group"></i><p>Sin socios activos con plan</p></div>`;
        return;
    }
    const labels  = planes.map(p => p.nombre);
    const datos   = planes.map(p => parseInt(p.cantidad));
    const colors  = planes.map(p => p.color || '#f97316');
    if (chartPlanes) chartPlanes.destroy();
    chartPlanes = new Chart(document.getElementById('chartPlanes').getContext('2d'), {
        type: 'doughnut',
        data: { labels, datasets: [{ data: datos, backgroundColor: colors, borderWidth:2, borderColor:'var(--surface)' }] },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { display:false } }
        }
    });
    const total = datos.reduce((a,b) => a+b, 0);
    document.getElementById('planesLegend').innerHTML = planes.map((p,i) => `
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;">
            <div style="width:10px;height:10px;border-radius:50%;background:${colors[i]};flex-shrink:0;"></div>
            <span style="flex:1;color:var(--text-primary);font-weight:600;">${escHtml(p.nombre)}</span>
            <span style="color:var(--text-secondary);">${p.cantidad} (${total?Math.round(p.cantidad/total*100):0}%)</span>
        </div>`).join('');
}

// ── Chart asistencias ─────────────────────────────────────
function renderAsistencias(rows) {
    const hoy   = new Date();
    const labels = [], datos = [];
    const mapa   = {};
    rows.forEach(r => { mapa[r.fecha] = parseInt(r.total); });
    for (let i = 29; i >= 0; i--) {
        const d = new Date(hoy - i*864e5);
        const k = d.toISOString().slice(0,10);
        labels.push(i % 5 === 0 ? `${d.getDate()}/${d.getMonth()+1}` : '');
        datos.push(mapa[k] || 0);
    }
    if (chartAsist) chartAsist.destroy();
    chartAsist = new Chart(document.getElementById('chartAsistencias').getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Check-ins',
                data: datos,
                backgroundColor: 'rgba(249,115,22,.5)',
                borderColor: '#f97316',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display:false } },
            scales: {
                y: { ticks: { stepSize:1, color:'#888', font:{size:10} }, grid:{color:'rgba(128,128,128,.1)'} },
                x: { ticks: { color:'#888', font:{size:10}, maxRotation:0 }, grid:{display:false} }
            }
        }
    });
}

// ── Lista próximos vencimientos ───────────────────────────
function renderProximos(lista) {
    document.getElementById('cntProximos').textContent = lista.length;
    if (!lista.length) {
        document.getElementById('listaProximos').innerHTML = `<div class="rep-empty"><i class="fas fa-check-circle" style="color:#22c55e;"></i><p>Nadie vence en los próximos 30 días</p></div>`;
        return;
    }
    document.getElementById('listaProximos').innerHTML = lista.map(s => {
        const d = parseInt(s.dias_restantes);
        const cls = d <= 3 ? 'dias-venc' : d <= 7 ? 'dias-warn' : 'dias-ok';
        const ini = `${(s.nombre||'?')[0]}${(s.apellido||'?')[0]}`.toUpperCase();
        return `<div class="socio-row">
            <div class="socio-avatar" style="background:linear-gradient(135deg,#f97316,#fb923c);">${escHtml(ini)}</div>
            <div class="socio-info">
                <div class="socio-nombre">${escHtml(s.nombre)} ${escHtml(s.apellido)}</div>
                <div class="socio-detalle">${escHtml(s.plan_nombre||'Sin plan')} · ${escHtml(s.telefono||'—')}</div>
            </div>
            <span class="dias-badge ${cls}">${d}d</span>
        </div>`;
    }).join('');
}

// ── Lista vencidos ────────────────────────────────────────
function renderVencidos(lista) {
    document.getElementById('cntVencidos').textContent = lista.length;
    if (!lista.length) {
        document.getElementById('listaVencidos').innerHTML = `<div class="rep-empty"><i class="fas fa-check-circle" style="color:#22c55e;"></i><p>¡Todos los socios al día!</p></div>`;
        return;
    }
    document.getElementById('listaVencidos').innerHTML = lista.map(s => {
        const ini = `${(s.nombre||'?')[0]}${(s.apellido||'?')[0]}`.toUpperCase();
        return `<div class="socio-row">
            <div class="socio-avatar" style="background:linear-gradient(135deg,#ef4444,#f87171);">${escHtml(ini)}</div>
            <div class="socio-info">
                <div class="socio-nombre">${escHtml(s.nombre)} ${escHtml(s.apellido)}</div>
                <div class="socio-detalle">${escHtml(s.plan_nombre||'Sin plan')} · venció hace ${s.dias_vencido}d</div>
            </div>
            <button class="btn-cobrar" onclick="abrirCobro(${s.id},'${escAttr(s.nombre)} ${escAttr(s.apellido)}',${s.plan_id||0},${s.plan_precio||0})">
                <i class="fas fa-dollar-sign"></i> Cobrar
            </button>
        </div>`;
    }).join('');
}

// ── Cobro rápido ──────────────────────────────────────────
function abrirCobro(id, nombre, planId, precio) {
    document.getElementById('cobroSocioId').value = id;
    document.getElementById('cobroPlanId').value  = planId;
    document.getElementById('cobroNombre').textContent = nombre;
    document.getElementById('cobroMonto').value   = precio || '';
    document.getElementById('cobroFecha').value   = new Date().toISOString().slice(0,10);
    document.getElementById('modalCobro').classList.add('open');
}

function cerrarCobro() { document.getElementById('modalCobro').classList.remove('open'); }

async function guardarCobro() {
    const socio_id = parseInt(document.getElementById('cobroSocioId').value);
    const plan_id  = parseInt(document.getElementById('cobroPlanId').value) || null;
    const monto    = parseFloat(document.getElementById('cobroMonto').value);
    const fecha    = document.getElementById('cobroFecha').value;
    const metodo   = document.getElementById('cobroMetodo').value;
    if (!monto || monto <= 0) { alert('Ingresá el monto'); return; }
    const r = await fetch(API_PAGOS, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ socio_id, plan_id, monto, fecha, metodo, periodo_desde: fecha })
    });
    const j = await r.json();
    if (j.success) { cerrarCobro(); init(); }
    else alert(j.message || 'Error al registrar');
}

// ── Helpers ───────────────────────────────────────────────
function $m(v)       { return '$' + parseFloat(v||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function escHtml(s)  { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escAttr(s)  { return String(s||'').replace(/'/g,"\\'"); }

document.getElementById('modalCobro').addEventListener('click', e => { if (e.target === document.getElementById('modalCobro')) cerrarCobro(); });
document.addEventListener('keydown', e => { if (e.key==='Escape') cerrarCobro(); });

init();
</script>
</body>
</html>
