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
    <title>Clases - Gimnasio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .dias-chips { display:flex;gap:4px;flex-wrap:wrap; }
        .chip { display:inline-block;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(249,115,22,.12);color:#f97316; }
        .badge-nivel { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .nivel-basico      { background:rgba(72,187,120,.12);color:#22c55e; }
        .nivel-intermedio  { background:rgba(246,173,85,.15);color:#f59e0b; }
        .nivel-avanzado    { background:rgba(245,101,101,.12);color:#ef4444; }
        .clase-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;transition:var(--transition); }
        .clase-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.1);transform:translateY(-2px); }
        .clase-card-top { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px; }
        .clase-card h4 { margin:0;font-size:16px;font-weight:700;color:var(--text-primary); }
        .clase-card-meta { display:flex;flex-direction:column;gap:6px;font-size:13px;color:var(--text-secondary); }
        .clase-card-meta span { display:flex;align-items:center;gap:7px; }
        .clase-card-meta i { width:14px;color:var(--primary); }
        .clase-card-footer { margin-top:14px;padding-top:14px;border-top:1px solid var(--border);display:flex;gap:8px; }
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
        .dias-check-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:8px; }
        .dia-check { display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-primary);cursor:pointer; }
        .dia-check input { accent-color:var(--primary); }
        .empty-state { text-align:center;padding:60px 20px;color:var(--text-secondary); }
        .empty-state i { font-size:44px;margin-bottom:14px;display:block;opacity:.4; }
        .toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);color:var(--text-primary);border-radius:12px;padding:14px 20px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:12px;max-width:320px;border:1px solid var(--border);border-left:4px solid var(--primary); }
        .toast.show { display:flex; }
        .toast.error { border-left-color:var(--error); }
        body.dark-mode .clase-card { border-color:rgba(255,255,255,.08); }
        body.dark-mode .clase-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.3); }
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
                        <i class="fas fa-calendar-days"></i>
                    </div>
                    <div>
                        <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">Clases</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Gestión de clases y horarios</p>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="abrirModalNuevo()">
                    <i class="fas fa-plus"></i> Nueva Clase
                </button>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px;margin-bottom:24px;">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-dumbbell"></i></div>
                    <div class="stat-info"><p class="stat-label">Total clases</p><h3 class="stat-value" id="statTotal">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                    <div class="stat-info"><p class="stat-label">Capacidad total</p><h3 class="stat-value" id="statCapacidad">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-person-running"></i></div>
                    <div class="stat-info"><p class="stat-label">Instructores</p><h3 class="stat-value" id="statInstructores">—</h3></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Listado de Clases</h3>
                </div>
                <div class="card-body">
                    <div id="loadingState" style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    <div id="clasesGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;display:none;"></div>
                    <div id="emptyState" class="empty-state" style="display:none;">
                        <i class="fas fa-calendar-xmark"></i><p>No hay clases registradas</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Clase -->
<div class="modal-overlay" id="modalClase">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nueva Clase</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="claseId">
            <div class="form-2col">
                <div class="form-group full">
                    <label class="form-label">Nombre de la clase *</label>
                    <input type="text" class="form-control" id="fNombre" placeholder="Ej: CrossFit, Yoga, Spinning...">
                </div>
                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <input type="text" class="form-control" id="fInstructor">
                </div>
                <div class="form-group">
                    <label class="form-label">Capacidad máx.</label>
                    <input type="number" class="form-control" id="fCapacidad" min="1" max="200">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora inicio</label>
                    <input type="time" class="form-control" id="fHoraInicio">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora fin</label>
                    <input type="time" class="form-control" id="fHoraFin">
                </div>
                <div class="form-group">
                    <label class="form-label">Nivel</label>
                    <select class="form-control" id="fNivel">
                        <option value="basico">Básico</option>
                        <option value="intermedio">Intermedio</option>
                        <option value="avanzado">Avanzado</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Días de la semana</label>
                    <div class="dias-check-grid">
                        <label class="dia-check"><input type="checkbox" name="dias" value="lunes"> Lunes</label>
                        <label class="dia-check"><input type="checkbox" name="dias" value="martes"> Martes</label>
                        <label class="dia-check"><input type="checkbox" name="dias" value="miercoles"> Miérc.</label>
                        <label class="dia-check"><input type="checkbox" name="dias" value="jueves"> Jueves</label>
                        <label class="dia-check"><input type="checkbox" name="dias" value="viernes"> Viernes</label>
                        <label class="dia-check"><input type="checkbox" name="dias" value="sabado"> Sábado</label>
                        <label class="dia-check"><input type="checkbox" name="dias" value="domingo"> Domingo</label>
                    </div>
                </div>
                <div class="form-group full">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" id="fDescripcion" rows="2" style="resize:vertical;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarClase()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-check-circle" style="color:var(--primary);"></i><span id="toastMsg"></span></div>

<script>
let clases = [];

async function cargarClases() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('clasesGrid').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    try {
        const r = await fetch('../../api/gym/clases.php');
        const d = await r.json();
        document.getElementById('loadingState').style.display = 'none';
        if (d.success) {
            clases = d.data;
            actualizarStats();
            renderClases();
        }
    } catch(e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    }
}

function actualizarStats() {
    document.getElementById('statTotal').textContent = clases.length;
    const cap = clases.reduce((s, c) => s + (parseInt(c.capacidad_maxima) || 0), 0);
    document.getElementById('statCapacidad').textContent = cap;
    const instrs = new Set(clases.filter(c => c.instructor).map(c => c.instructor));
    document.getElementById('statInstructores').textContent = instrs.size;
}

function renderClases() {
    const grid = document.getElementById('clasesGrid');
    if (!clases.length) { document.getElementById('emptyState').style.display = 'block'; return; }
    grid.style.display = 'grid';
    const nivelBadge = { basico:'nivel-basico', intermedio:'nivel-intermedio', avanzado:'nivel-avanzado' };
    grid.innerHTML = clases.map(c => {
        const dias = c.dias_semana ? c.dias_semana.split(',').map(d => `<span class="chip">${d.trim().substring(0,3).toUpperCase()}</span>`).join('') : '';
        return `<div class="clase-card">
            <div class="clase-card-top">
                <div>
                    <h4>${c.nombre}</h4>
                    ${c.nivel ? `<span class="badge-nivel ${nivelBadge[c.nivel]||''}" style="margin-top:6px;">${c.nivel.charAt(0).toUpperCase()+c.nivel.slice(1)}</span>` : ''}
                </div>
                <div style="display:flex;gap:6px;">
                    <button class="btn btn-secondary btn-sm btn-icon" title="Editar" onclick="abrirEditar(${c.id})"><i class="fas fa-pen"></i></button>
                    <button class="btn btn-danger btn-sm btn-icon" title="Eliminar" onclick="eliminar(${c.id},'${c.nombre}')"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="clase-card-meta">
                ${c.instructor ? `<span><i class="fas fa-person-running"></i>${c.instructor}</span>` : ''}
                ${c.hora_inicio ? `<span><i class="fas fa-clock"></i>${c.hora_inicio}${c.hora_fin ? ' — '+c.hora_fin : ''}</span>` : ''}
                ${c.capacidad_maxima ? `<span><i class="fas fa-users"></i>Máx. ${c.capacidad_maxima} personas</span>` : ''}
                ${c.descripcion ? `<span><i class="fas fa-info-circle"></i>${c.descripcion}</span>` : ''}
            </div>
            ${dias ? `<div class="clase-card-footer dias-chips">${dias}</div>` : ''}
        </div>`;
    }).join('');
}

function abrirModalNuevo() {
    document.getElementById('modalTitle').textContent = 'Nueva Clase';
    document.getElementById('claseId').value = '';
    ['fNombre','fInstructor','fDescripcion'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fCapacidad').value = 20;
    document.getElementById('fHoraInicio').value = '';
    document.getElementById('fHoraFin').value = '';
    document.getElementById('fNivel').value = 'basico';
    document.querySelectorAll('input[name="dias"]').forEach(cb => cb.checked = false);
    document.getElementById('modalClase').classList.add('open');
}

function abrirEditar(id) {
    const c = clases.find(x => x.id == id);
    if (!c) return;
    document.getElementById('modalTitle').textContent = 'Editar Clase';
    document.getElementById('claseId').value    = c.id;
    document.getElementById('fNombre').value    = c.nombre;
    document.getElementById('fInstructor').value= c.instructor || '';
    document.getElementById('fCapacidad').value = c.capacidad_maxima || 20;
    document.getElementById('fHoraInicio').value= c.hora_inicio || '';
    document.getElementById('fHoraFin').value   = c.hora_fin || '';
    document.getElementById('fNivel').value     = c.nivel || 'basico';
    document.getElementById('fDescripcion').value = c.descripcion || '';
    const dias = c.dias_semana ? c.dias_semana.split(',').map(d => d.trim()) : [];
    document.querySelectorAll('input[name="dias"]').forEach(cb => cb.checked = dias.includes(cb.value));
    document.getElementById('modalClase').classList.add('open');
}

function cerrarModal() { document.getElementById('modalClase').classList.remove('open'); }

async function guardarClase() {
    const id = document.getElementById('claseId').value;
    const nombre = document.getElementById('fNombre').value.trim();
    if (!nombre) { showToast('El nombre es requerido', true); return; }
    const dias = Array.from(document.querySelectorAll('input[name="dias"]:checked')).map(cb => cb.value).join(',');
    const body = {
        nombre,
        instructor:      document.getElementById('fInstructor').value.trim(),
        capacidad_maxima:parseInt(document.getElementById('fCapacidad').value) || 20,
        hora_inicio:     document.getElementById('fHoraInicio').value,
        hora_fin:        document.getElementById('fHoraFin').value,
        nivel:           document.getElementById('fNivel').value,
        descripcion:     document.getElementById('fDescripcion').value.trim(),
        dias_semana:     dias,
    };
    if (id) body.id = id;
    try {
        const r = await fetch('../../api/gym/clases.php', {method: id?'PUT':'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
        const d = await r.json();
        if (d.success) { showToast(id?'Clase actualizada':'Clase creada'); cerrarModal(); cargarClases(); }
        else showToast(d.message||'Error al guardar', true);
    } catch(e) { showToast('Error de conexión', true); }
}

async function eliminar(id, nombre) {
    if (!confirm(`¿Eliminar la clase "${nombre}"?`)) return;
    try {
        const r = await fetch('../../api/gym/clases.php', {method:'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id})});
        const d = await r.json();
        if (d.success) { showToast('Clase eliminada'); cargarClases(); }
        else showToast(d.message||'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

function showToast(msg, error = false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast show' + (error ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalClase').addEventListener('click', e => { if (e.target === document.getElementById('modalClase')) cerrarModal(); });

cargarClases();
</script>
</body>
</html>
