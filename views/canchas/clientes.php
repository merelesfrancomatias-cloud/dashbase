<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Canchas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .search-bar { display:flex;gap:12px;align-items:center;flex-wrap:wrap; }
        .search-input { flex:1;min-width:220px;padding:10px 16px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary); }
        .search-input:focus { outline:none;border-color:var(--primary); }
        .cliente-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;transition:var(--transition);cursor:pointer; }
        .cliente-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-2px); }
        .avatar { width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:16px;flex-shrink:0; }
        .stat-mini { font-size:12px;color:var(--text-secondary);display:flex;align-items:center;gap:4px; }
        .stat-mini strong { color:var(--text-primary);font-size:13px; }
        .empty-state { text-align:center;padding:60px 20px;color:var(--text-secondary); }
        .empty-state i { font-size:48px;margin-bottom:16px;opacity:.3;display:block; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface);border-radius:16px;width:100%;max-width:480px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .modal h3 { margin:0 0 20px;font-size:18px;color:var(--text-primary); }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:6px; }
        .form-control { width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);box-sizing:border-box; }
        .form-control:focus { outline:none;border-color:var(--primary); }
        .modal-actions { display:flex;gap:10px;justify-content:flex-end;margin-top:20px; }
        .historial-item { display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border); }
        .historial-item:last-child { border-bottom:none; }
        .grid-clientes { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px; }
        .badge-reservas { background:rgba(22,163,74,.12);color:#16a34a;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Page header -->
            <div class="page-header" style="background:var(--surface);padding:22px 24px;border-radius:14px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,.07);border:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 style="margin:0;font-size:22px;color:var(--text-primary);font-weight:700;">
                        <i class="fas fa-users" style="color:#16a34a;margin-right:8px;"></i>Clientes Frecuentes
                    </h1>
                    <p style="margin:4px 0 0;color:var(--text-secondary);font-size:14px;">Fichas de clientes con historial de reservas</p>
                </div>
                <button class="btn btn-primary" onclick="abrirModal()">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" id="stat-total">0</div>
                        <div class="stat-label">Total Clientes</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" id="stat-reservas">0</div>
                        <div class="stat-label">Reservas este mes</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-star"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" id="stat-frecuentes">0</div>
                        <div class="stat-label">Clientes frecuentes</div>
                    </div>
                </div>
            </div>

            <!-- Buscador -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-body" style="padding:16px 20px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color:var(--text-secondary);"></i>
                        <input type="text" id="inputBusqueda" class="search-input" placeholder="Buscar por nombre, teléfono o email..." oninput="buscar(this.value)">
                        <span id="badge-count" style="font-size:13px;color:var(--text-secondary);white-space:nowrap;"></span>
                    </div>
                </div>
            </div>

            <!-- Grid de clientes -->
            <div class="grid-clientes" id="gridClientes">
                <div class="empty-state" style="grid-column:1/-1;">
                    <i class="fas fa-users"></i>
                    <p>Cargando clientes...</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Crear/Editar Cliente -->
<div class="modal-overlay" id="modalCliente">
    <div class="modal">
        <h3 id="modalTitulo"><i class="fas fa-user-plus" style="color:#16a34a;margin-right:8px;"></i>Nuevo Cliente</h3>
        <input type="hidden" id="clienteId">
        <div class="form-group">
            <label>Nombre completo *</label>
            <input type="text" id="inpNombre" class="form-control" placeholder="Ej: Juan Pérez">
        </div>
        <div class="form-group">
            <label>Teléfono *</label>
            <input type="tel" id="inpTelefono" class="form-control" placeholder="Ej: 11-4567-8901">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" id="inpEmail" class="form-control" placeholder="Ej: juan@email.com">
        </div>
        <div class="form-group">
            <label>Notas</label>
            <textarea id="inpNotas" class="form-control" rows="2" placeholder="Observaciones, preferencias..."></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarCliente()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Historial Cliente -->
<div class="modal-overlay" id="modalHistorial">
    <div class="modal" style="max-width:560px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="margin:0;" id="histTitulo">Historial</h3>
            <button onclick="cerrarHistorial()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <!-- Stats del cliente -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;" id="histStats"></div>
        <!-- Lista reservas -->
        <div style="max-height:300px;overflow-y:auto;" id="histLista"></div>
        <div style="margin-top:16px;display:flex;gap:10px;justify-content:flex-end;">
            <button class="btn btn-secondary" id="btnEditarCliente" onclick="editarDesdeHistorial()"><i class="fas fa-edit"></i> Editar</button>
            <button class="btn btn-danger"    id="btnEliminarCliente" onclick="eliminarDesdeHistorial()"><i class="fas fa-trash"></i> Eliminar</button>
        </div>
    </div>
</div>

<script>
let clientes = [];
let clienteActual = null;

async function cargarClientes(q = '') {
    const url = q
        ? `../../api/canchas/clientes.php?search=${encodeURIComponent(q)}`
        : '../../api/canchas/clientes.php';
    const r = await fetch(url);
    const j = await r.json();
    clientes = j.data || [];
    renderClientes(clientes);
    actualizarStats(clientes);
}

function actualizarStats(lista) {
    document.getElementById('stat-total').textContent = lista.length;
    const totalRes = lista.reduce((s, c) => s + (parseInt(c.total_reservas) || 0), 0);
    document.getElementById('stat-reservas').textContent = totalRes;
    const frecuentes = lista.filter(c => (parseInt(c.total_reservas) || 0) >= 3).length;
    document.getElementById('stat-frecuentes').textContent = frecuentes;
    document.getElementById('badge-count').textContent = lista.length + ' cliente' + (lista.length !== 1 ? 's' : '');
}

function renderClientes(lista) {
    const grid = document.getElementById('gridClientes');
    if (!lista.length) {
        grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-users"></i><p>No se encontraron clientes</p><button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus"></i> Agregar primero</button></div>`;
        return;
    }
    grid.innerHTML = lista.map(c => {
        const ini = (c.nombre || '?').charAt(0).toUpperCase();
        const ultima = c.ultima_reserva ? new Date(c.ultima_reserva + 'T00:00:00').toLocaleDateString('es-AR', {day:'2-digit',month:'short'}) : '—';
        const gasto  = c.total_gastado ? '$' + Number(c.total_gastado).toLocaleString('es-AR') : '$0';
        const nRes   = c.total_reservas || 0;
        return `
        <div class="cliente-card" onclick="verHistorial(${c.id})">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div class="avatar">${ini}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.nombre)}</div>
                    <div style="font-size:12px;color:var(--text-secondary);">${esc(c.telefono)}</div>
                </div>
                ${nRes >= 3 ? '<span class="badge-reservas"><i class="fas fa-star"></i> Frecuente</span>' : ''}
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div class="stat-mini"><i class="fas fa-calendar-check"></i><span>${nRes} reserva${nRes !== 1 ? 's' : ''}</span></div>
                <div class="stat-mini"><i class="fas fa-dollar-sign"></i><strong>${gasto}</strong></div>
                <div class="stat-mini"><i class="fas fa-clock"></i><span>Última: ${ultima}</span></div>
            </div>
            ${c.notas ? `<div style="margin-top:10px;font-size:12px;color:var(--text-secondary);background:var(--background);padding:8px 10px;border-radius:8px;">${esc(c.notas)}</div>` : ''}
        </div>`;
    }).join('');
}

let busquedaTimer;
function buscar(q) {
    clearTimeout(busquedaTimer);
    busquedaTimer = setTimeout(() => cargarClientes(q), 300);
}

function abrirModal(c = null) {
    clienteActual = c;
    document.getElementById('modalTitulo').innerHTML = c
        ? '<i class="fas fa-user-edit" style="color:#16a34a;margin-right:8px;"></i>Editar Cliente'
        : '<i class="fas fa-user-plus" style="color:#16a34a;margin-right:8px;"></i>Nuevo Cliente';
    document.getElementById('clienteId').value   = c ? c.id : '';
    document.getElementById('inpNombre').value   = c ? c.nombre : '';
    document.getElementById('inpTelefono').value = c ? c.telefono : '';
    document.getElementById('inpEmail').value    = c ? (c.email || '') : '';
    document.getElementById('inpNotas').value    = c ? (c.notas || '') : '';
    document.getElementById('modalCliente').classList.add('open');
    document.getElementById('inpNombre').focus();
}

function cerrarModal() { document.getElementById('modalCliente').classList.remove('open'); }

async function guardarCliente() {
    const id       = document.getElementById('clienteId').value;
    const nombre   = document.getElementById('inpNombre').value.trim();
    const telefono = document.getElementById('inpTelefono').value.trim();
    const email    = document.getElementById('inpEmail').value.trim();
    const notas    = document.getElementById('inpNotas').value.trim();

    if (!nombre) { alert('El nombre es requerido'); return; }
    if (!telefono) { alert('El teléfono es requerido'); return; }

    const method = id ? 'PUT' : 'POST';
    const body   = { nombre, telefono, email, notas };
    if (id) body.id = parseInt(id);

    const r = await fetch('../../api/canchas/clientes.php', {
        method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(body)
    });
    const j = await r.json();
    if (!j.success) { alert(j.message || 'Error'); return; }
    cerrarModal();
    cerrarHistorial();
    cargarClientes();
}

async function verHistorial(id) {
    const r = await fetch(`../../api/canchas/clientes.php?id=${id}`);
    const j = await r.json();
    if (!j.success) return;
    const c = j.data;
    clienteActual = c;

    document.getElementById('histTitulo').textContent = c.nombre;
    document.getElementById('btnEditarCliente').dataset.id = id;
    document.getElementById('btnEliminarCliente').dataset.id = id;

    const s = c.stats || {};
    document.getElementById('histStats').innerHTML = `
        <div style="background:var(--background);padding:12px;border-radius:10px;text-align:center;">
            <div style="font-size:20px;font-weight:700;color:#16a34a;">${s.total_reservas || 0}</div>
            <div style="font-size:11px;color:var(--text-secondary);">Reservas</div>
        </div>
        <div style="background:var(--background);padding:12px;border-radius:10px;text-align:center;">
            <div style="font-size:20px;font-weight:700;color:#3b82f6;">$${Number(s.total_gastado || 0).toLocaleString('es-AR')}</div>
            <div style="font-size:11px;color:var(--text-secondary);">Total gastado</div>
        </div>
        <div style="background:var(--background);padding:12px;border-radius:10px;text-align:center;">
            <div style="font-size:14px;font-weight:700;color:var(--text-primary);">${s.ultima_visita ? new Date(s.ultima_visita + 'T00:00:00').toLocaleDateString('es-AR', {day:'2-digit',month:'short',year:'numeric'}) : '—'}</div>
            <div style="font-size:11px;color:var(--text-secondary);">Última visita</div>
        </div>
    `;

    const hist = c.historial || [];
    if (!hist.length) {
        document.getElementById('histLista').innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:20px;">Sin reservas registradas</p>';
    } else {
        document.getElementById('histLista').innerHTML = hist.map(h => `
            <div class="historial-item">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(22,163,74,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-futbol" style="color:#16a34a;font-size:14px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);">${esc(h.cancha_nombre)} <span style="color:var(--text-secondary);font-weight:400;">· ${esc(h.deporte)}</span></div>
                    <div style="font-size:12px;color:var(--text-secondary);">${new Date(h.fecha + 'T00:00:00').toLocaleDateString('es-AR', {weekday:'short',day:'2-digit',month:'short'})} · ${h.hora_inicio.slice(0,5)} – ${h.hora_fin.slice(0,5)}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:700;font-size:13px;color:var(--text-primary);">$${Number(h.monto || 0).toLocaleString('es-AR')}</div>
                    <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:${h.estado === 'confirmada' ? 'rgba(72,187,120,.15)' : h.estado === 'pendiente' ? 'rgba(246,173,85,.15)' : 'rgba(160,174,192,.15)'};color:${h.estado === 'confirmada' ? '#22c55e' : h.estado === 'pendiente' ? '#f59e0b' : '#94a3b8'};">${h.estado}</span>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('modalHistorial').classList.add('open');
}

function cerrarHistorial() { document.getElementById('modalHistorial').classList.remove('open'); }

function editarDesdeHistorial() {
    cerrarHistorial();
    setTimeout(() => abrirModal(clienteActual), 150);
}

async function eliminarDesdeHistorial() {
    if (!confirm('¿Eliminar este cliente?')) return;
    const id = clienteActual.id;
    const r = await fetch(`../../api/canchas/clientes.php?id=${id}`, { method: 'DELETE' });
    const j = await r.json();
    if (!j.success) { alert(j.message); return; }
    cerrarHistorial();
    cargarClientes();
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Cerrar modal al click fuera
document.getElementById('modalCliente').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarModal(); });
document.getElementById('modalHistorial').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarHistorial(); });

cargarClientes();
</script>
</body>
</html>
