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
    <title>Empleados — Peluquería</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        /* ── Header ── */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px 0; flex-wrap: wrap; gap: 12px;
        }
        .page-header h2 { font-size: 22px; font-weight: 800; color: var(--text-color,#1e293b); margin: 0; }
        .header-right { display: flex; align-items: center; gap: 10px; }

        .btn-nuevo {
            display: flex; align-items: center; gap: 8px; padding: 9px 18px;
            background: #8b5cf6; color: #fff; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s;
        }
        .btn-nuevo:hover { background: #7c3aed; }

        /* ── Stats ── */
        .stats-row {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px; padding: 16px 24px 0;
        }
        .stat-box {
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 14px; padding: 16px;
            display: flex; align-items: center; gap: 12px;
        }
        .stat-icon {
            width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .si-purple { background: rgba(139,92,246,.12); color: #8b5cf6; }
        .si-green  { background: rgba(22,163,74,.12);  color: #16a34a; }
        .si-gray   { background: rgba(100,116,139,.1); color: #64748b; }
        .stat-n { font-size: 22px; font-weight: 800; line-height: 1; color: var(--text-color,#1e293b); }
        .stat-l { font-size: 11px; color: var(--muted-color,#64748b); margin-top: 3px; font-weight: 500; }

        /* ── Filtro ── */
        .filtro-bar { padding: 16px 24px 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .filter-btn {
            padding: 6px 16px; border-radius: 20px; border: 1px solid var(--border-color,#e5e7eb);
            background: transparent; font-size: 12px; font-weight: 600; cursor: pointer;
            color: var(--text-color,#374151); transition: all .15s;
        }
        .filter-btn.active { background: #8b5cf6; color: #fff; border-color: #8b5cf6; }
        .filter-btn:not(.active):hover { border-color: #8b5cf6; color: #8b5cf6; }

        /* ── Grid ── */
        .empleados-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px; padding: 20px 24px;
        }

        /* ── Tarjeta empleado ── */
        .emp-card {
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 18px; overflow: hidden;
            transition: box-shadow .18s, transform .18s;
        }
        .emp-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.09); transform: translateY(-2px); }
        .emp-card.inactivo { opacity: .6; }

        .emp-card-top {
            padding: 22px 20px 16px;
            display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center;
            position: relative;
        }
        .emp-card-top::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 56px;
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        }
        .emp-avatar {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 22px; font-weight: 900;
            border: 3px solid #fff; position: relative; z-index: 1;
            box-shadow: 0 4px 16px rgba(139,92,246,.3);
            flex-shrink: 0;
        }
        .emp-nombre { font-size: 16px; font-weight: 800; color: var(--text-color,#1e293b); line-height: 1.2; }
        .emp-cargo {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(139,92,246,.1); color: #7c3aed;
            border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 700;
        }
        .badge-inactivo {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(100,116,139,.1); color: #64748b;
            border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 700;
        }

        /* Datos de contacto */
        .emp-datos {
            padding: 0 20px 4px; display: flex; flex-direction: column; gap: 6px;
        }
        .emp-dato {
            display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted-color,#64748b);
        }
        .emp-dato i { width: 14px; text-align: center; font-size: 11px; color: #94a3b8; }

        /* Stats del empleado */
        .emp-stats {
            display: grid; grid-template-columns: 1fr 1fr;
            border-top: 1px solid var(--border-color,#f1f5f9); margin-top: 12px;
        }
        .emp-stat {
            padding: 12px 16px; text-align: center;
        }
        .emp-stat:first-child { border-right: 1px solid var(--border-color,#f1f5f9); }
        .emp-stat-n { font-size: 18px; font-weight: 900; color: #8b5cf6; }
        .emp-stat-l { font-size: 10px; color: var(--muted-color,#94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

        /* Footer acciones */
        .emp-footer {
            display: flex; border-top: 1px solid var(--border-color,#e5e7eb);
        }
        .emp-action {
            flex: 1; padding: 12px; border: none; background: transparent; cursor: pointer;
            font-size: 13px; font-weight: 600; color: var(--muted-color,#64748b);
            display: flex; align-items: center; justify-content: center; gap: 6px;
            transition: background .15s, color .15s;
        }
        .emp-action:first-child { border-right: 1px solid var(--border-color,#e5e7eb); }
        .emp-action:hover { background: rgba(139,92,246,.06); color: #8b5cf6; }
        .emp-action.danger:hover { background: rgba(239,68,68,.06); color: #dc2626; }
        .emp-action.success:hover { background: rgba(22,163,74,.06); color: #16a34a; }

        /* Empty */
        .empty-state {
            grid-column: 1/-1; padding: 60px 20px; text-align: center;
            color: var(--muted-color,#9ca3af); display: flex; flex-direction: column;
            align-items: center; gap: 12px;
        }
        .empty-state i { font-size: 48px; opacity: .35; }
        .empty-state p { font-size: 14px; }

        /* ── Modal ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5);
            z-index: 1000; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--card-bg,#fff); border-radius: 20px;
            width: 100%; max-width: 460px; overflow: hidden; margin: auto;
        }
        .modal-head {
            padding: 20px 24px; border-bottom: 1px solid var(--border-color,#e5e7eb);
            display: flex; align-items: center; justify-content: space-between;
            background: var(--hover-bg,#f8fafc);
        }
        .modal-head h3 { font-size: 16px; font-weight: 700; }
        .modal-body { padding: 22px 24px; display: flex; flex-direction: column; gap: 14px; }
        .modal-footer {
            padding: 14px 24px; border-top: 1px solid var(--border-color,#e5e7eb);
            display: flex; gap: 10px; justify-content: flex-end;
        }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 11px; font-weight: 700; color: var(--muted-color,#64748b); text-transform: uppercase; letter-spacing: .4px; }
        .form-group input, .form-group select {
            padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb);
            background: var(--card-bg,#fff); font-size: 14px; color: var(--text-color,#1e293b);
            outline: none; width: 100%; box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139,92,246,.12);
        }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .btn-primary {
            padding: 10px 20px; background: #8b5cf6; color: #fff; border: none;
            border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 7px; transition: background .15s;
        }
        .btn-primary:hover { background: #7c3aed; }
        .btn-cancel {
            padding: 10px 16px; border-radius: 10px;
            border: 1px solid var(--border-color,#e5e7eb); background: transparent;
            font-size: 14px; cursor: pointer; color: var(--text-color,#374151);
        }
        .btn-x { background: none; border: none; cursor: pointer; font-size: 16px; color: var(--muted-color,#64748b); }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <!-- Header -->
        <div class="page-header">
            <h2><i class="fas fa-user-tie" style="color:#8b5cf6;margin-right:10px;"></i>Equipo de Trabajo</h2>
            <div class="header-right">
                <button class="btn-nuevo" onclick="abrirNuevo()">
                    <i class="fas fa-plus"></i> Nuevo Empleado
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-icon si-purple"><i class="fas fa-users"></i></div>
                <div><div class="stat-n" id="stTotal">—</div><div class="stat-l">Total</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div>
                <div><div class="stat-n" id="stActivos">—</div><div class="stat-l">Activos</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon si-gray"><i class="fas fa-circle-pause"></i></div>
                <div><div class="stat-n" id="stInactivos">—</div><div class="stat-l">Inactivos</div></div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtro-bar">
            <button class="filter-btn active" data-f="todos" onclick="filtrar(this,'todos')">Todos</button>
            <button class="filter-btn" data-f="activos" onclick="filtrar(this,'activos')">Activos</button>
            <button class="filter-btn" data-f="inactivos" onclick="filtrar(this,'inactivos')">Inactivos</button>
        </div>

        <!-- Grid -->
        <div class="empleados-grid" id="empleadosGrid">
            <div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>
        </div>

    </div>
</div>

<!-- Modal Nuevo/Editar -->
<div class="modal-overlay" id="modalEmp">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitulo"><i class="fas fa-user-plus" style="color:#8b5cf6;margin-right:8px;"></i>Nuevo Empleado</h3>
            <button class="btn-x" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="eId">
            <div class="grid2">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="eNombre" placeholder="Ej: Laura">
                </div>
                <div class="form-group">
                    <label>Apellido *</label>
                    <input type="text" id="eApellido" placeholder="Ej: González">
                </div>
            </div>
            <div class="form-group">
                <label>Cargo / Especialidad</label>
                <input type="text" id="eCargo" placeholder="Ej: Estilista, Colorista, Manicurista…" list="cargosSugeridos">
                <datalist id="cargosSugeridos">
                    <option value="Estilista">
                    <option value="Colorista">
                    <option value="Manicurista">
                    <option value="Pedicurista">
                    <option value="Maquilladora">
                    <option value="Barbero">
                    <option value="Recepcionista">
                </datalist>
            </div>
            <div class="grid2">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" id="eTelefono" placeholder="11-4512-3456">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="eEmail" placeholder="email@ejemplo.com">
                </div>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="eActivo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="guardar()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script>
const API_EMP = '../../api/peluqueria/empleados.php';
const API_REP = '../../api/peluqueria/reportes.php';

let todos   = [];
let filtroActual = 'todos';
let statsEmp = {}; // { empId: { turnos, ingresos } }

// ── Cargar ────────────────────────────────────────────────────────────────────
async function cargar() {
    const [rEmp, rRep] = await Promise.all([
        fetch(API_EMP, { credentials: 'include' }),
        fetch(`${API_REP}?tipo=empleados&desde=${hace30()}&hasta=${hoy()}`, { credentials: 'include' })
    ]);
    const dEmp = await rEmp.json();
    const dRep = await rRep.json();

    todos = dEmp.data || [];

    // Indexar stats por id
    if (dRep.success) {
        (dRep.data || []).forEach(r => {
            statsEmp[r.empleado_id] = r;
        });
    }

    actualizarStats();
    renderGrid();
}

function actualizarStats() {
    const activos   = todos.filter(e => parseInt(e.activo) === 1).length;
    const inactivos = todos.length - activos;
    document.getElementById('stTotal').textContent    = todos.length;
    document.getElementById('stActivos').textContent  = activos;
    document.getElementById('stInactivos').textContent = inactivos;
}

function filtrar(btn, f) {
    filtroActual = f;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.toggle('active', b.dataset.f === f));
    renderGrid();
}

function renderGrid() {
    const grid = document.getElementById('empleadosGrid');
    let lista = todos;
    if (filtroActual === 'activos')   lista = todos.filter(e => parseInt(e.activo) === 1);
    if (filtroActual === 'inactivos') lista = todos.filter(e => parseInt(e.activo) !== 1);

    if (!lista.length) {
        grid.innerHTML = `<div class="empty-state"><i class="fas fa-user-slash"></i><p>No hay empleados ${filtroActual !== 'todos' ? 'en este filtro' : 'registrados'}</p></div>`;
        return;
    }

    grid.innerHTML = lista.map(e => {
        const activo  = parseInt(e.activo) === 1;
        const inicial = (e.nombre || '?').charAt(0).toUpperCase();
        const nombre  = `${e.nombre} ${e.apellido || ''}`.trim();
        const st      = statsEmp[e.id] || {};
        const turnos  = st.completados || 0;
        const ing     = parseFloat(st.ingresos || 0);

        return `<div class="emp-card ${activo ? '' : 'inactivo'}">
            <div class="emp-card-top">
                <div class="emp-avatar">${inicial}</div>
                <div class="emp-nombre">${esc(nombre)}</div>
                ${e.cargo ? `<div class="emp-cargo"><i class="fas fa-scissors" style="font-size:10px;"></i>${esc(e.cargo)}</div>` : ''}
                ${!activo ? `<div class="badge-inactivo"><i class="fas fa-pause" style="font-size:9px;"></i>Inactivo</div>` : ''}
            </div>

            ${(e.telefono || e.email) ? `
            <div class="emp-datos">
                ${e.telefono ? `<div class="emp-dato"><i class="fas fa-phone"></i>${esc(e.telefono)}</div>` : ''}
                ${e.email    ? `<div class="emp-dato"><i class="fas fa-envelope"></i>${esc(e.email)}</div>` : ''}
            </div>` : ''}

            <div class="emp-stats">
                <div class="emp-stat">
                    <div class="emp-stat-n">${turnos}</div>
                    <div class="emp-stat-l">Turnos (30d)</div>
                </div>
                <div class="emp-stat">
                    <div class="emp-stat-n">$${ing > 0 ? abrev(ing) : '0'}</div>
                    <div class="emp-stat-l">Ingresos (30d)</div>
                </div>
            </div>

            <div class="emp-footer">
                <button class="emp-action" onclick="editar(${e.id})">
                    <i class="fas fa-pencil"></i> Editar
                </button>
                <button class="emp-action ${activo ? 'danger' : 'success'}" onclick="toggleActivo(${e.id}, ${activo ? 0 : 1})">
                    <i class="fas fa-${activo ? 'pause' : 'play'}"></i>
                    ${activo ? 'Desactivar' : 'Activar'}
                </button>
            </div>
        </div>`;
    }).join('');
}

// ── CRUD ──────────────────────────────────────────────────────────────────────
function abrirNuevo() {
    document.getElementById('eId').value       = '';
    document.getElementById('eNombre').value   = '';
    document.getElementById('eApellido').value = '';
    document.getElementById('eCargo').value    = '';
    document.getElementById('eTelefono').value = '';
    document.getElementById('eEmail').value    = '';
    document.getElementById('eActivo').value   = '1';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-user-plus" style="color:#8b5cf6;margin-right:8px;"></i>Nuevo Empleado';
    document.getElementById('modalEmp').classList.add('open');
    setTimeout(() => document.getElementById('eNombre').focus(), 100);
}

function editar(id) {
    const e = todos.find(x => x.id == id);
    if (!e) return;
    document.getElementById('eId').value       = e.id;
    document.getElementById('eNombre').value   = e.nombre || '';
    document.getElementById('eApellido').value = e.apellido || '';
    document.getElementById('eCargo').value    = e.cargo || '';
    document.getElementById('eTelefono').value = e.telefono || '';
    document.getElementById('eEmail').value    = e.email || '';
    document.getElementById('eActivo').value   = e.activo ? '1' : '0';
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-pencil" style="color:#8b5cf6;margin-right:8px;"></i>Editar Empleado';
    document.getElementById('modalEmp').classList.add('open');
}

async function guardar() {
    const id      = parseInt(document.getElementById('eId').value) || 0;
    const nombre  = document.getElementById('eNombre').value.trim();
    const apellido = document.getElementById('eApellido').value.trim();
    if (!nombre) { alert('Ingresá el nombre'); return; }

    const body = {
        id: id || undefined,
        nombre, apellido,
        cargo:    document.getElementById('eCargo').value.trim(),
        telefono: document.getElementById('eTelefono').value.trim(),
        email:    document.getElementById('eEmail').value.trim(),
        activo:   parseInt(document.getElementById('eActivo').value),
    };
    const r = await fetch(API_EMP, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(body)
    });
    const d = await r.json();
    if (d.success) { cerrarModal(); cargar(); }
    else alert(d.message || 'Error al guardar');
}

async function toggleActivo(id, nuevoEstado) {
    const msg = nuevoEstado === 0 ? '¿Desactivar este empleado?' : '¿Activar este empleado?';
    if (!confirm(msg)) return;
    await fetch(API_EMP, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id, activo: nuevoEstado })
    });
    cargar();
}

function cerrarModal() { document.getElementById('modalEmp').classList.remove('open'); }

document.getElementById('modalEmp').addEventListener('click', e => {
    if (e.target === document.getElementById('modalEmp')) cerrarModal();
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function hoy()    { return new Date().toISOString().split('T')[0]; }
function hace30() { const d = new Date(); d.setDate(d.getDate()-30); return d.toISOString().split('T')[0]; }
function esc(s)   { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function abrev(n) {
    if (n >= 1000000) return (n/1000000).toFixed(1).replace('.0','') + 'M';
    if (n >= 1000)    return (n/1000).toFixed(1).replace('.0','') + 'k';
    return Number(n).toLocaleString('es-AR');
}

cargar();
</script>
</body>
</html>
