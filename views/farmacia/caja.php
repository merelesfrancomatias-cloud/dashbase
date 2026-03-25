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
    <title>Caja & Reportes — Farmacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --farm:#10b981; --farm-dark:#059669; --farm-light:rgba(16,185,129,.1); }

        .app-layout { display:flex; min-height:100vh; }

        .page-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .page-header h1 { margin:0; font-size:22px; font-weight:800; color:var(--text-primary); }
        .page-header p  { margin:0; font-size:13px; color:var(--text-secondary); }

        .tab-bar { display:flex; gap:4px; padding:16px 24px 0; border-bottom:1px solid var(--border); margin-bottom:20px; }
        .tab-btn { padding:9px 20px; border-radius:10px 10px 0 0; font-size:13px; font-weight:600; cursor:pointer; border:none; background:none; color:var(--text-secondary); border-bottom:2px solid transparent; transition:all .15s; }
        .tab-btn.active { color:var(--farm); border-bottom-color:var(--farm); background:var(--farm-light); }
        .tab-btn:hover:not(.active) { color:var(--text-primary); background:var(--background); }

        /* Stats */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(155px, 1fr)); gap:14px; padding:0 24px 20px; }
        .stat-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:16px; }
        .stat-card .sc-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-bottom:10px; background:var(--farm-light); color:var(--farm); }
        .stat-card .sc-val  { font-size:26px; font-weight:800; color:var(--text-primary); line-height:1; }
        .stat-card .sc-lbl  { font-size:11px; color:var(--text-secondary); margin-top:4px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }

        /* Alertas */
        .alert-banner { margin:0 24px 14px; padding:11px 16px; border-radius:10px; display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; cursor:pointer; }
        .alert-venc  { background:#fef2f2; border:1.5px solid #ef4444; color:#991b1b; }
        .alert-stock { background:#fffbeb; border:1.5px solid #f59e0b; color:#92400e; }
        .alert-banner a { color:inherit; margin-left:auto; font-size:12px; text-decoration:underline; }

        /* Tabla recetas */
        .section-title { font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); padding:0 24px 10px; display:flex; align-items:center; gap:8px; }
        .tabla-wrap { margin:0 24px; border:1.5px solid var(--border); border-radius:12px; overflow:hidden; overflow-x:auto; }
        .tabla-wrap table { width:100%; border-collapse:collapse; min-width:520px; }
        .tabla-wrap th { padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); background:var(--background); border-bottom:1px solid var(--border); text-align:left; white-space:nowrap; }
        .tabla-wrap td { padding:11px 14px; font-size:13px; color:var(--text-primary); border-bottom:1px solid var(--border); }
        .tabla-wrap tr:last-child td { border-bottom:none; }
        .tabla-wrap tr:hover td { background:var(--background); }

        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-despachada { background:var(--farm-light); color:var(--farm-dark); }
        .badge-pendiente  { background:#fef3c7; color:#d97706; }
        .badge-vencida    { background:#fee2e2; color:#dc2626; }

        /* Alertas panel */
        .alertas-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; padding:0 24px 24px; }
        @media(max-width:700px){ .alertas-grid { grid-template-columns:1fr; } }
        .alerta-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:18px; }
        .alerta-card h4 { font-size:13px; font-weight:800; color:var(--text-primary); margin-bottom:12px; display:flex; align-items:center; gap:8px; }
        .alerta-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px; }
        .alerta-row:last-child { border-bottom:none; }
        .alerta-nombre { font-weight:600; flex:1; }
        .alerta-detalle { font-size:11px; color:var(--text-secondary); }
        .chip-vencido { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700; }
        .chip-proximo { background:#fef3c7; color:#d97706; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700; }
        .chip-sin-stock { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700; }
        .chip-bajo { background:#fef3c7; color:#d97706; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700; }

        /* Reportes */
        .periodo-bar { padding:0 24px 16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .periodo-btn { padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; border:1.5px solid var(--border); background:var(--surface); color:var(--text-secondary); cursor:pointer; transition:all .15s; }
        .periodo-btn.active { background:var(--farm); border-color:var(--farm); color:#fff; }

        .rep-kpis { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:14px; padding:0 24px 20px; }
        .rep-kpi { background:var(--surface); border:1.5px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
        .rep-kpi .rk-val { font-size:20px; font-weight:800; color:var(--text-primary); line-height:1; }
        .rep-kpi .rk-lbl { font-size:11px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.4px; }

        .charts-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; padding:0 24px 24px; }
        .chart-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:18px; }
        .chart-title { font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:14px; }
        .chart-wrap  { position:relative; height:200px; }

        .empty-state { text-align:center; padding:40px; color:var(--text-secondary); }
        .empty-state i { font-size:36px; opacity:.15; display:block; margin-bottom:12px; }
    </style>
</head>
<body>
<div class="app-layout">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content" style="flex:1;overflow:auto;">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-cash-register" style="color:var(--farm);margin-right:10px;"></i>Caja & Reportes</h1>
            <p id="subtitulo">Cargando…</p>
        </div>
        <a href="recetas.php" class="btn btn-secondary" style="font-size:13px;">
            <i class="fas fa-prescription"></i> Ver Recetas
        </a>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" onclick="mostrarTab('caja',this)"><i class="fas fa-store"></i> Caja del Día</button>
        <button class="tab-btn" onclick="mostrarTab('alertas',this)"><i class="fas fa-exclamation-triangle"></i> Alertas</button>
        <button class="tab-btn" onclick="mostrarTab('reportes',this)"><i class="fas fa-chart-bar"></i> Reportes</button>
    </div>

    <!-- ══ TAB CAJA ══════════════════════════════════════════════════════════════ -->
    <div id="tabCaja">
        <div class="stats-grid" id="cajaStats">
            <?php for ($i=0;$i<6;$i++): ?>
            <div class="stat-card" style="opacity:.3;"><div class="sc-icon"><i class="fas fa-circle-notch fa-spin"></i></div><div class="sc-val">—</div><div class="sc-lbl">Cargando</div></div>
            <?php endfor; ?>
        </div>

        <div id="alertasBanners"></div>

        <div class="section-title"><i class="fas fa-prescription"></i> Recetas despachadas hoy</div>
        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Obra Social</th>
                        <th>Items</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="recetasTbody">
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-secondary);"><i class="fas fa-circle-notch fa-spin"></i> Cargando…</td></tr>
                </tbody>
            </table>
        </div>
        <div style="height:24px;"></div>
    </div>

    <!-- ══ TAB ALERTAS ══════════════════════════════════════════════════════════ -->
    <div id="tabAlertas" style="display:none;">
        <div style="padding:0 24px 12px;">
            <select id="diasAlerta" onchange="cargarAlertas()" class="fi" style="width:160px;font-size:13px;padding:7px 12px;border:1.5px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-primary);">
                <option value="15">Próximos 15 días</option>
                <option value="30" selected>Próximos 30 días</option>
                <option value="60">Próximos 60 días</option>
                <option value="90">Próximos 90 días</option>
            </select>
        </div>
        <div class="alertas-grid">
            <div class="alerta-card">
                <h4><i class="fas fa-calendar-times" style="color:#ef4444;"></i> Vencimientos</h4>
                <div id="alertasVenc"><div class="empty-state"><i class="fas fa-circle-notch fa-spin"></i></div></div>
            </div>
            <div class="alerta-card">
                <h4><i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> Stock bajo</h4>
                <div id="alertasStock"><div class="empty-state"><i class="fas fa-circle-notch fa-spin"></i></div></div>
            </div>
        </div>
    </div>

    <!-- ══ TAB REPORTES ══════════════════════════════════════════════════════════ -->
    <div id="tabReportes" style="display:none;">
        <div class="periodo-bar">
            <span style="font-size:12px;font-weight:700;color:var(--text-secondary);">Período:</span>
            <button class="periodo-btn" onclick="cargarReportes(7,this)">7 días</button>
            <button class="periodo-btn active" onclick="cargarReportes(30,this)">30 días</button>
            <button class="periodo-btn" onclick="cargarReportes(90,this)">90 días</button>
        </div>

        <div class="rep-kpis">
            <div class="rep-kpi"><span style="font-size:22px;">💊</span><div><div class="rk-val" id="rk-despachadas">—</div><div class="rk-lbl">Despachadas</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">⏳</span><div><div class="rk-val" id="rk-pendientes">—</div><div class="rk-lbl">Pendientes</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">🏥</span><div><div class="rk-val" id="rk-obras">—</div><div class="rk-lbl">Obras sociales</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">⚠️</span><div><div class="rk-val" id="rk-venc">—</div><div class="rk-lbl">Alerta venc.</div></div></div>
        </div>

        <div class="charts-grid">
            <div class="chart-card" style="grid-column:1/-1;">
                <div class="chart-title"><i class="fas fa-chart-bar" style="color:var(--farm);margin-right:6px;"></i>Recetas despachadas por día</div>
                <div class="chart-wrap"><canvas id="chartRecetas"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-circle-half-stroke" style="color:var(--farm);margin-right:6px;"></i>Por obra social</div>
                <div class="chart-wrap"><canvas id="chartObrasSociales"></canvas></div>
            </div>
        </div>
    </div>
</div><!-- /main-content -->
</div><!-- /app-layout -->

<div class="toast" id="toast"></div>

<script>
const BASE      = '../../api/farmacia';
const API_CAJA  = BASE + '/caja.php';

const charts = {};
let repCargado     = false;
let alertasCargado = false;

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    const hoy = new Date().toLocaleDateString('es-AR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
    document.getElementById('subtitulo').textContent = hoy.charAt(0).toUpperCase() + hoy.slice(1);
    await Promise.all([cargarResumen(), cargarRecetasHoy()]);
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
function mostrarTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tabCaja').style.display     = tab === 'caja'     ? '' : 'none';
    document.getElementById('tabAlertas').style.display  = tab === 'alertas'  ? '' : 'none';
    document.getElementById('tabReportes').style.display = tab === 'reportes' ? '' : 'none';
    if (tab === 'alertas'  && !alertasCargado) cargarAlertas();
    if (tab === 'reportes' && !repCargado)     cargarReportes(30, document.querySelector('.periodo-btn.active'));
}

// ── Resumen ────────────────────────────────────────────────────────────────────
async function cargarResumen() {
    const r = await fetch(`${API_CAJA}?type=resumen`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    const d = j.data;

    // Banners de alerta
    const banners = document.getElementById('alertasBanners');
    let html = '';
    const vencTotal = parseInt(d.productos_vencidos||0) + parseInt(d.proximos_vencer||0);
    if (vencTotal > 0) {
        html += `<div class="alert-banner alert-venc" onclick="document.querySelector('[onclick*=alertas]').click()">
            <i class="fas fa-calendar-times"></i>
            ${d.productos_vencidos > 0 ? `<strong>${d.productos_vencidos} producto${d.productos_vencidos>1?'s':''} vencido${d.productos_vencidos>1?'s':''}.</strong>&nbsp;` : ''}
            ${d.proximos_vencer > 0 ? `${d.proximos_vencer} vence${d.proximos_vencer>1?'n':''} en 30 días.` : ''}
            <a>Ver alertas →</a>
        </div>`;
    }
    if (parseInt(d.stock_bajo||0) > 0) {
        html += `<div class="alert-banner alert-stock" onclick="document.querySelector('[onclick*=alertas]').click()">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>${d.stock_bajo} producto${d.stock_bajo>1?'s':''} con stock bajo.</strong>
            <a>Ver alertas →</a>
        </div>`;
    }
    banners.innerHTML = html;

    document.getElementById('cajaStats').innerHTML = `
        <div class="stat-card">
            <div class="sc-icon" style="background:var(--farm-light);color:var(--farm);"><i class="fas fa-prescription"></i></div>
            <div class="sc-val">${d.despachadas_hoy}</div>
            <div class="sc-lbl">Despachadas hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clock"></i></div>
            <div class="sc-val">${d.pendientes}</div>
            <div class="sc-lbl">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(99,102,241,.1);color:#4f46e5;"><i class="fas fa-calendar-check"></i></div>
            <div class="sc-val">${d.despachadas_mes}</div>
            <div class="sc-lbl">Despachadas mes</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-times-circle"></i></div>
            <div class="sc-val">${d.recetas_vencidas}</div>
            <div class="sc-lbl">Recetas vencidas</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-calendar-times"></i></div>
            <div class="sc-val">${d.productos_vencidos}</div>
            <div class="sc-lbl">Prod. vencidos</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-boxes-stacked"></i></div>
            <div class="sc-val">${d.stock_bajo}</div>
            <div class="sc-lbl">Stock bajo</div>
        </div>
    `;
}

// ── Recetas hoy ────────────────────────────────────────────────────────────────
async function cargarRecetasHoy() {
    const r = await fetch(`${API_CAJA}?type=recetas_hoy`, {credentials:'include'});
    const j = await r.json();
    const tb = document.getElementById('recetasTbody');
    if (!j.success || !j.data.length) {
        tb.innerHTML = `<tr><td colspan="6"><div class="empty-state">
            <i class="fas fa-prescription"></i>
            <p>No se despacharon recetas hoy</p>
        </div></td></tr>`;
        return;
    }
    tb.innerHTML = j.data.map(rec => `<tr>
        <td style="font-weight:700;color:var(--farm);">#${rec.id}</td>
        <td>
            <div style="font-weight:700;">${esc(rec.paciente||'—')}</div>
            ${rec.dni_paciente ? `<div style="font-size:11px;color:var(--text-secondary);">DNI: ${esc(rec.dni_paciente)}</div>` : ''}
        </td>
        <td>${esc(rec.medico||'—')}</td>
        <td>${esc(rec.obra_social||'Particular')}</td>
        <td><span style="font-weight:600;">${rec.cantidad_items}</span> item${rec.cantidad_items!=1?'s':''}</td>
        <td><span class="badge badge-${rec.estado}">${rec.estado}</span></td>
    </tr>`).join('');
}

// ── Alertas ────────────────────────────────────────────────────────────────────
async function cargarAlertas() {
    alertasCargado = true;
    const dias = document.getElementById('diasAlerta')?.value || 30;
    const [rVenc, rStock] = await Promise.all([
        fetch(`${API_CAJA}?type=alertas_vencimiento&dias=${dias}`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=alertas_stock`, {credentials:'include'}).then(r=>r.json()),
    ]);

    // Vencimientos
    const venc = rVenc.data || [];
    const vDiv = document.getElementById('alertasVenc');
    if (!venc.length) {
        vDiv.innerHTML = `<div class="empty-state"><i class="fas fa-check-circle" style="color:var(--farm);"></i><p>Sin alertas de vencimiento</p></div>`;
    } else {
        vDiv.innerHTML = venc.map(p => {
            const chip = p.estado === 'vencido'
                ? `<span class="chip-vencido">VENCIDO</span>`
                : `<span class="chip-proximo">Vence en ${p.dias_restantes}d</span>`;
            const fecha = formatFecha(p.fecha_vencimiento);
            return `<div class="alerta-row">
                <div>
                    <div class="alerta-nombre">${esc(p.nombre)}</div>
                    <div class="alerta-detalle">Vence: ${fecha} · Stock: ${p.stock}</div>
                </div>
                ${chip}
            </div>`;
        }).join('');
    }

    // Stock bajo
    const stock = rStock.data || [];
    const sDiv = document.getElementById('alertasStock');
    if (!stock.length) {
        sDiv.innerHTML = `<div class="empty-state"><i class="fas fa-check-circle" style="color:var(--farm);"></i><p>Todo el stock OK</p></div>`;
    } else {
        sDiv.innerHTML = stock.map(p => {
            const chip = p.nivel === 'sin_stock'
                ? `<span class="chip-sin-stock">SIN STOCK</span>`
                : `<span class="chip-bajo">${p.stock}/${p.stock_minimo}</span>`;
            return `<div class="alerta-row">
                <div>
                    <div class="alerta-nombre">${esc(p.nombre)}</div>
                    ${p.codigo_barras ? `<div class="alerta-detalle">${esc(p.codigo_barras)}</div>` : ''}
                </div>
                ${chip}
            </div>`;
        }).join('');
    }
}

// ── Reportes ──────────────────────────────────────────────────────────────────
async function cargarReportes(dias, btn) {
    document.querySelectorAll('.periodo-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    repCargado = true;

    const [rDia, rOS, rRes] = await Promise.all([
        fetch(`${API_CAJA}?type=recetas_dia&dias=${dias}`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=por_obra_social`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=resumen`, {credentials:'include'}).then(r=>r.json()),
    ]);

    const diasData = rDia.data || [];
    const total    = diasData.reduce((s, x) => s + parseInt(x.cantidad||0), 0);
    const d        = rRes.data || {};

    document.getElementById('rk-despachadas').textContent = total;
    document.getElementById('rk-pendientes').textContent  = d.pendientes || 0;
    document.getElementById('rk-obras').textContent       = (rOS.data||[]).length;
    document.getElementById('rk-venc').textContent        = (parseInt(d.productos_vencidos||0) + parseInt(d.proximos_vencer||0));

    // Chart: Recetas por día
    if (charts.recetas) charts.recetas.destroy();
    charts.recetas = new Chart(document.getElementById('chartRecetas'), {
        type:'bar',
        data:{
            labels: diasData.map(x => { const [y,m,d]=x.fecha.split('-'); return `${d}/${m}`; }),
            datasets:[{ label:'Recetas', data: diasData.map(x => x.cantidad),
                backgroundColor:'rgba(16,185,129,.6)', borderColor:'#10b981',
                borderWidth:1.5, borderRadius:4 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{ y:{beginAtZero:true, ticks:{stepSize:1}} }
        }
    });

    // Chart: Por obra social
    const os = rOS.data || [];
    const OS_COLORS = ['#10b981','#6366f1','#0ea5e9','#f59e0b','#ec4899','#f97316','#8b5cf6','#94a3b8'];
    if (charts.os) charts.os.destroy();
    charts.os = new Chart(document.getElementById('chartObrasSociales'), {
        type:'doughnut',
        data:{
            labels: os.map(x => x.obra_social),
            datasets:[{ data: os.map(x => x.cantidad),
                backgroundColor: os.map((_, i) => OS_COLORS[i % OS_COLORS.length]),
                borderWidth:2 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }
        }
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function formatFecha(f) { if (!f) return '—'; const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`; }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.style.background = tipo==='error'?'#ef4444':'#1e293b';
    t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500);
}

init();
</script>
</body>
</html>
