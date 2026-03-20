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
    <title>Canchas - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .deporte-chip { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(22,163,74,.12);color:#16a34a; }
        .badge-activo   { background:rgba(72,187,120,.15);color:#22c55e;display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-inactivo { background:rgba(160,174,192,.15);color:#94a3b8;display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .cancha-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;transition:var(--transition);display:flex;flex-direction:column;gap:14px; }
        .cancha-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.1);transform:translateY(-2px); }
        .cancha-card-header { display:flex;justify-content:space-between;align-items:flex-start; }
        .cancha-card h4 { margin:0;font-size:17px;font-weight:700;color:var(--text-primary); }
        .cancha-card-meta { display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--text-secondary); }
        .cancha-card-meta span { display:flex;align-items:center;gap:8px; }
        .cancha-card-meta i { width:14px;color:var(--primary);flex-shrink:0; }
        .cancha-card-footer { display:flex;gap:8px;padding-top:14px;border-top:1px solid var(--border); }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface);border-radius:16px;width:100%;max-width:500px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);border:1px solid var(--border); }
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
        body.dark-mode .cancha-card { border-color:rgba(255,255,255,.08); }
        body.dark-mode .cancha-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.3); }
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
                        <i class="fas fa-futbol"></i>
                    </div>
                    <div>
                        <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">Canchas</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Gestión de canchas disponibles</p>
                    </div>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="reservas.php" class="btn btn-secondary"><i class="fas fa-calendar-check"></i> Ver Reservas</a>
                    <button class="btn btn-primary" onclick="abrirModalNuevo()"><i class="fas fa-plus"></i> Nueva Cancha</button>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px;margin-bottom:24px;">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-futbol"></i></div>
                    <div class="stat-info"><p class="stat-label">Total canchas</p><h3 class="stat-value" id="statTotal">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info"><p class="stat-label">Activas</p><h3 class="stat-value" id="statActivas">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info"><p class="stat-label">Precio prom/hora</p><h3 class="stat-value" id="statPrecio">—</h3></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3><i class="fas fa-list"></i> Canchas registradas</h3></div>
                <div class="card-body">
                    <div id="loadingState" style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    <div id="canchasGrid" style="display:none;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;"></div>
                    <div id="emptyState" class="empty-state" style="display:none;"><i class="fas fa-futbol"></i><p>No hay canchas registradas aún</p></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalCancha">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nueva Cancha</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="canchaId">
            <div class="form-2col">
                <div class="form-group full">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="fNombre" placeholder="Ej: Cancha 1, Cancha Norte...">
                </div>
                <div class="form-group">
                    <label class="form-label">Deporte</label>
                    <select class="form-control" id="fDeporte">
                        <option value="">General</option>
                        <option value="Fútbol 5">Fútbol 5</option>
                        <option value="Fútbol 7">Fútbol 7</option>
                        <option value="Fútbol 11">Fútbol 11</option>
                        <option value="Pádel">Pádel</option>
                        <option value="Tenis">Tenis</option>
                        <option value="Vóley">Vóley</option>
                        <option value="Básquet">Básquet</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Precio por hora</label>
                    <input type="number" class="form-control" id="fPrecio" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Capacidad (jugadores)</label>
                    <input type="number" class="form-control" id="fCapacidad" min="0" placeholder="Ej: 10">
                </div>
                <div class="form-group full">
                    <label class="form-label">Descripción / Características</label>
                    <textarea class="form-control" id="fDescripcion" rows="2" style="resize:vertical;" placeholder="Pasto sintético, iluminación, vestuarios..."></textarea>
                </div>
                <div class="form-group full" id="activoGroup" style="display:none;">
                    <label class="form-label">Estado</label>
                    <select class="form-control" id="fActivo">
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>
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
let canchas = [];

async function cargar() {
    try {
        const r = await fetch('../../api/canchas/canchas.php', {credentials: 'include'});
        const d = await r.json();
        document.getElementById('loadingState').style.display = 'none';
        if (d.success) {
            canchas = d.data;
            const activas = canchas.filter(c => c.activo == 1);
            document.getElementById('statTotal').textContent   = canchas.length;
            document.getElementById('statActivas').textContent = activas.length;
            const precios = activas.filter(c => parseFloat(c.precio_hora) > 0).map(c => parseFloat(c.precio_hora));
            document.getElementById('statPrecio').textContent = precios.length
                ? '$' + Math.round(precios.reduce((a,b)=>a+b,0)/precios.length).toLocaleString('es-AR')
                : '—';
            renderGrid();
        }
    } catch(e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    }
}

const deporteColor = {
    'Fútbol 5':'#16a34a','Fútbol 7':'#15803d','Fútbol 11':'#14532d',
    'Pádel':'#0ea5e9','Tenis':'#f59e0b','Vóley':'#8b5cf6','Básquet':'#ef4444'
};

function renderGrid() {
    const grid = document.getElementById('canchasGrid');
    if (!canchas.length) { document.getElementById('emptyState').style.display = 'block'; return; }
    grid.style.display = 'grid';
    grid.innerHTML = canchas.map(c => {
        const col = deporteColor[c.deporte] || '#16a34a';
        return `<div class="cancha-card">
            <div class="cancha-card-header">
                <div>
                    <h4>${c.nombre}</h4>
                    ${c.deporte ? `<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:${col}20;color:${col};margin-top:6px;">${c.deporte}</span>` : ''}
                </div>
                <span class="${c.activo == 1 ? 'badge-activo' : 'badge-inactivo'}">${c.activo == 1 ? '● Activa' : '● Inactiva'}</span>
            </div>
            <div class="cancha-card-meta">
                ${c.precio_hora > 0 ? `<span><i class="fas fa-dollar-sign"></i>$${parseFloat(c.precio_hora).toLocaleString('es-AR')} / hora</span>` : ''}
                ${c.capacidad > 0  ? `<span><i class="fas fa-users"></i>${c.capacidad} jugadores</span>` : ''}
                ${c.descripcion    ? `<span><i class="fas fa-info-circle"></i>${c.descripcion}</span>` : ''}
            </div>
            <div class="cancha-card-footer">
                <a href="reservas.php?cancha_id=${c.id}" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center;">
                    <i class="fas fa-calendar-check"></i> Reservas
                </a>
                <button class="btn btn-secondary btn-sm btn-icon" onclick="abrirEditar(${c.id})" title="Editar"><i class="fas fa-pen"></i></button>
                <button class="btn btn-danger btn-sm btn-icon" onclick="eliminar(${c.id},'${c.nombre}')" title="Eliminar"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

function abrirModalNuevo() {
    document.getElementById('modalTitle').textContent = 'Nueva Cancha';
    document.getElementById('canchaId').value = '';
    ['fNombre','fDescripcion'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fDeporte').value  = '';
    document.getElementById('fPrecio').value   = '';
    document.getElementById('fCapacidad').value= '';
    document.getElementById('activoGroup').style.display = 'none';
    document.getElementById('modalCancha').classList.add('open');
}

function abrirEditar(id) {
    const c = canchas.find(x => x.id == id); if (!c) return;
    document.getElementById('modalTitle').textContent = 'Editar Cancha';
    document.getElementById('canchaId').value    = c.id;
    document.getElementById('fNombre').value     = c.nombre;
    document.getElementById('fDeporte').value    = c.deporte || '';
    document.getElementById('fPrecio').value     = c.precio_hora || '';
    document.getElementById('fCapacidad').value  = c.capacidad || '';
    document.getElementById('fDescripcion').value= c.descripcion || '';
    document.getElementById('fActivo').value     = c.activo;
    document.getElementById('activoGroup').style.display = 'block';
    document.getElementById('modalCancha').classList.add('open');
}

function cerrarModal() { document.getElementById('modalCancha').classList.remove('open'); }

async function guardar() {
    const id     = document.getElementById('canchaId').value;
    const nombre = document.getElementById('fNombre').value.trim();
    if (!nombre) { showToast('El nombre es requerido', true); return; }
    const body = {
        nombre,
        deporte:     document.getElementById('fDeporte').value,
        precio_hora: parseFloat(document.getElementById('fPrecio').value) || 0,
        capacidad:   parseInt(document.getElementById('fCapacidad').value) || 0,
        descripcion: document.getElementById('fDescripcion').value.trim(),
    };
    if (id) { body.id = id; body.activo = parseInt(document.getElementById('fActivo').value); }
    try {
        const r = await fetch('../../api/canchas/canchas.php', {method: id?'PUT':'POST', credentials: 'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
        const d = await r.json();
        if (d.success) { showToast(id?'Cancha actualizada':'Cancha creada'); cerrarModal(); cargar(); }
        else showToast(d.message||'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

async function eliminar(id, nombre) {
    if (!confirm(`¿Eliminar la cancha "${nombre}"?`)) return;
    try {
        const r = await fetch('../../api/canchas/canchas.php', {method:'DELETE', credentials: 'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id})});
        const d = await r.json();
        if (d.success) { showToast('Cancha eliminada'); cargar(); }
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
document.getElementById('modalCancha').addEventListener('click', e => { if (e.target===document.getElementById('modalCancha')) cerrarModal(); });

cargar();
</script>
</body>
</html>
