<?php
session_start();
if (!isset($_SESSION['negocio_id'])) {
    header('Location: ../auth/login.php'); exit;
}
$base = '/DASHBASE';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes Restaurant</title>
<link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css">
<link rel="stylesheet" href="<?= $base ?>/public/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ── Layout ─────────────────────────────────────────────── */
.rep-header{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:24px}
.rep-header h1{font-size:1.4rem;font-weight:700;margin:0;flex:1}
.rep-filters{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.rep-filters input[type=date]{padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:13px}
.btn-rep{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:600;transition:.15s}
.btn-rep-primary{background:var(--primary);color:#fff}
.btn-rep-primary:hover{filter:brightness(1.1)}

/* ── KPI Cards ──────────────────────────────────────────── */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px}
.kpi-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px 20px;position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--kpi-color,var(--primary))}
.kpi-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:6px}
.kpi-value{font-size:1.8rem;font-weight:800;color:var(--text);line-height:1}
.kpi-sub{font-size:12px;color:var(--text-muted);margin-top:5px;display:flex;align-items:center;gap:4px}
.kpi-up{color:#10b981}.kpi-down{color:#ef4444}.kpi-neutral{color:var(--text-muted)}

/* ── Charts grid ────────────────────────────────────────── */
.charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px}
@media(max-width:768px){.charts-grid{grid-template-columns:1fr}}
.chart-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px}
.chart-card-full{grid-column:1/-1}
.chart-title{font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.chart-title i{color:var(--primary)}
.chart-wrap{position:relative}
.chart-wrap canvas{max-height:260px}

/* ── Tabla platos ───────────────────────────────────────── */
.platos-table{width:100%;border-collapse:collapse}
.platos-table th{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);padding:8px 12px;border-bottom:2px solid var(--border);text-align:left}
.platos-table td{padding:10px 12px;border-bottom:1px solid var(--border);font-size:13px;color:var(--text)}
.platos-table tr:last-child td{border-bottom:none}
.platos-table tr:hover td{background:rgba(var(--primary-rgb),.04)}
.rank-badge{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;font-size:11px;font-weight:800;background:var(--border);color:var(--text-muted)}
.rank-1{background:#fbbf24;color:#7c4a00}
.rank-2{background:#94a3b8;color:#fff}
.rank-3{background:#b45309;color:#fff}
.bar-mini{height:6px;border-radius:3px;background:var(--primary);opacity:.7;transition:width .4s}

/* ── Métodos de pago ────────────────────────────────────── */
.pago-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.pago-item:last-child{border-bottom:none}
.pago-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.pago-label{flex:1;font-size:13px;font-weight:600;color:var(--text);text-transform:capitalize}
.pago-amount{font-size:13px;font-weight:700;color:var(--text)}
.pago-pct{font-size:11px;color:var(--text-muted);min-width:36px;text-align:right}

/* ── Empty state ────────────────────────────────────────── */
.rep-empty{text-align:center;padding:48px 20px;color:var(--text-muted)}
.rep-empty i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.4}
.rep-empty p{font-size:14px}

/* ── Spinner ────────────────────────────────────────────── */
.rep-loading{display:flex;align-items:center;justify-content:center;padding:60px;gap:10px;color:var(--text-muted)}
.spin{animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Período rápido ─────────────────────────────────────── */
.periodo-btns{display:flex;gap:6px;flex-wrap:wrap}
.p-btn{padding:5px 12px;border:1px solid var(--border);border-radius:20px;background:var(--surface);color:var(--text-muted);cursor:pointer;font-size:12px;font-weight:600;transition:.15s}
.p-btn:hover,.p-btn.active{background:var(--primary);color:#fff;border-color:var(--primary)}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">

<div class="rep-header">
    <div style="display:flex;align-items:center;gap:10px">
        <div style="width:38px;height:38px;border-radius:10px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff">
            <i class="fas fa-chart-line"></i>
        </div>
        <h1>Reportes Restaurant</h1>
    </div>

    <div class="rep-filters">
        <div class="periodo-btns">
            <button class="p-btn" data-dias="7">7d</button>
            <button class="p-btn active" data-dias="30">30d</button>
            <button class="p-btn" data-dias="90">3m</button>
            <button class="p-btn" data-dias="365">1a</button>
        </div>
        <input type="date" id="inputDesde">
        <input type="date" id="inputHasta">
        <button class="btn-rep btn-rep-primary" onclick="cargarTodo()">
            <i class="fas fa-sync-alt"></i> Actualizar
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="kpi-grid" id="kpiGrid">
    <div class="rep-loading" style="grid-column:1/-1">
        <i class="fas fa-circle-notch spin"></i> Cargando...
    </div>
</div>

<!-- Gráficos -->
<div class="charts-grid">
    <!-- Ventas por día -->
    <div class="chart-card chart-card-full">
        <div class="chart-title"><i class="fas fa-chart-area"></i> Ingresos por Día</div>
        <div class="chart-wrap"><canvas id="chartVentas"></canvas></div>
    </div>

    <!-- Franjas horarias -->
    <div class="chart-card">
        <div class="chart-title"><i class="fas fa-clock"></i> Actividad por Hora</div>
        <div class="chart-wrap"><canvas id="chartFranjas"></canvas></div>
    </div>

    <!-- Métodos de pago -->
    <div class="chart-card">
        <div class="chart-title"><i class="fas fa-credit-card"></i> Métodos de Pago</div>
        <div id="pagosContainer">
            <div class="rep-loading"><i class="fas fa-circle-notch spin"></i></div>
        </div>
    </div>
</div>

<!-- Platos más vendidos -->
<div class="chart-card" style="margin-bottom:24px">
    <div class="chart-title"><i class="fas fa-fire"></i> Platos Más Vendidos</div>
    <div id="platosContainer">
        <div class="rep-loading"><i class="fas fa-circle-notch spin"></i></div>
    </div>
</div>

</main>
</div>

<script>
const API = '/DASHBASE/api/restaurant/reportes.php';
let chartVentas = null, chartFranjas = null;

// Colores para métodos de pago
const PAGO_COLORS = {
    efectivo:   '#10b981',
    tarjeta:    '#6366f1',
    debito:     '#8b5cf6',
    credito:    '#f59e0b',
    transferencia: '#0ea5e9',
    mercadopago:'#3b82f6',
    qr:         '#14b8a6',
};
function pagoColor(metodo) {
    const m = (metodo||'').toLowerCase();
    for (const [k,v] of Object.entries(PAGO_COLORS)) if (m.includes(k)) return v;
    return '#94a3b8';
}

// ── Inicializar fechas ────────────────────────────────────
function initFechas() {
    const hoy = new Date();
    const fmt = d => d.toISOString().slice(0,10);
    const hasta = fmt(hoy);
    const desde = fmt(new Date(hoy - 29*864e5));
    document.getElementById('inputHasta').value = hasta;
    document.getElementById('inputDesde').value = desde;
}

function getFechas() {
    return {
        desde: document.getElementById('inputDesde').value,
        hasta: document.getElementById('inputHasta').value,
    };
}

// ── Formato moneda ────────────────────────────────────────
function $m(v) {
    return '$' + parseFloat(v||0).toLocaleString('es-AR', {minimumFractionDigits:0, maximumFractionDigits:0});
}
function varianza(pct, positivo=true) {
    if (pct === null || pct === undefined) return `<span class="kpi-neutral">— sin datos prev.</span>`;
    const bueno = positivo ? pct >= 0 : pct <= 0;
    const cls = bueno ? 'kpi-up' : 'kpi-down';
    const ico = pct >= 0 ? '↑' : '↓';
    return `<span class="${cls}">${ico} ${Math.abs(pct)}% vs período ant.</span>`;
}

// ── Carga paralela ────────────────────────────────────────
async function cargarTodo() {
    const {desde, hasta} = getFechas();
    const qs = `desde=${desde}&hasta=${hasta}`;

    // Loading states
    document.getElementById('kpiGrid').innerHTML = `<div class="rep-loading" style="grid-column:1/-1"><i class="fas fa-circle-notch spin"></i> Cargando...</div>`;
    document.getElementById('platosContainer').innerHTML = `<div class="rep-loading"><i class="fas fa-circle-notch spin"></i></div>`;
    document.getElementById('pagosContainer').innerHTML  = `<div class="rep-loading"><i class="fas fa-circle-notch spin"></i></div>`;

    try {
        const [rRes, rVentas, rPlatos, rFranjas] = await Promise.all([
            fetch(`${API}?tipo=resumen&${qs}`).then(r=>r.json()),
            fetch(`${API}?tipo=ventas_dia&${qs}`).then(r=>r.json()),
            fetch(`${API}?tipo=platos&${qs}&limit=10`).then(r=>r.json()),
            fetch(`${API}?tipo=franjas&${qs}`).then(r=>r.json()),
        ]);

        if (rRes.success)    renderKPIs(rRes.data);
        if (rVentas.success) renderChartVentas(rVentas.data);
        if (rPlatos.success) renderPlatos(rPlatos.data);
        if (rFranjas.success) renderChartFranjas(rFranjas.data);
        if (rRes.success)    renderPagos(rRes.data.metodos_pago);
    } catch(e) {
        console.error(e);
    }
}

// ── KPIs ──────────────────────────────────────────────────
function renderKPIs(d) {
    const tiempoMesa = d.minutos_mesa_prom > 0
        ? (d.minutos_mesa_prom >= 60
            ? Math.floor(d.minutos_mesa_prom/60)+'h '+(d.minutos_mesa_prom%60)+'m'
            : d.minutos_mesa_prom+'m')
        : '—';

    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi-card" style="--kpi-color:#10b981">
            <div class="kpi-label">Ingresos</div>
            <div class="kpi-value">${$m(d.ingresos)}</div>
            <div class="kpi-sub">${varianza(d.var_ingresos)}</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#6366f1">
            <div class="kpi-label">Comandas Cerradas</div>
            <div class="kpi-value">${d.total_comandas}</div>
            <div class="kpi-sub">${varianza(d.var_comandas)}</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#f59e0b">
            <div class="kpi-label">Ticket Promedio</div>
            <div class="kpi-value">${$m(d.ticket_promedio)}</div>
            <div class="kpi-sub"><span class="kpi-neutral">por mesa</span></div>
        </div>
        <div class="kpi-card" style="--kpi-color:#0ea5e9">
            <div class="kpi-label">Personas Prom.</div>
            <div class="kpi-value">${d.personas_promedio}</div>
            <div class="kpi-sub"><span class="kpi-neutral">por mesa</span></div>
        </div>
        <div class="kpi-card" style="--kpi-color:#8b5cf6">
            <div class="kpi-label">Tiempo en Mesa</div>
            <div class="kpi-value" style="font-size:1.4rem">${tiempoMesa}</div>
            <div class="kpi-sub"><span class="kpi-neutral">promedio</span></div>
        </div>
        <div class="kpi-card" style="--kpi-color:#ef4444">
            <div class="kpi-label">Período analizado</div>
            <div class="kpi-value" style="font-size:1.4rem">${d.periodo_dias}d</div>
            <div class="kpi-sub"><span class="kpi-neutral">${getFechas().desde} → ${getFechas().hasta}</span></div>
        </div>
    `;
}

// ── Chart Ventas ──────────────────────────────────────────
function renderChartVentas(rows) {
    const labels = rows.map(r => {
        const [y,m,d] = r.fecha.split('-');
        return `${d}/${m}`;
    });
    const totales  = rows.map(r => parseFloat(r.total));
    const comandas = rows.map(r => parseInt(r.comandas));

    if (chartVentas) chartVentas.destroy();
    const ctx = document.getElementById('chartVentas').getContext('2d');
    chartVentas = new Chart(ctx, {
        data: {
            labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Ingresos ($)',
                    data: totales,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,.1)',
                    fill: true,
                    tension: .35,
                    pointRadius: rows.length <= 14 ? 4 : 0,
                    pointHoverRadius: 6,
                    yAxisID: 'y',
                    borderWidth: 2,
                },
                {
                    type: 'bar',
                    label: 'Comandas',
                    data: comandas,
                    backgroundColor: 'rgba(99,102,241,.3)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { color: getComputedStyle(document.body).getPropertyValue('--text') || '#fff', font:{size:12} } } },
            scales: {
                y:  { position:'left',  ticks: { callback: v => '$'+v.toLocaleString('es-AR'), color:'#888', font:{size:11} }, grid: { color:'rgba(128,128,128,.15)' } },
                y2: { position:'right', ticks: { color:'#888', font:{size:11} }, grid: { display:false } },
                x:  { ticks: { color:'#888', font:{size:11} }, grid: { color:'rgba(128,128,128,.08)' } }
            }
        }
    });
}

// ── Chart Franjas ─────────────────────────────────────────
function renderChartFranjas(rows) {
    const labels   = rows.map(r => r.hora + ':00');
    const comandas = rows.map(r => parseInt(r.comandas));
    const maxVal   = Math.max(...comandas);

    if (chartFranjas) chartFranjas.destroy();
    const ctx = document.getElementById('chartFranjas').getContext('2d');
    chartFranjas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Comandas abiertas',
                data: comandas,
                backgroundColor: comandas.map(v => v === maxVal && v > 0
                    ? 'rgba(251,191,36,.9)'
                    : 'rgba(99,102,241,.5)'),
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { stepSize:1, color:'#888', font:{size:11} }, grid: { color:'rgba(128,128,128,.15)' } },
                x: { ticks: { color:'#888', font:{size:10}, maxRotation:0 }, grid: { display:false } }
            }
        }
    });
}

// ── Métodos de pago ───────────────────────────────────────
function renderPagos(pagos) {
    if (!pagos || !pagos.length) {
        document.getElementById('pagosContainer').innerHTML = `<div class="rep-empty"><i class="fas fa-credit-card"></i><p>Sin datos</p></div>`;
        return;
    }
    const totalGeneral = pagos.reduce((s,p) => s + parseFloat(p.total), 0);
    let html = '';
    pagos.forEach(p => {
        const pct = totalGeneral > 0 ? Math.round(parseFloat(p.total)/totalGeneral*100) : 0;
        html += `
            <div class="pago-item">
                <div class="pago-dot" style="background:${pagoColor(p.metodo_pago)}"></div>
                <div class="pago-label">${p.metodo_pago || 'Otro'}</div>
                <div style="flex:1;padding:0 10px">
                    <div class="bar-mini" style="width:${pct}%;background:${pagoColor(p.metodo_pago)}"></div>
                </div>
                <div class="pago-amount">${$m(p.total)}</div>
                <div class="pago-pct">${pct}%</div>
            </div>`;
    });
    document.getElementById('pagosContainer').innerHTML = html;
}

// ── Platos ────────────────────────────────────────────────
function renderPlatos(platos) {
    if (!platos || !platos.length) {
        document.getElementById('platosContainer').innerHTML = `<div class="rep-empty"><i class="fas fa-utensils"></i><p>Sin datos en este período</p></div>`;
        return;
    }
    const maxU = Math.max(...platos.map(p => parseFloat(p.unidades)));
    let html = `<div style="overflow-x:auto"><table class="platos-table"><thead>
        <tr>
            <th>#</th>
            <th>Plato</th>
            <th>Unidades vendidas</th>
            <th style="text-align:right">Total</th>
            <th style="text-align:right">Precio prom.</th>
        </tr>
    </thead><tbody>`;
    platos.forEach((p, i) => {
        const n = i + 1;
        const pct = maxU > 0 ? Math.round(parseFloat(p.unidades)/maxU*100) : 0;
        html += `<tr>
            <td><span class="rank-badge rank-${n}">${n}</span></td>
            <td style="font-weight:600">${escHtml(p.nombre)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-weight:700;min-width:28px">${Math.round(p.unidades)}</span>
                    <div style="flex:1;max-width:160px">
                        <div class="bar-mini" style="width:${pct}%"></div>
                    </div>
                </div>
            </td>
            <td style="text-align:right;font-weight:700">${$m(p.total)}</td>
            <td style="text-align:right;color:var(--text-muted)">${$m(p.precio_promedio)}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    document.getElementById('platosContainer').innerHTML = html;
}

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Botones de período rápido ─────────────────────────────
document.querySelectorAll('.p-btn[data-dias]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.p-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const dias = parseInt(btn.dataset.dias);
        const hoy  = new Date();
        document.getElementById('inputHasta').value = hoy.toISOString().slice(0,10);
        document.getElementById('inputDesde').value = new Date(hoy - (dias-1)*864e5).toISOString().slice(0,10);
        cargarTodo();
    });
});

// ── Init ──────────────────────────────────────────────────
initFechas();
cargarTodo();
</script>

</body>
</html>
