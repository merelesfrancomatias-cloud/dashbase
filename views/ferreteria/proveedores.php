<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --ferr: #f59e0b; }
        .app-layout { display:flex; min-height:100vh; }
        .prov-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }
        .prov-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 18px;
            transition: all .2s; cursor: pointer;
        }
        .prov-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
        .prov-avatar {
            width: 46px; height: 46px; border-radius: 12px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 18px; font-weight: 800; flex-shrink: 0;
        }
        .prov-nombre { font-size: 15px; font-weight: 700; color: var(--text-primary); }
        .prov-razon  { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }
        .prov-info-row { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-secondary); margin-top:6px; }
        .prov-info-row i { width:14px; color: var(--ferr); }
        .prov-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-productos { background:rgba(245,158,11,.12); color:#b45309; }

        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:18px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 22px; }
        .modal-footer { padding:14px 22px 18px; display:flex; gap:10px; justify-content:flex-end; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px; }
        .form-group input, .form-group textarea {
            width:100%; padding:9px 12px; border:1px solid var(--border);
            border-radius:10px; font-size:14px; background:var(--surface);
            color:var(--text-primary); box-sizing:border-box;
        }
        .form-group input:focus, .form-group textarea:focus { outline:none; border-color:var(--ferr); }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .btn-cancel { padding:9px 18px; background:var(--background); border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; color:var(--text-primary); }
        .btn-save   { padding:9px 22px; background:var(--ferr); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-save:hover { background:#d97706; }
        .empty-state { text-align:center; padding:60px 24px; color:var(--text-secondary); }
        .empty-state i { font-size:48px; opacity:.15; display:block; margin-bottom:16px; }
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; }
        .toast.show { opacity:1; }
        .search-box { position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input { padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; width:220px; background:var(--background); color:var(--text-primary); }
        .search-box input:focus { outline:none; border-color:var(--ferr); }
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
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-truck" style="color:var(--ferr);margin-right:8px;"></i>Proveedores
                        </h1>
                        <p style="margin:4px 0 0;color:var(--text-secondary);font-size:14px;" id="subtitle">Cargando…</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Buscar proveedor…" oninput="buscar()">
                        </div>
                        <button class="btn btn-primary" onclick="abrirModal()">
                            <i class="fas fa-plus"></i> Nuevo Proveedor
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:20px;">
                <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-truck"></i></div><div class="stat-info"><div class="stat-value" id="st-total">0</div><div class="stat-label">Proveedores activos</div></div></div>
                <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-box"></i></div><div class="stat-info"><div class="stat-value" id="st-productos">0</div><div class="stat-label">Productos asociados</div></div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-envelope"></i></div><div class="stat-info"><div class="stat-value" id="st-email">0</div><div class="stat-label">Con email</div></div></div>
            </div>

            <!-- Grid proveedores -->
            <div id="proveedoresGrid"></div>

        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalProv">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-truck" style="color:var(--ferr);margin-right:8px;"></i>Nuevo Proveedor</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="pId">
            <div class="form-group">
                <label>Nombre del proveedor <span style="color:#ef4444;">*</span></label>
                <input type="text" id="pNombre" placeholder="Ej: Distribuidora Central SA">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Razón Social</label>
                    <input type="text" id="pRazon" placeholder="Razón social legal">
                </div>
                <div class="form-group">
                    <label>CUIT</label>
                    <input type="text" id="pCuit" placeholder="XX-XXXXXXXX-X">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Contacto</label>
                    <input type="text" id="pContacto" placeholder="Nombre del contacto">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" id="pTelefono" placeholder="+54 11 XXXX-XXXX">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="pEmail" placeholder="ventas@proveedor.com">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="pDireccion" placeholder="Calle, número, ciudad">
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea id="pNotas" rows="2" placeholder="Condiciones de pago, días de entrega…" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-save" onclick="guardarProveedor()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const API = '<?= $base ?>/api/proveedores/index.php';
let proveedores = [];

async function cargar() {
    const r = await fetch(API, {credentials:'include'});
    const j = await r.json();
    proveedores = j.success ? (j.data || []) : [];
    document.getElementById('st-total').textContent    = proveedores.length;
    document.getElementById('st-productos').textContent = proveedores.reduce((s,p)=>s+parseInt(p.total_productos||0),0);
    document.getElementById('st-email').textContent   = proveedores.filter(p=>p.email).length;
    document.getElementById('subtitle').textContent   = `${proveedores.length} proveedor${proveedores.length!==1?'es':''} activo${proveedores.length!==1?'s':''}`;
    renderGrid(proveedores);
}

function buscar() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    renderGrid(q ? proveedores.filter(p =>
        (p.nombre||'').toLowerCase().includes(q) ||
        (p.contacto||'').toLowerCase().includes(q) ||
        (p.email||'').toLowerCase().includes(q)
    ) : proveedores);
}

function renderGrid(lista) {
    const div = document.getElementById('proveedoresGrid');
    if (!lista.length) {
        div.innerHTML = '<div class="empty-state"><i class="fas fa-truck"></i><p>No hay proveedores cargados aún</p><button class="btn btn-primary" onclick="abrirModal()" style="margin-top:12px;"><i class="fas fa-plus"></i> Agregar proveedor</button></div>';
        return;
    }
    div.innerHTML = `<div class="prov-grid">${lista.map(p => {
        const ini = (p.nombre||'?').charAt(0).toUpperCase();
        return `
        <div class="prov-card" onclick="editarProveedor(${p.id})">
            <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:12px;">
                <div class="prov-avatar">${esc(ini)}</div>
                <div style="flex:1;min-width:0;">
                    <div class="prov-nombre">${esc(p.nombre)}</div>
                    ${p.razon_social ? `<div class="prov-razon">${esc(p.razon_social)}</div>` : ''}
                    ${p.cuit ? `<div class="prov-razon">CUIT: ${esc(p.cuit)}</div>` : ''}
                </div>
                <span class="prov-badge badge-productos"><i class="fas fa-box"></i> ${p.total_productos||0}</span>
            </div>
            ${p.contacto  ? `<div class="prov-info-row"><i class="fas fa-user"></i>${esc(p.contacto)}</div>` : ''}
            ${p.telefono  ? `<div class="prov-info-row"><i class="fas fa-phone"></i>${esc(p.telefono)}</div>` : ''}
            ${p.email     ? `<div class="prov-info-row"><i class="fas fa-envelope"></i>${esc(p.email)}</div>` : ''}
            ${p.direccion ? `<div class="prov-info-row"><i class="fas fa-map-marker-alt"></i>${esc(p.direccion)}</div>` : ''}
            <div style="margin-top:14px;display:flex;gap:8px;" onclick="event.stopPropagation()">
                <button onclick="verOrdenes(${p.id},'${esc(p.nombre)}')" style="flex:1;padding:7px;border:1px solid var(--border);background:var(--background);border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;color:var(--text-secondary);">
                    <i class="fas fa-clipboard-list"></i> Ver órdenes
                </button>
                <button onclick="editarProveedor(${p.id})" style="flex:1;padding:7px;background:rgba(245,158,11,.1);border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;color:var(--ferr);">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button onclick="confirmarEliminar(${p.id},'${esc(p.nombre)}')" style="width:34px;padding:7px;background:rgba(239,68,68,.08);border:none;border-radius:8px;cursor:pointer;color:#ef4444;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
    }).join('')}</div>`;
}

function abrirModal(prov = null) {
    document.getElementById('pId').value        = prov ? prov.id : '';
    document.getElementById('pNombre').value    = prov ? prov.nombre : '';
    document.getElementById('pRazon').value     = prov ? (prov.razon_social||'') : '';
    document.getElementById('pCuit').value      = prov ? (prov.cuit||'') : '';
    document.getElementById('pContacto').value  = prov ? (prov.contacto||'') : '';
    document.getElementById('pTelefono').value  = prov ? (prov.telefono||'') : '';
    document.getElementById('pEmail').value     = prov ? (prov.email||'') : '';
    document.getElementById('pDireccion').value = prov ? (prov.direccion||'') : '';
    document.getElementById('pNotas').value     = prov ? (prov.notas||'') : '';
    document.getElementById('modalTitulo').innerHTML = prov
        ? `<i class="fas fa-edit" style="color:var(--ferr);margin-right:8px;"></i>Editar Proveedor`
        : `<i class="fas fa-plus-circle" style="color:var(--ferr);margin-right:8px;"></i>Nuevo Proveedor`;
    document.getElementById('modalProv').classList.add('open');
    setTimeout(() => document.getElementById('pNombre').focus(), 100);
}

function editarProveedor(id) {
    const p = proveedores.find(x => x.id == id);
    if (p) abrirModal(p);
}

function cerrarModal() {
    document.getElementById('modalProv').classList.remove('open');
}

async function guardarProveedor() {
    const id     = document.getElementById('pId').value;
    const nombre = document.getElementById('pNombre').value.trim();
    if (!nombre) { toast('Ingresá el nombre del proveedor', 'error'); return; }
    const body = {
        nombre,
        razon_social: document.getElementById('pRazon').value.trim()    || null,
        cuit:         document.getElementById('pCuit').value.trim()      || null,
        contacto:     document.getElementById('pContacto').value.trim()  || null,
        telefono:     document.getElementById('pTelefono').value.trim()  || null,
        email:        document.getElementById('pEmail').value.trim()     || null,
        direccion:    document.getElementById('pDireccion').value.trim() || null,
        notas:        document.getElementById('pNotas').value.trim()     || null,
    };
    if (id) body.id = parseInt(id);
    const r = await fetch(API, {
        method: id ? 'PUT' : 'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) { cerrarModal(); toast(id ? 'Proveedor actualizado ✓' : 'Proveedor creado ✓'); cargar(); }
    else toast(j.message || 'Error al guardar', 'error');
}

function confirmarEliminar(id, nombre) {
    if (!confirm(`¿Eliminar a "${nombre}"? Los productos asociados quedarán sin proveedor.`)) return;
    fetch(`${API}?id=${id}`, {method:'DELETE', credentials:'include'})
        .then(r => r.json()).then(j => {
            if (j.success) { toast('Proveedor eliminado'); cargar(); }
            else toast(j.message || 'Error', 'error');
        });
}

function verOrdenes(id, nombre) {
    window.location.href = `ordenes.php?proveedor_id=${id}&proveedor=${encodeURIComponent(nombre)}`;
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

document.getElementById('modalProv').addEventListener('click', e => {
    if (e.target === document.getElementById('modalProv')) cerrarModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });

cargar();
</script>
</body>
</html>
