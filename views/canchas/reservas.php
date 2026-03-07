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
        @media(max-width:600px) { .form-2col { grid-template-columns:1fr; } .form-2col .full { grid-column:1; } }
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
                    <div class="nav-fecha">
                        <button class="nav-btn" onclick="cambiarFecha(-1)" title="Día anterior"><i class="fas fa-chevron-left"></i></button>
                        <input type="date" class="form-control" id="filtroFecha" style="margin:0;min-width:150px;" onchange="cargarReservas()">
                        <button class="nav-btn" onclick="cambiarFecha(1)" title="Día siguiente"><i class="fas fa-chevron-right"></i></button>
                        <button class="nav-btn" onclick="irHoy()" title="Hoy"><i class="fas fa-calendar-day"></i></button>
                    </div>
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="setFiltro('',this)">Todas</button>
                        <button class="filter-tab" onclick="setFiltro('confirmada',this)">Confirmadas</button>
                        <button class="filter-tab" onclick="setFiltro('pendiente',this)">Pendientes</button>
                        <button class="filter-tab" onclick="setFiltro('cancelada',this)">Canceladas</button>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <div id="loadingState" style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
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
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="fCliente" placeholder="Nombre del cliente">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="fTelefono" placeholder="Ej: 11-1234-5678">
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

<div class="toast" id="toast"><i class="fas fa-check-circle" style="color:var(--primary);"></i><span id="toastMsg"></span></div>

<script>
let reservas = [], canchas = [], filtroEstado = '';

async function init() {
    await cargarCanchas();
    const params = new URLSearchParams(window.location.search);
    const canchaId = params.get('cancha_id');
    if (canchaId) document.getElementById('fCancha').value = canchaId;
    irHoy();
}

async function cargarCanchas() {
    try {
        const r = await fetch('../../api/canchas/canchas.php');
        const d = await r.json();
        if (d.success) {
            canchas = d.data.filter(c => c.activo == 1);
            const opts = '<option value="">Seleccionar cancha...</option>' +
                canchas.map(c => `<option value="${c.id}" data-precio="${c.precio_hora}">${c.nombre}${c.deporte?' — '+c.deporte:''}</option>`).join('');
            document.getElementById('fCancha').innerHTML = opts;
        }
    } catch(e) {}
}

async function cargarReservas() {
    const fecha = document.getElementById('filtroFecha').value;
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('reservasTable').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    try {
        let url = `../../api/canchas/reservas.php?fecha=${fecha}`;
        const r = await fetch(url);
        const d = await r.json();
        document.getElementById('loadingState').style.display = 'none';
        if (d.success) {
            reservas = d.data.reservas || [];
            const st  = d.data.stats   || {};
            document.getElementById('statHoy').textContent     = st.total      || 0;
            document.getElementById('statConf').textContent    = st.confirmadas || 0;
            document.getElementById('statPend').textContent    = st.pendientes  || 0;
            document.getElementById('statIngresos').textContent= st.ingresos
                ? '$' + parseFloat(st.ingresos).toLocaleString('es-AR',{minimumFractionDigits:0})
                : '$0';
            renderTabla();
        }
    } catch(e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    }
}

function renderTabla() {
    const lista = filtroEstado ? reservas.filter(r => r.estado === filtroEstado) : reservas;
    if (!lista.length) { document.getElementById('emptyState').style.display = 'block'; return; }
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
    ['fCliente','fTelefono','fNotas','fMonto'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fFecha').value      = document.getElementById('filtroFecha').value;
    document.getElementById('fHoraInicio').value = '';
    document.getElementById('fHoraFin').value    = '';
    document.getElementById('fEstado').value     = 'confirmada';
    document.getElementById('fMetodo').value     = 'efectivo';
    document.getElementById('montoCalc').style.display = 'none';
    document.getElementById('modalReserva').classList.add('open');
}

function abrirEditar(id) {
    const r = reservas.find(x => x.id == id); if (!r) return;
    document.getElementById('modalTitle').textContent = 'Editar Reserva';
    document.getElementById('reservaId').value    = r.id;
    document.getElementById('fCancha').value      = r.cancha_id;
    document.getElementById('fFecha').value       = r.fecha;
    document.getElementById('fHoraInicio').value  = r.hora_inicio ? r.hora_inicio.substring(0,5) : '';
    document.getElementById('fHoraFin').value     = r.hora_fin   ? r.hora_fin.substring(0,5)    : '';
    document.getElementById('fCliente').value     = r.cliente_nombre    || '';
    document.getElementById('fTelefono').value    = r.cliente_telefono  || '';
    document.getElementById('fMonto').value       = r.monto || '';
    document.getElementById('fMetodo').value      = r.metodo_pago || 'efectivo';
    document.getElementById('fEstado').value      = r.estado;
    document.getElementById('fNotas').value       = r.notas || '';
    calcularMonto();
    document.getElementById('modalReserva').classList.add('open');
}

function cerrarModal() { document.getElementById('modalReserva').classList.remove('open'); }

async function guardar() {
    const id         = document.getElementById('reservaId').value;
    const cancha_id  = document.getElementById('fCancha').value;
    const fecha      = document.getElementById('fFecha').value;
    const hora_inicio= document.getElementById('fHoraInicio').value;
    const hora_fin   = document.getElementById('fHoraFin').value;
    if (!cancha_id || !fecha || !hora_inicio || !hora_fin)
        { showToast('Cancha, fecha y horario son requeridos', true); return; }
    if (hora_fin <= hora_inicio)
        { showToast('La hora de fin debe ser mayor al inicio', true); return; }
    const body = {
        cancha_id, fecha, hora_inicio, hora_fin,
        cliente_nombre:   document.getElementById('fCliente').value.trim(),
        cliente_telefono: document.getElementById('fTelefono').value.trim(),
        monto:            parseFloat(document.getElementById('fMonto').value) || 0,
        metodo_pago:      document.getElementById('fMetodo').value,
        estado:           document.getElementById('fEstado').value,
        notas:            document.getElementById('fNotas').value.trim(),
    };
    if (id) body.id = id;
    try {
        const r = await fetch('../../api/canchas/reservas.php', {method: id?'PUT':'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
        const d = await r.json();
        if (d.success) { showToast(id?'Reserva actualizada':'Reserva creada'); cerrarModal(); cargarReservas(); }
        else showToast(d.message||'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

async function cambiarEstado(id, estado) {
    try {
        const r = await fetch('../../api/canchas/reservas.php', {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id, estado})});
        const d = await r.json();
        if (d.success) { showToast('Estado actualizado'); cargarReservas(); }
        else showToast(d.message||'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

function showToast(msg, error=false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast show' + (error?' error':'');
    setTimeout(()=>t.classList.remove('show'), 3500);
}

document.addEventListener('keydown', e => { if (e.key==='Escape') cerrarModal(); });
document.getElementById('modalReserva').addEventListener('click', e => { if (e.target===document.getElementById('modalReserva')) cerrarModal(); });

init();
</script>
</body>
</html>
