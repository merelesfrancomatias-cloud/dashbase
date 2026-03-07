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
    <title>Control de Stock — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        .stock-toolbar { display: flex; align-items: center; gap: 12px; padding: 20px 24px 0; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 200px; max-width: 340px; position: relative; }
        .search-box input { width: 100%; padding: 9px 14px 9px 36px; border-radius: 10px; border: 1px solid var(--border-color, #e5e7eb); background: var(--card-bg, #fff); font-size: 14px; color: var(--text-color, #1e293b); outline: none; }
        .search-box i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted-color, #9ca3af); font-size: 13px; }

        .filter-tabs { display: flex; gap: 6px; }
        .filter-tab { padding: 7px 14px; border-radius: 20px; border: 1px solid var(--border-color, #e5e7eb); background: transparent; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--muted-color, #64748b); transition: all .15s; }
        .filter-tab.active { background: #16a34a; color: #fff; border-color: #16a34a; }
        .filter-tab.warn.active { background: #f59e0b; border-color: #f59e0b; }
        .filter-tab.danger.active { background: #ef4444; border-color: #ef4444; }

        /* Estadísticas rápidas */
        .stock-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; padding: 20px 24px 0; }
        .stock-stat { background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 14px; padding: 16px 20px; }
        .stock-stat-n { font-size: 28px; font-weight: 900; line-height: 1; }
        .stock-stat-l { font-size: 12px; color: var(--muted-color, #64748b); font-weight: 600; margin-top: 4px; }
        .stat-ok    .stock-stat-n { color: #16a34a; }
        .stat-warn  .stock-stat-n { color: #f59e0b; }
        .stat-crit  .stock-stat-n { color: #ef4444; }
        .stat-total .stock-stat-n { color: #3b82f6; }

        /* Tabla */
        .stock-table-wrap { padding: 20px 24px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--card-bg, #f8fafc); }
        th { padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--muted-color, #64748b); text-align: left; border-bottom: 1px solid var(--border-color, #e5e7eb); white-space: nowrap; }
        td { padding: 12px 14px; font-size: 14px; color: var(--text-color, #374151); border-bottom: 1px solid var(--border-color, #f1f5f9); vertical-align: middle; }
        tr:hover td { background: var(--hover-bg, #f8fafc); }

        .stock-bar-wrap { width: 100px; }
        .stock-bar { height: 6px; border-radius: 3px; background: var(--border-color, #e5e7eb); overflow: hidden; }
        .stock-bar-fill { height: 100%; border-radius: 3px; transition: width .3s; }
        .stock-num { font-size: 15px; font-weight: 700; }
        .stock-min { font-size: 11px; color: var(--muted-color, #9ca3af); }

        .badge-cat { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-ok   { background: rgba(22,163,74,.1);  color: #16a34a; }
        .badge-warn { background: rgba(245,158,11,.1); color: #d97706; }
        .badge-crit { background: rgba(239,68,68,.1);  color: #dc2626; }

        .cod-barras { font-family: monospace; font-size: 12px; color: var(--muted-color, #9ca3af); }
        .ubicacion-tag { font-size: 11px; color: var(--muted-color, #9ca3af); background: var(--hover-bg, #f1f5f9); padding: 2px 7px; border-radius: 6px; }

        .btn-ajustar { padding: 5px 12px; border-radius: 8px; border: 1px solid var(--border-color, #e5e7eb); background: transparent; font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-color, #374151); transition: all .15s; }
        .btn-ajustar:hover { border-color: #16a34a; color: #16a34a; background: rgba(22,163,74,.06); }

        /* Modal ajuste stock */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: var(--card-bg, #fff); border-radius: 20px; width: 100%; max-width: 420px; overflow: hidden; }
        .modal-head { padding: 20px 24px; border-bottom: 1px solid var(--border-color, #e5e7eb); display: flex; align-items: center; justify-content: space-between; }
        .modal-head h3 { font-size: 16px; font-weight: 700; }
        .modal-body { padding: 24px; display: flex; flex-direction: column; gap: 14px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border-color, #e5e7eb); display: flex; gap: 10px; justify-content: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 12px; font-weight: 600; color: var(--muted-color, #64748b); text-transform: uppercase; letter-spacing: .4px; }
        .form-group input, .form-group select { padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color, #e5e7eb); background: var(--card-bg, #fff); font-size: 14px; color: var(--text-color, #1e293b); outline: none; }
        .form-group input:focus, .form-group select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
        .prod-info-box { background: var(--hover-bg, #f8fafc); border-radius: 10px; padding: 14px; }
        .prod-info-box .nombre { font-size: 15px; font-weight: 700; }
        .prod-info-box .detalle { font-size: 13px; color: var(--muted-color, #64748b); margin-top: 4px; }
        .btn-primary { display: flex; align-items: center; gap: 7px; padding: 9px 18px; background: #16a34a; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: #15803d; }
        .btn-cancel { padding: 9px 18px; border-radius: 10px; border: 1px solid var(--border-color, #e5e7eb); background: transparent; font-size: 14px; cursor: pointer; color: var(--text-color, #374151); }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <!-- Stats -->
        <div class="stock-stats" id="stockStats">
            <div class="stock-stat stat-total"><div class="stock-stat-n" id="statTotal">—</div><div class="stock-stat-l">Total productos</div></div>
            <div class="stock-stat stat-ok"><div class="stock-stat-n" id="statOk">—</div><div class="stock-stat-l">Stock normal</div></div>
            <div class="stock-stat stat-warn"><div class="stock-stat-n" id="statWarn">—</div><div class="stock-stat-l">Stock bajo</div></div>
            <div class="stock-stat stat-crit"><div class="stock-stat-n" id="statCrit">—</div><div class="stock-stat-l">Sin stock</div></div>
        </div>

        <div class="stock-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar producto o código…" oninput="filtrar()">
            </div>
            <div class="filter-tabs">
                <button class="filter-tab active" data-f="todos" onclick="setFiltro('todos',this)">Todos</button>
                <button class="filter-tab warn" data-f="bajo" onclick="setFiltro('bajo',this)">⚠ Stock bajo</button>
                <button class="filter-tab danger" data-f="cero" onclick="setFiltro('cero',this)">🔴 Sin stock</button>
            </div>
        </div>

        <div class="stock-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Código</th>
                        <th>Ubicación</th>
                        <th>Stock</th>
                        <th>Nivel</th>
                        <th>Precio Venta</th>
                        <th>Proveedor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="stockTbody">
                    <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted-color,#9ca3af);"><i class="fas fa-spinner fa-spin"></i> Cargando…</td></tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal ajuste -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Ajustar Stock</h3>
            <button style="background:none;border:none;cursor:pointer;font-size:16px;" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ajProdId">
            <div class="prod-info-box">
                <div class="nombre" id="ajNombre"></div>
                <div class="detalle" id="ajDetalle"></div>
            </div>
            <div class="form-group">
                <label>Tipo de movimiento</label>
                <select id="ajTipo">
                    <option value="entrada">➕ Entrada (recepción de mercadería)</option>
                    <option value="salida">➖ Salida (ajuste / pérdida)</option>
                    <option value="ajuste">📋 Ajuste directo (nuevo valor)</option>
                </select>
            </div>
            <div class="form-group">
                <label id="ajCantLabel">Cantidad a agregar</label>
                <input type="number" id="ajCantidad" min="0" placeholder="0">
            </div>
            <div class="form-group">
                <label>Motivo (opcional)</label>
                <input type="text" id="ajMotivo" placeholder="Ej: Recepción proveedor, pérdida, inventario…">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="guardarAjuste()">
                <i class="fas fa-check"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script>
const API_PROD = '../../api/productos/index.php';
let todosProductos = [];
let filtroActivo = 'todos';

document.getElementById('ajTipo').addEventListener('change', () => {
    const t = document.getElementById('ajTipo').value;
    document.getElementById('ajCantLabel').textContent =
        t === 'ajuste' ? 'Nuevo stock total' : (t === 'entrada' ? 'Cantidad a agregar' : 'Cantidad a restar');
});

async function cargar() {
    const r = await fetch(API_PROD + '?limit=500', { credentials: 'include' });
    const d = await r.json();
    if (!d.success) return;
    todosProductos = d.data?.productos || d.data || [];
    actualizarStats();
    renderizar();
}

function actualizarStats() {
    const total = todosProductos.length;
    const cero  = todosProductos.filter(p => (p.stock||0) <= 0).length;
    const bajo  = todosProductos.filter(p => (p.stock||0) > 0 && (p.stock||0) <= (p.stock_minimo||5)).length;
    const ok    = total - cero - bajo;
    document.getElementById('statTotal').textContent = total;
    document.getElementById('statOk').textContent    = ok;
    document.getElementById('statWarn').textContent  = bajo;
    document.getElementById('statCrit').textContent  = cero;
}

function setFiltro(f, el) {
    filtroActivo = f;
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    renderizar();
}

function filtrar() { renderizar(); }

function renderizar() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    let lista = todosProductos.filter(p => {
        const matchQ = !q || p.nombre.toLowerCase().includes(q) || (p.codigo_barras||'').includes(q);
        const s = p.stock || 0;
        const sm = p.stock_minimo || 5;
        const matchF = filtroActivo === 'todos' ? true
                      : filtroActivo === 'cero' ? s <= 0
                      : s > 0 && s <= sm;
        return matchQ && matchF;
    });

    const tbody = document.getElementById('stockTbody');
    if (!lista.length) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted-color,#9ca3af);">Sin resultados</td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map(p => {
        const s = p.stock || 0;
        const sm = p.stock_minimo || 5;
        const nivel = s <= 0 ? 'crit' : s <= sm ? 'warn' : 'ok';
        const nivelLabel = s <= 0 ? 'Sin stock' : s <= sm ? 'Bajo' : 'Normal';
        const pct = sm > 0 ? Math.min(100, Math.round(s / (sm * 2) * 100)) : 100;
        const barColor = nivel === 'crit' ? '#ef4444' : nivel === 'warn' ? '#f59e0b' : '#16a34a';
        return `<tr>
            <td>
                <div style="font-weight:600;font-size:14px;">${esc(p.nombre)}</div>
                ${p.descripcion ? `<div style="font-size:11px;color:var(--muted-color,#9ca3af);margin-top:2px;">${esc(p.descripcion).substring(0,50)}</div>` : ''}
            </td>
            <td><span class="badge-cat" style="background:${(p.categoria_color||'#64748b')}22;color:${p.categoria_color||'#64748b'}">${esc(p.categoria_nombre||'—')}</span></td>
            <td><span class="cod-barras">${esc(p.codigo_barras||'—')}</span></td>
            <td>${p.ubicacion ? `<span class="ubicacion-tag">${esc(p.ubicacion)}</span>` : '—'}</td>
            <td>
                <div class="stock-num" style="color:${barColor}">${s}</div>
                <div class="stock-min">mín. ${sm}</div>
            </td>
            <td>
                <div class="stock-bar-wrap">
                    <span class="badge-${nivel}">${nivelLabel}</span>
                    <div class="stock-bar" style="margin-top:5px;">
                        <div class="stock-bar-fill" style="width:${pct}%;background:${barColor};"></div>
                    </div>
                </div>
            </td>
            <td style="font-weight:600;">$${Number(p.precio_venta||0).toLocaleString('es-AR')}</td>
            <td style="font-size:13px;color:var(--muted-color,#64748b);">${esc(p.proveedor_nombre||'—')}</td>
            <td><button class="btn-ajustar" onclick="abrirAjuste(${p.id},'${esc(p.nombre)}',${s},${sm})">Ajustar</button></td>
        </tr>`;
    }).join('');
}

function abrirAjuste(id, nombre, stock, stockMin) {
    document.getElementById('ajProdId').value = id;
    document.getElementById('ajNombre').textContent = nombre;
    document.getElementById('ajDetalle').textContent = `Stock actual: ${stock} unidades · Mínimo: ${stockMin}`;
    document.getElementById('ajCantidad').value = '';
    document.getElementById('ajMotivo').value = '';
    document.getElementById('ajTipo').value = 'entrada';
    document.getElementById('ajCantLabel').textContent = 'Cantidad a agregar';
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('ajCantidad').focus();
}

function cerrarModal() { document.getElementById('modalOverlay').classList.remove('open'); }

async function guardarAjuste() {
    const id    = parseInt(document.getElementById('ajProdId').value);
    const tipo  = document.getElementById('ajTipo').value;
    const cant  = parseFloat(document.getElementById('ajCantidad').value) || 0;
    const prod  = todosProductos.find(p => p.id == id);
    if (!prod) return;
    let nuevoStock = prod.stock || 0;
    if (tipo === 'entrada')  nuevoStock += cant;
    else if (tipo === 'salida') nuevoStock = Math.max(0, nuevoStock - cant);
    else nuevoStock = cant;

    const r = await fetch(API_PROD, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id, stock: nuevoStock })
    });
    const d = await r.json();
    if (d.success) {
        prod.stock = nuevoStock;
        cerrarModal();
        actualizarStats();
        renderizar();
    } else { alert(d.message || 'Error al guardar'); }
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('modalOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('modalOverlay')) cerrarModal();
});

cargar();
</script>
</body>
</html>
