<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Carta Digital</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ══════════════════════════════════════════════
   RESET & BASE
══════════════════════════════════════════════ */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
:root {
  --brand:  #FF7A30;
  --dark:   #1a1a2e;
  --bg:     #f8f9fb;
  --card:   #ffffff;
  --text:   #1e293b;
  --muted:  #64748b;
  --border: #e2e8f0;
  --radius: 14px;
}
html { scroll-behavior: smooth; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg); color: var(--text);
  min-height: 100vh; padding-bottom: 60px;
}

/* ══════════════════════════════════════════════
   SPLASH
══════════════════════════════════════════════ */
#splash {
  position: fixed; inset: 0; z-index: 999;
  background: var(--dark);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 20px;
  transition: opacity .4s, visibility .4s;
}
#splash.hide { opacity: 0; visibility: hidden; }
.splash-icon {
  width: 72px; height: 72px; border-radius: 20px;
  background: linear-gradient(135deg, var(--brand), #ff5500);
  display: flex; align-items: center; justify-content: center;
  font-size: 32px; color: white;
  box-shadow: 0 20px 60px rgba(255,122,48,.4);
}
.splash-dots { display: flex; gap: 8px; }
.splash-dot { width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.3); animation: dot 1.2s infinite; }
.splash-dot:nth-child(2) { animation-delay:.2s; }
.splash-dot:nth-child(3) { animation-delay:.4s; }
@keyframes dot { 0%,80%,100%{transform:scale(.8);opacity:.3} 40%{transform:scale(1.2);opacity:1} }

/* ══════════════════════════════════════════════
   HEADER DEL NEGOCIO
══════════════════════════════════════════════ */
.negocio-header {
  background: linear-gradient(135deg, var(--dark) 0%, #16213e 100%);
  color: white; padding: 32px 20px 80px;
  text-align: center; position: relative; overflow: hidden;
}
.negocio-header::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(circle at 70% 50%, rgba(255,122,48,.18), transparent 60%);
}
.negocio-logo {
  width: 82px; height: 82px; border-radius: 22px;
  margin: 0 auto 16px; background: white;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,.3);
  position: relative; z-index: 1;
}
.negocio-logo img { width:100%; height:100%; object-fit:contain; padding:8px; }
.negocio-logo-ph { font-size:38px; }
.negocio-nombre { font-size:26px; font-weight:800; margin-bottom:6px; position:relative; z-index:1; }
.negocio-slogan { font-size:14px; opacity:.6; position:relative; z-index:1; }
.negocio-horario {
  display: inline-flex; align-items: center; gap: 6px;
  margin-top: 10px; font-size: 12px; opacity: .5;
  position: relative; z-index: 1;
}

/* ══════════════════════════════════════════════
   CATEGORÍAS (sticky)
══════════════════════════════════════════════ */
.cats-wrapper {
  position: sticky; top: 0; z-index: 50;
  background: white; border-bottom: 1px solid var(--border);
  transform: translateY(-40px);
  box-shadow: 0 4px 20px rgba(0,0,0,.07);
}
.cats-scroll {
  display: flex; gap: 8px;
  overflow-x: auto; padding: 14px 16px;
  scrollbar-width: none;
}
.cats-scroll::-webkit-scrollbar { display:none; }
.cat-pill {
  flex-shrink: 0; padding: 8px 16px; border-radius: 99px;
  font-size: 13px; font-weight: 600;
  border: 1.5px solid var(--border);
  background: white; color: var(--muted);
  cursor: pointer; transition: all .2s; white-space: nowrap;
}
.cat-pill:hover { border-color: var(--brand); color: var(--brand); }
.cat-pill.active {
  background: var(--brand); color: white; border-color: var(--brand);
  box-shadow: 0 4px 12px rgba(255,122,48,.3);
}

/* ══════════════════════════════════════════════
   BUSCADOR
══════════════════════════════════════════════ */
.search-wrap { padding: 0 16px 16px; background: white; transform: translateY(-40px); }
.search-input-wrap { position: relative; }
.search-input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:14px; }
.search-input-wrap input {
  width: 100%; padding: 11px 14px 11px 40px;
  border: 1.5px solid var(--border); border-radius: 12px;
  font-size: 14px; color: var(--text);
  background: var(--bg); transition: border-color .2s;
}
.search-input-wrap input:focus { outline: none; border-color: var(--brand); background: white; }

/* ══════════════════════════════════════════════
   CONTENIDO
══════════════════════════════════════════════ */
.main-content { transform: translateY(-40px); }

.cat-section { padding: 0 16px 8px; }
.cat-section-header {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 0 12px;
}
.cat-section-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.cat-section-title { font-size:17px; font-weight:800; }
.cat-section-count {
  font-size:12px; color:var(--muted);
  background:var(--border); padding:2px 8px; border-radius:99px;
}

/* ══════════════════════════════════════════════
   GRID DE PLATOS
══════════════════════════════════════════════ */
.platos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
  gap: 12px; margin-bottom: 8px;
}
@media (min-width:540px)  { .platos-grid { grid-template-columns: repeat(auto-fill, minmax(200px,1fr)); } }
@media (min-width:900px)  {
  .platos-grid { grid-template-columns: repeat(auto-fill, minmax(240px,1fr)); }
  .cat-section { padding: 0 32px 8px; }
  .cats-scroll { padding: 14px 32px; }
  .search-wrap { padding: 0 32px 16px; }
}

/* ══════════════════════════════════════════════
   CARD DE PLATO
══════════════════════════════════════════════ */
.plato-card {
  background: var(--card); border-radius: var(--radius);
  border: 1px solid var(--border); overflow: hidden;
  cursor: pointer; transition: transform .2s, box-shadow .2s;
  display: flex; flex-direction: column;
  -webkit-tap-highlight-color: transparent;
}
.plato-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.1); }
.plato-img {
  aspect-ratio: 4/3; background: #f1f5f9;
  display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.plato-img img { width:100%; height:100%; object-fit:cover; }
.plato-img-ph { font-size:40px; opacity:.18; }
.plato-body { padding:12px; flex:1; display:flex; flex-direction:column; gap:4px; }
.plato-nombre { font-size:14px; font-weight:700; color:var(--text); line-height:1.3; }
.plato-desc {
  font-size:12px; color:var(--muted); line-height:1.4; flex:1;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.plato-precio { font-size:17px; font-weight:800; color:var(--brand); margin-top:8px; }

/* ══════════════════════════════════════════════
   MODAL DETALLE
══════════════════════════════════════════════ */
.modal-overlay {
  position:fixed; inset:0; z-index:200;
  background:rgba(0,0,0,.55); backdrop-filter:blur(4px);
  display:flex; align-items:flex-end; justify-content:center;
  opacity:0; visibility:hidden; transition:all .25s;
}
.modal-overlay.open { opacity:1; visibility:visible; }
.modal-sheet {
  background:white; width:100%; max-width:560px;
  border-radius:24px 24px 0 0; max-height:90vh; overflow-y:auto;
  transform:translateY(100%); transition:transform .3s cubic-bezier(.32,0,.15,1);
}
@media (min-width:600px) {
  .modal-overlay { align-items:center; }
  .modal-sheet { border-radius:20px; max-height:80vh; transform:translateY(20px) scale(.97); }
}
.modal-overlay.open .modal-sheet { transform:translateY(0) scale(1); }
.modal-drag { width:40px; height:4px; border-radius:99px; background:var(--border); margin:14px auto 0; }
.modal-img { width:100%; aspect-ratio:16/9; object-fit:cover; display:block; }
.modal-img-ph {
  width:100%; padding:48px 0;
  background:#f1f5f9; display:flex; align-items:center; justify-content:center;
  font-size:64px; opacity:.15;
}
.modal-body { padding:20px; }
.modal-cat-badge {
  display:inline-block; padding:3px 10px; border-radius:99px;
  font-size:11px; font-weight:700; color:white; margin-bottom:10px;
}
.modal-nombre { font-size:22px; font-weight:800; color:var(--text); margin-bottom:8px; }
.modal-desc   { font-size:14px; color:var(--muted); line-height:1.6; margin-bottom:18px; }
.modal-precio { font-size:28px; font-weight:900; color:var(--brand); }
.modal-img-wrap { position:relative; }
.modal-close {
  position:absolute; top:12px; right:12px;
  width:34px; height:34px; border-radius:50%;
  background:rgba(0,0,0,.5); border:none; cursor:pointer;
  color:white; font-size:13px;
  display:flex; align-items:center; justify-content:center;
}

/* ══════════════════════════════════════════════
   ESTADOS VACÍOS
══════════════════════════════════════════════ */
.empty-state { text-align:center; padding:60px 20px; color:var(--muted); }
.empty-state i { font-size:48px; opacity:.2; margin-bottom:16px; display:block; }
.empty-state h3 { font-size:18px; font-weight:700; color:var(--text); margin-bottom:8px; }

/* ══════════════════════════════════════════════
   FOOTER
══════════════════════════════════════════════ */
.carta-footer { text-align:center; padding:24px; font-size:11px; color:var(--muted); transform:translateY(-40px); }
.carta-footer a { color:var(--brand); text-decoration:none; font-weight:600; }
</style>
</head>
<body>

<!-- Splash -->
<div id="splash">
  <div class="splash-icon"><i class="fas fa-utensils"></i></div>
  <div class="splash-dots">
    <div class="splash-dot"></div><div class="splash-dot"></div><div class="splash-dot"></div>
  </div>
</div>

<!-- Header negocio -->
<header class="negocio-header">
  <div class="negocio-logo" id="negocioLogo"><span class="negocio-logo-ph">🍽️</span></div>
  <div class="negocio-nombre" id="negocioNombre">Cargando…</div>
  <div id="negocioSlogan" class="negocio-slogan"></div>
  <div id="negocioHorario" class="negocio-horario" style="display:none;">
    <i class="fas fa-clock"></i><span id="horarioText"></span>
  </div>
</header>

<!-- Pills categorías -->
<div class="cats-wrapper">
  <div class="cats-scroll" id="catsPills">
    <button class="cat-pill active" onclick="filtrarCat('todos')">🍽️ Todo el menú</button>
  </div>
</div>

<!-- Buscador -->
<div class="search-wrap">
  <div class="search-input-wrap">
    <i class="fas fa-search"></i>
    <input type="search" id="buscador" placeholder="Buscar en el menú…" oninput="buscar(this.value)">
  </div>
</div>

<!-- Platos -->
<main class="main-content" id="mainContent"></main>

<!-- Footer -->
<div class="carta-footer">
  Menú digital por <a href="/" target="_blank">DASH</a>
</div>

<!-- Modal detalle -->
<div class="modal-overlay" id="modalDetalle" onclick="if(event.target===this)cerrarModal()">
  <div class="modal-sheet">
    <div class="modal-drag"></div>
    <div class="modal-img-wrap" id="modalImgWrap"></div>
    <div class="modal-body">
      <div id="modalBadge"  class="modal-cat-badge"></div>
      <div id="modalNombre" class="modal-nombre"></div>
      <div id="modalDesc"   class="modal-desc"></div>
      <div id="modalPrecio" class="modal-precio"></div>
    </div>
  </div>
</div>

<script>
// ─── Config ───────────────────────────────────
const params    = new URLSearchParams(location.search);
const negocioId = params.get('negocio_id') || '';

// Detecta la ruta base automáticamente
const BASE_UPLOADS = location.origin + '/DASHBASE/public/uploads/productos/';
const BASE_LOGOS   = location.origin + '/DASHBASE/public/uploads/negocios/';
const BASE_API     = location.origin + '/DASHBASE/api/restaurant/carta-publica.php';

let todosPlatos     = [];
let todasCategorias = [];
let catActual       = 'todos';

// ─── Init ──────────────────────────────────────
async function init() {
  if (!negocioId) {
    hideSplash();
    mostrarError('Enlace inválido', 'Usá el QR del restaurante para acceder.');
    return;
  }
  try {
    const r = await fetch(`${BASE_API}?negocio_id=${negocioId}`);
    const d = await r.json();
    if (!d.success) { hideSplash(); mostrarError('No disponible', d.message || ''); return; }

    todosPlatos     = d.platos     || [];
    todasCategorias = d.categorias || [];

    renderHeader(d.negocio);
    renderPills();
    renderPlatos(todosPlatos);
  } catch(e) {
    mostrarError('Error de conexión', 'Revisá tu internet e intentá de nuevo.');
  } finally {
    hideSplash();
  }
}

function hideSplash() {
  setTimeout(() => document.getElementById('splash').classList.add('hide'), 400);
}

// ─── Header ───────────────────────────────────
function renderHeader(neg) {
  document.title = `${neg.nombre} — Menú`;
  if (neg.color_primario) document.documentElement.style.setProperty('--brand', neg.color_primario);

  const logoEl = document.getElementById('negocioLogo');
  if (neg.logo) {
    logoEl.innerHTML = `<img src="${BASE_LOGOS}${esc(neg.logo)}" onerror="this.parentElement.innerHTML='<span class=\\'negocio-logo-ph\\'>🍽️</span>'">`;
  }
  document.getElementById('negocioNombre').textContent = neg.nombre;
  if (neg.slogan) document.getElementById('negocioSlogan').textContent = neg.slogan;
  if (neg.horario_inicio && neg.horario_cierre) {
    document.getElementById('negocioHorario').style.display = 'inline-flex';
    document.getElementById('horarioText').textContent =
      `${neg.horario_inicio.slice(0,5)} – ${neg.horario_cierre.slice(0,5)}`;
  }
}

// ─── Pills ────────────────────────────────────
function renderPills() {
  let html = `<button class="cat-pill active" data-cat="todos" onclick="filtrarCat('todos')">🍽️ Todo el menú</button>`;
  todasCategorias.forEach(c => {
    html += `<button class="cat-pill" data-cat="${c.id}" onclick="filtrarCat(${c.id})">${c.icono ? c.icono+' ' : ''}${esc(c.nombre)}</button>`;
  });
  document.getElementById('catsPills').innerHTML = html;
}

// ─── Render platos ────────────────────────────
function renderPlatos(platos) {
  const main = document.getElementById('mainContent');
  if (!platos.length) {
    main.innerHTML = `<div class="empty-state" style="transform:translateY(0);">
      <i class="fas fa-search"></i><h3>Sin resultados</h3>
      <p>Probá con otro término</p></div>`;
    return;
  }

  const grupos = {};
  const sinCat = [];
  platos.forEach(p => p.categoria_id ? (grupos[p.categoria_id] = grupos[p.categoria_id] || [], grupos[p.categoria_id].push(p)) : sinCat.push(p));

  let html = '';
  todasCategorias.forEach(cat => {
    const items = grupos[cat.id];
    if (!items?.length) return;
    html += seccionHTML(cat.nombre, cat.color || '#64748b', cat.id, items);
  });
  if (sinCat.length) html += seccionHTML('Otros', '#94a3b8', 'otros', sinCat);

  main.innerHTML = html;
}

function seccionHTML(nombre, color, id, platos) {
  return `<div class="cat-section" id="sec-${id}">
    <div class="cat-section-header">
      <div class="cat-section-dot" style="background:${color}"></div>
      <div class="cat-section-title">${esc(nombre)}</div>
      <div class="cat-section-count">${platos.length}</div>
    </div>
    <div class="platos-grid">${platos.map(tarjetaHTML).join('')}</div>
  </div>`;
}

function tarjetaHTML(p) {
  const img = p.foto
    ? `<img src="${BASE_UPLOADS}${esc(p.foto)}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\\'plato-img-ph\\'>🍽️</div>'">`
    : `<div class="plato-img-ph">🍽️</div>`;
  return `<div class="plato-card" onclick="abrirDetalle(${p.id})">
    <div class="plato-img">${img}</div>
    <div class="plato-body">
      <div class="plato-nombre">${esc(p.nombre)}</div>
      ${p.descripcion ? `<div class="plato-desc">${esc(p.descripcion)}</div>` : ''}
      <div class="plato-precio">${fmt(p.precio_venta)}</div>
    </div>
  </div>`;
}

// ─── Filtro categoría ─────────────────────────
function filtrarCat(catId) {
  catActual = catId;
  document.querySelectorAll('.cat-pill').forEach(b => b.classList.toggle('active', b.dataset.cat == catId));
  const filtrados = catId === 'todos' ? todosPlatos
    : todosPlatos.filter(p => catId === 'otros' ? !p.categoria_id : p.categoria_id == catId);
  renderPlatos(filtrados);
  if (catId !== 'todos') {
    const sec = document.getElementById(`sec-${catId}`);
    if (sec) setTimeout(() => sec.scrollIntoView({ behavior:'smooth', block:'start' }), 50);
  }
}

// ─── Buscador ─────────────────────────────────
function buscar(q) {
  const term = q.toLowerCase().trim();
  if (!term) { renderPlatos(catActual === 'todos' ? todosPlatos : todosPlatos.filter(p => p.categoria_id == catActual)); return; }
  renderPlatos(todosPlatos.filter(p =>
    p.nombre.toLowerCase().includes(term) ||
    (p.descripcion || '').toLowerCase().includes(term) ||
    (p.categoria_nombre || '').toLowerCase().includes(term)
  ));
}

// ─── Modal detalle ────────────────────────────
function abrirDetalle(id) {
  const p = todosPlatos.find(x => x.id == id);
  if (!p) return;

  const imgWrap = document.getElementById('modalImgWrap');
  const closeBtn = `<button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>`;
  imgWrap.innerHTML = p.foto
    ? `<img class="modal-img" src="${BASE_UPLOADS}${esc(p.foto)}" onerror="this.parentElement.innerHTML='<div class=\\'modal-img-ph\\'>🍽️</div>${closeBtn}'">${closeBtn}`
    : `<div class="modal-img-ph">🍽️</div>${closeBtn}`;

  const badge = document.getElementById('modalBadge');
  if (p.categoria_nombre) {
    badge.textContent = p.categoria_nombre;
    badge.style.background = p.categoria_color || '#64748b';
    badge.style.display = 'inline-block';
  } else { badge.style.display = 'none'; }

  document.getElementById('modalNombre').textContent = p.nombre;
  const descEl = document.getElementById('modalDesc');
  descEl.textContent   = p.descripcion || '';
  descEl.style.display = p.descripcion ? 'block' : 'none';
  document.getElementById('modalPrecio').textContent = fmt(p.precio_venta);

  document.getElementById('modalDetalle').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function cerrarModal() {
  document.getElementById('modalDetalle').classList.remove('open');
  document.body.style.overflow = '';
}

// ─── Error state ──────────────────────────────
function mostrarError(titulo, msg) {
  document.getElementById('mainContent').innerHTML = `
    <div class="empty-state" style="transform:translateY(0);padding-top:80px;">
      <i class="fas fa-exclamation-circle" style="color:#ef4444;opacity:.7;"></i>
      <h3>${esc(titulo)}</h3><p>${esc(msg)}</p>
    </div>`;
}

// ─── Helpers ──────────────────────────────────
function fmt(n) { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.addEventListener('keydown', e => { if (e.key==='Escape') cerrarModal(); });

init();
</script>
</body>
</html>
