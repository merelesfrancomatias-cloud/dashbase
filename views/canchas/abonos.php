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
    <title>Abonos - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .badge-estado { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-activo    { background:rgba(72,187,120,.15);color:#22c55e; }
        .badge-pausado   { background:rgba(246,173,85,.15);color:#f59e0b; }
        .badge-vencido   { background:rgba(160,174,192,.15);color:#94a3b8; }
        .badge-cancelado { background:rgba(245,101,101,.15);color:#ef4444; }
        .table-wrap { overflow-x:auto; }
        table { width:100%;border-collapse:collapse;font-size:14px; }
        th { text-align:left;padding:11px 16px;background:var(--background);color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border);white-space:nowrap;font-size:12px;text-transform:uppercase;letter-spacing:.5px; }
        td { padding:13px 16px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }
        .action-btns { display:flex;gap:6px; }
        .btn-table { width:34px;height:34px;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:var(--transition); }
        .btn-cancel{ background:rgba(245,101,101,.1);color:#ef4444; }
        .btn-cancel:hover { background:#ef4444;color:#fff; }
        .filter-tab { padding:7px 14px;border-radius:20px;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:13px;font-family:inherit;transition:var(--transition); }
        .filter-tab.active,.filter-tab:hover { background:var(--primary);color:#fff;border-color:var(--primary); }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface);border-radius:16px;width:100%;max-width:520px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);border:1px solid var(--border); }
        .modal-header { padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
        .modal-header h3 { margin:0;font-size:17px;color:var(--text-primary); }
        .modal-close { background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:18px;padding:4px; }
        .modal-close:hover { color:var(--error); }
        .modal-body { padding:24px; }
        .modal-footer { padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end; }
        .form-2col { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
        .form-2col .full { grid-column:1/-1; }
        .empty-state { text-align:center;padding:60px 20px;color:var(--text-secondary); }
        .empty-state i { font-size:44px;margin-bottom:14px;display:block;opacity:.4; }
        .toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);color:var(--text-primary);border-radius:12px;padding:14px 20px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:12px;max-width:320px;border:1px solid var(--border);border-left:4px solid var(--primary); }
        .toast.show { display:flex; }
        .toast.error { border-left-color:var(--error); }
        .abono-dia-tag { display:inline-flex;align-items:center;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:700;background:rgba(15,209,134,.12);color:var(--primary); }
        .reservas-count { display:inline-flex;align-items:center;gap:4px;font-size:13px;color:var(--text-secondary); }
        .reservas-count strong { color:var(--primary); }
    </style>
</head>
<body>
<div class="app-layout">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<?php include '../includes/header.php'; ?>

<div class="page-content">
    <!-- Page header -->
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
        <div>
            <h1 class="page-title" style="margin:0;font-size:22px;font-weight:700;">Abonos / Membresías</h1>
            <p style="margin:4px 0 0;color:var(--text-secondary);font-size:14px;">Slots fijos semanales con reservas automáticas</p>
        </div>
        <button class="btn btn-primary" onclick="abrirModal()">
            <i class="fas fa-plus"></i> Nuevo Abono
        </button>
    </div>

    <!-- Stats rápidos -->
    <div class="stats-grid" style="margin-bottom:24px;" id="statsWrap"></div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <button class="filter-tab active" onclick="setFiltro('',this)">Todos</button>
            <button class="filter-tab" onclick="setFiltro('activo',this)">Activos</button>
            <button class="filter-tab" onclick="setFiltro('pausado',this)">Pausados</button>
            <button class="filter-tab" onclick="setFiltro('vencido',this)">Vencidos</button>
            <button class="filter-tab" onclick="setFiltro('cancelado',this)">Cancelados</button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Cancha</th>
                        <th>Día</th>
                        <th>Horario</th>
                        <th>Vigencia</th>
                        <th>Monto/mes</th>
                        <th>Reservas</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-secondary);">Cargando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->

<!-- Modal nuevo abono -->
<div class="modal-overlay" id="modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Nuevo Abono</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-2col">
                <div class="full form-group">
                    <label class="form-label">Cliente *</label>
                    <input type="text" id="fNombre" class="form-control" placeholder="Nombre completo">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" id="fTelefono" class="form-control" placeholder="+54 9 ...">
                </div>
                <div class="form-group">
                    <label class="form-label">Cancha *</label>
                    <select id="fCancha" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Día de la semana *</label>
                    <select id="fDia" class="form-control">
                        <option value="0">Domingo</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora inicio *</label>
                    <input type="time" id="fHoraIni" class="form-control" value="20:00" min="08:00" max="22:00">
                </div>
                <div class="form-group">
                    <label class="form-label">Duración (horas) *</label>
                    <select id="fDur" class="form-control">
                        <option value="1">1 hora</option>
                        <option value="2">2 horas</option>
                        <option value="3">3 horas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Desde *</label>
                    <input type="date" id="fDesde" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Hasta *</label>
                    <input type="date" id="fHasta" class="form-control">
                </div>
                <div class="full form-group">
                    <label class="form-label">Monto mensual ($)</label>
                    <input type="number" id="fMonto" class="form-control" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="full form-group">
                    <label class="form-label">Notas</label>
                    <textarea id="fNotas" class="form-control" rows="2" placeholder="Observaciones…"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" id="btnGuardar" onclick="guardar()">Crear Abono</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"><span id="toastMsg"></span></div>

<script>
const API = '../../api/canchas/abonos.php';
const DIAS = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
let filtroActual = '';
let canchasDisp = [];

async function init() {
    await cargarCanchas();
    await cargar();
}

async function cargarCanchas() {
    try {
        const r = await fetch('../../api/canchas/canchas.php').then(x=>x.json());
        if (!r.success) return;
        canchasDisp = (r.data || []).filter(c=>c.activo==1);
        const sel = document.getElementById('fCancha');
        sel.innerHTML = canchasDisp.map(c=>`<option value="${c.id}">${c.nombre} – ${c.deporte||'General'}</option>`).join('');
    } catch {}
}

async function cargar() {
    const url = filtroActual ? `${API}?estado=${filtroActual}` : API;
    try {
        const r = await fetch(url).then(x=>x.json());
        if (!r.success) { showToast(r.message||'Error', true); return; }
        const rows = r.data || [];
        renderStats(rows);
        renderTabla(rows);
    } catch(e) {
        showToast('Error de conexión', true);
    }
}

function renderStats(rows) {
    const activos  = rows.filter(r=>r.estado==='activo').length;
    const ingMes   = rows.filter(r=>r.estado==='activo').reduce((s,r)=>s+parseFloat(r.monto_mensual||0),0);
    const reservas = rows.reduce((s,r)=>s+parseInt(r.total_reservas||0),0);
    document.getElementById('statsWrap').innerHTML = `
        <div class="stat-card"><div class="stat-icon" style="background:rgba(15,209,134,.12);color:var(--primary)"><i class="fas fa-id-card"></i></div><div class="stat-info"><p class="stat-label">Abonos Activos</p><h3 class="stat-value">${activos}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(102,126,234,.12);color:#667eea"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><p class="stat-label">Ingresos Mensuales</p><h3 class="stat-value">$${ingMes.toLocaleString('es-AR',{minimumFractionDigits:0})}</h3></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(246,173,85,.12);color:#f59e0b"><i class="fas fa-calendar-check"></i></div><div class="stat-info"><p class="stat-label">Reservas Generadas</p><h3 class="stat-value">${reservas}</h3></div></div>
    `;
}

function renderTabla(rows) {
    const tbody = document.getElementById('tbody');
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><i class="fas fa-id-card"></i><p>No hay abonos${filtroActual ? ' en este estado' : ''}.</p></div></td></tr>`;
        return;
    }
    const badgeMap = {
        activo:    'badge-activo',
        pausado:   'badge-pausado',
        vencido:   'badge-vencido',
        cancelado: 'badge-cancelado',
    };
    tbody.innerHTML = rows.map(r => {
        const desde = new Date(r.fecha_inicio+'T00:00:00').toLocaleDateString('es-AR',{day:'2-digit',month:'2-digit',year:'2-digit'});
        const hasta = new Date(r.fecha_fin+'T00:00:00').toLocaleDateString('es-AR',{day:'2-digit',month:'2-digit',year:'2-digit'});
        const horaIni = (r.hora_inicio||'').slice(0,5);
        const horaFin = (r.hora_fin||'').slice(0,5);
        const monto = parseFloat(r.monto_mensual||0).toLocaleString('es-AR',{minimumFractionDigits:0,maximumFractionDigits:0});
        const badge = badgeMap[r.estado] || 'badge-vencido';
        const wa = r.cliente_telefono ? `<button class="btn-table" title="WhatsApp" style="background:rgba(37,211,102,.12);color:#25d366" onclick="recordatorioWA('${r.cliente_telefono}','${(r.cliente_nombre||'').replace(/'/g,"\\'")}','${r.cancha_nombre}','${DIAS[r.dia_semana]}','${horaIni}','${horaFin}')"><i class="fab fa-whatsapp"></i></button>` : '';
        const cancelBtn = r.estado !== 'cancelado' ? `<button class="btn-table btn-cancel" title="Cancelar abono" onclick="cancelar(${r.id})"><i class="fas fa-ban"></i></button>` : '';
        return `<tr>
            <td><strong>${esc(r.cliente_nombre)}</strong>${r.cliente_telefono?`<br><small style="color:var(--text-secondary)">${esc(r.cliente_telefono)}</small>`:''}</td>
            <td>${esc(r.cancha_nombre)}<br><small style="color:var(--text-secondary)">${esc(r.deporte||'')}</small></td>
            <td><span class="abono-dia-tag">${DIAS[r.dia_semana]}</span></td>
            <td>${horaIni} – ${horaFin}<br><small style="color:var(--text-secondary)">${r.duracion_horas}h</small></td>
            <td style="white-space:nowrap;">${desde}<br><small style="color:var(--text-secondary)">al ${hasta}</small></td>
            <td>$${monto}</td>
            <td><span class="reservas-count"><i class="fas fa-calendar-check"></i> <strong>${r.total_reservas||0}</strong></span></td>
            <td><span class="badge-estado ${badge}">${r.estado}</span></td>
            <td><div class="action-btns">${wa}${cancelBtn}</div></td>
        </tr>`;
    }).join('');
}

function setFiltro(estado, btn) {
    filtroActual = estado;
    document.querySelectorAll('.filter-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    cargar();
}

function abrirModal() {
    // Default dates: today → +1 month
    const hoy = new Date();
    const mes = new Date(hoy); mes.setMonth(mes.getMonth()+1);
    document.getElementById('fDesde').value = fmt(hoy);
    document.getElementById('fHasta').value = fmt(mes);
    document.getElementById('modal').classList.add('open');
}

function cerrarModal() {
    document.getElementById('modal').classList.remove('open');
    ['fNombre','fTelefono','fHoraIni','fMonto','fNotas'].forEach(id=>{
        document.getElementById(id).value = id==='fHoraIni'?'20:00':'';
    });
    document.getElementById('fDur').value = '1';
    document.getElementById('fDia').value = '1';
}

async function guardar() {
    const nombre   = document.getElementById('fNombre').value.trim();
    const canchaId = parseInt(document.getElementById('fCancha').value);
    const dia      = parseInt(document.getElementById('fDia').value);
    const horaIni  = document.getElementById('fHoraIni').value;
    const dur      = parseInt(document.getElementById('fDur').value);
    const desde    = document.getElementById('fDesde').value;
    const hasta    = document.getElementById('fHasta').value;
    const monto    = parseFloat(document.getElementById('fMonto').value||0);

    if (!nombre || !canchaId || !horaIni || !desde || !hasta) {
        showToast('Completá los campos obligatorios', true); return;
    }
    if (hasta < desde) { showToast('La fecha final debe ser posterior al inicio', true); return; }

    // Calcular hora fin
    const [h, m] = horaIni.split(':').map(Number);
    const tsFin = new Date(2000, 0, 1, h + dur, m);
    const horaFin = tsFin.toTimeString().slice(0,5);

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const r = await fetch(API, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                cancha_id: canchaId, dia_semana: dia,
                cliente_nombre: nombre,
                cliente_telefono: document.getElementById('fTelefono').value.trim(),
                hora_inicio: horaIni, hora_fin: horaFin, duracion_horas: dur,
                monto_mensual: monto,
                fecha_inicio: desde, fecha_fin: hasta,
                notas: document.getElementById('fNotas').value.trim(),
            })
        }).then(x=>x.json());

        if (!r.success) { showToast(r.message||'Error', true); return; }
        showToast(`Abono creado — ${r.data.reservas_generadas} reservas generadas`);
        cerrarModal();
        cargar();
    } catch { showToast('Error de conexión', true); }
    finally { btn.disabled=false; btn.textContent='Crear Abono'; }
}

async function cancelar(id) {
    if (!confirm('¿Cancelar este abono y todas sus reservas futuras?')) return;
    try {
        const r = await fetch(`${API}?id=${id}`, {method:'DELETE'}).then(x=>x.json());
        if (!r.success) { showToast(r.message||'Error', true); return; }
        showToast('Abono cancelado');
        cargar();
    } catch { showToast('Error de conexión', true); }
}

function recordatorioWA(tel, nombre, cancha, dia, horaIni, horaFin) {
    const msg = `Hola ${nombre}! 👋 Te recordamos tu abono en *${cancha}* — todos los *${dia}* de *${horaIni}* a *${horaFin}*. ¡Te esperamos! ⚽`;
    const num = tel.replace(/\D/g,'');
    window.open(`https://wa.me/${num}?text=${encodeURIComponent(msg)}`, '_blank');
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmt(d) {
    return d.toISOString().slice(0,10);
}

function showToast(msg, isErr=false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.toggle('error', isErr);
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'), 3500);
}

init();
</script>
</body>
</html>
