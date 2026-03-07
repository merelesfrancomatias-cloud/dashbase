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
    <title>Agenda — Veterinaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --vet:#84cc16; --vet-dark:#65a30d; --vet-light:rgba(132,204,22,.1); }

        .vet-toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .vet-toolbar h1 { margin:0; font-size:20px; font-weight:700; }
        .vet-toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        .stats-bar { display:flex; gap:10px; padding:16px 24px 0; flex-wrap:wrap; }
        .stat-pill { display:flex; align-items:center; gap:8px; padding:7px 14px; border-radius:20px; font-size:13px; font-weight:600; border:1.5px solid var(--border); background:var(--background); color:var(--text-primary); }
        .stat-pill.verde    { background:var(--vet-light);          border-color:var(--vet);   color:var(--vet-dark); }
        .stat-pill.naranja  { background:rgba(249,115,22,.1);       border-color:#f97316;      color:#c2410c; }
        .stat-pill.azul     { background:rgba(99,102,241,.1);       border-color:#6366f1;      color:#4338ca; }
        .stat-pill.emerald  { background:rgba(5,150,105,.1);        border-color:#059669;      color:#065f46; }

        .filtros-bar { padding:14px 24px 0; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
        .fi-date { padding:9px 13px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-primary); cursor:pointer; }
        .fi-date:focus { outline:none; border-color:var(--vet); }
        .chip-filter { padding:6px 14px; border-radius:20px; border:1.5px solid var(--border); background:var(--background); font-size:12px; font-weight:600; cursor:pointer; color:var(--text-secondary); transition:all .15s; }
        .chip-filter.on { background:var(--vet-light); border-color:var(--vet); color:var(--vet-dark); }

        .agenda-list { padding:16px 24px; display:flex; flex-direction:column; gap:10px; }

        .turno-card {
            background:var(--surface); border-radius:16px; border:2px solid var(--border);
            overflow:hidden; transition:all .2s; display:flex; flex-direction:column;
        }
        .turno-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
        .turno-card.pendiente  { border-left:4px solid #f97316; }
        .turno-card.atendido   { border-left:4px solid var(--vet); opacity:.85; }
        .turno-card.cancelado  { border-left:4px solid #ef4444; opacity:.65; }

        .turno-top { padding:14px 16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .turno-hora { font-size:22px; font-weight:800; color:var(--text-primary); min-width:56px; text-align:center; }
        .turno-hora small { display:block; font-size:10px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.5px; }
        .turno-avatar { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
        .turno-info { flex:1; min-width:0; }
        .turno-nombre { font-size:15px; font-weight:700; color:var(--text-primary); }
        .turno-duenio { font-size:12px; color:var(--text-secondary); margin-top:2px; }
        .turno-badges { display:flex; gap:6px; flex-wrap:wrap; margin-top:5px; }
        .badge-tipo {
            font-size:10px; font-weight:700; padding:2px 8px; border-radius:8px;
            text-transform:uppercase; letter-spacing:.4px;
        }
        .badge-estado {
            font-size:10px; font-weight:700; padding:2px 8px; border-radius:8px;
            text-transform:uppercase; letter-spacing:.4px;
        }
        .estado-pendiente { background:rgba(249,115,22,.12);  color:#c2410c; }
        .estado-atendido  { background:rgba(132,204,22,.12);  color:var(--vet-dark); }
        .estado-cancelado { background:rgba(239,68,68,.12);   color:#dc2626; }
        .turno-actions { display:flex; gap:8px; margin-left:auto; }

        .btn-accion {
            padding:7px 12px; border-radius:9px; border:1.5px solid var(--border);
            font-size:12px; font-weight:600; cursor:pointer; transition:all .15s;
            background:var(--background); color:var(--text-secondary); display:flex; align-items:center; gap:5px;
        }
        .btn-accion.primary { background:linear-gradient(135deg,var(--vet),var(--vet-dark)); color:#fff; border-color:var(--vet-dark); }
        .btn-accion.danger  { background:rgba(239,68,68,.1); color:#dc2626; border-color:#ef4444; }
        .btn-accion.primary:hover { opacity:.9; }
        .btn-accion:hover   { background:var(--surface); box-shadow:0 2px 6px rgba(0,0,0,.08); }

        .turno-motivo { padding:0 16px 14px; font-size:13px; color:var(--text-secondary); border-top:1px solid var(--border); padding-top:10px; }

        /* Modales */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:560px; max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer-bar { padding:14px 24px 20px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block; font-size:12px; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .fi { width:100%; padding:10px 13px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-primary); box-sizing:border-box; transition:border-color .15s; }
        .fi:focus { outline:none; border-color:var(--vet); box-shadow:0 0 0 3px var(--vet-light); }
        .fg-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .msec { background:var(--background); border-radius:14px; padding:16px; margin-bottom:14px; }
        .msec-title { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); margin:0 0 12px; display:flex; align-items:center; gap:6px; }
        .toggle-chip { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:20px; border:1.5px solid var(--border); cursor:pointer; font-size:13px; font-weight:600; color:var(--text-secondary); background:var(--surface); transition:all .15s; user-select:none; }
        .toggle-chip.on { background:var(--vet-light); border-color:var(--vet); color:var(--vet-dark); }

        .empty-state { text-align:center; padding:60px 24px; color:var(--text-secondary); }
        .empty-state i { font-size:48px; opacity:.15; display:block; margin-bottom:16px; }

        .btn-nav { padding:8px 12px; border:1.5px solid var(--border); border-radius:10px; background:var(--surface); cursor:pointer; color:var(--text-secondary); font-size:15px; transition:all .15s; }
        .btn-nav:hover { background:var(--vet-light); border-color:var(--vet); color:var(--vet-dark); }

        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; pointer-events:none; }
        .toast.show { opacity:1; }

        @media(max-width:600px){
            .vet-toolbar, .filtros-bar, .agenda-list { padding-left:12px; padding-right:12px; }
            .turno-hora { font-size:18px; min-width:44px; }
            .turno-actions { flex-wrap:wrap; }
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
        <?php include '../includes/header.php'; ?>

        <!-- Toolbar -->
        <div class="vet-toolbar">
            <div>
                <h1><i class="fas fa-calendar-alt" style="color:var(--vet);margin-right:8px;"></i>Agenda del Día</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="pacientes.php" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-paw"></i> Pacientes
                </a>
                <button class="btn btn-primary" onclick="abrirNuevoTurno()" style="background:linear-gradient(135deg,#84cc16,#65a30d);border:none;">
                    <i class="fas fa-plus"></i> Nuevo Turno
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-pill naranja"><i class="fas fa-clock"></i> <span id="st-pend">0</span> Pendientes</div>
            <div class="stat-pill azul"><i class="fas fa-check-circle"></i> <span id="st-atend">0</span> Atendidos</div>
            <div class="stat-pill verde"><i class="fas fa-calendar-day"></i> <span id="st-total">0</span> Turnos</div>
            <div class="stat-pill emerald"><i class="fas fa-dollar-sign"></i> <span id="st-facturado">$0</span> Facturado</div>
        </div>

        <!-- Filtros de fecha -->
        <div class="filtros-bar">
            <button class="btn-nav" onclick="cambiarFecha(-1)" title="Día anterior"><i class="fas fa-chevron-left"></i></button>
            <input class="fi-date" type="date" id="fechaFiltro" onchange="cargarAgenda()">
            <button class="btn-nav" onclick="cambiarFecha(1)" title="Día siguiente"><i class="fas fa-chevron-right"></i></button>
            <button class="chip-filter on" id="btnHoy" onclick="irHoy()">Hoy</button>
            <div style="flex:1;"></div>
            <span class="chip-filter on" data-estado="" onclick="setEstado(this,'')">Todos</span>
            <span class="chip-filter" data-estado="pendiente" onclick="setEstado(this,'pendiente')"><i class="fas fa-clock"></i> Pendientes</span>
            <span class="chip-filter" data-estado="atendido"  onclick="setEstado(this,'atendido')"><i class="fas fa-check"></i> Atendidos</span>
        </div>

        <!-- Lista turnos -->
        <div id="agendaContent" class="agenda-list" style="padding-bottom:24px;">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Atender ═══ -->
<div class="modal-overlay" id="modalAtender">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#84cc16,#65a30d);border-radius:20px 20px 0 0;border-bottom:none;padding:20px 24px 16px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;"><i class="fas fa-stethoscope"></i></div>
                <div>
                    <h3 style="margin:0;color:#fff;font-size:16px;font-weight:700;">Atender consulta</h3>
                    <p id="atenderSubt" style="margin:0;color:rgba(255,255,255,.75);font-size:11px;">-</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalAtender()" style="color:rgba(255,255,255,.8);">✕</button>
        </div>
        <input type="hidden" id="atenderId">
        <div class="modal-body">
            <div class="msec">
                <p class="msec-title"><i class="fas fa-stethoscope" style="color:var(--vet);"></i> Resultado de la atención</p>
                <div class="fg"><label>Diagnóstico</label><textarea class="fi" id="atDiag" rows="2" placeholder="Diagnóstico final…" style="resize:vertical;"></textarea></div>
                <div class="fg"><label>Tratamiento</label><textarea class="fi" id="atTrat" rows="2" placeholder="Indicaciones y tratamiento…" style="resize:vertical;"></textarea></div>
                <div class="fg" style="margin-bottom:0;"><label>Medicamentos</label><input class="fi" type="text" id="atMeds" placeholder="Ej: Amoxicilina 250mg…"></div>
            </div>
            <div class="msec">
                <p class="msec-title"><i class="fas fa-ruler" style="color:var(--vet);"></i> Mediciones</p>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;"><label>Peso (kg)</label><input class="fi" type="number" id="atPeso" step="0.1" min="0" placeholder="0.0"></div>
                    <div class="fg" style="margin-bottom:0;"><label>Temperatura (°C)</label><input class="fi" type="number" id="atTemp" step="0.1" min="35" max="42" placeholder="38.5"></div>
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>Próximo turno</label><input class="fi" type="date" id="atProx"></div>
                <div class="fg"><label>Monto <span style="font-size:10px;color:var(--text-secondary);font-weight:400;">(va a caja)</span></label><input class="fi" type="number" id="atMonto" placeholder="0" min="0"></div>
            </div>
            <div class="fg" style="margin-bottom:0;">
                <label>Método de pago</label>
                <select class="fi" id="atMetodo">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="tarjeta_debito">💳 Débito</option>
                    <option value="tarjeta_credito">💳 Crédito</option>
                    <option value="transferencia">🏦 Transferencia</option>
                    <option value="qr">📱 QR</option>
                </select>
            </div>
        </div>
        <div class="modal-footer-bar">
            <button class="btn btn-secondary" onclick="cerrarModalAtender()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarAtencion()" style="background:linear-gradient(135deg,#84cc16,#65a30d);border:none;">
                <i class="fas fa-check-circle"></i> Marcar atendido
            </button>
        </div>
    </div>
</div>

<!-- ═══ Modal Nuevo Turno ═══ -->
<div class="modal-overlay" id="modalNuevoTurno">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:20px 20px 0 0;border-bottom:none;padding:20px 24px 16px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;"><i class="fas fa-calendar-plus"></i></div>
                <div>
                    <h3 style="margin:0;color:#fff;font-size:16px;font-weight:700;">Nuevo Turno</h3>
                    <p style="margin:0;color:rgba(255,255,255,.75);font-size:11px;">Agendar consulta</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarNuevoTurno()" style="color:rgba(255,255,255,.8);">✕</button>
        </div>
        <div class="modal-body">
            <div class="fg">
                <label>Paciente <span style="color:#ef4444;">*</span></label>
                <select class="fi" id="ntPaciente">
                    <option value="">— Seleccionar mascota —</option>
                </select>
            </div>
            <div class="msec">
                <p class="msec-title"><i class="fas fa-clock" style="color:#6366f1;"></i> Fecha y hora</p>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;"><label>Fecha <span style="color:#ef4444;">*</span></label><input class="fi" type="date" id="ntFecha"></div>
                    <div class="fg" style="margin-bottom:0;"><label>Hora</label><input class="fi" type="time" id="ntHora" value="09:00"></div>
                </div>
            </div>
            <div class="fg">
                <label>Tipo de atención</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;">
                    <span class="toggle-chip on" data-ntipo="consulta" onclick="selNTipo(this)">🩺 Consulta</span>
                    <span class="toggle-chip" data-ntipo="vacuna"   onclick="selNTipo(this)">💉 Vacuna</span>
                    <span class="toggle-chip" data-ntipo="cirugia"  onclick="selNTipo(this)">🔪 Cirugía</span>
                    <span class="toggle-chip" data-ntipo="baño"     onclick="selNTipo(this)">🛁 Baño</span>
                    <span class="toggle-chip" data-ntipo="control"  onclick="selNTipo(this)">✅ Control</span>
                    <span class="toggle-chip" data-ntipo="urgencia" onclick="selNTipo(this)">🚨 Urgencia</span>
                </div>
            </div>
            <div class="fg" style="margin-bottom:0;">
                <label>Motivo</label>
                <input class="fi" type="text" id="ntMotivo" placeholder="Revisión anual, vacunación, herida…">
            </div>
        </div>
        <div class="modal-footer-bar">
            <button class="btn btn-secondary" onclick="cerrarNuevoTurno()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarNuevoTurno()" style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;">
                <i class="fas fa-calendar-check"></i> Agendar turno
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const BASE      = '<?= $base ?>';
const API_CONS  = BASE + '/api/veterinaria/consultas.php';
const API_PAC   = BASE + '/api/veterinaria/pacientes.php';

let consultas    = [];
let filtroEstado = '';
let listaPacientes = [];

// ── Init ──────────────────────────────────────────────────────────────────────
function init() {
    const hoy = new Date().toISOString().slice(0,10);
    document.getElementById('fechaFiltro').value = hoy;
    cargarAgenda();
    cargarPacientes();
}

async function cargarPacientes() {
    const r = await fetch(API_PAC, {credentials:'include'});
    const j = await r.json();
    if (j.success) {
        listaPacientes = j.data.pacientes || [];
        const sel = document.getElementById('ntPaciente');
        listaPacientes.forEach(p => {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = `${p.nombre} (${p.especie}) — ${p.duenio_nombre}`;
            sel.appendChild(o);
        });
    }
}

async function cargarAgenda() {
    const fecha = document.getElementById('fechaFiltro').value;
    if (!fecha) return;
    const hoy = new Date().toISOString().slice(0,10);
    document.getElementById('btnHoy').style.display = fecha === hoy ? 'none' : '';
    document.getElementById('subtitulo').textContent = 'Cargando…';
    document.getElementById('agendaContent').innerHTML = `<div style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>`;
    const url = `${API_CONS}?fecha=${fecha}`;
    let r, j;
    try {
        r = await fetch(url, {credentials:'include'});
        j = await r.json();
    } catch(e) {
        document.getElementById('agendaContent').innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i><p>Error de conexión con la API</p></div>`;
        document.getElementById('subtitulo').textContent = 'Error de conexión';
        return;
    }
    if (!j.success) {
        document.getElementById('agendaContent').innerHTML = `<div class="empty-state"><i class="fas fa-lock" style="color:#f59e0b;"></i><p style="font-size:14px;font-weight:600;">${j.message||'Sin acceso'}</p><a href="../../index.php" class="btn btn-primary" style="margin-top:12px;">Iniciar sesión</a></div>`;
        document.getElementById('subtitulo').textContent = j.message || 'No autorizado';
        return;
    }

    consultas = j.data.consultas || [];
    const stats = j.data.stats || {};
    const d = new Date(fecha+'T00:00:00');
    const dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    document.getElementById('subtitulo').textContent =
        `${dias[d.getDay()]} ${d.getDate()} de ${meses[d.getMonth()]}`;
    document.getElementById('st-pend').textContent       = stats.pendientes   || 0;
    document.getElementById('st-atend').textContent      = stats.atendidos    || 0;
    document.getElementById('st-total').textContent      = stats.total_hoy    || 0;
    document.getElementById('st-facturado').textContent  = '$' + Number(stats.facturado_hoy||0).toLocaleString('es-AR');
    renderAgenda();
}

function renderAgenda() {
    let lista = consultas;
    if (filtroEstado) lista = lista.filter(c => c.estado === filtroEstado);
    lista = lista.sort((a,b) => (a.hora||'').localeCompare(b.hora||''));
    const cont = document.getElementById('agendaContent');
    if (!lista.length) {
        cont.innerHTML = `<div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:8px;">Sin turnos para este día</p>
            <p style="font-size:13px;margin-bottom:20px;">Podés agendar un nuevo turno</p>
            <button class="btn btn-primary" onclick="abrirNuevoTurno()" style="background:linear-gradient(135deg,#84cc16,#65a30d);border:none;"><i class="fas fa-plus"></i> Nuevo Turno</button>
        </div>`;
        return;
    }
    cont.innerHTML = lista.map(renderTurnoCard).join('');
}

function renderTurnoCard(c) {
    const hora    = (c.hora||'--:--').slice(0,5);
    const emoji   = especieEmoji[c.especie] || '🐾';
    const tipoClr = tipoColor(c.tipo);
    const acciones = c.estado === 'pendiente'
        ? `<button class="btn-accion primary" onclick="abrirAtender(${c.id},'${esc(c.pac_nombre)}','${esc(c.duenio_nombre)}')"><i class="fas fa-stethoscope"></i> Atender</button>
           <button class="btn-accion danger"  onclick="cancelarTurno(${c.id})"><i class="fas fa-times"></i> Cancelar</button>`
        : c.estado === 'atendido'
        ? `<span style="font-size:11px;font-weight:700;color:var(--vet-dark);padding:4px 8px;background:var(--vet-light);border-radius:8px;"><i class="fas fa-check"></i> Atendido</span>`
        : `<span style="font-size:11px;font-weight:700;color:#dc2626;padding:4px 8px;background:rgba(239,68,68,.1);border-radius:8px;"><i class="fas fa-times"></i> Cancelado</span>`;

    return `<div class="turno-card ${c.estado}">
        <div class="turno-top">
            <div class="turno-hora">${hora}<small>hs</small></div>
            <div class="turno-avatar pac-avatar ${c.especie||'otro'}" style="background:${tipoClr}20;color:${tipoClr};">${emoji}</div>
            <div class="turno-info">
                <div class="turno-nombre">${esc(c.pac_nombre||'—')}</div>
                <div class="turno-duenio"><i class="fas fa-user" style="margin-right:4px;font-size:10px;"></i>${esc(c.duenio_nombre||'—')}</div>
                <div class="turno-badges">
                    <span class="badge-tipo" style="background:${tipoClr}15;color:${tipoClr};">${tipoLabel(c.tipo)}</span>
                    <span class="badge-estado estado-${c.estado}">${estadoLabel(c.estado)}</span>
                    ${c.peso_consulta ? `<span class="badge-tipo" style="background:var(--background);color:var(--text-secondary);"><i class="fas fa-weight"></i> ${c.peso_consulta}kg</span>` : ''}
                    ${c.monto > 0 ? `<span class="badge-tipo" style="background:rgba(5,150,105,.1);color:#059669;font-weight:800;"><i class="fas fa-dollar-sign"></i>${fmt(c.monto)}</span>` : ''}
                </div>
            </div>
            <div class="turno-actions">${acciones}</div>
        </div>
        ${c.motivo || c.diagnostico ? `<div class="turno-motivo">
            ${c.motivo ? `<span style="font-weight:600;"><i class="fas fa-comment" style="margin-right:4px;color:var(--text-secondary);font-size:11px;"></i>${esc(c.motivo)}</span>` : ''}
            ${c.diagnostico ? `<span style="margin-left:12px;color:var(--text-secondary);"><i class="fas fa-notes-medical" style="margin-right:4px;font-size:11px;"></i>${esc(c.diagnostico)}</span>` : ''}
        </div>` : ''}
    </div>`;
}

function setEstado(el, val) {
    document.querySelectorAll('.chip-filter[data-estado]').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
    filtroEstado = val;
    renderAgenda();
}

function cambiarFecha(delta) {
    const d = new Date(document.getElementById('fechaFiltro').value + 'T00:00:00');
    d.setDate(d.getDate() + delta);
    document.getElementById('fechaFiltro').value = d.toISOString().slice(0,10);
    cargarAgenda();
}

function irHoy() {
    document.getElementById('fechaFiltro').value = new Date().toISOString().slice(0,10);
    cargarAgenda();
}

// ── Modal Atender ─────────────────────────────────────────────────────────────
function abrirAtender(id, pacNombre, duenioNombre) {
    document.getElementById('atenderId').value      = id;
    document.getElementById('atenderSubt').textContent = `${pacNombre} — ${duenioNombre}`;
    document.getElementById('atDiag').value  = '';
    document.getElementById('atTrat').value  = '';
    document.getElementById('atMeds').value  = '';
    document.getElementById('atPeso').value  = '';
    document.getElementById('atTemp').value  = '';
    document.getElementById('atProx').value  = '';
    document.getElementById('atMonto').value = '';
    document.getElementById('modalAtender').classList.add('open');
    setTimeout(() => document.getElementById('atDiag').focus(), 100);
}
function cerrarModalAtender() { document.getElementById('modalAtender').classList.remove('open'); }

async function guardarAtencion() {
    const id = document.getElementById('atenderId').value;
    if (!id) return;
    const body = {
        diagnostico:   document.getElementById('atDiag').value.trim()    || null,
        tratamiento:   document.getElementById('atTrat').value.trim()    || null,
        medicamentos:  document.getElementById('atMeds').value.trim()    || null,
        peso_consulta: parseFloat(document.getElementById('atPeso').value) || null,
        temperatura:   parseFloat(document.getElementById('atTemp').value) || null,
        proximo_turno: document.getElementById('atProx').value            || null,
        monto:         parseFloat(document.getElementById('atMonto').value)|| 0,
        metodo_pago:   document.getElementById('atMetodo').value,
        estado:        'atendido',
    };
    const r = await fetch(`${API_CONS}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) { cerrarModalAtender(); toast('Consulta marcada como atendida ✓'); cargarAgenda(); }
    else toast(j.message || 'Error', 'error');
}

async function cancelarTurno(id) {
    if (!confirm('¿Cancelar este turno?')) return;
    const r = await fetch(`${API_CONS}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'}, body: JSON.stringify({estado:'cancelado'})
    });
    const j = await r.json();
    if (j.success) { toast('Turno cancelado'); cargarAgenda(); }
    else toast(j.message || 'Error', 'error');
}

// ── Modal Nuevo Turno ─────────────────────────────────────────────────────────
function abrirNuevoTurno() {
    const hoy = new Date().toISOString().slice(0,10);
    document.getElementById('ntFecha').value   = document.getElementById('fechaFiltro').value || hoy;
    document.getElementById('ntHora').value    = '09:00';
    document.getElementById('ntPaciente').value= '';
    document.getElementById('ntMotivo').value  = '';
    document.querySelectorAll('[data-ntipo]').forEach(c => c.classList.remove('on'));
    document.querySelector('[data-ntipo="consulta"]').classList.add('on');
    document.getElementById('modalNuevoTurno').classList.add('open');
}
function cerrarNuevoTurno() { document.getElementById('modalNuevoTurno').classList.remove('open'); }

function selNTipo(el) {
    document.querySelectorAll('[data-ntipo]').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
}

async function guardarNuevoTurno() {
    const pacId = document.getElementById('ntPaciente').value;
    const fecha = document.getElementById('ntFecha').value;
    if (!pacId) { toast('Seleccioná un paciente', 'error'); return; }
    if (!fecha) { toast('Ingresá la fecha', 'error'); return; }
    const tipo = document.querySelector('[data-ntipo].on')?.dataset.ntipo || 'consulta';
    const body = {
        paciente_id: parseInt(pacId),
        fecha,
        hora:   document.getElementById('ntHora').value,
        tipo,
        motivo: document.getElementById('ntMotivo').value.trim() || null,
        estado: 'pendiente',
        monto:  0,
    };
    const r = await fetch(API_CONS, {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) { cerrarNuevoTurno(); toast('Turno agendado ✓'); cargarAgenda(); }
    else toast(j.message || 'Error', 'error');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const especieEmoji = {perro:'🐶',gato:'🐱',ave:'🐦',conejo:'🐰',reptil:'🦎',otro:'🐾'};
function tipoColor(t) {
    return {consulta:'#6366f1',vacuna:'#eab308',cirugia:'#ef4444',baño:'#3b82f6',grooming:'#3b82f6',control:'#0FD186',urgencia:'#f97316'}[t] || '#64748b';
}
function tipoLabel(t) {
    return {consulta:'Consulta',vacuna:'Vacuna',cirugia:'Cirugía',baño:'Baño',grooming:'Grooming',control:'Control',urgencia:'Urgencia'}[t] || t;
}
function estadoLabel(e) {
    return {pendiente:'Pendiente',atendido:'Atendido',cancelado:'Cancelado'}[e] || e;
}
function fmt(n)   { return '$' + Number(n||0).toLocaleString('es-AR'); }
function esc(s)   { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent    = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { cerrarModalAtender(); cerrarNuevoTurno(); }
});
['modalAtender','modalNuevoTurno'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});

// Agrega la clase pac-avatar para el avatar en turno-avatar (heredado de pacientes.css inline)
document.head.insertAdjacentHTML('beforeend', `<style>
.pac-avatar.perro  { background:rgba(234,179,8,.15);  }
.pac-avatar.gato   { background:rgba(99,102,241,.15); }
.pac-avatar.ave    { background:rgba(59,130,246,.15); }
.pac-avatar.conejo { background:rgba(236,72,153,.15); }
.pac-avatar.reptil { background:rgba(132,204,22,.15); }
.pac-avatar.otro   { background:rgba(100,116,139,.15);}
</style>`);

init();
</script>
</body>
</html>
