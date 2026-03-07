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
    <title>Recetas — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        :root { --farm: #10b981; }
        .app-layout { display:flex; min-height:100vh; }

        /* Stats */
        .stat-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        @media(max-width:700px){ .stat-strip { grid-template-columns:repeat(2,1fr); } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .stat-val  { font-size:22px; font-weight:800; line-height:1; }
        .stat-lbl  { font-size:12px; color:var(--text-secondary); margin-top:3px; }

        /* Receta cards */
        .receta-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:14px; padding:20px; }
        .receta-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px; display:flex; flex-direction:column; gap:10px; transition:.15s; }
        .receta-card:hover { border-color:var(--farm); box-shadow:0 4px 16px rgba(16,185,129,.12); }
        .receta-head { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
        .receta-num  { font-size:11px; font-weight:700; color:var(--farm); text-transform:uppercase; letter-spacing:.5px; }
        .receta-pac  { font-size:15px; font-weight:700; color:var(--text-primary); }
        .receta-med  { font-size:13px; color:var(--text-secondary); }
        .receta-meta { display:flex; flex-wrap:wrap; gap:6px; }
        .meta-chip   { font-size:11px; background:var(--background); border:1px solid var(--border); border-radius:6px; padding:2px 8px; color:var(--text-secondary); }
        .receta-items { font-size:12px; color:var(--text-secondary); border-top:1px solid var(--border); padding-top:8px; }
        .receta-items li { margin-bottom:2px; }
        .receta-actions { display:flex; gap:8px; margin-top:4px; }

        /* Estado badges */
        .est-pendiente  { background:#fef3c7; color:#d97706; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .est-despachada { background:#d1fae5; color:#059669; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .est-vencida    { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .est-anulada    { background:#f3f4f6; color:#6b7280; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

        /* Tabs */
        .tab-bar { display:flex; gap:6px; padding:0 20px 0; border-bottom:1px solid var(--border); }
        .tab-btn { padding:10px 16px; font-size:13px; font-weight:600; border:none; background:none; cursor:pointer; color:var(--text-secondary); border-bottom:2px solid transparent; margin-bottom:-1px; transition:.15s; }
        .tab-btn.active { color:var(--farm); border-bottom-color:var(--farm); }

        /* Toolbar */
        .toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:16px 20px 0; }
        .search-box { flex:1; min-width:200px; position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input { width:100%; padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .search-box input:focus { outline:none; border-color:var(--farm); }

        .empty-state { text-align:center; padding:50px 20px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:10000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:var(--surface); border-radius:16px; width:700px; max-width:100%; max-height:90vh; display:flex; flex-direction:column; }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-body   { padding:20px 24px; overflow-y:auto; flex:1; }
        .modal-footer { padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; }
        .form-row { display:grid; gap:14px; margin-bottom:14px; }
        .form-row.cols2 { grid-template-columns:1fr 1fr; }
        .form-row.cols3 { grid-template-columns:1fr 1fr 1fr; }
        @media(max-width:540px){ .form-row.cols2, .form-row.cols3 { grid-template-columns:1fr; } }
        .form-group label { display:block; font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:5px; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px;
            font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:var(--farm); }
        .section-title { font-size:13px; font-weight:700; color:var(--text-primary); margin:16px 0 10px; padding-bottom:6px; border-bottom:1px solid var(--border); }

        /* Items tabla */
        .items-table { width:100%; border-collapse:collapse; }
        .items-table th { font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-secondary); padding:6px 8px; border-bottom:1px solid var(--border); text-align:left; }
        .items-table td { padding:6px 8px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .items-table tr:last-child td { border-bottom:none; }
        .btn-rm { background:none; border:none; color:#dc2626; cursor:pointer; padding:4px; font-size:13px; }
        .btn-rm:hover { color:#b91c1c; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Header -->
            <div class="card" style="margin-bottom:20px;padding:18px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-prescription" style="color:var(--farm);margin-right:8px;"></i>Recetas Médicas
                        </h1>
                        <p style="margin:4px 0 0;font-size:14px;color:var(--text-secondary);">Gestión y despacho de recetas</p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="vencimientos.php" class="btn btn-secondary" style="font-size:13px;"><i class="fas fa-calendar-times"></i> Vencimientos</a>
                        <button onclick="abrirModal()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);">
                            <i class="fas fa-plus"></i> Nueva Receta
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-strip">
                <div class="stat-card"><div class="stat-icon" style="background:#fef3c720;color:#d97706;"><i class="fas fa-clock"></i></div><div><div class="stat-val" id="stPend">—</div><div class="stat-lbl">Pendientes</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#d1fae520;color:#059669;"><i class="fas fa-check-double"></i></div><div><div class="stat-val" id="stDesp">—</div><div class="stat-lbl">Despachadas hoy</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fee2e220;color:#dc2626;"><i class="fas fa-calendar-times"></i></div><div><div class="stat-val" id="stVenc">—</div><div class="stat-lbl">Vencidas</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,.1);color:var(--farm);"><i class="fas fa-prescription-bottle-alt"></i></div><div><div class="stat-val" id="stTotal">—</div><div class="stat-lbl">Total del mes</div></div></div>
            </div>

            <!-- Card con tabs -->
            <div class="card" style="padding:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px 0;flex-wrap:wrap;gap:10px;">
                    <div class="tab-bar" style="padding:0;border-bottom:none;">
                        <button class="tab-btn active" data-tab="todas"       onclick="cambiarTab(this,'todas')">Todas</button>
                        <button class="tab-btn"         data-tab="pendiente"  onclick="cambiarTab(this,'pendiente')">Pendientes</button>
                        <button class="tab-btn"         data-tab="despachada" onclick="cambiarTab(this,'despachada')">Despachadas</button>
                        <button class="tab-btn"         data-tab="vencida"    onclick="cambiarTab(this,'vencida')">Vencidas</button>
                    </div>
                </div>
                <div class="toolbar">
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchRec" placeholder="Buscar paciente, médico, nro. receta…" oninput="filtrar()"></div>
                </div>
                <div class="receta-grid" id="recetaGrid">
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal nueva/editar receta -->
<div id="modalReceta" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--text-primary);" id="modalTitle">
                <i class="fas fa-prescription" style="color:var(--farm);margin-right:8px;"></i>Nueva Receta
            </h2>
            <button onclick="cerrarModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body">

            <div class="section-title"><i class="fas fa-file-medical"></i> Datos de la receta</div>
            <div class="form-row cols3">
                <div class="form-group"><label>Nº Receta</label><input type="text" id="fNumero" placeholder="Ej: R-001234"></div>
                <div class="form-group"><label>Fecha emisión</label><input type="date" id="fEmision"></div>
                <div class="form-group"><label>Fecha vencimiento</label><input type="date" id="fVencimiento"></div>
            </div>

            <div class="section-title"><i class="fas fa-user-md"></i> Médico</div>
            <div class="form-row cols2">
                <div class="form-group"><label>Nombre del médico</label><input type="text" id="fMedico" placeholder="Dr/Dra…"></div>
                <div class="form-group"><label>Matrícula</label><input type="text" id="fMatricula" placeholder="MP / MN"></div>
            </div>

            <div class="section-title"><i class="fas fa-user"></i> Paciente</div>
            <div class="form-row cols2">
                <div class="form-group"><label>Nombre del paciente</label><input type="text" id="fPaciente" placeholder="Apellido, Nombre"></div>
                <div class="form-group"><label>DNI</label><input type="text" id="fDni" placeholder="Sin puntos"></div>
            </div>
            <div class="form-row cols2">
                <div class="form-group"><label>Obra social / prepaga</label><input type="text" id="fObraSocial" placeholder="PAMI, OSDE, Galeno…"></div>
                <div class="form-group"><label>Nº afiliado</label><input type="text" id="fAfiliado"></div>
            </div>

            <div class="section-title"><i class="fas fa-pills"></i> Medicamentos</div>
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th>Medicamento / Producto</th>
                        <th style="width:110px;">Presentación</th>
                        <th style="width:70px;">Cant.</th>
                        <th style="width:180px;">Indicaciones</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsTbody"></tbody>
            </table>
            <button onclick="agregarItem()" style="margin-top:10px;background:none;border:1px dashed var(--border);border-radius:8px;padding:7px 16px;cursor:pointer;font-size:13px;color:var(--text-secondary);width:100%;transition:.15s;" onmouseover="this.style.borderColor='var(--farm)'" onmouseout="this.style.borderColor='var(--border)'">
                <i class="fas fa-plus"></i> Agregar medicamento
            </button>

            <div class="section-title"><i class="fas fa-sticky-note"></i> Estado y notas</div>
            <div class="form-row cols2">
                <div class="form-group">
                    <label>Estado</label>
                    <select id="fEstado">
                        <option value="pendiente">Pendiente</option>
                        <option value="despachada">Despachada</option>
                        <option value="vencida">Vencida</option>
                        <option value="anulada">Anulada</option>
                    </select>
                </div>
                <div class="form-group"><label>Notas internas</label><input type="text" id="fNotas" placeholder="Observaciones…"></div>
            </div>

        </div>
        <div class="modal-footer">
            <button onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
            <button onclick="guardar()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);" id="btnGuardar">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- Modal detalle -->
<div id="modalDetalle" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--text-primary);" id="detTitle">Detalle de Receta</h2>
            <button onclick="cerrarDetalle()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body" id="detBody"></div>
        <div class="modal-footer" id="detFooter"></div>
    </div>
</div>

<script>
const API = '../../api/farmacia/recetas.php';
const API_PROD = '../../api/productos/index.php';
let tabActual = 'todas';
let todos = [];
let editingId = null;
let productos = [];
let itemCount = 0;

async function init() {
    const rp = await fetch(API_PROD, {credentials:'include'});
    const jp = await rp.json();
    productos = jp.success ? (jp.data?.productos ?? []) : [];
    await cargar();
}

async function cargar() {
    const est = tabActual !== 'todas' ? '&estado=' + tabActual : '';
    const r = await fetch(API + '?limit=200' + est, {credentials:'include'});
    const j = await r.json();
    todos = j.success ? j.data : [];
    actualizarStats();
    filtrar();
}

function actualizarStats() {
    document.getElementById('stPend').textContent  = todos.filter(r=>r.estado==='pendiente').length;
    document.getElementById('stVenc').textContent  = todos.filter(r=>r.estado==='vencida').length;
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('stDesp').textContent  = todos.filter(r=>r.estado==='despachada' && r.updated_at?.startsWith(hoy)).length;
    document.getElementById('stTotal').textContent = todos.length;
}

function cambiarTab(btn, tab) {
    tabActual = tab;
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    cargar();
}

function filtrar() {
    const q = document.getElementById('searchRec').value.toLowerCase().trim();
    const lista = q ? todos.filter(r =>
        (r.paciente||'').toLowerCase().includes(q) ||
        (r.medico||'').toLowerCase().includes(q) ||
        (r.numero_receta||'').toLowerCase().includes(q)
    ) : todos;
    renderGrid(lista);
}

function renderGrid(lista) {
    const g = document.getElementById('recetaGrid');
    if (!lista.length) {
        g.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-prescription"></i><p>No hay recetas en esta categoría</p></div>';
        return;
    }
    g.innerHTML = lista.map(r => {
        const est = r.estado || 'pendiente';
        return `<div class="receta-card">
            <div class="receta-head">
                <div>
                    <div class="receta-num">${r.numero_receta ? 'Receta #' + esc(r.numero_receta) : 'Sin número'}</div>
                    <div class="receta-pac">${esc(r.paciente||'Paciente sin nombre')}</div>
                    <div class="receta-med"><i class="fas fa-user-md" style="font-size:11px;margin-right:4px;"></i>${esc(r.medico||'—')}</div>
                </div>
                <span class="est-${est}">${esc(est.charAt(0).toUpperCase()+est.slice(1))}</span>
            </div>
            <div class="receta-meta">
                ${r.obra_social ? `<span class="meta-chip"><i class="fas fa-hospital"></i> ${esc(r.obra_social)}</span>` : ''}
                ${r.fecha_emision ? `<span class="meta-chip"><i class="fas fa-calendar"></i> ${fmtFecha(r.fecha_emision)}</span>` : ''}
                ${r.fecha_vencimiento ? `<span class="meta-chip"><i class="fas fa-clock"></i> Vence: ${fmtFecha(r.fecha_vencimiento)}</span>` : ''}
            </div>
            <div class="receta-actions">
                <button onclick="verDetalle(${r.id})" class="btn btn-secondary" style="flex:1;font-size:12px;padding:6px 10px;">
                    <i class="fas fa-eye"></i> Ver
                </button>
                ${est === 'pendiente' ? `
                <button onclick="despachar(${r.id})" class="btn btn-primary" style="flex:1;font-size:12px;padding:6px 10px;background:var(--farm);border-color:var(--farm);">
                    <i class="fas fa-check"></i> Despachar
                </button>` : ''}
                <button onclick="abrirModal(${r.id})" class="btn btn-secondary" style="font-size:12px;padding:6px 10px;">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

// ── Modal nueva/editar ────────────────────────────────────────────────────────
function abrirModal(id = null) {
    editingId = id;
    itemCount = 0;
    document.getElementById('itemsTbody').innerHTML = '';
    document.getElementById('modalTitle').innerHTML = `<i class="fas fa-prescription" style="color:var(--farm);margin-right:8px;"></i>${id ? 'Editar Receta' : 'Nueva Receta'}`;
    if (!id) {
        ['fNumero','fMedico','fMatricula','fPaciente','fDni','fObraSocial','fAfiliado','fNotas'].forEach(x => document.getElementById(x).value='');
        document.getElementById('fEmision').value = new Date().toISOString().split('T')[0];
        document.getElementById('fVencimiento').value = '';
        document.getElementById('fEstado').value = 'pendiente';
        agregarItem();
    } else {
        cargarEditar(id);
    }
    document.getElementById('modalReceta').classList.add('show');
}

async function cargarEditar(id) {
    const r = await fetch(API + '?id=' + id, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    const d = j.data;
    document.getElementById('fNumero').value     = d.numero_receta || '';
    document.getElementById('fEmision').value    = d.fecha_emision || '';
    document.getElementById('fVencimiento').value= d.fecha_vencimiento || '';
    document.getElementById('fMedico').value     = d.medico || '';
    document.getElementById('fMatricula').value  = d.matricula || '';
    document.getElementById('fPaciente').value   = d.paciente || '';
    document.getElementById('fDni').value        = d.dni_paciente || '';
    document.getElementById('fObraSocial').value = d.obra_social || '';
    document.getElementById('fAfiliado').value   = d.nro_afiliado || '';
    document.getElementById('fEstado').value     = d.estado || 'pendiente';
    document.getElementById('fNotas').value      = d.notas || '';
    (d.items || []).forEach(it => agregarItem(it));
}

function cerrarModal() { document.getElementById('modalReceta').classList.remove('show'); editingId=null; }

function agregarItem(it = null) {
    const idx = itemCount++;
    const opts = productos.map(p => `<option value="${p.id}" ${it && it.producto_id == p.id ? 'selected' : ''}>${esc(p.nombre)}</option>`).join('');
    const tr = document.createElement('tr');
    tr.id = 'item-row-' + idx;
    tr.innerHTML = `
        <td>
            <input type="text" id="it_med_${idx}" placeholder="Ej: Ibuprofeno 600mg" value="${esc(it?.medicamento||'')}" style="width:100%;padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;background:var(--background);color:var(--text-primary);">
        </td>
        <td><input type="text" id="it_pres_${idx}" placeholder="30 comp." value="${esc(it?.presentacion||'')}" style="width:100%;padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;background:var(--background);color:var(--text-primary);"></td>
        <td><input type="number" id="it_cant_${idx}" value="${it?.cantidad||1}" min="1" style="width:100%;padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;background:var(--background);color:var(--text-primary);"></td>
        <td><input type="text" id="it_ind_${idx}"  placeholder="1 comp. cada 8hs" value="${esc(it?.indicaciones||'')}" style="width:100%;padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;background:var(--background);color:var(--text-primary);"></td>
        <td><button class="btn-rm" onclick="quitarItem(${idx})"><i class="fas fa-times"></i></button></td>
    `;
    document.getElementById('itemsTbody').appendChild(tr);
}

function quitarItem(idx) {
    const row = document.getElementById('item-row-' + idx);
    if (row) row.remove();
}

async function guardar() {
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    const items = [];
    document.querySelectorAll('#itemsTbody tr').forEach(tr => {
        const id = tr.id.replace('item-row-','');
        const med = document.getElementById('it_med_'+id)?.value.trim();
        if (!med) return;
        items.push({
            medicamento: med,
            presentacion: document.getElementById('it_pres_'+id)?.value.trim()||null,
            cantidad: parseInt(document.getElementById('it_cant_'+id)?.value)||1,
            indicaciones: document.getElementById('it_ind_'+id)?.value.trim()||null,
        });
    });
    const body = {
        numero_receta: document.getElementById('fNumero').value.trim()||null,
        medico: document.getElementById('fMedico').value.trim()||null,
        matricula: document.getElementById('fMatricula').value.trim()||null,
        paciente: document.getElementById('fPaciente').value.trim()||null,
        dni_paciente: document.getElementById('fDni').value.trim()||null,
        obra_social: document.getElementById('fObraSocial').value.trim()||null,
        nro_afiliado: document.getElementById('fAfiliado').value.trim()||null,
        fecha_emision: document.getElementById('fEmision').value||null,
        fecha_vencimiento: document.getElementById('fVencimiento').value||null,
        estado: document.getElementById('fEstado').value,
        notas: document.getElementById('fNotas').value.trim()||null,
        items,
    };
    if (editingId) body.id = editingId;
    const r = await fetch(API, {
        method: editingId ? 'PUT' : 'POST', credentials:'include',
        headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)
    });
    const j = await r.json();
    btn.disabled = false;
    if (j.success) { cerrarModal(); cargar(); } else { alert('Error: ' + j.message); }
}

async function despachar(id) {
    if (!confirm('¿Marcar receta como despachada?')) return;
    const r = await fetch(API, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id, estado:'despachada'})
    });
    const j = await r.json();
    if (j.success) cargar();
}

async function verDetalle(id) {
    const r = await fetch(API + '?id=' + id, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    const d = j.data;
    const est = d.estado || 'pendiente';
    document.getElementById('detTitle').innerHTML = `Receta ${d.numero_receta ? '#'+esc(d.numero_receta) : ''} &nbsp;<span class="est-${est}">${est.charAt(0).toUpperCase()+est.slice(1)}</span>`;
    document.getElementById('detBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div><div style="font-size:11px;color:var(--text-secondary);font-weight:700;margin-bottom:4px;">PACIENTE</div><div style="font-weight:700;">${esc(d.paciente||'—')}</div><div style="font-size:12px;color:var(--text-secondary);">DNI: ${esc(d.dni_paciente||'—')}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);font-weight:700;margin-bottom:4px;">MÉDICO</div><div style="font-weight:700;">${esc(d.medico||'—')}</div><div style="font-size:12px;color:var(--text-secondary);">Mat: ${esc(d.matricula||'—')}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);font-weight:700;margin-bottom:4px;">OBRA SOCIAL</div><div style="font-weight:600;">${esc(d.obra_social||'Particular')}</div><div style="font-size:12px;color:var(--text-secondary);">Afiliado: ${esc(d.nro_afiliado||'—')}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);font-weight:700;margin-bottom:4px;">FECHAS</div><div style="font-size:12px;">Emisión: <strong>${fmtFecha(d.fecha_emision)}</strong></div><div style="font-size:12px;">Vence: <strong>${fmtFecha(d.fecha_vencimiento)}</strong></div></div>
        </div>
        <div style="font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid var(--border);">
            <i class="fas fa-pills" style="color:var(--farm);margin-right:6px;"></i>Medicamentos (${(d.items||[]).length})
        </div>
        ${(d.items||[]).map(it=>`
        <div style="padding:10px 12px;background:var(--background);border-radius:8px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-weight:700;font-size:13px;">${esc(it.medicamento)}</div>
                <div style="font-size:12px;color:var(--text-secondary);">${esc(it.presentacion||'')} ${it.indicaciones ? '· '+esc(it.indicaciones) : ''}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700;font-size:14px;">${it.cantidad}x</div>
                ${it.dispensado ? '<span style="font-size:11px;background:#d1fae5;color:#059669;padding:2px 8px;border-radius:10px;">Dispensado</span>' : '<span style="font-size:11px;background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:10px;">Pendiente</span>'}
            </div>
        </div>`).join('')}
        ${d.notas ? `<div style="margin-top:12px;padding:10px 14px;background:var(--background);border-radius:8px;font-size:13px;color:var(--text-secondary);"><i class="fas fa-sticky-note" style="margin-right:6px;"></i>${esc(d.notas)}</div>` : ''}
    `;
    document.getElementById('detFooter').innerHTML = `
        <button onclick="cerrarDetalle()" class="btn btn-secondary">Cerrar</button>
        ${est === 'pendiente' ? `<button onclick="cerrarDetalle();despachar(${d.id})" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);"><i class="fas fa-check"></i> Despachar</button>` : ''}
        <button onclick="cerrarDetalle();abrirModal(${d.id})" class="btn btn-secondary"><i class="fas fa-edit"></i> Editar</button>
    `;
    document.getElementById('modalDetalle').classList.add('show');
}
function cerrarDetalle() { document.getElementById('modalDetalle').classList.remove('show'); }

function fmtFecha(f) { if(!f||f==='—') return '—'; const p=f.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.getElementById('modalReceta').addEventListener('click',e=>{if(e.target===e.currentTarget)cerrarModal();});
document.getElementById('modalDetalle').addEventListener('click',e=>{if(e.target===e.currentTarget)cerrarDetalle();});

init();
</script>
</body>
</html>
