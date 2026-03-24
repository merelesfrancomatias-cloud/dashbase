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
    <title>Agenda de Turnos — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        /* ── Toolbar ── */
        .agenda-toolbar {
            display: flex; align-items: center; gap: 12px;
            padding: 18px 24px 0; flex-wrap: wrap;
        }
        .fecha-nav {
            display: flex; align-items: center; gap: 6px;
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 12px; padding: 4px 6px;
        }
        .fecha-nav button {
            width: 32px; height: 32px; border: none; background: transparent;
            border-radius: 8px; cursor: pointer; color: var(--text-color,#374151);
            font-size: 13px; display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .fecha-nav button:hover { background: var(--hover-bg,#f1f5f9); }
        .fecha-nav .fecha-label {
            font-size: 15px; font-weight: 700; color: var(--text-color,#1e293b);
            min-width: 180px; text-align: center;
        }
        .btn-hoy {
            padding: 7px 14px; border-radius: 10px;
            border: 1px solid var(--border-color,#e5e7eb); background: transparent;
            font-size: 12px; font-weight: 600; cursor: pointer; color: var(--text-color,#374151);
            transition: all .15s;
        }
        .btn-hoy:hover { border-color: #8b5cf6; color: #8b5cf6; background: rgba(139,92,246,.06); }
        .btn-nuevo-turno {
            margin-left: auto; display: flex; align-items: center; gap: 8px;
            padding: 9px 18px; background: #8b5cf6; color: #fff;
            border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;
            transition: background .15s;
        }
        .btn-nuevo-turno:hover { background: #7c3aed; }

        /* ── Stats ── */
        .agenda-stats {
            display: grid; grid-template-columns: repeat(auto-fill,minmax(150px,1fr));
            gap: 12px; padding: 16px 24px 0;
        }
        .a-stat {
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 12px; padding: 14px 16px;
            display: flex; align-items: center; gap: 12px;
        }
        .a-stat-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;
        }
        .a-stat-n { font-size: 22px; font-weight: 900; line-height: 1; }
        .a-stat-l { font-size: 11px; color: var(--muted-color,#64748b); font-weight: 600; margin-top: 2px; }

        /* ── Timeline ── */
        .agenda-wrap { padding: 16px 24px; }
        .agenda-list { display: flex; flex-direction: column; gap: 8px; }

        /* Separador de hora */
        .hora-sep {
            display: flex; align-items: center; gap: 12px;
            margin: 6px 0 2px;
        }
        .hora-sep-time {
            font-size: 11px; font-weight: 800; color: var(--muted-color,#9ca3af);
            min-width: 44px; text-align: right; font-variant-numeric: tabular-nums;
        }
        .hora-sep-line { flex: 1; height: 1px; background: var(--border-color,#f1f5f9); }

        /* Tarjeta turno */
        .turno-card {
            display: flex; align-items: stretch; gap: 0;
            border-radius: 14px; overflow: hidden;
            border: 1px solid var(--border-color,#e5e7eb);
            background: var(--card-bg,#fff);
            transition: box-shadow .15s, transform .15s;
            cursor: pointer;
        }
        .turno-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-1px); }
        .turno-accent { width: 5px; flex-shrink: 0; }
        .turno-body { flex: 1; padding: 14px 16px; display: flex; align-items: center; gap: 16px; }
        .turno-time {
            min-width: 72px; text-align: center;
            font-size: 13px; font-weight: 700; color: var(--text-color,#374151);
            font-variant-numeric: tabular-nums; line-height: 1.4;
        }
        .turno-time .duracion { font-size: 10px; color: var(--muted-color,#9ca3af); font-weight: 500; }
        .turno-divider { width: 1px; background: var(--border-color,#e5e7eb); align-self: stretch; flex-shrink: 0; }
        .turno-info { flex: 1; min-width: 0; padding: 0 16px; }
        .turno-cliente { font-size: 15px; font-weight: 700; color: var(--text-color,#1e293b); }
        .turno-servicio { font-size: 12px; color: var(--muted-color,#64748b); margin-top: 2px; }
        .turno-tel { font-size: 11px; color: var(--muted-color,#9ca3af); margin-top: 3px; }
        .turno-precio { font-size: 16px; font-weight: 800; color: #8b5cf6; white-space: nowrap; }
        .turno-precio .precio-label { font-size: 10px; color: var(--muted-color,#9ca3af); font-weight: 500; display: block; }
        .turno-actions { display: flex; gap: 6px; padding-right: 12px; align-items: center; }
        .t-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--muted-color,#64748b); transition: all .15s; }
        .t-btn:hover { border-color: #8b5cf6; color: #8b5cf6; background: rgba(139,92,246,.06); }

        /* Badge estado */
        .badge-turno {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 20px; font-size: 10px; font-weight: 700;
            white-space: nowrap;
        }
        .st-pendiente  { background: rgba(100,116,139,.1);  color: #64748b; }
        .st-confirmado { background: rgba(59,130,246,.1);   color: #2563eb; }
        .st-en_curso   { background: rgba(245,158,11,.12);  color: #d97706; }
        .st-completado { background: rgba(22,163,74,.1);    color: #16a34a; }
        .st-cancelado  { background: rgba(239,68,68,.1);    color: #dc2626; }
        .st-no_show    { background: rgba(156,163,175,.1);  color: #9ca3af; }

        .empty-day {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 60px 20px; color: var(--muted-color,#9ca3af); gap: 10px;
        }
        .empty-day i { font-size: 40px; }
        .empty-day p { font-size: 14px; }

        /* ── MODAL ── */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; overflow-y: auto; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: var(--card-bg,#fff); border-radius: 20px; width: 100%; max-width: 500px; overflow: hidden; margin: auto; }
        .modal-head { padding: 20px 24px; border-bottom: 1px solid var(--border-color,#e5e7eb); display: flex; align-items: center; justify-content: space-between; background: var(--hover-bg,#f8fafc); }
        .modal-head h3 { font-size: 16px; font-weight: 700; }
        .modal-body { padding: 22px 24px; display: flex; flex-direction: column; gap: 14px; }
        .modal-footer { padding: 14px 24px; border-top: 1px solid var(--border-color,#e5e7eb); display: flex; gap: 10px; justify-content: flex-end; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 11px; font-weight: 700; color: var(--muted-color,#64748b); text-transform: uppercase; letter-spacing: .4px; }
        .form-group input, .form-group select, .form-group textarea { padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); background: var(--card-bg,#fff); font-size: 14px; color: var(--text-color,#1e293b); outline: none; width: 100%; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139,92,246,.12); }
        .btn-primary { padding: 10px 20px; background: #8b5cf6; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-cancel { padding: 10px 16px; border-radius: 10px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; font-size: 14px; cursor: pointer; }

        /* Modal detalle */
        .detalle-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border-color,#f1f5f9); }
        .detalle-row:last-child { border: none; }
        .detalle-label { font-size: 12px; font-weight: 600; color: var(--muted-color,#64748b); }
        .detalle-val { font-size: 14px; font-weight: 600; color: var(--text-color,#1e293b); }
        .estado-btns { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .estado-btn { padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; font-size: 11px; font-weight: 700; cursor: pointer; transition: all .15s; }
        .estado-btn:hover { border-color: #8b5cf6; color: #8b5cf6; background: rgba(139,92,246,.06); }
        .estado-btn.active { background: #8b5cf6; color: #fff; border-color: #8b5cf6; }

        /* ── Multi-servicios ── */
        .servicios-lista { display: flex; flex-direction: column; gap: 6px; min-height: 10px; }
        .servicio-tag {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(139,92,246,.08); border: 1px solid rgba(139,92,246,.2);
            border-radius: 8px; padding: 7px 10px; font-size: 13px;
        }
        .servicio-tag-info { flex: 1; }
        .servicio-tag-nombre { font-weight: 600; color: #7c3aed; }
        .servicio-tag-meta   { font-size: 11px; color: var(--muted-color,#64748b); margin-top: 1px; }
        .servicio-tag-del { background: none; border: none; cursor: pointer; color: #ef4444; font-size: 14px; padding: 2px 6px; border-radius: 6px; }
        .servicio-tag-del:hover { background: rgba(239,68,68,.1); }
        .add-servicio-row { display: flex; gap: 8px; align-items: center; }
        .add-servicio-row select { flex: 1; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); font-size: 13px; background: var(--card-bg,#fff); color: var(--text-color,#1e293b); outline: none; }
        .add-servicio-row select:focus { border-color: #8b5cf6; }
        .btn-add-serv { padding: 8px 14px; background: #8b5cf6; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }

        /* ── Autocomplete Cliente ── */
        .ac-wrap { position: relative; }
        .ac-dropdown {
            position: absolute; top: calc(100% + 4px); left: 0; right: 0;
            background: var(--card-bg,#fff); border: 1px solid #8b5cf6;
            border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
            z-index: 9999; max-height: 220px; overflow-y: auto; display: none;
        }
        .ac-dropdown.show { display: block; }
        .ac-item {
            padding: 10px 14px; cursor: pointer; border-bottom: 1px solid var(--border-color,#f1f5f9);
            transition: background .12s;
        }
        .ac-item:last-child { border-bottom: none; }
        .ac-item:hover, .ac-item.sel { background: rgba(139,92,246,.08); }
        .ac-item-nombre { font-size: 14px; font-weight: 600; color: var(--text-color,#1e293b); }
        .ac-item-tel { font-size: 12px; color: var(--muted-color,#64748b); margin-top: 1px; }
        .cliente-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.3);
            border-radius: 6px; padding: 3px 8px; font-size: 12px; color: #7c3aed;
            margin-top: 4px; font-weight: 600;
        }
        .cliente-badge button { background: none; border: none; cursor: pointer; color: #ef4444; font-size: 12px; padding: 0 2px; line-height: 1; }
        .btn-add-serv:hover { background: #7c3aed; }
        .totales-servicios {
            background: var(--hover-bg,#f8fafc); border-radius: 8px; padding: 8px 12px;
            display: flex; justify-content: space-between; font-size: 13px; font-weight: 600;
            color: var(--text-color,#1e293b); border: 1px solid var(--border-color,#e5e7eb);
        }
        .totales-servicios span { color: #8b5cf6; }
        .servicios-detalle { display: flex; flex-direction: column; gap: 5px; margin-top: 6px; }
        .serv-det-item { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; border-bottom: 1px solid var(--border-color,#f1f5f9); }
        .serv-det-item:last-child { border: none; }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <!-- Toolbar -->
        <div class="agenda-toolbar">
            <div class="fecha-nav">
                <button onclick="cambiarDia(-1)"><i class="fas fa-chevron-left"></i></button>
                <div class="fecha-label" id="fechaLabel">Cargando…</div>
                <button onclick="cambiarDia(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
            <button class="btn-hoy" onclick="irHoy()">Hoy</button>
            <input type="date" id="pickerFecha" style="padding:7px 10px;border-radius:10px;border:1px solid var(--border-color,#e5e7eb);font-size:13px;background:var(--card-bg,#fff);color:var(--text-color);" oninput="setFecha(this.value)">
            <button class="btn-nuevo-turno" onclick="abrirNuevo()">
                <i class="fas fa-plus"></i> Nuevo Turno
            </button>
        </div>

        <!-- Stats del día -->
        <div class="agenda-stats">
            <div class="a-stat">
                <div class="a-stat-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6;"><i class="fas fa-calendar-day"></i></div>
                <div><div class="a-stat-n" id="stTotal" style="color:#8b5cf6;">0</div><div class="a-stat-l">Total</div></div>
            </div>
            <div class="a-stat">
                <div class="a-stat-icon" style="background:rgba(245,158,11,.1);color:#d97706;"><i class="fas fa-fire"></i></div>
                <div><div class="a-stat-n" id="stEnCurso" style="color:#d97706;">0</div><div class="a-stat-l">En curso</div></div>
            </div>
            <div class="a-stat">
                <div class="a-stat-icon" style="background:rgba(22,163,74,.1);color:#16a34a;"><i class="fas fa-check-circle"></i></div>
                <div><div class="a-stat-n" id="stCompletados" style="color:#16a34a;">0</div><div class="a-stat-l">Completados</div></div>
            </div>
            <div class="a-stat">
                <div class="a-stat-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6;"><i class="fas fa-dollar-sign"></i></div>
                <div><div class="a-stat-n" id="stFacturado" style="color:#8b5cf6;">$0</div><div class="a-stat-l">Facturado</div></div>
            </div>
        </div>

        <div class="agenda-wrap">
            <div class="agenda-list" id="agendaList">
                <div class="empty-day"><i class="fas fa-spinner fa-spin"></i><p>Cargando…</p></div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Nuevo/Editar Turno -->
<div class="modal-overlay" id="modalTurno">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTituloTurno"><i class="fas fa-calendar-plus" style="color:#8b5cf6;margin-right:8px;"></i> Nuevo Turno</h3>
            <button style="background:none;border:none;cursor:pointer;font-size:16px;" onclick="cerrarModal('modalTurno')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="tId">
            <input type="hidden" id="tClienteId">
            <div class="form-group">
                <label>Cliente *</label>
                <div class="ac-wrap">
                    <input type="text" id="tCliente" placeholder="Buscar o escribir nombre del cliente…" autocomplete="off">
                    <div class="ac-dropdown" id="acDropdown"></div>
                </div>
                <div id="clienteSelBadge" style="display:none;"></div>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" id="tTelefono" placeholder="Ej: 11-4512-3456">
            </div>
            <!-- Servicios múltiples -->
            <div class="form-group">
                <label>Servicios</label>
                <div class="servicios-lista" id="serviciosLista"></div>
                <div class="add-servicio-row" style="margin-top:6px;">
                    <select id="tServicio">
                        <option value="">— Seleccionar servicio —</option>
                    </select>
                    <button type="button" class="btn-add-serv" onclick="agregarServicio()">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
                <div class="totales-servicios" id="totalesServicios" style="display:none;margin-top:6px;">
                    <div id="totDuracion">0 min</div>
                    <div>Total: <span id="totPrecio">$0</span></div>
                </div>
            </div>
            <div class="grid2">
                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" id="tFecha">
                </div>
                <div class="form-group">
                    <label>Hora inicio *</label>
                    <input type="time" id="tHoraInicio" step="900">
                </div>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="tEstado">
                    <option value="pendiente">Pendiente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="en_curso">En curso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="no_show">No show</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea id="tNotas" rows="2" placeholder="Observaciones…" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal('modalTurno')">Cancelar</button>
            <button class="btn-primary" onclick="guardarTurno()">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="detNombre">Detalle del Turno</h3>
            <button style="background:none;border:none;cursor:pointer;font-size:16px;" onclick="cerrarModal('modalDetalle')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detalleBody"></div>
        <div class="modal-footer" id="detallePies"></div>
    </div>
</div>

<script>
const API_T  = '../../api/peluqueria/turnos.php';
const API_S  = '../../api/peluqueria/servicios.php';
let fechaActual = hoy();
let servicios   = [];
let serviciosSeleccionados = []; // [{servicio_id, servicio_nombre, duracion_min, precio}]

async function cargarServicios() {
    const r = await fetch(API_S, { credentials: 'include' });
    const d = await r.json();
    if (d.success) {
        servicios = d.data || [];
        const sel = document.getElementById('tServicio');
        sel.innerHTML = '<option value="">— Seleccionar servicio —</option>' +
            servicios.map(s => `<option value="${s.id}" data-dur="${s.duracion_min}" data-precio="${s.precio}" data-nombre="${s.nombre}">${s.nombre} (${s.duracion_min}min · $${Number(s.precio).toLocaleString('es-AR')})</option>`).join('');
    }
}

function agregarServicio() {
    const sel = document.getElementById('tServicio');
    if (!sel.value) return;
    const opt = sel.options[sel.selectedIndex];
    serviciosSeleccionados.push({
        servicio_id:     parseInt(sel.value),
        servicio_nombre: opt.dataset.nombre || opt.text.split(' (')[0],
        duracion_min:    parseInt(opt.dataset.dur) || 30,
        precio:          parseFloat(opt.dataset.precio) || 0
    });
    sel.value = '';
    renderServiciosSeleccionados();
}

function quitarServicio(idx) {
    serviciosSeleccionados.splice(idx, 1);
    renderServiciosSeleccionados();
}

function renderServiciosSeleccionados() {
    const lista  = document.getElementById('serviciosLista');
    const totBox = document.getElementById('totalesServicios');
    if (!serviciosSeleccionados.length) {
        lista.innerHTML = '<p style="font-size:12px;color:#9ca3af;margin:0;">Sin servicios seleccionados</p>';
        totBox.style.display = 'none';
        return;
    }
    lista.innerHTML = serviciosSeleccionados.map((s, i) => `
        <div class="servicio-tag">
            <div class="servicio-tag-info">
                <div class="servicio-tag-nombre">${esc(s.servicio_nombre)}</div>
                <div class="servicio-tag-meta">${s.duracion_min} min · $${Number(s.precio).toLocaleString('es-AR')}</div>
            </div>
            <button class="servicio-tag-del" onclick="quitarServicio(${i})" title="Quitar"><i class="fas fa-times"></i></button>
        </div>`).join('');
    const totalDur    = serviciosSeleccionados.reduce((s,x) => s + x.duracion_min, 0);
    const totalPrecio = serviciosSeleccionados.reduce((s,x) => s + parseFloat(x.precio), 0);
    document.getElementById('totDuracion').textContent = totalDur + ' min';
    document.getElementById('totPrecio').textContent   = '$' + totalPrecio.toLocaleString('es-AR');
    totBox.style.display = 'flex';
}

async function cargarDia() {
    const r = await fetch(`${API_T}?fecha=${fechaActual}`, { credentials: 'include' });
    const d = await r.json();
    if (!d.success) return;

    const { turnos, stats, fecha } = d.data;

    // Actualizar label
    const dt = new Date(fecha + 'T00:00:00');
    const dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const esHoy = fecha === hoy();
    document.getElementById('fechaLabel').textContent =
        `${dias[dt.getDay()]}, ${dt.getDate()} de ${meses[dt.getMonth()]}${esHoy ? ' · Hoy' : ''}`;
    document.getElementById('pickerFecha').value = fecha;

    // Stats
    document.getElementById('stTotal').textContent      = stats.total || 0;
    document.getElementById('stEnCurso').textContent    = stats.en_curso || 0;
    document.getElementById('stCompletados').textContent = stats.completados || 0;
    document.getElementById('stFacturado').textContent  = '$' + Number(stats.facturado||0).toLocaleString('es-AR');

    // Renderizar
    renderAgenda(turnos);
}

function renderAgenda(turnos) {
    const cont = document.getElementById('agendaList');
    if (!turnos.length) {
        cont.innerHTML = `<div class="empty-day"><i class="fas fa-calendar-xmark"></i><p>Sin turnos para este día</p></div>`;
        return;
    }
    let html = '';
    let lastHour = '';
    const COLORES = {
        pendiente:'#94a3b8', confirmado:'#3b82f6', en_curso:'#f59e0b',
        completado:'#16a34a', cancelado:'#ef4444', no_show:'#9ca3af'
    };
    const LABELS = {
        pendiente:'Pendiente', confirmado:'Confirmado', en_curso:'En curso',
        completado:'Completado', cancelado:'Cancelado', no_show:'No show'
    };
    turnos.forEach(t => {
        const hora = (t.hora_inicio||'').substring(0,5);
        const h    = hora.substring(0,2);
        if (h !== lastHour) {
            html += `<div class="hora-sep"><span class="hora-sep-time">${h}:00</span><div class="hora-sep-line"></div></div>`;
            lastHour = h;
        }
        const color = COLORES[t.estado] || '#94a3b8';
        const servNombre = t.servicio_nombre || '—';
        html += `<div class="turno-card" onclick="verDetalle(${t.id})">
            <div class="turno-accent" style="background:${color};"></div>
            <div class="turno-body">
                <div class="turno-time">
                    ${hora}<br>${(t.hora_fin||'').substring(0,5)}
                    <div class="duracion">${t.duracion_min}min</div>
                </div>
                <div class="turno-divider"></div>
                <div class="turno-info">
                    <div class="turno-cliente">${esc(t.cliente_nombre)}</div>
                    <div class="turno-servicio"><i class="fas fa-scissors" style="font-size:10px;margin-right:4px;"></i>${esc(servNombre)}</div>
                    ${t.cliente_telefono ? `<div class="turno-tel"><i class="fas fa-phone" style="font-size:10px;margin-right:4px;"></i>${esc(t.cliente_telefono)}</div>` : ''}
                </div>
                <div>
                    <div class="turno-precio">$${Number(t.precio||0).toLocaleString('es-AR')}<span class="precio-label">precio</span></div>
                    <div style="margin-top:6px;"><span class="badge-turno st-${t.estado}">${LABELS[t.estado]||t.estado}</span></div>
                </div>
                <div class="turno-actions" onclick="event.stopPropagation()">
                    <button class="t-btn" title="Editar" onclick="editarTurno(${t.id})"><i class="fas fa-pencil"></i></button>
                    <button class="t-btn" title="Cancelar" onclick="cancelarTurno(${t.id})" style="color:#ef4444;border-color:rgba(239,68,68,.3);"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>`;
    });
    cont.innerHTML = html;
}

function cambiarDia(delta) {
    const d = new Date(fechaActual + 'T00:00:00');
    d.setDate(d.getDate() + delta);
    fechaActual = d.toISOString().split('T')[0];
    cargarDia();
}
function irHoy()     { fechaActual = hoy(); cargarDia(); }
function setFecha(f) { if (f) { fechaActual = f; cargarDia(); } }

function abrirNuevo() {
    document.getElementById('tId').value         = '';
    document.getElementById('tClienteId').value  = '';
    document.getElementById('tCliente').value    = '';
    document.getElementById('tTelefono').value   = '';
    document.getElementById('tServicio').value   = '';
    document.getElementById('tFecha').value      = fechaActual;
    document.getElementById('tHoraInicio').value = '09:00';
    document.getElementById('tEstado').value     = 'pendiente';
    document.getElementById('tNotas').value      = '';
    serviciosSeleccionados = [];
    renderServiciosSeleccionados();
    ocultarBadgeCliente();
    document.getElementById('modalTituloTurno').innerHTML = '<i class="fas fa-calendar-plus" style="color:#8b5cf6;margin-right:8px;"></i> Nuevo Turno';
    document.getElementById('modalTurno').classList.add('open');
    setTimeout(() => document.getElementById('tCliente').focus(), 100);
}

async function editarTurno(id) {
    const r = await fetch(`${API_T}?id=${id}`, { credentials: 'include' });
    const d = await r.json();
    if (!d.success) return;
    const t = d.data;
    document.getElementById('tId').value         = t.id;
    document.getElementById('tClienteId').value  = t.cliente_id || '';
    document.getElementById('tCliente').value    = t.cliente_nombre || '';
    document.getElementById('tTelefono').value   = t.cliente_telefono || '';
    if (t.cliente_id) mostrarBadgeCliente(t.cliente_nombre, t.cliente_id);
    else ocultarBadgeCliente();
    document.getElementById('tServicio').value   = '';
    document.getElementById('tFecha').value      = t.fecha;
    document.getElementById('tHoraInicio').value = (t.hora_inicio||'').substring(0,5);
    document.getElementById('tEstado').value     = t.estado;
    document.getElementById('tNotas').value      = t.notas || '';

    // Cargar servicios: usar turno_servicios si existen, sino construir desde el campo simple
    if (t.servicios && t.servicios.length) {
        serviciosSeleccionados = t.servicios.map(s => ({
            servicio_id:     s.servicio_id || null,
            servicio_nombre: s.servicio_nombre,
            duracion_min:    parseInt(s.duracion_min) || 30,
            precio:          parseFloat(s.precio) || 0
        }));
    } else if (t.servicio_nombre) {
        serviciosSeleccionados = [{
            servicio_id:     t.servicio_id || null,
            servicio_nombre: t.servicio_nombre,
            duracion_min:    parseInt(t.duracion_min) || 30,
            precio:          parseFloat(t.precio) || 0
        }];
    } else {
        serviciosSeleccionados = [];
    }
    renderServiciosSeleccionados();
    document.getElementById('modalTituloTurno').innerHTML = '<i class="fas fa-pencil" style="color:#8b5cf6;margin-right:8px;"></i> Editar Turno';
    document.getElementById('modalTurno').classList.add('open');
}

async function guardarTurno() {
    const id      = parseInt(document.getElementById('tId').value) || 0;
    const cliente = document.getElementById('tCliente').value.trim();
    const fecha   = document.getElementById('tFecha').value;
    const inicio  = document.getElementById('tHoraInicio').value;
    if (!cliente) { alert('Ingresá el nombre del cliente'); return; }
    if (!fecha)   { alert('Elegí una fecha'); return; }
    if (!inicio)  { alert('Elegí la hora de inicio'); return; }

    const body = {
        id:               id || undefined,
        cliente_id:       parseInt(document.getElementById('tClienteId').value) || null,
        cliente_nombre:   cliente,
        cliente_telefono: document.getElementById('tTelefono').value,
        fecha,
        hora_inicio:      inicio,
        estado:           document.getElementById('tEstado').value,
        notas:            document.getElementById('tNotas').value,
        servicios:        serviciosSeleccionados,
    };

    const r = await fetch(API_T, {
        method: id ? 'PUT' : 'POST',
        headers: {'Content-Type':'application/json'},
        credentials: 'include',
        body: JSON.stringify(body)
    });
    const d = await r.json();
    if (d.success) { cerrarModal('modalTurno'); cargarDia(); }
    else alert(d.message || 'Error al guardar');
}

async function verDetalle(id) {
    const r = await fetch(`${API_T}?id=${id}`, { credentials: 'include' });
    const d = await r.json();
    if (!d.success) return;
    const t = d.data;
    const LABELS = { pendiente:'Pendiente',confirmado:'Confirmado',en_curso:'En curso',completado:'Completado',cancelado:'Cancelado',no_show:'No show' };
    document.getElementById('detNombre').textContent = t.cliente_nombre;

    // Mostrar servicios si hay múltiples, o el servicio simple
    let serviciosHtml = '';
    if (t.servicios && t.servicios.length > 1) {
        serviciosHtml = `
        <div class="detalle-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
            <span class="detalle-label">Servicios</span>
            <div class="servicios-detalle">
                ${t.servicios.map(s => `<div class="serv-det-item"><span>${esc(s.servicio_nombre)}</span><span style="color:#8b5cf6;">${s.duracion_min}min · $${Number(s.precio).toLocaleString('es-AR')}</span></div>`).join('')}
            </div>
        </div>`;
    } else {
        serviciosHtml = `<div class="detalle-row"><span class="detalle-label">Servicio</span><span class="detalle-val">${esc(t.servicio_nombre||'—')}</span></div>`;
    }

    document.getElementById('detalleBody').innerHTML = `
        ${serviciosHtml}
        <div class="detalle-row"><span class="detalle-label">Fecha</span><span class="detalle-val">${fmtFecha(t.fecha)}</span></div>
        <div class="detalle-row"><span class="detalle-label">Horario</span><span class="detalle-val">${(t.hora_inicio||'').substring(0,5)} – ${(t.hora_fin||'').substring(0,5)} (${t.duracion_min}min)</span></div>
        <div class="detalle-row"><span class="detalle-label">Teléfono</span><span class="detalle-val">${esc(t.cliente_telefono||'—')}</span></div>
        <div class="detalle-row"><span class="detalle-label">Total</span><span class="detalle-val" style="color:#8b5cf6;font-size:18px;">$${Number(t.precio||0).toLocaleString('es-AR')}</span></div>
        ${t.notas ? `<div class="detalle-row"><span class="detalle-label">Notas</span><span class="detalle-val">${esc(t.notas)}</span></div>` : ''}
        <div style="margin-top:12px;">
            <div class="detalle-label" style="margin-bottom:8px;">CAMBIAR ESTADO</div>
            <div class="estado-btns">
                ${['pendiente','confirmado','en_curso','completado','cancelado','no_show'].map(e =>
                    `<button class="estado-btn ${t.estado===e?'active':''}" onclick="cambiarEstado(${t.id},'${e}')">${LABELS[e]}</button>`
                ).join('')}
            </div>
        </div>
    `;
    document.getElementById('detallePies').innerHTML = `
        <button class="btn-primary" onclick="cerrarModal('modalDetalle');editarTurno(${t.id})">
            <i class="fas fa-pencil"></i> Editar
        </button>
        <button class="btn-cancel" onclick="cerrarModal('modalDetalle')">Cerrar</button>
    `;
    document.getElementById('modalDetalle').classList.add('open');
}

async function cambiarEstado(id, estado) {
    if (estado === 'completado') {
        // Pedir método de pago antes de marcar completado
        document.getElementById('cobroTurnoId').value = id;
        document.getElementById('cobroMetodo').value  = 'efectivo';
        document.getElementById('modalCobro').classList.add('open');
        return;
    }
    const r = await fetch(API_T, {
        method:'PUT', headers:{'Content-Type':'application/json'}, credentials:'include',
        body: JSON.stringify({ id, estado })
    });
    const d = await r.json();
    if (d.success) { cerrarModal('modalDetalle'); cargarDia(); }
}

async function confirmarCobro() {
    const id     = document.getElementById('cobroTurnoId').value;
    const metodo = document.getElementById('cobroMetodo').value;
    const r = await fetch(API_T, {
        method:'PUT', headers:{'Content-Type':'application/json'}, credentials:'include',
        body: JSON.stringify({ id: parseInt(id), estado: 'completado', metodo_pago: metodo })
    });
    const d = await r.json();
    if (d.success) {
        cerrarModal('modalCobro');
        cerrarModal('modalDetalle');
        cargarDia();
    }
}

async function cancelarTurno(id) {
    if (!confirm('¿Cancelar este turno?')) return;
    await fetch(`${API_T}?id=${id}`, { method:'DELETE', credentials:'include' });
    cargarDia();
}

function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }
function hoy() { return new Date().toISOString().split('T')[0]; }
function fmtFecha(f) { if (!f) return '—'; const p = f.split(' ')[0].split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ── Autocomplete Cliente ──────────────────────────────────────────────────────
const API_C = '../../api/peluqueria/clientes.php';
let acTimer = null;

function mostrarBadgeCliente(nombre, id) {
    const badge = document.getElementById('clienteSelBadge');
    badge.style.display = 'block';
    badge.innerHTML = `<span class="cliente-badge"><i class="fas fa-user-check"></i> ${esc(nombre)} <button onclick="desvincularCliente()" title="Quitar">×</button></span>`;
}
function ocultarBadgeCliente() {
    document.getElementById('clienteSelBadge').style.display = 'none';
    document.getElementById('clienteSelBadge').innerHTML = '';
}
function desvincularCliente() {
    document.getElementById('tClienteId').value = '';
    ocultarBadgeCliente();
}
function cerrarDropdown() {
    document.getElementById('acDropdown').classList.remove('show');
    document.getElementById('acDropdown').innerHTML = '';
}
function seleccionarCliente(c) {
    document.getElementById('tClienteId').value  = c.id;
    document.getElementById('tCliente').value    = c.nombre;
    document.getElementById('tTelefono').value   = c.telefono || '';
    mostrarBadgeCliente(c.nombre, c.id);
    cerrarDropdown();
}

async function buscarClientesAC(q) {
    if (!q || q.length < 2) { cerrarDropdown(); return; }
    try {
        const r = await fetch(`${API_C}?search=${encodeURIComponent(q)}`, { credentials: 'include' });
        const d = await r.json();
        const drop = document.getElementById('acDropdown');
        const lista = d.data || [];
        if (!lista.length) { cerrarDropdown(); return; }
        drop.innerHTML = lista.slice(0, 8).map(c => `
            <div class="ac-item" onclick='seleccionarCliente(${JSON.stringify({id:c.id,nombre:c.nombre,telefono:c.telefono||''})})'>
                <div class="ac-item-nombre">${esc(c.nombre)}</div>
                ${c.telefono ? `<div class="ac-item-tel"><i class="fas fa-phone" style="font-size:10px;margin-right:3px;"></i>${esc(c.telefono)}</div>` : ''}
            </div>`).join('');
        drop.classList.add('show');
    } catch(e) { cerrarDropdown(); }
}

document.getElementById('tCliente').addEventListener('input', function() {
    document.getElementById('tClienteId').value = '';
    ocultarBadgeCliente();
    clearTimeout(acTimer);
    acTimer = setTimeout(() => buscarClientesAC(this.value.trim()), 250);
});
document.getElementById('tCliente').addEventListener('blur', function() {
    setTimeout(cerrarDropdown, 200);
});

document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

document.getElementById('pickerFecha').value = fechaActual;
cargarServicios();
cargarDia();
</script>

<!-- Modal cobro rápido -->
<div class="modal-overlay" id="modalCobro">
    <div class="modal" style="max-width:360px;">
        <div class="modal-header">
            <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fas fa-check-circle" style="color:#22c55e;margin-right:8px;"></i>Completar turno</h3>
            <button class="modal-close" onclick="cerrarModal('modalCobro')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:20px 24px;">
            <input type="hidden" id="cobroTurnoId">
            <div class="form-group">
                <label class="form-label">Método de pago</label>
                <select class="form-control" id="cobroMetodo">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="transferencia">📱 Transferencia</option>
                    <option value="tarjeta">💳 Tarjeta de crédito</option>
                    <option value="debito">💳 Débito</option>
                </select>
            </div>
        </div>
        <div class="modal-footer" style="padding:14px 24px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border-color,#e5e7eb);">
            <button class="btn btn-secondary" onclick="cerrarModal('modalCobro')">Cancelar</button>
            <button class="btn btn-primary" style="background:#22c55e;border-color:#22c55e;" onclick="confirmarCobro()">
                <i class="fas fa-check"></i> Confirmar cobro
            </button>
        </div>
    </div>
</div>
</body>
</html>
