<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos / Cuotas — Gimnasio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .mes-nav { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
        .mes-nav button { width:36px;height:36px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;color:var(--text-primary);font-size:14px;transition:var(--transition); }
        .mes-nav button:hover { background:#f97316;color:white;border-color:#f97316; }
        .mes-nav select { padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text-primary);cursor:pointer; }
        .mes-nav select:focus { outline:none;border-color:#f97316; }
        .mes-label { font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap; }

        /* Tabs de estado */
        .tab-bar { display:flex;gap:6px;flex-wrap:wrap; }
        .tab-btn { padding:7px 16px;border-radius:20px;border:1px solid var(--border);background:var(--surface);font-size:13px;font-weight:600;cursor:pointer;color:var(--text-secondary);transition:var(--transition); }
        .tab-btn:hover { border-color:#f97316;color:#f97316; }
        .tab-btn.active { background:#f97316;color:white;border-color:#f97316; }

        /* Tabla */
        table { width:100%;border-collapse:collapse;font-size:14px; }
        th { text-align:left;padding:10px 14px;background:var(--background);color:var(--text-secondary);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border); }
        td { padding:12px 14px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }

        /* Badges de método */
        .metodo-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
        .metodo-efectivo      { background:rgba(22,163,74,.12);color:#16a34a; }
        .metodo-transferencia { background:rgba(59,130,246,.12);color:#3b82f6; }
        .metodo-tarjeta       { background:rgba(139,92,246,.12);color:#8b5cf6; }
        .metodo-debito        { background:rgba(249,115,22,.12);color:#f97316; }

        /* Badge estado socio */
        .estado-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
        .estado-activo   { background:rgba(22,163,74,.12);color:#16a34a; }
        .estado-vencido  { background:rgba(239,68,68,.12);color:#dc2626; }
        .estado-pendiente{ background:rgba(249,115,22,.12);color:#f97316; }

        /* Socio avatar en tabla */
        .socio-avatar { width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#f97316,#fb923c);display:inline-flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;flex-shrink:0; }

        /* Modal */
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface);border-radius:18px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 22px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
        .modal-header h3 { margin:0;font-size:17px;font-weight:700;color:var(--text-primary); }
        .modal-close { background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;padding:4px 8px;border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 22px; }
        .modal-footer { padding:14px 22px 18px;display:flex;gap:10px;justify-content:flex-end; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block;font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px; }
        .form-group input,.form-group select,.form-group textarea { width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);box-sizing:border-box;transition:border-color .15s; }
        .form-group input:focus,.form-group select:focus { outline:none;border-color:#f97316; }
        .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
        .plan-preview { background:var(--background);border-radius:10px;padding:12px;font-size:13px;color:var(--text-secondary);margin-top:6px;display:none; }
        .plan-preview strong { color:var(--text-primary); }
        .btn-cancel { padding:9px 18px;background:var(--background);border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;color:var(--text-primary); }
        .btn-save   { padding:9px 22px;background:#f97316;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s; }
        .btn-save:hover { background:#ea6c0a; }

        .empty-state { text-align:center;padding:50px 24px;color:var(--text-secondary); }
        .empty-state i { font-size:40px;opacity:.2;display:block;margin-bottom:12px; }

        /* Socios vencidos alert */
        .alert-vencidos { background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:12px;margin-bottom:16px;cursor:pointer; }
        .alert-vencidos i { color:#dc2626;font-size:18px;flex-shrink:0; }
        .alert-vencidos-text strong { font-size:14px;color:#dc2626;display:block; }
        .alert-vencidos-text span { font-size:12px;color:var(--text-secondary); }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Page header -->
            <div class="card" style="margin-bottom:20px;padding:20px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-dollar-sign" style="color:#f97316;margin-right:8px;"></i>Pagos / Cuotas
                        </h1>
                        <p style="margin:4px 0 0;color:var(--text-secondary);font-size:14px;" id="subtituloMes">Cargando…</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div class="mes-nav">
                            <button onclick="cambiarMes(-1)" title="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                            <select id="selectMes" onchange="cargarPagos()"></select>
                            <button onclick="cambiarMes(1)" title="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <button class="btn btn-primary" onclick="abrirNuevoPago()">
                            <i class="fas fa-plus"></i> Registrar Pago
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:20px;">
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><div class="stat-value" id="st-total">$0</div><div class="stat-label">Ingresos del mes</div></div></div>
                <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-receipt"></i></div><div class="stat-info"><div class="stat-value" id="st-pagos">0</div><div class="stat-label">Pagos registrados</div></div></div>
                <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-users"></i></div><div class="stat-info"><div class="stat-value" id="st-socios-activos">0</div><div class="stat-label">Socios activos</div></div></div>
                <div class="stat-card"><div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-info"><div class="stat-value" id="st-vencidos">0</div><div class="stat-label">Cuotas vencidas</div></div></div>
            </div>

            <!-- Alerta vencidos -->
            <div class="alert-vencidos" id="alertaVencidos" style="display:none;" onclick="filtrarTab('vencidos')">
                <i class="fas fa-bell"></i>
                <div class="alert-vencidos-text">
                    <strong id="alertaVencidosTexto">Socios con cuota vencida</strong>
                    <span>Hacé clic para ver la lista y registrar sus pagos</span>
                </div>
                <i class="fas fa-chevron-right" style="margin-left:auto;color:#dc2626;"></i>
            </div>

            <!-- Tabla de pagos -->
            <div class="card" id="cardPagos">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div class="tab-bar" id="tabBar">
                        <button class="tab-btn active" data-tab="todos" onclick="filtrarTab('todos')">Todos</button>
                        <button class="tab-btn" data-tab="efectivo" onclick="filtrarTab('efectivo')"><i class="fas fa-money-bill-wave" style="margin-right:4px;"></i>Efectivo</button>
                        <button class="tab-btn" data-tab="transferencia" onclick="filtrarTab('transferencia')"><i class="fas fa-mobile-alt" style="margin-right:4px;"></i>Transferencia</button>
                        <button class="tab-btn" data-tab="tarjeta" onclick="filtrarTab('tarjeta')"><i class="fas fa-credit-card" style="margin-right:4px;"></i>Tarjeta</button>
                        <button class="tab-btn" data-tab="vencidos" onclick="filtrarTab('vencidos')" id="tabVencidos" style="display:none;"><i class="fas fa-exclamation-circle" style="margin-right:4px;color:#dc2626;"></i>Vencidos</button>
                    </div>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:13px;"></i>
                        <input type="text" id="searchInput" placeholder="Buscar socio…" oninput="filtrarBusqueda()" style="padding:8px 12px 8px 32px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text-primary);width:200px;">
                    </div>
                </div>
                <div class="card-body" style="padding:0;" id="tablaPagos">
                    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Cargando…</p></div>
                </div>
            </div>

            <!-- Socios vencidos (oculto, se muestra con filtro) -->
            <div id="seccionVencidos" style="display:none;margin-top:20px;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-circle" style="color:#dc2626;margin-right:6px;"></i>Socios con Cuota Vencida</h3>
                    </div>
                    <div class="card-body" style="padding:0;" id="tablaVencidos"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal nuevo pago -->
<div class="modal-overlay" id="modalPago">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle" style="color:#f97316;margin-right:8px;"></i>Registrar Pago</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Socio <span style="color:#ef4444;">*</span></label>
                <select id="pSocio" onchange="onChangeSocio()">
                    <option value="">— Seleccionar socio —</option>
                </select>
            </div>
            <div id="socioInfo" style="display:none;background:var(--background);border-radius:10px;padding:12px;margin-bottom:14px;font-size:13px;">
                <div style="font-weight:700;color:var(--text-primary);" id="socioInfoNombre"></div>
                <div style="color:var(--text-secondary);margin-top:3px;" id="socioInfoEstado"></div>
            </div>
            <div class="form-group">
                <label>Plan</label>
                <select id="pPlan" onchange="onChangePlan()">
                    <option value="">— Sin plan específico —</option>
                </select>
            </div>
            <div class="plan-preview" id="planPreview"></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Monto <span style="color:#ef4444;">*</span></label>
                    <input type="number" id="pMonto" placeholder="0" min="0" step="100">
                </div>
                <div class="form-group">
                    <label>Método</label>
                    <select id="pMetodo">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="debito">Débito</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Fecha pago</label>
                    <input type="date" id="pFecha">
                </div>
                <div class="form-group">
                    <label>Período desde</label>
                    <input type="date" id="pDesde">
                </div>
            </div>
            <div class="form-group">
                <label>Notas</label>
                <input type="text" id="pNotas" placeholder="Opcional…">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-save" onclick="guardarPago()"><i class="fas fa-save"></i> Guardar Pago</button>
        </div>
    </div>
</div>

<script>
const API_PAGOS  = '../../api/gym/pagos.php';
const API_SOCIOS = '../../api/gym/socios.php';
const API_PLANES = '../../api/gym/planes.php';

let mesActual = '';
let todosPagos = [];
let todosVencidos = [];
let tabActiva = 'todos';
let socios = [];
let planes = [];

// ── Mes ──────────────────────────────────────────────────────────────────────
function initMes() {
    const sel = document.getElementById('selectMes');
    const ahora = new Date();
    let opts = '';
    for (let i = -6; i <= 3; i++) {
        const d = new Date(ahora.getFullYear(), ahora.getMonth() + i, 1);
        const val = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0');
        const label = d.toLocaleDateString('es-AR', {month:'long', year:'numeric'});
        const cap = label.charAt(0).toUpperCase() + label.slice(1);
        opts += `<option value="${val}" ${i===0?'selected':''}>${cap}</option>`;
    }
    sel.innerHTML = opts;
    mesActual = sel.value;
}

function cambiarMes(d) {
    const sel = document.getElementById('selectMes');
    const idx = sel.selectedIndex + d;
    if (idx >= 0 && idx < sel.options.length) {
        sel.selectedIndex = idx;
        mesActual = sel.value;
        cargarPagos();
    }
}

// ── Cargar pagos del mes ──────────────────────────────────────────────────────
async function cargarPagos() {
    mesActual = document.getElementById('selectMes').value;
    const [yr, mn] = mesActual.split('-');
    const dt = new Date(parseInt(yr), parseInt(mn)-1, 1);
    const label = dt.toLocaleDateString('es-AR', {month:'long', year:'numeric'});
    document.getElementById('subtituloMes').textContent = label.charAt(0).toUpperCase() + label.slice(1);

    const r = await fetch(`${API_PAGOS}?mes=${mesActual}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    todosPagos = j.data.pagos || [];
    const totalMes = j.data.total_mes || 0;

    document.getElementById('st-total').textContent = fmt(totalMes);
    document.getElementById('st-pagos').textContent = todosPagos.length;

    // Stats socios activos y vencidos
    await cargarStatsVencidos();
    aplicarFiltro();
}

async function cargarStatsVencidos() {
    const r = await fetch(`${API_SOCIOS}`, {credentials:'include'});
    const j = await r.json();
    const todos = j.success ? (j.data || []) : [];
    const activos = todos.filter(s => s.estado === 'activo');
    document.getElementById('st-socios-activos').textContent = activos.length;

    const hoy = new Date(); hoy.setHours(0,0,0,0);
    todosVencidos = todos.filter(s => {
        if (!s.fecha_vencimiento) return false;
        return new Date(s.fecha_vencimiento + 'T00:00:00') < hoy;
    });
    document.getElementById('st-vencidos').textContent = todosVencidos.length;

    const alerta = document.getElementById('alertaVencidos');
    const tabV   = document.getElementById('tabVencidos');
    if (todosVencidos.length > 0) {
        alerta.style.display = 'flex';
        tabV.style.display   = 'inline-flex';
        document.getElementById('alertaVencidosTexto').textContent =
            `${todosVencidos.length} socio${todosVencidos.length>1?'s':''} con cuota vencida`;
    } else {
        alerta.style.display = 'none';
        tabV.style.display   = 'none';
    }
}

// ── Filtros ───────────────────────────────────────────────────────────────────
function filtrarTab(tab) {
    tabActiva = tab;
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.tab === tab);
    });
    const esVencidos = tab === 'vencidos';
    document.getElementById('seccionVencidos').style.display = esVencidos ? 'block' : 'none';
    document.getElementById('cardPagos').style.display = esVencidos ? 'none' : 'block';
    if (esVencidos) { renderVencidos(); return; }
    aplicarFiltro();
}

function filtrarBusqueda() { aplicarFiltro(); }

function aplicarFiltro() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    let lista = todosPagos;
    if (tabActiva !== 'todos') lista = lista.filter(p => p.metodo === tabActiva);
    if (q) lista = lista.filter(p => (p.socio_nombre||'').toLowerCase().includes(q));
    renderTabla(lista);
}

// ── Render tabla pagos ────────────────────────────────────────────────────────
function renderTabla(lista) {
    const div = document.getElementById('tablaPagos');
    if (!lista.length) {
        div.innerHTML = '<div class="empty-state"><i class="fas fa-receipt"></i><p>Sin pagos registrados este período</p></div>';
        return;
    }
    div.innerHTML = `
    <div style="overflow-x:auto;">
    <table>
        <thead><tr>
            <th>Socio</th><th>Plan</th><th>Monto</th><th>Método</th><th>Fecha</th><th>Período</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        ${lista.map(p => {
            const ini = (p.socio_nombre||'??').split(' ').slice(0,2).map(w=>w[0]||'').join('').toUpperCase();
            return `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="socio-avatar">${esc(ini)}</div>
                        <div>
                            <div style="font-weight:600;">${esc(p.socio_nombre||'—')}</div>
                            ${p.notas ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(p.notas)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td style="color:var(--text-secondary);font-size:13px;">${esc(p.plan_nombre||'—')}</td>
                <td style="font-weight:700;color:#f97316;font-size:15px;">${fmt(p.monto)}</td>
                <td><span class="metodo-badge metodo-${p.metodo}">${p.metodo}</span></td>
                <td style="color:var(--text-secondary);font-size:13px;">${fmtFecha(p.fecha)}</td>
                <td style="font-size:12px;color:var(--text-secondary);">
                    ${p.periodo_desde ? fmtFecha(p.periodo_desde) + (p.periodo_hasta ? '<br>→ ' + fmtFecha(p.periodo_hasta) : '') : '—'}
                </td>
                <td>
                    <button onclick="pagarSocio(${p.socio_id})" title="Nuevo pago" style="background:rgba(249,115,22,.1);color:#f97316;border:none;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:12px;font-weight:600;">
                        <i class="fas fa-plus"></i> Pago
                    </button>
                </td>
            </tr>`;
        }).join('')}
        </tbody>
        <tfoot>
            <tr style="background:var(--background);">
                <td colspan="2" style="padding:12px 14px;font-weight:700;">Total</td>
                <td style="font-weight:700;color:#f97316;padding:12px 14px;">${fmt(lista.reduce((s,p)=>s+parseFloat(p.monto||0),0))}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
    </div>`;
}

// ── Render tabla vencidos ─────────────────────────────────────────────────────
function renderVencidos() {
    const div = document.getElementById('tablaVencidos');
    if (!todosVencidos.length) {
        div.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle" style="color:#16a34a;"></i><p>¡Todos los socios al día!</p></div>';
        return;
    }
    div.innerHTML = `
    <div style="overflow-x:auto;">
    <table>
        <thead><tr><th>Socio</th><th>Plan</th><th>Venció</th><th>Días vencido</th><th>Acción</th></tr></thead>
        <tbody>
        ${todosVencidos.map(s => {
            const hoy    = new Date(); hoy.setHours(0,0,0,0);
            const venc   = new Date(s.fecha_vencimiento);
            const dias   = Math.ceil((hoy - venc) / (1000*60*60*24));
            const ini    = `${(s.nombre||'?')[0]}${(s.apellido||'?')[0]}`.toUpperCase();
            return `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="socio-avatar" style="background:linear-gradient(135deg,#ef4444,#f87171);">${esc(ini)}</div>
                        <div>
                            <div style="font-weight:600;">${esc(s.nombre)} ${esc(s.apellido)}</div>
                            <div style="font-size:11px;color:var(--text-secondary);">${esc(s.telefono||'')}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:13px;color:var(--text-secondary);">${esc(s.plan_nombre||'—')}</td>
                <td style="font-size:13px;color:#dc2626;font-weight:600;">${fmtFecha(s.fecha_vencimiento)}</td>
                <td><span style="background:rgba(239,68,68,.12);color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">${dias} día${dias!==1?'s':''}</span></td>
                <td>
                    <button onclick="pagarSocio(${s.id})" style="background:#f97316;color:white;border:none;border-radius:8px;padding:7px 14px;cursor:pointer;font-size:12px;font-weight:700;">
                        <i class="fas fa-dollar-sign"></i> Cobrar cuota
                    </button>
                </td>
            </tr>`;
        }).join('')}
        </tbody>
    </table>
    </div>`;
}

// ── Modal ─────────────────────────────────────────────────────────────────────
async function cargarSociosYPlanes() {
    const [rs, rp] = await Promise.all([
        fetch(API_SOCIOS, {credentials:'include'}),
        fetch(API_PLANES, {credentials:'include'})
    ]);
    const js = await rs.json();
    const jp = await rp.json();
    socios = js.success ? (js.data || []) : [];
    planes = jp.success ? (jp.data || []) : [];

    const selS = document.getElementById('pSocio');
    selS.innerHTML = '<option value="">— Seleccionar socio —</option>' +
        socios.map(s => `<option value="${s.id}">${esc(s.nombre)} ${esc(s.apellido)}${s.telefono?' · '+s.telefono:''}</option>`).join('');

    const selP = document.getElementById('pPlan');
    selP.innerHTML = '<option value="">— Sin plan específico —</option>' +
        planes.map(p => `<option value="${p.id}" data-precio="${p.precio}" data-dias="${p.duracion_dias}">${esc(p.nombre)} — $${Number(p.precio).toLocaleString('es-AR')} (${p.duracion_dias} días)</option>`).join('');
}

function abrirNuevoPago(socioId = null) {
    document.getElementById('pSocio').value  = socioId || '';
    document.getElementById('pPlan').value   = '';
    document.getElementById('pMonto').value  = '';
    document.getElementById('pMetodo').value = 'efectivo';
    document.getElementById('pFecha').value  = hoy();
    document.getElementById('pDesde').value  = hoy();
    document.getElementById('pNotas').value  = '';
    document.getElementById('planPreview').style.display = 'none';
    document.getElementById('socioInfo').style.display = 'none';
    if (socioId) { onChangeSocio(); }
    document.getElementById('modalPago').classList.add('open');
}

function pagarSocio(socioId) {
    abrirNuevoPago(socioId);
}

function onChangeSocio() {
    const id = parseInt(document.getElementById('pSocio').value) || 0;
    const socio = socios.find(s => s.id === id);
    const infoDiv = document.getElementById('socioInfo');
    if (socio) {
        document.getElementById('socioInfoNombre').textContent = `${socio.nombre} ${socio.apellido}`;
        const venc = socio.fecha_vencimiento ? new Date(socio.fecha_vencimiento) : null;
        const hoyD = new Date(); hoyD.setHours(0,0,0,0);
        let estadoTxt = '';
        if (!venc) { estadoTxt = 'Sin vencimiento registrado'; }
        else if (venc < hoyD) { estadoTxt = `⚠️ Vencido el ${fmtFecha(socio.fecha_vencimiento)}`; }
        else { estadoTxt = `✅ Vence el ${fmtFecha(socio.fecha_vencimiento)}`; }
        document.getElementById('socioInfoEstado').textContent = estadoTxt;
        // Pre-seleccionar plan actual del socio
        if (socio.plan_id) {
            document.getElementById('pPlan').value = socio.plan_id;
            onChangePlan();
        }
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
}

function onChangePlan() {
    const sel = document.getElementById('pPlan');
    const opt = sel.options[sel.selectedIndex];
    const prev = document.getElementById('planPreview');
    if (opt.value) {
        const precio = parseFloat(opt.dataset.precio) || 0;
        const dias   = parseInt(opt.dataset.dias) || 30;
        document.getElementById('pMonto').value = precio;
        const desde = document.getElementById('pDesde').value || hoy();
        const hasta = new Date(new Date(desde).getTime() + dias * 86400000);
        const hastaStr = hasta.toISOString().split('T')[0];
        prev.innerHTML = `<strong>Duración:</strong> ${dias} días &nbsp;|&nbsp; <strong>Vence:</strong> ${fmtFecha(hastaStr)}`;
        prev.style.display = 'block';
    } else {
        prev.style.display = 'none';
    }
}

async function guardarPago() {
    const socio_id = parseInt(document.getElementById('pSocio').value) || 0;
    const monto    = parseFloat(document.getElementById('pMonto').value) || 0;
    if (!socio_id) { alert('Seleccioná un socio'); return; }
    if (monto <= 0) { alert('Ingresá un monto válido'); return; }

    const plan_id = parseInt(document.getElementById('pPlan').value) || null;
    const body = {
        socio_id,
        plan_id,
        monto,
        metodo:         document.getElementById('pMetodo').value,
        fecha:          document.getElementById('pFecha').value,
        periodo_desde:  document.getElementById('pDesde').value,
        notas:          document.getElementById('pNotas').value,
    };
    const r = await fetch(API_PAGOS, {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) { cerrarModal(); cargarPagos(); }
    else alert(j.message || 'Error al guardar');
}

function cerrarModal() {
    document.getElementById('modalPago').classList.remove('open');
}
document.getElementById('modalPago').addEventListener('click', e => {
    if (e.target === document.getElementById('modalPago')) cerrarModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(n)     { return '$' + Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function hoy()      { return new Date().toISOString().split('T')[0]; }
function fmtFecha(f){ if (!f) return '—'; const p=(f.split(' ')[0]).split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s)     { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ── Init ──────────────────────────────────────────────────────────────────────
initMes();
cargarSociosYPlanes();
cargarPagos();
</script>
</body>
</html>
