<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace(str_replace(chr(92),chr(47),$_SERVER['DOCUMENT_ROOT']),'',str_replace(chr(92),chr(47),dirname(dirname(dirname(realpath(__FILE__)))))),'/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Almacén — DASHBASE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__,2).'/public/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__,2).'/public/css/components.css') ?>">
<style>
/* ── Tabs ── */
.alm-tabs{display:flex;gap:6px;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:5px;margin-bottom:22px;overflow-x:auto;}
.alm-tab{display:flex;align-items:center;gap:8px;padding:9px 18px;border-radius:10px;border:none;background:transparent;color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;transition:all .2s;}
.alm-tab:hover{background:var(--background);color:var(--text-primary);}
.alm-tab.on{background:var(--primary);color:#fff;}
.alm-panel{display:none;}
.alm-panel.on{display:block;}

/* ── Stat cards ── */
.stat-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:22px;}
.stat-c{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px 20px;}
.stat-c-val{font-size:26px;font-weight:800;color:var(--text-primary);margin-bottom:4px;}
.stat-c-lbl{font-size:12px;color:var(--text-secondary);font-weight:500;}
.stat-alert{color:var(--error)!important;}

/* ── Tabla ── */
.alm-table-wrap{overflow-x:auto;}
.alm-table{width:100%;border-collapse:collapse;font-size:14px;}
.alm-table th{text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary);padding:10px 14px;border-bottom:2px solid var(--border);white-space:nowrap;}
.alm-table td{padding:12px 14px;border-bottom:1px solid var(--border);vertical-align:middle;}
.alm-table tr:last-child td{border-bottom:none;}
.alm-table tbody tr:hover{background:var(--background);}

/* Stock badge */
.stk{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700;}
.stk-ok{background:#d1fae5;color:#065f46;}
.stk-low{background:#fee2e2;color:#991b1b;}

/* Costo margin */
.margin-pos{color:#059669;font-weight:700;}
.margin-neg{color:#dc2626;font-weight:700;}

/* ── Panel lateral receta ── */
.receta-panel{
  position:fixed;top:0;right:-480px;height:100vh;width:460px;
  background:var(--surface);box-shadow:-4px 0 30px rgba(0,0,0,.12);
  transition:right .3s cubic-bezier(.4,0,.2,1);
  z-index:1000;display:flex;flex-direction:column;overflow:hidden;
}
.receta-panel.open{right:0;}
.rp-header{
  padding:20px;
  background:linear-gradient(135deg,#F97316,#ea580c);
  color:#fff;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;
}
.rp-header h3{font-size:16px;font-weight:800;}
.rp-body{flex:1;overflow-y:auto;padding:18px;}
.rp-total{padding:16px 18px;border-top:1px solid var(--border);background:var(--background);flex-shrink:0;}

/* Rows receta */
.rec-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);}
.rec-row:last-child{border-bottom:none;}
.rec-nombre{flex:1;font-size:14px;font-weight:600;}
.rec-cant{font-size:13px;color:var(--text-secondary);}
.rec-costo{font-size:13px;font-weight:700;color:var(--primary);}
.rec-del{background:none;border:none;color:var(--error);cursor:pointer;padding:4px;font-size:13px;}

/* ── Modales ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center;padding:16px;}
.modal-overlay.show{display:flex;}
.modal-box{background:var(--surface);border-radius:18px;padding:24px;width:100%;max-width:460px;max-height:90vh;overflow-y:auto;animation:su .25s ease;}
@keyframes su{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-title{font-size:17px;font-weight:800;margin-bottom:18px;}
.form-row{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.form-row label{font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;}
.form-row input,.form-row select,.form-row textarea{
  padding:10px 12px;border:1.5px solid var(--border);border-radius:10px;
  font-size:14px;outline:none;background:var(--background);color:var(--text-primary);
  transition:border-color .2s;
}
.form-row input:focus,.form-row select:focus{border-color:var(--primary);background:#fff;}
.form-actions{display:flex;gap:10px;margin-top:6px;}

/* Panel overlay */
.panel-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:999;}
.panel-overlay.show{display:block;}

@media(max-width:768px){
  .receta-panel{width:100%;right:-100%;}
}
</style>
</head>
<body>
<script>window.APP_BASE='<?= $base ?>';</script>

<div class="dashboard-layout">
  <?php include '../includes/sidebar.php'; ?>

  <div class="main-content" style="flex:1;padding:24px;overflow-y:auto;">
    <?php include '../includes/header.php'; ?>

    <!-- Título -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
      <div>
        <h2 style="font-size:22px;font-weight:800;color:var(--text-primary);margin:0;">
          <i class="fas fa-boxes" style="color:#F97316;margin-right:8px;"></i>Almacén
        </h2>
        <p style="font-size:13px;color:var(--text-secondary);margin:4px 0 0;">Insumos, compras y costos por plato</p>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-outline btn-sm" onclick="abrirModalInsumo()"><i class="fas fa-plus"></i> Nuevo insumo</button>
        <button class="btn btn-primary btn-sm" onclick="abrirModalCompra()"><i class="fas fa-shopping-cart"></i> Registrar compra</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stat-row" id="statRow">
      <div class="stat-c"><div class="stat-c-val" id="st-mes">—</div><div class="stat-c-lbl">Gasto este mes</div></div>
      <div class="stat-c"><div class="stat-c-val" id="st-sem">—</div><div class="stat-c-lbl">Gasto esta semana</div></div>
      <div class="stat-c"><div class="stat-c-val" id="st-ins">—</div><div class="stat-c-lbl">Insumos activos</div></div>
      <div class="stat-c"><div class="stat-c-val stat-alert" id="st-low">—</div><div class="stat-c-lbl">Stock bajo</div></div>
    </div>

    <!-- Tabs -->
    <div class="alm-tabs">
      <button class="alm-tab on" onclick="tab('insumos',this)"><i class="fas fa-boxes"></i> Insumos</button>
      <button class="alm-tab"    onclick="tab('compras',this)"><i class="fas fa-receipt"></i> Compras</button>
      <button class="alm-tab"    onclick="tab('costos',this)"><i class="fas fa-calculator"></i> Costo por plato</button>
    </div>

    <!-- ══ Tab Insumos ══ -->
    <div class="alm-panel on" id="panel-insumos">
      <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
        <input type="text" class="form-control" id="filtroInsumo" placeholder="Buscar insumo…" oninput="renderInsumos()" style="max-width:260px;">
      </div>
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="alm-table-wrap">
          <table class="alm-table">
            <thead>
              <tr>
                <th>Nombre</th><th>Categoría</th><th>Unidad</th>
                <th>Precio unit.</th><th>Stock actual</th><th>Stock mín.</th>
                <th>Gasto total</th><th>Platos</th><th></th>
              </tr>
            </thead>
            <tbody id="tbInsumos"><tr><td colspan="9" style="text-align:center;padding:32px;color:var(--text-secondary);">Cargando…</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ Tab Compras ══ -->
    <div class="alm-panel" id="panel-compras">
      <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;align-items:center;">
        <input type="date" class="form-control" id="filtroDesde" style="max-width:160px;">
        <input type="date" class="form-control" id="filtroHasta" style="max-width:160px;">
        <button class="btn btn-outline btn-sm" onclick="cargarCompras()"><i class="fas fa-filter"></i> Filtrar</button>
        <span id="totalPeriodo" style="font-size:15px;font-weight:700;color:var(--primary);margin-left:auto;"></span>
      </div>
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="alm-table-wrap">
          <table class="alm-table">
            <thead>
              <tr><th>Fecha</th><th>Insumo</th><th>Cantidad</th><th>Precio unit.</th><th>Total</th><th>Proveedor</th><th>Usuario</th><th></th></tr>
            </thead>
            <tbody id="tbCompras"><tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text-secondary);">Cargando…</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ Tab Costo por plato ══ -->
    <div class="alm-panel" id="panel-costos">
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="alm-table-wrap">
          <table class="alm-table">
            <thead>
              <tr><th>Plato</th><th>Categoría</th><th>Precio venta</th><th>Costo calculado</th><th>Margen</th><th>Ingredientes</th><th></th></tr>
            </thead>
            <tbody id="tbCostos"><tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-secondary);">Cargando…</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Overlay panel receta -->
<div class="panel-overlay" id="recOv" onclick="cerrarReceta()"></div>

<!-- Panel receta lateral -->
<div class="receta-panel" id="recPanel">
  <div class="rp-header">
    <div>
      <h3 id="rpTitulo">Receta</h3>
      <p id="rpSub" style="font-size:12px;opacity:.8;margin-top:2px;"></p>
    </div>
    <button style="background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:15px;" onclick="cerrarReceta()">×</button>
  </div>
  <div class="rp-body">
    <!-- Agregar ingrediente -->
    <div style="background:var(--background);border-radius:12px;padding:14px;margin-bottom:16px;">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px;">Agregar ingrediente</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <select id="rpInsumo" style="flex:2;min-width:140px;padding:9px 10px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text-primary);"></select>
        <input type="number" id="rpCant" placeholder="Cantidad" step="0.01" style="flex:1;min-width:80px;padding:9px 10px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;outline:none;">
        <button class="btn btn-primary btn-sm" onclick="agregarIngrediente()"><i class="fas fa-plus"></i></button>
      </div>
    </div>

    <!-- Lista de ingredientes -->
    <div id="rpIngredientes"></div>
  </div>
  <div class="rp-total">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <span style="font-size:13px;color:var(--text-secondary);">Costo por porción</span>
      <strong style="font-size:22px;color:var(--primary);" id="rpCostoTotal">$0</strong>
    </div>
    <button class="btn btn-primary" style="width:100%;margin-top:12px;" onclick="guardarReceta()"><i class="fas fa-save"></i> Guardar receta</button>
  </div>
</div>

<!-- Modal insumo -->
<div class="modal-overlay" id="modalInsumo">
  <div class="modal-box">
    <div class="modal-title" id="mi-titulo">Nuevo insumo</div>
    <input type="hidden" id="mi-id">
    <div class="form-row"><label>Nombre *</label><input type="text" id="mi-nombre" placeholder="Harina, aceite, tomate…"></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-row"><label>Categoría</label><input type="text" id="mi-cat" placeholder="Lácteos, Verduras…"></div>
      <div class="form-row"><label>Unidad *</label>
        <select id="mi-unidad">
          <option>kg</option><option>g</option><option>litro</option>
          <option>ml</option><option>unidad</option><option>docena</option><option>caja</option><option>paquete</option>
        </select>
      </div>
    </div>
    <div class="form-row"><label>Precio unitario</label><input type="number" id="mi-precio" step="0.01" placeholder="0.00"></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-row"><label>Stock actual</label><input type="number" id="mi-stock" step="0.01" placeholder="0"></div>
      <div class="form-row"><label>Stock mínimo</label><input type="number" id="mi-min" step="0.01" placeholder="0"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="cerrarModal('modalInsumo')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarInsumo()"><i class="fas fa-save"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Modal compra -->
<div class="modal-overlay" id="modalCompra">
  <div class="modal-box">
    <div class="modal-title">Registrar compra</div>
    <div class="form-row"><label>Insumo *</label><select id="mc-insumo" onchange="actualizarPrecioRef()"></select></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-row"><label>Cantidad *</label><input type="number" id="mc-cant" step="0.01" placeholder="0" oninput="calcTotal()"></div>
      <div class="form-row"><label>Precio unit. *</label><input type="number" id="mc-precio" step="0.01" placeholder="0.00" oninput="calcTotal()"></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-row"><label>Proveedor</label><input type="text" id="mc-prov" placeholder="Nombre del proveedor"></div>
      <div class="form-row"><label>Fecha *</label><input type="date" id="mc-fecha"></div>
    </div>
    <div class="form-row"><label>Notas</label><textarea id="mc-notas" rows="2" style="resize:vertical;"></textarea></div>
    <div style="background:var(--background);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;justify-content:space-between;">
      <span style="font-size:13px;color:var(--text-secondary);">Total</span>
      <strong style="font-size:18px;color:var(--primary);" id="mc-total">$0</strong>
    </div>
    <div class="form-actions">
      <button class="btn btn-outline" onclick="cerrarModal('modalCompra')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarCompra()"><i class="fas fa-check"></i> Registrar</button>
    </div>
  </div>
</div>

<script>
const BASE = window.APP_BASE;
let insumos = [], platoActual = null, recetaActual = [];

// ── Init ───────────────────────────────────────────
async function init() {
  await Promise.all([cargarResumen(), cargarInsumos()]);
}

// ── Tabs ───────────────────────────────────────────
function tab(id, el) {
  document.querySelectorAll('.alm-tab').forEach(b=>b.classList.remove('on'));
  document.querySelectorAll('.alm-panel').forEach(p=>p.classList.remove('on'));
  el.classList.add('on');
  document.getElementById('panel-'+id).classList.add('on');
  if (id==='compras') cargarCompras();
  if (id==='costos')  cargarCostos();
}

// ── Stats ──────────────────────────────────────────
async function cargarResumen() {
  const r = await fetch(`${BASE}/api/restaurant/almacen.php?entity=resumen`);
  const d = await r.json();
  if (!d.success) return;
  document.getElementById('st-mes').textContent  = fmt(d.data.gasto_mes);
  document.getElementById('st-sem').textContent  = fmt(d.data.gasto_semana);
  document.getElementById('st-ins').textContent  = d.data.total_insumos;
  document.getElementById('st-low').textContent  = d.data.stock_bajo + (d.data.stock_bajo > 0 ? ' ⚠' : '');
}

// ── Insumos ────────────────────────────────────────
async function cargarInsumos() {
  const r = await fetch(`${BASE}/api/restaurant/almacen.php?entity=insumos`);
  const d = await r.json();
  if (!d.success) return;
  insumos = d.data;
  renderInsumos();
  poblarSelectInsumos();
}

function renderInsumos() {
  const q   = document.getElementById('filtroInsumo').value.toLowerCase();
  const fil = q ? insumos.filter(i=>i.nombre.toLowerCase().includes(q)||(i.categoria||'').toLowerCase().includes(q)) : insumos;
  const tb  = document.getElementById('tbInsumos');
  if (!fil.length) { tb.innerHTML=`<tr><td colspan="9" style="text-align:center;padding:32px;color:var(--text-secondary);">No hay insumos</td></tr>`; return; }
  tb.innerHTML = fil.map(i => {
    const bajo = parseFloat(i.stock_actual) <= parseFloat(i.stock_minimo) && parseFloat(i.stock_minimo) > 0;
    const stk  = bajo
      ? `<span class="stk stk-low"><i class="fas fa-exclamation-triangle"></i>${num(i.stock_actual)} ${i.unidad}</span>`
      : `<span class="stk stk-ok">${num(i.stock_actual)} ${i.unidad}</span>`;
    return `<tr>
      <td><strong>${esc(i.nombre)}</strong></td>
      <td style="color:var(--text-secondary)">${esc(i.categoria||'—')}</td>
      <td>${esc(i.unidad)}</td>
      <td>${fmt(i.precio_unitario)}</td>
      <td>${stk}</td>
      <td style="color:var(--text-secondary)">${num(i.stock_minimo)} ${i.unidad}</td>
      <td>${fmt(i.gasto_total)}</td>
      <td style="text-align:center"><span style="font-size:12px;background:var(--background);padding:2px 8px;border-radius:99px;">${i.usado_en_platos}</span></td>
      <td>
        <div style="display:flex;gap:6px;">
          <button class="btn btn-outline btn-sm" onclick="editarInsumo(${i.id})" title="Editar"><i class="fas fa-edit"></i></button>
          <button class="btn btn-outline btn-sm" onclick="confirmarEliminarInsumo(${i.id},'${esc(i.nombre)}')" title="Eliminar"><i class="fas fa-trash" style="color:var(--error)"></i></button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// ── Compras ────────────────────────────────────────
async function cargarCompras() {
  const desde = document.getElementById('filtroDesde').value;
  const hasta = document.getElementById('filtroHasta').value;
  let url = `${BASE}/api/restaurant/almacen.php?entity=compras`;
  if (desde) url += `&desde=${desde}`;
  if (hasta) url += `&hasta=${hasta}`;
  const r = await fetch(url);
  const d = await r.json();
  if (!d.success) return;
  document.getElementById('totalPeriodo').textContent = 'Total: ' + fmt(d.data.total_periodo);
  const tb = document.getElementById('tbCompras');
  if (!d.data.compras.length) { tb.innerHTML=`<tr><td colspan="8" style="text-align:center;padding:32px;color:var(--text-secondary);">Sin compras en el período</td></tr>`; return; }
  tb.innerHTML = d.data.compras.map(c=>`<tr>
    <td>${c.fecha}</td>
    <td><strong>${esc(c.insumo_nombre)}</strong> <span style="font-size:11px;color:var(--text-secondary)">${esc(c.unidad)}</span></td>
    <td>${num(c.cantidad)}</td>
    <td>${fmt(c.precio_unitario)}</td>
    <td><strong style="color:var(--primary)">${fmt(c.total)}</strong></td>
    <td style="color:var(--text-secondary)">${esc(c.proveedor||'—')}</td>
    <td style="color:var(--text-secondary)">${esc(c.usuario_nombre||'—')}</td>
    <td><button class="btn btn-outline btn-sm" onclick="eliminarCompra(${c.id})" title="Eliminar"><i class="fas fa-trash" style="color:var(--error)"></i></button></td>
  </tr>`).join('');
}

// ── Costos ─────────────────────────────────────────
async function cargarCostos() {
  const r = await fetch(`${BASE}/api/restaurant/almacen.php?entity=recetas`);
  const d = await r.json();
  if (!d.success) return;
  const tb = document.getElementById('tbCostos');
  if (!d.data.length) { tb.innerHTML=`<tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-secondary);">Sin platos</td></tr>`; return; }
  tb.innerHTML = d.data.map(p=>{
    const costo  = parseFloat(p.costo_calculado);
    const venta  = parseFloat(p.precio_venta);
    const margen = costo > 0 ? ((venta-costo)/venta*100).toFixed(1) : null;
    const mcls   = margen === null ? '' : parseFloat(margen) > 30 ? 'margin-pos' : 'margin-neg';
    return `<tr>
      <td><strong>${esc(p.nombre)}</strong></td>
      <td style="color:var(--text-secondary)">${esc(p.categoria_nombre||'Sin categoría')}</td>
      <td>${fmt(venta)}</td>
      <td>${costo>0?fmt(costo):'<span style="color:var(--text-secondary);font-size:12px;">Sin receta</span>'}</td>
      <td class="${mcls}">${margen!==null?margen+'%':'—'}</td>
      <td style="text-align:center"><span style="font-size:12px;background:var(--background);padding:2px 8px;border-radius:99px;">${p.total_ingredientes}</span></td>
      <td><button class="btn btn-primary btn-sm" onclick="abrirReceta(${p.id},'${esc(p.nombre)}',${venta})"><i class="fas fa-flask"></i> Receta</button></td>
    </tr>`;
  }).join('');
}

// ── Panel Receta ───────────────────────────────────
async function abrirReceta(pid, nombre, precio) {
  platoActual = {id:pid, nombre, precio};
  document.getElementById('rpTitulo').textContent = nombre;
  document.getElementById('rpSub').textContent    = 'Precio de venta: ' + fmt(precio);
  recetaActual = [];

  // Cargar receta existente
  const r = await fetch(`${BASE}/api/restaurant/almacen.php?entity=recetas&producto_id=${pid}`);
  const d = await r.json();
  if (d.success) {
    recetaActual = d.data.items.map(i=>({
      insumo_id:    parseInt(i.insumo_id),
      insumo_nombre:i.insumo_nombre,
      unidad:       i.unidad,
      precio_unit:  parseFloat(i.precio_unitario),
      cantidad:     parseFloat(i.cantidad_porcion),
    }));
  }
  renderReceta();
  document.getElementById('recPanel').classList.add('open');
  document.getElementById('recOv').classList.add('show');
}

function cerrarReceta() {
  document.getElementById('recPanel').classList.remove('open');
  document.getElementById('recOv').classList.remove('show');
  platoActual = null; recetaActual = [];
}

function renderReceta() {
  const cont = document.getElementById('rpIngredientes');
  if (!recetaActual.length) {
    cont.innerHTML = `<div style="text-align:center;padding:32px;color:var(--text-secondary);font-size:13px;"><i class="fas fa-flask" style="font-size:28px;opacity:.2;display:block;margin-bottom:10px;"></i>Sin ingredientes todavía</div>`;
    actualizarCostoReceta();
    return;
  }
  cont.innerHTML = recetaActual.map((ing,i)=>`
    <div class="rec-row">
      <div class="rec-nombre">${esc(ing.insumo_nombre)}</div>
      <div class="rec-cant">${num(ing.cantidad)} ${ing.unidad}</div>
      <div class="rec-costo">${fmt(ing.cantidad * ing.precio_unit)}</div>
      <button class="rec-del" onclick="quitarIngrediente(${i})"><i class="fas fa-times"></i></button>
    </div>`).join('');
  actualizarCostoReceta();
}

function actualizarCostoReceta() {
  const total = recetaActual.reduce((s,i)=>s+(i.cantidad*i.precio_unit),0);
  document.getElementById('rpCostoTotal').textContent = fmt(total);
}

function poblarSelectInsumos() {
  const sel = document.getElementById('rpInsumo');
  sel.innerHTML = '<option value="">Seleccionar insumo…</option>' +
    insumos.map(i=>`<option value="${i.id}" data-precio="${i.precio_unitario}" data-unidad="${esc(i.unidad)}">${esc(i.nombre)} (${esc(i.unidad)})</option>`).join('');
}

function agregarIngrediente() {
  const sel   = document.getElementById('rpInsumo');
  const iid   = parseInt(sel.value);
  const cant  = parseFloat(document.getElementById('rpCant').value);
  if (!iid || isNaN(cant) || cant<=0) { showToast('Seleccioná insumo y cantidad válida','error'); return; }
  const opt   = sel.options[sel.selectedIndex];
  const precio= parseFloat(opt.dataset.precio)||0;
  const unidad= opt.dataset.unidad||'';
  const nombre= opt.text.split(' (')[0];

  // Si ya existe, actualizar cantidad
  const existe = recetaActual.findIndex(x=>x.insumo_id===iid);
  if (existe>=0) { recetaActual[existe].cantidad = cant; }
  else { recetaActual.push({insumo_id:iid,insumo_nombre:nombre,unidad,precio_unit:precio,cantidad:cant}); }

  document.getElementById('rpCant').value = '';
  sel.value = '';
  renderReceta();
}

function quitarIngrediente(idx) {
  recetaActual.splice(idx,1);
  renderReceta();
}

async function guardarReceta() {
  if (!platoActual) return;
  const r = await fetch(`${BASE}/api/restaurant/almacen.php?entity=recetas`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({
      producto_id: platoActual.id,
      ingredientes: recetaActual.map(i=>({insumo_id:i.insumo_id, cantidad_porcion:i.cantidad}))
    })
  });
  const d = await r.json();
  if (d.success) {
    showToast('Receta guardada','success');
    cerrarReceta();
    cargarCostos();
  } else { showToast(d.message||'Error','error'); }
}

// ── Modal Insumo ───────────────────────────────────
function abrirModalInsumo() {
  document.getElementById('mi-titulo').textContent = 'Nuevo insumo';
  document.getElementById('mi-id').value = '';
  ['mi-nombre','mi-cat','mi-precio','mi-stock','mi-min'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('mi-unidad').value = 'kg';
  document.getElementById('modalInsumo').classList.add('show');
}

async function editarInsumo(id) {
  const ins = insumos.find(i=>i.id==id);
  if (!ins) return;
  document.getElementById('mi-titulo').textContent = 'Editar insumo';
  document.getElementById('mi-id').value     = ins.id;
  document.getElementById('mi-nombre').value = ins.nombre;
  document.getElementById('mi-cat').value    = ins.categoria||'';
  document.getElementById('mi-unidad').value = ins.unidad;
  document.getElementById('mi-precio').value = ins.precio_unitario;
  document.getElementById('mi-stock').value  = ins.stock_actual;
  document.getElementById('mi-min').value    = ins.stock_minimo;
  document.getElementById('modalInsumo').classList.add('show');
}

async function guardarInsumo() {
  const id     = document.getElementById('mi-id').value;
  const nombre = document.getElementById('mi-nombre').value.trim();
  if (!nombre) { showToast('Ingresá el nombre','error'); return; }
  const body = {
    nombre, categoria: document.getElementById('mi-cat').value.trim(),
    unidad: document.getElementById('mi-unidad').value,
    precio_unitario: parseFloat(document.getElementById('mi-precio').value)||0,
    stock_actual:    parseFloat(document.getElementById('mi-stock').value)||0,
    stock_minimo:    parseFloat(document.getElementById('mi-min').value)||0,
  };
  const url    = `${BASE}/api/restaurant/almacen.php?entity=insumos${id?'&id='+id:''}`;
  const method = id ? 'PUT' : 'POST';
  const r      = await fetch(url,{method,headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
  const d      = await r.json();
  if (d.success) {
    cerrarModal('modalInsumo');
    showToast(id?'Insumo actualizado':'Insumo creado','success');
    await cargarInsumos();
    await cargarResumen();
  } else { showToast(d.message||'Error','error'); }
}

async function confirmarEliminarInsumo(id, nombre) {
  if (!confirm(`¿Eliminar insumo "${nombre}"?`)) return;
  await fetch(`${BASE}/api/restaurant/almacen.php?entity=insumos&id=${id}`,{method:'DELETE'});
  showToast('Insumo eliminado','success');
  await cargarInsumos();
  await cargarResumen();
}

// ── Modal Compra ───────────────────────────────────
function abrirModalCompra() {
  const sel = document.getElementById('mc-insumo');
  sel.innerHTML = insumos.map(i=>`<option value="${i.id}" data-precio="${i.precio_unitario}">${esc(i.nombre)} (${esc(i.unidad)})</option>`).join('');
  document.getElementById('mc-fecha').value = new Date().toISOString().slice(0,10);
  ['mc-cant','mc-prov','mc-notas'].forEach(id=>document.getElementById(id).value='');
  actualizarPrecioRef();
  document.getElementById('modalCompra').classList.add('show');
}

function actualizarPrecioRef() {
  const sel = document.getElementById('mc-insumo');
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('mc-precio').value = opt?.dataset.precio||'';
  calcTotal();
}

function calcTotal() {
  const c = parseFloat(document.getElementById('mc-cant').value)||0;
  const p = parseFloat(document.getElementById('mc-precio').value)||0;
  document.getElementById('mc-total').textContent = fmt(c*p);
}

async function guardarCompra() {
  const iid   = document.getElementById('mc-insumo').value;
  const cant  = parseFloat(document.getElementById('mc-cant').value);
  const precio= parseFloat(document.getElementById('mc-precio').value);
  const fecha = document.getElementById('mc-fecha').value;
  if (!iid||isNaN(cant)||cant<=0||isNaN(precio)||precio<0||!fecha) {
    showToast('Completá todos los campos obligatorios','error'); return;
  }
  const r = await fetch(`${BASE}/api/restaurant/almacen.php?entity=compras`,{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({insumo_id:iid,cantidad:cant,precio_unitario:precio,fecha,
      proveedor:document.getElementById('mc-prov').value,
      notas:document.getElementById('mc-notas').value})
  });
  const d = await r.json();
  if (d.success) {
    cerrarModal('modalCompra');
    showToast('Compra registrada: '+fmt(d.data.total),'success');
    await cargarInsumos();
    await cargarResumen();
    if (document.getElementById('panel-compras').classList.contains('on')) cargarCompras();
  } else { showToast(d.message||'Error','error'); }
}

async function eliminarCompra(id) {
  if (!confirm('¿Eliminar esta compra? Se revertirá el stock.')) return;
  await fetch(`${BASE}/api/restaurant/almacen.php?entity=compras&id=${id}`,{method:'DELETE'});
  showToast('Compra eliminada','success');
  cargarCompras(); cargarResumen(); cargarInsumos();
}

// ── Helpers ────────────────────────────────────────
function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
function fmt(n){ return '$'+Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function num(n){ return parseFloat(n||0).toLocaleString('es-AR',{maximumFractionDigits:3}); }
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function showToast(msg,type='success'){
  const t=document.createElement('div');
  t.style.cssText=`position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
    background:${type==='success'?'#0FD186':'#F56565'};color:#fff;
    padding:12px 24px;border-radius:10px;font-weight:600;font-size:14px;
    z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2);white-space:nowrap;`;
  t.textContent=msg;
  document.body.appendChild(t);
  setTimeout(()=>t.remove(),3500);
}

// Inicializar filtro de fechas (mes actual)
const hoy=new Date();
document.getElementById('filtroDesde').value=hoy.getFullYear()+'-'+(hoy.getMonth()+1+'').padStart(2,'0')+'-01';
document.getElementById('filtroHasta').value=hoy.toISOString().slice(0,10);

init();
</script>
</body>
</html>
