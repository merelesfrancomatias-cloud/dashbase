<?php
$appRoot = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reservar Cancha</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --accent:#16a34a;--accent-dark:#15803d;--accent-light:rgba(22,163,74,.1);--accent-mid:rgba(22,163,74,.18);
  --text:#0f172a;--sub:#475569;--muted:#94a3b8;--border:#e2e8f0;--card:#fff;--bg:#f8fafc;--success:#16a34a;--radius:18px;
}
html{scroll-behavior:smooth}
body{background:var(--bg);font-family:'Inter',-apple-system,sans-serif;color:var(--text);min-height:100vh}

/* ── HERO ── */
.hero{position:relative;width:100%;min-height:220px;background:linear-gradient(135deg,#052e16 0%,#14532d 55%,#16a34a 100%);overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;padding-bottom:72px}
.hero-portada{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.3;pointer-events:none}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(2,44,14,.3) 0%,rgba(2,44,14,.72) 100%)}
.hero-content{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;padding:0 20px}
.hero-logo-wrap{width:88px;height:88px;border-radius:22px;background:#fff;box-shadow:0 8px 30px rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.hero-logo-wrap img{width:100%;height:100%;object-fit:cover}
.hero-logo-placeholder{font-size:36px}
.hero-nombre{font-size:24px;font-weight:800;color:#fff;letter-spacing:-.3px;margin-top:2px}
.hero-dir{font-size:13px;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:5px}
.hero-social{display:flex;gap:10px;margin-top:4px}
.hero-social a{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;color:#fff;border:1px solid rgba(255,255,255,.3);background:rgba(255,255,255,.1);transition:.15s}
.hero-social a:hover{background:rgba(255,255,255,.22)}

/* ── MAIN ── */
.main{max-width:560px;margin:0 auto;padding:0 16px 60px}

/* ── STEPS ── */
.step{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:22px 20px;margin-bottom:14px;transition:.2s;opacity:.5;pointer-events:none}
.step.active{opacity:1;pointer-events:all;box-shadow:0 4px 24px rgba(22,163,74,.1)}
.step.done{opacity:.7}
.step-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.step-num{width:30px;height:30px;border-radius:50%;background:var(--accent-light);color:var(--accent);font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.step.active .step-num{background:var(--accent);color:#fff}
.step-title{font-size:15px;font-weight:700;color:var(--text)}
.step-sub{font-size:12px;color:var(--muted);margin-top:2px}

/* ── DATE PICKER ── */
.date-input{width:100%;padding:12px 16px;font-size:15px;border:2px solid var(--border);border-radius:12px;font-family:inherit;color:var(--text);background:#fff;transition:.15s}
.date-input:focus{outline:none;border-color:var(--accent)}

/* ── DUR TABS ── */
.dur-tabs{display:flex;gap:8px;margin-bottom:16px}
.dur-btn{flex:1;padding:10px;border:2px solid var(--border);border-radius:12px;background:#fff;color:var(--sub);font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;transition:.15s;text-align:center}
.dur-btn.active{border-color:var(--accent);background:var(--accent-light);color:var(--accent)}

/* ── CANCHA CARDS ── */
.canchas-grid{display:grid;gap:10px}
.cancha-opt{border:2px solid var(--border);border-radius:14px;padding:14px 16px;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:14px;background:#fff}
.cancha-opt:hover{border-color:var(--accent);background:var(--accent-light)}
.cancha-opt.selected{border-color:var(--accent);background:var(--accent-light)}
.cancha-icon{width:44px;height:44px;border-radius:12px;background:rgba(22,163,74,.12);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.cancha-name{font-weight:700;font-size:14px}
.cancha-meta{font-size:12px;color:var(--sub);margin-top:2px;display:flex;align-items:center;gap:8px}
.cancha-price{margin-left:auto;font-weight:800;font-size:15px;color:var(--accent);white-space:nowrap}

/* ── SLOTS ── */
.slots-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.slot-btn{padding:10px 6px;border:2px solid var(--border);border-radius:12px;background:#fff;cursor:pointer;text-align:center;transition:.15s;font-family:inherit}
.slot-btn:hover{border-color:var(--accent);background:var(--accent-light)}
.slot-btn.selected{border-color:var(--accent);background:var(--accent);color:#fff}
.slot-hora{font-size:13px;font-weight:700}
.slot-hasta{font-size:11px;color:var(--muted);margin-top:2px}
.slot-btn.selected .slot-hasta{color:rgba(255,255,255,.7)}

/* ── RESUMEN ── */
.resumen-card{background:linear-gradient(135deg,#052e16,#15803d);border-radius:16px;padding:20px;color:#fff;margin-bottom:16px}
.resumen-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.12);font-size:13px}
.resumen-row:last-child{border-bottom:none;padding-top:12px;margin-top:4px}
.resumen-row span:first-child{color:rgba(255,255,255,.7)}
.resumen-row span:last-child{font-weight:700}
.resumen-total{font-size:20px!important;color:#4ade80!important}

/* ── FORM ── */
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:12px;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}
.form-input{width:100%;padding:12px 14px;border:2px solid var(--border);border-radius:12px;font-size:14px;font-family:inherit;color:var(--text);transition:.15s;background:#fff}
.form-input:focus{outline:none;border-color:var(--accent)}

/* ── BTN ── */
.btn-reservar{width:100%;padding:15px;background:var(--accent);color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:800;cursor:pointer;font-family:inherit;transition:.15s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-reservar:hover{background:var(--accent-dark)}
.btn-reservar:disabled{opacity:.6;cursor:not-allowed}

/* ── SUCCESS ── */
.success-screen{display:none;text-align:center;padding:32px 24px}
.success-icon{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:32px;color:#fff;box-shadow:0 8px 24px rgba(22,163,74,.35)}
.success-title{font-size:22px;font-weight:800;margin-bottom:6px}
.success-sub{font-size:14px;color:var(--sub);margin-bottom:24px}
.success-detail{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:16px;text-align:left;margin-bottom:20px}
.success-detail-row{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #dcfce7}
.success-detail-row:last-child{border-bottom:none}
.success-detail-row span:last-child{font-weight:700;color:#15803d}
.btn-wa{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;background:#25d366;color:#fff;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none}

.empty-msg{text-align:center;padding:24px;color:var(--muted);font-size:13px}
.spin{animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:400px){.slots-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>

<div class="hero" id="hero">
  <div class="hero-portada" id="heroPortada"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-logo-wrap" id="heroLogo"><span class="hero-logo-placeholder">⚽</span></div>
    <div class="hero-nombre" id="heroNombre">Cargando...</div>
    <div class="hero-dir" id="heroDir"></div>
    <div class="hero-social" id="heroSocial"></div>
  </div>
</div>

<div class="main" style="margin-top:-52px;position:relative;z-index:10">

  <!-- PASO 1: Fecha y duración -->
  <div class="step active" id="step1">
    <div class="step-header">
      <div class="step-num">1</div>
      <div><div class="step-title">Elegí fecha y duración</div><div class="step-sub">¿Cuándo querés jugar?</div></div>
    </div>
    <div class="form-group" style="margin-bottom:14px">
      <label class="form-label">Fecha</label>
      <input type="date" class="date-input" id="inputFecha">
    </div>
    <div class="form-group" style="margin-bottom:0">
      <label class="form-label">Duración</label>
      <div class="dur-tabs">
        <button class="dur-btn active" data-h="1" onclick="setDur(1,this)">1 hora</button>
        <button class="dur-btn" data-h="2" onclick="setDur(2,this)">2 horas</button>
        <button class="dur-btn" data-h="3" onclick="setDur(3,this)">3 horas</button>
      </div>
    </div>
  </div>

  <!-- PASO 2: Cancha -->
  <div class="step" id="step2">
    <div class="step-header">
      <div class="step-num">2</div>
      <div><div class="step-title">Elegí la cancha</div><div class="step-sub" id="step2Sub">Disponibles para esa fecha</div></div>
    </div>
    <div class="canchas-grid" id="canchasGrid"><div class="empty-msg"><i class="fas fa-spinner spin"></i></div></div>
  </div>

  <!-- PASO 3: Horario -->
  <div class="step" id="step3">
    <div class="step-header">
      <div class="step-num">3</div>
      <div><div class="step-title">Elegí el horario</div><div class="step-sub" id="step3Sub">Slots disponibles</div></div>
    </div>
    <div class="slots-grid" id="slotsGrid"><div class="empty-msg" style="grid-column:1/-1"><i class="fas fa-spinner spin"></i></div></div>
  </div>

  <!-- PASO 4: Tus datos -->
  <div class="step" id="step4">
    <div class="step-header">
      <div class="step-num">4</div>
      <div><div class="step-title">Tus datos</div><div class="step-sub">Para confirmar la reserva</div></div>
    </div>

    <div class="resumen-card" id="resumenCard" style="display:none">
      <div class="resumen-row"><span>Cancha</span><span id="rCancha">—</span></div>
      <div class="resumen-row"><span>Fecha</span><span id="rFecha">—</span></div>
      <div class="resumen-row"><span>Horario</span><span id="rHorario">—</span></div>
      <div class="resumen-row"><span>Duración</span><span id="rDur">—</span></div>
      <div class="resumen-row"><span>Total</span><span class="resumen-total" id="rMonto">—</span></div>
    </div>

    <div class="form-group">
      <label class="form-label">Tu nombre *</label>
      <input type="text" class="form-input" id="fNombre" placeholder="Ej: Juan García" autocomplete="name">
    </div>
    <div class="form-group">
      <label class="form-label">WhatsApp / Teléfono</label>
      <input type="tel" class="form-input" id="fTelefono" placeholder="Ej: 11-1234-5678" autocomplete="tel">
    </div>
    <button class="btn-reservar" id="btnReservar" onclick="confirmar()">
      <i class="fas fa-calendar-check"></i> Confirmar Reserva
    </button>
  </div>

  <!-- SUCCESS -->
  <div class="step active" id="stepSuccess" style="display:none;padding:0;opacity:1;pointer-events:all">
    <div class="success-screen" id="successScreen">
      <div class="success-icon">✓</div>
      <div class="success-title">¡Reserva confirmada!</div>
      <div class="success-sub">Guardamos tu lugar. Te esperamos.</div>
      <div class="success-detail" id="successDetail"></div>
      <a class="btn-wa" id="btnWA" href="#" target="_blank">
        <i class="fab fa-whatsapp"></i> Confirmar por WhatsApp
      </a>
    </div>
  </div>

</div>

<script>
const BASE_URL = '<?= $appRoot ?>';
const API = `${BASE_URL}/api/canchas/reserva-publica.php`;

const params  = new URLSearchParams(location.search);
const NEG_ID  = parseInt(params.get('negocio') || params.get('negocio_id') || 0);

let negocio = null, canchas = [], selCancha = null, selSlot = null, durHoras = 1;
const fmtPeso = new Intl.NumberFormat('es-AR', { style:'currency', currency:'ARS', maximumFractionDigits:0 });

// ── Init ────────────────────────────────────────────────────────────────────
(async () => {
  if (!NEG_ID) { document.getElementById('heroNombre').textContent = 'Negocio no encontrado'; return; }
  const r = await fetch(`${API}?negocio_id=${NEG_ID}`);
  const j = await r.json();
  if (!j.success) return;
  negocio = j.data.negocio;
  canchas = j.data.canchas;
  renderHero(negocio);
  renderCanchas();

  // Fecha mínima = hoy
  const hoy = new Date();
  const minFecha = hoy.toISOString().split('T')[0];
  const inp = document.getElementById('inputFecha');
  inp.min = minFecha;
  inp.value = minFecha;

  inp.addEventListener('change', () => { resetDesde(2); renderCanchas(); });
  activarStep1();
})();

function renderHero(n) {
  document.title = `Reservar — ${n.nombre}`;
  document.getElementById('heroNombre').textContent = n.nombre;
  if (n.imagen_portada) document.getElementById('heroPortada').style.backgroundImage = `url('${BASE_URL}/${n.imagen_portada}')`;
  if (n.logo) document.getElementById('heroLogo').innerHTML = `<img src="${BASE_URL}/${n.logo}" alt="${n.nombre}">`;
  const dir = [n.direccion, n.ciudad].filter(Boolean).join(', ');
  if (dir) document.getElementById('heroDir').innerHTML = `<i class="fas fa-map-marker-alt"></i> ${dir}`;
  const social = [];
  if (n.instagram) social.push(`<a href="https://instagram.com/${n.instagram.replace('@','')}" target="_blank"><i class="fab fa-instagram"></i> Instagram</a>`);
  if (n.whatsapp)  social.push(`<a href="https://wa.me/${n.whatsapp.replace(/\D/g,'')}" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>`);
  document.getElementById('heroSocial').innerHTML = social.join('');
}

// ── Steps ───────────────────────────────────────────────────────────────────
function activarStep1() { activar('step1'); }

function activar(id) {
  ['step1','step2','step3','step4'].forEach(s => {
    const el = document.getElementById(s);
    el.classList.remove('active','done');
    const steps = ['step1','step2','step3','step4'];
    const idx = steps.indexOf(id), cur = steps.indexOf(s);
    if (cur < idx) el.classList.add('done');
    if (cur === idx) el.classList.add('active');
  });
  document.getElementById(id).scrollIntoView({ behavior:'smooth', block:'start' });
}

function resetDesde(stepN) {
  if (stepN <= 2) { selCancha = null; }
  if (stepN <= 3) { selSlot   = null; }
  ['step2','step3','step4'].slice(stepN - 2).forEach(s => {
    const el = document.getElementById(s);
    el.classList.remove('active','done');
  });
}

// ── Duración ────────────────────────────────────────────────────────────────
function setDur(h, btn) {
  durHoras = h;
  document.querySelectorAll('.dur-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  resetDesde(2);
  renderCanchas();
}

// ── Canchas ─────────────────────────────────────────────────────────────────
const DEPORTE_ICON = { futbol:'⚽', tenis:'🎾', padel:'🏸', basquet:'🏀', voley:'🏐', hockey:'🏑' };

function renderCanchas() {
  const grid = document.getElementById('canchasGrid');
  if (!canchas.length) { grid.innerHTML = '<div class="empty-msg">Sin canchas disponibles</div>'; return; }
  const fecha = document.getElementById('inputFecha').value;
  document.getElementById('step2Sub').textContent = `Disponibles el ${fechaLeg(fecha)} · ${durHoras}h`;
  grid.innerHTML = canchas.map(c => {
    const icon = DEPORTE_ICON[c.deporte?.toLowerCase()] || '🏟️';
    return `<div class="cancha-opt" onclick="seleccionarCancha(${c.id},'${esc(c.nombre)}','${c.deporte||''}',${c.precio_hora})" id="copt-${c.id}">
      <div class="cancha-icon">${icon}</div>
      <div style="flex:1;min-width:0">
        <div class="cancha-name">${esc(c.nombre)}</div>
        <div class="cancha-meta">
          ${c.deporte ? `<span>${esc(c.deporte)}</span>` : ''}
          ${c.capacidad ? `<span><i class="fas fa-users" style="font-size:10px"></i> ${c.capacidad}</span>` : ''}
        </div>
      </div>
      <div class="cancha-price">${fmtPeso.format(c.precio_hora * durHoras)}</div>
    </div>`;
  }).join('');
  activar('step2');
}

async function seleccionarCancha(id, nombre, deporte, precioHora) {
  document.querySelectorAll('.cancha-opt').forEach(el => el.classList.remove('selected'));
  document.getElementById(`copt-${id}`).classList.add('selected');
  selCancha = { id, nombre, deporte, precioHora };
  selSlot   = null;
  await cargarSlots();
}

// ── Slots ────────────────────────────────────────────────────────────────────
async function cargarSlots() {
  const fecha = document.getElementById('inputFecha').value;
  document.getElementById('step3Sub').textContent = `${selCancha.nombre} · ${fechaLeg(fecha)} · ${durHoras}h`;
  document.getElementById('slotsGrid').innerHTML = `<div class="empty-msg" style="grid-column:1/-1"><i class="fas fa-spinner spin"></i></div>`;
  activar('step3');

  const r = await fetch(`${API}?negocio_id=${NEG_ID}&cancha_id=${selCancha.id}&fecha=${fecha}&duracion_horas=${durHoras}`);
  const j = await r.json();

  if (!j.success || !j.data.slots.length) {
    document.getElementById('slotsGrid').innerHTML = '<div class="empty-msg" style="grid-column:1/-1">Sin horarios disponibles para este día.<br>Probá otra fecha o duración.</div>';
    return;
  }

  document.getElementById('slotsGrid').innerHTML = j.data.slots.map(s =>
    `<button class="slot-btn" id="slot-${s.hora_inicio.replace(':','')}" onclick="seleccionarSlot('${s.hora_inicio}','${s.hora_fin}',${s.monto})">
      <div class="slot-hora">${s.hora_inicio}</div>
      <div class="slot-hasta">hasta ${s.hora_fin}</div>
    </button>`
  ).join('');
}

function seleccionarSlot(ini, fin, monto) {
  document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
  document.getElementById(`slot-${ini.replace(':','')}`).classList.add('selected');
  selSlot = { ini, fin, monto };
  mostrarResumen();
  activar('step4');
  document.getElementById('step4').scrollIntoView({ behavior:'smooth', block:'start' });
}

// ── Resumen ──────────────────────────────────────────────────────────────────
function mostrarResumen() {
  const fecha = document.getElementById('inputFecha').value;
  document.getElementById('rCancha').textContent  = selCancha.nombre;
  document.getElementById('rFecha').textContent   = fechaLeg(fecha);
  document.getElementById('rHorario').textContent = `${selSlot.ini} → ${selSlot.fin}`;
  document.getElementById('rDur').textContent     = `${durHoras}h`;
  document.getElementById('rMonto').textContent   = fmtPeso.format(selSlot.monto);
  document.getElementById('resumenCard').style.display = '';
}

// ── Confirmar ────────────────────────────────────────────────────────────────
async function confirmar() {
  const nombre   = document.getElementById('fNombre').value.trim();
  const telefono = document.getElementById('fTelefono').value.trim();
  if (!nombre) { document.getElementById('fNombre').focus(); return; }
  if (!selCancha || !selSlot) return;

  const btn = document.getElementById('btnReservar');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner spin"></i> Reservando...';

  const fecha = document.getElementById('inputFecha').value;
  try {
    const r = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ negocio_id: NEG_ID, cancha_id: selCancha.id, fecha, hora_inicio: selSlot.ini, duracion_horas: durHoras, cliente_nombre: nombre, cliente_telefono: telefono })
    });
    const j = await r.json();
    if (j.success) {
      mostrarSuccess(j.data, nombre, fecha);
    } else {
      alert(j.message || 'Error al reservar. Intentá de nuevo.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirmar Reserva';
    }
  } catch(e) {
    alert('Error de conexión. Intentá de nuevo.');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirmar Reserva';
  }
}

function mostrarSuccess(data, nombre, fecha) {
  ['step1','step2','step3','step4'].forEach(s => {
    const el = document.getElementById(s);
    el.classList.remove('active'); el.classList.add('done');
  });
  const ss = document.getElementById('stepSuccess');
  ss.style.display = '';
  ss.scrollIntoView({ behavior:'smooth', block:'start' });

  document.getElementById('successDetail').innerHTML = `
    <div class="success-detail-row"><span>Cancha</span><span>${esc(data.cancha)}</span></div>
    <div class="success-detail-row"><span>Fecha</span><span>${fechaLeg(fecha)}</span></div>
    <div class="success-detail-row"><span>Horario</span><span>${data.hora_inicio} → ${data.hora_fin}</span></div>
    <div class="success-detail-row"><span>Total</span><span>${fmtPeso.format(data.monto)}</span></div>
  `;

  if (data.negocio_wa) {
    const msg = `Hola! Hice una reserva en *${selCancha.nombre}* para el *${fechaLeg(fecha)}* de *${data.hora_inicio}* a *${data.hora_fin}*. Mi nombre es ${nombre}. ¡Gracias!`;
    document.getElementById('btnWA').href = `https://wa.me/${data.negocio_wa.replace(/\D/g,'')}?text=${encodeURIComponent(msg)}`;
  } else {
    document.getElementById('btnWA').style.display = 'none';
  }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fechaLeg(f) {
  return new Date(f + 'T00:00:00').toLocaleDateString('es-AR', { weekday:'long', day:'numeric', month:'long' });
}
</script>
</body>
</html>
