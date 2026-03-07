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
    <title>Clientes — Peluquería</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px 0; flex-wrap: wrap; gap: 12px;
        }
        .page-header h2 { font-size: 22px; font-weight: 800; color: var(--text-color,#1e293b); margin: 0; }
        .btn-nuevo {
            display: flex; align-items: center; gap: 8px; padding: 9px 18px;
            background: #8b5cf6; color: #fff; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s;
        }
        .btn-nuevo:hover { background: #7c3aed; }

        /* Barra de búsqueda */
        .search-bar {
            padding: 16px 24px 0; display: flex; gap: 10px; flex-wrap: wrap;
        }
        .search-input {
            flex: 1; min-width: 220px; padding: 9px 14px 9px 36px;
            border: 1px solid var(--border-color,#e5e7eb); border-radius: 10px;
            font-size: 14px; background: var(--card-bg,#fff); color: var(--text-color,#1e293b);
            position: relative;
        }
        .search-wrap { position: relative; flex: 1; min-width: 220px; }
        .search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; }

        /* Stats */
        .stats-row {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px; padding: 16px 24px 0;
        }
        .stat-box {
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px;
        }
        .stat-icon-pelu {
            width: 44px; height: 44px; border-radius: 12px; display: flex;
            align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
        }
        .stat-icon-pelu.violet { background: rgba(139,92,246,.12); color: #8b5cf6; }
        .stat-icon-pelu.green  { background: rgba(16,185,129,.12);  color: #10b981; }
        .stat-icon-pelu.orange { background: rgba(249,115,22,.12);  color: #f97316; }
        .stat-value { font-size: 22px; font-weight: 800; color: var(--text-color,#1e293b); line-height: 1; }
        .stat-label { font-size: 12px; color: var(--text-secondary,#6b7280); margin-top: 4px; }

        /* Grid clientes */
        .clientes-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 16px; padding: 20px 24px;
        }
        .cliente-card {
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 14px; padding: 18px; cursor: pointer;
            transition: box-shadow .18s, transform .18s;
            display: flex; flex-direction: column; gap: 10px;
        }
        .cliente-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
        .cliente-top { display: flex; align-items: center; gap: 12px; }
        .avatar-pelu {
            width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg,#8b5cf6,#a78bfa);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 17px; font-weight: 800; flex-shrink: 0;
        }
        .cliente-nombre { font-weight: 700; font-size: 15px; color: var(--text-color,#1e293b); }
        .cliente-tel { font-size: 12px; color: var(--text-secondary,#6b7280); margin-top: 2px; }
        .badge-vip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; background: rgba(139,92,246,.12); color: #7c3aed;
            border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .cliente-meta {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
            background: var(--bg-color,#f8fafc); border-radius: 10px; padding: 10px;
        }
        .meta-item { text-align: center; }
        .meta-val  { font-size: 14px; font-weight: 700; color: var(--text-color,#1e293b); }
        .meta-lbl  { font-size: 10px; color: var(--text-secondary,#6b7280); margin-top: 2px; }
        .cliente-notas { font-size: 12px; color: var(--text-secondary,#6b7280); font-style: italic; }
        .card-acciones { display: flex; gap: 8px; }
        .btn-sm {
            flex: 1; padding: 7px; border: none; border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; transition: background .15s;
            display: flex; align-items: center; justify-content: center; gap: 5px;
        }
        .btn-sm.hist   { background: rgba(139,92,246,.1); color: #8b5cf6; }
        .btn-sm.hist:hover { background: rgba(139,92,246,.2); }
        .btn-sm.edit   { background: rgba(16,185,129,.1);  color: #059669; }
        .btn-sm.edit:hover { background: rgba(16,185,129,.2); }
        .btn-sm.del    { background: rgba(239,68,68,.1);   color: #dc2626; }
        .btn-sm.del:hover  { background: rgba(239,68,68,.2); }
        .empty-state { text-align: center; padding: 60px 24px; color: var(--text-secondary,#6b7280); }
        .empty-state i { font-size: 48px; opacity: .2; display: block; margin-bottom: 16px; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45);
            z-index: 1000; align-items: center; justify-content: center; padding: 16px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--card-bg,#fff); border-radius: 18px; width: 100%;
            max-width: 480px; max-height: 90vh; overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .modal-header {
            padding: 20px 22px 14px; border-bottom: 1px solid var(--border-color,#e5e7eb);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--text-color,#1e293b); }
        .modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #9ca3af; padding: 4px 8px; border-radius: 8px; }
        .modal-close:hover { background: var(--bg-color,#f3f4f6); }
        .modal-body { padding: 20px 22px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-color,#1e293b); margin-bottom: 6px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 9px 12px; border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 10px; font-size: 14px; background: var(--card-bg,#fff);
            color: var(--text-color,#1e293b); box-sizing: border-box; transition: border-color .15s;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #8b5cf6; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .modal-footer { padding: 14px 22px 18px; display: flex; gap: 10px; justify-content: flex-end; }
        .btn-cancel { padding: 9px 18px; background: var(--bg-color,#f3f4f6); border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; color: var(--text-color,#374151); }
        .btn-save   { padding: 9px 22px; background: #8b5cf6; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
        .btn-save:hover { background: #7c3aed; }

        /* Modal historial */
        .hist-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 16px; }
        .hist-stat  { background: var(--bg-color,#f8fafc); border-radius: 10px; padding: 12px; text-align: center; }
        .hist-stat-val { font-size: 18px; font-weight: 800; color: #8b5cf6; }
        .hist-stat-lbl { font-size: 11px; color: var(--text-secondary,#6b7280); margin-top: 3px; }
        .hist-list  { display: flex; flex-direction: column; gap: 8px; max-height: 320px; overflow-y: auto; }
        .hist-item  {
            display: flex; align-items: center; gap: 12px;
            background: var(--bg-color,#f8fafc); border-radius: 10px; padding: 10px 12px;
        }
        .hist-item-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(139,92,246,.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
        .hist-item-info { flex: 1; }
        .hist-item-servicio { font-size: 13px; font-weight: 600; color: var(--text-color,#1e293b); }
        .hist-item-fecha    { font-size: 11px; color: var(--text-secondary,#6b7280); margin-top: 2px; }
        .hist-item-monto    { font-size: 14px; font-weight: 700; color: #8b5cf6; }

        @media (max-width: 640px) {
            .clientes-grid { grid-template-columns: 1fr; }
            .hist-stats { grid-template-columns: repeat(3,1fr); }
        }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <!-- Header -->
        <div class="page-header">
            <h2><i class="fas fa-address-book" style="color:#8b5cf6;margin-right:10px;"></i>Clientes</h2>
            <button class="btn-nuevo" onclick="abrirNuevo()">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon-pelu violet"><i class="fas fa-users"></i></div>
                <div><div class="stat-value" id="st-total">—</div><div class="stat-label">Total clientes</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon-pelu green"><i class="fas fa-calendar-check"></i></div>
                <div><div class="stat-value" id="st-mes">—</div><div class="stat-label">Turnos este mes</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon-pelu orange"><i class="fas fa-star"></i></div>
                <div><div class="stat-value" id="st-vip">—</div><div class="stat-label">Clientes VIP</div></div>
            </div>
        </div>

        <!-- Búsqueda -->
        <div class="search-bar">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Buscar por nombre, teléfono…" oninput="buscarDebounce()">
            </div>
        </div>

        <!-- Grid -->
        <div class="clientes-grid" id="clientesGrid">
            <div class="empty-state" style="grid-column:1/-1;">
                <i class="fas fa-spinner fa-spin"></i><p>Cargando clientes…</p>
            </div>
        </div>

    </div>
</div>

<!-- Modal crear/editar -->
<div class="modal-overlay" id="modalForm">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalFormTitulo">Nuevo Cliente</h3>
            <button class="modal-close" onclick="cerrarModal('modalForm')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="clienteId">
            <div class="form-group">
                <label>Nombre <span style="color:#ef4444">*</span></label>
                <input type="text" id="fNombre" placeholder="Nombre completo">
            </div>
            <div class="form-group">
                <label>Teléfono <span style="color:#ef4444">*</span></label>
                <input type="tel" id="fTelefono" placeholder="Ej: 351 123 4567">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="fEmail" placeholder="correo@ejemplo.com">
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea id="fNotas" placeholder="Color habitual, alergias, preferencias…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal('modalForm')">Cancelar</button>
            <button class="btn-save" onclick="guardarCliente()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal historial -->
<div class="modal-overlay" id="modalHistorial">
    <div class="modal-box" style="max-width:540px;">
        <div class="modal-header">
            <h3 id="histTitulo">Historial del cliente</h3>
            <button class="modal-close" onclick="cerrarModal('modalHistorial')">✕</button>
        </div>
        <div class="modal-body" id="histBody">
            <p style="text-align:center;color:#9ca3af;">Cargando…</p>
        </div>
    </div>
</div>

<script>
const API = '../../api/peluqueria/clientes.php';
let _debounce = null;
let _clientes = [];

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function fmtFecha(s) {
    if (!s) return '—';
    const d = new Date(s);
    return d.toLocaleDateString('es-AR', {day:'2-digit',month:'2-digit',year:'numeric'});
}
function fmt(n) {
    return '$' + Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0});
}

async function cargarClientes(q = '') {
    const url = q ? `${API}?search=${encodeURIComponent(q)}` : API;
    const r = await fetch(url, {credentials:'include'});
    const j = await r.json();
    _clientes = j.success ? (j.data || []) : [];
    renderGrid(_clientes);
    actualizarStats(_clientes);
}

function actualizarStats(lista) {
    document.getElementById('st-total').textContent = lista.length;
    // turnos este mes: usar total_mes si viene del API, si no contar
    const conMes = lista.filter(c => parseInt(c.turnos_mes||0) > 0).length;
    const totalMes = lista.reduce((s,c) => s + parseInt(c.turnos_mes||0), 0);
    document.getElementById('st-mes').textContent = totalMes;
    const vip = lista.filter(c => parseInt(c.total_turnos||0) >= 5).length;
    document.getElementById('st-vip').textContent = vip;
}

function renderGrid(lista) {
    const grid = document.getElementById('clientesGrid');
    if (!lista.length) {
        grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
            <i class="fas fa-address-book"></i><p>No se encontraron clientes</p>
        </div>`;
        return;
    }
    grid.innerHTML = lista.map(c => {
        const iniciales = c.nombre.split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase();
        const esVip = parseInt(c.total_turnos||0) >= 5;
        const badgeVip = esVip ? `<span class="badge-vip"><i class="fas fa-star"></i> VIP</span>` : '';
        return `
        <div class="cliente-card" onclick="verHistorial(${c.id})">
            <div class="cliente-top">
                <div class="avatar-pelu">${esc(iniciales)}</div>
                <div style="flex:1;min-width:0;">
                    <div class="cliente-nombre">${esc(c.nombre)} ${badgeVip}</div>
                    <div class="cliente-tel"><i class="fas fa-phone" style="margin-right:4px;opacity:.5;"></i>${esc(c.telefono)}</div>
                    ${c.email ? `<div class="cliente-tel"><i class="fas fa-envelope" style="margin-right:4px;opacity:.5;"></i>${esc(c.email)}</div>` : ''}
                </div>
            </div>
            <div class="cliente-meta">
                <div class="meta-item">
                    <div class="meta-val">${c.total_turnos || 0}</div>
                    <div class="meta-lbl">Turnos totales</div>
                </div>
                <div class="meta-item">
                    <div class="meta-val" style="color:#8b5cf6;">${fmt(c.total_gastado)}</div>
                    <div class="meta-lbl">Total gastado</div>
                </div>
            </div>
            ${c.ultima_visita ? `<div class="cliente-tel"><i class="fas fa-clock" style="margin-right:5px;opacity:.5;"></i>Última visita: ${fmtFecha(c.ultima_visita)}</div>` : ''}
            ${c.notas ? `<div class="cliente-notas"><i class="fas fa-sticky-note" style="margin-right:5px;opacity:.5;"></i>${esc(c.notas)}</div>` : ''}
            <div class="card-acciones" onclick="event.stopPropagation()">
                <button class="btn-sm hist" onclick="verHistorial(${c.id})"><i class="fas fa-history"></i> Historial</button>
                <button class="btn-sm edit" onclick="editarCliente(${c.id})"><i class="fas fa-edit"></i> Editar</button>
                <button class="btn-sm del"  onclick="eliminarCliente(${c.id}, '${esc(c.nombre)}')"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

function buscarDebounce() {
    clearTimeout(_debounce);
    _debounce = setTimeout(() => cargarClientes(document.getElementById('searchInput').value.trim()), 300);
}

// ── Modal crear/editar ────────────────────────────────────────────────────────
function abrirNuevo() {
    document.getElementById('clienteId').value = '';
    document.getElementById('fNombre').value   = '';
    document.getElementById('fTelefono').value = '';
    document.getElementById('fEmail').value    = '';
    document.getElementById('fNotas').value    = '';
    document.getElementById('modalFormTitulo').textContent = 'Nuevo Cliente';
    document.getElementById('modalForm').classList.add('open');
}

function editarCliente(id) {
    const c = _clientes.find(x => x.id == id);
    if (!c) return;
    document.getElementById('clienteId').value = c.id;
    document.getElementById('fNombre').value   = c.nombre || '';
    document.getElementById('fTelefono').value = c.telefono || '';
    document.getElementById('fEmail').value    = c.email || '';
    document.getElementById('fNotas').value    = c.notas || '';
    document.getElementById('modalFormTitulo').textContent = 'Editar Cliente';
    document.getElementById('modalForm').classList.add('open');
}

async function guardarCliente() {
    const id      = document.getElementById('clienteId').value;
    const nombre  = document.getElementById('fNombre').value.trim();
    const telefono= document.getElementById('fTelefono').value.trim();
    const email   = document.getElementById('fEmail').value.trim();
    const notas   = document.getElementById('fNotas').value.trim();

    if (!nombre || !telefono) { alert('Nombre y teléfono son obligatorios.'); return; }

    const method = id ? 'PUT' : 'POST';
    const body   = id ? {id, nombre, telefono, email, notas} : {nombre, telefono, email, notas};
    const r = await fetch(API, {method, credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModal('modalForm'); cargarClientes(); }
    else alert(j.message || 'Error al guardar');
}

async function eliminarCliente(id, nombre) {
    if (!confirm(`¿Eliminar a ${nombre}? Esta acción no se puede deshacer.`)) return;
    const r = await fetch(`${API}?id=${id}`, {method:'DELETE', credentials:'include'});
    const j = await r.json();
    if (j.success) cargarClientes();
    else alert(j.message || 'Error al eliminar');
}

// ── Modal historial ───────────────────────────────────────────────────────────
async function verHistorial(id) {
    document.getElementById('histBody').innerHTML = '<p style="text-align:center;color:#9ca3af;padding:20px;">Cargando…</p>';
    document.getElementById('modalHistorial').classList.add('open');
    const r = await fetch(`${API}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success || !j.data) { document.getElementById('histBody').innerHTML = '<p style="text-align:center;color:#ef4444;">Error al cargar historial</p>'; return; }
    const c     = j.data;
    const stats = j.stats || {};
    const hist  = j.historial || [];
    document.getElementById('histTitulo').textContent = c.nombre;

    const histHtml = hist.length
        ? hist.map(t => `
            <div class="hist-item">
                <div class="hist-item-icon"><i class="fas fa-scissors"></i></div>
                <div class="hist-item-info">
                    <div class="hist-item-servicio">${esc(t.servicio_nombre || t.descripcion || 'Turno')}</div>
                    <div class="hist-item-fecha">${fmtFecha(t.fecha)}${t.hora ? ' · ' + t.hora.slice(0,5) : ''}${t.empleado_nombre ? ' · ' + esc(t.empleado_nombre) : ''}</div>
                </div>
                <div class="hist-item-monto">${fmt(t.precio || t.monto || 0)}</div>
            </div>`).join('')
        : '<p style="text-align:center;color:#9ca3af;padding:20px;">Sin historial de turnos</p>';

    document.getElementById('histBody').innerHTML = `
        <div class="hist-stats">
            <div class="hist-stat"><div class="hist-stat-val">${stats.total_turnos || 0}</div><div class="hist-stat-lbl">Turnos</div></div>
            <div class="hist-stat"><div class="hist-stat-val" style="color:#10b981;">${fmt(stats.total_gastado)}</div><div class="hist-stat-lbl">Total gastado</div></div>
            <div class="hist-stat"><div class="hist-stat-val" style="font-size:12px;">${fmtFecha(stats.ultima_visita)}</div><div class="hist-stat-lbl">Última visita</div></div>
        </div>
        <div class="hist-list">${histHtml}</div>
        <div style="display:flex;gap:8px;margin-top:16px;justify-content:flex-end;">
            <button class="btn-sm edit" style="flex:none;padding:8px 16px;" onclick="cerrarModal('modalHistorial');editarCliente(${c.id})"><i class="fas fa-edit"></i> Editar</button>
            <button class="btn-sm del"  style="flex:none;padding:8px 16px;" onclick="cerrarModal('modalHistorial');eliminarCliente(${c.id},'${esc(c.nombre)}')"><i class="fas fa-trash"></i> Eliminar</button>
        </div>`;
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); });

// Init
cargarClientes();
</script>
</body>
</html>
