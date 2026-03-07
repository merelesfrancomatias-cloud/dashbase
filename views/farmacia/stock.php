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
    <title>Control de Stock — Farmacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        :root { --farm: #10b981; }
        .app-layout { display:flex; min-height:100vh; }
        .stat-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        @media(max-width:700px){ .stat-strip { grid-template-columns:repeat(2,1fr); } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .stat-val  { font-size:22px; font-weight:800; line-height:1; }
        .stat-lbl  { font-size:12px; color:var(--text-secondary); margin-top:3px; }

        .toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:16px 20px 0; }
        .search-box { flex:1; min-width:220px; position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input { width:100%; padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .search-box input:focus { outline:none; border-color:var(--farm); }
        .filter-chip { padding:7px 14px; border-radius:20px; border:1px solid var(--border); background:transparent; font-size:12px; font-weight:600; cursor:pointer; color:var(--text-secondary); transition:.15s; }
        .filter-chip.active { background:var(--farm); color:#fff; border-color:var(--farm); }
        .filter-chip.warn.active  { background:#f59e0b; border-color:#f59e0b; color:#fff; }
        .filter-chip.danger.active{ background:#dc2626; border-color:#dc2626; color:#fff; }

        .table-wrap { padding:16px 20px 20px; overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th { padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-secondary); text-align:left; border-bottom:1px solid var(--border); background:var(--background); white-space:nowrap; }
        td { padding:12px 14px; font-size:13px; color:var(--text-primary); border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:hover td { background:var(--hover,#f8fafc); }

        .stock-bar-wrap { width:90px; }
        .stock-bar { height:6px; border-radius:3px; background:var(--border); overflow:hidden; }
        .stock-bar-fill { height:100%; border-radius:3px; }
        .fill-ok     { background:var(--farm); }
        .fill-warn   { background:#f59e0b; }
        .fill-danger { background:#dc2626; }

        .badge-ok     { background:rgba(16,185,129,.1); color:var(--farm); padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-warn   { background:#fef3c7; color:#d97706; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-danger { background:#fee2e2; color:#dc2626; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }

        .empty-state { text-align:center; padding:50px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }

        /* Modal ajuste */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:10000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:var(--surface); border-radius:16px; width:420px; max-width:100%; display:flex; flex-direction:column; }
        .modal-header { padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-body   { padding:20px 22px; }
        .modal-footer { padding:14px 22px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:5px; }
        .form-group input, .form-group select { width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .form-group input:focus, .form-group select:focus { outline:none; border-color:var(--farm); }
        .prod-preview { background:var(--background); border-radius:10px; padding:12px 14px; margin-bottom:14px; }
        .prod-preview .nombre { font-weight:700; font-size:15px; }
        .prod-preview .detalle { font-size:12px; color:var(--text-secondary); margin-top:3px; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Header -->
            <div class="card" style="margin-bottom:20px;padding:18px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-boxes-stacked" style="color:var(--farm);margin-right:8px;"></i>Control de Stock
                        </h1>
                        <p style="margin:4px 0 0;font-size:14px;color:var(--text-secondary);">Niveles de inventario de medicamentos y productos</p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="vencimientos.php" class="btn btn-secondary" style="font-size:13px;"><i class="fas fa-calendar-times"></i> Vencimientos</a>
                        <a href="../productos/index.php" class="btn btn-secondary" style="font-size:13px;"><i class="fas fa-box"></i> Productos</a>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-strip">
                <div class="stat-card"><div class="stat-icon" style="background:#fee2e220;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div><div><div class="stat-val" id="stCrit">—</div><div class="stat-lbl">Sin stock</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fef3c720;color:#d97706;"><i class="fas fa-arrow-down"></i></div><div><div class="stat-val" id="stBajo">—</div><div class="stat-lbl">Stock bajo</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,.1);color:var(--farm);"><i class="fas fa-check-circle"></i></div><div><div class="stat-val" id="stOk">—</div><div class="stat-lbl">Stock normal</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-boxes-stacked"></i></div><div><div class="stat-val" id="stTotal">—</div><div class="stat-lbl">Total productos</div></div></div>
            </div>

            <!-- Tabla -->
            <div class="card" style="padding:0;">
                <div class="toolbar">
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchStock" placeholder="Buscar medicamento, código…" oninput="filtrar()"></div>
                    <button class="filter-chip danger active" id="chipCrit" data-f="critico" onclick="toggleChip(this)">Sin stock</button>
                    <button class="filter-chip warn"         id="chipBajo" data-f="bajo"    onclick="toggleChip(this)">Stock bajo</button>
                    <button class="filter-chip"              id="chipOk"   data-f="ok"      onclick="toggleChip(this)">Normal</button>
                    <button class="filter-chip"              id="chipTodos" data-f="todos"  onclick="toggleChip(this)">Todos</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Medicamento / Producto</th>
                                <th>Categoría</th>
                                <th>Stock actual</th>
                                <th>Stock mínimo</th>
                                <th>Nivel</th>
                                <th>Estado</th>
                                <th>Precio</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaStock"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal ajuste stock -->
<div id="modalAjuste" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--text-primary);">Ajustar Stock</h3>
            <button onclick="cerrarAjuste()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body">
            <div class="prod-preview" id="prevProd"></div>
            <div class="form-group">
                <label>Tipo de movimiento</label>
                <select id="ajusteTipo">
                    <option value="entrada">Entrada (suma)</option>
                    <option value="salida">Salida (resta)</option>
                    <option value="ajuste">Ajuste manual (fija el valor)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" id="ajusteCant" min="0" value="1">
            </div>
            <div class="form-group">
                <label>Motivo</label>
                <input type="text" id="ajusteMotivo" placeholder="Recepción mercadería, merma, inventario…">
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="cerrarAjuste()" class="btn btn-secondary">Cancelar</button>
            <button onclick="confirmarAjuste()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);" id="btnAjuste">
                <i class="fas fa-save"></i> Confirmar
            </button>
        </div>
    </div>
</div>

<script>
const API_PROD = '../../api/productos/index.php';
let todos = [];
let filtroActivo = 'critico';
let prodAjuste = null;

async function cargar() {
    const r = await fetch(API_PROD + '?limit=500', {credentials:'include'});
    const j = await r.json();
    todos = j.success ? (j.data?.productos ?? j.data ?? []) : [];
    actualizarStats();
    filtrar();
}

function estadoStock(p) {
    const stock = parseFloat(p.stock ?? p.cantidad ?? 0);
    const min   = parseFloat(p.stock_minimo ?? p.stock_min ?? 0);
    if (stock <= 0) return 'critico';
    if (min > 0 && stock <= min) return 'bajo';
    return 'ok';
}

function actualizarStats() {
    const crit = todos.filter(p=>estadoStock(p)==='critico').length;
    const bajo = todos.filter(p=>estadoStock(p)==='bajo').length;
    const ok   = todos.filter(p=>estadoStock(p)==='ok').length;
    document.getElementById('stCrit').textContent  = crit;
    document.getElementById('stBajo').textContent  = bajo;
    document.getElementById('stOk').textContent    = ok;
    document.getElementById('stTotal').textContent = todos.length;
}

function toggleChip(el) {
    filtroActivo = el.dataset.f;
    document.querySelectorAll('.filter-chip').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    filtrar();
}

function filtrar() {
    const q = document.getElementById('searchStock').value.toLowerCase().trim();
    let lista = todos;
    if (filtroActivo === 'critico') lista = lista.filter(p=>estadoStock(p)==='critico');
    else if (filtroActivo === 'bajo') lista = lista.filter(p=>estadoStock(p)==='bajo');
    else if (filtroActivo === 'ok')   lista = lista.filter(p=>estadoStock(p)==='ok');
    if (q) lista = lista.filter(p=>
        (p.nombre||'').toLowerCase().includes(q) ||
        (p.codigo_barras||'').toLowerCase().includes(q)
    );
    renderTabla(lista);
}

function renderTabla(lista) {
    const tb = document.getElementById('tablaStock');
    if (!lista.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty-state"><i class="fas fa-boxes-stacked"></i><p>No hay productos en esta categoría</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = lista.map(p => {
        const stock = parseFloat(p.stock ?? p.cantidad ?? 0);
        const min   = parseFloat(p.stock_minimo ?? p.stock_min ?? 0);
        const est   = estadoStock(p);
        const pct   = min > 0 ? Math.min(100, Math.round(stock/min*100)) : (stock > 0 ? 100 : 0);
        const fillCls = est==='ok' ? 'fill-ok' : est==='bajo' ? 'fill-warn' : 'fill-danger';
        const badgeCls = est==='ok' ? 'badge-ok' : est==='bajo' ? 'badge-warn' : 'badge-danger';
        const badgeLbl = est==='ok' ? 'Normal' : est==='bajo' ? 'Bajo' : 'Sin stock';
        return `<tr>
            <td>
                <div style="font-weight:700;">${esc(p.nombre)}</div>
                ${p.codigo_barras?`<div style="font-family:monospace;font-size:11px;color:var(--text-secondary);">${esc(p.codigo_barras)}</div>`:''}
            </td>
            <td><span style="font-size:12px;background:var(--background);padding:2px 8px;border-radius:6px;border:1px solid var(--border);">${esc(p.categoria_nombre||p.categoria||'—')}</span></td>
            <td><span style="font-size:16px;font-weight:800;color:${est==='critico'?'#dc2626':est==='bajo'?'#d97706':'var(--farm)'};">${stock}</span></td>
            <td><span style="font-size:13px;color:var(--text-secondary);">${min||'—'}</span></td>
            <td>
                <div class="stock-bar-wrap">
                    <div class="stock-bar"><div class="stock-bar-fill ${fillCls}" style="width:${pct}%"></div></div>
                    <div style="font-size:10px;color:var(--text-secondary);margin-top:2px;">${pct}%</div>
                </div>
            </td>
            <td><span class="${badgeCls}">${badgeLbl}</span></td>
            <td><span style="font-weight:700;">$${parseFloat(p.precio||0).toFixed(2)}</span></td>
            <td><button onclick="abrirAjuste(${p.id})" class="btn btn-secondary" style="font-size:12px;padding:5px 10px;"><i class="fas fa-edit"></i> Ajustar</button></td>
        </tr>`;
    }).join('');
}

function abrirAjuste(id) {
    prodAjuste = todos.find(p=>p.id==id);
    if (!prodAjuste) return;
    const stock = parseFloat(prodAjuste.stock ?? prodAjuste.cantidad ?? 0);
    document.getElementById('prevProd').innerHTML = `
        <div class="nombre">${esc(prodAjuste.nombre)}</div>
        <div class="detalle">Stock actual: <strong>${stock}</strong> unidades · Mínimo: <strong>${prodAjuste.stock_minimo||prodAjuste.stock_min||0}</strong></div>
    `;
    document.getElementById('ajusteCant').value = '';
    document.getElementById('ajusteMotivo').value = '';
    document.getElementById('ajusteTipo').value = 'entrada';
    document.getElementById('modalAjuste').classList.add('show');
    setTimeout(()=>document.getElementById('ajusteCant').focus(),50);
}

function cerrarAjuste() { document.getElementById('modalAjuste').classList.remove('show'); prodAjuste=null; }

async function confirmarAjuste() {
    if (!prodAjuste) return;
    const tipo = document.getElementById('ajusteTipo').value;
    const cant = parseFloat(document.getElementById('ajusteCant').value);
    if (isNaN(cant) || cant < 0) { document.getElementById('ajusteCant').focus(); return; }

    const stockActual = parseFloat(prodAjuste.stock ?? prodAjuste.cantidad ?? 0);
    let nuevoStock;
    if (tipo === 'entrada')  nuevoStock = stockActual + cant;
    else if (tipo === 'salida') nuevoStock = Math.max(0, stockActual - cant);
    else nuevoStock = cant; // ajuste manual

    const btn = document.getElementById('btnAjuste');
    btn.disabled = true;
    const r = await fetch(API_PROD, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id: prodAjuste.id, stock: nuevoStock })
    });
    const j = await r.json();
    btn.disabled = false;
    if (j.success) { cerrarAjuste(); cargar(); } else { alert('Error: ' + j.message); }
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
document.getElementById('modalAjuste').addEventListener('click',e=>{if(e.target===e.currentTarget)cerrarAjuste();});
cargar();
</script>
</body>
</html>
