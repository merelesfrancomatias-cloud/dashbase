<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/');
$filtroProvId   = (int)($_GET['proveedor_id'] ?? 0);
$filtroProvNombre = htmlspecialchars($_GET['proveedor'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Compra — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --ferr: #f59e0b; }
        .app-layout { display:flex; min-height:100vh; }

        /* Tabla */
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th { text-align:left; padding:10px 14px; background:var(--background); color:var(--text-secondary); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); }
        td { padding:12px 14px; border-bottom:1px solid var(--border); color:var(--text-primary); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }

        /* Estado badges */
        .est-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .est-borrador  { background:rgba(100,116,139,.12); color:#64748b; }
        .est-enviada   { background:rgba(59,130,246,.12);  color:#3b82f6; }
        .est-recibida  { background:rgba(22,163,74,.12);   color:#16a34a; }
        .est-cancelada { background:rgba(239,68,68,.12);   color:#ef4444; }

        /* Tabs */
        .tab-bar { display:flex; gap:6px; flex-wrap:wrap; }
        .tab-btn { padding:7px 16px; border-radius:20px; border:1px solid var(--border); background:var(--surface); font-size:13px; font-weight:600; cursor:pointer; color:var(--text-secondary); transition:all .15s; }
        .tab-btn:hover { border-color:var(--ferr); color:var(--ferr); }
        .tab-btn.active { background:var(--ferr); color:white; border-color:var(--ferr); }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:18px; width:100%; max-width:680px; max-height:93vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 22px; }
        .modal-footer { padding:14px 22px 18px; display:flex; gap:10px; justify-content:flex-end; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:9px 12px; border:1px solid var(--border);
            border-radius:10px; font-size:14px; background:var(--surface);
            color:var(--text-primary); box-sizing:border-box;
        }
        .form-group input:focus, .form-group select:focus { outline:none; border-color:var(--ferr); }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .btn-cancel { padding:9px 18px; background:var(--background); border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; color:var(--text-primary); }
        .btn-save   { padding:9px 22px; background:var(--ferr); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-save:hover { background:#d97706; }

        /* Items de orden en modal */
        .items-table { width:100%; border-collapse:collapse; font-size:13px; margin-top:8px; }
        .items-table th { background:var(--background); padding:8px 10px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-secondary); border-bottom:1px solid var(--border); }
        .items-table td { padding:8px 10px; border-bottom:1px solid var(--border); }
        .items-table tr:last-child td { border-bottom:none; }
        .btn-add-item { background:rgba(245,158,11,.1); color:var(--ferr); border:1px dashed var(--ferr); border-radius:8px; padding:7px 14px; cursor:pointer; font-size:13px; font-weight:600; width:100%; margin-top:8px; }
        .btn-add-item:hover { background:rgba(245,158,11,.2); }
        .btn-remove-item { background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; padding:4px 8px; border-radius:6px; }
        .btn-remove-item:hover { background:rgba(239,68,68,.1); }
        .item-input { padding:6px 8px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); color:var(--text-primary); }
        .item-input:focus { outline:none; border-color:var(--ferr); }

        /* Modal detalle */
        .detalle-seccion { margin-bottom:16px; }
        .detalle-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); margin-bottom:6px; }
        .detalle-valor { font-size:14px; color:var(--text-primary); }

        .empty-state { text-align:center; padding:50px 24px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; }
        .toast.show { opacity:1; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Header -->
            <div class="card" style="margin-bottom:20px;padding:20px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <a href="proveedores.php" style="background:var(--background);border:none;border-radius:8px;padding:8px 12px;cursor:pointer;color:var(--text-secondary);text-decoration:none;font-size:14px;">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                                <i class="fas fa-clipboard-list" style="color:var(--ferr);margin-right:8px;"></i>Órdenes de Compra
                            </h1>
                            <p style="margin:4px 0 0;color:var(--text-secondary);font-size:14px;" id="subtitle">
                                <?= $filtroProvNombre ? "Filtrando por: <strong style='color:var(--ferr);'>$filtroProvNombre</strong>" : 'Todas las órdenes' ?>
                            </p>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="abrirNuevaOrden()">
                        <i class="fas fa-plus"></i> Nueva Orden
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:20px;">
                <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-file-alt"></i></div><div class="stat-info"><div class="stat-value" id="st-total">0</div><div class="stat-label">Total órdenes</div></div></div>
                <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-paper-plane"></i></div><div class="stat-info"><div class="stat-value" id="st-enviadas">0</div><div class="stat-label">Enviadas</div></div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-info"><div class="stat-value" id="st-recibidas">0</div><div class="stat-label">Recibidas</div></div></div>
                <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><div class="stat-value" id="st-monto">$0</div><div class="stat-label">Monto total</div></div></div>
            </div>

            <!-- Tabla -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div class="tab-bar" id="tabBar">
                        <button class="tab-btn active" data-tab="todas"     onclick="filtrarTab('todas')">Todas</button>
                        <button class="tab-btn" data-tab="borrador"  onclick="filtrarTab('borrador')">Borrador</button>
                        <button class="tab-btn" data-tab="enviada"   onclick="filtrarTab('enviada')">Enviadas</button>
                        <button class="tab-btn" data-tab="recibida"  onclick="filtrarTab('recibida')">Recibidas</button>
                        <button class="tab-btn" data-tab="cancelada" onclick="filtrarTab('cancelada')">Canceladas</button>
                    </div>
                </div>
                <div class="card-body" style="padding:0;" id="tablaOrdenes">
                    <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Cargando…</p></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal nueva/editar orden -->
<div class="modal-overlay" id="modalOrden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-plus-circle" style="color:var(--ferr);margin-right:8px;"></i>Nueva Orden de Compra</h3>
            <button class="modal-close" onclick="cerrarModal('modalOrden')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Proveedor <span style="color:#ef4444;">*</span></label>
                    <select id="oProveedor"></select>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" id="oFecha">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Entrega esperada</label>
                    <input type="date" id="oEntrega">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="oEstado">
                        <option value="borrador">Borrador</option>
                        <option value="enviada">Enviada</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notas</label>
                <input type="text" id="oNotas" placeholder="Observaciones…">
            </div>
            <!-- Ítems -->
            <div style="margin-top:4px;">
                <label style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:8px;display:block;">
                    Ítems de la orden <span style="color:#ef4444;">*</span>
                </label>
                <table class="items-table">
                    <thead><tr>
                        <th style="width:40%;">Descripción</th>
                        <th style="width:15%;">Cant.</th>
                        <th style="width:20%;">Precio unit.</th>
                        <th style="width:15%;">Subtotal</th>
                        <th style="width:10%;"></th>
                    </tr></thead>
                    <tbody id="itemsBody"></tbody>
                </table>
                <button class="btn-add-item" onclick="agregarItem()"><i class="fas fa-plus"></i> Agregar ítem</button>
                <div style="text-align:right;margin-top:12px;font-size:15px;font-weight:700;color:var(--ferr);">
                    Total: <span id="totalOrden">$0</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal('modalOrden')">Cancelar</button>
            <button class="btn-save" onclick="guardarOrden()"><i class="fas fa-save"></i> Guardar Orden</button>
        </div>
    </div>
</div>

<!-- Modal detalle orden -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box" style="max-width:600px;">
        <div class="modal-header">
            <h3 id="detalleTitulo">Orden de Compra</h3>
            <button class="modal-close" onclick="cerrarModal('modalDetalle')">✕</button>
        </div>
        <div class="modal-body" id="detalleBody"></div>
        <div class="modal-footer" id="detalleFooter"></div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE         = '<?= $base ?>';
const API_ORDENES  = BASE + '/api/ordenes/index.php';
const API_PROVS    = BASE + '/api/proveedores/index.php';
const API_PRODS    = BASE + '/api/productos/index.php';

const FILTRO_PROV  = <?= $filtroProvId ?>;

let ordenes    = [];
let proveedores= [];
let productos  = [];
let tabActiva  = 'todas';
let itemCounter= 0;

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    const [ro, rp, rpr] = await Promise.all([
        fetch(API_ORDENES + (FILTRO_PROV ? `?proveedor_id=${FILTRO_PROV}` : ''), {credentials:'include'}),
        fetch(API_PROVS, {credentials:'include'}),
        fetch(API_PRODS, {credentials:'include'}),
    ]);
    const jo  = await ro.json();
    const jp  = await rp.json();
    const jpr = await rpr.json();
    ordenes     = jo.success  ? (jo.data  || []) : [];
    proveedores = jp.success  ? (jp.data  || []) : [];
    productos   = jpr.success ? (jpr.data || []) : [];
    actualizarStats();
    renderTabla(ordenesFiltradas());
    // Poblar select proveedores en modal
    const sel = document.getElementById('oProveedor');
    sel.innerHTML = '<option value="">— Seleccionar proveedor —</option>' +
        proveedores.map(p => `<option value="${p.id}" ${p.id===FILTRO_PROV?'selected':''}>${esc(p.nombre)}</option>`).join('');
}

function actualizarStats() {
    document.getElementById('st-total').textContent    = ordenes.length;
    document.getElementById('st-enviadas').textContent = ordenes.filter(o=>o.estado==='enviada').length;
    document.getElementById('st-recibidas').textContent= ordenes.filter(o=>o.estado==='recibida').length;
    document.getElementById('st-monto').textContent    = fmt(ordenes.filter(o=>o.estado!=='cancelada').reduce((s,o)=>s+parseFloat(o.total||0),0));
}

function ordenesFiltradas() {
    if (tabActiva === 'todas') return ordenes;
    return ordenes.filter(o => o.estado === tabActiva);
}

function filtrarTab(tab) {
    tabActiva = tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    renderTabla(ordenesFiltradas());
}

function renderTabla(lista) {
    const div = document.getElementById('tablaOrdenes');
    if (!lista.length) {
        div.innerHTML = '<div class="empty-state"><i class="fas fa-clipboard-list"></i><p>Sin órdenes en esta categoría</p></div>';
        return;
    }
    div.innerHTML = `<div style="overflow-x:auto;">
    <table>
        <thead><tr>
            <th>Número</th><th>Proveedor</th><th>Fecha</th><th>Entrega</th><th>Estado</th><th>Ítems</th><th>Total</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        ${lista.map(o => `
        <tr>
            <td style="font-weight:700;color:var(--ferr);">${esc(o.numero)}</td>
            <td>${esc(o.proveedor_nombre||'—')}</td>
            <td style="font-size:13px;color:var(--text-secondary);">${fmtFecha(o.fecha)}</td>
            <td style="font-size:13px;color:var(--text-secondary);">${o.fecha_entrega_esperada ? fmtFecha(o.fecha_entrega_esperada) : '—'}</td>
            <td><span class="est-badge est-${o.estado}">${labelEstado(o.estado)}</span></td>
            <td style="text-align:center;">${o.total_items||0}</td>
            <td style="font-weight:700;">${fmt(o.total)}</td>
            <td>
                <div style="display:flex;gap:6px;">
                    <button onclick="verDetalle(${o.id})" style="padding:5px 10px;background:var(--background);border:1px solid var(--border);border-radius:7px;cursor:pointer;font-size:12px;color:var(--text-secondary);" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${o.estado === 'borrador' || o.estado === 'enviada' ? `
                    <button onclick="cambiarEstado(${o.id},'${o.estado}')" style="padding:5px 10px;background:rgba(245,158,11,.1);border:none;border-radius:7px;cursor:pointer;font-size:12px;color:var(--ferr);font-weight:600;" title="Avanzar estado">
                        <i class="fas fa-arrow-right"></i>
                    </button>` : ''}
                </div>
            </td>
        </tr>`).join('')}
        </tbody>
    </table></div>`;
}

// ── Detalle ───────────────────────────────────────────────────────────────────
async function verDetalle(id) {
    const r = await fetch(`${API_ORDENES}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error cargando orden', 'error'); return; }
    const o = j.data;
    document.getElementById('detalleTitulo').innerHTML =
        `<i class="fas fa-clipboard-list" style="color:var(--ferr);margin-right:8px;"></i>${esc(o.numero)}`;

    const body = document.getElementById('detalleBody');
    body.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
        <div class="detalle-seccion"><div class="detalle-label">Proveedor</div><div class="detalle-valor">${esc(o.proveedor_nombre||'—')}</div></div>
        <div class="detalle-seccion"><div class="detalle-label">Fecha</div><div class="detalle-valor">${fmtFecha(o.fecha)}</div></div>
        <div class="detalle-seccion"><div class="detalle-label">Estado</div><div class="detalle-valor"><span class="est-badge est-${o.estado}">${labelEstado(o.estado)}</span></div></div>
    </div>
    ${o.notas ? `<div class="detalle-seccion"><div class="detalle-label">Notas</div><div class="detalle-valor">${esc(o.notas)}</div></div>` : ''}
    <table class="items-table">
        <thead><tr><th>Descripción</th><th style="text-align:right;">Cant.</th><th style="text-align:right;">Precio unit.</th><th style="text-align:right;">Subtotal</th></tr></thead>
        <tbody>
        ${(o.items||[]).map(i => `
        <tr>
            <td>${esc(i.descripcion||i.producto_nombre||'—')}</td>
            <td style="text-align:right;">${i.cantidad}</td>
            <td style="text-align:right;">${fmt(i.precio_unitario)}</td>
            <td style="text-align:right;font-weight:600;">${fmt(i.subtotal)}</td>
        </tr>`).join('')}
        </tbody>
        <tfoot>
            <tr style="background:var(--background);">
                <td colspan="3" style="padding:10px;font-weight:700;text-align:right;">Total</td>
                <td style="padding:10px;font-weight:800;color:var(--ferr);text-align:right;">${fmt(o.total)}</td>
            </tr>
        </tfoot>
    </table>`;

    const footer = document.getElementById('detalleFooter');
    let btns = `<button class="btn-cancel" onclick="cerrarModal('modalDetalle')">Cerrar</button>`;
    if (o.estado === 'borrador') {
        btns += `<button class="btn-save" onclick="cambiarEstado(${o.id},'borrador');cerrarModal('modalDetalle');">
            <i class="fas fa-paper-plane"></i> Marcar como Enviada
        </button>`;
    } else if (o.estado === 'enviada') {
        btns += `<button class="btn-save" onclick="cambiarEstado(${o.id},'enviada');cerrarModal('modalDetalle');" style="background:#16a34a;">
            <i class="fas fa-check"></i> Marcar como Recibida (actualiza stock)
        </button>`;
    }
    footer.innerHTML = btns;
    document.getElementById('modalDetalle').classList.add('open');
}

async function cambiarEstado(id, estadoActual) {
    const siguiente = estadoActual === 'borrador' ? 'enviada' : 'recibida';
    const msgs = { enviada:'Orden marcada como enviada ✓', recibida:'Orden recibida — stock actualizado ✓' };
    const r = await fetch(API_ORDENES, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id, estado: siguiente})
    });
    const j = await r.json();
    if (j.success) { toast(msgs[siguiente]); await init(); renderTabla(ordenesFiltradas()); }
    else toast(j.message || 'Error', 'error');
}

// ── Nueva orden ───────────────────────────────────────────────────────────────
function abrirNuevaOrden() {
    itemCounter = 0;
    document.getElementById('oFecha').value   = hoy();
    document.getElementById('oEntrega').value = '';
    document.getElementById('oNotas').value   = '';
    document.getElementById('oEstado').value  = 'borrador';
    document.getElementById('itemsBody').innerHTML = '';
    document.getElementById('totalOrden').textContent = '$0';
    if (FILTRO_PROV) document.getElementById('oProveedor').value = FILTRO_PROV;
    agregarItem();
    document.getElementById('modalOrden').classList.add('open');
}

function agregarItem() {
    const idx = itemCounter++;
    const prodOpts = productos.map(p => `<option value="${p.id}" data-precio="${p.precio_costo||0}">${esc(p.nombre)}</option>`).join('');
    const tr = document.createElement('tr');
    tr.id = `item-${idx}`;
    tr.innerHTML = `
        <td>
            <select class="item-input" style="width:100%;" onchange="autocompletarItemProd(${idx},this)">
                <option value="">Descripción libre…</option>
                ${prodOpts}
            </select>
            <input type="text" class="item-input" style="width:100%;margin-top:4px;" placeholder="Descripción" id="desc-${idx}">
        </td>
        <td><input type="number" class="item-input" style="width:70px;" id="cant-${idx}" value="1" min="0.001" step="1" oninput="recalcItem(${idx})"></td>
        <td><input type="number" class="item-input" style="width:90px;" id="precio-${idx}" value="0" min="0" step="100" oninput="recalcItem(${idx})"></td>
        <td style="font-weight:600;" id="sub-${idx}">$0</td>
        <td><button class="btn-remove-item" onclick="quitarItem(${idx})"><i class="fas fa-times"></i></button></td>`;
    document.getElementById('itemsBody').appendChild(tr);
}

function autocompletarItemProd(idx, sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.getElementById(`desc-${idx}`).value  = opt.text;
        document.getElementById(`precio-${idx}`).value = opt.dataset.precio || 0;
        recalcItem(idx);
    }
}

function recalcItem(idx) {
    const cant   = parseFloat(document.getElementById(`cant-${idx}`)?.value)   || 0;
    const precio = parseFloat(document.getElementById(`precio-${idx}`)?.value) || 0;
    const sub    = cant * precio;
    const el = document.getElementById(`sub-${idx}`);
    if (el) el.textContent = fmt(sub);
    recalcTotal();
}

function recalcTotal() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const idx = tr.id.split('-')[1];
        const cant   = parseFloat(document.getElementById(`cant-${idx}`)?.value)   || 0;
        const precio = parseFloat(document.getElementById(`precio-${idx}`)?.value) || 0;
        total += cant * precio;
    });
    document.getElementById('totalOrden').textContent = fmt(total);
}

function quitarItem(idx) {
    const tr = document.getElementById(`item-${idx}`);
    if (tr) { tr.remove(); recalcTotal(); }
}

async function guardarOrden() {
    const provId = parseInt(document.getElementById('oProveedor').value) || 0;
    if (!provId) { toast('Seleccioná un proveedor', 'error'); return; }
    const items = [];
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const idx    = tr.id.split('-')[1];
        const selProd= tr.querySelector('select');
        const desc   = document.getElementById(`desc-${idx}`)?.value.trim()  || '';
        const cant   = parseFloat(document.getElementById(`cant-${idx}`)?.value)   || 0;
        const precio = parseFloat(document.getElementById(`precio-${idx}`)?.value) || 0;
        if (desc && cant > 0) {
            items.push({
                producto_id:    parseInt(selProd?.value) || null,
                descripcion:    desc,
                cantidad:       cant,
                precio_unitario:precio,
            });
        }
    });
    if (!items.length) { toast('Agregá al menos un ítem', 'error'); return; }
    const r = await fetch(API_ORDENES, {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            proveedor_id:           provId,
            fecha:                  document.getElementById('oFecha').value,
            fecha_entrega_esperada: document.getElementById('oEntrega').value || null,
            estado:                 document.getElementById('oEstado').value,
            notas:                  document.getElementById('oNotas').value.trim() || null,
            items,
        })
    });
    const j = await r.json();
    if (j.success) {
        cerrarModal('modalOrden');
        toast(`Orden ${j.data.numero} creada ✓`);
        await init();
        renderTabla(ordenesFiltradas());
    } else toast(j.message || 'Error', 'error');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function labelEstado(e) {
    return {borrador:'Borrador', enviada:'Enviada', recibida:'Recibida', cancelada:'Cancelada'}[e] || e;
}
function fmt(n)      { return '$' + Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function hoy()       { return new Date().toISOString().split('T')[0]; }
function fmtFecha(f) { if (!f) return '—'; const p=f.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s)      { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo==='error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}
function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

init();
</script>
</body>
</html>
