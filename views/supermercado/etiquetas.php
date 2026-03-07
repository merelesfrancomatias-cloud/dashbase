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
    <title>Etiquetas / Balanza — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root { --super: #0FD186; }
        .app-layout { display:flex; min-height:100vh; }

        /* Paneles */
        .etiq-layout { display:grid; grid-template-columns:380px 1fr; gap:20px; }
        @media (max-width:900px) { .etiq-layout { grid-template-columns:1fr; } }

        /* Panel izq: configuración */
        .panel-config { display:flex; flex-direction:column; gap:16px; }

        /* Selector de productos */
        .producto-item {
            display:flex; align-items:center; gap:10px; padding:10px 14px;
            border-radius:10px; border:1px solid var(--border); background:var(--surface);
            cursor:pointer; transition:all .15s;
        }
        .producto-item:hover { border-color:var(--super); }
        .producto-item.selected { border-color:var(--super); background:rgba(15,209,134,.06); }
        .prod-check { width:20px; height:20px; border-radius:6px; border:2px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:11px; flex-shrink:0; transition:all .15s; }
        .producto-item.selected .prod-check { background:var(--super); border-color:var(--super); color:white; }
        .prod-nombre { font-size:13px; font-weight:600; color:var(--text-primary); }
        .prod-cat    { font-size:11px; color:var(--text-secondary); }
        .prod-precio { margin-left:auto; font-size:14px; font-weight:700; color:var(--super); white-space:nowrap; }
        .prod-qty    { display:flex; align-items:center; gap:6px; flex-shrink:0; }
        .prod-qty button { width:24px; height:24px; border-radius:6px; border:1px solid var(--border); background:var(--background); cursor:pointer; font-size:13px; display:flex; align-items:center; justify-content:center; }
        .prod-qty button:hover { background:var(--super); color:white; border-color:var(--super); }
        .prod-qty span { font-size:13px; font-weight:700; min-width:20px; text-align:center; }

        /* Preview etiquetas */
        .preview-area {
            background: var(--surface); border:1px solid var(--border); border-radius:14px;
            min-height:400px; display:flex; flex-direction:column;
        }
        .preview-toolbar {
            padding:14px 18px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
        }
        .preview-grid {
            padding:20px; display:flex; flex-wrap:wrap; gap:10px; align-items:flex-start;
        }

        /* Etiqueta individual */
        .etiqueta {
            border:1px solid #ccc; border-radius:4px; padding:6px 8px;
            display:inline-flex; flex-direction:column; align-items:center;
            background:white; color:#000; font-family:'Arial', sans-serif;
            position:relative; box-sizing:border-box; break-inside:avoid;
        }
        .etiq-negocio { font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:2px; color:#333; }
        .etiq-nombre  { font-size:9px; font-weight:700; text-align:center; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom:3px; }
        .etiq-precio  { font-size:14px; font-weight:900; color:#000; margin:2px 0; }
        .etiq-precio .sym { font-size:9px; vertical-align:top; margin-top:2px; font-weight:700; }
        .etiq-barcode { display:block; }
        .etiq-codigo  { font-size:8px; color:#555; margin-top:1px; }
        .etiq-vencimiento { font-size:7px; color:#ef4444; margin-top:2px; }

        /* Tamaños */
        .etiq-sm { width:60mm; }
        .etiq-md { width:80mm; }
        .etiq-lg { width:100mm; }

        /* Opciones de formato */
        .formato-btn {
            padding:6px 14px; border:1px solid var(--border); border-radius:8px;
            background:var(--background); cursor:pointer; font-size:13px; font-weight:600;
            color:var(--text-secondary); transition:all .15s;
        }
        .formato-btn.active { background:var(--super); color:white; border-color:var(--super); }

        /* Checkboxes de opciones */
        .opt-row { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-primary); cursor:pointer; }
        .opt-row input[type=checkbox] { width:16px; height:16px; accent-color:var(--super); cursor:pointer; }

        /* Búsqueda */
        .search-box { position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input { padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; width:100%; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .search-box input:focus { outline:none; border-color:var(--super); }

        /* Filtro categoría */
        select.cat-filter { padding:7px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); color:var(--text-primary); }
        select.cat-filter:focus { outline:none; border-color:var(--super); }

        .empty-state { text-align:center; padding:40px 24px; color:var(--text-secondary); width:100%; }
        .empty-state i { font-size:36px; opacity:.15; display:block; margin-bottom:10px; }

        /* Print */
        @media print {
            .app-layout > aside, .main-content > header, .container > .card:first-child,
            .etiq-layout > .panel-config, .preview-toolbar, .no-print { display:none !important; }
            .main-content { margin:0 !important; padding:0 !important; }
            .container { padding:0 !important; }
            .preview-area { border:none; border-radius:0; }
            .preview-grid { padding:4mm; gap:4mm; }
            .etiqueta { border-color:#999; }
            body { background:white !important; }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Page header -->
            <div class="card no-print" style="margin-bottom:20px;padding:18px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-tag" style="color:var(--super);margin-right:8px;"></i>Etiquetas / Balanza
                        </h1>
                        <p style="margin:4px 0 0;font-size:14px;color:var(--text-secondary);">Generá etiquetas con código de barras para tus productos</p>
                    </div>
                    <button onclick="window.print()" class="btn btn-primary" style="background:var(--super);border-color:var(--super);" id="btnImprimir" disabled>
                        <i class="fas fa-print"></i> Imprimir etiquetas
                    </button>
                </div>
            </div>

            <div class="etiq-layout">

                <!-- Panel izquierdo: selección y config -->
                <div class="panel-config no-print">

                    <!-- Opciones de etiqueta -->
                    <div class="card" style="padding:18px;">
                        <h3 style="margin:0 0 14px;font-size:14px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-sliders-h" style="color:var(--super);margin-right:6px;"></i>Formato de etiqueta
                        </h3>
                        <div style="margin-bottom:12px;">
                            <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Tamaño</div>
                            <div style="display:flex;gap:6px;">
                                <button class="formato-btn active" data-size="sm" onclick="setSize('sm',this)">Pequeño (60mm)</button>
                                <button class="formato-btn" data-size="md" onclick="setSize('md',this)">Mediano (80mm)</button>
                                <button class="formato-btn" data-size="lg" onclick="setSize('lg',this)">Grande (100mm)</button>
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <label class="opt-row"><input type="checkbox" id="optNegocio" checked onchange="renderPreview()"> Mostrar nombre del negocio</label>
                            <label class="opt-row"><input type="checkbox" id="optBarcode" checked onchange="renderPreview()"> Código de barras</label>
                            <label class="opt-row"><input type="checkbox" id="optPrecio"  checked onchange="renderPreview()"> Precio</label>
                            <label class="opt-row"><input type="checkbox" id="optVenc"  onchange="renderPreview()"> Fecha de vencimiento (si tiene)</label>
                        </div>
                    </div>

                    <!-- Buscar y seleccionar productos -->
                    <div class="card" style="padding:18px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                            <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--text-primary);">
                                <i class="fas fa-box" style="color:var(--super);margin-right:6px;"></i>Seleccionar productos
                            </h3>
                            <div style="display:flex;gap:6px;">
                                <button onclick="seleccionarTodos()" style="padding:5px 10px;background:rgba(15,209,134,.1);border:none;border-radius:7px;cursor:pointer;font-size:12px;color:var(--super);font-weight:600;">Todos</button>
                                <button onclick="deseleccionarTodos()" style="padding:5px 10px;background:var(--background);border:1px solid var(--border);border-radius:7px;cursor:pointer;font-size:12px;color:var(--text-secondary);font-weight:600;">Ninguno</button>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;margin-bottom:10px;">
                            <div class="search-box" style="flex:1;">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchProd" placeholder="Buscar producto…" oninput="filtrarProductos()">
                            </div>
                            <select class="cat-filter" id="catFilter" onchange="filtrarProductos()">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>
                        <div id="prodList" style="max-height:340px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;">
                            <div style="text-align:center;padding:20px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                    </div>

                </div>

                <!-- Panel derecho: preview -->
                <div class="preview-area">
                    <div class="preview-toolbar">
                        <div>
                            <span style="font-size:14px;font-weight:700;color:var(--text-primary);">
                                <i class="fas fa-eye" style="color:var(--super);margin-right:6px;"></i>Vista previa
                            </span>
                            <span style="font-size:12px;color:var(--text-secondary);margin-left:8px;" id="previewCount"></span>
                        </div>
                        <button onclick="window.print()" class="btn btn-primary" style="background:var(--super);border-color:var(--super);padding:7px 16px;font-size:13px;" id="btnImprimirPrev" disabled>
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                    <div class="preview-grid" id="previewGrid">
                        <div class="empty-state">
                            <i class="fas fa-tag"></i>
                            <p>Seleccioná productos de la izquierda para ver la vista previa</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const API_PROD = '../../api/productos/index.php';
const API_CATS = '../../api/categorias/index.php';

let todos        = [];
let categorias   = [];
let seleccionados= {}; // { id: cantidad }
let etiqSize     = 'sm';
let negocioNombre= '';

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    const [rp, rc] = await Promise.all([
        fetch(API_PROD, {credentials:'include'}),
        fetch(API_CATS, {credentials:'include'}),
    ]);
    const jp = await rp.json();
    const jc = await rc.json();
    // La API devuelve { success, data: { productos: [...], estadisticas: {} } }
    const rawProds = jp.success ? (jp.data?.productos ?? jp.data ?? []) : [];
    todos      = (Array.isArray(rawProds) ? rawProds : []).filter(p => parseInt(p.activo)===1);
    categorias = jc.success ? (jc.data||[]) : [];
    console.log('[Etiquetas] Productos cargados:', todos.length, '| Categorías:', categorias.length);
    if (!jp.success) console.warn('[Etiquetas] Error API productos:', jp);
    // Nombre del negocio desde localStorage o session
    try { negocioNombre = JSON.parse(localStorage.getItem('user')||'{}').negocio_nombre || ''; } catch(e) {}
    // Poblar filtro de categorías
    const sel = document.getElementById('catFilter');
    sel.innerHTML = '<option value="">Todas las categorías</option>' +
        categorias.map(c => `<option value="${c.id}">${esc(c.nombre)}</option>`).join('');
    filtrarProductos();
}

// ── Lista de productos ────────────────────────────────────────────────────────
function filtrarProductos() {
    const q    = document.getElementById('searchProd').value.toLowerCase().trim();
    const catId= document.getElementById('catFilter').value;
    let lista  = todos;
    if (q)     lista = lista.filter(p => (p.nombre||'').toLowerCase().includes(q));
    if (catId) lista = lista.filter(p => p.categoria_id == catId);
    renderProdList(lista);
}

function renderProdList(lista) {
    const div = document.getElementById('prodList');
    if (!lista.length) {
        div.innerHTML = '<div class="empty-state" style="padding:20px;"><i class="fas fa-box"></i><p>Sin resultados</p></div>';
        return;
    }
    div.innerHTML = lista.map(p => {
        const sel = !!seleccionados[p.id];
        const qty = seleccionados[p.id] || 1;
        return `
        <div class="producto-item ${sel?'selected':''}" id="pitem-${p.id}" onclick="toggleProd(${p.id})">
            <div class="prod-check"><i class="fas fa-check" style="${sel?'':'display:none'}"></i></div>
            <div style="flex:1;min-width:0;">
                <div class="prod-nombre">${esc(p.nombre)}</div>
                <div class="prod-cat">${esc(catNombre(p.categoria_id))}${p.codigo_barras ? ' · ' + esc(p.codigo_barras) : ''}</div>
            </div>
            <span class="prod-precio">${fmt(p.precio_venta)}</span>
            ${sel ? `<div class="prod-qty" onclick="event.stopPropagation()">
                <button onclick="cambiarQty(${p.id},-1)">−</button>
                <span id="qty-${p.id}">${qty}</span>
                <button onclick="cambiarQty(${p.id},1)">+</button>
            </div>` : ''}
        </div>`;
    }).join('');
}

function toggleProd(id) {
    if (seleccionados[id]) {
        delete seleccionados[id];
    } else {
        seleccionados[id] = 1;
    }
    filtrarProductos();
    renderPreview();
    actualizarBtnImprimir();
}

function cambiarQty(id, delta) {
    if (!seleccionados[id]) return;
    seleccionados[id] = Math.max(1, (seleccionados[id]||1) + delta);
    document.getElementById(`qty-${id}`).textContent = seleccionados[id];
    renderPreview();
}

function seleccionarTodos() {
    todos.forEach(p => { if (!seleccionados[p.id]) seleccionados[p.id] = 1; });
    filtrarProductos();
    renderPreview();
    actualizarBtnImprimir();
}

function deseleccionarTodos() {
    seleccionados = {};
    filtrarProductos();
    renderPreview();
    actualizarBtnImprimir();
}

function actualizarBtnImprimir() {
    const hay = Object.keys(seleccionados).length > 0;
    document.getElementById('btnImprimir').disabled    = !hay;
    document.getElementById('btnImprimirPrev').disabled = !hay;
}

// ── Preview ───────────────────────────────────────────────────────────────────
function renderPreview() {
    const prodsSel = todos.filter(p => seleccionados[p.id]);
    const grid = document.getElementById('previewGrid');
    if (!prodsSel.length) {
        grid.innerHTML = '<div class="empty-state"><i class="fas fa-tag"></i><p>Seleccioná productos de la izquierda para ver la vista previa</p></div>';
        document.getElementById('previewCount').textContent = '';
        return;
    }
    const showNegocio = document.getElementById('optNegocio').checked;
    const showBarcode = document.getElementById('optBarcode').checked;
    const showPrecio  = document.getElementById('optPrecio').checked;
    const showVenc    = document.getElementById('optVenc').checked;
    let totalEtiq = 0;
    let html = '';
    prodsSel.forEach(p => {
        const qty = seleccionados[p.id] || 1;
        totalEtiq += qty;
        const barVal = p.codigo_barras || String(p.id).padStart(12,'0');
        for (let i = 0; i < qty; i++) {
            html += `<div class="etiqueta etiq-${etiqSize}" id="etiq-${p.id}-${i}">
                ${showNegocio && negocioNombre ? `<div class="etiq-negocio">${esc(negocioNombre)}</div>` : ''}
                <div class="etiq-nombre">${esc(p.nombre)}</div>
                ${showPrecio ? `<div class="etiq-precio"><span class="sym">$</span>${Number(p.precio_venta).toLocaleString('es-AR',{minimumFractionDigits:0})}</div>` : ''}
                ${showBarcode ? `<svg class="etiq-barcode" id="bc-${p.id}-${i}"></svg><div class="etiq-codigo">${esc(barVal)}</div>` : ''}
                ${showVenc && p.fecha_vencimiento ? `<div class="etiq-vencimiento">Vto: ${fmtFecha(p.fecha_vencimiento)}</div>` : ''}
            </div>`;
        }
    });
    grid.innerHTML = html;
    document.getElementById('previewCount').textContent = `${totalEtiq} etiqueta${totalEtiq !== 1 ? 's' : ''}`;
    // Render barcodes
    if (showBarcode) {
        prodsSel.forEach(p => {
            const qty = seleccionados[p.id] || 1;
            const barVal = p.codigo_barras || String(p.id).padStart(12,'0');
            for (let i = 0; i < qty; i++) {
                const el = document.getElementById(`bc-${p.id}-${i}`);
                if (!el) continue;
                try {
                    const bcWidth  = etiqSize === 'sm' ? 1.2 : etiqSize === 'md' ? 1.6 : 2;
                    const bcHeight = etiqSize === 'sm' ? 28  : etiqSize === 'md' ? 36  : 44;
                    JsBarcode(el, barVal, {
                        format: 'CODE128', width: bcWidth, height: bcHeight,
                        displayValue: false, margin: 2, background: 'white',
                    });
                } catch(e) {
                    el.parentElement.querySelector('.etiq-codigo').textContent = barVal;
                    el.remove();
                }
            }
        });
    }
}

function setSize(size, btn) {
    etiqSize = size;
    document.querySelectorAll('.formato-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderPreview();
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function catNombre(catId) {
    const c = categorias.find(x => x.id == catId);
    return c ? c.nombre : '';
}
function fmt(n)      { return '$' + Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function fmtFecha(f) { if(!f) return ''; const p=f.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s)      { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

init();
</script>
</body>
</html>
