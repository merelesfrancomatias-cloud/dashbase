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
    <title>Proveedores — Farmacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        :root { --farm: #10b981; }
        .app-layout { display:flex; min-height:100vh; }
        .stat-strip { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
        @media(max-width:600px){ .stat-strip { grid-template-columns:1fr 1fr; } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .stat-val  { font-size:22px; font-weight:800; line-height:1; }
        .stat-lbl  { font-size:12px; color:var(--text-secondary); margin-top:3px; }

        .prov-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:14px; padding:20px; }
        .prov-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; display:flex; flex-direction:column; gap:10px; transition:.15s; }
        .prov-card:hover { border-color:var(--farm); box-shadow:0 4px 16px rgba(16,185,129,.12); }
        .prov-name { font-size:16px; font-weight:700; color:var(--text-primary); }
        .prov-cuit { font-size:12px; color:var(--text-secondary); }
        .prov-tipo { display:inline-block; font-size:11px; font-weight:700; padding:2px 9px; border-radius:20px; background:rgba(16,185,129,.1); color:var(--farm); }
        .prov-row  { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-secondary); }
        .prov-row i { width:16px; text-align:center; color:var(--farm); flex-shrink:0; }
        .prov-actions { display:flex; gap:8px; border-top:1px solid var(--border); padding-top:10px; }

        .toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:16px 20px 0; }
        .search-box { flex:1; min-width:200px; position:relative; }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .search-box input { width:100%; padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .search-box input:focus { outline:none; border-color:var(--farm); }

        .empty-state { text-align:center; padding:50px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }

        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:10000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.show { display:flex; }
        .modal-box { background:var(--surface); border-radius:16px; width:560px; max-width:100%; max-height:90vh; display:flex; flex-direction:column; }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-body   { padding:20px 24px; overflow-y:auto; flex:1; }
        .modal-footer { padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; }
        .form-row { display:grid; gap:14px; margin-bottom:14px; }
        .form-row.cols2 { grid-template-columns:1fr 1fr; }
        @media(max-width:480px){ .form-row.cols2 { grid-template-columns:1fr; } }
        .form-group label { display:block; font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:5px; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus { outline:none; border-color:var(--farm); }
        .form-group textarea { resize:vertical; min-height:70px; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <div class="card" style="margin-bottom:20px;padding:18px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-truck" style="color:var(--farm);margin-right:8px;"></i>Proveedores
                        </h1>
                        <p style="margin:4px 0 0;font-size:14px;color:var(--text-secondary);">Distribuidoras y proveedores de productos farmacéuticos</p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="laboratorios.php" class="btn btn-secondary" style="font-size:13px;"><i class="fas fa-flask"></i> Laboratorios</a>
                        <button onclick="abrirModal()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);">
                            <i class="fas fa-plus"></i> Nuevo Proveedor
                        </button>
                    </div>
                </div>
            </div>

            <div class="stat-strip">
                <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,.1);color:var(--farm);"><i class="fas fa-truck"></i></div><div><div class="stat-val" id="stTotal">—</div><div class="stat-lbl">Proveedores activos</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-envelope"></i></div><div><div class="stat-val" id="stEmail">—</div><div class="stat-lbl">Con email</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-phone"></i></div><div><div class="stat-val" id="stTel">—</div><div class="stat-lbl">Con teléfono</div></div></div>
            </div>

            <div class="card" style="padding:0;">
                <div class="toolbar">
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchProv" placeholder="Buscar proveedor…" oninput="filtrar()"></div>
                </div>
                <div class="prov-grid" id="provGrid">
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="modalProv" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--text-primary);" id="modalTitle">
                <i class="fas fa-truck" style="color:var(--farm);margin-right:8px;"></i>Nuevo Proveedor
            </h2>
            <button onclick="cerrarModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label>Nombre <span style="color:#dc2626;">*</span></label><input type="text" id="fNombre" placeholder="Nombre del proveedor / empresa"></div>
            </div>
            <div class="form-row cols2">
                <div class="form-group"><label>CUIT</label><input type="text" id="fCuit" placeholder="20-12345678-9"></div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select id="fTipo">
                        <option value="Distribuidora">Distribuidora</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Importador">Importador</option>
                        <option value="Mayorista">Mayorista</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
            <div class="form-row cols2">
                <div class="form-group"><label>Condición de pago</label>
                    <select id="fCondicion">
                        <option value="">Seleccionar…</option>
                        <option value="Contado">Contado</option>
                        <option value="30 días">30 días</option>
                        <option value="45 días">45 días</option>
                        <option value="60 días">60 días</option>
                        <option value="90 días">90 días</option>
                        <option value="Consignación">Consignación</option>
                    </select>
                </div>
                <div class="form-group"><label>Contacto</label><input type="text" id="fContacto" placeholder="Nombre del representante"></div>
            </div>
            <div class="form-row cols2">
                <div class="form-group"><label>Teléfono</label><input type="text" id="fTelefono" placeholder="+54 11 …"></div>
                <div class="form-group"><label>Email</label><input type="email" id="fEmail" placeholder="ventas@proveedor.com"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Dirección</label><input type="text" id="fDireccion" placeholder="Calle, número, ciudad"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Notas</label><textarea id="fNotas" placeholder="Días de visita, líneas de productos, observaciones…"></textarea></div>
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

<script>
// Reutiliza la API de proveedores general si existe, sino usa un endpoint propio
const API = '../../api/farmacia/laboratorios.php'; // usa la misma estructura
let todos = [];
let editingId = null;

// Para proveedores farmacia usamos la API de laboratorios con tipo diferenciado
// Si hay una API de proveedores general la usamos
const API_PROV = '../../api/farmacia/proveedores.php';

async function cargar() {
    // Intentar primero la API específica, sino la de laboratorios
    let r = await fetch(API_PROV, {credentials:'include'}).catch(()=>null);
    if (!r || !r.ok) r = await fetch(API, {credentials:'include'});
    const j = await r.json();
    todos = j.success ? j.data : [];
    actualizarStats();
    filtrar();
}

function actualizarStats() {
    document.getElementById('stTotal').textContent = todos.length;
    document.getElementById('stEmail').textContent = todos.filter(p=>p.email).length;
    document.getElementById('stTel').textContent   = todos.filter(p=>p.telefono).length;
}

function filtrar() {
    const q = document.getElementById('searchProv').value.toLowerCase().trim();
    const lista = q ? todos.filter(p=>(p.nombre||'').toLowerCase().includes(q)||(p.cuit||'').toLowerCase().includes(q)||(p.contacto||'').toLowerCase().includes(q)) : todos;
    renderGrid(lista);
}

function renderGrid(lista) {
    const g = document.getElementById('provGrid');
    if (!lista.length) {
        g.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-truck"></i><p>No hay proveedores registrados</p></div>';
        return;
    }
    g.innerHTML = lista.map(p => `
        <div class="prov-card">
            <div>
                <div class="prov-name">${esc(p.nombre)}</div>
                ${p.cuit?`<div class="prov-cuit">CUIT: ${esc(p.cuit)}</div>`:''}
                <div style="margin-top:4px;">
                    ${p.tipo?`<span class="prov-tipo">${esc(p.tipo)}</span>`:''}
                    ${p.condicion_pago?`<span style="font-size:11px;color:var(--text-secondary);margin-left:6px;"><i class="fas fa-handshake"></i> ${esc(p.condicion_pago)}</span>`:''}
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;">
                ${p.contacto  ?`<div class="prov-row"><i class="fas fa-user"></i><span>${esc(p.contacto)}</span></div>`:''}
                ${p.telefono  ?`<div class="prov-row"><i class="fas fa-phone"></i><a href="tel:${esc(p.telefono)}" style="color:var(--text-secondary);text-decoration:none;">${esc(p.telefono)}</a></div>`:''}
                ${p.email     ?`<div class="prov-row"><i class="fas fa-envelope"></i><a href="mailto:${esc(p.email)}" style="color:var(--text-secondary);text-decoration:none;">${esc(p.email)}</a></div>`:''}
                ${p.direccion ?`<div class="prov-row"><i class="fas fa-map-marker-alt"></i><span>${esc(p.direccion)}</span></div>`:''}
                ${p.notas     ?`<div class="prov-row"><i class="fas fa-sticky-note"></i><span style="font-style:italic;">${esc(p.notas)}</span></div>`:''}
            </div>
            <div class="prov-actions">
                <button onclick="abrirModal(${p.id})" class="btn btn-secondary" style="flex:1;font-size:12px;padding:6px 10px;"><i class="fas fa-edit"></i> Editar</button>
                <button onclick="eliminar(${p.id},'${esc(p.nombre)}')" class="btn btn-secondary" style="font-size:12px;padding:6px 10px;color:#dc2626;border-color:#dc2626;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `).join('');
}

function abrirModal(id=null) {
    editingId=id;
    document.getElementById('modalTitle').innerHTML=`<i class="fas fa-truck" style="color:var(--farm);margin-right:8px;"></i>${id?'Editar Proveedor':'Nuevo Proveedor'}`;
    if(!id) {
        ['fNombre','fCuit','fContacto','fTelefono','fEmail','fDireccion','fNotas'].forEach(x=>document.getElementById(x).value='');
        document.getElementById('fTipo').value='Distribuidora';
        document.getElementById('fCondicion').value='';
    } else {
        const p=todos.find(x=>x.id==id);
        if(p){
            document.getElementById('fNombre').value    =p.nombre||'';
            document.getElementById('fCuit').value      =p.cuit||'';
            document.getElementById('fTipo').value      =p.tipo||'Distribuidora';
            document.getElementById('fCondicion').value =p.condicion_pago||'';
            document.getElementById('fContacto').value  =p.contacto||'';
            document.getElementById('fTelefono').value  =p.telefono||'';
            document.getElementById('fEmail').value     =p.email||'';
            document.getElementById('fDireccion').value =p.direccion||'';
            document.getElementById('fNotas').value     =p.notas||'';
        }
    }
    document.getElementById('modalProv').classList.add('show');
    setTimeout(()=>document.getElementById('fNombre').focus(),50);
}

function cerrarModal(){document.getElementById('modalProv').classList.remove('show');editingId=null;}

async function guardar(){
    const nombre=document.getElementById('fNombre').value.trim();
    if(!nombre){document.getElementById('fNombre').focus();return;}
    const btn=document.getElementById('btnGuardar');btn.disabled=true;
    const body={
        nombre,
        cuit:document.getElementById('fCuit').value.trim()||null,
        tipo:document.getElementById('fTipo').value||null,
        condicion_pago:document.getElementById('fCondicion').value||null,
        contacto:document.getElementById('fContacto').value.trim()||null,
        telefono:document.getElementById('fTelefono').value.trim()||null,
        email:document.getElementById('fEmail').value.trim()||null,
        direccion:document.getElementById('fDireccion').value.trim()||null,
        notas:document.getElementById('fNotas').value.trim()||null,
    };
    if(editingId)body.id=editingId;
    const r=await fetch(API,{method:editingId?'PUT':'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const j=await r.json();btn.disabled=false;
    if(j.success){cerrarModal();cargar();}else{alert('Error: '+j.message);}
}

async function eliminar(id,nombre){
    if(!confirm(`¿Eliminar "${nombre}"?`))return;
    const r=await fetch(API,{method:'DELETE',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
    const j=await r.json();
    if(j.success)cargar();else alert('Error: '+j.message);
}

function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
document.getElementById('modalProv').addEventListener('click',e=>{if(e.target===e.currentTarget)cerrarModal();});
cargar();
</script>
</body>
</html>
