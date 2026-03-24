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
    <title>Servicios — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <style>
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px 0; flex-wrap: wrap; gap: 12px;
        }
        .page-header h2 { font-size: 22px; font-weight: 800; color: var(--text-color,#1e293b); }
        .btn-nuevo {
            display: flex; align-items: center; gap: 8px; padding: 9px 18px;
            background: #8b5cf6; color: #fff; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s;
        }
        .btn-nuevo:hover { background: #7c3aed; }

        /* Filtros */
        .filtros-bar {
            display: flex; gap: 8px; padding: 16px 24px 0; flex-wrap: wrap;
        }
        .cat-filter {
            padding: 5px 14px; border-radius: 20px; border: 1px solid var(--border-color,#e5e7eb);
            background: transparent; font-size: 12px; font-weight: 600; cursor: pointer;
            color: var(--text-color,#374151); transition: all .15s;
        }
        .cat-filter:hover, .cat-filter.active {
            background: #8b5cf6; color: #fff; border-color: #8b5cf6;
        }

        /* Grid de servicios */
        .servicios-wrap { padding: 16px 24px; }
        .servicios-grid {
            display: grid; grid-template-columns: repeat(auto-fill,minmax(240px,1fr)); gap: 14px;
        }
        .servicio-card {
            background: var(--card-bg,#fff); border: 1px solid var(--border-color,#e5e7eb);
            border-radius: 16px; overflow: hidden; transition: box-shadow .15s, transform .15s;
        }
        .servicio-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); transform: translateY(-2px); }
        .sc-top { height: 5px; }
        .sc-body { padding: 16px; }
        .sc-cat { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
        .sc-nombre { font-size: 16px; font-weight: 800; color: var(--text-color,#1e293b); margin-bottom: 4px; }
        .sc-desc { font-size: 12px; color: var(--muted-color,#64748b); margin-bottom: 12px; min-height: 18px; }
        .sc-meta { display: flex; align-items: center; justify-content: space-between; }
        .sc-duracion { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--muted-color,#64748b); font-weight: 600; }
        .sc-precio { font-size: 20px; font-weight: 900; }
        .sc-footer { padding: 10px 16px; border-top: 1px solid var(--border-color,#f1f5f9); display: flex; justify-content: flex-end; gap: 8px; }
        .sc-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--muted-color,#64748b); transition: all .15s; }
        .sc-btn:hover { border-color: #8b5cf6; color: #8b5cf6; background: rgba(139,92,246,.06); }
        .sc-btn.del:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,.06); }
        .badge-inactivo { font-size: 10px; font-weight: 700; background: rgba(239,68,68,.1); color: #dc2626; padding: 2px 8px; border-radius: 20px; }

        .empty-grid { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; color: var(--muted-color,#9ca3af); gap: 10px; grid-column: 1/-1; }
        .empty-grid i { font-size: 40px; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; overflow-y: auto; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: var(--card-bg,#fff); border-radius: 20px; width: 100%; max-width: 460px; overflow: hidden; margin: auto; }
        .modal-head { padding: 20px 24px; border-bottom: 1px solid var(--border-color,#e5e7eb); display: flex; align-items: center; justify-content: space-between; background: var(--hover-bg,#f8fafc); }
        .modal-head h3 { font-size: 16px; font-weight: 700; }
        .modal-body { padding: 22px 24px; display: flex; flex-direction: column; gap: 14px; }
        .modal-footer { padding: 14px 24px; border-top: 1px solid var(--border-color,#e5e7eb); display: flex; gap: 10px; justify-content: flex-end; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 11px; font-weight: 700; color: var(--muted-color,#64748b); text-transform: uppercase; letter-spacing: .4px; }
        .form-group input, .form-group select, .form-group textarea { padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border-color,#e5e7eb); background: var(--card-bg,#fff); font-size: 14px; color: var(--text-color,#1e293b); outline: none; width: 100%; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139,92,246,.12); }
        .btn-primary { padding: 10px 20px; background: #8b5cf6; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-cancel { padding: 10px 16px; border-radius: 10px; border: 1px solid var(--border-color,#e5e7eb); background: transparent; font-size: 14px; cursor: pointer; }

        /* Color picker row */
        .color-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
        .color-chip { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; transition: border-color .15s, transform .1s; }
        .color-chip:hover, .color-chip.sel { border-color: #1e293b; transform: scale(1.15); }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="content-area">

        <div class="page-header">
            <h2><i class="fas fa-scissors" style="color:#8b5cf6;margin-right:10px;"></i>Servicios del Salón</h2>
            <button class="btn-nuevo" onclick="abrirNuevo()"><i class="fas fa-plus"></i> Nuevo Servicio</button>
        </div>

        <!-- Filtros por categoría -->
        <div class="filtros-bar" id="filtrosBar">
            <button class="cat-filter active" data-cat="" onclick="filtrarCat(this,'')">Todos</button>
        </div>

        <div class="servicios-wrap">
            <div class="servicios-grid" id="serviciosGrid">
                <div class="empty-grid"><i class="fas fa-spinner fa-spin"></i><p>Cargando…</p></div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalServicio">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitulo"><i class="fas fa-scissors" style="color:#8b5cf6;margin-right:8px;"></i> Nuevo Servicio</h3>
            <button style="background:none;border:none;cursor:pointer;font-size:16px;" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="sId">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" id="sNombre" placeholder="Ej: Corte de cabello">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" id="sDesc" placeholder="Descripción breve (opcional)">
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select id="sCat">
                    <option value="Cabello">Cabello</option>
                    <option value="Color">Color</option>
                    <option value="Tratamiento">Tratamiento</option>
                    <option value="Peinado">Peinado</option>
                    <option value="Estetica">Estética</option>
                    <option value="Unias">Uñas</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="grid2">
                <div class="form-group">
                    <label>Duración (min) *</label>
                    <input type="number" id="sDur" value="30" min="5" step="5">
                </div>
                <div class="form-group">
                    <label>Precio *</label>
                    <input type="number" id="sPrecio" value="0" min="0" step="100">
                </div>
            </div>
            <div class="form-group">
                <label>Comisión empleado (%)</label>
                <input type="number" id="sComision" value="0" min="0" max="100" step="0.5" placeholder="Ej: 30 para 30%">
            </div>
            <div class="form-group">
                <label>Color identificador</label>
                <div class="color-row" id="colorRow">
                    <input type="hidden" id="sColor" value="#8b5cf6">
                </div>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="sActivo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="guardar()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<script>
const API = '../../api/peluqueria/servicios.php';
const COLORES = ['#8b5cf6','#ec4899','#0ea5e9','#f59e0b','#ef4444','#16a34a','#f472b6','#6366f1','#14b8a6','#f97316','#64748b'];
let todos = [];
let filtroActual = '';

// Color chips
const colorRow = document.getElementById('colorRow');
const colorInput = document.getElementById('sColor');
COLORES.forEach(c => {
    const chip = document.createElement('div');
    chip.className = 'color-chip'; chip.style.background = c; chip.dataset.c = c;
    chip.addEventListener('click', () => {
        document.querySelectorAll('.color-chip').forEach(x => x.classList.remove('sel'));
        chip.classList.add('sel'); colorInput.value = c;
    });
    colorRow.appendChild(chip);
});
function setColor(c) {
    colorInput.value = c;
    document.querySelectorAll('.color-chip').forEach(x => x.classList.toggle('sel', x.dataset.c === c));
}

async function cargar() {
    const r = await fetch(API, { credentials: 'include' });
    const d = await r.json();
    todos = d.data || [];
    renderCats(); renderGrid();
}

function renderCats() {
    const cats = [...new Set(todos.map(s => s.categoria))].filter(Boolean).sort();
    const bar = document.getElementById('filtrosBar');
    bar.innerHTML = `<button class="cat-filter ${filtroActual===''?'active':''}" data-cat="" onclick="filtrarCat(this,'')">Todos</button>` +
        cats.map(c => `<button class="cat-filter ${filtroActual===c?'active':''}" data-cat="${c}" onclick="filtrarCat(this,'${c}')">${esc(c)}</button>`).join('');
}

function filtrarCat(btn, cat) {
    filtroActual = cat;
    document.querySelectorAll('.cat-filter').forEach(b => b.classList.toggle('active', b.dataset.cat === cat));
    renderGrid();
}

function renderGrid() {
    const lista = filtroActual ? todos.filter(s => s.categoria === filtroActual) : todos;
    const grid = document.getElementById('serviciosGrid');
    if (!lista.length) {
        grid.innerHTML = `<div class="empty-grid"><i class="fas fa-scissors"></i><p>Sin servicios para esta categoría</p></div>`;
        return;
    }
    grid.innerHTML = lista.map(s => `
        <div class="servicio-card">
            <div class="sc-top" style="background:${esc(s.color||'#8b5cf6')};"></div>
            <div class="sc-body">
                <div class="sc-cat" style="color:${esc(s.color||'#8b5cf6')}">${esc(s.categoria||'')}</div>
                <div class="sc-nombre">${esc(s.nombre)}
                    ${!parseInt(s.activo) ? '<span class="badge-inactivo">Inactivo</span>' : ''}
                </div>
                <div class="sc-desc">${esc(s.descripcion||'')}</div>
                <div class="sc-meta">
                    <div class="sc-duracion"><i class="fas fa-clock"></i>${s.duracion_min} min</div>
                    <div class="sc-precio" style="color:${esc(s.color||'#8b5cf6')}">$${Number(s.precio).toLocaleString('es-AR')}</div>
                </div>
                ${parseFloat(s.comision_porcentaje||0) > 0 ? `<div style="font-size:11px;color:#64748b;font-weight:600;margin-top:4px;"><i class="fas fa-percent" style="font-size:9px;margin-right:3px;"></i>Comisión: ${s.comision_porcentaje}%</div>` : ''}
            </div>
            <div class="sc-footer">
                <button class="sc-btn" title="Editar" onclick="editar(${s.id})"><i class="fas fa-pencil"></i></button>
                <button class="sc-btn del" title="Eliminar" onclick="eliminar(${s.id})"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `).join('');
}

function abrirNuevo() {
    document.getElementById('sId').value    = '';
    document.getElementById('sNombre').value = '';
    document.getElementById('sDesc').value  = '';
    document.getElementById('sCat').value   = 'Cabello';
    document.getElementById('sDur').value   = '30';
    document.getElementById('sPrecio').value   = '0';
    document.getElementById('sComision').value = '0';
    document.getElementById('sActivo').value   = '1';
    setColor('#8b5cf6');
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-scissors" style="color:#8b5cf6;margin-right:8px;"></i> Nuevo Servicio';
    document.getElementById('modalServicio').classList.add('open');
    setTimeout(() => document.getElementById('sNombre').focus(), 100);
}

function editar(id) {
    const s = todos.find(x => x.id == id);
    if (!s) return;
    document.getElementById('sId').value    = s.id;
    document.getElementById('sNombre').value = s.nombre;
    document.getElementById('sDesc').value  = s.descripcion || '';
    document.getElementById('sCat').value   = s.categoria || 'Otro';
    document.getElementById('sDur').value      = s.duracion_min;
    document.getElementById('sPrecio').value   = s.precio;
    document.getElementById('sComision').value = s.comision_porcentaje || 0;
    document.getElementById('sActivo').value = s.activo ? '1' : '0';
    setColor(s.color || '#8b5cf6');
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-pencil" style="color:#8b5cf6;margin-right:8px;"></i> Editar Servicio';
    document.getElementById('modalServicio').classList.add('open');
}

async function guardar() {
    const id  = parseInt(document.getElementById('sId').value) || 0;
    const nom = document.getElementById('sNombre').value.trim();
    if (!nom) { alert('Ingresá el nombre del servicio'); return; }
    const body = {
        id: id || undefined,
        nombre:      nom,
        descripcion: document.getElementById('sDesc').value,
        categoria:   document.getElementById('sCat').value,
        duracion_min:          parseInt(document.getElementById('sDur').value),
        precio:                parseFloat(document.getElementById('sPrecio').value),
        comision_porcentaje:   parseFloat(document.getElementById('sComision').value) || 0,
        color:       document.getElementById('sColor').value,
        activo:      parseInt(document.getElementById('sActivo').value),
    };
    const r = await fetch(API, {
        method: id ? 'PUT' : 'POST',
        headers: {'Content-Type':'application/json'},
        credentials: 'include', body: JSON.stringify(body)
    });
    const d = await r.json();
    if (d.success) { cerrarModal(); cargar(); }
    else alert(d.message || 'Error al guardar');
}

async function eliminar(id) {
    if (!confirm('¿Desactivar este servicio?')) return;
    await fetch(`${API}?id=${id}`, { method: 'DELETE', credentials: 'include' });
    cargar();
}

function cerrarModal() { document.getElementById('modalServicio').classList.remove('open'); }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.getElementById('modalServicio').addEventListener('click', e => {
    if (e.target === document.getElementById('modalServicio')) cerrarModal();
});

cargar();
</script>
</body>
</html>
