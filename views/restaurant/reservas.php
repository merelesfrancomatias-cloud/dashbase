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
    <title>Reservas — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        /* ── Layout ── */
        .reservas-wrap { display: flex; flex-direction: column; gap: 20px; }

        .rv-header {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }

        /* ── Navegación fecha ── */
        .fecha-nav {
            display: flex; align-items: center; gap: 10px;
        }
        .fecha-nav button {
            width: 36px; height: 36px; border-radius: 8px;
            border: 1.5px solid var(--border); background: white;
            cursor: pointer; font-size: 14px; color: var(--text-secondary);
            transition: var(--transition);
        }
        .fecha-nav button:hover { border-color: var(--primary); color: var(--primary); }
        .fecha-display {
            font-size: 17px; font-weight: 800; color: var(--text-primary);
            min-width: 180px; text-align: center; cursor: pointer;
        }
        .fecha-display:hover { color: var(--primary); }
        input[type="date"]#datepicker {
            opacity: 0; position: absolute; pointer-events: none;
        }

        /* ── Tarjetas resumen ── */
        .rv-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }
        .rv-stat {
            background: white; border-radius: 12px; padding: 14px 16px;
            border: 1.5px solid var(--border); text-align: center;
        }
        .rv-stat-num { font-size: 26px; font-weight: 800; color: var(--text-primary); }
        .rv-stat-lbl { font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; margin-top: 2px; }

        /* ── Timeline de horas ── */
        .timeline-wrap {
            display: flex; gap: 16px;
        }

        .horas-col {
            display: flex; flex-direction: column;
        }
        .hora-slot { height: 60px; display: flex; align-items: flex-start; }
        .hora-label {
            font-size: 11px; color: var(--text-secondary); font-weight: 600;
            min-width: 44px; padding-top: 2px;
        }
        .hora-line {
            flex: 1; border-top: 1px dashed var(--border); margin-top: 2px;
        }

        /* ── Lista de reservas ── */
        .reservas-lista { display: flex; flex-direction: column; gap: 10px; flex: 1; }

        .reserva-card {
            background: white; border-radius: 12px;
            border: 1.5px solid var(--border);
            padding: 14px 16px;
            display: flex; align-items: center; gap: 14px;
            cursor: pointer; transition: var(--transition);
            position: relative;
        }
        .reserva-card:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }

        .rv-estado-bar {
            width: 4px; border-radius: 4px; align-self: stretch; flex-shrink: 0;
        }
        .rv-hora {
            text-align: center; min-width: 56px;
        }
        .rv-hora-time { font-size: 17px; font-weight: 800; color: var(--text-primary); line-height: 1; }
        .rv-hora-dur  { font-size: 11px; color: var(--text-secondary); }

        .rv-info { flex: 1; }
        .rv-nombre { font-size: 15px; font-weight: 700; color: var(--text-primary); }
        .rv-detalle { font-size: 12px; color: var(--text-secondary); margin-top: 3px; }

        .rv-badge {
            padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
        }

        /* Colores estado */
        .badge-pendiente    { background: #FFF3CD; color: #856404; }
        .badge-confirmada   { background: #D1FAE5; color: #065F46; }
        .badge-sentada      { background: #DBEAFE; color: #1E40AF; }
        .badge-cancelada    { background: #F3F4F6; color: #6B7280; }
        .badge-no_show      { background: #FEE2E2; color: #991B1B; }

        .rv-bar-pendiente    { background: var(--warning); }
        .rv-bar-confirmada   { background: var(--success); }
        .rv-bar-sentada      { background: #4299E1; }
        .rv-bar-cancelada    { background: var(--text-secondary); }
        .rv-bar-no_show      { background: var(--error); }

        /* ── Modal nueva reserva ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 2000;
            align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: white; border-radius: 16px; padding: 28px;
            width: 100%; max-width: 440px;
            animation: slideUp .25s ease;
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }

        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 5px; }
        .form-input {
            width: 100%; padding: 10px 12px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 14px; outline: none; transition: border .2s;
        }
        .form-input:focus { border-color: var(--primary); }

        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--text-secondary);
        }
        .empty-state i { font-size: 48px; margin-bottom: 12px; opacity: .3; display: block; }

        @media (max-width: 600px) {
            .rv-stats { grid-template-columns: repeat(2, 1fr); }
            .rv-hora-time { font-size: 14px; }
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content" style="flex:1; padding: 24px; overflow-y:auto;">
        <?php include '../includes/header.php'; ?>

        <div class="reservas-wrap">

            <!-- Header -->
            <div class="rv-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <a href="<?= $base ?>/views/restaurant/mesas.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <div style="font-size:20px;font-weight:800;"><i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:8px;"></i>Reservas</div>
                        <div style="font-size:13px;color:var(--text-secondary);" id="subheaderFecha">Cargando…</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <!-- Navegación fecha -->
                    <div class="fecha-nav">
                        <button onclick="cambiarFecha(-1)"><i class="fas fa-chevron-left"></i></button>
                        <div class="fecha-display" onclick="abrirDatepicker()" id="fechaDisplay">—</div>
                        <input type="date" id="datepicker" onchange="setFechaFromInput(this.value)">
                        <button onclick="cambiarFecha(1)"><i class="fas fa-chevron-right"></i></button>
                        <button onclick="irHoy()" style="padding:0 14px;width:auto;font-size:12px;font-weight:700;">Hoy</button>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="abrirNuevaReserva()">
                        <i class="fas fa-plus"></i> Nueva reserva
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="rv-stats" id="rvStats">
                <div class="rv-stat"><div class="rv-stat-num" id="st_total">—</div><div class="rv-stat-lbl">Total</div></div>
                <div class="rv-stat"><div class="rv-stat-num" style="color:var(--success)" id="st_conf">—</div><div class="rv-stat-lbl">Confirmadas</div></div>
                <div class="rv-stat"><div class="rv-stat-num" style="color:var(--warning)" id="st_pend">—</div><div class="rv-stat-lbl">Pendientes</div></div>
                <div class="rv-stat"><div class="rv-stat-num" style="color:#4299E1" id="st_sent">—</div><div class="rv-stat-lbl">Sentadas</div></div>
                <div class="rv-stat"><div class="rv-stat-num" style="color:var(--error)" id="st_noshow">—</div><div class="rv-stat-lbl">No show</div></div>
            </div>

            <!-- Lista reservas -->
            <div class="reservas-lista" id="reservasLista">
                <div class="empty-state"><i class="fas fa-spinner fa-spin"></i><br>Cargando reservas…</div>
            </div>

        </div>
    </div>
</div>

<!-- Modal detalle/editar reserva -->
<div class="modal-overlay" id="modalReserva">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div style="font-size:18px;font-weight:800;" id="modalRvTitulo">Nueva Reserva</div>
            <button onclick="cerrarModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--text-secondary);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <input type="hidden" id="rv_id">

        <div class="form-group">
            <label class="form-label">Nombre del cliente *</label>
            <input type="text" class="form-input" id="rv_nombre" placeholder="Ej: García, Juan">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label class="form-label">Fecha *</label>
                <input type="date" class="form-input" id="rv_fecha">
            </div>
            <div class="form-group">
                <label class="form-label">Hora *</label>
                <input type="time" class="form-input" id="rv_hora" step="900">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label class="form-label">Personas</label>
                <input type="number" class="form-input" id="rv_personas" value="2" min="1">
            </div>
            <div class="form-group">
                <label class="form-label">Duración (min)</label>
                <input type="number" class="form-input" id="rv_duracion" value="90" step="15" min="30">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Mesa</label>
            <select class="form-input" id="rv_mesa">
                <option value="">— Sin asignar —</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Teléfono</label>
            <input type="text" class="form-input" id="rv_telefono" placeholder="+54 11 ...">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" id="rv_email" placeholder="email@ejemplo.com">
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select class="form-input" id="rv_estado">
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
                <option value="sentada">Sentada</option>
                <option value="cancelada">Cancelada</option>
                <option value="no_show">No Show</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea class="form-input" id="rv_observaciones" rows="2" placeholder="Alergias, ocasión especial, etc."></textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;" id="modalBtns">
            <button style="flex:1;padding:11px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;font-size:14px;background:white;font-weight:600;" onclick="cerrarModal()">Cancelar</button>
            <button style="flex:2;padding:11px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:700;background:linear-gradient(135deg,#0FD186,#0AB871);color:white;" onclick="guardarReserva()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script>
const BASE = window.APP_BASE;
let fechaActual = new Date();
fechaActual.setHours(0,0,0,0);
let mesasDisponibles = [];

/* ── INIT ── */
async function init() {
    await cargarMesasDrop();
    cargarReservas();
    renderFecha();
}

async function cargarMesasDrop() {
    const r = await fetch(`${BASE}/api/restaurant/mesas.php`);
    const d = await r.json();
    if (!d.success) return;
    mesasDisponibles = d.data.mesas;
    const sel = document.getElementById('rv_mesa');
    sel.innerHTML = '<option value="">— Sin asignar —</option>' +
        d.data.mesas.map(m => `<option value="${m.id}">Mesa ${m.numero}${m.sector_nombre ? ' ('+m.sector_nombre+')' : ''} · ${m.capacidad} p.</option>`).join('');
}

/* ── FECHA ── */
function renderFecha() {
    const opts = {weekday:'long', day:'numeric', month:'long', year:'numeric'};
    document.getElementById('fechaDisplay').textContent = fechaActual.toLocaleDateString('es-AR', opts);
    const d = fechaActual;
    const hoy = new Date(); hoy.setHours(0,0,0,0);
    const diff = Math.round((d - hoy) / 86400000);
    let sub = '';
    if (diff === 0) sub = 'Hoy';
    else if (diff === 1) sub = 'Mañana';
    else if (diff === -1) sub = 'Ayer';
    else sub = diff > 0 ? `En ${diff} días` : `Hace ${-diff} días`;
    document.getElementById('subheaderFecha').textContent = sub;
}

function cambiarFecha(dias) {
    fechaActual.setDate(fechaActual.getDate() + dias);
    renderFecha();
    cargarReservas();
}
function irHoy() {
    fechaActual = new Date(); fechaActual.setHours(0,0,0,0);
    renderFecha(); cargarReservas();
}
function abrirDatepicker() {
    const dp = document.getElementById('datepicker');
    dp.value = fechaISO(fechaActual);
    dp.style.pointerEvents = 'auto';
    dp.click();
    dp.style.pointerEvents = 'none';
}
function setFechaFromInput(v) {
    if (!v) return;
    fechaActual = new Date(v + 'T00:00:00');
    renderFecha(); cargarReservas();
}

/* ── CARGAR RESERVAS ── */
async function cargarReservas() {
    const fecha = fechaISO(fechaActual);
    const r     = await fetch(`${BASE}/api/restaurant/reservas.php?fecha=${fecha}&todas=1`);
    const d     = await r.json();
    if (!d.success) { renderListaVacia(); return; }
    const reservas = d.data;
    renderStats(reservas);
    renderLista(reservas);
}

function renderStats(rv) {
    document.getElementById('st_total').textContent   = rv.length;
    document.getElementById('st_conf').textContent    = rv.filter(r=>r.estado==='confirmada').length;
    document.getElementById('st_pend').textContent    = rv.filter(r=>r.estado==='pendiente').length;
    document.getElementById('st_sent').textContent    = rv.filter(r=>r.estado==='sentada').length;
    document.getElementById('st_noshow').textContent  = rv.filter(r=>r.estado==='no_show').length;
}

function renderLista(reservas) {
    const cont = document.getElementById('reservasLista');
    if (!reservas.length) {
        cont.innerHTML = `<div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            Sin reservas para este día<br>
            <button class="btn btn-primary btn-sm" style="margin-top:16px;" onclick="abrirNuevaReserva()"><i class="fas fa-plus"></i> Agregar</button>
        </div>`;
        return;
    }
    // Ordenar por hora
    reservas.sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));

    cont.innerHTML = reservas.map(r => {
        const hora = r.hora_inicio ? r.hora_inicio.substring(0,5) : '—';
        const fin  = calcularFin(r.hora_inicio, r.duracion_minutos);
        const personas = r.personas;
        const mesa     = r.mesa_numero ? `Mesa ${r.mesa_numero}` : 'Sin mesa';
        const etiquetasEstado = {pendiente:'Pendiente',confirmada:'Confirmada',sentada:'Sentada',cancelada:'Cancelada',no_show:'No Show'};

        return `<div class="reserva-card" onclick="editarReserva(${r.id})">
            <div class="rv-estado-bar rv-bar-${r.estado}"></div>
            <div class="rv-hora">
                <div class="rv-hora-time">${hora}</div>
                <div class="rv-hora-dur">${r.duracion_minutos||90}m</div>
            </div>
            <div class="rv-info">
                <div class="rv-nombre">${esc(r.cliente_nombre)}</div>
                <div class="rv-detalle">
                    <i class="fas fa-users"></i> ${personas} personas &nbsp;·&nbsp;
                    <i class="fas fa-chair"></i> ${mesa}
                    ${r.cliente_telefono ? ` &nbsp;·&nbsp; <i class="fas fa-phone"></i> ${esc(r.cliente_telefono)}` : ''}
                </div>
                ${r.observaciones ? `<div class="rv-detalle" style="margin-top:4px;font-style:italic;">"${esc(r.observaciones)}"</div>` : ''}
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <div class="rv-badge badge-${r.estado}">${etiquetasEstado[r.estado]||r.estado}</div>
                <div style="font-size:11px;color:var(--text-secondary);">${fin?'hasta '+fin:''}</div>
            </div>
        </div>`;
    }).join('');
}

function renderListaVacia() {
    document.getElementById('reservasLista').innerHTML =
        `<div class="empty-state"><i class="fas fa-exclamation-circle"></i>Error al cargar reservas</div>`;
}

/* ── MODAL ── */
function abrirNuevaReserva() {
    document.getElementById('rv_id').value      = '';
    document.getElementById('rv_nombre').value  = '';
    document.getElementById('rv_fecha').value   = fechaISO(fechaActual);
    document.getElementById('rv_hora').value    = '20:00';
    document.getElementById('rv_personas').value= '2';
    document.getElementById('rv_duracion').value= '90';
    document.getElementById('rv_mesa').value    = '';
    document.getElementById('rv_telefono').value= '';
    document.getElementById('rv_email').value   = '';
    document.getElementById('rv_estado').value  = 'pendiente';
    document.getElementById('rv_observaciones').value = '';
    document.getElementById('modalRvTitulo').textContent = 'Nueva Reserva';
    document.getElementById('modalBtns').innerHTML = `
        <button style="flex:1;padding:11px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;font-size:14px;background:white;font-weight:600;" onclick="cerrarModal()">Cancelar</button>
        <button style="flex:2;padding:11px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:700;background:linear-gradient(135deg,#0FD186,#0AB871);color:white;" onclick="guardarReserva()">
            <i class="fas fa-save"></i> Guardar
        </button>`;
    document.getElementById('modalReserva').classList.add('show');
}

async function editarReserva(id) {
    const r = await fetch(`${BASE}/api/restaurant/reservas.php?id=${id}`);
    const d = await r.json();
    if (!d.success) return;
    const rv = d.data;

    document.getElementById('rv_id').value      = rv.id;
    document.getElementById('rv_nombre').value  = rv.cliente_nombre;
    document.getElementById('rv_fecha').value   = rv.fecha_reserva;
    document.getElementById('rv_hora').value    = rv.hora_inicio ? rv.hora_inicio.substring(0,5) : '';
    document.getElementById('rv_personas').value= rv.personas;
    document.getElementById('rv_duracion').value= rv.duracion_minutos || 90;
    document.getElementById('rv_mesa').value    = rv.mesa_id || '';
    document.getElementById('rv_telefono').value= rv.cliente_telefono || '';
    document.getElementById('rv_email').value   = rv.cliente_email || '';
    document.getElementById('rv_estado').value  = rv.estado;
    document.getElementById('rv_observaciones').value = rv.observaciones || '';
    document.getElementById('modalRvTitulo').textContent = 'Editar Reserva';
    document.getElementById('modalBtns').innerHTML = `
        <button style="flex:1;padding:11px;border:1.5px solid var(--error);border-radius:10px;cursor:pointer;font-size:14px;background:white;font-weight:600;color:var(--error);" onclick="cancelarReserva(${rv.id})">
            <i class="fas fa-times"></i> Cancelar reserva
        </button>
        <button style="flex:2;padding:11px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:700;background:linear-gradient(135deg,#0FD186,#0AB871);color:white;" onclick="guardarReserva()">
            <i class="fas fa-save"></i> Guardar
        </button>`;
    document.getElementById('modalReserva').classList.add('show');
}

async function guardarReserva() {
    const id      = document.getElementById('rv_id').value;
    const payload = {
        cliente_nombre:   document.getElementById('rv_nombre').value.trim(),
        fecha_reserva:    document.getElementById('rv_fecha').value,
        hora_inicio:      document.getElementById('rv_hora').value,
        personas:         document.getElementById('rv_personas').value,
        duracion_minutos: document.getElementById('rv_duracion').value,
        mesa_id:          document.getElementById('rv_mesa').value || null,
        cliente_telefono: document.getElementById('rv_telefono').value,
        cliente_email:    document.getElementById('rv_email').value,
        estado:           document.getElementById('rv_estado').value,
        observaciones:    document.getElementById('rv_observaciones').value,
    };
    if (!payload.cliente_nombre || !payload.fecha_reserva || !payload.hora_inicio) {
        showToast('Completá los campos obligatorios', 'error'); return;
    }

    let url    = `${BASE}/api/restaurant/reservas.php`;
    let method = 'POST';
    if (id) { url += `?id=${id}`; method = 'PUT'; }

    const r = await fetch(url, {
        method, headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.success) {
        cerrarModal();
        showToast(id ? 'Reserva actualizada' : 'Reserva creada', 'success');
        cargarReservas();
    } else {
        showToast(d.message || 'Error al guardar', 'error');
    }
}

async function cancelarReserva(id) {
    if (!confirm('¿Cancelar esta reserva?')) return;
    await fetch(`${BASE}/api/restaurant/reservas.php?id=${id}`, { method: 'DELETE' });
    cerrarModal();
    showToast('Reserva cancelada', 'success');
    cargarReservas();
}

function cerrarModal() { document.getElementById('modalReserva').classList.remove('show'); }

/* ── UTILS ── */
function fechaISO(d) {
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}
function calcularFin(hora, min) {
    if (!hora || !min) return '';
    const [h, m] = hora.split(':').map(Number);
    const fin = new Date(2000,0,1,h,m+parseInt(min));
    return String(fin.getHours()).padStart(2,'0') + ':' + String(fin.getMinutes()).padStart(2,'0');
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
        background:${type==='success'?'#0FD186':'#F56565'};color:white;
        padding:12px 24px;border-radius:10px;font-weight:600;font-size:14px;
        z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2);`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

init();
</script>
</body>
</html>
