<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja & Reportes — Ferretería</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --ferr:#f59e0b; --ferr-dark:#d97706; --ferr-light:rgba(245,158,11,.1); }

        .page-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .page-header h1 { margin:0; font-size:22px; font-weight:800; color:var(--text-primary); }
        .page-header p  { margin:0; font-size:13px; color:var(--text-secondary); }

        .tab-bar { display:flex; gap:4px; padding:16px 24px 0; border-bottom:1px solid var(--border); margin-bottom:20px; }
        .tab-btn { padding:9px 20px; border-radius:10px 10px 0 0; font-size:13px; font-weight:600; cursor:pointer; border:none; background:none; color:var(--text-secondary); border-bottom:2px solid transparent; transition:all .15s; }
        .tab-btn.active { color:var(--ferr); border-bottom-color:var(--ferr); background:var(--ferr-light); }
        .tab-btn:hover:not(.active) { color:var(--text-primary); background:var(--background); }

        .stats-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(155px, 1fr)); gap:14px; padding:0 24px 20px; }
        .stat-card  { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:16px; }
        .stat-card .sc-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-bottom:10px; background:var(--ferr-light); color:var(--ferr); }
        .stat-card .sc-val  { font-size:26px; font-weight:800; color:var(--text-primary); line-height:1; }
        .stat-card .sc-lbl  { font-size:11px; color:var(--text-secondary); margin-top:4px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }

        .alert-banner { margin:0 24px 14px; padding:11px 16px; border-radius:10px; display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; }
        .alert-stock  { background:#fffbeb; border:1.5px solid #f59e0b; color:#92400e; }

        .section-title { font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); padding:0 24px 10px; display:flex; align-items:center; gap:8px; }

        .tabla-wrap { margin:0 24px; border:1.5px solid var(--border); border-radius:12px; overflow:hidden; overflow-x:auto; }
        .tabla-wrap table { width:100%; border-collapse:collapse; min-width:560px; }
        .tabla-wrap th { padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); background:var(--background); border-bottom:1px solid var(--border); text-align:left; white-space:nowrap; }
        .tabla-wrap td { padding:11px 14px; font-size:13px; color:var(--text-primary); border-bottom:1px solid var(--border); }
        .tabla-wrap tr:last-child td { border-bottom:none; }
        .tabla-wrap tr:hover td { background:var(--background); }

        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-borrador  { background:rgba(100,116,139,.12); color:#64748b; }
        .badge-enviado   { background:rgba(59,130,246,.12);  color:#3b82f6; }
        .badge-aprobado  { background:rgba(16,185,129,.12);  color:#059669; }
        .badge-rechazado { background:rgba(239,68,68,.12);   color:#ef4444; }
        .badge-vencido   { background:rgba(239,68,68,.08);   color:#dc2626; }

        /* Stock bajo mini-tabla */
        .stock-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:14px; padding:0 24px 24px; }
        .stock-item { background:var(--surface); border:1.5px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .stock-item.sin-stock { border-color:#ef4444; }
        .stock-item.bajo      { border-color:#f59e0b; }
        .stock-nombre  { font-weight:700; font-size:13px; }
        .stock-codigo  { font-size:11px; color:var(--text-secondary); }
        .stock-num     { font-size:22px; font-weight:800; text-align:right; line-height:1; }
        .stock-min     { font-size:11px; color:var(--text-secondary); text-align:right; }
        .chip-sin { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:800; }
        .chip-bajo { background:#fef3c7; color:#d97706; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700; }

        /* Reportes */
        .periodo-bar { padding:0 24px 16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .periodo-btn { padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; border:1.5px solid var(--border); background:var(--surface); color:var(--text-secondary); cursor:pointer; transition:all .15s; }
        .periodo-btn.active { background:var(--ferr); border-color:var(--ferr); color:#fff; }

        .rep-kpis { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:14px; padding:0 24px 20px; }
        .rep-kpi { background:var(--surface); border:1.5px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
        .rep-kpi .rk-val { font-size:20px; font-weight:800; color:var(--text-primary); line-height:1; }
        .rep-kpi .rk-lbl { font-size:11px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.4px; }

        .charts-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; padding:0 24px 24px; }
        .chart-card  { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:18px; }
        .chart-title { font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:14px; }
        .chart-wrap  { position:relative; height:200px; }

        .empty-state { text-align:center; padding:40px; color:var(--text-secondary); }
        .empty-state i { font-size:36px; opacity:.15; display:block; margin-bottom:12px; }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-cash-register" style="color:var(--ferr);margin-right:10px;"></i>Caja & Reportes</h1>
            <p id="subtitulo">Cargando…</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="<?= $base ?>/views/presupuestos/index.php" class="btn btn-secondary" style="font-size:13px;">
                <i class="fas fa-file-invoice-dollar"></i> Presupuestos
            </a>
            <a href="ordenes.php" class="btn btn-secondary" style="font-size:13px;">
                <i class="fas fa-clipboard-list"></i> Órdenes
            </a>
        </div>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" onclick="mostrarTab('caja',this)"><i class="fas fa-store"></i> Caja del Día</button>
        <button class="tab-btn" onclick="mostrarTab('stock',this)"><i class="fas fa-boxes-stacked"></i> Stock Bajo</button>
        <button class="tab-btn" onclick="mostrarTab('reportes',this)"><i class="fas fa-chart-bar"></i> Reportes</button>
    </div>

    <!-- ══ TAB CAJA ══════════════════════════════════════════════════════════════ -->
    <div id="tabCaja">
        <div class="stats-grid" id="cajaStats">
            <?php for ($i=0;$i<6;$i++): ?>
            <div class="stat-card" style="opacity:.3;"><div class="sc-icon"><i class="fas fa-circle-notch fa-spin"></i></div><div class="sc-val">—</div><div class="sc-lbl">Cargando</div></div>
            <?php endfor; ?>
        </div>

        <div id="stockBanner"></div>

        <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Presupuestos de hoy + enviados activos</div>
        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Vence</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="presTbody">
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-secondary);"><i class="fas fa-circle-notch fa-spin"></i> Cargando…</td></tr>
                </tbody>
            </table>
        </div>
        <div style="height:24px;"></div>
    </div>

    <!-- ══ TAB STOCK BAJO ════════════════════════════════════════════════════════ -->
    <div id="tabStock" style="display:none;">
        <div style="padding:0 24px 12px;font-size:13px;color:var(--text-secondary);">
            Productos con stock igual o por debajo del mínimo configurado.
        </div>
        <div class="stock-grid" id="stockGrid">
            <div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-circle-notch fa-spin"></i></div>
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
            <div class="rep-kpi"><span style="font-size:22px;">📋</span><div><div class="rk-val" id="rk-total">—</div><div class="rk-lbl">Presupuestado</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">✅</span><div><div class="rk-val" id="rk-aprobado">—</div><div class="rk-lbl">Aprobado</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">🛒</span><div><div class="rk-val" id="rk-oc">—</div><div class="rk-lbl">OC pendiente</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">📦</span><div><div class="rk-val" id="rk-inventario">—</div><div class="rk-lbl">Inventario</div></div></div>
        </div>

        <div class="charts-grid">
            <div class="chart-card" style="grid-column:1/-1;">
                <div class="chart-title"><i class="fas fa-chart-bar" style="color:var(--ferr);margin-right:6px;"></i>Presupuestos por día</div>
                <div class="chart-wrap"><canvas id="chartPresupuestos"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-circle-half-stroke" style="color:var(--ferr);margin-right:6px;"></i>Por estado (presupuestos)</div>
                <div class="chart-wrap"><canvas id="chartEstado"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-circle-half-stroke" style="color:var(--ferr);margin-right:6px;"></i>Órdenes por estado</div>
                <div class="chart-wrap"><canvas id="chartOC"></canvas></div>
            </div>
            <div class="chart-card" style="grid-column:1/-1;">
                <div class="chart-title"><i class="fas fa-chart-bar" style="color:var(--ferr);margin-right:6px;"></i>Órdenes de compra por proveedor</div>
                <div class="chart-wrap" style="height:240px;"><canvas id="chartProveedor"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE     = '<?= $base ?>';
const API_CAJA = BASE + '/api/ferreteria/caja.php';

const charts = {};
let repCargado   = false;
let stockCargado = false;

async function init() {
    const hoy = new Date().toLocaleDateString('es-AR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
    document.getElementById('subtitulo').textContent = hoy.charAt(0).toUpperCase() + hoy.slice(1);
    await Promise.all([cargarResumen(), cargarPresupuestosHoy()]);
}

function mostrarTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tabCaja').style.display    = tab === 'caja'     ? '' : 'none';
    document.getElementById('tabStock').style.display   = tab === 'stock'    ? '' : 'none';
    document.getElementById('tabReportes').style.display= tab === 'reportes' ? '' : 'none';
    if (tab === 'stock'    && !stockCargado) cargarStockBajo();
    if (tab === 'reportes' && !repCargado)  cargarReportes(30, document.querySelector('.periodo-btn.active'));
}

async function cargarResumen() {
    const r = await fetch(`${API_CAJA}?type=resumen`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    const d = j.data;

    const stockBajo = parseInt(d.stock_bajo||0);
    if (stockBajo > 0) {
        document.getElementById('stockBanner').innerHTML = `
            <div class="alert-banner alert-stock">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>${stockBajo} producto${stockBajo>1?'s':''} con stock bajo.</strong>
                <button onclick="mostrarTab('stock',document.querySelectorAll('.tab-btn')[1])" style="margin-left:auto;background:none;border:none;color:inherit;font-weight:700;cursor:pointer;font-size:12px;text-decoration:underline;">Ver →</button>
            </div>`;
    }

    document.getElementById('cajaStats').innerHTML = `
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(16,185,129,.1);color:#059669;"><i class="fas fa-check-circle"></i></div>
            <div class="sc-val">${fmt(d.total_aprobado)}</div>
            <div class="sc-lbl">Aprobado mes</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon"><i class="fas fa-paper-plane"></i></div>
            <div class="sc-val">${d.enviados}</div>
            <div class="sc-lbl">Presup. enviados</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon"><i class="fas fa-file-alt"></i></div>
            <div class="sc-val">${d.presupuestos_hoy}</div>
            <div class="sc-lbl">Presup. hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(59,130,246,.1);color:#3b82f6;"><i class="fas fa-truck-loading"></i></div>
            <div class="sc-val">${d.oc_enviadas}</div>
            <div class="sc-lbl">OC pendientes</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(16,185,129,.1);color:#059669;"><i class="fas fa-boxes"></i></div>
            <div class="sc-val">${fmt(d.valor_inventario)}</div>
            <div class="sc-lbl">Valor inventario</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:${parseInt(d.stock_bajo)>0?'#fef3c7':'var(--background)'};color:${parseInt(d.stock_bajo)>0?'#d97706':'var(--text-secondary)'};"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="sc-val" style="color:${parseInt(d.stock_bajo)>0?'#d97706':'inherit'};">${d.stock_bajo}</div>
            <div class="sc-lbl">Stock bajo</div>
        </div>
    `;
}

async function cargarPresupuestosHoy() {
    const r = await fetch(`${API_CAJA}?type=presupuestos_hoy`, {credentials:'include'});
    const j = await r.json();
    const tb = document.getElementById('presTbody');
    if (!j.success || !j.data.length) {
        tb.innerHTML = `<tr><td colspan="6"><div class="empty-state">
            <i class="fas fa-file-invoice-dollar"></i>
            <p>No hay presupuestos activos para hoy</p>
        </div></td></tr>`;
        return;
    }
    tb.innerHTML = j.data.map(p => `<tr>
        <td style="font-weight:700;color:var(--ferr);">${esc(p.numero)}</td>
        <td>
            <div style="font-weight:600;">${esc(p.cliente_nombre||'—')}</div>
            ${p.cliente_tel ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(p.cliente_tel)}</div>` : ''}
        </td>
        <td style="font-size:12px;color:var(--text-secondary);">${fmtFecha(p.fecha)}</td>
        <td style="font-size:12px;color:${p.fecha_vencimiento && p.fecha_vencimiento < new Date().toISOString().slice(0,10) ? '#ef4444' : 'var(--text-secondary)'};">${p.fecha_vencimiento ? fmtFecha(p.fecha_vencimiento) : '—'}</td>
        <td style="font-weight:700;">${fmt(p.total)}</td>
        <td><span class="badge badge-${p.estado}">${estadoLabel(p.estado)}</span></td>
    </tr>`).join('');
}

async function cargarStockBajo() {
    stockCargado = true;
    const r = await fetch(`${API_CAJA}?type=stock_bajo`, {credentials:'include'});
    const j = await r.json();
    const grid = document.getElementById('stockGrid');
    if (!j.success || !j.data.length) {
        grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
            <i class="fas fa-check-double" style="color:#10b981;opacity:1;"></i>
            <p style="color:#059669;font-weight:700;">¡Todo el stock está OK!</p>
        </div>`;
        return;
    }
    grid.innerHTML = j.data.map(p => {
        const chip = p.nivel === 'sin_stock'
            ? `<span class="chip-sin">SIN STOCK</span>`
            : `<span class="chip-bajo">${p.stock} / ${p.stock_minimo} mín</span>`;
        return `<div class="stock-item ${p.nivel === 'sin_stock' ? 'sin-stock' : 'bajo'}">
            <div>
                <div class="stock-nombre">${esc(p.nombre)}</div>
                ${p.codigo_barras ? `<div class="stock-codigo">${esc(p.codigo_barras)}</div>` : ''}
                ${chip}
            </div>
            <div>
                <div class="stock-num" style="color:${p.nivel === 'sin_stock' ? '#ef4444' : '#d97706'};">${p.stock}</div>
                <div class="stock-min">min: ${p.stock_minimo}</div>
            </div>
        </div>`;
    }).join('');
}

async function cargarReportes(dias, btn) {
    document.querySelectorAll('.periodo-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    repCargado = true;

    const [rDia, rEst, rOC, rProv, rRes] = await Promise.all([
        fetch(`${API_CAJA}?type=presupuestos_dia&dias=${dias}`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=por_estado`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=oc_por_estado`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=oc_por_proveedor`, {credentials:'include'}).then(r=>r.json()),
        fetch(`${API_CAJA}?type=resumen`, {credentials:'include'}).then(r=>r.json()),
    ]);

    const diasData  = rDia.data || [];
    const resData   = rRes.data || {};
    const estData   = rEst.data || [];
    const totalPres = diasData.reduce((s,x) => s + parseFloat(x.total||0), 0);
    const aprobado  = estData.find(x=>x.estado==='aprobado');

    document.getElementById('rk-total').textContent     = fmt(totalPres);
    document.getElementById('rk-aprobado').textContent  = aprobado ? fmt(aprobado.total) : '$0';
    document.getElementById('rk-oc').textContent        = fmt(resData.oc_pendiente||0);
    document.getElementById('rk-inventario').textContent= fmt(resData.valor_inventario||0);

    // Chart: Presupuestos por día
    if (charts.presupuestos) charts.presupuestos.destroy();
    charts.presupuestos = new Chart(document.getElementById('chartPresupuestos'), {
        type:'bar',
        data:{
            labels: diasData.map(x => { const [y,m,d]=x.fecha.split('-'); return `${d}/${m}`; }),
            datasets:[{ label:'Total', data: diasData.map(x => parseFloat(x.total||0)),
                backgroundColor:'rgba(245,158,11,.6)', borderColor:'#f59e0b',
                borderWidth:1.5, borderRadius:4 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{ y:{beginAtZero:true, ticks:{callback:v=>'$'+Number(v).toLocaleString('es-AR')}} }}
    });

    // Chart: Por estado presupuestos
    const EST_COLORS = {aprobado:'#10b981',enviado:'#3b82f6',borrador:'#94a3b8',rechazado:'#ef4444',vencido:'#f97316'};
    if (charts.estado) charts.estado.destroy();
    charts.estado = new Chart(document.getElementById('chartEstado'), {
        type:'doughnut',
        data:{
            labels: estData.map(x => estadoLabel(x.estado)),
            datasets:[{ data: estData.map(x => x.cantidad),
                backgroundColor: estData.map(x => EST_COLORS[x.estado]||'#94a3b8'), borderWidth:2 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }}
    });

    // Chart: OC por estado
    const ocData = rOC.data || [];
    const OC_COLORS = {enviada:'#3b82f6',recibida:'#10b981',borrador:'#94a3b8',cancelada:'#ef4444'};
    if (charts.oc) charts.oc.destroy();
    charts.oc = new Chart(document.getElementById('chartOC'), {
        type:'doughnut',
        data:{
            labels: ocData.map(x => x.estado),
            datasets:[{ data: ocData.map(x => x.cantidad),
                backgroundColor: ocData.map(x => OC_COLORS[x.estado]||'#94a3b8'), borderWidth:2 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }}
    });

    // Chart: Por proveedor (horizontal bar)
    const provData = rProv.data || [];
    if (charts.proveedor) charts.proveedor.destroy();
    charts.proveedor = new Chart(document.getElementById('chartProveedor'), {
        type:'bar',
        data:{
            labels: provData.map(x => x.proveedor),
            datasets:[{ label:'Total OC', data: provData.map(x => parseFloat(x.total||0)),
                backgroundColor:'rgba(245,158,11,.6)', borderColor:'#f59e0b',
                borderWidth:1.5, borderRadius:4 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            indexAxis:'y',
            plugins:{ legend:{display:false} },
            scales:{ x:{beginAtZero:true, ticks:{callback:v=>'$'+Number(v).toLocaleString('es-AR')}} }}
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoLabel(e) {
    return {borrador:'Borrador',enviado:'Enviado',aprobado:'Aprobado',rechazado:'Rechazado',vencido:'Vencido'}[e]||e;
}
function fmt(n)  { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s)  { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtFecha(f) { if (!f) return '—'; const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`; }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.style.background = tipo==='error'?'#ef4444':'#1e293b';
    t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500);
}

init();
</script>
</body>
</html>
