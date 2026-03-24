<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes — Peluquería</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
<link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.pelu-color { color:#8b5cf6; }
.rep-header { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px; }
.rep-header h1 { margin:0;font-size:20px;font-weight:700;color:var(--text-primary); }
.filters { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.filters input[type=date] { padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-primary);font-size:13px; }
.periodo-btns { display:flex;gap:6px; }
.p-btn { padding:5px 12px;border:1px solid var(--border);border-radius:20px;background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:12px;font-weight:600;transition:.15s;font-family:inherit; }
.p-btn:hover,.p-btn.active { background:#8b5cf6;color:#fff;border-color:#8b5cf6; }
.btn-rep { display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:600;background:#8b5cf6;color:#fff;transition:.15s; }
.btn-rep:hover { background:#7c3aed; }

/* KPIs */
.kpi-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px; }
.kpi-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px 20px;position:relative;overflow:hidden; }
.kpi-card::before { content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--kc,#8b5cf6); }
.kpi-lbl { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin-bottom:6px; }
.kpi-val { font-size:1.8rem;font-weight:800;color:var(--text-primary);line-height:1; }
.kpi-sub { font-size:12px;color:var(--text-secondary);margin-top:5px; }
.up { color:#22c55e; } .dn { color:#ef4444; } .neu { color:var(--text-secondary); }

/* Charts */
.charts-grid { display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:24px; }
@media(max-width:768px) { .charts-grid { grid-template-columns:1fr; } }
.chart-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px; }
.chart-title { font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:14px;display:flex;align-items:center;gap:8px; }
.chart-title i { color:#8b5cf6; }
canvas { max-height:240px; }

/* Tablas */
.two-col { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px; }
@media(max-width:768px) { .two-col { grid-template-columns:1fr; } }
.sec-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden; }
.sec-head { padding:16px 20px;border-bottom:1px solid var(--border); font-size:13px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px; }
.sec-head i { color:#8b5cf6; }
.rep-table { width:100%;border-collapse:collapse;font-size:13px; }
.rep-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);border-bottom:1px solid var(--border);background:var(--background); }
.rep-table td { padding:11px 16px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
.rep-table tr:last-child td { border-bottom:none; }
.rep-table tr:hover td { background:var(--background); }
.rank { display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;font-size:11px;font-weight:800;background:var(--border);color:var(--text-secondary); }
.r1 { background:#fbbf24;color:#7c4a00; } .r2 { background:#94a3b8;color:#fff; } .r3 { background:#b45309;color:#fff; }
.bar-mini { height:5px;border-radius:3px;background:#8b5cf6;opacity:.6; }
.pago-row { display:flex;align-items:center;gap:10px;padding:11px 16px;border-bottom:1px solid var(--border); }
.pago-row:last-child { border-bottom:none; }
.pago-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
.spin { animation:spin .8s linear infinite; } @keyframes spin { to { transform:rotate(360deg); } }
.rep-empty { text-align:center;padding:32px;color:var(--text-secondary);font-size:13px; }
.rep-empty i { display:block;font-size:2rem;margin-bottom:8px;opacity:.3; }
</style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <div class="rep-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:12px;background:rgba(139,92,246,.12);display:flex;align-items:center;justify-content:center;color:#8b5cf6;font-size:18px;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h1>Reportes — Peluquería</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Ingresos, turnos y rendimiento</p>
                    </div>
                </div>
                <div class="filters">
                    <div class="periodo-btns">
                        <button class="p-btn" data-dias="7">7d</button>
                        <button class="p-btn active" data-dias="30">30d</button>
                        <button class="p-btn" data-dias="90">3m</button>
                    </div>
                    <input type="date" id="inDesde">
                    <input type="date" id="inHasta">
                    <button class="btn-rep" onclick="cargarTodo()"><i class="fas fa-sync-alt"></i> Actualizar</button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-grid" id="kpiGrid">
                <div class="kpi-card" style="grid-column:1/-1"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i><p>Cargando...</p></div></div>
            </div>

            <!-- Ingresos por día + días de la semana -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-chart-area"></i> Ingresos por día</div>
                    <canvas id="chartDia"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-calendar-week"></i> Actividad por día de semana</div>
                    <canvas id="chartSemana"></canvas>
                </div>
            </div>

            <!-- Servicios + Empleados + Pagos -->
            <div class="two-col">
                <div class="sec-card">
                    <div class="sec-head"><i class="fas fa-scissors"></i> Servicios más realizados</div>
                    <div id="tablaServicios"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div></div>
                </div>
                <div class="sec-card">
                    <div class="sec-head"><i class="fas fa-user-tie"></i> Rendimiento por empleado</div>
                    <div id="tablaEmpleados"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div></div>
                </div>
            </div>

            <!-- Métodos de pago -->
            <div class="sec-card" style="margin-bottom:24px;">
                <div class="sec-head"><i class="fas fa-credit-card"></i> Métodos de pago</div>
                <div id="tablaPagos"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div></div>
            </div>

        </div>
    </div>
</div>

<script>
const API = '../../api/peluqueria/reportes.php';
let chartDia = null, chartSemana = null;
const PAGO_COLORS = { efectivo:'#22c55e', transferencia:'#3b82f6', tarjeta:'#8b5cf6', debito:'#f97316' };

function initFechas() {
    const hoy = new Date();
    document.getElementById('inHasta').value = hoy.toISOString().slice(0,10);
    document.getElementById('inDesde').value = new Date(hoy - 29*864e5).toISOString().slice(0,10);
}
function getFechas() {
    return { desde: document.getElementById('inDesde').value, hasta: document.getElementById('inHasta').value };
}

async function cargarTodo() {
    const { desde, hasta } = getFechas();
    const qs = `desde=${desde}&hasta=${hasta}`;
    document.getElementById('kpiGrid').innerHTML = `<div class="kpi-card" style="grid-column:1/-1"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i><p>Cargando...</p></div></div>`;
    ['tablaServicios','tablaEmpleados','tablaPagos'].forEach(id => {
        document.getElementById(id).innerHTML = `<div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div>`;
    });

    const [rRes, rDia, rServ, rEmp, rSem] = await Promise.all([
        fetch(`${API}?tipo=resumen&${qs}`).then(r=>r.json()),
        fetch(`${API}?tipo=ingresos_dia&${qs}`).then(r=>r.json()),
        fetch(`${API}?tipo=servicios&${qs}`).then(r=>r.json()),
        fetch(`${API}?tipo=empleados&${qs}`).then(r=>r.json()),
        fetch(`${API}?tipo=dias_semana&${qs}`).then(r=>r.json()),
    ]);

    if (rRes.success)  renderKPIs(rRes.data);
    if (rDia.success)  renderChartDia(rDia.data);
    if (rServ.success) renderServicios(rServ.data);
    if (rEmp.success)  renderEmpleados(rEmp.data);
    if (rSem.success)  renderChartSemana(rSem.data);
    if (rRes.success)  renderPagos(rRes.data.metodos_pago);
}

function renderKPIs(d) {
    const varPct = d.var_ingresos;
    let varHtml = '<span class="neu">— sin datos prev.</span>';
    if (varPct !== null && varPct !== undefined) {
        const cls = varPct >= 0 ? 'up' : 'dn';
        varHtml = `<span class="${cls}">${varPct >= 0 ? '↑' : '↓'} ${Math.abs(varPct)}% vs período ant.</span>`;
    }
    const conv = d.total_turnos > 0 ? Math.round(d.completados / d.total_turnos * 100) : 0;
    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi-card" style="--kc:#22c55e">
            <div class="kpi-lbl">Ingresos</div>
            <div class="kpi-val">${$m(d.ingresos)}</div>
            <div class="kpi-sub">${varHtml}</div>
        </div>
        <div class="kpi-card" style="--kc:#8b5cf6">
            <div class="kpi-lbl">Turnos completados</div>
            <div class="kpi-val">${d.completados}</div>
            <div class="kpi-sub"><span class="neu">de ${d.total_turnos} agendados (${conv}%)</span></div>
        </div>
        <div class="kpi-card" style="--kc:#f59e0b">
            <div class="kpi-lbl">Ticket promedio</div>
            <div class="kpi-val">${$m(d.ticket_promedio)}</div>
            <div class="kpi-sub"><span class="neu">por turno completado</span></div>
        </div>
        <div class="kpi-card" style="--kc:#ef4444">
            <div class="kpi-lbl">No show / Cancelados</div>
            <div class="kpi-val">${d.no_show + d.cancelados}</div>
            <div class="kpi-sub"><span class="neu">${d.no_show} no show · ${d.cancelados} cancelados</span></div>
        </div>
    `;
}

function renderChartDia(rows) {
    const labels = rows.map(r => { const [,m,d] = r.fecha.split('-'); return `${d}/${m}`; });
    const datos  = rows.map(r => parseFloat(r.total));
    if (chartDia) chartDia.destroy();
    chartDia = new Chart(document.getElementById('chartDia').getContext('2d'), {
        type: 'bar',
        data: { labels, datasets: [{
            label: 'Ingresos', data: datos,
            backgroundColor: 'rgba(139,92,246,.45)', borderColor: '#8b5cf6',
            borderWidth: 1, borderRadius: 5
        }]},
        options: {
            responsive: true, plugins: { legend: { display:false } },
            scales: {
                y: { ticks: { callback: v => '$'+v.toLocaleString('es-AR'), color:'#888', font:{size:11} }, grid:{color:'rgba(128,128,128,.12)'} },
                x: { ticks: { color:'#888', font:{size:11} }, grid:{display:false} }
            }
        }
    });
}

function renderChartSemana(rows) {
    const DIAS = { 1:'Dom', 2:'Lun', 3:'Mar', 4:'Mié', 5:'Jue', 6:'Vie', 7:'Sáb' };
    const mapa = {};
    rows.forEach(r => { mapa[r.dia_num] = { turnos: parseInt(r.turnos), ing: parseFloat(r.ingresos) }; });
    const labels = [], datos = [];
    for (let i = 2; i <= 7; i++) { labels.push(DIAS[i]); datos.push(mapa[i]?.turnos || 0); }
    labels.push('Dom'); datos.push(mapa[1]?.turnos || 0);
    const max = Math.max(...datos);
    if (chartSemana) chartSemana.destroy();
    chartSemana = new Chart(document.getElementById('chartSemana').getContext('2d'), {
        type: 'bar',
        data: { labels, datasets: [{
            data: datos,
            backgroundColor: datos.map(v => v === max && v > 0 ? '#8b5cf6' : 'rgba(139,92,246,.3)'),
            borderRadius: 5, borderSkipped: false
        }]},
        options: {
            responsive: true, plugins: { legend: { display:false } },
            scales: {
                y: { ticks: { stepSize:1, color:'#888', font:{size:11} }, grid:{color:'rgba(128,128,128,.12)'} },
                x: { ticks: { color:'#888', font:{size:11} }, grid:{display:false} }
            }
        }
    });
}

function renderServicios(lista) {
    if (!lista.length) { document.getElementById('tablaServicios').innerHTML = `<div class="rep-empty"><i class="fas fa-scissors"></i><p>Sin datos</p></div>`; return; }
    const max = Math.max(...lista.map(s => parseInt(s.cantidad)));
    let html = '<div style="overflow-x:auto"><table class="rep-table"><thead><tr><th>#</th><th>Servicio</th><th>Veces</th><th style="text-align:right">Total</th></tr></thead><tbody>';
    lista.forEach((s, i) => {
        const n = i + 1;
        const pct = max > 0 ? Math.round(parseInt(s.cantidad)/max*100) : 0;
        html += `<tr>
            <td><span class="rank ${n<=3?'r'+n:''}">${n}</span></td>
            <td style="font-weight:600">${esc(s.servicio_nombre)}</td>
            <td><div style="display:flex;align-items:center;gap:8px"><span style="font-weight:700;min-width:24px">${s.cantidad}</span><div style="flex:1;max-width:100px"><div class="bar-mini" style="width:${pct}%"></div></div></div></td>
            <td style="text-align:right;font-weight:700">${$m(s.total)}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('tablaServicios').innerHTML = html;
}

function renderEmpleados(lista) {
    if (!lista.length) { document.getElementById('tablaEmpleados').innerHTML = `<div class="rep-empty"><i class="fas fa-user-tie"></i><p>Sin empleados registrados en turnos</p></div>`; return; }
    let html = '<div style="overflow-x:auto"><table class="rep-table"><thead><tr><th>Empleado</th><th>Turnos</th><th style="text-align:right">Ingresos</th><th style="text-align:right">Comisión</th></tr></thead><tbody>';
    lista.forEach(e => {
        const tieneComision = parseFloat(e.comision_estimada||0) > 0;
        html += `<tr>
            <td>
                <div style="font-weight:700;">${esc(e.empleado)}</div>
                ${e.cargo ? `<div style="font-size:11px;color:var(--text-secondary)">${esc(e.cargo)}</div>` : ''}
            </td>
            <td><span style="font-size:12px;color:var(--text-secondary)">${e.completados}/${e.turnos} completados</span></td>
            <td style="text-align:right;font-weight:700;color:#8b5cf6">${$m(e.ingresos)}</td>
            <td style="text-align:right;font-weight:700;color:${tieneComision?'#16a34a':'#94a3b8'}">${tieneComision ? $m(e.comision_estimada) : '—'}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('tablaEmpleados').innerHTML = html;
}

function renderPagos(pagos) {
    if (!pagos || !pagos.length) {
        document.getElementById('tablaPagos').innerHTML = `<div class="rep-empty"><i class="fas fa-credit-card"></i><p>Sin cobros registrados con método de pago en este período</p></div>`;
        return;
    }
    const total = pagos.reduce((s,p) => s + parseFloat(p.total), 0);
    let html = '';
    pagos.forEach(p => {
        const pct = total > 0 ? Math.round(parseFloat(p.total)/total*100) : 0;
        const color = PAGO_COLORS[p.metodo_pago] || '#94a3b8';
        html += `<div class="pago-row">
            <div class="pago-dot" style="background:${color}"></div>
            <div style="flex:1;font-size:13px;font-weight:600;color:var(--text-primary);text-transform:capitalize">${esc(p.metodo_pago||'Otro')}</div>
            <div style="flex:2;padding:0 12px"><div class="bar-mini" style="width:${pct}%;background:${color}"></div></div>
            <div style="font-size:13px;font-weight:700;color:var(--text-primary)">${$m(p.total)}</div>
            <div style="font-size:11px;color:var(--text-secondary);min-width:34px;text-align:right">${pct}%</div>
        </div>`;
    });
    document.getElementById('tablaPagos').innerHTML = html;
}

document.querySelectorAll('.p-btn[data-dias]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.p-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const dias = parseInt(btn.dataset.dias);
        const hoy = new Date();
        document.getElementById('inHasta').value = hoy.toISOString().slice(0,10);
        document.getElementById('inDesde').value = new Date(hoy - (dias-1)*864e5).toISOString().slice(0,10);
        cargarTodo();
    });
});

function $m(v)  { return '$' + parseFloat(v||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

initFechas();
cargarTodo();
</script>
</body>
</html>
