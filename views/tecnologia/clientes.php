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
    <title>Clientes — Tecnología</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --tec:#6366f1; --tec-light:rgba(99,102,241,.1); }
        .tec-toolbar { position:sticky;top:0;z-index:10;background:var(--surface);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
        .tec-toolbar h1 { margin:0;font-size:20px;font-weight:700;color:var(--text-primary); }
        .tec-toolbar p  { margin:0;font-size:12px;color:var(--text-secondary); }
        .stats-bar { display:flex;gap:12px;padding:16px 24px 0;flex-wrap:wrap; }
        .stat-pill { display:flex;align-items:center;gap:8px;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;border:1.5px solid var(--border);background:var(--background);color:var(--text-primary); }
        .stat-pill .dot { width:10px;height:10px;border-radius:50%; }
        .filter-bar { padding:14px 24px 0;display:flex;gap:10px;flex-wrap:wrap; }
        .filter-input { flex:1;min-width:200px;padding:9px 14px 9px 36px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:10px center; }
        .filter-input:focus { outline:none;border-color:var(--tec); }
        .cli-table { width:100%;border-collapse:collapse;font-size:13px; }
        .cli-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);border-bottom:1.5px solid var(--border);background:var(--background); }
        .cli-table td { padding:12px 16px;border-bottom:1px solid var(--border);color:var(--text-primary); }
        .cli-table tr:hover td { background:var(--tec-light); }
        .avatar { width:36px;height:36px;border-radius:50%;background:var(--tec-light);color:var(--tec);font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1.5px solid var(--tec); }
        .badge { padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700; }
        .badge-activo  { background:rgba(239,68,68,.1);color:#dc2626; }
        .badge-listo   { background:rgba(15,209,134,.1);color:#059669; }
        .badge-zero    { background:var(--background);color:var(--text-secondary); }
        .btn-sm { padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--background);cursor:pointer;font-size:12px;color:var(--text-secondary);transition:all .15s;display:flex;align-items:center;gap:4px; }
        .btn-sm:hover { background:var(--tec);color:#fff;border-color:var(--tec); }
        /* Modal */
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface);border-radius:20px;width:100%;max-width:540px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--surface);z-index:2; }
        .modal-header h3 { margin:0;font-size:17px;font-weight:700; }
        .modal-close { background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;padding:4px 8px;border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block;font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px; }
        .fi { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);box-sizing:border-box; }
        .fi:focus { outline:none;border-color:var(--tec); }
        .fg-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
        .toast { position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#1e293b;color:white;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;opacity:0;transition:opacity .3s;white-space:nowrap;pointer-events:none; }
        .toast.show { opacity:1; }
        @media(max-width:600px) { .cli-table { display:none; } .filter-bar,.stats-bar,.tec-toolbar { padding:12px; } }
    </style>
</head>
<body>
<script>window.APP_BASE='<?= $base ?>';</script>
<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
        <?php include '../includes/header.php'; ?>

        <div class="tec-toolbar">
            <div>
                <h1><i class="fas fa-laptop" style="color:var(--tec);margin-right:8px;"></i>Clientes</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="ordenes.php" class="btn btn-secondary" style="text-decoration:none;"><i class="fas fa-tools"></i> Órdenes</a>
                <button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus"></i> Nuevo Cliente</button>
            </div>
        </div>

        <div class="stats-bar">
            <div class="stat-pill"><div class="dot" style="background:#6366f1;"></div><span id="st-total">0</span> Clientes</div>
            <div class="stat-pill"><div class="dot" style="background:#ef4444;"></div><span id="st-activos">0</span> Con orden activa</div>
            <div class="stat-pill"><div class="dot" style="background:#0FD186;"></div><span id="st-listos">0</span> Listos p/entregar</div>
        </div>

        <div class="filter-bar">
            <input class="filter-input" type="text" id="buscar" placeholder="Buscar por nombre, DNI o teléfono…" oninput="filtrar()">
        </div>

        <div style="padding:16px 24px 24px;overflow-x:auto;" id="cliContent">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin" style="font-size:28px;"></i></div>
        </div>
    </div>
</div>

<!-- Modal cliente -->
<div class="modal-overlay" id="modalCli">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-user-plus" style="color:var(--tec);margin-right:8px;"></i>Nuevo Cliente</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cliId">
            <div class="fg-grid">
                <div class="fg"><label>Nombre <span style="color:#ef4444;">*</span></label><input class="fi" type="text" id="cliNombre" placeholder="Juan"></div>
                <div class="fg"><label>Apellido</label><input class="fi" type="text" id="cliApellido" placeholder="García"></div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>DNI</label><input class="fi" type="text" id="cliDni" placeholder="12.345.678"></div>
                <div class="fg"><label>Teléfono</label><input class="fi" type="tel" id="cliTel" placeholder="+54 9 11…"></div>
            </div>
            <div class="fg"><label>Email</label><input class="fi" type="email" id="cliEmail" placeholder="juan@mail.com"></div>
            <div class="fg"><label>Dirección</label><input class="fi" type="text" id="cliDir" placeholder="Calle 123…"></div>
            <div class="fg"><label>Observaciones</label><textarea class="fi" id="cliObs" rows="2" style="resize:vertical;"></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardar()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
<script>
const BASE = '<?= $base ?>';
const API  = BASE + '/api/tecnologia/clientes.php';
let todos  = [], filtrados = [];

async function init() {
    const r = await fetch(API, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { document.getElementById('cliContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-lock" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i><p>${j.message}</p></div>`; return; }
    todos = j.data.clientes || [];
    const st = j.data.stats || {};
    document.getElementById('subtitulo').textContent = `${st.total||0} clientes registrados`;
    document.getElementById('st-total').textContent   = st.total  || 0;
    document.getElementById('st-activos').textContent = st.activos|| 0;
    document.getElementById('st-listos').textContent  = st.listos || 0;
    filtrar();
}

function filtrar() {
    const q = (document.getElementById('buscar').value||'').toLowerCase().trim();
    filtrados = q ? todos.filter(c => (c.nombre+c.apellido+c.dni+c.telefono).toLowerCase().includes(q)) : [...todos];
    render();
}

function render() {
    const cont = document.getElementById('cliContent');
    if (!filtrados.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-users" style="font-size:48px;opacity:.12;display:block;margin-bottom:16px;"></i><p style="font-size:16px;font-weight:600;margin-bottom:16px;">No hay clientes</p><button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus"></i> Nuevo Cliente</button></div>`;
        return;
    }
    cont.innerHTML = `<table class="cli-table"><thead><tr><th>Cliente</th><th>DNI</th><th>Teléfono</th><th>Órdenes</th><th>Activas</th><th>Listos</th><th></th></tr></thead><tbody>
        ${filtrados.map(c => `<tr>
            <td><div style="display:flex;align-items:center;gap:10px;">
                <div class="avatar">${esc((c.apellido||c.nombre||'?')[0]).toUpperCase()}</div>
                <div><div style="font-weight:700;">${esc(c.apellido?c.apellido+', '+c.nombre:c.nombre)}</div>${c.email?`<div style="font-size:11px;color:var(--text-secondary);">${esc(c.email)}</div>`:''}</div>
            </div></td>
            <td>${c.dni||'—'}</td>
            <td>${c.telefono?`<a href="tel:${esc(c.telefono)}" style="color:var(--tec);font-weight:600;">${esc(c.telefono)}</a>`:'—'}</td>
            <td><span class="badge badge-zero">${c.total_ordenes||0}</span></td>
            <td>${(c.ordenes_activas>0)?`<span class="badge badge-activo">${c.ordenes_activas}</span>`:'—'}</td>
            <td>${(c.listos_para_entregar>0)?`<span class="badge badge-listo">${c.listos_para_entregar} listo${c.listos_para_entregar>1?'s':''}</span>`:'—'}</td>
            <td><div style="display:flex;gap:6px;">
                <button class="btn-sm" onclick="editar(${c.id})" title="Editar"><i class="fas fa-edit"></i></button>
                <a href="ordenes.php?cliente=${c.id}" class="btn-sm" style="text-decoration:none;" title="Ver órdenes"><i class="fas fa-tools"></i></a>
            </div></td>
        </tr>`).join('')}
    </tbody></table>`;
}

function abrirModal(c=null) {
    document.getElementById('cliId').value       = c?c.id:'';
    document.getElementById('cliNombre').value   = c?(c.nombre||''):'';
    document.getElementById('cliApellido').value = c?(c.apellido||''):'';
    document.getElementById('cliDni').value      = c?(c.dni||''):'';
    document.getElementById('cliTel').value      = c?(c.telefono||''):'';
    document.getElementById('cliEmail').value    = c?(c.email||''):'';
    document.getElementById('cliDir').value      = c?(c.direccion||''):'';
    document.getElementById('cliObs').value      = c?(c.observaciones||''):'';
    document.getElementById('modalTitulo').innerHTML = c
        ? `<i class="fas fa-edit" style="color:var(--tec);margin-right:8px;"></i>Editar Cliente`
        : `<i class="fas fa-user-plus" style="color:var(--tec);margin-right:8px;"></i>Nuevo Cliente`;
    document.getElementById('modalCli').classList.add('open');
    setTimeout(()=>document.getElementById('cliNombre').focus(),100);
}
function cerrarModal() { document.getElementById('modalCli').classList.remove('open'); }
function editar(id) { abrirModal(todos.find(c=>c.id==id)); }

async function guardar() {
    const id = document.getElementById('cliId').value;
    const nombre = document.getElementById('cliNombre').value.trim();
    if (!nombre) { toast('El nombre es obligatorio','error'); return; }
    const body = { nombre, apellido:document.getElementById('cliApellido').value.trim(), dni:document.getElementById('cliDni').value.trim()||null, telefono:document.getElementById('cliTel').value.trim()||null, email:document.getElementById('cliEmail').value.trim()||null, direccion:document.getElementById('cliDir').value.trim()||null, observaciones:document.getElementById('cliObs').value.trim()||null };
    const r = await fetch(id?`${API}?id=${id}`:API, {method:id?'PUT':'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModal(); toast(id?'Cliente actualizado ✓':'Cliente creado ✓'); init(); }
    else toast(j.message||'Error','error');
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function toast(msg,tipo='ok') { const t=document.getElementById('toast'); t.textContent=msg; t.style.background=tipo==='error'?'#ef4444':'#1e293b'; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500); }

document.getElementById('modalCli').addEventListener('click',e=>{ if(e.target.id==='modalCli') cerrarModal(); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') cerrarModal(); });
init();
</script>
</body>
</html>
