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
    <title>Clientes — Farmacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        :root { --farm: #10b981; }
        .app-layout { display:flex; min-height:100vh; }
        .stat-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        @media(max-width:700px){ .stat-strip { grid-template-columns:repeat(2,1fr); } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .stat-val  { font-size:22px; font-weight:800; line-height:1; }
        .stat-lbl  { font-size:12px; color:var(--text-secondary); margin-top:3px; }

        .toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:16px 20px 0; }
        .search-box { flex:1; min-width:220px; position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input { width:100%; padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .search-box input:focus { outline:none; border-color:var(--farm); }

        table { width:100%; border-collapse:collapse; }
        th { padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-secondary); text-align:left; border-bottom:1px solid var(--border); background:var(--background); white-space:nowrap; }
        td { padding:12px 14px; font-size:13px; color:var(--text-primary); border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:hover td { background:var(--hover,#f8fafc); }
        .table-wrap { padding:0 20px 20px; overflow-x:auto; }

        .obra-badge { display:inline-block; font-size:11px; font-weight:700; padding:2px 9px; border-radius:20px; background:rgba(16,185,129,.1); color:var(--farm); }
        .empty-state { text-align:center; padding:50px 20px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:10000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:var(--surface); border-radius:16px; width:540px; max-width:100%; max-height:90vh; display:flex; flex-direction:column; }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-body   { padding:20px 24px; overflow-y:auto; flex:1; }
        .modal-footer { padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; }
        .form-row { display:grid; gap:14px; margin-bottom:14px; }
        .form-row.cols2 { grid-template-columns:1fr 1fr; }
        .form-row.cols3 { grid-template-columns:1fr 1fr 1fr; }
        @media(max-width:480px){ .form-row.cols2,.form-row.cols3 { grid-template-columns:1fr; } }
        .form-group label { display:block; font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:5px; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus { outline:none; border-color:var(--farm); }
        .section-title { font-size:13px; font-weight:700; color:var(--text-primary); margin:14px 0 10px; padding-bottom:6px; border-bottom:1px solid var(--border); }
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
                            <i class="fas fa-users" style="color:var(--farm);margin-right:8px;"></i>Clientes
                        </h1>
                        <p style="margin:4px 0 0;font-size:14px;color:var(--text-secondary);">Clientes habituales, datos de obra social y frecuencia</p>
                    </div>
                    <button onclick="abrirModal()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-strip">
                <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,.1);color:var(--farm);"><i class="fas fa-users"></i></div><div><div class="stat-val" id="stTotal">—</div><div class="stat-lbl">Total clientes</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-hospital"></i></div><div><div class="stat-val" id="stObraSocial">—</div><div class="stat-lbl">Con obra social</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-calendar-alt"></i></div><div><div class="stat-val" id="stMes">—</div><div class="stat-lbl">Nuevos este mes</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#f3e8ff;color:#8b5cf6;"><i class="fas fa-pills"></i></div><div><div class="stat-val" id="stRecetas">—</div><div class="stat-lbl">Con recetas activas</div></div></div>
            </div>

            <!-- Tabla -->
            <div class="card" style="padding:0;">
                <div class="toolbar">
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchCli" placeholder="Buscar por nombre, DNI, obra social…" oninput="filtrar()"></div>
                    <select id="filObraSocial" onchange="filtrar()" class="btn btn-secondary" style="font-size:13px;padding:8px 12px;">
                        <option value="">Todas las obras sociales</option>
                    </select>
                </div>
                <div class="table-wrap" style="margin-top:16px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th>Obra Social</th>
                                <th>Nº Afiliado</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCli"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal nuevo/editar cliente -->
<div id="modalCliente" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--text-primary);" id="modalTitle">
                <i class="fas fa-user-plus" style="color:var(--farm);margin-right:8px;"></i>Nuevo Cliente
            </h2>
            <button onclick="cerrarModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body">
            <div class="section-title"><i class="fas fa-user"></i> Datos personales</div>
            <div class="form-row cols2">
                <div class="form-group"><label>Nombre <span style="color:#dc2626;">*</span></label><input type="text" id="fNombre" placeholder="Nombre y apellido"></div>
                <div class="form-group"><label>DNI</label><input type="text" id="fDni" placeholder="Sin puntos"></div>
            </div>
            <div class="form-row cols2">
                <div class="form-group"><label>Teléfono</label><input type="text" id="fTelefono" placeholder="+54 11 …"></div>
                <div class="form-group"><label>Email</label><input type="email" id="fEmail" placeholder="correo@email.com"></div>
            </div>
            <div class="form-group" style="margin-bottom:14px;"><label>Dirección</label><input type="text" id="fDireccion" placeholder="Calle, número, ciudad"></div>
            <div class="form-group" style="margin-bottom:14px;"><label>Fecha de nacimiento</label><input type="date" id="fNacimiento"></div>

            <div class="section-title"><i class="fas fa-hospital"></i> Cobertura médica</div>
            <div class="form-row cols2">
                <div class="form-group"><label>Obra social / Prepaga</label><input type="text" id="fObraSocial" placeholder="PAMI, OSDE, Galeno…" list="obrasSocialesList"></div>
                <div class="form-group"><label>Nº Afiliado</label><input type="text" id="fAfiliado" placeholder="Número de afiliado"></div>
            </div>
            <datalist id="obrasSocialesList">
                <option value="PAMI"><option value="OSDE"><option value="Galeno"><option value="Swiss Medical">
                <option value="Medicus"><option value="IOMA"><option value="APROSS"><option value="OSECAC">
                <option value="Particular">
            </datalist>

            <div class="section-title"><i class="fas fa-sticky-note"></i> Notas</div>
            <div class="form-group"><textarea id="fNotas" rows="3" placeholder="Alergias, medicamentos crónicos, observaciones…"></textarea></div>
        </div>
        <div class="modal-footer">
            <button onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
            <button onclick="guardar()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);" id="btnGuardar">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script>
const API = '../../api/clientes/index.php';
let todos = [];
let editingId = null;

async function cargar() {
    const r = await fetch(API, {credentials:'include'});
    const j = await r.json();
    todos = j.success ? (j.data?.clientes ?? j.data ?? []) : [];
    actualizarStats();
    poblarFiltroObraSocial();
    filtrar();
}

function actualizarStats() {
    document.getElementById('stTotal').textContent = todos.length;
    document.getElementById('stObraSocial').textContent = todos.filter(c=>c.obra_social||c.cobertura).length;
    const hoy = new Date();
    const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('stMes').textContent = todos.filter(c=>(c.fecha_creacion||c.created_at||'') >= primerDiaMes).length;
    document.getElementById('stRecetas').textContent = '—';
}

function poblarFiltroObraSocial() {
    const sel = document.getElementById('filObraSocial');
    const actual = sel.value;
    const obras = [...new Set(todos.map(c=>c.obra_social||c.cobertura).filter(Boolean))].sort();
    sel.innerHTML = '<option value="">Todas las obras sociales</option>' + obras.map(o=>`<option value="${esc(o)}" ${actual===o?'selected':''}>${esc(o)}</option>`).join('');
}

function filtrar() {
    const q   = document.getElementById('searchCli').value.toLowerCase().trim();
    const os  = document.getElementById('filObraSocial').value;
    let lista = todos;
    if (q) lista = lista.filter(c =>
        (c.nombre||'').toLowerCase().includes(q) ||
        (c.dni||'').toLowerCase().includes(q) ||
        (c.obra_social||c.cobertura||'').toLowerCase().includes(q) ||
        (c.telefono||'').toLowerCase().includes(q)
    );
    if (os) lista = lista.filter(c=>(c.obra_social||c.cobertura)===os);
    renderTabla(lista);
}

function renderTabla(lista) {
    const tb = document.getElementById('tablaCli');
    if (!lista.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fas fa-users"></i><p>No hay clientes registrados</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = lista.map(c => `
        <tr>
            <td><div style="font-weight:700;">${esc(c.nombre)}</div>${c.fecha_nacimiento?`<div style="font-size:11px;color:var(--text-secondary);">${fmtFecha(c.fecha_nacimiento)}</div>`:''}</td>
            <td><span style="font-family:monospace;font-size:12px;">${esc(c.dni||'—')}</span></td>
            <td>${c.telefono?`<a href="tel:${esc(c.telefono)}" style="color:var(--text-primary);text-decoration:none;">${esc(c.telefono)}</a>`:'—'}</td>
            <td>${(c.obra_social||c.cobertura)?`<span class="obra-badge">${esc(c.obra_social||c.cobertura)}</span>`:'<span style="color:var(--text-secondary);font-size:12px;">Particular</span>'}</td>
            <td><span style="font-size:12px;color:var(--text-secondary);">${esc(c.nro_afiliado||c.numero_afiliado||'—')}</span></td>
            <td>${c.email?`<a href="mailto:${esc(c.email)}" style="font-size:12px;color:var(--text-secondary);text-decoration:none;">${esc(c.email)}</a>`:'—'}</td>
            <td>
                <div style="display:flex;gap:6px;">
                    <button onclick="editar(${c.id})" class="btn btn-secondary" style="font-size:11px;padding:5px 10px;"><i class="fas fa-edit"></i></button>
                    <button onclick="eliminar(${c.id},'${esc(c.nombre)}')" class="btn btn-secondary" style="font-size:11px;padding:5px 10px;color:#dc2626;border-color:#fca5a5;"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModal(id = null) {
    editingId = id;
    document.getElementById('modalTitle').innerHTML = `<i class="fas fa-user-plus" style="color:var(--farm);margin-right:8px;"></i>${id ? 'Editar Cliente' : 'Nuevo Cliente'}`;
    if (!id) {
        ['fNombre','fDni','fTelefono','fEmail','fDireccion','fObraSocial','fAfiliado','fNotas'].forEach(x=>document.getElementById(x).value='');
        document.getElementById('fNacimiento').value='';
    }
    document.getElementById('modalCliente').classList.add('show');
    setTimeout(()=>document.getElementById('fNombre').focus(),50);
}

function editar(id) {
    const c = todos.find(x=>x.id==id);
    if (!c) return;
    editingId = id;
    document.getElementById('modalTitle').innerHTML = `<i class="fas fa-user-edit" style="color:var(--farm);margin-right:8px;"></i>Editar Cliente`;
    document.getElementById('fNombre').value     = c.nombre||'';
    document.getElementById('fDni').value        = c.dni||'';
    document.getElementById('fTelefono').value   = c.telefono||'';
    document.getElementById('fEmail').value      = c.email||'';
    document.getElementById('fDireccion').value  = c.direccion||'';
    document.getElementById('fNacimiento').value = c.fecha_nacimiento||'';
    document.getElementById('fObraSocial').value = c.obra_social||c.cobertura||'';
    document.getElementById('fAfiliado').value   = c.nro_afiliado||c.numero_afiliado||'';
    document.getElementById('fNotas').value      = c.notas||'';
    document.getElementById('modalCliente').classList.add('show');
}

function cerrarModal() { document.getElementById('modalCliente').classList.remove('show'); editingId=null; }

async function guardar() {
    const nombre = document.getElementById('fNombre').value.trim();
    if (!nombre) { document.getElementById('fNombre').focus(); return; }
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    const body = {
        nombre,
        dni:          document.getElementById('fDni').value.trim()||null,
        telefono:     document.getElementById('fTelefono').value.trim()||null,
        email:        document.getElementById('fEmail').value.trim()||null,
        direccion:    document.getElementById('fDireccion').value.trim()||null,
        fecha_nacimiento: document.getElementById('fNacimiento').value||null,
        obra_social:  document.getElementById('fObraSocial').value.trim()||null,
        nro_afiliado: document.getElementById('fAfiliado').value.trim()||null,
        notas:        document.getElementById('fNotas').value.trim()||null,
    };
    if (editingId) body.id = editingId;
    const r = await fetch(API, {
        method: editingId ? 'PUT' : 'POST', credentials:'include',
        headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
    });
    const j = await r.json();
    btn.disabled = false;
    if (j.success) { cerrarModal(); cargar(); } else { alert('Error: ' + j.message); }
}

async function eliminar(id, nombre) {
    if (!confirm(`¿Eliminar cliente "${nombre}"?`)) return;
    const r = await fetch(API, {
        method:'DELETE', credentials:'include',
        headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})
    });
    const j = await r.json();
    if (j.success) cargar(); else alert('Error: ' + j.message);
}

function fmtFecha(f) { if(!f) return ''; const p=f.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.getElementById('modalCliente').addEventListener('click',e=>{if(e.target===e.currentTarget)cerrarModal();});
cargar();
</script>
</body>
</html>
