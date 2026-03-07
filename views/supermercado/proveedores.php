<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        .prov-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
            padding: 24px;
        }
        .prov-card {
            background: var(--card-bg, #fff);
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 16px;
            padding: 20px;
            transition: box-shadow .2s, transform .15s;
            cursor: pointer;
        }
        .prov-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); transform: translateY(-2px); }
        .prov-card-head { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .prov-avatar {
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(135deg, #16a34a, #0FD186);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 900; color: #fff; flex-shrink: 0;
        }
        .prov-nombre { font-size: 15px; font-weight: 700; color: var(--text-color, #1e293b); }
        .prov-razon { font-size: 12px; color: var(--muted-color, #64748b); margin-top: 2px; }
        .prov-info { display: flex; flex-direction: column; gap: 6px; }
        .prov-info-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-color, #374151); }
        .prov-info-row i { width: 16px; text-align: center; color: var(--muted-color, #9ca3af); font-size: 12px; }
        .prov-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-color, #f1f5f9); }
        .prov-badge { font-size: 11px; font-weight: 600; background: rgba(22,163,74,.1); color: #16a34a; padding: 3px 10px; border-radius: 20px; }
        .prov-actions { display: flex; gap: 6px; }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border-color, #e5e7eb); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted-color, #64748b); font-size: 13px; transition: all .15s; }
        .btn-icon:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59,130,246,.06); }
        .btn-icon.del:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,.06); }

        /* Header tools */
        .page-toolbar { display: flex; align-items: center; gap: 12px; padding: 20px 24px 0; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 200px; max-width: 360px; position: relative; }
        .search-box input { width: 100%; padding: 9px 14px 9px 36px; border-radius: 10px; border: 1px solid var(--border-color, #e5e7eb); background: var(--card-bg, #fff); font-size: 14px; color: var(--text-color, #1e293b); outline: none; }
        .search-box i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted-color, #9ca3af); font-size: 13px; }
        .btn-primary { display: flex; align-items: center; gap: 7px; padding: 9px 18px; background: #16a34a; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
        .btn-primary:hover { background: #15803d; }
        .empty-state { text-align: center; padding: 80px 20px; color: var(--muted-color, #9ca3af); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: .4; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: var(--card-bg, #fff); border-radius: 20px; width: 100%; max-width: 540px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
        .modal-head { padding: 20px 24px; border-bottom: 1px solid var(--border-color, #e5e7eb); display: flex; align-items: center; justify-content: space-between; }
        .modal-head h3 { font-size: 17px; font-weight: 700; }
        .modal-body { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border-color, #e5e7eb); display: flex; gap: 10px; justify-content: flex-end; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 12px; font-weight: 600; color: var(--muted-color, #64748b); text-transform: uppercase; letter-spacing: .4px; }
        .form-group input, .form-group textarea { padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color, #e5e7eb); background: var(--card-bg, #fff); font-size: 14px; color: var(--text-color, #1e293b); outline: none; }
        .form-group input:focus, .form-group textarea:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
        .btn-cancel { padding: 9px 18px; border-radius: 10px; border: 1px solid var(--border-color, #e5e7eb); background: transparent; font-size: 14px; cursor: pointer; color: var(--text-color, #374151); }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <div class="page-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar proveedor…" oninput="filtrar()">
            </div>
            <button class="btn-primary" onclick="abrirModal()">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </button>
            <span id="countLabel" style="font-size:13px; color:var(--muted-color,#64748b); margin-left:auto;"></span>
        </div>

        <div class="prov-grid" id="provGrid">
            <div class="empty-state" style="grid-column:1/-1">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando…</p>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitle">Nuevo Proveedor</h3>
            <button class="btn-icon" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="provId">
            <div class="form-group">
                <label>Nombre Comercial *</label>
                <input type="text" id="fNombre" placeholder="Ej: Distribuidora Norte">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Razón Social</label>
                    <input type="text" id="fRazon" placeholder="S.A. / S.R.L.">
                </div>
                <div class="form-group">
                    <label>CUIT</label>
                    <input type="text" id="fCuit" placeholder="30-12345678-9">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contacto</label>
                    <input type="text" id="fContacto" placeholder="Nombre del vendedor">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="fTelefono" placeholder="011-4444-5555">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="fEmail" placeholder="ventas@proveedor.com">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="fDireccion" placeholder="Calle y número">
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea id="fNotas" rows="2" placeholder="Condiciones, días de entrega…" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="guardar()">
                <i class="fas fa-check"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script>
const API = '../../api/proveedores/index.php';
let todosLosProveedores = [];

async function cargar() {
    const r = await fetch(API, { credentials: 'include' });
    const d = await r.json();
    if (d.success) {
        todosLosProveedores = d.data;
        renderizar(todosLosProveedores);
    }
}

function renderizar(lista) {
    const grid = document.getElementById('provGrid');
    document.getElementById('countLabel').textContent = `${lista.length} proveedor${lista.length !== 1 ? 'es' : ''}`;
    if (!lista.length) {
        grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
            <i class="fas fa-truck"></i>
            <p style="margin-top:12px; font-weight:600;">Sin proveedores</p>
            <p style="font-size:13px; margin-top:4px;">Agregá tu primer proveedor</p>
        </div>`;
        return;
    }
    grid.innerHTML = lista.map(p => {
        const inicial = (p.nombre || '?')[0].toUpperCase();
        const tel = p.telefono ? `<div class="prov-info-row"><i class="fas fa-phone"></i>${esc(p.telefono)}</div>` : '';
        const email = p.email ? `<div class="prov-info-row"><i class="fas fa-envelope"></i>${esc(p.email)}</div>` : '';
        const contacto = p.contacto ? `<div class="prov-info-row"><i class="fas fa-user"></i>${esc(p.contacto)}</div>` : '';
        return `<div class="prov-card">
            <div class="prov-card-head">
                <div class="prov-avatar">${inicial}</div>
                <div>
                    <div class="prov-nombre">${esc(p.nombre)}</div>
                    <div class="prov-razon">${esc(p.razon_social || p.cuit || '')}</div>
                </div>
            </div>
            <div class="prov-info">
                ${contacto}${tel}${email}
            </div>
            <div class="prov-footer">
                <span class="prov-badge"><i class="fas fa-box" style="margin-right:4px;"></i>${p.total_productos} producto${p.total_productos!=1?'s':''}</span>
                <div class="prov-actions">
                    <button class="btn-icon" onclick="editarProveedor(${p.id})" title="Editar"><i class="fas fa-pen"></i></button>
                    <button class="btn-icon del" onclick="eliminarProveedor(${p.id}, '${esc(p.nombre)}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>`;
    }).join('');
}

function filtrar() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    renderizar(todosLosProveedores.filter(p =>
        p.nombre.toLowerCase().includes(q) ||
        (p.contacto||'').toLowerCase().includes(q) ||
        (p.email||'').toLowerCase().includes(q)
    ));
}

function abrirModal(prov = null) {
    document.getElementById('provId').value = prov?.id || '';
    document.getElementById('modalTitle').textContent = prov ? 'Editar Proveedor' : 'Nuevo Proveedor';
    document.getElementById('fNombre').value = prov?.nombre || '';
    document.getElementById('fRazon').value = prov?.razon_social || '';
    document.getElementById('fCuit').value = prov?.cuit || '';
    document.getElementById('fContacto').value = prov?.contacto || '';
    document.getElementById('fTelefono').value = prov?.telefono || '';
    document.getElementById('fEmail').value = prov?.email || '';
    document.getElementById('fDireccion').value = prov?.direccion || '';
    document.getElementById('fNotas').value = prov?.notas || '';
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('fNombre').focus();
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

function editarProveedor(id) {
    const p = todosLosProveedores.find(x => x.id == id);
    if (p) abrirModal(p);
}

async function guardar() {
    const id = document.getElementById('provId').value;
    const body = {
        id: id ? parseInt(id) : undefined,
        nombre: document.getElementById('fNombre').value.trim(),
        razon_social: document.getElementById('fRazon').value.trim(),
        cuit: document.getElementById('fCuit').value.trim(),
        contacto: document.getElementById('fContacto').value.trim(),
        telefono: document.getElementById('fTelefono').value.trim(),
        email: document.getElementById('fEmail').value.trim(),
        direccion: document.getElementById('fDireccion').value.trim(),
        notas: document.getElementById('fNotas').value.trim(),
    };
    if (!body.nombre) { alert('El nombre es obligatorio'); return; }
    const r = await fetch(API, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(body)
    });
    const d = await r.json();
    if (d.success) { cerrarModal(); cargar(); } else { alert(d.message || 'Error'); }
}

async function eliminarProveedor(id, nombre) {
    if (!confirm(`¿Eliminar a "${nombre}"?`)) return;
    await fetch(API, { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify({ id }) });
    cargar();
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
