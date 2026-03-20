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
    <title>Reservas - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .badge-estado { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-confirmada { background:rgba(72,187,120,.15);color:#22c55e; }
        .badge-pendiente  { background:rgba(246,173,85,.15);color:#f59e0b; }
        .badge-cancelada  { background:rgba(160,174,192,.15);color:#94a3b8; }
        .table-wrap { overflow-x:auto; }
        table { width:100%;border-collapse:collapse;font-size:14px; }
        th { text-align:left;padding:11px 16px;background:var(--background);color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border);white-space:nowrap;font-size:12px;text-transform:uppercase;letter-spacing:.5px; }
        td { padding:13px 16px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }
        .action-btns { display:flex;gap:6px; }
        .btn-table { width:34px;height:34px;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:var(--transition); }
        .btn-check { background:rgba(72,187,120,.1);color:#22c55e; }
        .btn-check:hover { background:#22c55e;color:#fff; }
        .btn-cancel{ background:rgba(245,101,101,.1);color:#ef4444; }
        .btn-cancel:hover { background:#ef4444;color:#fff; }
        .btn-edit  { background:rgba(66,153,225,.1);color:#4299e1; }
        .btn-edit:hover  { background:#4299e1;color:#fff; }
        .filter-tabs { display:flex;gap:6px;flex-wrap:wrap; }
        .filter-tab { padding:7px 14px;border-radius:20px;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:13px;font-family:inherit;transition:var(--transition); }
        .filter-tab.active,.filter-tab:hover { background:var(--primary);color:#fff;border-color:var(--primary); }
        .view-tabs { display:flex;gap:6px; }
        .view-tab { padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:13px;font-family:inherit;transition:var(--transition); }
        .view-tab.active,.view-tab:hover { background:var(--primary);color:#fff;border-color:var(--primary); }
        .calendar-grid { display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px; }
        .calendar-weekday { text-align:center;font-size:12px;color:var(--text-secondary);padding:6px 0;font-weight:600;text-transform:uppercase; }
        .calendar-day { border:1px solid var(--border);background:var(--surface);border-radius:10px;min-height:72px;padding:8px;cursor:pointer;display:flex;flex-direction:column;justify-content:space-between;transition:var(--transition); }
        .calendar-day:hover { border-color:var(--primary); }
        .calendar-day.empty { visibility:hidden; pointer-events:none; }
        .calendar-day.today { box-shadow:inset 0 0 0 1px var(--primary); }
        .calendar-day.selected { background:rgba(15,209,134,.1); border-color:var(--primary); }
        .calendar-day-num { font-weight:700;color:var(--text-primary);font-size:14px; }
        .calendar-day-count { font-size:11px;color:var(--primary);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;display:block; }
        .calendar-detail { margin-top:16px;border-top:1px solid var(--border);padding-top:14px; }
        .slots-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:8px; }
        .slots-section-title { margin:0 0 8px 0;font-size:13px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.4px; }
        .slots-section { margin-bottom:14px; }
        .weekly-wrap { overflow-x:auto; overflow-y:visible; border-top:1px solid var(--border); }
        .weekly-table { width:100%; min-width:980px; border-collapse:collapse; }
        .weekly-table th, .weekly-table td { border-right:1px solid var(--border); border-bottom:1px solid var(--border); padding:10px 8px; vertical-align:middle; }
        .weekly-table th:last-child, .weekly-table td:last-child { border-right:none; }
        .weekly-table th { background:var(--surface); }
        .weekly-hour-col {
            width:74px;
            min-width:74px;
            max-width:74px;
            text-align:center;
            font-weight:700;
            color:var(--text-secondary);
            background:var(--surface);
            position:sticky;
            left:0;
            z-index:4;
            box-shadow: 1px 0 0 var(--border);
        }
        .weekly-table thead .weekly-hour-col { z-index:5; }
        .weekly-day-head { text-align:center; min-width:120px; }
        .weekly-day-head.today {
            background:linear-gradient(180deg, rgba(15,209,134,.2) 0%, rgba(15,209,134,.08) 100%);
            box-shadow:inset 0 -3px 0 var(--primary), inset 0 0 0 1px rgba(15,209,134,.35);
        }
        .weekly-day-head.today .weekly-day-name { color:#5af2cf; }
        .weekly-day-head.today .weekly-day-date { color:#9fead8; }
        .today-pill {
            display:inline-flex;
            align-items:center;
            margin-left:6px;
            padding:2px 7px;
            border-radius:999px;
            font-size:10px;
            font-weight:800;
            letter-spacing:.3px;
            background:rgba(15,209,134,.22);
            color:#7cf0d5;
            border:1px solid rgba(15,209,134,.45);
            box-shadow:0 0 10px rgba(15,209,134,.25);
            text-transform:uppercase;
        }
        .weekly-day-name { font-size:13px; font-weight:800; text-transform:uppercase; color:var(--text-primary); }
        .weekly-day-date { margin-top:3px; font-size:12px; color:var(--text-secondary); }
        .weekly-cell { text-align:center; }
        .weekly-cell.today-col { background:rgba(15,209,134,.12); box-shadow:inset 1px 0 0 rgba(15,209,134,.28), inset -1px 0 0 rgba(15,209,134,.28); }
        .weekly-cell.disponible { color:#3ee8cf; font-weight:700; }
        .weekly-cell.disponible.clickable { cursor:pointer; }
        .weekly-cell.disponible.clickable:hover { background:rgba(15,209,134,.18); color:#7cf0d5; }
        .weekly-cell.ocupado { background:rgba(126,34,206,.12); color:#fb7185; font-weight:700; }
        .weekly-cell small { display:block; margin-top:4px; font-size:11px; color:var(--text-secondary); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .weekly-range { min-width:170px; text-align:center; font-weight:700; color:var(--text-primary); }
        .slot-item { border:1px solid var(--border);background:var(--surface);border-radius:10px;padding:8px 10px;font-size:12px;display:flex;justify-content:space-between;align-items:center;gap:8px; }
        .slot-item.clickable { cursor:pointer; }
        .slot-item.disponible.clickable:hover { background:rgba(15,209,134,.14); }
        .slot-item.ocupado { border-color:rgba(245,158,11,.35); }
        .slot-item.disponible { border-color:rgba(34,197,94,.25); }
        .slot-badge { font-size:11px;border-radius:20px;padding:2px 8px;font-weight:600; }
        .slot-badge.ocupado { background:rgba(245,158,11,.15);color:#f59e0b; }
        .slot-badge.disponible { background:rgba(34,197,94,.15);color:#22c55e; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface);border-radius:16px;width:100%;max-width:540px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);border:1px solid var(--border); }
        .modal-header { padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
        .modal-header h3 { margin:0;font-size:17px;color:var(--text-primary); }
        .modal-close { background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:18px;padding:4px; }
        .modal-close:hover { color:var(--error); }
        .modal-body { padding:24px; }
        .modal-footer { padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end; }
        .form-2col { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
        .form-2col .full { grid-column:1/-1; }
        .monto-calc { background:var(--background);border:1px solid var(--border);border-radius:10px;padding:12px 16px;margin-top:10px;display:flex;justify-content:space-between;align-items:center;font-size:14px; }
        .monto-calc .val { font-size:18px;font-weight:700;color:var(--primary); }
        .empty-state { text-align:center;padding:60px 20px;color:var(--text-secondary); }
        .empty-state i { font-size:44px;margin-bottom:14px;display:block;opacity:.4; }
        .nav-fecha { display:flex;align-items:center;gap:10px; }
        .nav-btn { width:36px;height:36px;border:1px solid var(--border);background:var(--surface);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);font-size:14px; }
        .nav-btn:hover { background:var(--primary);color:#fff;border-color:var(--primary); }
        .toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);color:var(--text-primary);border-radius:12px;padding:14px 20px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:12px;max-width:320px;border:1px solid var(--border);border-left:4px solid var(--primary); }
        .toast.show { display:flex; }
        .toast.error { border-left-color:var(--error); }
        body.dark-mode th { background:rgba(255,255,255,.03); }
        body.dark-mode tr:hover td { background:rgba(255,255,255,.03); }
        body.dark-mode .filter-tab { border-color:rgba(255,255,255,.1); }
        body.dark-mode .monto-calc { background:rgba(255,255,255,.04); }
        body.dark-mode .weekly-table th,
        body.dark-mode .weekly-hour-col { background:var(--surface) !important; }
        @media(max-width:600px) {
            .form-2col { grid-template-columns:1fr; }
            .form-2col .full { grid-column:1; }
            .calendar-day { min-height:54px; padding:6px; }
            .calendar-day-num { font-size:13px; }
            .calendar-day-count { font-size:9px; line-height:1.1; }
            .view-tabs { width:100%; }
            .view-tab { flex:1; }
            .weekly-table { min-width:860px; }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <div class="page-header" style="background:var(--surface);padding:22px 24px;border-radius:14px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,.07);border:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:48px;height:48px;background:rgba(22,163,74,.12);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#16a34a;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">Reservas</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Calendario de turnos y alquileres</p>
                    </div>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <a href="canchas.php" class="btn btn-secondary"><i class="fas fa-futbol"></i> Canchas</a>
                    <button class="btn btn-primary" onclick="abrirModalNuevo()"><i class="fas fa-plus"></i> Nueva Reserva</button>
                </div>
            </div>

            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px;margin-bottom:24px;">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-info"><p class="stat-label">Reservas hoy</p><h3 class="stat-value" id="statHoy">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info"><p class="stat-label">Confirmadas</p><h3 class="stat-value" id="statConf">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                    <div class="stat-info"><p class="stat-label">Pendientes</p><h3 class="stat-value" id="statPend">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info"><p class="stat-label">Ingresos hoy</p><h3 class="stat-value" id="statIngresos" style="font-size:20px;">—</h3></div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div class="nav-fecha" id="tablaControls">
                        <button class="nav-btn" onclick="cambiarFecha(-1)" title="Día anterior"><i class="fas fa-chevron-left"></i></button>
                        <input type="date" class="form-control" id="filtroFecha" style="margin:0;min-width:150px;" onchange="cargarReservas()">
                        <button class="nav-btn" onclick="cambiarFecha(1)" title="Día siguiente"><i class="fas fa-chevron-right"></i></button>
                        <button class="nav-btn" onclick="irHoy()" title="Hoy"><i class="fas fa-calendar-day"></i></button>
                    </div>
                    <div class="nav-fecha" id="calendarioControls" style="display:none;">
                        <button class="nav-btn" onclick="cambiarMes(-1)" title="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                        <div id="mesLabel" style="min-width:170px;text-align:center;font-weight:700;color:var(--text-primary);"></div>
                        <button class="nav-btn" onclick="cambiarMes(1)" title="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
                        <button class="nav-btn" onclick="irMesActual()" title="Mes actual"><i class="fas fa-calendar"></i></button>
                    </div>
                    <div class="nav-fecha" id="semanalControls" style="display:none;">
                        <button class="nav-btn" onclick="cambiarSemana(-1)" title="Semana anterior"><i class="fas fa-chevron-left"></i></button>
                        <div id="semanaLabel" class="weekly-range"></div>
                        <button class="nav-btn" onclick="cambiarSemana(1)" title="Semana siguiente"><i class="fas fa-chevron-right"></i></button>
                        <button class="nav-btn" onclick="irSemanaActual()" title="Semana actual"><i class="fas fa-calendar-week"></i></button>
                    </div>
                    <div class="filter-tabs" id="filtrosTabla">
                        <button class="filter-tab active" onclick="setFiltro('',this)">Todas</button>
                        <button class="filter-tab" onclick="setFiltro('confirmada',this)">Confirmadas</button>
                        <button class="filter-tab" onclick="setFiltro('pendiente',this)">Pendientes</button>
                        <button class="filter-tab" onclick="setFiltro('cancelada',this)">Canceladas</button>
                    </div>
                    <div class="view-tabs">
                        <button class="view-tab active" id="btnVistaTabla" onclick="setVista('tabla')"><i class="fas fa-table"></i> Tabla</button>
                        <button class="view-tab" id="btnVistaCalendario" onclick="setVista('calendario')"><i class="fas fa-calendar-alt"></i> Calendario</button>
                        <button class="view-tab" id="btnVistaSemanal" onclick="setVista('semanal')"><i class="fas fa-calendar-week"></i> Semanal</button>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <div id="loadingState" style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    <div id="tablaView">
                        <div class="table-wrap">
                            <table id="reservasTable" style="display:none;">
                                <thead><tr>
                                    <th>Cancha</th><th>Horario</th><th>Cliente</th>
                                    <th>Monto</th><th>Estado</th><th>Acciones</th>
                                </tr></thead>
                                <tbody id="reservasTbody"></tbody>
                            </table>
                        </div>
                        <div id="emptyState" class="empty-state" style="display:none;">
                            <i class="fas fa-calendar-xmark"></i><p>Sin reservas para esta fecha</p>
                        </div>
                    </div>
                    <div id="calendarioView" style="display:none;padding:16px;">
                        <div class="calendar-grid" id="calendarGrid"></div>
                        <div class="calendar-detail" id="calendarDetail" style="display:none;">
                            <h4 id="calendarDetailTitle" style="margin:0 0 10px 0;color:var(--text-primary);font-size:15px;"></h4>
                            <div class="slots-grid" id="calendarSlots"></div>
                        </div>
                    </div>
                    <div id="semanalView" style="display:none;">
                        <div class="weekly-wrap">
                            <table class="weekly-table" id="weeklyTable"></table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Reserva -->
<div class="modal-overlay" id="modalReserva">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nueva Reserva</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="reservaId">
            <input type="hidden" id="clienteId">
            <div class="form-2col">
                <div class="form-group full">
                    <label class="form-label">Cancha *</label>
                    <select class="form-control" id="fCancha" onchange="calcularMonto()"></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha *</label>
                    <input type="date" class="form-control" id="fFecha">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-control" id="fEstado">
                        <option value="confirmada">Confirmada</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora inicio *</label>
                    <input type="time" class="form-control" id="fHoraInicio" onchange="calcularMonto()">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora fin *</label>
                    <input type="time" class="form-control" id="fHoraFin" onchange="calcularMonto()">
                </div>
                <div class="form-group full">
                    <label class="form-label">Cliente</label>
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <button type="button" class="btn btn-sm" id="btnClienteSeleccionar" style="flex:1;background:var(--primary);color:white;border:none;cursor:pointer;border-radius:8px;" onclick="cambiarModoCliente('seleccionar')">
                            <i class="fas fa-list"></i> Seleccionar
                        </button>
                        <button type="button" class="btn btn-sm" id="btnClienteManual" style="flex:1;background:var(--surface);color:var(--text-primary);border:1px solid var(--border);cursor:pointer;border-radius:8px;" onclick="cambiarModoCliente('manual')">
                            <i class="fas fa-pen"></i> Ingresar
                        </button>
                    </div>
                    <select class="form-control" id="fClienteSelect" style="display:none;" onchange="seleccionarCliente()">
                        <option value="">Buscar cliente...</option>
                    </select>
                    <div id="clienteManualFields" style="display:none;">
                        <input type="text" class="form-control" id="fCliente" placeholder="Nombre del cliente" style="margin-bottom:8px;">
                        <input type="text" class="form-control" id="fTelefono" placeholder="Ej: 11-1234-5678">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Monto</label>
                    <input type="number" class="form-control" id="fMonto" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Método de pago</label>
                    <select class="form-control" id="fMetodo">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Notas</label>
                    <textarea class="form-control" id="fNotas" rows="2" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="monto-calc" id="montoCalc" style="display:none;">
                <span><i class="fas fa-calculator" style="color:var(--primary);margin-right:6px;"></i> Monto estimado</span>
                <span class="val" id="montoCalcVal">$0</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardar()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Horarios del día -->
<div class="modal-overlay" id="modalHorariosDia">
    <div class="modal" style="max-width:760px;">
        <div class="modal-header">
            <h3 id="modalHorariosTitle">Horarios del día</h3>
            <button class="modal-close" onclick="cerrarModalHorarios()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="slots-grid" id="modalHorariosSlots"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalHorarios()">Cerrar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-check-circle" style="color:var(--primary);"></i><span id="toastMsg"></span></div>

<script>
let reservas = [], canchas = [], filtroEstado = '';
let vistaActual = 'tabla';
let reservasMes = [];
let mesActual = new Date();
let fechaCalendarioSeleccionada = '';
let semanaInicio = null;

async function init() {
    await cargarCanchas();
    const params = new URLSearchParams(window.location.search);
    const canchaId = params.get('cancha_id');
    if (canchaId) document.getElementById('fCancha').value = canchaId;
    mesActual = new Date();
    semanaInicio = getInicioSemana(new Date());
    irHoy();
}

async function cargarCanchas() {
    try {
        const r = await fetch('../../api/canchas/canchas.php', {credentials: 'include'});
        const d = await r.json();
        if (d.success) {
            canchas = d.data.filter(c => c.activo == 1);
            const opts = '<option value="">Seleccionar cancha...</option>' +
                canchas.map(c => `<option value="${c.id}" data-precio="${c.precio_hora}">${c.nombre}${c.deporte?' — '+c.deporte:''}</option>`).join('');
            document.getElementById('fCancha').innerHTML = opts;
        }
    } catch(e) {}
}

function actualizarStats(st) {
    document.getElementById('statHoy').textContent      = st.total || 0;
    document.getElementById('statConf').textContent     = st.confirmadas || 0;
    document.getElementById('statPend').textContent     = st.pendientes || 0;
    document.getElementById('statIngresos').textContent = st.ingresos
        ? '$' + parseFloat(st.ingresos).toLocaleString('es-AR',{minimumFractionDigits:0})
        : '$0';
}

async function cargarReservas() {
    const fecha = document.getElementById('filtroFecha').value;
    document.getElementById('loadingState').style.display = 'block';
    if (vistaActual === 'tabla') {
        document.getElementById('reservasTable').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
    }
    try {
        const r = await fetch(`../../api/canchas/reservas.php?fecha=${fecha}`, {credentials: 'include'});
        const d = await r.json();
        document.getElementById('loadingState').style.display = 'none';
        if (d.success) {
            reservas = d.data.reservas || [];
            actualizarStats(d.data.stats || {});
            if (vistaActual === 'tabla') {
                renderTabla();
            } else {
                renderDetalleCalendario(fecha, reservas);
            }
        }
    } catch(e) {
        document.getElementById('loadingState').style.display = 'none';
        if (vistaActual === 'tabla') {
            document.getElementById('emptyState').style.display = 'block';
        }
    }
}

function renderTabla() {
    const lista = filtroEstado ? reservas.filter(r => r.estado === filtroEstado) : reservas;
    if (!lista.length) {
        document.getElementById('emptyState').style.display = 'block';
        return;
    }
    document.getElementById('reservasTable').style.display = 'table';
    const badges = {
        confirmada:`<span class="badge-estado badge-confirmada">● Confirmada</span>`,
        pendiente: `<span class="badge-estado badge-pendiente">● Pendiente</span>`,
        cancelada: `<span class="badge-estado badge-cancelada">● Cancelada</span>`,
    };
    document.getElementById('reservasTbody').innerHTML = lista.map(r => `<tr>
        <td>
            <div style="font-weight:600;">${r.cancha_nombre}</div>
            <div style="font-size:12px;color:var(--text-secondary);">${r.deporte||''}</div>
        </td>
        <td>
            <span style="font-weight:600;">${r.hora_inicio ? r.hora_inicio.substring(0,5) : '—'}</span>
            <span style="color:var(--text-secondary);"> → </span>
            <span style="font-weight:600;">${r.hora_fin ? r.hora_fin.substring(0,5) : '—'}</span>
        </td>
        <td>
            <div style="font-weight:600;">${r.cliente_nombre||'—'}</div>
            <div style="font-size:12px;color:var(--text-secondary);">${r.cliente_telefono||''}</div>
        </td>
        <td>
            ${r.monto > 0
                ? `<span style="font-weight:700;color:var(--primary);">$${parseFloat(r.monto).toLocaleString('es-AR',{minimumFractionDigits:0})}</span><div style="font-size:11px;color:var(--text-secondary);">${r.metodo_pago||''}</div>`
                : '—'}
        </td>
        <td>${badges[r.estado]||r.estado}</td>
        <td>
            <div class="action-btns">
                ${r.estado==='pendiente'?`<button class="btn-table btn-check" title="Confirmar" onclick="cambiarEstado(${r.id},'confirmada')"><i class="fas fa-check"></i></button>`:''}
                ${r.estado!=='cancelada'?`<button class="btn-table btn-cancel" title="Cancelar" onclick="cambiarEstado(${r.id},'cancelada')"><i class="fas fa-ban"></i></button>`:''}
                <button class="btn-table btn-edit" title="Editar" onclick="abrirEditar(${r.id})"><i class="fas fa-pen"></i></button>
            </div>
        </td>
    </tr>`).join('');
}

function setFiltro(e, btn) {
    filtroEstado = e;
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderTabla();
    if (!reservas.length || document.getElementById('reservasTable').style.display === 'none') {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('reservasTable').style.display = 'none';
    }
}

function setVista(vista) {
    vistaActual = vista;
    const esTabla = vista === 'tabla';
    const esCalendario = vista === 'calendario';
    const esSemanal = vista === 'semanal';

    document.getElementById('btnVistaTabla').classList.toggle('active', esTabla);
    document.getElementById('btnVistaCalendario').classList.toggle('active', esCalendario);
    document.getElementById('btnVistaSemanal').classList.toggle('active', esSemanal);

    document.getElementById('tablaControls').style.display = esTabla ? 'flex' : 'none';
    document.getElementById('filtrosTabla').style.display = esTabla ? 'flex' : 'none';
    document.getElementById('tablaView').style.display = esTabla ? 'block' : 'none';

    document.getElementById('calendarioControls').style.display = esCalendario ? 'flex' : 'none';
    document.getElementById('calendarioView').style.display = esCalendario ? 'block' : 'none';

    document.getElementById('semanalControls').style.display = esSemanal ? 'flex' : 'none';
    document.getElementById('semanalView').style.display = esSemanal ? 'block' : 'none';

    if (esTabla) {
        document.getElementById('calendarDetail').style.display = 'none';
        cargarReservas();
    } else if (esCalendario) {
        cargarReservasMes();
    } else {
        cargarReservasSemana();
    }
}

function getInicioSemana(baseDate) {
    const d = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate());
    const day = d.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return d;
}

function formatDateISO(dateObj) {
    return `${dateObj.getFullYear()}-${String(dateObj.getMonth()+1).padStart(2,'0')}-${String(dateObj.getDate()).padStart(2,'0')}`;
}

function formatHourRange(hora) {
    const ini = `${String(hora).padStart(2,'0')}:00`;
    const next = hora === 23 ? '00:00' : `${String(hora+1).padStart(2,'0')}:00`;
    return `${ini} - ${next}`;
}

function updateSemanaLabel() {
    const end = new Date(semanaInicio);
    end.setDate(end.getDate() + 6);
    document.getElementById('semanaLabel').textContent = `${semanaInicio.getDate()}/${semanaInicio.getMonth()+1} - ${end.getDate()}/${end.getMonth()+1}`;
}

function cambiarSemana(delta) {
    semanaInicio.setDate(semanaInicio.getDate() + (delta * 7));
    cargarReservasSemana();
}

function irSemanaActual() {
    semanaInicio = getInicioSemana(new Date());
    cargarReservasSemana();
}

async function cargarReservasSemana() {
    document.getElementById('loadingState').style.display = 'block';
    try {
        const r = await fetch('../../api/canchas/reservas.php', {credentials: 'include'});
        const d = await r.json();
        reservasMes = (d.success && d.data && d.data.reservas) ? d.data.reservas : [];
        updateSemanaLabel();
        renderSemanal();
    } catch (e) {
        reservasMes = [];
        updateSemanaLabel();
        renderSemanal();
    } finally {
        document.getElementById('loadingState').style.display = 'none';
    }
}

function renderSemanal() {
    const table = document.getElementById('weeklyTable');
    const dias = [];
    const nombres = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
    const hoy = new Date();
    const hoyISO = formatDateISO(hoy);
    for (let i = 0; i < 7; i++) {
        const d = new Date(semanaInicio);
        d.setDate(d.getDate() + i);
        dias.push(d);
    }

    let html = '<thead><tr><th class="weekly-hour-col">Hora</th>';
    html += dias.map((d, idx) => {
        const esHoy = formatDateISO(d) === hoyISO;
        return `<th class="weekly-day-head ${esHoy ? 'today' : ''}"><div class="weekly-day-name">${nombres[idx]}</div><div class="weekly-day-date">${d.getDate()}/${d.getMonth()+1}${esHoy ? '<span class="today-pill">Hoy</span>' : ''}</div></th>`;
    }).join('');
    html += '</tr></thead><tbody>';

    for (let hora = 8; hora <= 23; hora++) {
        html += `<tr><td class="weekly-hour-col">${hora}</td>`;
        for (let i = 0; i < dias.length; i++) {
            const fechaISO = formatDateISO(dias[i]);
            const esHoyCol = fechaISO === hoyISO;
            const slotStartMin = hora * 60;
            const slotEndMin = (hora === 23 ? 24 : hora + 1) * 60;

            const ocupadas = reservasMes.filter(r => {
                if (r.estado === 'cancelada' || r.fecha !== fechaISO) return false;
                const ini = strToMin((r.hora_inicio || '00:00').substring(0,5));
                let end = strToMin((r.hora_fin || '00:00').substring(0,5));
                if (end === 0 && ini > 0) end = 24 * 60;
                return ini < slotEndMin && end > slotStartMin;
            });

            if (ocupadas.length) {
                const r = ocupadas[0];
                html += `<td class="weekly-cell ocupado ${esHoyCol ? 'today-col' : ''}"><div>OCUPADO</div><small>${r.cliente_nombre || r.cancha_nombre || ''}</small></td>`;
            } else {
                const range = formatHourRange(hora);
                const [horaInicio] = range.split(' - ');
                html += `<td class="weekly-cell disponible clickable ${esHoyCol ? 'today-col' : ''}" onclick="abrirReservaDesdeSlot('${fechaISO}','${horaInicio}')">DISPONIBLE</td>`;
            }
        }
        html += '</tr>';
    }
    html += '</tbody>';
    table.innerHTML = html;
}

async function cargarReservasMes() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('calendarDetail').style.display = 'none';
    try {
        const r = await fetch('../../api/canchas/reservas.php', {credentials: 'include'});
        const d = await r.json();
        reservasMes = (d.success && d.data && d.data.reservas) ? d.data.reservas : [];
        renderCalendarioMes();

        const hoy = new Date().toISOString().split('T')[0];
        const baseFecha = fechaCalendarioSeleccionada || document.getElementById('filtroFecha').value || hoy;
        const baseObj = new Date(baseFecha + 'T00:00:00');
        if (baseObj.getMonth() === mesActual.getMonth() && baseObj.getFullYear() === mesActual.getFullYear()) {
            seleccionarDiaCalendario(baseFecha);
        }
    } catch (e) {
        reservasMes = [];
        renderCalendarioMes();
    } finally {
        document.getElementById('loadingState').style.display = 'none';
    }
}

function renderCalendarioMes() {
    const grid = document.getElementById('calendarGrid');
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const dias = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
    document.getElementById('mesLabel').textContent = `${meses[mesActual.getMonth()]} ${mesActual.getFullYear()}`;

    const primer = new Date(mesActual.getFullYear(), mesActual.getMonth(), 1);
    const ultimo = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 0);
    const offset = (primer.getDay() + 6) % 7;
    const hoy = new Date().toISOString().split('T')[0];

    let html = dias.map(d => `<div class="calendar-weekday">${d}</div>`).join('');
    for (let i = 0; i < offset; i++) html += '<div class="calendar-day empty"></div>';

    for (let dia = 1; dia <= ultimo.getDate(); dia++) {
        const fecha = `${mesActual.getFullYear()}-${String(mesActual.getMonth()+1).padStart(2,'0')}-${String(dia).padStart(2,'0')}`;
        const count = reservasMes.filter(r => r.fecha === fecha && r.estado !== 'cancelada').length;
        const countLabel = count
            ? (window.innerWidth <= 600 ? `${count} res.` : `${count} reserva${count>1?'s':''}`)
            : '';
        const classes = ['calendar-day', fecha === hoy ? 'today' : '', fecha === fechaCalendarioSeleccionada ? 'selected' : ''].join(' ').trim();
        html += `<button type="button" class="${classes}" onclick="seleccionarDiaCalendario('${fecha}')">
            <span class="calendar-day-num">${dia}</span>
            <span class="calendar-day-count">${countLabel}</span>
        </button>`;
    }

    grid.innerHTML = html;
}

function cambiarMes(delta) {
    mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() + delta, 1);
    renderCalendarioMes();
}

function irMesActual() {
    mesActual = new Date();
    renderCalendarioMes();
}

function seleccionarDiaCalendario(fecha) {
    fechaCalendarioSeleccionada = fecha;
    document.getElementById('filtroFecha').value = fecha;
    renderCalendarioMes();
    cargarReservas();
}

function renderDetalleCalendario(fecha, lista) {
    const fechaObj = new Date(fecha + 'T00:00:00');
    const fechaTxt = fechaObj.toLocaleDateString('es-AR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
    document.getElementById('modalHorariosTitle').textContent = `Horarios del ${fechaTxt}`;

    const activos = (lista || []).filter(r => r.estado !== 'cancelada');
    const slots = [];

    for (let hora = 8; hora <= 23; hora++) {
        const inicio = `${String(hora).padStart(2,'0')}:00`;
        const finNum = hora === 23 ? 24 : hora + 1;
        const fin = `${String(finNum % 24).padStart(2,'0')}:00`;

        const slotStartMin = hora * 60;
        const slotEndMin = finNum * 60;

        const ocupadas = activos.filter(r => {
            const ini = strToMin((r.hora_inicio || '00:00').substring(0,5));
            let end = strToMin((r.hora_fin || '00:00').substring(0,5));
            if (end === 0 && ini > 0) end = 24 * 60;
            return ini < slotEndMin && end > slotStartMin;
        });

        const ocupado = ocupadas.length > 0;
        const info = ocupado
            ? `${ocupadas[0].cancha_nombre || ''}${ocupadas[0].cliente_nombre ? ' · ' + ocupadas[0].cliente_nombre : ''}`
            : '';

        slots.push(`<div class="slot-item ${ocupado ? 'ocupado' : 'disponible'}">
            <div>
                <div style="font-weight:700;color:var(--text-primary);">${inicio} - ${fin}</div>
                <div style="font-size:11px;color:var(--text-secondary);">${info}</div>
            </div>
            <span class="slot-badge ${ocupado ? 'ocupado' : 'disponible'}">${ocupado ? 'Ocupado' : 'Disponible'}</span>
        </div>`);
    }

    const slotsClickable = slots.map((slotHtml, idx) => {
        const hora = 8 + idx;
        const inicio = `${String(hora).padStart(2,'0')}:00`;
        return slotHtml.includes('Disponible')
            ? slotHtml.replace('slot-item disponible', `slot-item disponible clickable`).replace('<div class="slot-item disponible clickable"', `<div class="slot-item disponible clickable" onclick="abrirReservaDesdeSlot('${fecha}','${inicio}')"`)
            : slotHtml;
    });

    document.getElementById('modalHorariosSlots').innerHTML = slotsClickable.join('');
    document.getElementById('modalHorariosDia').classList.add('open');
}

function sumarUnaHora(horaInicio) {
    const [h] = horaInicio.split(':').map(Number);
    const siguiente = (h + 1) % 24;
    return `${String(siguiente).padStart(2,'0')}:00`;
}

function abrirReservaDesdeSlot(fecha, horaInicio) {
    cerrarModalHorarios();
    abrirModalNuevo();
    document.getElementById('fFecha').value = fecha;
    document.getElementById('fHoraInicio').value = horaInicio;
    document.getElementById('fHoraFin').value = sumarUnaHora(horaInicio);
    calcularMonto();
}

function cerrarModalHorarios() {
    document.getElementById('modalHorariosDia').classList.remove('open');
}

function irHoy() {
    document.getElementById('filtroFecha').value = new Date().toISOString().split('T')[0];
    cargarReservas();
}

function cambiarFecha(dias) {
    const d = new Date(document.getElementById('filtroFecha').value);
    d.setDate(d.getDate() + dias);
    document.getElementById('filtroFecha').value = d.toISOString().split('T')[0];
    cargarReservas();
}

function calcularMonto() {
    const sel  = document.getElementById('fCancha');
    const opt  = sel.options[sel.selectedIndex];
    const precio = parseFloat(opt?.dataset?.precio || 0);
    const hi   = document.getElementById('fHoraInicio').value;
    const hf   = document.getElementById('fHoraFin').value;
    const calc = document.getElementById('montoCalc');
    if (precio > 0 && hi && hf && hf > hi) {
        const horas = (strToMin(hf) - strToMin(hi)) / 60;
        const total = Math.round(horas * precio);
        document.getElementById('montoCalcVal').textContent = '$' + total.toLocaleString('es-AR');
        document.getElementById('fMonto').value = total;
        calc.style.display = 'flex';
    } else { calc.style.display = 'none'; }
}

function strToMin(t) { const [h,m] = t.split(':').map(Number); return h*60+m; }

function abrirModalNuevo() {
    document.getElementById('modalTitle').textContent = 'Nueva Reserva';
    document.getElementById('reservaId').value = '';
    document.getElementById('clienteId').value = '';
    ['fCliente','fTelefono','fNotas','fMonto'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fClienteSelect').value = '';
    document.getElementById('fFecha').value      = document.getElementById('filtroFecha').value;
    document.getElementById('fHoraInicio').value = '';
    document.getElementById('fHoraFin').value    = '';
    document.getElementById('fEstado').value     = 'confirmada';
    document.getElementById('fMetodo').value     = 'efectivo';
    document.getElementById('montoCalc').style.display = 'none';
    cambiarModoCliente('seleccionar');
    cargarClientesSelect();
    document.getElementById('modalReserva').classList.add('open');
}

function cambiarModoCliente(modo) {
    const select = document.getElementById('fClienteSelect');
    const manual = document.getElementById('clienteManualFields');
    const btnSel = document.getElementById('btnClienteSeleccionar');
    const btnMan = document.getElementById('btnClienteManual');

    if (modo === 'seleccionar') {
        select.style.display = 'block';
        manual.style.display = 'none';
        btnSel.style.background = 'var(--primary)';
        btnSel.style.color = 'white';
        btnSel.style.borderColor = 'var(--primary)';
        btnMan.style.background = 'var(--surface)';
        btnMan.style.color = 'var(--text-primary)';
        btnMan.style.borderColor = 'var(--border)';
    } else {
        select.style.display = 'none';
        manual.style.display = 'block';
        btnSel.style.background = 'var(--surface)';
        btnSel.style.color = 'var(--text-primary)';
        btnSel.style.borderColor = 'var(--border)';
        btnMan.style.background = 'var(--primary)';
        btnMan.style.color = 'white';
        btnMan.style.borderColor = 'var(--primary)';
        document.getElementById('clienteId').value = '';
        document.getElementById('fClienteSelect').value = '';
    }
}

async function cargarClientesSelect() {
    try {
        const r = await fetch('../../api/canchas/clientes.php', {credentials: 'include'});
        const d = await r.json();
        if (d.success) {
            const clientes = d.data || [];
            const opts = '<option value="">Seleccionar cliente...</option>' +
                clientes.map(c => `<option value="${c.id}">${c.nombre}${c.telefono ? ' — ' + c.telefono : ''}</option>`).join('');
            document.getElementById('fClienteSelect').innerHTML = opts;
        }
    } catch(e) { console.error(e); }
}

function seleccionarCliente() {
    const select = document.getElementById('fClienteSelect');
    const clienteId = select.value;
    document.getElementById('clienteId').value = clienteId;
    if (!clienteId) return;
    const txt = select.options[select.selectedIndex]?.text || '';
    const [nombre, telefono] = txt.split(' — ');
    document.getElementById('fCliente').value = nombre || '';
    document.getElementById('fTelefono').value = telefono || '';
}

async function abrirEditar(id) {
    const r = reservas.find(x => x.id == id); if (!r) return;
    document.getElementById('modalTitle').textContent = 'Editar Reserva';
    document.getElementById('reservaId').value    = r.id;
    document.getElementById('fCancha').value      = r.cancha_id;
    document.getElementById('fFecha').value       = r.fecha;
    document.getElementById('fHoraInicio').value  = r.hora_inicio ? r.hora_inicio.substring(0,5) : '';
    document.getElementById('fHoraFin').value     = r.hora_fin   ? r.hora_fin.substring(0,5)    : '';
    document.getElementById('fMonto').value       = r.monto || '';
    document.getElementById('fMetodo').value      = r.metodo_pago || 'efectivo';
    document.getElementById('fEstado').value      = r.estado;
    document.getElementById('fNotas').value       = r.notas || '';

    await cargarClientesSelect();
    if (r.cliente_id) {
        cambiarModoCliente('seleccionar');
        document.getElementById('clienteId').value = r.cliente_id;
        document.getElementById('fClienteSelect').value = r.cliente_id;
        seleccionarCliente();
    } else {
        cambiarModoCliente('manual');
        document.getElementById('fCliente').value  = r.cliente_nombre || '';
        document.getElementById('fTelefono').value = r.cliente_telefono || '';
    }

    calcularMonto();
    document.getElementById('modalReserva').classList.add('open');
}

function cerrarModal() { document.getElementById('modalReserva').classList.remove('open'); }

async function guardar() {
    const id          = document.getElementById('reservaId').value;
    const cancha_id   = document.getElementById('fCancha').value;
    const fecha       = document.getElementById('fFecha').value;
    const hora_inicio = document.getElementById('fHoraInicio').value;
    const hora_fin    = document.getElementById('fHoraFin').value;

    if (!cancha_id || !fecha || !hora_inicio || !hora_fin) {
        showToast('Cancha, fecha y horario son requeridos', true);
        return;
    }
    if (hora_fin <= hora_inicio) {
        showToast('La hora de fin debe ser mayor al inicio', true);
        return;
    }

    const clienteId = document.getElementById('clienteId').value;
    const clienteNombre = document.getElementById('fCliente').value.trim();
    const clienteTelefono = document.getElementById('fTelefono').value.trim();
    if (!clienteId && !clienteNombre && !clienteTelefono) {
        showToast('Debe seleccionar o ingresar un cliente', true);
        return;
    }

    const body = {
        cancha_id, fecha, hora_inicio, hora_fin,
        cliente_nombre: clienteNombre,
        cliente_telefono: clienteTelefono,
        monto: parseFloat(document.getElementById('fMonto').value) || 0,
        metodo_pago: document.getElementById('fMetodo').value,
        estado: document.getElementById('fEstado').value,
        notas: document.getElementById('fNotas').value.trim(),
    };
    if (clienteId) body.cliente_id = parseInt(clienteId, 10);
    if (id) body.id = id;

    try {
        const r = await fetch('../../api/canchas/reservas.php', {
            method: id ? 'PUT' : 'POST',
            credentials: 'include',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(body)
        });
        const d = await r.json();
        if (d.success) {
            showToast(id ? 'Reserva actualizada' : 'Reserva creada');
            cerrarModal();
            if (vistaActual === 'calendario') {
                await cargarReservasMes();
                if (fechaCalendarioSeleccionada) await cargarReservas();
            } else if (vistaActual === 'semanal') {
                await cargarReservasSemana();
            } else {
                await cargarReservas();
            }
        } else {
            showToast(d.message || 'Error', true);
        }
    } catch(e) {
        showToast('Error de conexión', true);
    }
}

async function cambiarEstado(id, estado) {
    try {
        const r = await fetch('../../api/canchas/reservas.php', {
            method:'PUT',
            credentials: 'include',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({id, estado})
        });
        const d = await r.json();
        if (d.success) {
            showToast('Estado actualizado');
            if (vistaActual === 'calendario') {
                await cargarReservasMes();
                if (fechaCalendarioSeleccionada) await cargarReservas();
            } else if (vistaActual === 'semanal') {
                await cargarReservasSemana();
            } else {
                await cargarReservas();
            }
        } else {
            showToast(d.message||'Error', true);
        }
    } catch(e) {
        showToast('Error de conexión', true);
    }
}

function showToast(msg, error=false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast show' + (error ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalReserva').addEventListener('click', e => {
    if (e.target === document.getElementById('modalReserva')) cerrarModal();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarModalHorarios();
});
document.getElementById('modalHorariosDia').addEventListener('click', e => {
    if (e.target === document.getElementById('modalHorariosDia')) cerrarModalHorarios();
});
window.addEventListener('resize', () => {
    if (vistaActual === 'calendario') renderCalendarioMes();
});

init();
</script>
</body>
</html>
