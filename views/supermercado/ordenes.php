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
    <title>Órdenes de Compra — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        .page-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 0; flex-wrap: wrap; gap: 12px; }
        .page-title { font-size: 20px; font-weight: 800; color: var(--text-color,#1e293b); }
        .btn-new { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: #16a34a; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
        .btn-new:hover { background: #15803d; }

        /* Stats */
        .ord-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; padding: 16px 24px 0; }
        .ord-stat { background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb); border-radius: 14px; padding: 14px 18px; }
        .ord-stat-n { font-size: 26px; font-weight: 900; }
        .ord-stat-l { font-size: 12px; color: var(--muted-color,#64748b); font-weight: 600; margin-top: 2px; }

        /* Tabs de estado */
        .estado-tabs { display: flex; gap: 6px; padding: 16px 24px 0; flex-wrap: wrap; }
        .estado-tab { padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--muted-color,#64748b); transition: all .15s; }
        .estado-tab.active { background: #16a34a; color: #fff; border-color: #16a34a; }

        /* Lista de órdenes */
        .ordenes-list { display: flex; flex-direction: column; gap: 10px; padding: 16px 24px; }
        .orden-card { background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb); border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; transition: box-shadow .15s; cursor: pointer; }
        .orden-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .orden-card.borde-borrador { border-left: 4px solid #94a3b8; }
        .orden-card.borde-enviada  { border-left: 4px solid #3b82f6; }
        .orden-card.borde-recibida { border-left: 4px solid #16a34a; }
        .orden-card.borde-cancelada{ border-left: 4px solid #ef4444; }

        .orden-num { font-size: 15px; font-weight: 800; color: var(--text-color,#1e293b); }
        .orden-prov { font-size: 13px; color: var(--muted-color,#64748b); margin-top: 2px; }
        .orden-fecha { font-size: 12px; color: var(--muted-color,#9ca3af); margin-top: 2px; }
        .orden-meta { flex: 1; min-width: 0; }
        .orden-total { font-size: 18px; font-weight: 800; color: #16a34a; white-space: nowrap; }
        .orden-items-count { font-size: 11px; color: var(--muted-color,#9ca3af); font-weight: 600; text-align: right; }

        .badge-estado { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .badge-borrador  { background: rgba(148,163,184,.12); color: #64748b; }
        .badge-enviada   { background: rgba(59,130,246,.12);  color: #2563eb; }
        .badge-recibida  { background: rgba(22,163,74,.12);   color: #16a34a; }
        .badge-cancelada { background: rgba(239,68,68,.12);   color: #dc2626; }

        .orden-actions { display: flex; gap: 8px; }
        .btn-sm { padding: 5px 12px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-color,#374151); transition: all .15s; }
        .btn-sm:hover { border-color: #16a34a; color: #16a34a; background: rgba(22,163,74,.06); }
        .btn-sm.danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,.06); }
        .btn-sm.primary { background: #16a34a; color: #fff; border-color: #16a34a; }
        .btn-sm.primary:hover { background: #15803d; }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; color: var(--muted-color,#9ca3af); gap: 10px; }
        .empty-state i { font-size: 40px; }
        .empty-state p { font-size: 14px; }

        /* ======== MODAL ======== */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 20px; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: var(--card-bg,#fff); border-radius: 20px; width: 100%; max-width: 720px; overflow: hidden; margin: auto; }
        .modal-head { padding: 20px 24px; border-bottom: 1px solid var(--border-color,#e5e7eb); display: flex; align-items: center; justify-content: space-between; background: #f8fafc; }
        .modal-head h3 { font-size: 16px; font-weight: 700; }
        .modal-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border-color,#e5e7eb); display: flex; gap: 10px; justify-content: flex-end; }

        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 11px; font-weight: 700; color: var(--muted-color,#64748b); text-transform: uppercase; letter-spacing: .4px; }
        .form-group input, .form-group select, .form-group textarea { padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); background: var(--card-bg,#fff); font-size: 14px; color: var(--text-color,#1e293b); outline: none; width: 100%; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }

        /* Items table */
        .items-section { border: 1px solid var(--border-color,#e5e7eb); border-radius: 12px; overflow: hidden; }
        .items-header { padding: 10px 14px; background: var(--hover-bg,#f8fafc); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color,#e5e7eb); }
        .items-header span { font-size: 12px; font-weight: 700; color: var(--muted-color,#64748b); text-transform: uppercase; }
        .btn-add-item { display: flex; align-items: center; gap: 5px; padding: 5px 12px; background: #16a34a; color: #fff; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { padding: 8px 12px; font-size: 11px; font-weight: 700; color: var(--muted-color,#64748b); text-transform: uppercase; text-align: left; background: var(--hover-bg,#f8fafc); }
        .items-table td { padding: 6px 8px; border-top: 1px solid var(--border-color,#f1f5f9); }
        .items-table input { border: 1px solid var(--border-color,#e5e7eb); border-radius: 6px; padding: 5px 8px; font-size: 13px; width: 100%; box-sizing: border-box; }
        .items-table select { border: 1px solid var(--border-color,#e5e7eb); border-radius: 6px; padding: 5px 8px; font-size: 13px; width: 100%; box-sizing: border-box; }
        .btn-del-item { padding: 4px 8px; border: none; background: rgba(239,68,68,.1); color: #ef4444; border-radius: 6px; cursor: pointer; font-size: 12px; }
        .orden-total-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
        .orden-total-box span { font-size: 13px; font-weight: 600; color: #15803d; }
        .orden-total-box .monto { font-size: 22px; font-weight: 900; color: #16a34a; }

        .btn-primary-full { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 22px; background: #16a34a; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-primary-full:hover { background: #15803d; }
        .btn-cancel { padding: 11px 18px; border-radius: 12px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; font-size: 14px; cursor: pointer; color: var(--text-color,#374151); }

        /* Modal detalle */
        .detalle-items { display: flex; flex-direction: column; gap: 8px; }
        .detalle-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--hover-bg,#f8fafc); border-radius: 8px; }
        .detalle-item-nombre { font-size: 14px; font-weight: 600; }
        .detalle-item-precio { font-size: 13px; color: var(--muted-color,#64748b); }
        .detalle-item-total { font-size: 14px; font-weight: 700; color: #16a34a; }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <div class="page-header">
            <div class="page-title">Órdenes de Compra</div>
            <button class="btn-new" onclick="abrirNueva()">
                <i class="fas fa-plus"></i> Nueva Orden
            </button>
        </div>

        <div class="ord-stats">
            <div class="ord-stat"><div class="ord-stat-n" id="sTotal" style="color:#3b82f6">—</div><div class="ord-stat-l">Total</div></div>
            <div class="ord-stat"><div class="ord-stat-n" id="sBorrador" style="color:#94a3b8">—</div><div class="ord-stat-l">Borrador</div></div>
            <div class="ord-stat"><div class="ord-stat-n" id="sEnviada" style="color:#2563eb">—</div><div class="ord-stat-l">Enviadas</div></div>
            <div class="ord-stat"><div class="ord-stat-n" id="sRecibida" style="color:#16a34a">—</div><div class="ord-stat-l">Recibidas</div></div>
        </div>

        <div class="estado-tabs">
            <button class="estado-tab active" data-e="" onclick="setEstado('',this)">Todos</button>
            <button class="estado-tab" data-e="borrador" onclick="setEstado('borrador',this)">Borrador</button>
            <button class="estado-tab" data-e="enviada" onclick="setEstado('enviada',this)">Enviadas</button>
            <button class="estado-tab" data-e="recibida" onclick="setEstado('recibida',this)">Recibidas</button>
            <button class="estado-tab" data-e="cancelada" onclick="setEstado('cancelada',this)">Canceladas</button>
        </div>

        <div class="ordenes-list" id="ordenesList">
            <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Cargando…</p></div>
        </div>

    </div>
</div>

<!-- Modal Nueva Orden -->
<div class="modal-overlay" id="modalNueva">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-clipboard-list" style="color:#16a34a;margin-right:8px;"></i> Nueva Orden de Compra</h3>
            <button style="background:none;border:none;cursor:pointer;font-size:16px;" onclick="cerrarNueva()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="grid2">
                <div class="form-group">
                    <label>Proveedor *</label>
                    <select id="nProveedor" required><option value="">Cargando…</option></select>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="nEstado">
                        <option value="borrador">Borrador</option>
                        <option value="enviada">Enviada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" id="nFecha">
                </div>
                <div class="form-group">
                    <label>Entrega esperada</label>
                    <input type="date" id="nFechaEnt">
                </div>
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea id="nNotas" rows="2" placeholder="Observaciones…" style="resize:vertical;"></textarea>
            </div>

            <!-- Items -->
            <div class="items-section">
                <div class="items-header">
                    <span>Productos / Ítems</span>
                    <button class="btn-add-item" onclick="agregarFila()"><i class="fas fa-plus"></i> Agregar</button>
                </div>
                <table class="items-table">
                    <thead><tr>
                        <th>Producto</th>
                        <th style="width:90px">Cant.</th>
                        <th style="width:110px">P. Unitario</th>
                        <th style="width:100px">Subtotal</th>
                        <th style="width:36px"></th>
                    </tr></thead>
                    <tbody id="itemsTbody"></tbody>
                </table>
            </div>

            <div class="orden-total-box">
                <span>Total de la orden</span>
                <div class="monto" id="nTotal">$0</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarNueva()">Cancelar</button>
            <button class="btn-primary-full" onclick="guardarOrden()">
                <i class="fas fa-save"></i> Crear Orden
            </button>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="detNumero">Orden #</h3>
            <button style="background:none;border:none;cursor:pointer;font-size:16px;" onclick="cerrarDetalle()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detalleBody">
        </div>
        <div class="modal-footer" id="detallePies"></div>
    </div>
</div>

<script>
const API = '../../api/ordenes/index.php';
const API_PROV = '../../api/proveedores/index.php';
const API_PROD = '../../api/productos/index.php';

let todasOrdenes = [];
let todosProveedores = [];
let todosProductos = [];
let estadoFiltro = '';
let itemCount = 0;

async function cargar() {
    const [rO, rProv, rProd] = await Promise.all([
        fetch(API, { credentials: 'include' }),
        fetch(API_PROV, { credentials: 'include' }),
        fetch(API_PROD + '?limit=500', { credentials: 'include' })
    ]);
    const [dO, dProv, dProd] = await Promise.all([rO.json(), rProv.json(), rProd.json()]);

    todasOrdenes    = dO.data || [];
    todosProveedores = dProv.data || [];
    todosProductos   = dProd.data?.productos || dProd.data || [];

    actualizarStats();
    renderLista();
    poblarProvSelect();
}

function actualizarStats() {
    document.getElementById('sTotal').textContent    = todasOrdenes.length;
    document.getElementById('sBorrador').textContent  = todasOrdenes.filter(o => o.estado==='borrador').length;
    document.getElementById('sEnviada').textContent   = todasOrdenes.filter(o => o.estado==='enviada').length;
    document.getElementById('sRecibida').textContent  = todasOrdenes.filter(o => o.estado==='recibida').length;
}

function setEstado(e, el) {
    estadoFiltro = e;
    document.querySelectorAll('.estado-tab').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    renderLista();
}

function renderLista() {
    const lista = estadoFiltro ? todasOrdenes.filter(o => o.estado === estadoFiltro) : todasOrdenes;
    const cont = document.getElementById('ordenesList');
    if (!lista.length) {
        cont.innerHTML = `<div class="empty-state"><i class="fas fa-clipboard-list"></i><p>Sin órdenes${estadoFiltro ? ' en este estado' : ''}</p></div>`;
        return;
    }
    cont.innerHTML = lista.map(o => {
        const ic = { borrador:'🗒️', enviada:'📤', recibida:'✅', cancelada:'❌' }[o.estado] || '📋';
        return `<div class="orden-card borde-${o.estado}" onclick="verDetalle(${o.id})">
            <div style="font-size:28px;line-height:1;">${ic}</div>
            <div class="orden-meta">
                <div class="orden-num">${esc(o.numero)}</div>
                <div class="orden-prov"><i class="fas fa-truck" style="font-size:11px;margin-right:4px;"></i>${esc(o.proveedor_nombre||'Sin proveedor')}</div>
                <div class="orden-fecha"><i class="fas fa-calendar" style="font-size:10px;margin-right:4px;"></i>${fmtFecha(o.fecha)}</div>
            </div>
            <div style="text-align:right;">
                <div class="orden-total">$${Number(o.total||0).toLocaleString('es-AR')}</div>
                <div class="orden-items-count">${o.total_items||0} ítem(s)</div>
                <span class="badge-estado badge-${o.estado}" style="margin-top:4px;">${o.estado}</span>
            </div>
        </div>`;
    }).join('');
}

function poblarProvSelect() {
    const sel = document.getElementById('nProveedor');
    sel.innerHTML = '<option value="">— Seleccionar proveedor —</option>' +
        todosProveedores.map(p => `<option value="${p.id}">${esc(p.nombre)}</option>`).join('');
}

// === MODAL NUEVA ===
function abrirNueva() {
    document.getElementById('nProveedor').value = '';
    document.getElementById('nEstado').value = 'borrador';
    document.getElementById('nFecha').value = hoy();
    document.getElementById('nFechaEnt').value = '';
    document.getElementById('nNotas').value = '';
    document.getElementById('itemsTbody').innerHTML = '';
    document.getElementById('nTotal').textContent = '$0';
    itemCount = 0;
    agregarFila();
    document.getElementById('modalNueva').classList.add('open');
}
function cerrarNueva() { document.getElementById('modalNueva').classList.remove('open'); }

function agregarFila() {
    const tbody = document.getElementById('itemsTbody');
    const idx = itemCount++;
    const opts = todosProductos.map(p => `<option value="${p.id}" data-precio="${p.precio_costo||0}">${esc(p.nombre)}</option>`).join('');
    const tr = document.createElement('tr');
    tr.dataset.idx = idx;
    tr.innerHTML = `
        <td>
            <select onchange="autocompletarPrecio(${idx},this)" style="min-width:180px;">
                <option value="">Descripción manual</option>
                ${opts}
            </select>
            <input type="text" placeholder="o escribir descripción" style="margin-top:4px;" id="desc_${idx}">
        </td>
        <td><input type="number" id="cant_${idx}" value="1" min="1" oninput="recalcFila(${idx})"></td>
        <td><input type="number" id="precio_${idx}" value="0" min="0" step="0.01" oninput="recalcFila(${idx})"></td>
        <td><span id="sub_${idx}" style="font-weight:700;color:#16a34a;">$0</span></td>
        <td><button class="btn-del-item" onclick="elimFila(${idx})"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
}

function autocompletarPrecio(idx, sel) {
    const opt = sel.options[sel.selectedIndex];
    const precio = parseFloat(opt.dataset.precio) || 0;
    document.getElementById('precio_' + idx).value = precio;
    document.getElementById('desc_' + idx).value = opt.value ? opt.text : '';
    recalcFila(idx);
}

function recalcFila(idx) {
    const c = parseFloat(document.getElementById('cant_'  + idx)?.value) || 0;
    const p = parseFloat(document.getElementById('precio_'+ idx)?.value) || 0;
    const sub = c * p;
    const el = document.getElementById('sub_' + idx);
    if (el) el.textContent = '$' + sub.toLocaleString('es-AR');
    recalcTotal();
}

function recalcTotal() {
    let total = 0;
    document.querySelectorAll('[id^="cant_"]').forEach(el => {
        const idx = el.id.replace('cant_','');
        const c = parseFloat(el.value) || 0;
        const p = parseFloat(document.getElementById('precio_'+idx)?.value) || 0;
        total += c * p;
    });
    document.getElementById('nTotal').textContent = '$' + total.toLocaleString('es-AR');
}

function elimFila(idx) {
    const tr = document.querySelector(`tr[data-idx="${idx}"]`);
    if (tr) tr.remove();
    recalcTotal();
}

async function guardarOrden() {
    const provId = parseInt(document.getElementById('nProveedor').value);
    if (!provId) { alert('Seleccioná un proveedor'); return; }

    const items = [];
    document.querySelectorAll('[id^="cant_"]').forEach(el => {
        const idx = el.id.replace('cant_','');
        const sel = el.closest('tr').querySelector('select');
        const desc = document.getElementById('desc_'+idx)?.value || sel?.options[sel.selectedIndex]?.text || '';
        const prodId = sel && sel.value ? parseInt(sel.value) : null;
        const cant   = parseFloat(el.value) || 0;
        const precio = parseFloat(document.getElementById('precio_'+idx)?.value) || 0;
        if (cant > 0 && desc) {
            items.push({ producto_id: prodId, descripcion: desc, cantidad: cant, precio_unitario: precio });
        }
    });

    if (!items.length) { alert('Agregá al menos un ítem con cantidad'); return; }

    const body = {
        proveedor_id: provId,
        estado: document.getElementById('nEstado').value,
        fecha: document.getElementById('nFecha').value,
        fecha_entrega_esperada: document.getElementById('nFechaEnt').value || null,
        notas: document.getElementById('nNotas').value,
        items
    };

    const r = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify(body) });
    const d = await r.json();
    if (d.success) {
        cerrarNueva();
        await cargar();
    } else { alert(d.message || 'Error al crear'); }
}

// === MODAL DETALLE ===
async function verDetalle(id) {
    const r = await fetch(API + '?id=' + id, { credentials: 'include' });
    const d = await r.json();
    if (!d.success) return;
    const o = d.data;

    document.getElementById('detNumero').textContent = o.numero + ' · ' + o.proveedor_nombre;
    const body = document.getElementById('detalleBody');
    body.innerHTML = `
        <div class="grid2" style="margin-bottom:4px;">
            <div><span style="font-size:11px;font-weight:700;color:var(--muted-color,#64748b);">ESTADO</span><br>
                <span class="badge-estado badge-${o.estado}">${o.estado}</span>
            </div>
            <div><span style="font-size:11px;font-weight:700;color:var(--muted-color,#64748b);">FECHA</span><br>
                <span style="font-size:14px;">${fmtFecha(o.fecha)}</span>
            </div>
            ${o.fecha_entrega_esperada ? `<div><span style="font-size:11px;font-weight:700;color:var(--muted-color,#64748b);">ENTREGA ESPERADA</span><br>
                <span style="font-size:14px;">${fmtFecha(o.fecha_entrega_esperada)}</span></div>` : ''}
            ${o.notas ? `<div><span style="font-size:11px;font-weight:700;color:var(--muted-color,#64748b);">NOTAS</span><br>
                <span style="font-size:14px;">${esc(o.notas)}</span></div>` : ''}
        </div>
        <div class="detalle-items">
            ${(o.items||[]).map(item => `
                <div class="detalle-item">
                    <div>
                        <div class="detalle-item-nombre">${esc(item.descripcion||item.producto_nombre||'—')}</div>
                        <div class="detalle-item-precio">x${item.cantidad} · $${Number(item.precio_unitario).toLocaleString('es-AR')}</div>
                    </div>
                    <div class="detalle-item-total">$${Number(item.subtotal).toLocaleString('es-AR')}</div>
                </div>`).join('')}
        </div>
        <div class="orden-total-box" style="margin-top:8px;">
            <span>Total</span>
            <div class="monto">$${Number(o.total).toLocaleString('es-AR')}</div>
        </div>
    `;

    const footer = document.getElementById('detallePies');
    footer.innerHTML = '';
    if (o.estado === 'borrador') {
        footer.innerHTML += `<button class="btn-sm primary" onclick="cambiarEstado(${o.id},'enviada')"><i class="fas fa-paper-plane"></i> Marcar como Enviada</button>`;
    }
    if (o.estado === 'enviada') {
        footer.innerHTML += `<button class="btn-sm primary" onclick="recibirOrden(${o.id})"><i class="fas fa-check-double"></i> Recibir y actualizar stock</button>`;
    }
    if (o.estado !== 'cancelada' && o.estado !== 'recibida') {
        footer.innerHTML += `<button class="btn-sm danger" onclick="cambiarEstado(${o.id},'cancelada')"><i class="fas fa-ban"></i> Cancelar</button>`;
    }
    footer.innerHTML += `<button class="btn-cancel" onclick="cerrarDetalle()">Cerrar</button>`;

    document.getElementById('modalDetalle').classList.add('open');
}

function cerrarDetalle() { document.getElementById('modalDetalle').classList.remove('open'); }

async function cambiarEstado(id, estado) {
    const r = await fetch(API, { method:'PUT', headers:{'Content-Type':'application/json'}, credentials:'include',
        body: JSON.stringify({ id, estado }) });
    const d = await r.json();
    if (d.success) { cerrarDetalle(); await cargar(); }
    else alert(d.message || 'Error');
}

async function recibirOrden(id) {
    if (!confirm('¿Confirmar recepción? Se actualizará el stock de todos los productos.')) return;
    await cambiarEstado(id, 'recibida');
}

// Helpers
function hoy() { return new Date().toISOString().split('T')[0]; }
function fmtFecha(f) { if (!f) return '—'; const p = f.split(' ')[0].split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.getElementById('modalNueva').addEventListener('click', e => { if (e.target===document.getElementById('modalNueva')) cerrarNueva(); });
document.getElementById('modalDetalle').addEventListener('click', e => { if (e.target===document.getElementById('modalDetalle')) cerrarDetalle(); });

cargar();
</script>
</body>
</html>
