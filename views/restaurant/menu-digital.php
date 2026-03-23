<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Menú Digital</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --brand:#FF7A30;--brand2:#ff5500;
  --dark:#111827;--dark2:#1f2937;
  --bg:#f9fafb;--surface:#fff;
  --text:#111827;--muted:#6b7280;--muted2:#9ca3af;
  --border:#f3f4f6;--border2:#e5e7eb;
  --r:16px;--r2:24px;
  --shadow:0 2px 12px rgba(0,0,0,.08);
  --shadow2:0 8px 32px rgba(0,0,0,.14);
  --trans:.2s cubic-bezier(.4,0,.2,1);
}
html{scroll-behavior:smooth}
body{
  font-family:'Outfit',sans-serif;
  background:var(--bg);color:var(--text);
  min-height:100vh;overflow-x:hidden;
}

/* ══════════ SPLASH ══════════ */
#splash{
  position:fixed;inset:0;z-index:1000;
  background:var(--dark);
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:28px;
  transition:opacity .5s,visibility .5s;
}
#splash.out{opacity:0;visibility:hidden}
.splash-logo{
  width:80px;height:80px;border-radius:24px;
  background:linear-gradient(135deg,var(--brand),var(--brand2));
  display:flex;align-items:center;justify-content:center;
  font-size:36px;color:#fff;
  box-shadow:0 0 0 12px rgba(255,122,48,.12),0 0 0 24px rgba(255,122,48,.06);
  animation:pulse 2s infinite;
}
@keyframes pulse{0%,100%{box-shadow:0 0 0 12px rgba(255,122,48,.12),0 0 0 24px rgba(255,122,48,.06)}50%{box-shadow:0 0 0 16px rgba(255,122,48,.18),0 0 0 32px rgba(255,122,48,.08)}}
.splash-bar{width:160px;height:3px;background:rgba(255,255,255,.1);border-radius:99px;overflow:hidden}
.splash-fill{height:100%;background:linear-gradient(90deg,var(--brand),var(--brand2));border-radius:99px;width:0;animation:load 1.2s ease forwards}
@keyframes load{to{width:100%}}

/* ══════════ HERO ══════════ */
.hero{position:relative;overflow:hidden}
.hero-img{
  width:100%;height:260px;object-fit:cover;display:block;
  filter:brightness(.75);
  transition:filter .3s;
}
.hero-gradient{
  position:absolute;inset:0;
  background:linear-gradient(180deg,rgba(0,0,0,.2) 0%,rgba(0,0,0,.7) 100%);
}
.hero-ph{
  width:100%;height:260px;
  background:linear-gradient(135deg,var(--dark) 0%,var(--dark2) 60%,#1e3a5f 100%);
  position:relative;
}
.hero-ph::after{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at 70% 40%,rgba(255,122,48,.25),transparent 65%);
}
@media(min-width:768px){.hero-img,.hero-ph{height:340px}}

/* Logo y nombre dentro del hero */
.hero-content{
  position:absolute;bottom:0;left:0;right:0;
  padding:24px 20px 28px;
  display:flex;align-items:flex-end;gap:16px;
}
.hero-logo{
  width:76px;height:76px;border-radius:20px;
  background:#fff;border:3px solid rgba(255,255,255,.9);
  box-shadow:0 8px 24px rgba(0,0,0,.3);
  overflow:hidden;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.hero-logo img{width:100%;height:100%;object-fit:contain;padding:6px}
.hero-logo-ph{font-size:34px}
.hero-text{color:#fff;flex:1;min-width:0}
.hero-nombre{font-size:22px;font-weight:800;line-height:1.2;text-shadow:0 2px 8px rgba(0,0,0,.4)}
.hero-slogan{font-size:13px;opacity:.8;margin-top:3px;font-weight:400}

/* ══════════ INFO BAR ══════════ */
.info-bar{
  background:#fff;
  padding:16px 20px;
  display:flex;flex-direction:column;gap:12px;
  border-bottom:1px solid var(--border2);
}
.info-chips{display:flex;flex-wrap:wrap;gap:8px}
.info-chip{
  display:inline-flex;align-items:center;gap:6px;
  font-size:12px;font-weight:500;color:var(--muted);
  background:var(--bg);border:1px solid var(--border2);
  padding:5px 12px;border-radius:99px;
}
.info-chip i{font-size:11px;color:var(--brand)}
.contact-row{display:flex;flex-wrap:wrap;gap:8px}
.contact-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;border-radius:12px;
  font-size:13px;font-weight:700;
  text-decoration:none;border:none;cursor:pointer;
  letter-spacing:.01em;
  transition:filter .15s,transform .15s;
}
.contact-btn:hover{filter:brightness(1.1);transform:translateY(-1px)}
.c-wsp{background:#22c55e;color:#fff}
.c-ig{background:linear-gradient(135deg,#f59e0b,#ef4444,#9333ea);color:#fff}
.c-tel{background:var(--dark);color:#fff}
.c-web{background:var(--brand);color:#fff}

/* ══════════ STICKY NAV ══════════ */
.nav-sticky{
  position:sticky;top:0;z-index:100;
  background:#fff;
  border-bottom:1px solid var(--border2);
  box-shadow:0 2px 16px rgba(0,0,0,.06);
}
.nav-top{
  display:flex;align-items:center;gap:10px;
  padding:12px 16px;
  border-bottom:1px solid var(--border);
}
.search-box{
  flex:1;position:relative;
}
.search-box i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted2);font-size:13px}
.search-box input{
  width:100%;padding:9px 14px 9px 36px;
  border:1.5px solid var(--border2);border-radius:12px;
  font-size:14px;font-family:'Outfit',sans-serif;
  background:var(--bg);color:var(--text);
  transition:border-color var(--trans),background var(--trans);
}
.search-box input:focus{outline:none;border-color:var(--brand);background:#fff}
.toggle-layout{
  width:38px;height:38px;border-radius:10px;
  border:1.5px solid var(--border2);background:#fff;
  cursor:pointer;color:var(--muted);font-size:14px;
  display:flex;align-items:center;justify-content:center;
  transition:all var(--trans);flex-shrink:0;
}
.toggle-layout:hover,.toggle-layout.active{border-color:var(--brand);color:var(--brand)}

.cats-scroll{
  display:flex;gap:6px;
  overflow-x:auto;padding:10px 16px;
  scrollbar-width:none;
}
.cats-scroll::-webkit-scrollbar{display:none}
.cat-btn{
  flex-shrink:0;display:inline-flex;align-items:center;gap:6px;
  padding:7px 16px;border-radius:99px;
  font-size:13px;font-weight:600;font-family:'Outfit',sans-serif;
  border:1.5px solid var(--border2);
  background:#fff;color:var(--muted);
  cursor:pointer;white-space:nowrap;
  transition:all var(--trans);
}
.cat-btn:hover{border-color:var(--brand);color:var(--brand)}
.cat-btn.on{
  background:var(--brand);color:#fff;border-color:var(--brand);
  box-shadow:0 4px 14px rgba(255,122,48,.35);
}

/* ══════════ MAIN ══════════ */
.main{padding:8px 0 80px}

.sec{padding:0 16px 4px}
@media(min-width:768px){.sec{padding:0 28px 4px}}

.sec-head{
  display:flex;align-items:center;gap:10px;
  padding:20px 0 12px;
}
.sec-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.sec-title{font-size:18px;font-weight:800}
.sec-count{
  font-size:12px;color:var(--muted);
  background:var(--border2);padding:2px 9px;border-radius:99px;font-weight:600;
}

/* ── GRID ── */
.platos-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
  gap:14px;margin-bottom:8px;
}
@media(min-width:480px){.platos-grid{grid-template-columns:repeat(auto-fill,minmax(190px,1fr))}}
@media(min-width:768px){.platos-grid{grid-template-columns:repeat(auto-fill,minmax(220px,1fr))}}

.card-g{
  background:#fff;border-radius:var(--r2);
  border:1px solid var(--border);overflow:hidden;
  cursor:pointer;
  transition:transform var(--trans),box-shadow var(--trans);
  display:flex;flex-direction:column;
  -webkit-tap-highlight-color:transparent;
}
.card-g:hover{transform:translateY(-4px);box-shadow:var(--shadow2)}
.card-g-img{
  aspect-ratio:4/3;overflow:hidden;
  background:#f3f4f6;
  display:flex;align-items:center;justify-content:center;
  position:relative;
}
.card-g-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s}
.card-g:hover .card-g-img img{transform:scale(1.06)}
.card-g-img-ph{font-size:44px;opacity:.15}
.card-g-badge{
  position:absolute;top:10px;left:10px;
  font-size:10px;font-weight:700;color:#fff;
  padding:3px 9px;border-radius:99px;
}
.card-g-body{padding:14px;flex:1;display:flex;flex-direction:column;gap:5px}
.card-g-cat{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.card-g-name{font-size:15px;font-weight:700;line-height:1.3}
.card-g-desc{
  font-size:12px;color:var(--muted);line-height:1.5;flex:1;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.card-g-foot{display:flex;align-items:center;justify-content:space-between;margin-top:10px}
.card-g-price{font-size:19px;font-weight:800;color:var(--brand)}
.card-g-icon{
  width:32px;height:32px;border-radius:10px;
  background:linear-gradient(135deg,var(--brand),var(--brand2));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:13px;
  box-shadow:0 4px 10px rgba(255,122,48,.35);
}

/* ── LISTA ── */
.platos-list{display:flex;flex-direction:column;gap:10px;margin-bottom:8px}
.card-l{
  background:#fff;border-radius:var(--r);
  border:1px solid var(--border);overflow:hidden;
  cursor:pointer;display:flex;align-items:center;gap:0;
  transition:transform var(--trans),box-shadow var(--trans);
  -webkit-tap-highlight-color:transparent;
}
.card-l:hover{transform:translateX(4px);box-shadow:var(--shadow)}
.card-l-img{
  width:100px;height:90px;flex-shrink:0;overflow:hidden;
  background:#f3f4f6;display:flex;align-items:center;justify-content:center;
}
.card-l-img img{width:100%;height:100%;object-fit:cover}
.card-l-img-ph{font-size:28px;opacity:.15}
.card-l-body{padding:12px 14px;flex:1;min-width:0}
.card-l-name{font-size:15px;font-weight:700;line-height:1.3;margin-bottom:3px}
.card-l-desc{
  font-size:12px;color:var(--muted);line-height:1.4;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.card-l-price{font-size:17px;font-weight:800;color:var(--brand);margin-top:8px}

/* ══════════ MODAL ══════════ */
.modal-bg{
  position:fixed;inset:0;z-index:500;
  background:rgba(0,0,0,.6);backdrop-filter:blur(6px);
  display:flex;align-items:flex-end;justify-content:center;
  opacity:0;visibility:hidden;
  transition:opacity .3s,visibility .3s;
}
.modal-bg.on{opacity:1;visibility:visible}
.modal-sheet{
  background:#fff;width:100%;max-width:600px;
  border-radius:28px 28px 0 0;
  max-height:92vh;overflow-y:auto;
  transform:translateY(100%);
  transition:transform .35s cubic-bezier(.32,0,.15,1);
}
@media(min-width:640px){
  .modal-bg{align-items:center}
  .modal-sheet{
    border-radius:24px;max-height:85vh;
    transform:scale(.95) translateY(20px);
  }
}
.modal-bg.on .modal-sheet{transform:translateY(0) scale(1)}
.modal-handle{
  width:44px;height:5px;border-radius:99px;
  background:var(--border2);margin:14px auto 0;
}
.modal-hero{position:relative}
.modal-cover{width:100%;aspect-ratio:16/9;object-fit:cover;display:block}
.modal-cover-ph{
  width:100%;padding:56px 0;background:#f3f4f6;
  display:flex;align-items:center;justify-content:center;
  font-size:72px;opacity:.12;
}
.modal-close-btn{
  position:absolute;top:14px;right:14px;
  width:38px;height:38px;border-radius:50%;
  background:rgba(0,0,0,.45);backdrop-filter:blur(6px);
  border:none;cursor:pointer;color:#fff;font-size:15px;
  display:flex;align-items:center;justify-content:center;
  transition:background var(--trans);
}
.modal-close-btn:hover{background:rgba(0,0,0,.7)}
.modal-body{padding:22px 22px 32px}
.modal-badge{
  display:inline-flex;align-items:center;gap:6px;
  font-size:11px;font-weight:700;color:#fff;
  padding:4px 12px;border-radius:99px;margin-bottom:12px;
  text-transform:uppercase;letter-spacing:.05em;
}
.modal-name{font-size:26px;font-weight:900;line-height:1.2;margin-bottom:10px}
.modal-desc{font-size:15px;color:var(--muted);line-height:1.65;margin-bottom:20px}
.modal-price-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:18px 20px;
  background:linear-gradient(135deg,rgba(255,122,48,.08),rgba(255,85,0,.04));
  border-radius:16px;border:1px solid rgba(255,122,48,.15);
}
.modal-price-label{font-size:13px;color:var(--muted);font-weight:500}
.modal-price-val{font-size:32px;font-weight:900;color:var(--brand)}

/* ══════════ EMPTY ══════════ */
.empty{text-align:center;padding:64px 24px;color:var(--muted)}
.empty i{font-size:52px;opacity:.15;margin-bottom:18px;display:block}
.empty h3{font-size:18px;font-weight:700;color:var(--text);margin-bottom:6px}
.empty p{font-size:14px}

/* ══════════ FOOTER ══════════ */
.carta-footer{
  text-align:center;padding:28px 20px;
  font-size:12px;color:var(--muted2);
  border-top:1px solid var(--border);
}
.carta-footer a{color:var(--brand);text-decoration:none;font-weight:700}

/* ══════════ ANIMACIONES DE ENTRADA ══════════ */
.fade-in{
  opacity:0;transform:translateY(16px);
  animation:fadeIn .4s forwards;
}
@keyframes fadeIn{to{opacity:1;transform:translateY(0)}}

</style>
</head>
<body>

<!-- Splash -->
<div id="splash">
  <div class="splash-logo">🍽️</div>
  <div class="splash-bar"><div class="splash-fill"></div></div>
</div>

<!-- Hero -->
<div class="hero" id="hero">
  <div class="hero-ph" id="heroBg"></div>
  <div class="hero-gradient"></div>
  <div class="hero-content">
    <div class="hero-logo" id="heroLogo"><span class="hero-logo-ph">🍽️</span></div>
    <div class="hero-text">
      <div class="hero-nombre" id="heroNombre">Cargando…</div>
      <div class="hero-slogan" id="heroSlogan"></div>
    </div>
  </div>
</div>

<!-- Info bar -->
<div class="info-bar" id="infoBar" style="display:none">
  <div class="info-chips" id="infoChips"></div>
  <div class="contact-row" id="contactRow"></div>
</div>

<!-- Sticky nav -->
<div class="nav-sticky">
  <div class="nav-top">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="search" id="searchInput" placeholder="Buscar plato…" oninput="buscar(this.value)">
    </div>
    <button class="toggle-layout" id="toggleBtn" onclick="toggleLayout()" title="Cambiar vista">
      <i class="fas fa-list" id="toggleIcon"></i>
    </button>
  </div>
  <div class="cats-scroll" id="catScroll"></div>
</div>

<!-- Contenido -->
<main class="main" id="main"></main>

<!-- Footer -->
<div class="carta-footer">Menú digital · <a href="#">DASH</a></div>

<!-- Modal -->
<div class="modal-bg" id="modal" onclick="if(event.target===this)cerrar()">
  <div class="modal-sheet" id="modalSheet">
    <div class="modal-handle"></div>
    <div class="modal-hero" id="modalHero"></div>
    <div class="modal-body">
      <div id="modalBadge" class="modal-badge"></div>
      <div id="modalName"  class="modal-name"></div>
      <div id="modalDesc"  class="modal-desc"></div>
      <div class="modal-price-row">
        <span class="modal-price-label">Precio</span>
        <span class="modal-price-val" id="modalPrice"></span>
      </div>
    </div>
  </div>
</div>

<script>
// ── Config ─────────────────────────────────────────
const qp = new URLSearchParams(location.search);
const NID = qp.get('negocio_id') || '';
const B   = location.origin + '/DASHBASE';

let platos = [], cats = [], catOn = 'all', layoutGrid = true;

// ── Init ────────────────────────────────────────────
async function init() {
  if (!NID) { hideSplash(); err('Enlace inválido','Usá el QR del restaurante.'); return; }
  try {
    const r = await fetch(`${B}/api/restaurant/carta-publica.php?negocio_id=${NID}`);
    const d = await r.json();
    if (!d.success) { hideSplash(); err('No disponible', d.message||''); return; }
    platos = d.platos || [];
    cats   = d.categorias || [];
    pintarHeader(d.negocio);
    pintarCats();
    pintar(platos);
  } catch(e) {
    err('Sin conexión','Revisá tu internet.');
  } finally { hideSplash(); }
}

function hideSplash(){
  setTimeout(()=>document.getElementById('splash').classList.add('out'),800);
}

// ── Header ──────────────────────────────────────────
function pintarHeader(n){
  document.title = n.nombre + ' · Menú';
  if (n.color_primario) document.documentElement.style.setProperty('--brand', n.color_primario);

  // Portada
  const bg = document.getElementById('heroBg');
  if (n.imagen_portada){
    bg.outerHTML = `<img class="hero-img" id="heroBg" src="${B}/public/uploads/portadas/${x(n.imagen_portada)}" onerror="this.outerHTML='<div class=hero-ph id=heroBg></div>'">`;
  }

  // Logo
  if (n.logo){
    document.getElementById('heroLogo').innerHTML =
      `<img src="${B}/public/uploads/logos/${x(n.logo)}" onerror="this.parentElement.innerHTML='<span class=hero-logo-ph>🍽️</span>'">`;
  }

  document.getElementById('heroNombre').textContent = n.nombre;
  if (n.slogan) document.getElementById('heroSlogan').textContent = n.slogan;

  // Chips info
  const chips = [];
  if (n.horario_inicio && n.horario_cierre)
    chips.push(`<span class="info-chip"><i class="fas fa-clock"></i>${n.horario_inicio.slice(0,5)} – ${n.horario_cierre.slice(0,5)}</span>`);
  if (n.ciudad || n.direccion)
    chips.push(`<span class="info-chip"><i class="fas fa-map-marker-alt"></i>${x([n.direccion,n.ciudad].filter(Boolean).join(', '))}</span>`);

  // Botones contacto
  const btns = [];
  if (n.whatsapp) btns.push(`<a class="contact-btn c-wsp" href="https://wa.me/${n.whatsapp.replace(/\D/g,'')}" target="_blank"><i class="fab fa-whatsapp"></i>WhatsApp</a>`);
  if (n.instagram) btns.push(`<a class="contact-btn c-ig" href="https://instagram.com/${n.instagram.replace(/^@/,'')}" target="_blank"><i class="fab fa-instagram"></i>Instagram</a>`);
  if (n.telefono && !n.whatsapp) btns.push(`<a class="contact-btn c-tel" href="tel:${x(n.telefono)}"><i class="fas fa-phone"></i>Llamar</a>`);
  if (n.sitio_web) btns.push(`<a class="contact-btn c-web" href="${x(n.sitio_web)}" target="_blank"><i class="fas fa-globe"></i>Web</a>`);

  const bar = document.getElementById('infoBar');
  if (chips.length || btns.length){
    if (chips.length) document.getElementById('infoChips').innerHTML = chips.join('');
    else document.getElementById('infoChips').style.display = 'none';
    if (btns.length) document.getElementById('contactRow').innerHTML = btns.join('');
    else document.getElementById('contactRow').style.display = 'none';
    bar.style.display = 'flex';
  }
}

// ── Categorías ──────────────────────────────────────
function pintarCats(){
  let html = `<button class="cat-btn on" data-id="all" onclick="filtrar('all',this)">🍽️ Todo</button>`;
  cats.forEach(c=>{
    html += `<button class="cat-btn" data-id="${c.id}" onclick="filtrar(${c.id},this)">${c.icono?c.icono+' ':''}${x(c.nombre)}</button>`;
  });
  document.getElementById('catScroll').innerHTML = html;
}

function filtrar(id, el){
  catOn = id;
  document.querySelectorAll('.cat-btn').forEach(b=>b.classList.toggle('on', b.dataset.id==id));
  const f = id==='all' ? platos : platos.filter(p=>p.categoria_id==id);
  pintar(f);
  const sec = document.getElementById('s'+id);
  if (sec && id!=='all') setTimeout(()=>sec.scrollIntoView({behavior:'smooth',block:'start'}),50);
}

// ── Layout toggle ───────────────────────────────────
function toggleLayout(){
  layoutGrid = !layoutGrid;
  document.getElementById('toggleIcon').className = layoutGrid ? 'fas fa-list' : 'fas fa-th';
  document.getElementById('toggleBtn').classList.toggle('active', !layoutGrid);
  const f = catOn==='all' ? platos : platos.filter(p=>p.categoria_id==catOn);
  pintar(f, true);
}

// ── Render platos ───────────────────────────────────
function pintar(lista, noScroll){
  const main = document.getElementById('main');
  if (!lista.length){
    main.innerHTML = `<div class="empty"><i class="fas fa-search"></i><h3>Sin resultados</h3><p>Probá con otra búsqueda</p></div>`;
    return;
  }
  // Agrupar por categoría
  const mapa = {}, sc = [];
  lista.forEach(p => p.categoria_id ? ((mapa[p.categoria_id]=mapa[p.categoria_id]||[]).push(p)) : sc.push(p));

  let html = '';
  cats.forEach(c=>{
    if (!mapa[c.id]?.length) return;
    html += seccion(c.nombre, c.color||'#6b7280', c.id, mapa[c.id]);
  });
  if (sc.length) html += seccion('Otros','#9ca3af','otros',sc);
  main.innerHTML = html;

  // Animar cards
  main.querySelectorAll('.card-g,.card-l').forEach((el,i)=>{
    el.style.animationDelay = (i*30)+'ms';
    el.classList.add('fade-in');
  });
}

function seccion(nombre, color, id, items){
  const cards = items.map(p => layoutGrid ? cardGrid(p) : cardList(p)).join('');
  return `<div class="sec" id="s${id}">
    <div class="sec-head">
      <div class="sec-dot" style="background:${color}"></div>
      <div class="sec-title">${x(nombre)}</div>
      <div class="sec-count">${items.length}</div>
    </div>
    <div class="${layoutGrid?'platos-grid':'platos-list'}">${cards}</div>
  </div>`;
}

function cardGrid(p){
  const img = p.foto
    ? `<img src="${B}/public/uploads/productos/${x(p.foto)}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=card-g-img-ph>🍽️</div>'">`
    : `<div class="card-g-img-ph">🍽️</div>`;
  const badge = p.categoria_nombre
    ? `<div class="card-g-badge" style="background:${p.categoria_color||'#6b7280'}">${x(p.categoria_nombre)}</div>` : '';
  return `<div class="card-g" onclick="abrirModal(${p.id})">
    <div class="card-g-img">${img}${badge}</div>
    <div class="card-g-body">
      <div class="card-g-name">${x(p.nombre)}</div>
      ${p.descripcion?`<div class="card-g-desc">${x(p.descripcion)}</div>`:''}
      <div class="card-g-foot">
        <div class="card-g-price">${fmt(p.precio_venta)}</div>
        <div class="card-g-icon"><i class="fas fa-arrow-right"></i></div>
      </div>
    </div>
  </div>`;
}

function cardList(p){
  const img = p.foto
    ? `<img src="${B}/public/uploads/productos/${x(p.foto)}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=card-l-img-ph>🍽️</div>'">`
    : `<div class="card-l-img-ph">🍽️</div>`;
  return `<div class="card-l" onclick="abrirModal(${p.id})">
    <div class="card-l-img">${img}</div>
    <div class="card-l-body">
      <div class="card-l-name">${x(p.nombre)}</div>
      ${p.descripcion?`<div class="card-l-desc">${x(p.descripcion)}</div>`:''}
      <div class="card-l-price">${fmt(p.precio_venta)}</div>
    </div>
  </div>`;
}

// ── Buscador ────────────────────────────────────────
let bTimer;
function buscar(q){
  clearTimeout(bTimer);
  bTimer = setTimeout(()=>{
    const t = q.toLowerCase().trim();
    if (!t){ filtrar(catOn, document.querySelector('.cat-btn.on')); return; }
    pintar(platos.filter(p=>
      p.nombre.toLowerCase().includes(t) ||
      (p.descripcion||'').toLowerCase().includes(t) ||
      (p.categoria_nombre||'').toLowerCase().includes(t)
    ));
  }, 200);
}

// ── Modal ───────────────────────────────────────────
function abrirModal(id){
  const p = platos.find(pl=>pl.id==id);
  if (!p) return;

  const close = `<button class="modal-close-btn" onclick="cerrar()"><i class="fas fa-times"></i></button>`;
  document.getElementById('modalHero').innerHTML = p.foto
    ? `<img class="modal-cover" src="${B}/public/uploads/productos/${x(p.foto)}" onerror="this.outerHTML='<div class=modal-cover-ph>🍽️</div>'">${close}`
    : `<div class="modal-cover-ph">🍽️</div>${close}`;

  const badge = document.getElementById('modalBadge');
  if (p.categoria_nombre){
    badge.textContent = p.categoria_nombre;
    badge.style.background = p.categoria_color||'#6b7280';
    badge.style.display = 'inline-flex';
  } else { badge.style.display = 'none'; }

  document.getElementById('modalName').textContent  = p.nombre;
  document.getElementById('modalDesc').textContent  = p.descripcion||'';
  document.getElementById('modalDesc').style.display = p.descripcion ? 'block':'none';
  document.getElementById('modalPrice').textContent = fmt(p.precio_venta);

  document.getElementById('modal').classList.add('on');
  document.body.style.overflow = 'hidden';
}

function cerrar(){
  document.getElementById('modal').classList.remove('on');
  document.body.style.overflow = '';
}

// ── Error ───────────────────────────────────────────
function err(t,m){
  document.getElementById('main').innerHTML =
    `<div class="empty" style="padding-top:80px">
      <i class="fas fa-exclamation-circle" style="color:#ef4444;opacity:.6"></i>
      <h3>${x(t)}</h3><p>${x(m)}</p>
    </div>`;
}

// ── Helpers ─────────────────────────────────────────
function fmt(n){ return '$'+Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0}) }
function x(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') }

document.addEventListener('keydown', e=>{ if(e.key==='Escape') cerrar() });

init();
</script>
</body>
</html>
