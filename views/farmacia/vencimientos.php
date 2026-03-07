<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$accentFarm = '#10b981';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vencimientos — DASHBASE</title>
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

        /* Tabla */
        .venc-table { width:100%; border-collapse:collapse; }
        .venc-table th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-secondary); padding:10px 14px; border-bottom:2px solid var(--border); text-align:left; white-space:nowrap; }
        .venc-table td { padding:11px 14px; border-bottom:1px solid var(--border); font-size:13px; vertical-align:middle; }
        .venc-table tr:last-child td { border-bottom:none; }
        .venc-table tr:hover td { background:var(--background); }

        /* Badges estado */
        .badge-vencido  { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-proximo  { background:#fef3c7; color:#d97706; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge-ok       { background:#d1fae5; color:#059669; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

        /* Dias chip */
        .dias-chip { font-size:12px; font-weight:700; }
        .dias-chip.neg { color:#dc2626; }
        .dias-chip.warn{ color:#d97706; }
        .dias-chip.ok  { color:#059669; }

        /* Toolbar */
        .toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .toolbar .search-box { flex:1; min-width:200px; position:relative; }
        .toolbar .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px; }
        .toolbar .search-box input { width:100%; padding:8px 12px 8px 32px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--background); color:var(--text-primary); box-sizing:border-box; }
        .toolbar .search-box input:focus { outline:none; border-color:var(--farm); }
        select.fil { padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:var(--surface); color:var(--text-primary); }
        select.fil:focus { outline:none; border-color:var(--farm); }

        /* Editar fecha inline */
        .btn-fecha { background:none; border:1px solid var(--border); border-radius:6px; padding:3px 8px; font-size:12px; cursor:pointer; color:var(--text-secondary); transition:.15s; }
        .btn-fecha:hover { border-color:var(--farm); color:var(--farm); }

        .empty-state { text-align:center; padding:50px 20px; color:var(--text-secondary); }
        .empty-state i { font-size:40px; opacity:.15; display:block; margin-bottom:12px; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Page header -->
            <div class="card" style="margin-bottom:20px;padding:18px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h1 style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);">
                            <i class="fas fa-calendar-times" style="color:var(--farm);margin-right:8px;"></i>Control de Vencimientos
                        </h1>
                        <p style="margin:4px 0 0;font-size:14px;color:var(--text-secondary);">Productos con fecha de vencimiento próxima o vencida</p>
                    </div>
                    <a href="recetas.php" class="btn btn-secondary" style="font-size:13px;">
                        <i class="fas fa-prescription"></i> Ir a Recetas
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-strip" id="statStrip">
                <div class="stat-card"><div class="stat-icon" style="background:#fee2e220;color:#dc2626;"><i class="fas fa-skull-crossbones"></i></div><div><div class="stat-val" id="stVencidos">—</div><div class="stat-lbl">Vencidos</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fef3c720;color:#d97706;"><i class="fas fa-exclamation-triangle"></i></div><div><div class="stat-val" id="stProximos">—</div><div class="stat-lbl">Próximos a vencer</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#d1fae520;color:#059669;"><i class="fas fa-check-circle"></i></div><div><div class="stat-val" id="stOk">—</div><div class="stat-lbl">En estado normal</div></div></div>
                <div class="stat-card"><div class="stat-icon" style="background:#e0f2fe20;color:#0ea5e9;"><i class="fas fa-boxes-stacked"></i></div><div><div class="stat-val" id="stTotal">—</div><div class="stat-lbl">Total con vencimiento</div></div></div>
            </div>

            <!-- Tabla -->
            <div class="card">
                <div style="padding:18px 20px 0;">
                    <div class="toolbar">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchVenc" placeholder="Buscar producto…" oninput="filtrar()"></div>
                        <select class="fil" id="filEstado" onchange="cargar()">
                            <option value="">Todos (próx. 90 días)</option>
                            <option value="30">Próximos 30 días</option>
                            <option value="60">Próximos 60 días</option>
                            <option value="90" selected>Próximos 90 días</option>
                            <option value="180">Próximos 180 días</option>
                            <option value="365">Próximos 365 días</option>
                        </select>
                        <select class="fil" id="filTipo" onchange="cargar()">
                            <option value="">Vencidos + próximos</option>
                            <option value="vencidos">Solo vencidos</option>
                        </select>
                        <button onclick="cargar()" class="btn btn-secondary" style="padding:8px 14px;font-size:13px;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="venc-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Vencimiento</th>
                                <th>Días</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyVenc">
                            <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal editar fecha -->
<div id="modalFecha" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:var(--surface);border-radius:16px;padding:28px;width:360px;max-width:95vw;">
        <h3 style="margin:0 0 16px;font-size:17px;font-weight:700;color:var(--text-primary);">
            <i class="fas fa-calendar-edit" style="color:var(--farm);margin-right:8px;"></i>Actualizar vencimiento
        </h3>
        <p style="font-size:13px;color:var(--text-secondary);margin:0 0 14px;" id="mfNombre"></p>
        <div style="margin-bottom:16px;">
            <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Nueva fecha de vencimiento</label>
            <input type="date" id="mfFecha" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;background:var(--background);color:var(--text-primary);box-sizing:border-box;">
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
            <button onclick="guardarFecha()" class="btn btn-primary" style="background:var(--farm);border-color:var(--farm);">Guardar</button>
        </div>
    </div>
</div>

<script>
const API_VENC = '../../api/farmacia/vencimientos.php';
let editingId = null;
let todosRows = [];

async function cargar() {
    const dias   = document.getElementById('filEstado').value || 90;
    const tipo   = document.getElementById('filTipo').value;
    const url    = API_VENC + '?dias=' + dias + (tipo === 'vencidos' ? '&vencidos=1' : '');
    const r = await fetch(url, {credentials:'include'});
    const j = await r.json();
    if (!j.success) return;
    todosRows = j.data.productos || [];
    const s   = j.data.stats || {};
    document.getElementById('stVencidos').textContent = s.vencidos ?? 0;
    document.getElementById('stProximos').textContent = s.proximos ?? 0;
    document.getElementById('stOk').textContent       = s.ok ?? 0;
    document.getElementById('stTotal').textContent    = s.total ?? 0;
    filtrar();
}

function filtrar() {
    const q = document.getElementById('searchVenc').value.toLowerCase().trim();
    const lista = q ? todosRows.filter(p => (p.nombre||'').toLowerCase().includes(q)) : todosRows;
    renderTabla(lista);
}

function renderTabla(lista) {
    const tb = document.getElementById('tbodyVenc');
    if (!lista.length) {
        tb.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="fas fa-check-double"></i><p>Sin productos en este rango</p></div></td></tr>';
        return;
    }
    tb.innerHTML = lista.map(p => {
        const dias = parseInt(p.dias_para_vencer);
        const est  = p.estado_vencimiento;
        const badgeClass = est === 'vencido' ? 'badge-vencido' : est === 'proximo' ? 'badge-proximo' : 'badge-ok';
        const badgeText  = est === 'vencido' ? 'Vencido' : est === 'proximo' ? 'Próximo' : 'OK';
        const diasClass  = dias < 0 ? 'neg' : dias <= 30 ? 'warn' : 'ok';
        const diasText   = dias < 0 ? `Hace ${Math.abs(dias)} días` : dias === 0 ? 'Hoy' : `${dias} días`;
        const color = p.categoria_color || '#64748b';
        return `<tr>
            <td>
                <div style="font-weight:600;font-size:13px;">${esc(p.nombre)}</div>
                ${p.codigo_barras ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(p.codigo_barras)}</div>` : ''}
            </td>
            <td><span style="font-size:12px;background:${color}20;color:${color};padding:2px 8px;border-radius:12px;">${esc(p.categoria_nombre||'—')}</span></td>
            <td style="font-weight:600;font-size:13px;">${fmtFecha(p.fecha_vencimiento)}</td>
            <td><span class="dias-chip ${diasClass}">${diasText}</span></td>
            <td style="font-weight:700;">${p.stock ?? 0} <span style="font-size:11px;color:var(--text-secondary);">${esc(p.unidad_medida||'')}</span></td>
            <td><span class="${badgeClass}">${badgeText}</span></td>
            <td><button class="btn-fecha" onclick="abrirModal(${p.id},'${esc(p.nombre)}','${p.fecha_vencimiento||''}')"><i class="fas fa-calendar-alt"></i> Editar</button></td>
        </tr>`;
    }).join('');
}

function abrirModal(id, nombre, fecha) {
    editingId = id;
    document.getElementById('mfNombre').textContent = nombre;
    document.getElementById('mfFecha').value = fecha || '';
    document.getElementById('modalFecha').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modalFecha').style.display = 'none';
    editingId = null;
}
async function guardarFecha() {
    const fecha = document.getElementById('mfFecha').value;
    const r = await fetch(API_VENC, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id: editingId, fecha_vencimiento: fecha})
    });
    const j = await r.json();
    if (j.success) { cerrarModal(); cargar(); }
}

function fmtFecha(f) { if(!f) return '—'; const p=f.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.getElementById('modalFecha').addEventListener('click', e => { if(e.target===e.currentTarget) cerrarModal(); });
cargar();
</script>
</body>
</html>
