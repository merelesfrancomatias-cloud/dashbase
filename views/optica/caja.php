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
    <title>Caja & Reportes — Óptica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --opt:#0ea5e9; --opt-dark:#0284c7; --opt-light:rgba(14,165,233,.1); }

        .page-header {
            padding:20px 24px 0;
            display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .page-header h1 { margin:0; font-size:22px; font-weight:800; color:var(--text-primary); }
        .page-header p  { margin:0; font-size:13px; color:var(--text-secondary); }

        /* Tabs */
        .tab-bar {
            display:flex; gap:4px; padding:16px 24px 0;
            border-bottom:1px solid var(--border); margin-bottom:20px;
        }
        .tab-btn {
            padding:9px 20px; border-radius:10px 10px 0 0;
            font-size:13px; font-weight:600; cursor:pointer;
            border:none; background:none; color:var(--text-secondary);
            border-bottom:2px solid transparent; transition:all .15s;
        }
        .tab-btn.active { color:var(--opt); border-bottom-color:var(--opt); background:var(--opt-light); }
        .tab-btn:hover:not(.active) { color:var(--text-primary); background:var(--background); }

        /* Stats grid */
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));
            gap:14px; padding:0 24px 20px;
        }
        .stat-card {
            background:var(--surface); border:1.5px solid var(--border);
            border-radius:14px; padding:14px 16px;
            display:flex; flex-direction:column; justify-content:flex-start; min-width:0;
        }
        .stat-card .sc-icon {
            width:36px; height:36px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-size:16px; margin-bottom:10px; flex-shrink:0;
            background:var(--opt-light); color:var(--opt);
        }
        .stat-card .sc-val  { font-size:20px; font-weight:800; color:var(--text-primary); line-height:1.2; word-break:break-word; min-width:0; }
        .stat-card .sc-lbl  { font-size:10px; color:var(--text-secondary); margin-top:4px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; line-height:1.3; }

        /* Tabla pedidos */
        .section-title {
            font-size:13px; font-weight:800; text-transform:uppercase;
            letter-spacing:.6px; color:var(--text-secondary);
            padding:0 24px 10px; display:flex; align-items:center; gap:8px;
        }
        .pedidos-tabla {
            margin:0 24px; border:1.5px solid var(--border);
            border-radius:12px; overflow:hidden;
        }
        .pedidos-tabla table { width:100%; border-collapse:collapse; }
        .pedidos-tabla th {
            padding:10px 14px; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.5px;
            color:var(--text-secondary); background:var(--background);
            border-bottom:1px solid var(--border); text-align:left;
        }
        .pedidos-tabla td {
            padding:11px 14px; font-size:13px; color:var(--text-primary);
            border-bottom:1px solid var(--border);
        }
        .pedidos-tabla tr:last-child td { border-bottom:none; }
        .pedidos-tabla tr:hover td     { background:var(--background); }

        /* Badges */
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-listo      { background:rgba(15,209,134,.15); color:#059669; }
        .badge-entregado  { background:rgba(99,102,241,.15); color:#4f46e5; }
        .badge-laboratorio{ background:var(--opt-light); color:var(--opt-dark); }
        .badge-pendiente  { background:rgba(245,158,11,.15); color:#d97706; }
        .badge-presupuesto{ background:var(--background); color:var(--text-secondary); border:1px solid var(--border); }

        /* Botones inline */
        .btn-sm {
            padding:5px 10px; border-radius:8px; font-size:12px; font-weight:600;
            border:1.5px solid transparent; cursor:pointer; display:inline-flex;
            align-items:center; gap:5px; transition:all .15s;
        }
        .btn-wa  { background:#25d366; color:#fff; border-color:#25d366; }
        .btn-wa:hover  { background:#128c7e; border-color:#128c7e; }
        .btn-print { background:var(--opt-light); color:var(--opt); border-color:var(--opt); }
        .btn-print:hover { background:var(--opt); color:#fff; }

        /* Charts */
        .charts-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));
            gap:16px; padding:0 24px 24px;
        }
        .chart-card {
            background:var(--surface); border:1.5px solid var(--border);
            border-radius:14px; padding:18px;
        }
        .chart-title {
            font-size:13px; font-weight:700; color:var(--text-primary);
            margin-bottom:14px;
        }
        .chart-wrap { position:relative; height:200px; }

        /* Período */
        .periodo-bar {
            padding:0 24px 16px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;
        }
        .periodo-btn {
            padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600;
            border:1.5px solid var(--border); background:var(--surface);
            color:var(--text-secondary); cursor:pointer; transition:all .15s;
        }
        .periodo-btn.active { background:var(--opt); border-color:var(--opt); color:#fff; }

        /* KPIs reportes */
        .rep-kpis {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
            gap:14px; padding:0 24px 20px;
        }
        .rep-kpi {
            background:var(--surface); border:1.5px solid var(--border);
            border-radius:12px; padding:14px 16px;
            display:flex; align-items:center; gap:12px;
        }
        .rep-kpi .rk-icon { font-size:22px; }
        .rep-kpi .rk-val  { font-size:20px; font-weight:800; color:var(--text-primary); line-height:1; }
        .rep-kpi .rk-lbl  { font-size:11px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.4px; }

        .empty-state {
            text-align:center; padding:48px 24px; color:var(--text-secondary);
        }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-cash-register" style="color:var(--opt);margin-right:10px;"></i>Caja & Reportes</h1>
            <p id="subtitulo">Cargando…</p>
        </div>
        <a href="pedidos.php" class="btn btn-secondary" style="font-size:13px;">
            <i class="fas fa-glasses"></i> Ver Pedidos
        </a>
    </div>

    <!-- Tabs -->
    <div class="tab-bar">
        <button class="tab-btn active" onclick="mostrarTab('caja',this)">
            <i class="fas fa-store"></i> Caja del Día
        </button>
        <button class="tab-btn" onclick="mostrarTab('reportes',this)">
            <i class="fas fa-chart-bar"></i> Reportes
        </button>
        <button class="tab-btn" id="tabBtnAlertas" onclick="mostrarTab('alertas',this)">
            <i class="fas fa-bell"></i> Alertas <span id="alertasBadge" style="display:none;background:#ef4444;color:#fff;border-radius:20px;padding:1px 7px;font-size:11px;font-weight:800;margin-left:4px;">0</span>
        </button>
        <button class="tab-btn" onclick="mostrarTab('laboratorio',this)">
            <i class="fas fa-flask"></i> Laboratorio
        </button>
    </div>

    <!-- ══ TAB CAJA ══════════════════════════════════════════════════════════════ -->
    <div id="tabCaja">
        <div class="stats-grid" id="cajaStats">
            <?php for ($i=0;$i<6;$i++): ?>
            <div class="stat-card" style="opacity:.3;"><div class="sc-icon"><i class="fas fa-circle-notch fa-spin"></i></div><div class="sc-val">—</div><div class="sc-lbl">Cargando</div></div>
            <?php endfor; ?>
        </div>

        <div class="section-title">
            <i class="fas fa-list-check"></i> Pedidos listos + entregados hoy
        </div>
        <div class="pedidos-tabla">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Armazón / Lente</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cajaPedidosTbody">
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-secondary);">
                        <i class="fas fa-circle-notch fa-spin"></i> Cargando…
                    </td></tr>
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

        <div class="rep-kpis" id="repKpis">
            <div class="rep-kpi"><span class="rk-icon">💰</span><div><div class="rk-val" id="rk-ingresos">—</div><div class="rk-lbl">Ingresos</div></div></div>
            <div class="rep-kpi"><span class="rk-icon">📦</span><div><div class="rk-val" id="rk-entregados">—</div><div class="rk-lbl">Entregados</div></div></div>
            <div class="rep-kpi"><span class="rk-icon">🧾</span><div><div class="rk-val" id="rk-ticket">—</div><div class="rk-lbl">Ticket promedio</div></div></div>
            <div class="rep-kpi"><span class="rk-icon">⚠️</span><div><div class="rk-val" id="rk-saldo">—</div><div class="rk-lbl">Saldo pendiente</div></div></div>
        </div>

        <div class="charts-grid">
            <div class="chart-card" style="grid-column:1/-1;">
                <div class="chart-title"><i class="fas fa-chart-bar" style="color:var(--opt);margin-right:6px;"></i>Ingresos por día</div>
                <div class="chart-wrap"><canvas id="chartIngresos"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-circle-half-stroke" style="color:var(--opt);margin-right:6px;"></i>Por tipo de lente</div>
                <div class="chart-wrap"><canvas id="chartTipo"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-chart-pie" style="color:var(--opt);margin-right:6px;"></i>Distribución por estado</div>
                <div class="chart-wrap"><canvas id="chartEstado"></canvas></div>
            </div>
        </div>
    </div>
    <!-- ══ TAB ALERTAS ═════════════════════════════════════════════════════════ -->
    <div id="tabAlertas" style="display:none;">
        <div id="alertasContent" style="padding:0 24px 24px;">
            <div style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>

    <!-- ══ TAB LABORATORIO ══════════════════════════════════════════════════════ -->
    <div id="tabLaboratorio" style="display:none;">
        <div style="padding:0 24px 24px;">
            <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <span style="font-size:13px;font-weight:700;color:var(--text-secondary);"><i class="fas fa-flask" style="color:var(--opt);margin-right:6px;"></i>Pedidos en Laboratorio</span>
            </div>
            <div class="pedidos-tabla">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Armazón</th>
                            <th>Laboratorio</th>
                            <th>Envío</th>
                            <th>Entrega Est.</th>
                            <th>Días</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="labTbody">
                        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE       = '<?= $base ?>';
const API_CAJA   = BASE + '/api/optica/caja.php';
const API_PED    = BASE + '/api/optica/pedidos.php';
const API_ALERTA = BASE + '/api/optica/alertas.php';

const charts = {};
let repCargado = false;
let repDias    = 30;

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    const hoy = new Date().toLocaleDateString('es-AR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
    document.getElementById('subtitulo').textContent = hoy.charAt(0).toUpperCase() + hoy.slice(1);
    await Promise.all([cargarResumen(), cargarPedidosHoy()]);
    // Cargar badge de alertas sin bloquear
    fetch(API_ALERTA, {credentials:'include'}).then(r => r.json()).then(j => {
        if (j.success && j.data.total_alertas > 0) {
            const badge = document.getElementById('alertasBadge');
            badge.textContent = j.data.total_alertas;
            badge.style.display = 'inline';
        }
    }).catch(() => {});
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
let labCargado = false;
let alertasCargado = false;

function mostrarTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tabCaja').style.display        = tab === 'caja'        ? '' : 'none';
    document.getElementById('tabReportes').style.display    = tab === 'reportes'    ? '' : 'none';
    document.getElementById('tabAlertas').style.display     = tab === 'alertas'     ? '' : 'none';
    document.getElementById('tabLaboratorio').style.display = tab === 'laboratorio' ? '' : 'none';
    if (tab === 'reportes'   && !repCargado)     cargarReportes(30, document.querySelector('.periodo-btn.active'));
    if (tab === 'alertas'    && !alertasCargado) cargarAlertas();
    if (tab === 'laboratorio'&& !labCargado)     cargarLaboratorio();
}

// ── Resumen ────────────────────────────────────────────────────────────────────
async function cargarResumen() {
    const r = await fetch(`${API_CAJA}?type=resumen`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    const d = j.data;
    document.getElementById('cajaStats').innerHTML = `
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(15,209,134,.1);color:#059669;"><i class="fas fa-dollar-sign"></i></div>
            <div class="sc-val">${fmtCompact(d.ingresos_hoy)}</div>
            <div class="sc-lbl">Ingresos hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(99,102,241,.1);color:#4f46e5;"><i class="fas fa-handshake"></i></div>
            <div class="sc-val">${d.entregados_hoy}</div>
            <div class="sc-lbl">Entregados hoy</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(15,209,134,.1);color:#059669;"><i class="fas fa-check-circle"></i></div>
            <div class="sc-val">${d.listos}</div>
            <div class="sc-lbl">Listos p/ retirar</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(245,158,11,.1);color:#d97706;"><i class="fas fa-flask"></i></div>
            <div class="sc-val">${d.en_laboratorio}</div>
            <div class="sc-lbl">En laboratorio</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon" style="background:rgba(239,68,68,.1);color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
            <div class="sc-val">${fmtCompact(d.monto_saldo)}</div>
            <div class="sc-lbl">Saldo pendiente</div>
        </div>
        <div class="stat-card">
            <div class="sc-icon"><i class="fas fa-user-plus"></i></div>
            <div class="sc-val">${d.clientes_nuevos}</div>
            <div class="sc-lbl">Clientes nuevos</div>
        </div>
    `;
}

// ── Pedidos del día ────────────────────────────────────────────────────────────
async function cargarPedidosHoy() {
    const r = await fetch(`${API_CAJA}?type=pedidos_hoy`, {credentials:'include'});
    const j = await r.json();
    const tb = document.getElementById('cajaPedidosTbody');
    if (!j.success || !j.data.length) {
        tb.innerHTML = `<tr><td colspan="6"><div class="empty-state">
            <i class="fas fa-check-double"></i>
            <p>No hay pedidos listos ni entregados hoy</p>
        </div></td></tr>`;
        return;
    }
    tb.innerHTML = j.data.map(p => {
        const saldo = parseFloat(p.saldo||0);
        const lente = lenteTipoLabel(p.lente_tipo);
        const waBtn = p.cliente_tel && p.estado === 'listo'
            ? `<button class="btn-sm btn-wa" onclick="waCliente('${esc(p.cliente_tel)}','${esc(p.cliente_nombre)}','${esc(p.armazon||'')}')"><i class="fab fa-whatsapp"></i> Avisar</button>`
            : '';
        const printBtn = `<button class="btn-sm btn-print" onclick="imprimirPedido(${p.id})" title="Imprimir folio"><i class="fas fa-print"></i></button>`;
        return `<tr>
            <td>
                <div style="font-weight:700;">${esc(p.cliente_nombre)}</div>
                ${p.obra_social ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(p.obra_social)}</div>` : ''}
            </td>
            <td>
                ${p.armazon ? `<div style="font-weight:600;">${esc(p.armazon)}${p.armazon_color?' — '+esc(p.armazon_color):''}</div>` : ''}
                <div style="font-size:12px;color:var(--text-secondary);">${lente}${p.lente_material?' · '+esc(p.lente_material):''}</div>
            </td>
            <td style="font-weight:700;">${fmt(p.total)}</td>
            <td>
                ${saldo > 0
                    ? `<span style="color:#dc2626;font-weight:700;">${fmt(saldo)}</span>`
                    : `<span style="color:#059669;font-weight:600;">✓ Pagado</span>`}
            </td>
            <td><span class="badge badge-${p.estado}">${estadoLabel(p.estado)}</span></td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;">${waBtn}${printBtn}</td>
        </tr>`;
    }).join('');
}

// ── Reportes ──────────────────────────────────────────────────────────────────
async function cargarReportes(dias, btn) {
    repDias = dias;
    document.querySelectorAll('.periodo-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    repCargado = true;

    const [rIng, rTipo, rEst] = await Promise.all([
        fetch(`${API_CAJA}?type=ingresos_dia&dias=${dias}`, {credentials:'include'}).then(r => r.json()),
        fetch(`${API_CAJA}?type=por_tipo`, {credentials:'include'}).then(r => r.json()),
        fetch(`${API_CAJA}?type=por_estado`, {credentials:'include'}).then(r => r.json()),
    ]);

    // KPIs
    const ingresos = rIng.data || [];
    const totalIng   = ingresos.reduce((s, x) => s + parseFloat(x.total||0), 0);
    const totalEnt   = ingresos.reduce((s, x) => s + parseInt(x.cantidad||0), 0);
    const ticket     = totalEnt ? totalIng / totalEnt : 0;

    const rRes = await fetch(`${API_CAJA}?type=resumen`, {credentials:'include'}).then(r => r.json());
    const saldo = rRes.data?.monto_saldo || 0;

    document.getElementById('rk-ingresos').textContent   = fmt(totalIng);
    document.getElementById('rk-entregados').textContent  = totalEnt;
    document.getElementById('rk-ticket').textContent      = fmt(ticket);
    document.getElementById('rk-saldo').textContent       = fmt(saldo);

    // Chart: Ingresos por día
    const lblsIng = ingresos.map(x => {
        const [y,m,d] = x.fecha.split('-');
        return `${d}/${m}`;
    });
    const valsIng = ingresos.map(x => parseFloat(x.total||0));

    if (charts.ingresos) charts.ingresos.destroy();
    charts.ingresos = new Chart(document.getElementById('chartIngresos'), {
        type:'bar',
        data:{ labels:lblsIng, datasets:[{
            label:'Ingresos', data:valsIng,
            backgroundColor:'rgba(14,165,233,.6)', borderColor:'#0ea5e9',
            borderWidth:1.5, borderRadius:4
        }]},
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{ y:{beginAtZero:true, ticks:{callback:v=>'$'+Number(v).toLocaleString('es-AR')}} }
        }
    });

    // Chart: Por tipo de lente
    const tipos = rTipo.data || [];
    const TIPO_COLORS = {
        monofocal:'#0ea5e9', bifocal:'#6366f1', progresivo:'#f59e0b',
        solar:'#f97316', contacto:'#ec4899', sin_lente:'#94a3b8'
    };
    if (charts.tipo) charts.tipo.destroy();
    charts.tipo = new Chart(document.getElementById('chartTipo'), {
        type:'doughnut',
        data:{
            labels: tipos.map(x => lenteTipoLabel(x.lente_tipo)),
            datasets:[{ data: tipos.map(x => x.cantidad),
                backgroundColor: tipos.map(x => TIPO_COLORS[x.lente_tipo]||'#94a3b8'),
                borderWidth:2
            }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }
        }
    });

    // Chart: Por estado
    const estados = rEst.data || [];
    const EST_COLORS = {
        listo:'#0FD186', laboratorio:'#0ea5e9', pendiente:'#f59e0b',
        presupuesto:'#94a3b8', entregado:'#6366f1'
    };
    if (charts.estado) charts.estado.destroy();
    charts.estado = new Chart(document.getElementById('chartEstado'), {
        type:'doughnut',
        data:{
            labels: estados.map(x => estadoLabel(x.estado)),
            datasets:[{ data: estados.map(x => x.cantidad),
                backgroundColor: estados.map(x => EST_COLORS[x.estado]||'#94a3b8'),
                borderWidth:2
            }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'bottom', labels:{font:{size:11}}} }
        }
    });
}

// ── WA al cliente ──────────────────────────────────────────────────────────────
function waCliente(tel, nombre, armazon) {
    const num = tel.replace(/\D/g, '');
    const armazonTxt = armazon ? `, *${armazon}*` : '';
    const msg = `Hola ${nombre}! 👓 Te avisamos que tu pedido óptico${armazonTxt} ya está listo para retirar. Podés pasar cuando quieras. ¡Hasta pronto!`;
    window.open(`https://wa.me/${num}?text=${encodeURIComponent(msg)}`, '_blank');
}

// ── Imprimir folio ─────────────────────────────────────────────────────────────
async function imprimirPedido(id) {
    const r = await fetch(`${API_PED}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error al cargar pedido', 'error'); return; }
    const p = j.data;
    const saldo = parseFloat(p.saldo||0);
    const fechaHoy = new Date().toLocaleDateString('es-AR',{day:'2-digit',month:'2-digit',year:'numeric'});

    const w = window.open('','_blank');
    w.document.write(`<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <title>Pedido #${p.id}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; color:#1e293b; padding:32px; max-width:580px; margin:0 auto; }
        .header { text-align:center; margin-bottom:24px; padding-bottom:16px; border-bottom:2px solid #0ea5e9; }
        .header h1 { font-size:22px; font-weight:800; color:#0ea5e9; }
        .header p  { font-size:12px; color:#64748b; margin-top:4px; }
        .pedido-num { display:inline-block; background:#0ea5e9; color:#fff; font-weight:800; font-size:13px; padding:4px 12px; border-radius:20px; margin-top:8px; }
        h3 { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#64748b; margin:20px 0 8px; padding-bottom:4px; border-bottom:1px solid #e2e8f0; }
        .row { display:flex; justify-content:space-between; padding:6px 0; font-size:13px; }
        .row .label { color:#64748b; }
        .row .value { font-weight:600; }
        .total-box { background:#f8fafc; border-radius:10px; padding:14px; margin-top:16px; }
        .total-box .row { border-bottom:1px solid #e2e8f0; }
        .total-box .row:last-child { border-bottom:none; font-size:16px; font-weight:800; color:#0ea5e9; }
        .saldo-box { background:#fef2f2; border:1.5px solid #ef4444; border-radius:10px; padding:12px 14px; margin-top:10px; text-align:center; }
        .saldo-box p { font-weight:700; color:#dc2626; font-size:15px; }
        .pagado-box { background:#f0fdf4; border:1.5px solid #22c55e; border-radius:10px; padding:10px 14px; margin-top:10px; text-align:center; font-weight:700; color:#166534; }
        .estado-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; background:#0FD186; color:#fff; }
        .footer { margin-top:28px; text-align:center; font-size:11px; color:#94a3b8; }
        @media print { body { padding:16px; } button { display:none; } }
    </style>
    </head><body>
    <div class="header">
        <h1>🔵 Óptica</h1>
        <p>Folio de Pedido</p>
        <span class="pedido-num">Pedido #${p.id}</span>
    </div>

    <h3>Cliente</h3>
    <div class="row"><span class="label">Nombre</span><span class="value">${esc2(p.cliente_nombre)}</span></div>
    ${p.obra_social ? `<div class="row"><span class="label">Obra social</span><span class="value">${esc2(p.obra_social)}</span></div>` : ''}
    <div class="row"><span class="label">Fecha</span><span class="value">${fechaHoy}</span></div>
    <div class="row"><span class="label">Estado</span><span class="value"><span class="estado-badge" style="background:${estadoColor(p.estado)}">${estadoLabel(p.estado)}</span></span></div>

    ${p.armazon || p.lente_tipo ? `
    <h3>Especificaciones</h3>
    ${p.armazon ? `<div class="row"><span class="label">Armazón</span><span class="value">${esc2(p.armazon)}${p.armazon_color?' — '+esc2(p.armazon_color):''}</span></div>` : ''}
    <div class="row"><span class="label">Tipo de lente</span><span class="value">${lenteTipoLabel(p.lente_tipo)}</span></div>
    ${p.lente_material ? `<div class="row"><span class="label">Material</span><span class="value">${esc2(p.lente_material)}</span></div>` : ''}
    ${p.lente_tratamiento ? `<div class="row"><span class="label">Tratamiento</span><span class="value">${esc2(p.lente_tratamiento)}</span></div>` : ''}
    ` : ''}

    ${p.laboratorio ? `
    <h3>Laboratorio</h3>
    <div class="row"><span class="label">Laboratorio</span><span class="value">${esc2(p.laboratorio)}</span></div>
    ${p.fecha_entrega_est ? `<div class="row"><span class="label">Entrega estimada</span><span class="value">${formatFecha(p.fecha_entrega_est)}</span></div>` : ''}
    ` : ''}

    <h3>Pago</h3>
    <div class="total-box">
        <div class="row"><span class="label">Armazón</span><span class="value">${fmt(p.armazon_precio)}</span></div>
        <div class="row"><span class="label">Lentes</span><span class="value">${fmt(p.lente_precio)}</span></div>
        ${parseFloat(p.descuento||0) > 0 ? `<div class="row"><span class="label">Descuento</span><span class="value" style="color:#ef4444;">- ${fmt(p.descuento)}</span></div>` : ''}
        <div class="row"><span class="label">Total</span><span class="value">${fmt(p.total)}</span></div>
        ${parseFloat(p.seña||0) > 0 ? `<div class="row"><span class="label">Seña</span><span class="value">${fmt(p.seña)}</span></div>` : ''}
    </div>
    ${saldo > 0
        ? `<div class="saldo-box"><p>Saldo pendiente: ${fmt(saldo)}</p></div>`
        : `<div class="pagado-box">✓ Pagado en su totalidad</div>`}

    ${p.observaciones ? `<h3>Observaciones</h3><p style="font-size:13px;padding:10px;background:#f8fafc;border-radius:8px;">${esc2(p.observaciones)}</p>` : ''}

    <div class="footer">
        <p>Gracias por su confianza · Folio generado el ${fechaHoy}</p>
    </div>

    <div style="margin-top:24px;text-align:center;">
        <button onclick="window.print()" style="background:#0ea5e9;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
            🖨️ Imprimir
        </button>
    </div>
    </body></html>`);
    w.document.close();
}

function estadoColor(e) {
    return {listo:'#0FD186',laboratorio:'#0ea5e9',pendiente:'#f59e0b',presupuesto:'#94a3b8',entregado:'#6366f1',cancelado:'#ef4444'}[e]||'#94a3b8';
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoLabel(e) {
    return {presupuesto:'Presupuesto',pendiente:'Pendiente',laboratorio:'Laboratorio',
            listo:'Listo ✓',entregado:'Entregado',cancelado:'Cancelado'}[e]||e;
}
function lenteTipoLabel(t) {
    return {monofocal:'Monofocal',bifocal:'Bifocal',progresivo:'Progresivo',
            solar:'Solar',contacto:'Contacto',sin_lente:'Sin lente'}[t]||t||'—';
}
function fmt(n)  { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function fmtCompact(n) {
    const v = Number(n||0);
    if (v >= 1000000) return '$' + (v/1000000).toLocaleString('es-AR',{minimumFractionDigits:1,maximumFractionDigits:1}) + 'M';
    if (v >= 10000)   return '$' + Math.round(v/1000).toLocaleString('es-AR') + 'k';
    return '$' + v.toLocaleString('es-AR',{minimumFractionDigits:0});
}
function esc(s)  { return String(s||'').replace(/'/g,"\\'"); }
function esc2(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function formatFecha(f) { if (!f) return ''; const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`; }

// ── Alertas ────────────────────────────────────────────────────────────────────
async function cargarAlertas() {
    alertasCargado = true;
    const r = await fetch(API_ALERTA, {credentials:'include'});
    const j = await r.json();
    const cont = document.getElementById('alertasContent');
    if (!j.success) { cont.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar alertas</p></div>`; return; }
    const d = j.data;
    if (d.total_alertas === 0 && d.con_saldo.count === 0) {
        cont.innerHTML = `<div class="empty-state" style="padding:48px;"><i class="fas fa-check-circle" style="color:#0FD186;opacity:1;font-size:48px;"></i><p style="margin-top:12px;font-size:15px;font-weight:600;color:#059669;">Todo en orden — sin alertas pendientes</p></div>`;
        return;
    }
    const secciones = [
        { key:'listos_sin_entregar', titulo:'Listos sin entregar (≥ 3 días)', color:'#0FD186', icon:'fas fa-check-circle',
          cols:['Cliente','Armazón','Teléfono','Días esperando','Total','Saldo'],
          row: p => `<tr>
            <td style="font-weight:700;">${esc2(p.cliente_nombre)}</td>
            <td>${esc2(p.armazon||'—')}</td>
            <td>${p.cliente_tel ? `<a href="https://wa.me/${p.cliente_tel.replace(/\D/g,'')}" target="_blank" style="color:#25d366;"><i class="fab fa-whatsapp"></i> ${esc2(p.cliente_tel)}</a>` : '—'}</td>
            <td><strong style="color:#dc2626;">${p.dias_esperando}d</strong></td>
            <td>${fmt(p.total)}</td>
            <td>${parseFloat(p.saldo||0) > 0 ? `<span style="color:#dc2626;font-weight:700;">${fmt(p.saldo)}</span>` : '<span style="color:#059669;">✓</span>'}</td>
          </tr>`},
        { key:'lab_retrasados', titulo:'Laboratorio vencido (fecha est. superada)', color:'#ef4444', icon:'fas fa-clock',
          cols:['Cliente','Armazón','Laboratorio','Fecha Est.','Días retraso'],
          row: p => `<tr>
            <td style="font-weight:700;">${esc2(p.cliente_nombre)}</td>
            <td>${esc2(p.armazon||'—')}</td>
            <td>${esc2(p.laboratorio||'—')}</td>
            <td>${formatFecha(p.fecha_entrega_est)}</td>
            <td><strong style="color:#dc2626;">${p.dias_retraso}d</strong></td>
          </tr>`},
        { key:'lab_sin_fecha', titulo:'Sin fecha de entrega estimada (> 5 días)', color:'#f59e0b', icon:'fas fa-question-circle',
          cols:['Cliente','Armazón','Laboratorio','Días en lab'],
          row: p => `<tr>
            <td style="font-weight:700;">${esc2(p.cliente_nombre)}</td>
            <td>${esc2(p.armazon||'—')}</td>
            <td>${esc2(p.laboratorio||'—')}</td>
            <td><strong style="color:#d97706;">${p.dias_en_lab}d</strong></td>
          </tr>`},
        { key:'con_saldo', titulo:'Con saldo pendiente', color:'#6366f1', icon:'fas fa-dollar-sign',
          cols:['Cliente','Estado','Total','Saldo'],
          row: p => `<tr>
            <td style="font-weight:700;">${esc2(p.cliente_nombre)}</td>
            <td><span class="badge badge-${p.estado}">${estadoLabel(p.estado)}</span></td>
            <td>${fmt(p.total)}</td>
            <td><span style="color:#dc2626;font-weight:700;">${fmt(p.saldo)}</span></td>
          </tr>`},
    ];
    cont.innerHTML = secciones.map(sec => {
        const cnt = d[sec.key]?.count || 0;
        if (!cnt) return '';
        return `
        <div style="margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <i class="${sec.icon}" style="color:${sec.color};"></i>
                <span style="font-weight:700;font-size:14px;">${sec.titulo}</span>
                <span style="background:${sec.color};color:#fff;border-radius:20px;padding:2px 8px;font-size:12px;font-weight:800;">${cnt}</span>
            </div>
            <div class="pedidos-tabla">
                <table>
                    <thead><tr>${sec.cols.map(c=>`<th>${c}</th>`).join('')}</tr></thead>
                    <tbody>${d[sec.key].items.map(sec.row).join('')}</tbody>
                </table>
            </div>
        </div>`;
    }).join('');
}

// ── Laboratorio ────────────────────────────────────────────────────────────────
async function cargarLaboratorio() {
    labCargado = true;
    const r = await fetch(`${API_PED}?estado=laboratorio`, {credentials:'include'});
    const j = await r.json();
    const tbody = document.getElementById('labTbody');
    if (!j.success || !j.data.pedidos.length) {
        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fas fa-flask"></i><p>No hay pedidos en laboratorio</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = j.data.pedidos.map(p => {
        const hoy = new Date();
        let diasInfo = '—';
        if (p.fecha_entrega_est) {
            const diff = Math.ceil((new Date(p.fecha_entrega_est) - hoy) / 86400000);
            diasInfo = diff < 0
                ? `<strong style="color:#dc2626;">${Math.abs(diff)}d retraso</strong>`
                : `<span style="color:#059669;">${diff}d restantes</span>`;
        } else if (p.created_at) {
            const dias = Math.ceil((hoy - new Date(p.created_at)) / 86400000);
            diasInfo = `<span style="color:#d97706;">${dias}d sin fecha</span>`;
        }
        return `<tr>
            <td style="font-weight:700;">${esc2(p.cliente_nombre)}</td>
            <td>${esc2(p.armazon||'—')}</td>
            <td>${esc2(p.laboratorio||'—')}</td>
            <td>${p.fecha_envio_lab ? formatFecha(p.fecha_envio_lab) : '—'}</td>
            <td>${p.fecha_entrega_est ? formatFecha(p.fecha_entrega_est) : '<span style="color:#d97706;">Sin fecha</span>'}</td>
            <td>${diasInfo}</td>
            <td>
                ${p.cliente_tel ? `<a href="https://wa.me/${p.cliente_tel.replace(/\D/g,'')}" target="_blank" class="btn-sm btn-wa"><i class="fab fa-whatsapp"></i></a>` : ''}
            </td>
        </tr>`;
    }).join('');
}

function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 2500);
}

init();
</script>
</body>
</html>
