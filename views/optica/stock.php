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
    <title>Stock — Óptica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --opt:#0ea5e9; --opt-dark:#0284c7; --opt-light:rgba(14,165,233,.1); }

        .opt-toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .opt-toolbar h1 { margin:0; font-size:20px; font-weight:700; color:var(--text-primary); }
        .opt-toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        /* Stats cards */
        .stats-grid {
            display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));
            gap:12px; padding:16px 24px;
        }
        .stat-card {
            background:var(--surface); border:1.5px solid var(--border);
            border-radius:14px; padding:14px 16px;
            display:flex; flex-direction:column; justify-content:space-between; min-width:0;
        }
        .stat-card .sc-icon {
            width:32px; height:32px; border-radius:9px;
            display:flex; align-items:center; justify-content:center;
            font-size:14px; margin-bottom:8px; flex-shrink:0;
            background:var(--opt-light); color:var(--opt);
        }
        .stat-card .sc-val {
            font-size:17px; font-weight:800; color:var(--text-primary);
            line-height:1.3; word-break:break-word; min-width:0;
        }
        .stat-card .sc-lbl {
            font-size:10px; color:var(--text-secondary); margin-top:3px;
            font-weight:700; text-transform:uppercase; letter-spacing:.3px; line-height:1.3;
        }

        /* Filtros */
        .filter-bar { padding:0 24px 14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .filter-input {
            flex:1; min-width:180px; padding:9px 14px 9px 36px;
            border:1.5px solid var(--border); border-radius:10px;
            font-size:13px; background:var(--surface); color:var(--text-primary);
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:10px center;
        }
        .filter-input:focus { outline:none; border-color:var(--opt); }
        .tipo-btn {
            padding:8px 14px; border-radius:20px; font-size:12px; font-weight:600;
            border:1.5px solid var(--border); background:var(--surface);
            color:var(--text-secondary); cursor:pointer; transition:all .15s;
        }
        .tipo-btn.active { background:var(--opt); border-color:var(--opt); color:#fff; }
        .bajo-btn {
            padding:8px 14px; border-radius:20px; font-size:12px; font-weight:600;
            border:1.5px solid #f59e0b; background:rgba(245,158,11,.1);
            color:#d97706; cursor:pointer; transition:all .15s;
        }
        .bajo-btn.active { background:#f59e0b; color:#fff; }

        /* Tabla stock */
        .stock-tabla-wrap {
            margin:0 24px 24px; border:1.5px solid var(--border);
            border-radius:14px; overflow:hidden;
        }
        .stock-tabla { width:100%; border-collapse:collapse; }
        .stock-tabla th {
            padding:10px 14px; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.5px;
            color:var(--text-secondary); background:var(--background);
            border-bottom:1px solid var(--border); text-align:left;
        }
        .stock-tabla td {
            padding:11px 14px; font-size:13px; color:var(--text-primary);
            border-bottom:1px solid var(--border); vertical-align:middle;
        }
        .stock-tabla tr:last-child td { border-bottom:none; }
        .stock-tabla tr:hover td { background:var(--background); }

        .tipo-badge {
            display:inline-flex; align-items:center; padding:3px 9px;
            border-radius:20px; font-size:11px; font-weight:700;
        }
        .tipo-montura  { background:rgba(14,165,233,.12); color:#0369a1; }
        .tipo-lente    { background:rgba(99,102,241,.12);  color:#4f46e5; }
        .tipo-contacto { background:rgba(245,158,11,.12);  color:#d97706; }
        .tipo-accesorio{ background:rgba(100,116,139,.12); color:#475569; }

        .stock-num { font-size:16px; font-weight:800; }
        .stock-ok  { color:#059669; }
        .stock-low { color:#d97706; }
        .stock-out { color:#dc2626; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:560px; max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:var(--surface); z-index:2; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px; }
        .fi { width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-primary); box-sizing:border-box; }
        .fi:focus { outline:none; border-color:var(--opt); }
        .fg-grid  { display:grid; grid-template-columns:1fr 1fr;     gap:12px; }
        .fg-grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }

        /* Modal movimiento */
        .mov-item-info { background:var(--opt-light); border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:13px; }
        .mov-item-info strong { display:block; font-size:15px; margin-bottom:4px; }

        /* Drawer lateral para historial */
        .drawer-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:900; }
        .drawer-overlay.open { display:block; }
        .drawer {
            position:fixed; right:-420px; top:0; bottom:0; width:100%; max-width:420px;
            background:var(--surface); border-left:1px solid var(--border);
            z-index:901; transition:right .3s; overflow-y:auto; padding-bottom:32px;
        }
        .drawer.open { right:0; }
        .drawer-header { padding:18px 20px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:var(--surface); }
        .drawer-header h3 { margin:0; font-size:16px; font-weight:700; }
        .mov-row {
            display:flex; justify-content:space-between; align-items:center;
            padding:10px 20px; border-bottom:1px solid var(--border); font-size:13px;
        }
        .mov-tipo-entrada { color:#059669; font-weight:700; }
        .mov-tipo-salida  { color:#dc2626; font-weight:700; }
        .mov-tipo-ajuste  { color:#d97706; font-weight:700; }

        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; pointer-events:none; }
        .toast.show { opacity:1; }

        .empty-state { text-align:center; padding:48px 24px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }

        @media(max-width:700px) {
            .stock-tabla th:nth-child(3),
            .stock-tabla td:nth-child(3),
            .stock-tabla th:nth-child(5),
            .stock-tabla td:nth-child(5) { display:none; }
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
        <?php include '../includes/header.php'; ?>

        <!-- Toolbar -->
        <div class="opt-toolbar">
            <div>
                <h1><i class="fas fa-box-open" style="color:var(--opt);margin-right:8px;"></i>Stock</h1>
                <p id="subtitulo">Inventario de monturas, lentes y accesorios</p>
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="abrirModalItem()">
                    <i class="fas fa-plus"></i> Nuevo Item
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card" style="opacity:.3;"><div class="sc-icon"><i class="fas fa-spinner fa-spin"></i></div><div class="sc-val">—</div><div class="sc-lbl">Cargando</div></div>
        </div>

        <!-- Filtros -->
        <div class="filter-bar">
            <input class="filter-input" type="text" id="buscar" placeholder="Buscar por nombre, marca o código…" oninput="aplicarFiltros()">
            <button class="tipo-btn active" onclick="setTipo('',this)">Todos</button>
            <button class="tipo-btn" onclick="setTipo('montura',this)">Monturas</button>
            <button class="tipo-btn" onclick="setTipo('lente',this)">Lentes</button>
            <button class="tipo-btn" onclick="setTipo('contacto',this)">Contacto</button>
            <button class="tipo-btn" onclick="setTipo('accesorio',this)">Accesorios</button>
            <button class="bajo-btn" id="btnBajo" onclick="toggleBajo()">⚠️ Stock Bajo</button>
        </div>

        <!-- Tabla -->
        <div class="stock-tabla-wrap">
            <table class="stock-tabla">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Marca / Modelo</th>
                        <th style="text-align:center;">Stock</th>
                        <th>Precio venta</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="stockTbody">
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-spinner fa-spin" style="opacity:1;color:var(--opt);"></i>
                            <p>Cargando inventario…</p>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Item -->
<div class="modal-overlay" id="modalItem">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-box-open" style="color:var(--opt);margin-right:8px;"></i>Nuevo Item</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="itemId">
            <div class="fg">
                <label>Tipo <span style="color:#ef4444;">*</span></label>
                <select class="fi" id="itemTipo">
                    <option value="montura">Montura</option>
                    <option value="lente">Lente</option>
                    <option value="contacto">Lente de contacto</option>
                    <option value="accesorio">Accesorio</option>
                </select>
            </div>
            <div class="fg">
                <label>Nombre <span style="color:#ef4444;">*</span></label>
                <input class="fi" type="text" id="itemNombre" placeholder="Ej: Ray-Ban Wayfarer">
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Marca</label>
                    <input class="fi" type="text" id="itemMarca" placeholder="Ray-Ban, Oakley…">
                </div>
                <div class="fg">
                    <label>Modelo</label>
                    <input class="fi" type="text" id="itemModelo" placeholder="RB2140…">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Color</label>
                    <input class="fi" type="text" id="itemColor" placeholder="Negro, Marrón…">
                </div>
                <div class="fg">
                    <label>Material</label>
                    <input class="fi" type="text" id="itemMaterial" placeholder="Acetato, Metal…">
                </div>
            </div>
            <div class="fg">
                <label>Código / SKU</label>
                <input class="fi" type="text" id="itemCodigo" placeholder="COD-001">
            </div>
            <div class="fg">
                <label>Descripción</label>
                <input class="fi" type="text" id="itemDesc" placeholder="Descripción opcional…">
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Precio costo</label>
                    <input class="fi" type="number" id="itemPcosto" placeholder="0" min="0" step="100">
                </div>
                <div class="fg">
                    <label>Precio venta</label>
                    <input class="fi" type="number" id="itemPventa" placeholder="0" min="0" step="100">
                </div>
            </div>
            <div class="fg-grid" id="fgStockInicial">
                <div class="fg">
                    <label>Stock inicial</label>
                    <input class="fi" type="number" id="itemStock" placeholder="0" min="0">
                </div>
                <div class="fg">
                    <label>Stock mínimo</label>
                    <input class="fi" type="number" id="itemMinimo" placeholder="2" min="0">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" id="btnGuardar" onclick="guardarItem()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- Modal Movimiento -->
<div class="modal-overlay" id="modalMov">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3 id="modalMovTitulo"><i class="fas fa-exchange-alt" style="color:var(--opt);margin-right:8px;"></i>Movimiento</h3>
            <button class="modal-close" onclick="cerrarModalMov()">✕</button>
        </div>
        <div class="modal-body">
            <div class="mov-item-info">
                <strong id="movItemNombre">—</strong>
                <span>Stock actual: <strong id="movStockActual">—</strong></span>
            </div>
            <input type="hidden" id="movItemId">
            <div class="fg">
                <label>Tipo de movimiento</label>
                <select class="fi" id="movTipo">
                    <option value="entrada">Entrada (aumenta stock)</option>
                    <option value="salida">Salida (baja stock)</option>
                    <option value="ajuste">Ajuste manual</option>
                </select>
            </div>
            <div class="fg">
                <label>Cantidad</label>
                <input class="fi" type="number" id="movCantidad" placeholder="Ej: 5" min="1">
            </div>
            <div class="fg">
                <label>Notas (opcional)</label>
                <input class="fi" type="text" id="movNotas" placeholder="Motivo del movimiento…">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalMov()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarMov()">
                <i class="fas fa-check"></i> Registrar
            </button>
        </div>
    </div>
</div>

<!-- Drawer historial -->
<div class="drawer-overlay" id="drawerOverlay" onclick="cerrarHistorial()"></div>
<div class="drawer" id="drawerHistorial">
    <div class="drawer-header">
        <h3><i class="fas fa-history" style="color:var(--opt);margin-right:8px;"></i>Historial</h3>
        <button class="modal-close" onclick="cerrarHistorial()">✕</button>
    </div>
    <div id="drawerContent" style="padding:8px 0;"></div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE      = '<?= $base ?>';
const API_STOCK = BASE + '/api/optica/stock.php';

let allItems = [];
let filtroTipo = '';
let filtroBajo = false;

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    await cargarStock();
}

async function cargarStock() {
    const r = await fetch(API_STOCK, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error al cargar stock', 'error'); return; }
    allItems = j.data.items || [];
    renderStats(j.data.stats);
    renderTabla(allItems);
}

function renderStats(s) {
    if (!s) return;
    const items = [
        {icon:'fas fa-box-open', val:s.total_items, lbl:'Items totales', color:'var(--opt-light);color:var(--opt)'},
        {icon:'fas fa-exclamation-triangle', val:s.stock_bajo, lbl:'Stock bajo', color:'rgba(245,158,11,.1);color:#d97706'},
        {icon:'fas fa-ban', val:s.sin_stock, lbl:'Sin stock', color:'rgba(239,68,68,.1);color:#dc2626'},
        {icon:'fas fa-glasses', val:s.monturas, lbl:'Monturas', color:'var(--opt-light);color:var(--opt)'},
        {icon:'fas fa-circle', val:s.lentes, lbl:'Lentes', color:'rgba(99,102,241,.1);color:#4f46e5'},
        {icon:'fas fa-dollar-sign', val:fmtCompact(s.valor_inventario), lbl:'Valor inventario', color:'rgba(15,209,134,.1);color:#059669'},
    ];
    document.getElementById('statsGrid').innerHTML = items.map(i => `
        <div class="stat-card">
            <div class="sc-icon" style="background:${i.color.split(';')[0]};${i.color.split(';')[1]||''}">
                <i class="${i.icon}"></i>
            </div>
            <div class="sc-val">${i.val ?? 0}</div>
            <div class="sc-lbl">${i.lbl}</div>
        </div>
    `).join('');
}

function renderTabla(items) {
    const tbody = document.getElementById('stockTbody');
    if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state">
            <i class="fas fa-box-open"></i><p>No hay items en el inventario</p>
        </div></td></tr>`;
        return;
    }
    tbody.innerHTML = items.map(i => {
        const stNum  = parseInt(i.stock_actual);
        const stMin  = parseInt(i.stock_minimo) || 2;
        const stCls  = stNum === 0 ? 'stock-out' : stNum <= stMin ? 'stock-low' : 'stock-ok';
        const stIco  = stNum === 0 ? '⛔' : stNum <= stMin ? '⚠️' : '✓';
        return `<tr>
            <td>
                <div style="font-weight:700;">${esc(i.nombre)}</div>
                ${i.codigo ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(i.codigo)}</div>` : ''}
                ${i.color ? `<div style="font-size:11px;color:var(--text-secondary);">Color: ${esc(i.color)}</div>` : ''}
            </td>
            <td><span class="tipo-badge tipo-${i.tipo}">${tipoLabel(i.tipo)}</span></td>
            <td>
                ${i.marca ? `<strong>${esc(i.marca)}</strong>` : '—'}
                ${i.modelo ? ` <span style="color:var(--text-secondary);">${esc(i.modelo)}</span>` : ''}
            </td>
            <td style="text-align:center;">
                <span class="stock-num ${stCls}">${stIco} ${stNum}</span>
                <div style="font-size:11px;color:var(--text-secondary);">mín ${stMin}</div>
            </td>
            <td>${i.precio_venta > 0 ? fmt(i.precio_venta) : '—'}</td>
            <td>
                <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                    <button class="btn btn-secondary" style="font-size:11px;padding:5px 8px;" onclick='abrirModalMov(${JSON.stringify({id:i.id,nombre:i.nombre,stock:i.stock_actual})})' title="Movimiento">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                    <button class="btn btn-secondary" style="font-size:11px;padding:5px 8px;" onclick="verHistorial(${i.id},'${esc(i.nombre)}')" title="Historial">
                        <i class="fas fa-history"></i>
                    </button>
                    <button class="btn btn-secondary" style="font-size:11px;padding:5px 8px;" onclick='editarItem(${JSON.stringify(i)})' title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn btn-secondary" style="font-size:11px;padding:5px 8px;color:#ef4444;" onclick="eliminarItem(${i.id},'${esc(i.nombre)}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── Filtros ───────────────────────────────────────────────────────────────────
function setTipo(tipo, btn) {
    filtroTipo = tipo;
    document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    aplicarFiltros();
}

function toggleBajo() {
    filtroBajo = !filtroBajo;
    document.getElementById('btnBajo').classList.toggle('active', filtroBajo);
    aplicarFiltros();
}

function aplicarFiltros() {
    const q = document.getElementById('buscar').value.trim().toLowerCase();
    let items = allItems;
    if (filtroTipo) items = items.filter(i => i.tipo === filtroTipo);
    if (filtroBajo) items = items.filter(i => parseInt(i.stock_actual) <= parseInt(i.stock_minimo));
    if (q) items = items.filter(i =>
        (i.nombre||'').toLowerCase().includes(q) ||
        (i.marca||'').toLowerCase().includes(q)  ||
        (i.modelo||'').toLowerCase().includes(q) ||
        (i.codigo||'').toLowerCase().includes(q)
    );
    renderTabla(items);
}

// ── Modal Item ────────────────────────────────────────────────────────────────
function abrirModalItem() {
    document.getElementById('itemId').value = '';
    document.getElementById('itemTipo').value    = 'montura';
    document.getElementById('itemNombre').value  = '';
    document.getElementById('itemMarca').value   = '';
    document.getElementById('itemModelo').value  = '';
    document.getElementById('itemColor').value   = '';
    document.getElementById('itemMaterial').value= '';
    document.getElementById('itemCodigo').value  = '';
    document.getElementById('itemDesc').value    = '';
    document.getElementById('itemPcosto').value  = '';
    document.getElementById('itemPventa').value  = '';
    document.getElementById('itemStock').value   = '0';
    document.getElementById('itemMinimo').value  = '2';
    document.getElementById('fgStockInicial').style.display = '';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-box-open" style="color:var(--opt);margin-right:8px;"></i>Nuevo Item';
    document.getElementById('modalItem').classList.add('open');
}

function editarItem(i) {
    document.getElementById('itemId').value      = i.id;
    document.getElementById('itemTipo').value    = i.tipo;
    document.getElementById('itemNombre').value  = i.nombre || '';
    document.getElementById('itemMarca').value   = i.marca || '';
    document.getElementById('itemModelo').value  = i.modelo || '';
    document.getElementById('itemColor').value   = i.color || '';
    document.getElementById('itemMaterial').value= i.material || '';
    document.getElementById('itemCodigo').value  = i.codigo || '';
    document.getElementById('itemDesc').value    = i.descripcion || '';
    document.getElementById('itemPcosto').value  = i.precio_costo || '';
    document.getElementById('itemPventa').value  = i.precio_venta || '';
    document.getElementById('itemMinimo').value  = i.stock_minimo || '';
    document.getElementById('fgStockInicial').style.display = 'none';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-pen" style="color:var(--opt);margin-right:8px;"></i>Editar Item';
    document.getElementById('modalItem').classList.add('open');
}

function cerrarModal() { document.getElementById('modalItem').classList.remove('open'); }

async function guardarItem() {
    const id     = document.getElementById('itemId').value;
    const nombre = document.getElementById('itemNombre').value.trim();
    if (!nombre) { toast('El nombre es requerido', 'error'); return; }

    const datos = {
        tipo          : document.getElementById('itemTipo').value,
        nombre,
        marca         : document.getElementById('itemMarca').value.trim()    || null,
        modelo        : document.getElementById('itemModelo').value.trim()   || null,
        color         : document.getElementById('itemColor').value.trim()    || null,
        material      : document.getElementById('itemMaterial').value.trim() || null,
        codigo        : document.getElementById('itemCodigo').value.trim()   || null,
        descripcion   : document.getElementById('itemDesc').value.trim()     || null,
        precio_costo  : parseFloat(document.getElementById('itemPcosto').value) || 0,
        precio_venta  : parseFloat(document.getElementById('itemPventa').value) || 0,
        stock_minimo  : parseInt(document.getElementById('itemMinimo').value)    || 2,
    };
    if (!id) datos.stock_actual = parseInt(document.getElementById('itemStock').value) || 0;

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    const url    = id ? `${API_STOCK}?id=${id}` : API_STOCK;
    const method = id ? 'PUT' : 'POST';
    const r = await fetch(url, { method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(datos), credentials:'include' });
    const j = await r.json();
    btn.disabled = false;

    if (!j.success) { toast(j.message || 'Error al guardar', 'error'); return; }
    toast(id ? 'Item actualizado' : 'Item creado');
    cerrarModal();
    await cargarStock();
}

async function eliminarItem(id, nombre) {
    if (!confirm(`¿Eliminar "${nombre}" del inventario?`)) return;
    const r = await fetch(`${API_STOCK}?id=${id}`, {method:'DELETE', credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error al eliminar', 'error'); return; }
    toast('Item eliminado');
    await cargarStock();
}

// ── Modal Movimiento ──────────────────────────────────────────────────────────
function abrirModalMov({id, nombre, stock}) {
    document.getElementById('movItemId').value = id;
    document.getElementById('movItemNombre').textContent = nombre;
    document.getElementById('movStockActual').textContent = stock;
    document.getElementById('movTipo').value = 'entrada';
    document.getElementById('movCantidad').value = '';
    document.getElementById('movNotas').value = '';
    document.getElementById('modalMov').classList.add('open');
}
function cerrarModalMov() { document.getElementById('modalMov').classList.remove('open'); }

async function guardarMov() {
    const itemId = parseInt(document.getElementById('movItemId').value);
    const tipo   = document.getElementById('movTipo').value;
    const cant   = parseInt(document.getElementById('movCantidad').value);
    const notas  = document.getElementById('movNotas').value.trim();
    if (!cant || cant <= 0) { toast('Ingresá una cantidad válida', 'error'); return; }

    const r = await fetch(`${API_STOCK}?accion=mov`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({item_id:itemId, tipo, cantidad:cant, notas}),
        credentials:'include'
    });
    const j = await r.json();
    if (!j.success) { toast(j.message || 'Error', 'error'); return; }
    toast(`Stock actualizado → ${j.data?.stock_nuevo}`);
    cerrarModalMov();
    await cargarStock();
}

// ── Historial (drawer) ────────────────────────────────────────────────────────
async function verHistorial(itemId, nombre) {
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('drawerHistorial').classList.add('open');
    const dc = document.getElementById('drawerContent');
    dc.innerHTML = `<div style="padding:20px;text-align:center;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></div>`;

    const r = await fetch(`${API_STOCK}?accion=movimientos&item_id=${itemId}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success || !j.data.length) {
        dc.innerHTML = `<div style="padding:20px;text-align:center;color:var(--text-secondary);">Sin movimientos registrados</div>`;
        return;
    }
    dc.innerHTML = j.data.map(m => `
        <div class="mov-row">
            <div>
                <span class="mov-tipo-${m.tipo}">${m.tipo.toUpperCase()}</span>
                <span style="margin-left:8px;font-weight:700;">${m.cantidad > 0 ? '+':''}${m.cantidad}</span>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                    ${m.notas ? esc(m.notas) + ' · ' : ''}${m.usuario_nombre || ''}
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;color:var(--text-secondary);">${new Date(m.created_at).toLocaleDateString('es-AR')}</div>
                <div style="font-size:12px;">${m.stock_anterior} → <strong>${m.stock_nuevo}</strong></div>
            </div>
        </div>
    `).join('');
}
function cerrarHistorial() {
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('drawerHistorial').classList.remove('open');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function tipoLabel(t) {
    return {montura:'Montura', lente:'Lente', contacto:'Contacto', accesorio:'Accesorio'}[t] || t;
}
function fmt(n)  { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function fmtCompact(n) {
    const v = Number(n||0);
    if (v >= 1000000) return '$' + (v/1000000).toLocaleString('es-AR',{minimumFractionDigits:1,maximumFractionDigits:1}) + 'M';
    if (v >= 10000)   return '$' + Math.round(v/1000).toLocaleString('es-AR') + 'k';
    return '$' + v.toLocaleString('es-AR',{minimumFractionDigits:0});
}
function esc(s)  { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,'&#39;'); }
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
