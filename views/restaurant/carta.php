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
    <title>Carta / Menú — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --rest: #FF7A30; }

        /* Layout */
        .carta-layout { display:flex; min-height:100vh; }

        /* Sidebar de categorías */
        .cat-sidebar {
            width: 220px; flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh; overflow-y: auto;
        }
        .cat-sidebar-title {
            padding: 18px 16px 10px;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .6px; color: var(--text-secondary);
        }
        .cat-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px; cursor: pointer;
            border-left: 3px solid transparent;
            font-size: 13px; font-weight: 500; color: var(--text-secondary);
            transition: all .15s; text-decoration: none;
        }
        .cat-item:hover { background: var(--background); color: var(--text-primary); }
        .cat-item.active {
            color: var(--rest); border-left-color: var(--rest);
            background: rgba(255,122,48,.07); font-weight: 700;
        }
        .cat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .cat-count { margin-left:auto; font-size:11px; background:var(--background); border-radius:10px; padding:1px 7px; }

        /* Main area */
        .carta-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        /* Toolbar */
        .carta-toolbar {
            position: sticky; top: 0; z-index: 10;
            background: var(--surface); border-bottom: 1px solid var(--border);
            padding: 14px 24px; display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }
        .toolbar-left { display:flex; align-items:center; gap:12px; }
        .search-box { position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input {
            padding: 8px 12px 8px 32px; border: 1px solid var(--border);
            border-radius: 8px; font-size: 13px; width: 220px;
            background: var(--background); color: var(--text-primary);
        }
        .search-box input:focus { outline: none; border-color: var(--rest); }
        .view-toggle button {
            padding: 7px 12px; border: 1px solid var(--border); background: var(--background);
            cursor: pointer; color: var(--text-secondary); font-size: 14px; transition: all .15s;
        }
        .view-toggle button:first-child { border-radius: 8px 0 0 8px; border-right: none; }
        .view-toggle button:last-child  { border-radius: 0 8px 8px 0; }
        .view-toggle button.active { background: var(--rest); color: white; border-color: var(--rest); }

        /* Sección de categoría */
        .cat-section { padding: 24px; }
        .cat-section-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 16px; padding-bottom: 12px;
            border-bottom: 2px solid var(--border);
        }
        .cat-section-badge {
            padding: 4px 12px; border-radius: 20px; font-size: 12px;
            font-weight: 700; color: white;
        }
        .cat-section-title { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .cat-section-count { font-size: 13px; color: var(--text-secondary); margin-left: auto; }

        /* Grid de platos */
        .platos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }
        .platos-list { display: flex; flex-direction: column; gap: 8px; }

        /* Card de plato (grid) */
        .plato-card {
            background: var(--surface); border-radius: 14px;
            border: 1px solid var(--border); overflow: hidden;
            transition: all .2s; cursor: pointer;
            display: flex; flex-direction: column;
        }
        .plato-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
        .plato-card.inactivo { opacity: .5; }
        .plato-img {
            height: 150px; background: var(--background);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; position: relative;
        }
        .plato-img img { width:100%; height:100%; object-fit:cover; }
        .plato-img-placeholder { font-size: 36px; opacity: .2; }
        .plato-status-badge {
            position: absolute; top: 8px; right: 8px;
            padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 700;
        }
        .badge-activo   { background: rgba(15,209,134,.9); color: white; }
        .badge-inactivo { background: rgba(239,68,68,.9);  color: white; }
        .plato-body { padding: 14px; flex: 1; display: flex; flex-direction: column; }
        .plato-nombre { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .plato-desc   { font-size: 12px; color: var(--text-secondary); line-height: 1.4; flex: 1; margin-bottom: 10px;
                        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .plato-footer { display: flex; align-items: center; justify-content: space-between; }
        .plato-precio { font-size: 18px; font-weight: 800; color: var(--rest); }
        .plato-actions { display: flex; gap: 6px; }
        .btn-icon { width:30px; height:30px; border-radius:8px; border:1px solid var(--border); background:var(--background);
                    cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--text-secondary); transition:all .15s; }
        .btn-icon:hover { background: var(--rest); color: white; border-color: var(--rest); }
        .btn-icon.danger:hover { background: #ef4444; border-color: #ef4444; }

        /* Lista de platos */
        .plato-row {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 16px;
            display: flex; align-items: center; gap: 14px;
            transition: all .15s; cursor: pointer;
        }
        .plato-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .plato-row.inactivo { opacity: .5; }
        .plato-row-thumb {
            width: 52px; height: 52px; border-radius: 10px;
            background: var(--background); display: flex; align-items: center;
            justify-content: center; overflow: hidden; flex-shrink: 0;
        }
        .plato-row-thumb img { width:100%; height:100%; object-fit:cover; }
        .plato-row-info { flex: 1; min-width: 0; }
        .plato-row-nombre { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .plato-row-desc { font-size: 12px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .plato-row-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

        /* Empty */
        .empty-cat { text-align: center; padding: 40px 24px; color: var(--text-secondary); }
        .empty-cat i { font-size: 36px; opacity: .15; display: block; margin-bottom: 10px; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:18px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 22px; }
        .modal-footer { padding:14px 22px 18px; display:flex; gap:10px; justify-content:flex-end; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px; }
        .form-group input,.form-group select,.form-group textarea {
            width:100%; padding:9px 12px; border:1px solid var(--border);
            border-radius:10px; font-size:14px; background:var(--surface);
            color:var(--text-primary); box-sizing:border-box;
        }
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus { outline:none; border-color:var(--rest); }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .foto-preview {
            width:100%; height:140px; border-radius:10px; border:2px dashed var(--border);
            display:flex; align-items:center; justify-content:center;
            overflow:hidden; cursor:pointer; background:var(--background); position:relative;
        }
        .foto-preview img { width:100%; height:100%; object-fit:cover; }
        .foto-preview-placeholder { text-align:center; color:var(--text-secondary); font-size:13px; }
        .foto-preview-placeholder i { font-size:28px; display:block; margin-bottom:6px; opacity:.3; }
        .btn-cancel { padding:9px 18px; background:var(--background); border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; color:var(--text-primary); }
        .btn-save   { padding:9px 22px; background:var(--rest); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-save:hover { background:#e06820; }

        /* Toggle disponible */
        .toggle-switch { position:relative; display:inline-block; width:42px; height:24px; }
        .toggle-switch input { opacity:0; width:0; height:0; }
        .toggle-slider { position:absolute; inset:0; background:#ccc; border-radius:24px; cursor:pointer; transition:.2s; }
        .toggle-slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.2s; }
        input:checked + .toggle-slider { background:#0FD186; }
        input:checked + .toggle-slider:before { transform:translateX(18px); }

        /* Toast */
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; }
        .toast.show { opacity:1; }

        /* Responsive */
        @media (max-width: 768px) {
            .cat-sidebar { display:none; }
            .platos-grid { grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap:10px; }
        }
    </style>
</head>
<body>
<div class="carta-layout">

    <!-- Sidebar categorías -->
    <div class="cat-sidebar" id="catSidebar">
        <div class="cat-sidebar-title">Categorías</div>
        <a class="cat-item active" data-cat="todos" onclick="filtrarCat('todos',this)">
            <span class="cat-dot" style="background:#64748b;"></span>
            Todos los platos
            <span class="cat-count" id="cnt-todos">0</span>
        </a>
        <div id="catList"></div>
    </div>

    <!-- Main -->
    <div class="carta-main">

        <!-- Toolbar -->
        <div class="carta-toolbar">
            <div class="toolbar-left">
                <button onclick="history.back()" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--text-secondary);padding:4px 8px;border-radius:8px;" title="Volver">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div>
                    <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">
                        <i class="fas fa-book-open" style="color:var(--rest);margin-right:8px;"></i>Carta / Menú
                    </h1>
                    <p style="margin:0;font-size:12px;color:var(--text-secondary);" id="toolbarSubtitle">Cargando…</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar plato…" id="searchInput" oninput="buscar()">
                </div>
                <div class="view-toggle">
                    <button class="active" id="btnGrid" onclick="setView('grid')" title="Cuadrícula"><i class="fas fa-th-large"></i></button>
                    <button id="btnList" onclick="setView('list')" title="Lista"><i class="fas fa-list"></i></button>
                </div>
                <button class="btn btn-primary" onclick="abrirModal()">
                    <i class="fas fa-plus"></i> Agregar plato
                </button>
            </div>
        </div>

        <!-- Contenido -->
        <div id="cartaContent" style="padding-bottom:40px;">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Modal agregar / editar plato -->
<div class="modal-overlay" id="modalPlato">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-utensils" style="color:var(--rest);margin-right:8px;"></i>Nuevo Plato</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="pId">
            <div class="form-group">
                <label>Foto del plato</label>
                <div class="foto-preview" id="fotoPreview" onclick="document.getElementById('fotoInput').click()">
                    <div class="foto-preview-placeholder">
                        <i class="fas fa-camera"></i>
                        <span>Clic para agregar foto</span>
                    </div>
                </div>
                <input type="file" id="fotoInput" accept="image/*" style="display:none;" onchange="previewFoto(this)">
                <div id="fotoActual" style="display:none;margin-top:6px;">
                    <a href="#" id="fotoActualLink" target="_blank" style="font-size:12px;color:var(--rest);">Ver foto actual</a>
                    <button type="button" onclick="quitarFoto()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;margin-left:8px;">✕ Quitar</button>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre del plato <span style="color:#ef4444;">*</span></label>
                <input type="text" id="pNombre" placeholder="Ej: Milanesa Napolitana">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea id="pDesc" rows="2" placeholder="Ingredientes, preparación…" style="resize:vertical;"></textarea>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Precio <span style="color:#ef4444;">*</span></label>
                    <input type="number" id="pPrecio" placeholder="0" min="0" step="50">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select id="pCategoria">
                        <option value="">— Sin categoría —</option>
                    </select>
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Precio costo</label>
                    <input type="number" id="pCosto" placeholder="0" min="0" step="50">
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:12px;padding-top:24px;">
                    <label style="margin:0;cursor:pointer;">
                        <label class="toggle-switch">
                            <input type="checkbox" id="pActivo" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </label>
                    <span style="font-size:13px;font-weight:600;color:var(--text-primary);">Disponible en carta</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-save" onclick="guardarPlato()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const BASE        = '<?= $base ?>';
const API_PROD    = BASE + '/api/productos/index.php';
const API_CATS    = BASE + '/api/categorias/index.php';
const API_UPLOAD  = BASE + '/api/productos/upload.php';

let todosPlatos  = [];
let categorias   = [];
let catActual    = 'todos';
let vistaActual  = 'grid';
let fotoNueva    = null;   // File object si el usuario eligió nueva foto
let fotoQuitada  = false;

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    const [rp, rc] = await Promise.all([
        fetch(API_PROD, {credentials:'include'}),
        fetch(API_CATS, {credentials:'include'})
    ]);
    const jp = await rp.json();
    const jc = await rc.json();
    todosPlatos = jp.success ? (jp.data?.productos || jp.data || []) : [];
    categorias  = jc.success ? (jc.data || []) : [];
    renderCatSidebar();
    renderCarta();
    // Subtitle
    document.getElementById('toolbarSubtitle').textContent =
        `${todosPlatos.length} plato${todosPlatos.length !== 1 ? 's' : ''} · ${categorias.length} categorías`;
    document.getElementById('cnt-todos').textContent = todosPlatos.length;
    // Poblar select categorías en modal
    const sel = document.getElementById('pCategoria');
    sel.innerHTML = '<option value="">— Sin categoría —</option>' +
        categorias.map(c => `<option value="${c.id}">${esc(c.nombre)}</option>`).join('');
}

// ── Sidebar ───────────────────────────────────────────────────────────────────
function renderCatSidebar() {
    const div = document.getElementById('catList');
    div.innerHTML = categorias.map(c => {
        const cnt = todosPlatos.filter(p => p.categoria_id == c.id).length;
        if (cnt === 0) return '';
        return `<a class="cat-item" data-cat="${c.id}" onclick="filtrarCat('${c.id}',this)">
            <span class="cat-dot" style="background:${esc(c.color||'#64748b')};"></span>
            ${esc(c.nombre)}
            <span class="cat-count">${cnt}</span>
        </a>`;
    }).join('');
}

function filtrarCat(cat, el) {
    catActual = cat;
    document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('searchInput').value = '';
    renderCarta();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderCarta() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    let lista = todosPlatos;
    if (catActual !== 'todos') lista = lista.filter(p => p.categoria_id == catActual);
    if (q) lista = lista.filter(p => (p.nombre||'').toLowerCase().includes(q) || (p.descripcion||'').toLowerCase().includes(q));

    const div = document.getElementById('cartaContent');

    if (catActual === 'todos' && !q) {
        // Mostrar por secciones de categoría
        const cats = categorias.filter(c => lista.some(p => p.categoria_id == c.id));
        // Items sin categoría
        const sinCat = lista.filter(p => !p.categoria_id);
        let html = '';
        cats.forEach(c => {
            const platos = lista.filter(p => p.categoria_id == c.id);
            html += renderSeccion(c, platos);
        });
        if (sinCat.length) html += renderSeccion(null, sinCat);
        if (!html) html = '<div class="empty-cat"><i class="fas fa-utensils"></i><p>No hay platos cargados aún</p><button class="btn btn-primary" onclick="abrirModal()" style="margin-top:12px;"><i class="fas fa-plus"></i> Agregar primer plato</button></div>';
        div.innerHTML = html;
    } else {
        // Vista filtrada — sin secciones
        let html = '<div class="cat-section">';
        if (!lista.length) {
            html += '<div class="empty-cat"><i class="fas fa-search"></i><p>Sin resultados</p></div>';
        } else {
            html += vistaActual === 'grid'
                ? `<div class="platos-grid">${lista.map(renderPlatoCard).join('')}</div>`
                : `<div class="platos-list">${lista.map(renderPlatoRow).join('')}</div>`;
        }
        html += '</div>';
        div.innerHTML = html;
    }
}

function renderSeccion(cat, platos) {
    const nombre = cat ? esc(cat.nombre) : 'Sin categoría';
    const color  = cat ? (cat.color || '#64748b') : '#64748b';
    return `
    <div class="cat-section" id="sec-${cat ? cat.id : 'sin'}">
        <div class="cat-section-header">
            <span class="cat-section-badge" style="background:${color};">${nombre}</span>
            <div class="cat-section-title"></div>
            <span class="cat-section-count">${platos.length} plato${platos.length !== 1 ? 's' : ''}</span>
        </div>
        ${vistaActual === 'grid'
            ? `<div class="platos-grid">${platos.map(renderPlatoCard).join('')}</div>`
            : `<div class="platos-list">${platos.map(renderPlatoRow).join('')}</div>`
        }
    </div>`;
}

function renderPlatoCard(p) {
    const imgHtml = p.foto
        ? `<img src="${BASE}/public/uploads/productos/${esc(p.foto)}" onerror="this.parentElement.innerHTML='<span class=\\'plato-img-placeholder\\'><i class=\\'fas fa-utensils\\'></i></span>'">`
        : `<span class="plato-img-placeholder"><i class="fas fa-utensils"></i></span>`;
    const activo = parseInt(p.activo) === 1;
    return `
    <div class="plato-card ${activo?'':'inactivo'}" onclick="editarPlato(${p.id})">
        <div class="plato-img">
            ${imgHtml}
            <span class="plato-status-badge ${activo?'badge-activo':'badge-inactivo'}">${activo?'Disponible':'No disponible'}</span>
        </div>
        <div class="plato-body">
            <div class="plato-nombre">${esc(p.nombre)}</div>
            <div class="plato-desc">${esc(p.descripcion||'')}</div>
            <div class="plato-footer">
                <div class="plato-precio">${fmt(p.precio_venta)}</div>
                <div class="plato-actions" onclick="event.stopPropagation()">
                    <button class="btn-icon" onclick="toggleDisponible(${p.id},${activo?0:1})" title="${activo?'Marcar no disponible':'Marcar disponible'}">
                        <i class="fas fa-${activo?'eye-slash':'eye'}"></i>
                    </button>
                    <button class="btn-icon danger" onclick="confirmarBorrar(${p.id},'${esc(p.nombre)}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}

function renderPlatoRow(p) {
    const activo = parseInt(p.activo) === 1;
    const imgHtml = p.foto
        ? `<img src="${BASE}/public/uploads/productos/${esc(p.foto)}" onerror="this.style.display='none'">`
        : `<i class="fas fa-utensils" style="font-size:20px;opacity:.2;color:var(--text-secondary);"></i>`;
    return `
    <div class="plato-row ${activo?'':'inactivo'}" onclick="editarPlato(${p.id})">
        <div class="plato-row-thumb">${imgHtml}</div>
        <div class="plato-row-info">
            <div class="plato-row-nombre">${esc(p.nombre)}</div>
            <div class="plato-row-desc">${esc(p.descripcion||'Sin descripción')}</div>
        </div>
        <div class="plato-row-right" onclick="event.stopPropagation()">
            <span style="font-weight:700;color:var(--rest);font-size:15px;">${fmt(p.precio_venta)}</span>
            <span class="plato-status-badge ${activo?'badge-activo':'badge-inactivo'}" style="position:static;">${activo?'✓':'✗'}</span>
            <button class="btn-icon" onclick="toggleDisponible(${p.id},${activo?0:1})" title="Cambiar disponibilidad">
                <i class="fas fa-${activo?'eye-slash':'eye'}"></i>
            </button>
            <button class="btn-icon danger" onclick="confirmarBorrar(${p.id},'${esc(p.nombre)}')" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>`;
}

// ── Filtros ───────────────────────────────────────────────────────────────────
function buscar() { renderCarta(); }

function setView(v) {
    vistaActual = v;
    document.getElementById('btnGrid').classList.toggle('active', v === 'grid');
    document.getElementById('btnList').classList.toggle('active', v === 'list');
    renderCarta();
}

// ── Modal ─────────────────────────────────────────────────────────────────────
function abrirModal(plato = null) {
    fotoNueva   = null;
    fotoQuitada = false;
    document.getElementById('pId').value      = plato ? plato.id : '';
    document.getElementById('pNombre').value  = plato ? plato.nombre : '';
    document.getElementById('pDesc').value    = plato ? (plato.descripcion||'') : '';
    document.getElementById('pPrecio').value  = plato ? plato.precio_venta : '';
    document.getElementById('pCosto').value   = plato ? (plato.precio_costo||'') : '';
    document.getElementById('pCategoria').value = plato ? (plato.categoria_id||'') : '';
    document.getElementById('pActivo').checked = plato ? parseInt(plato.activo) === 1 : true;
    document.getElementById('fotoInput').value = '';

    const prev = document.getElementById('fotoPreview');
    const actual = document.getElementById('fotoActual');
    if (plato && plato.foto) {
        prev.innerHTML = `<img src="${BASE}/public/uploads/productos/${esc(plato.foto)}" onerror="this.parentElement.innerHTML='<div class=\\'foto-preview-placeholder\\'><i class=\\'fas fa-camera\\'></i><span>Clic para agregar foto</span></div>'">`;
        actual.style.display = 'block';
        document.getElementById('fotoActualLink').href = `${BASE}/public/uploads/productos/${esc(plato.foto)}`;
    } else {
        prev.innerHTML = '<div class="foto-preview-placeholder"><i class="fas fa-camera"></i><span>Clic para agregar foto</span></div>';
        actual.style.display = 'none';
    }

    document.getElementById('modalTitulo').innerHTML = plato
        ? `<i class="fas fa-edit" style="color:var(--rest);margin-right:8px;"></i>Editar Plato`
        : `<i class="fas fa-plus-circle" style="color:var(--rest);margin-right:8px;"></i>Nuevo Plato`;
    document.getElementById('modalPlato').classList.add('open');
    setTimeout(() => document.getElementById('pNombre').focus(), 100);
}

function editarPlato(id) {
    const p = todosPlatos.find(x => x.id == id);
    if (p) abrirModal(p);
}

function cerrarModal() {
    document.getElementById('modalPlato').classList.remove('open');
}

function previewFoto(input) {
    if (!input.files || !input.files[0]) return;
    fotoNueva = input.files[0];
    const url = URL.createObjectURL(fotoNueva);
    document.getElementById('fotoPreview').innerHTML = `<img src="${url}">`;
}

function quitarFoto() {
    fotoNueva   = null;
    fotoQuitada = true;
    document.getElementById('fotoPreview').innerHTML = '<div class="foto-preview-placeholder"><i class="fas fa-camera"></i><span>Clic para agregar foto</span></div>';
    document.getElementById('fotoActual').style.display = 'none';
    document.getElementById('fotoInput').value = '';
}

async function guardarPlato() {
    const id     = document.getElementById('pId').value;
    const nombre = document.getElementById('pNombre').value.trim();
    const precio = parseFloat(document.getElementById('pPrecio').value) || 0;
    if (!nombre) { toast('Ingresá el nombre del plato', 'error'); return; }
    if (precio <= 0) { toast('Ingresá un precio válido', 'error'); return; }

    let fotoPath = null;
    // Si hay foto nueva → subirla primero
    if (fotoNueva) {
        const fd = new FormData();
        fd.append('foto', fotoNueva);
        const ru = await fetch(API_UPLOAD, {method:'POST', credentials:'include', body: fd});
        const ju = await ru.json();
        if (ju.success) fotoPath = ju.data.filename;
    }

    const body = {
        nombre,
        descripcion:    document.getElementById('pDesc').value.trim(),
        precio_venta:   precio,
        precio_costo:   parseFloat(document.getElementById('pCosto').value) || 0,
        categoria_id:   parseInt(document.getElementById('pCategoria').value) || null,
        activo:         document.getElementById('pActivo').checked ? 1 : 0,
    };
    if (fotoPath)   body.foto = fotoPath;
    if (fotoQuitada && !fotoNueva) body.foto = '';

    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_PROD}?id=${id}` : API_PROD;
    const r = await fetch(url, {
        method, credentials:'include',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) {
        cerrarModal();
        toast(id ? 'Plato actualizado ✓' : 'Plato agregado ✓');
        await init();
    } else {
        toast(j.message || 'Error al guardar', 'error');
    }
}

// ── Toggle disponible (sin abrir modal) ───────────────────────────────────────
async function toggleDisponible(id, nuevoActivo) {
    const r = await fetch(`${API_PROD}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({activo: nuevoActivo})
    });
    const j = await r.json();
    if (j.success) {
        const p = todosPlatos.find(x => x.id == id);
        if (p) p.activo = nuevoActivo;
        toast(nuevoActivo ? 'Marcado como disponible ✓' : 'Marcado como no disponible');
        renderCarta();
    }
}

// ── Borrar ────────────────────────────────────────────────────────────────────
function confirmarBorrar(id, nombre) {
    if (!confirm(`¿Eliminar "${nombre}" de la carta?`)) return;
    borrarPlato(id);
}

async function borrarPlato(id) {
    const r = await fetch(`${API_PROD}?id=${id}`, {method:'DELETE', credentials:'include'});
    const j = await r.json();
    if (j.success) {
        todosPlatos = todosPlatos.filter(p => p.id != id);
        toast('Plato eliminado');
        renderCatSidebar();
        renderCarta();
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(n)   { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s)   { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg, tipo = 'ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

// ── Cerrar modal con Esc / clic fuera ─────────────────────────────────────────
document.getElementById('modalPlato').addEventListener('click', e => {
    if (e.target === document.getElementById('modalPlato')) cerrarModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });

init();
</script>
</body>
</html>
