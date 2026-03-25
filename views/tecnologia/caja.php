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
    <title>Caja & Reportes — Servicio Técnico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --tec:#6366f1; --tec-dark:#4f46e5; --tec-light:rgba(99,102,241,.1); }

        .page-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .page-header h1 { margin:0; font-size:22px; font-weight:800; color:var(--text-primary); }
        .page-header p  { margin:0; font-size:13px; color:var(--text-secondary); }

        .tab-bar { display:flex; gap:4px; padding:16px 24px 0; border-bottom:1px solid var(--border); margin-bottom:20px; }
        .tab-btn { padding:9px 20px; border-radius:10px 10px 0 0; font-size:13px; font-weight:600; cursor:pointer; border:none; background:none; color:var(--text-secondary); border-bottom:2px solid transparent; transition:all .15s; }
        .tab-btn.active { color:var(--tec); border-bottom-color:var(--tec); background:var(--tec-light); }
        .tab-btn:hover:not(.active) { color:var(--text-primary); background:var(--background); }

        .stats-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:14px; padding:0 24px 20px; }
        .stat-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:16px; }
        .stat-card .sc-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-bottom:10px; background:var(--tec-light); color:var(--tec); }
        .stat-card .sc-val  { font-size:26px; font-weight:800; color:var(--text-primary); line-height:1; }
        .stat-card .sc-lbl  { font-size:11px; color:var(--text-secondary); margin-top:4px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }

        .section-title { font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); padding:0 24px 10px; display:flex; align-items:center; gap:8px; }

        .ordenes-tabla { margin:0 24px; border:1.5px solid var(--border); border-radius:12px; overflow:hidden; }
        .ordenes-tabla table { width:100%; border-collapse:collapse; }
        .ordenes-tabla th { padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); background:var(--background); border-bottom:1px solid var(--border); text-align:left; }
        .ordenes-tabla td { padding:11px 14px; font-size:13px; color:var(--text-primary); border-bottom:1px solid var(--border); }
        .ordenes-tabla tr:last-child td { border-bottom:none; }
        .ordenes-tabla tr:hover td { background:var(--background); }

        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-listo           { background:rgba(15,209,134,.15); color:#059669; }
        .badge-entregado       { background:var(--tec-light); color:var(--tec-dark); }
        .badge-en_reparacion   { background:rgba(245,158,11,.15); color:#d97706; }
        .badge-diagnosticando  { background:rgba(139,92,246,.15); color:#7c3aed; }
        .badge-esperando_repuesto { background:rgba(239,68,68,.15); color:#dc2626; }
        .badge-ingresado       { background:var(--background); color:var(--text-secondary); border:1px solid var(--border); }

        .prio-chip { display:inline-block; padding:2px 7px; border-radius:12px; font-size:10px; font-weight:800; margin-left:4px; }
        .prio-urgente { background:#fef2f2; color:#dc2626; }
        .prio-vip     { background:#fffbeb; color:#d97706; }

        .btn-sm { padding:5px 10px; border-radius:8px; font-size:12px; font-weight:600; border:1.5px solid transparent; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all .15s; }
        .btn-wa    { background:#25d366; color:#fff; border-color:#25d366; }
        .btn-wa:hover { background:#128c7e; border-color:#128c7e; }
        .btn-print { background:var(--tec-light); color:var(--tec); border-color:var(--tec); }
        .btn-print:hover { background:var(--tec); color:#fff; }

        .charts-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; padding:0 24px 24px; }
        .chart-card { background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:18px; }
        .chart-title { font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:14px; }
        .chart-wrap  { position:relative; height:200px; }

        .periodo-bar { padding:0 24px 16px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .periodo-btn { padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; border:1.5px solid var(--border); background:var(--surface); color:var(--text-secondary); cursor:pointer; transition:all .15s; }
        .periodo-btn.active { background:var(--tec); border-color:var(--tec); color:#fff; }

        .rep-kpis { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:14px; padding:0 24px 20px; }
        .rep-kpi { background:var(--surface); border:1.5px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
        .rep-kpi .rk-val { font-size:20px; font-weight:800; color:var(--text-primary); line-height:1; }
        .rep-kpi .rk-lbl { font-size:11px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.4px; }

        .urgente-banner { margin:0 24px 16px; padding:12px 16px; background:#fef2f2; border:1.5px solid #ef4444; border-radius:10px; display:flex; align-items:center; gap:10px; font-size:13px; color:#991b1b; font-weight:600; }
        .empty-state { text-align:center; padding:48px 24px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-cash-register" style="color:var(--tec);margin-right:10px;"></i>Caja & Reportes</h1>
            <p id="subtitulo">Cargando…</p>
        </div>
        <a href="ordenes.php" class="btn btn-secondary" style="font-size:13px;">
            <i class="fas fa-tools"></i> Ver Órdenes
        </a>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" onclick="mostrarTab('caja',this)"><i class="fas fa-store"></i> Caja del Día</button>
        <button class="tab-btn" onclick="mostrarTab('reportes',this)"><i class="fas fa-chart-bar"></i> Reportes</button>
    </div>

    <!-- ══ TAB CAJA ══════════════════════════════════════════════════════════════ -->
    <div id="tabCaja">
        <div class="stats-grid" id="cajaStats">
            <?php for ($i=0;$i<6;$i++): ?>
            <div class="stat-card" style="opacity:.3;"><div class="sc-icon"><i class="fas fa-circle-notch fa-spin"></i></div><div class="sc-val">—</div><div class="sc-lbl">Cargando</div></div>
            <?php endfor; ?>
        </div>

        <div id="urgenteBanner" style="display:none;" class="urgente-banner">
            <i class="fas fa-fire"></i> <span id="urgenteTexto"></span>
        </div>

        <div class="section-title"><i class="fas fa-list-check"></i> Listas para entregar + entregadas hoy</div>
        <div class="ordenes-tabla">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Equipo</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cajaOrdenesTbody">
                    <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-secondary);"><i class="fas fa-circle-notch fa-spin"></i> Cargando…</td></tr>
                </tbody>
            </table>
        </div>
        <div style="height:24px;"></div>
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
            <div class="rep-kpi"><span style="font-size:22px;">💰</span><div><div class="rk-val" id="rk-ingresos">—</div><div class="rk-lbl">Ingresos</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">🔧</span><div><div class="rk-val" id="rk-entregados">—</div><div class="rk-lbl">Entregados</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">🧾</span><div><div class="rk-val" id="rk-ticket">—</div><div class="rk-lbl">Ticket promedio</div></div></div>
            <div class="rep-kpi"><span style="font-size:22px;">⚠️</span><div><div class="rk-val" id="rk-saldo">—</div><div class="rk-lbl">Saldo pendiente</div></div></div>
        </div>

        <div class="charts-grid">
            <div class="chart-card" style="grid-column:1/-1;">
                <div class="chart-title"><i class="fas fa-chart-bar" style="color:var(--tec);margin-right:6px;"></i>Ingresos por día</div>
                <div class="chart-wrap"><canvas id="chartIngresos"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-circle-half-stroke" style="color:var(--tec);margin-right:6px;"></i>Por tipo de equipo</div>
                <div class="chart-wrap"><canvas id="chartTipo"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-chart-pie" style="color:var(--tec);margin-right:6px;"></i>Distribución por estado</div>
                <div class="chart-wrap"><canvas id="chartEstado"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE     = '<?= $base ?>';
const API_CAJA = BASE + '/api/tecnologia/caja.php';
const API_ORD  = BASE + '/api/tecnologia/ordenes.php';

const charts = {};
let repCargado = false;

async function init() {
    const hoy = new Date().toLocaleDateString('es-AR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
    document.getElementById('subtitulo').textContent = hoy.charAt(0).toUpperCase() + hoy.slice(1);
    await Promise.all([cargarResumen(), cargarOrdenesHoy()]);
}

function mostrarTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tabCaja').style.display     = tab === 'caja'     ? '' : 'none';
    document.getElementById('tabReportes').style.display = tab === 'reportes' ? '' : 'none';
    if (tab === 'reportes' && !repCargado) cargarReportes(30, document.querySelector('.periodo-btn.active'));
}

async function cargarResumen() {
    const r = await fetch(`${API_CAJA}?type=resumen`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    const d = j.data;

    if (parseInt(d.urgentes||0) > 0) {
        document.getElementById('urgenteBanner').style.display = 'flex';
        document.getElementById('urgenteTexto').textContent = `${d.urgentes} orden${d.urgentes>1?'es':''} urgente${d.urgentes>1?'s':''} activa${d.urgentes>1?'s':''}`;
    }

    document.getElementById('cajaStats').innerHTML = `
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(15,209,134,.1);color:#059669;"><i class="fas fa-dollar-sign"></i></div>
            <div class="sc-val">${fmt(d.ingresos_hoy)}</div>
            <div class="sc-lbl">Ingresos hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(99,102,241,.1);color:#4f46e5;"><i class="fas fa-handshake"></i></div>
            <div class="sc-val">${d.entregados_hoy}</div>
            <div class="sc-lbl">Entregados hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(15,209,134,.1);color:#059669;"><i class="fas fa-check-circle"></i></div>
            <div class="sc-val">${d.listas}</div>
            <div class="sc-lbl">Listas p/ entregar</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(245,158,11,.1);color:#d97706;"><i class="fas fa-wrench"></i></div>
            <div class="sc-val">${d.en_taller}</div>
            <div class="sc-lbl">En taller</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(239,68,68,.1);color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
            <div class="sc-val">${fmt(d.monto_saldo)}</div>
            <div class="sc-lbl">Saldo pendiente</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(239,68,68,.1);color:#dc2626;"><i class="fas fa-fire"></i></div>
            <div class="sc-val">${d.urgentes}</div>
            <div class="sc-lbl">Urgentes activas</div>
        </div>
    `;
}

async function cargarOrdenesHoy() {
    const r = await fetch(`${API_CAJA}?type=ordenes_hoy`, {credentials:'include'});
    const j = await r.json();
    const tb = document.getElementById('cajaOrdenesTbody');
    if (!j.success || !j.data.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty-state">
            <i class="fas fa-check-double"></i>
            <p>No hay órdenes listas ni entregadas hoy</p>
        </div></td></tr>`;
        return;
    }
    tb.innerHTML = j.data.map(o => {
        const saldo  = parseFloat(o.saldo||0);
        const equipo = [tipoLabel(o.equipo_tipo), o.equipo_marca, o.equipo_modelo].filter(Boolean).join(' ');
        const prio   = o.prioridad !== 'normal' ? `<span class="prio-chip prio-${o.prioridad}">${o.prioridad === 'urgente' ? '🔴' : '⭐'}</span>` : '';
        const waBtn  = o.cliente_tel && o.estado === 'listo'
            ? `<button class="btn-sm btn-wa" onclick="waCliente('${esc(o.cliente_tel)}','${esc(o.cliente_nombre)}','${esc(equipo)}')"><i class="fab fa-whatsapp"></i> Avisar</button>`
            : '';
        const printBtn = `<button class="btn-sm btn-print" onclick="imprimirOrden(${o.id})" title="Imprimir orden"><i class="fas fa-print"></i></button>`;
        return `<tr>
            <td style="font-weight:700;color:var(--tec);">#${o.id}${prio}</td>
            <td>
                <div style="font-weight:700;">${esc(o.cliente_nombre)}</div>
                ${o.cliente_tel ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(o.cliente_tel)}</div>` : ''}
            </td>
            <td>
                <div style="font-weight:600;">${esc(equipo||'—')}</div>
                ${o.equipo_serie ? `<div style="font-size:11px;color:var(--text-secondary);">SN: ${esc(o.equipo_serie)}</div>` : ''}
            </td>
            <td style="font-weight:700;">${fmt(o.total)}</td>
            <td>${saldo > 0 ? `<span style="color:#dc2626;font-weight:700;">${fmt(saldo)}</span>` : `<span style="color:#059669;font-weight:600;">✓ Pagado</span>`}</td>
            <td><span class="badge badge-${o.estado}">${estadoLabel(o.estado)}</span></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">${waBtn}${printBtn}</td>
        </tr>`;
    }).join('');
}

async function cargarReportes(dias, btn) {
    document.querySelectorAll('.periodo-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    repCargado = true;

    const [rIng, rTipo, rEst, rRes] = await Promise.all([
        fetch(`${API_CAJA}?type=ingresos_dia&dias=${dias}`, {credentials:'include'}).then(r => r.json()),
        fetch(`${API_CAJA}?type=por_tipo`, {credentials:'include'}).then(r => r.json()),
        fetch(`${API_CAJA}?type=por_estado`, {credentials:'include'}).then(r => r.json()),
        fetch(`${API_CAJA}?type=resumen`, {credentials:'include'}).then(r => r.json()),
    ]);

    const ingresos   = rIng.data || [];
    const totalIng   = ingresos.reduce((s, x) => s + parseFloat(x.total||0), 0);
    const totalEnt   = ingresos.reduce((s, x) => s + parseInt(x.cantidad||0), 0);
    const ticket     = totalEnt ? totalIng / totalEnt : 0;
    const saldo      = rRes.data?.monto_saldo || 0;

    document.getElementById('rk-ingresos').textContent   = fmt(totalIng);
    document.getElementById('rk-entregados').textContent  = totalEnt;
    document.getElementById('rk-ticket').textContent      = fmt(ticket);
    document.getElementById('rk-saldo').textContent       = fmt(saldo);

    // Chart: Ingresos por día
    if (charts.ingresos) charts.ingresos.destroy();
    charts.ingresos = new Chart(document.getElementById('chartIngresos'), {
        type:'bar',
        data:{ labels: ingresos.map(x => { const [y,m,d]=x.fecha.split('-'); return `${d}/${m}`; }),
               datasets:[{ label:'Ingresos', data: ingresos.map(x => parseFloat(x.total||0)),
                   backgroundColor:'rgba(99,102,241,.6)', borderColor:'#6366f1',
                   borderWidth:1.5, borderRadius:4 }]},
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{ y:{beginAtZero:true, ticks:{callback:v=>'$'+Number(v).toLocaleString('es-AR')}} }}
    });

    // Chart: Por tipo de equipo
    const tipos = rTipo.data || [];
    const TIPO_COLORS = {
        celular:'#6366f1', tablet:'#8b5cf6', notebook:'#0ea5e9',
        pc:'#06b6d4', impresora:'#f59e0b', tv:'#f97316', consola:'#ec4899', otro:'#94a3b8'
    };
    const TIPO_ICONS = {celular:'📱',tablet:'📲',notebook:'💻',pc:'🖥️',impresora:'🖨️',tv:'📺',consola:'🎮',otro:'🔧'};
    if (charts.tipo) charts.tipo.destroy();
    charts.tipo = new Chart(document.getElementById('chartTipo'), {
        type:'doughnut',
        data:{
            labels: tipos.map(x => (TIPO_ICONS[x.equipo_tipo]||'')+ ' ' + tipoLabel(x.equipo_tipo).replace(/[^\w\s]/g,'')),
            datasets:[{ data: tipos.map(x => x.cantidad),
                backgroundColor: tipos.map(x => TIPO_COLORS[x.equipo_tipo]||'#94a3b8'), borderWidth:2 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }}
    });

    // Chart: Por estado
    const estados = rEst.data || [];
    const EST_COLORS = {
        listo:'#0FD186', en_reparacion:'#f59e0b', diagnosticando:'#a78bfa',
        esperando_repuesto:'#ef4444', ingresado:'#6366f1', entregado:'#64748b', sin_reparacion:'#94a3b8'
    };
    if (charts.estado) charts.estado.destroy();
    charts.estado = new Chart(document.getElementById('chartEstado'), {
        type:'doughnut',
        data:{
            labels: estados.map(x => estadoLabel(x.estado)),
            datasets:[{ data: estados.map(x => x.cantidad),
                backgroundColor: estados.map(x => EST_COLORS[x.estado]||'#94a3b8'), borderWidth:2 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }}
    });
}

// ── WA al cliente ──────────────────────────────────────────────────────────────
function waCliente(tel, nombre, equipo) {
    const num = tel.replace(/\D/g, '');
    const msg = `Hola ${nombre}! 🔧 Te avisamos que tu *${equipo||'equipo'}* ya está listo para retirar. Podés pasar cuando quieras. ¡Hasta pronto!`;
    window.open(`https://wa.me/${num}?text=${encodeURIComponent(msg)}`, '_blank');
}

// ── Imprimir orden ─────────────────────────────────────────────────────────────
async function imprimirOrden(id) {
    const r = await fetch(`${API_ORD}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error al cargar orden', 'error'); return; }
    const o = j.data;
    const saldo    = parseFloat(o.saldo||0);
    const fechaHoy = new Date().toLocaleDateString('es-AR',{day:'2-digit',month:'2-digit',year:'numeric'});
    const equipo   = [tipoLabel(o.equipo_tipo).replace(/[^\w\s]/g,'').trim(), o.equipo_marca, o.equipo_modelo].filter(Boolean).join(' ');
    const estColors = {listo:'#0FD186',en_reparacion:'#f59e0b',diagnosticando:'#8b5cf6',esperando_repuesto:'#ef4444',ingresado:'#6366f1',entregado:'#64748b',sin_reparacion:'#94a3b8',cancelado:'#ef4444'};

    const w = window.open('','_blank');
    w.document.write(`<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <title>Orden #${o.id}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; color:#1e293b; padding:32px; max-width:580px; margin:0 auto; }
        .header { text-align:center; margin-bottom:24px; padding-bottom:16px; border-bottom:2px solid #6366f1; }
        .header h1 { font-size:22px; font-weight:800; color:#6366f1; }
        .header p  { font-size:12px; color:#64748b; margin-top:4px; }
        .ord-num { display:inline-block; background:#6366f1; color:#fff; font-weight:800; font-size:13px; padding:4px 14px; border-radius:20px; margin-top:8px; }
        h3 { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#64748b; margin:20px 0 8px; padding-bottom:4px; border-bottom:1px solid #e2e8f0; }
        .row { display:flex; justify-content:space-between; padding:6px 0; font-size:13px; }
        .row .label { color:#64748b; }
        .row .value { font-weight:600; max-width:65%; text-align:right; }
        .falla-box { background:#fef2f2; border-left:3px solid #ef4444; border-radius:0 8px 8px 0; padding:10px 14px; font-size:13px; margin-bottom:8px; }
        .diag-box  { background:#f0fdf4; border-left:3px solid #22c55e; border-radius:0 8px 8px 0; padding:10px 14px; font-size:13px; }
        .total-box { background:#f8fafc; border-radius:10px; padding:14px; margin-top:16px; }
        .total-box .row { border-bottom:1px solid #e2e8f0; }
        .total-box .row:last-child { border-bottom:none; font-size:16px; font-weight:800; color:#6366f1; }
        .saldo-box  { background:#fef2f2; border:1.5px solid #ef4444; border-radius:10px; padding:12px 14px; margin-top:10px; text-align:center; }
        .saldo-box p { font-weight:700; color:#dc2626; font-size:15px; }
        .pagado-box { background:#f0fdf4; border:1.5px solid #22c55e; border-radius:10px; padding:10px 14px; margin-top:10px; text-align:center; font-weight:700; color:#166534; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; color:#fff; }
        .firma-box { margin-top:32px; display:flex; justify-content:space-between; }
        .firma-col { text-align:center; width:45%; }
        .firma-linea { border-top:1px solid #94a3b8; padding-top:6px; font-size:11px; color:#64748b; margin-top:40px; }
        .footer { margin-top:20px; text-align:center; font-size:11px; color:#94a3b8; }
        @media print { button { display:none; } }
    </style></head><body>
    <div class="header">
        <h1>🔧 Servicio Técnico</h1>
        <p>Orden de Servicio</p>
        <span class="ord-num">OS #${o.id}</span>
    </div>

    <h3>Datos del cliente</h3>
    <div class="row"><span class="label">Nombre</span><span class="value">${fe(o.cliente_nombre)}</span></div>
    ${o.cliente_tel ? `<div class="row"><span class="label">Teléfono</span><span class="value">${fe(o.cliente_tel)}</span></div>` : ''}
    <div class="row"><span class="label">Fecha ingreso</span><span class="value">${formatFecha(o.fecha_ingreso)}</span></div>
    ${o.fecha_promesa ? `<div class="row"><span class="label">Fecha promesa</span><span class="value">${formatFecha(o.fecha_promesa)}</span></div>` : ''}
    <div class="row"><span class="label">Estado</span><span class="value"><span class="badge" style="background:${estColors[o.estado]||'#94a3b8'}">${estadoLabel(o.estado)}</span></span></div>
    ${o.prioridad !== 'normal' ? `<div class="row"><span class="label">Prioridad</span><span class="value">${o.prioridad === 'urgente' ? '🔴 Urgente' : '⭐ VIP'}</span></div>` : ''}

    <h3>Equipo</h3>
    <div class="row"><span class="label">Tipo</span><span class="value">${tipoLabel(o.equipo_tipo).replace(/[^\w\s]/g,'').trim()}</span></div>
    ${o.equipo_marca  ? `<div class="row"><span class="label">Marca</span><span class="value">${fe(o.equipo_marca)}</span></div>` : ''}
    ${o.equipo_modelo ? `<div class="row"><span class="label">Modelo</span><span class="value">${fe(o.equipo_modelo)}</span></div>` : ''}
    ${o.equipo_serie  ? `<div class="row"><span class="label">N° Serie / IMEI</span><span class="value">${fe(o.equipo_serie)}</span></div>` : ''}
    ${o.equipo_color  ? `<div class="row"><span class="label">Color</span><span class="value">${fe(o.equipo_color)}</span></div>` : ''}
    ${o.accesorios    ? `<div class="row"><span class="label">Accesorios</span><span class="value">${fe(o.accesorios)}</span></div>` : ''}
    ${o.tecnico       ? `<div class="row"><span class="label">Técnico</span><span class="value">${fe(o.tecnico)}</span></div>` : ''}

    <h3>Diagnóstico</h3>
    <p style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px;">Falla reportada:</p>
    <div class="falla-box">${fe(o.falla_reportada)}</div>
    ${o.diagnostico ? `<p style="font-size:11px;font-weight:700;color:#64748b;margin:8px 0 6px;">Diagnóstico técnico:</p><div class="diag-box">${fe(o.diagnostico)}</div>` : ''}

    <h3>Presupuesto</h3>
    <div class="total-box">
        <div class="row"><span class="label">Mano de obra</span><span class="value">${fmt(o.mano_obra)}</span></div>
        <div class="row"><span class="label">Repuestos</span><span class="value">${fmt(o.repuestos_total)}</span></div>
        <div class="row"><span class="label">Total</span><span class="value">${fmt(o.total)}</span></div>
        ${parseFloat(o.seña||0)>0 ? `<div class="row"><span class="label">Seña abonada</span><span class="value">${fmt(o.seña)}</span></div>` : ''}
    </div>
    ${saldo > 0
        ? `<div class="saldo-box"><p>Saldo pendiente: ${fmt(saldo)}</p></div>`
        : `<div class="pagado-box">✓ Pagado en su totalidad</div>`}

    ${o.observaciones ? `<h3>Observaciones</h3><p style="font-size:13px;padding:10px;background:#f8fafc;border-radius:8px;">${fe(o.observaciones)}</p>` : ''}

    <div class="firma-box">
        <div class="firma-col"><div class="firma-linea">Firma del cliente</div></div>
        <div class="firma-col"><div class="firma-linea">Firma del técnico</div></div>
    </div>

    <div class="footer"><p>Servicio Técnico · OS #${o.id} · ${fechaHoy}</p></div>
    <div style="margin-top:20px;text-align:center;">
        <button onclick="window.print()" style="background:#6366f1;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">🖨️ Imprimir</button>
    </div>
    </body></html>`);
    w.document.close();
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoLabel(e) {
    return {ingresado:'Ingresado',diagnosticando:'Diagnosticando',esperando_repuesto:'Sin repuesto',
            en_reparacion:'En reparación',listo:'Listo ✓',entregado:'Entregado',
            sin_reparacion:'Sin reparación',cancelado:'Cancelado'}[e]||e;
}
function tipoLabel(t) {
    return {celular:'📱 Celular',tablet:'📲 Tablet',notebook:'💻 Notebook',pc:'🖥️ PC',
            impresora:'🖨️ Impresora',tv:'📺 TV',consola:'🎮 Consola',otro:'🔧 Otro'}[t]||t||'—';
}
function fmt(n)  { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s)  { return String(s||'').replace(/'/g,"\\'"); }
function fe(s)   { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function formatFecha(f) { if (!f) return ''; const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`; }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.style.background = tipo==='error'?'#ef4444':'#1e293b';
    t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500);
}

init();
</script>
</body>
</html>
