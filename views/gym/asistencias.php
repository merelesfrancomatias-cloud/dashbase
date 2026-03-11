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
    <title>Asistencias - Gimnasio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .table-wrap { overflow-x:auto; }
        table { width:100%;border-collapse:collapse;font-size:14px; }
        th { text-align:left;padding:11px 16px;background:var(--background);color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border);white-space:nowrap;font-size:12px;text-transform:uppercase;letter-spacing:.5px; }
        td { padding:13px 16px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }
        .check-in-panel { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;display:flex;flex-direction:column;gap:16px; }
        .check-in-title { font-size:16px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:10px; }
        .check-in-title i { color:#f97316; }
        .hora-display { font-size:36px;font-weight:800;color:var(--text-primary);text-align:center;font-variant-numeric:tabular-nums; }
        .fecha-display { text-align:center;font-size:13px;color:var(--text-secondary);margin-top:4px; }
        .btn-checkin { width:100%;padding:14px;background:#f97316;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:var(--transition); }
        .btn-checkin:hover { background:#ea6c0a; }
        .empty-state { text-align:center;padding:60px 20px;color:var(--text-secondary); }
        .empty-state i { font-size:44px;margin-bottom:14px;display:block;opacity:.4; }
        .toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);color:var(--text-primary);border-radius:12px;padding:14px 20px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:12px;max-width:320px;border:1px solid var(--border);border-left:4px solid var(--primary); }
        .toast.show { display:flex; }
        .toast.error { border-left-color:var(--error); }
        body.dark-mode th { background:rgba(255,255,255,.03); }
        body.dark-mode tr:hover td { background:rgba(255,255,255,.03); }
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
                    <div style="width:48px;height:48px;background:rgba(249,115,22,.12);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#f97316;">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div>
                        <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">Asistencias</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Control de entrada y presencia</p>
                    </div>
                </div>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="date" class="form-control" id="filtroFecha" style="margin:0;min-width:160px;">
                    <button class="btn btn-secondary" onclick="cargarAsistencias()"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px;margin-bottom:24px;">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info"><p class="stat-label">Asistencias hoy</p><h3 class="stat-value" id="statHoy">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-calendar-week"></i></div>
                    <div class="stat-info"><p class="stat-label">Esta semana</p><h3 class="stat-value" id="statSemana">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                    <div class="stat-info"><p class="stat-label">Últ. registro</p><h3 class="stat-value" id="statUltimo" style="font-size:20px;">—</h3></div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">
                <div class="card">
                    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                        <h3><i class="fas fa-list-check"></i> Registro del día</h3>
                        <span id="fechaTitulo" style="font-size:13px;color:var(--text-secondary);"></span>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div id="loadingState" style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                        <div class="table-wrap">
                            <table id="asistTable" style="display:none;">
                                <thead><tr>
                                    <th>#</th><th>Socio</th><th>Hora entrada</th><th>Acción</th>
                                </tr></thead>
                                <tbody id="asistTbody"></tbody>
                            </table>
                        </div>
                        <div id="emptyState" class="empty-state" style="display:none;">
                            <i class="fas fa-person-walking-arrow-right"></i>
                            <p>Sin asistencias en esta fecha</p>
                        </div>
                    </div>
                </div>

                <div class="check-in-panel">
                    <div class="check-in-title"><i class="fas fa-fingerprint"></i> Check-in Manual</div>
                    <div>
                        <div class="hora-display" id="horaReloj">00:00</div>
                        <div class="fecha-display" id="fechaReloj"></div>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Socio</label>
                        <select class="form-control" id="selSocio">
                            <option value="">Seleccionar socio...</option>
                        </select>
                    </div>
                    <button class="btn-checkin" onclick="hacerCheckin()">
                        <i class="fas fa-fingerprint"></i> Registrar Entrada
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-check-circle" style="color:var(--primary);"></i><span id="toastMsg"></span></div>

<script>
let asistencias = [], socios = [];

function initReloj() {
    function tick() {
        const now = new Date();
        document.getElementById('horaReloj').textContent = now.toTimeString().substring(0, 5);
        const dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        document.getElementById('fechaReloj').textContent = `${dias[now.getDay()]}, ${now.getDate()} ${meses[now.getMonth()]} ${now.getFullYear()}`;
    }
    tick();
    setInterval(tick, 1000);
}

async function cargarSocios() {
    try {
        const r = await fetch('../../api/gym/socios.php?estado=activo');
        const d = await r.json();
        if (d.success) {
            socios = d.data.socios || [];
            const sel = document.getElementById('selSocio');
            sel.innerHTML = '<option value="">Seleccionar socio...</option>' +
                socios.map(s => `<option value="${s.id}">${s.nombre} ${s.apellido}</option>`).join('');
        }
    } catch(e) {}
}

async function cargarAsistencias() {
    const fecha = document.getElementById('filtroFecha').value;
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('asistTable').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('fechaTitulo').textContent = fecha;
    try {
        const r = await fetch(`../../api/gym/asistencias.php?fecha=${fecha}`);
        const d = await r.json();
        document.getElementById('loadingState').style.display = 'none';
        if (d.success) {
            asistencias = d.data.asistencias || [];
            actualizarStats();
            renderTabla();
        }
    } catch(e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    }
}

function actualizarStats() {
    document.getElementById('statHoy').textContent = asistencias.length;
    const ult = asistencias.length ? asistencias[asistencias.length-1].hora : null;
    document.getElementById('statUltimo').textContent = ult ? ult.substring(0,5) : '—';
}

function renderTabla() {
    if (!asistencias.length) { document.getElementById('emptyState').style.display = 'block'; return; }
    document.getElementById('asistTable').style.display = 'table';
    document.getElementById('asistTbody').innerHTML = asistencias.map((a, i) => `<tr>
        <td style="color:var(--text-secondary);font-size:13px;">${i+1}</td>
        <td>
            <div style="font-weight:600;">${a.socio_nombre||'—'} ${a.socio_apellido||''}</div>
        </td>
        <td>
            <span style="background:rgba(72,187,120,.12);color:#22c55e;padding:4px 10px;border-radius:6px;font-size:13px;font-weight:700;">
                <i class="fas fa-clock" style="margin-right:4px;"></i>${a.hora ? a.hora.substring(0,5) : '—'}
            </span>
        </td>
        <td>
            <button class="btn btn-danger btn-sm btn-icon" title="Eliminar" onclick="eliminar(${a.id})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`).join('');
}

async function hacerCheckin() {
    const socio_id = document.getElementById('selSocio').value;
    if (!socio_id) { showToast('Seleccioná un socio', true); return; }
    const now  = new Date();
    const fecha= now.toISOString().split('T')[0];
    const hora = now.toTimeString().substring(0, 5);
    try {
        const r = await fetch('../../api/gym/asistencias.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({socio_id, fecha, hora})
        });
        const d = await r.json();
        if (d.success) {
            const sel = document.getElementById('selSocio');
            const nombre = sel.options[sel.selectedIndex].text;
            showToast('Check-in: ' + nombre);
            document.getElementById('selSocio').value = '';
            if (document.getElementById('filtroFecha').value === fecha) cargarAsistencias();
        } else {
            showToast(d.message || 'Error al registrar', true);
        }
    } catch(e) { showToast('Error de conexión', true); }
}

async function eliminar(id) {
    if (!confirm('¿Eliminar este registro de asistencia?')) return;
    try {
        const r = await fetch('../../api/gym/asistencias.php', {
            method: 'DELETE',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({id})
        });
        const d = await r.json();
        if (d.success) { showToast('Registro eliminado'); cargarAsistencias(); }
        else showToast(d.message || 'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

async function cargarSemana() {
    try {
        const hoy   = new Date();
        const lunes = new Date(hoy); lunes.setDate(hoy.getDate() - hoy.getDay() + 1);
        const desde = lunes.toISOString().split('T')[0];
        const hasta = hoy.toISOString().split('T')[0];
        const r = await fetch(`../../api/gym/asistencias.php?desde=${desde}&hasta=${hasta}`);
        const d = await r.json();
        if (d.success) {
            document.getElementById('statSemana').textContent = d.data.total ?? (Array.isArray(d.data.asistencias) ? d.data.asistencias.length : '—');
        }
    } catch(e) {}
}

function showToast(msg, error = false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast show' + (error ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}

document.getElementById('filtroFecha').value = new Date().toISOString().split('T')[0];
initReloj();
cargarSocios();
cargarAsistencias();
cargarSemana();
</script>
</body>
</html>
