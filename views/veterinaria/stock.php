<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock — Veterinaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__.'/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__.'/../../public/css/components.css') ?>">
    <style>
        :root { --vet:#84cc16; --vet-dark:#65a30d; --vet-light:rgba(132,204,22,.1); }
        .toolbar { padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;background:var(--surface); }
        .filtros-bar { padding:12px 24px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;align-items:center;background:var(--surface); }
        .fi-sm { padding:7px 11px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;background:var(--background);color:var(--text-primary); }
        .stats-row { display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:12px;padding:20px 24px; }
        .stat-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px; }
        .stat-icon { width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
        .stat-info .stat-label { font-size:11px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin:0 0 2px; }
        .stat-info .stat-value { font-size:22px;font-weight:800;color:var(--text-primary);margin:0; }
        .tabla-wrap { padding:0 24px 24px;overflow-x:auto; }
        table { width:100%;border-collapse:collapse;font-size:13px; }
        th { padding:10px 14px;background:var(--background);color:var(--text-secondary);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap; }
        td { padding:12px 14px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }
        .cat-chip { display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700; }
        .cat-antiparasitario { background:rgba(132,204,22,.12);color:#65a30d; }
        .cat-vacuna           { background:rgba(15,209,134,.12);color:#059669; }
        .cat-antibiotico      { background:rgba(239,68,68,.12);color:#dc2626; }
        .cat-analgesico       { background:rgba(251,191,36,.12);color:#b45309; }
        .cat-insumo           { background:rgba(99,102,241,.12);color:#4f46e5; }
        .cat-otro             { background:var(--background);color:var(--text-secondary); }
        .stock-ok      { color:#059669;font-weight:700; }
        .stock-alerta  { color:#dc2626;font-weight:800; }
        .stock-bar     { height:6px;border-radius:3px;background:var(--border);overflow:hidden;width:80px;display:inline-block;vertical-align:middle;margin-left:6px; }
        .stock-fill    { height:100%;border-radius:3px; }
        .btn-table { width:30px;height:30px;border:none;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;transition:.15s; }
        .btn-edit  { background:rgba(99,102,241,.1);color:#4f46e5; }
        .btn-edit:hover  { background:#4f46e5;color:#fff; }
        .btn-mov   { background:rgba(132,204,22,.1);color:var(--vet-dark); }
        .btn-mov:hover   { background:var(--vet);color:#fff; }
        .btn-del   { background:rgba(239,68,68,.1);color:#ef4444; }
        .btn-del:hover   { background:#ef4444;color:#fff; }
        .alerta-row td { background:rgba(239,68,68,.04); }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface);border-radius:16px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);border:1px solid var(--border); }
        .modal-header { padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
        .modal-header h3 { margin:0;font-size:17px;color:var(--text-primary); }
        .modal-close { background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:18px;padding:4px; }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border); }
        .form-2col { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
        .form-2col .full { grid-column:1/-1; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block;font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px; }
        .form-control { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);font-family:inherit; }
        .form-control:focus { outline:none;border-color:var(--vet);box-shadow:0 0 0 3px var(--vet-light); }
        .toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);color:var(--text-primary);border-radius:12px;padding:14px 20px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:12px;max-width:320px;border:1px solid var(--border);border-left:4px solid var(--vet); }
        .toast.show { display:flex; }
        .toast.error { border-left-color:#ef4444; }
        .empty-state { text-align:center;padding:60px 24px;color:var(--text-secondary); }
        .empty-state i { font-size:44px;margin-bottom:12px;display:block;opacity:.3; }
    </style>
</head>
<body>
<div class="app-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>

<div class="toolbar">
    <div>
        <h1 style="margin:0;font-size:20px;font-weight:700;"><i class="fas fa-box-open" style="color:var(--vet);margin-right:8px;"></i>Stock de Medicamentos</h1>
        <p style="margin:0;font-size:12px;color:var(--text-secondary);">Inventario de medicamentos e insumos</p>
    </div>
    <button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus"></i> Nuevo ítem</button>
</div>

<div class="stats-row" id="statsRow"></div>

<div class="filtros-bar">
    <input type="text" class="fi-sm" id="busqueda" placeholder="🔍 Buscar producto…" oninput="filtrar()" style="min-width:200px;">
    <select class="fi-sm" id="filtCat" onchange="filtrar()">
        <option value="">Todas las categorías</option>
        <option value="antiparasitario">Antiparasitario</option>
        <option value="vacuna">Vacuna</option>
        <option value="antibiotico">Antibiótico</option>
        <option value="analgesico">Analgésico</option>
        <option value="insumo">Insumo</option>
        <option value="otro">Otro</option>
    </select>
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
        <input type="checkbox" id="soloAlertas" onchange="filtrar()"> Solo con alerta de stock
    </label>
</div>

<div class="tabla-wrap">
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th>Stock actual</th>
                <th>Mín.</th>
                <th>P. costo</th>
                <th>P. venta</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="tbody">
            <tr><td colspan="8" style="padding:30px;text-align:center;">Cargando…</td></tr>
        </tbody>
    </table>
</div>

</div><!-- /main-content -->
</div><!-- /app-layout -->

<!-- Modal nuevo/editar -->
<div class="modal-overlay" id="modalItem">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalItemTitulo">Nuevo ítem</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="fId">
            <div class="form-2col">
                <div class="form-group full">
                    <label>Nombre *</label>
                    <input type="text" id="fNombre" class="form-control" placeholder="Ej: Ivermectina 1%">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select id="fCat" class="form-control">
                        <option value="antiparasitario">Antiparasitario</option>
                        <option value="vacuna">Vacuna</option>
                        <option value="antibiotico">Antibiótico</option>
                        <option value="analgesico">Analgésico</option>
                        <option value="insumo">Insumo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Unidad</label>
                    <select id="fUnidad" class="form-control">
                        <option value="unidad">Unidad</option>
                        <option value="caja">Caja</option>
                        <option value="frasco">Frasco</option>
                        <option value="ampolla">Ampolla</option>
                        <option value="comprimido">Comprimido</option>
                        <option value="ml">ml</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock actual</label>
                    <input type="number" id="fStock" class="form-control" value="0" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Stock mínimo</label>
                    <input type="number" id="fStockMin" class="form-control" value="0" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Precio costo $</label>
                    <input type="number" id="fCosto" class="form-control" value="0" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Precio venta $</label>
                    <input type="number" id="fVenta" class="form-control" value="0" min="0" step="0.01">
                </div>
                <div class="form-group full">
                    <label>Descripción / Notas</label>
                    <textarea id="fDesc" class="form-control" rows="2" placeholder="Laboratorio, concentración, observaciones…"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardar()">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal movimiento de stock -->
<div class="modal-overlay" id="modalMov">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <h3>Movimiento de stock</h3>
            <button class="modal-close" onclick="cerrarMovModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p id="movItemNombre" style="font-weight:700;margin-bottom:16px;font-size:15px;"></p>
            <input type="hidden" id="movItemId">
            <div class="form-group">
                <label>Tipo</label>
                <select id="movTipo" class="form-control">
                    <option value="entrada">➕ Entrada (compra/recepción)</option>
                    <option value="salida">➖ Salida (uso/venta)</option>
                    <option value="ajuste">🔄 Ajuste (corregir stock)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" id="movCantidad" class="form-control" value="1" min="0.01" step="0.01">
            </div>
            <div class="form-group">
                <label>Motivo / Referencia</label>
                <input type="text" id="movMotivo" class="form-control" placeholder="Ej: Compra a proveedor, uso en consulta…">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="cerrarMovModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarMov()">Registrar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"><span id="toastMsg"></span></div>

<script>
const API = '../../api/veterinaria/stock.php';
let items = [];
let kpis  = {};

async function cargar() {
    try {
        const r = await fetch(API).then(x=>x.json());
        if (!r.success) { showToast(r.message||'Error',true); return; }
        items = r.data.items || [];
        kpis  = r.data.kpis  || {};
        renderStats();
        filtrar();
    } catch { showToast('Error de conexión',true); }
}

function renderStats() {
    document.getElementById('statsRow').innerHTML = `
        <div class="stat-card"><div class="stat-icon" style="background:rgba(132,204,22,.12);color:var(--vet-dark)"><i class="fas fa-box"></i></div><div class="stat-info"><p class="stat-label">Productos</p><h3 class="stat-value">${kpis.total||0}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(239,68,68,.12);color:#dc2626"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-info"><p class="stat-label">Bajo stock</p><h3 class="stat-value" style="color:${kpis.alertas>0?'#dc2626':'inherit'}">${kpis.alertas||0}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(99,102,241,.12);color:#4f46e5"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><p class="stat-label">Valor costo</p><h3 class="stat-value">$${Number(kpis.valor_costo||0).toLocaleString('es-AR',{minimumFractionDigits:0})}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(15,209,134,.12);color:#059669"><i class="fas fa-tag"></i></div><div class="stat-info"><p class="stat-label">Valor venta</p><h3 class="stat-value">$${Number(kpis.valor_venta||0).toLocaleString('es-AR',{minimumFractionDigits:0})}</h3></div></div>
    `;
}

function filtrar() {
    const q    = document.getElementById('busqueda').value.toLowerCase();
    const cat  = document.getElementById('filtCat').value;
    const solo = document.getElementById('soloAlertas').checked;

    let rows = items.filter(i => {
        if (q   && !i.nombre.toLowerCase().includes(q)) return false;
        if (cat  && i.categoria !== cat)                 return false;
        if (solo && parseFloat(i.stock_actual) > parseFloat(i.stock_minimo)) return false;
        return true;
    });
    renderTabla(rows);
}

function renderTabla(rows) {
    const tbody = document.getElementById('tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><i class="fas fa-box-open"></i><p>Sin productos. Agregá el primero con el botón "Nuevo ítem".</p></div></td></tr>`;
        return;
    }
    const CAT_LBL = {antiparasitario:'Antiparasitario',vacuna:'Vacuna',antibiotico:'Antibiótico',analgesico:'Analgésico',insumo:'Insumo',otro:'Otro'};
    tbody.innerHTML = rows.map(i => {
        const stock    = parseFloat(i.stock_actual);
        const minimo   = parseFloat(i.stock_minimo);
        const esAlerta = stock <= minimo && minimo >= 0;
        const pct      = minimo > 0 ? Math.min(100, Math.round(stock/minimo*100)) : 100;
        const fillColor= esAlerta ? '#ef4444' : pct < 150 ? '#f59e0b' : '#84cc16';
        const fmtMoney = n => '$'+Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0});
        return `<tr class="${esAlerta?'alerta-row':''}">
            <td>
                ${esAlerta?'<i class="fas fa-exclamation-triangle" style="color:#ef4444;margin-right:6px;" title="Stock bajo mínimo"></i>':''}
                <strong>${esc(i.nombre)}</strong>
                ${i.descripcion?`<br><small style="color:var(--text-secondary)">${esc(i.descripcion)}</small>`:''}
            </td>
            <td><span class="cat-chip cat-${i.categoria||'otro'}">${CAT_LBL[i.categoria]||'Otro'}</span></td>
            <td style="color:var(--text-secondary)">${esc(i.unidad)}</td>
            <td>
                <span class="${esAlerta?'stock-alerta':'stock-ok'}">${stock}</span>
                <span class="stock-bar"><span class="stock-fill" style="width:${pct}%;background:${fillColor};"></span></span>
            </td>
            <td style="color:var(--text-secondary)">${minimo}</td>
            <td>${fmtMoney(i.precio_costo)}</td>
            <td style="font-weight:700;">${fmtMoney(i.precio_venta)}</td>
            <td>
                <div style="display:flex;gap:4px;">
                    <button class="btn-table btn-mov" title="Movimiento de stock" onclick="abrirMov(${i.id},'${esc(i.nombre).replace(/'/g,"\\'")}')"><i class="fas fa-exchange-alt"></i></button>
                    <button class="btn-table btn-edit" title="Editar" onclick="abrirEditar(${i.id})"><i class="fas fa-pen"></i></button>
                    <button class="btn-table btn-del" title="Eliminar" onclick="eliminar(${i.id})"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function abrirModal() {
    document.getElementById('fId').value = '';
    document.getElementById('modalItemTitulo').textContent = 'Nuevo ítem';
    ['fNombre','fDesc'].forEach(id=>document.getElementById(id).value='');
    ['fStock','fStockMin','fCosto','fVenta'].forEach(id=>document.getElementById(id).value='0');
    document.getElementById('fCat').value   = 'otro';
    document.getElementById('fUnidad').value = 'unidad';
    document.getElementById('modalItem').classList.add('open');
}

function abrirEditar(id) {
    const i = items.find(x=>x.id==id);
    if (!i) return;
    document.getElementById('fId').value       = i.id;
    document.getElementById('fNombre').value   = i.nombre;
    document.getElementById('fDesc').value     = i.descripcion||'';
    document.getElementById('fCat').value      = i.categoria||'otro';
    document.getElementById('fUnidad').value   = i.unidad||'unidad';
    document.getElementById('fStock').value    = i.stock_actual;
    document.getElementById('fStockMin').value = i.stock_minimo;
    document.getElementById('fCosto').value    = i.precio_costo;
    document.getElementById('fVenta').value    = i.precio_venta;
    document.getElementById('modalItemTitulo').textContent = 'Editar ítem';
    document.getElementById('modalItem').classList.add('open');
}

function cerrarModal() { document.getElementById('modalItem').classList.remove('open'); }

async function guardar() {
    const id     = document.getElementById('fId').value;
    const nombre = document.getElementById('fNombre').value.trim();
    if (!nombre) { showToast('El nombre es obligatorio',true); return; }

    const body = {
        nombre, id: id||undefined,
        descripcion:  document.getElementById('fDesc').value.trim(),
        categoria:    document.getElementById('fCat').value,
        unidad:       document.getElementById('fUnidad').value,
        stock_actual: parseFloat(document.getElementById('fStock').value)||0,
        stock_minimo: parseFloat(document.getElementById('fStockMin').value)||0,
        precio_costo: parseFloat(document.getElementById('fCosto').value)||0,
        precio_venta: parseFloat(document.getElementById('fVenta').value)||0,
    };

    const method = id ? 'PUT' : 'POST';
    try {
        const r = await fetch(API, {method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)}).then(x=>x.json());
        if (!r.success) { showToast(r.message||'Error',true); return; }
        showToast(id?'Ítem actualizado':'Ítem creado');
        cerrarModal();
        cargar();
    } catch { showToast('Error de conexión',true); }
}

async function eliminar(id) {
    if (!confirm('¿Eliminar este ítem del stock?')) return;
    try {
        const r = await fetch(`${API}?id=${id}`, {method:'DELETE'}).then(x=>x.json());
        if (!r.success) { showToast(r.message||'Error',true); return; }
        showToast('Ítem eliminado');
        cargar();
    } catch { showToast('Error de conexión',true); }
}

function abrirMov(id, nombre) {
    document.getElementById('movItemId').value     = id;
    document.getElementById('movItemNombre').textContent = nombre;
    document.getElementById('movCantidad').value   = '1';
    document.getElementById('movMotivo').value     = '';
    document.getElementById('movTipo').value       = 'salida';
    document.getElementById('modalMov').classList.add('open');
}

function cerrarMovModal() { document.getElementById('modalMov').classList.remove('open'); }

async function guardarMov() {
    const id  = parseInt(document.getElementById('movItemId').value);
    const qty = parseFloat(document.getElementById('movCantidad').value)||0;
    if (qty <= 0) { showToast('Cantidad inválida',true); return; }

    try {
        const r = await fetch(API, {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                id, mov:true,
                tipo:     document.getElementById('movTipo').value,
                cantidad: qty,
                motivo:   document.getElementById('movMotivo').value.trim(),
            })
        }).then(x=>x.json());
        if (!r.success) { showToast(r.message||'Error',true); return; }
        showToast(`Movimiento registrado — nuevo stock: ${r.data.stock_actual}`);
        cerrarMovModal();
        cargar();
    } catch { showToast('Error de conexión',true); }
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function showToast(msg, isErr=false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.toggle('error',isErr);
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),3000);
}

document.getElementById('modalItem').addEventListener('click',e=>{ if(e.target.id==='modalItem') cerrarModal(); });
document.getElementById('modalMov').addEventListener('click',e=>{ if(e.target.id==='modalMov') cerrarMovModal(); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape'){ cerrarModal(); cerrarMovModal(); } });

cargar();
</script>
</body>
</html>
