<?php
$appRoot = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/');
$negocioId = (int)(($_GET['negocio'] ?? $_GET['negocio_id'] ?? 0));
if (!$negocioId) { http_response_code(404); echo '<h1>Negocio no especificado</h1>'; exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Online — Hotel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',-apple-system,sans-serif; background:#f8fafc; color:#1e293b; }
        a { text-decoration:none; }
        input, select, textarea, button { font-family:inherit; }

        /* ── HERO ─────────────────────────────────────────────────────────── */
        .hero {
            position:relative; min-height:300px;
            background:linear-gradient(135deg,#4f46e5 0%,#6366f1 100%);
            display:flex; align-items:flex-end; overflow:hidden;
        }
        .hero-portada {
            position:absolute; inset:0; background-size:cover; background-position:center;
        }
        .hero-overlay {
            position:absolute; inset:0;
            background:linear-gradient(to top, rgba(15,23,42,.85) 0%, rgba(15,23,42,.3) 100%);
        }
        .hero-content {
            position:relative; z-index:1; width:100%;
            padding:32px 24px 28px;
            display:flex; align-items:flex-end; gap:20px;
        }
        .hero-logo {
            width:72px; height:72px; border-radius:16px;
            background:#fff; object-fit:cover; flex-shrink:0;
            border:3px solid rgba(255,255,255,.4);
            display:flex; align-items:center; justify-content:center; font-size:28px;
        }
        .hero-logo img { width:100%; height:100%; border-radius:13px; object-fit:cover; }
        .hero-info { flex:1; }
        .hero-nombre { font-size:24px; font-weight:800; color:#fff; margin-bottom:4px; }
        .hero-dir    { font-size:13px; color:rgba(255,255,255,.75); margin-bottom:10px; }
        .hero-badges { display:flex; gap:8px; flex-wrap:wrap; }
        .hero-badge  { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-ig    { background:rgba(255,255,255,.15); color:#fff; backdrop-filter:blur(8px); }
        .badge-wa    { background:rgba(37,211,102,.25); color:#fff; }

        /* ── Contenedor principal ───────────────────────────────────────────── */
        .container { max-width:860px; margin:0 auto; padding:24px 16px 60px; }

        /* ── Steps ───────────────────────────────────────────────────────── */
        .step { background:#fff; border-radius:16px; border:1px solid #e2e8f0; margin-bottom:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .step-header {
            padding:18px 22px; display:flex; align-items:center; gap:14px; cursor:pointer;
            user-select:none;
        }
        .step-num {
            width:32px; height:32px; border-radius:50%; display:flex; align-items:center;
            justify-content:center; font-weight:800; font-size:14px; flex-shrink:0;
            background:#e2e8f0; color:#64748b; transition:.2s;
        }
        .step.active .step-num  { background:#6366f1; color:#fff; }
        .step.done   .step-num  { background:#0fd186; color:#fff; }
        .step-title { font-size:15px; font-weight:700; flex:1; }
        .step-summary { font-size:12px; color:#6366f1; font-weight:600; }
        .step-body { display:none; padding:0 22px 22px; border-top:1px solid #e2e8f0; }
        .step.active .step-body { display:block; }

        /* ── Formulario de fechas ─────────────────────────────────────────── */
        .date-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
        .form-group label { display:block; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .form-control { width:100%; padding:10px 13px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px; color:#1e293b; transition:border-color .15s; }
        .form-control:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        .btn { padding:11px 22px; border-radius:11px; font-size:14px; font-weight:700; cursor:pointer; border:none; transition:.15s; display:inline-flex; align-items:center; gap:8px; }
        .btn-primary { background:#6366f1; color:#fff; }
        .btn-primary:hover { background:#4f46e5; }
        .btn-secondary { background:#f1f5f9; color:#334155; }
        .btn-secondary:hover { background:#e2e8f0; }
        .btn-success  { background:#0fd186; color:#fff; }
        .btn-success:hover { background:#059669; }

        /* ── Cards de habitaciones ───────────────────────────────────────── */
        .hab-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:14px; margin-top:16px; }
        .hab-card {
            border:2px solid #e2e8f0; border-radius:14px; padding:18px; cursor:pointer;
            transition:.2s; position:relative;
        }
        .hab-card:hover { border-color:#6366f1; box-shadow:0 4px 16px rgba(99,102,241,.12); }
        .hab-card.selected { border-color:#6366f1; background:#f5f3ff; }
        .hab-card.ocupada  { opacity:.5; cursor:not-allowed; pointer-events:none; }
        .hab-tipo-chip { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; background:#ede9fe; color:#6d28d9; margin-bottom:10px; }
        .hab-nombre { font-size:16px; font-weight:700; margin-bottom:6px; }
        .hab-meta   { font-size:12px; color:#64748b; margin-bottom:10px; display:flex; flex-wrap:wrap; gap:8px; }
        .hab-meta span { display:flex; align-items:center; gap:4px; }
        .hab-amenities { display:flex; flex-wrap:wrap; gap:5px; margin-bottom:12px; }
        .amen-tag { font-size:11px; padding:2px 7px; border-radius:20px; background:#f1f5f9; color:#475569; }
        .hab-price { font-size:18px; font-weight:800; color:#6366f1; }
        .hab-price small { font-size:12px; color:#64748b; font-weight:400; }
        .hab-no-disp { position:absolute; top:12px; right:12px; background:#ef4444; color:#fff; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; }
        .check-sel { position:absolute; top:12px; right:12px; width:22px; height:22px; border-radius:50%; background:#6366f1; color:#fff; display:none; align-items:center; justify-content:center; font-size:12px; }
        .hab-card.selected .check-sel { display:flex; }

        /* ── Resumen ─────────────────────────────────────────────────────── */
        .resumen-card { background:linear-gradient(135deg,#4f46e5 0%,#6366f1 100%); border-radius:14px; padding:20px 22px; color:#fff; margin-bottom:18px; }
        .res-row { display:flex; justify-content:space-between; align-items:center; font-size:13px; opacity:.85; margin-bottom:8px; }
        .res-total { display:flex; justify-content:space-between; align-items:center; font-size:22px; font-weight:800; border-top:1px solid rgba(255,255,255,.25); padding-top:12px; margin-top:4px; }

        /* ── Formulario cliente ──────────────────────────────────────────── */
        .form-2col { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

        /* ── Success ─────────────────────────────────────────────────────── */
        .success-screen { text-align:center; padding:40px 24px; }
        .success-icon { width:72px; height:72px; border-radius:50%; background:#d1fae5; color:#059669; display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto 20px; }
        .success-title { font-size:22px; font-weight:800; margin-bottom:8px; }
        .success-sub   { font-size:14px; color:#64748b; margin-bottom:24px; }
        .success-details { background:#f8fafc; border-radius:12px; padding:16px; text-align:left; margin-bottom:20px; }
        .det-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #e2e8f0; font-size:13px; }
        .det-row:last-child { border-bottom:none; }
        .det-lbl { color:#64748b; }
        .det-val { font-weight:700; }

        /* ── Mensaje de carga ────────────────────────────────────────────── */
        .loading { text-align:center; padding:30px; color:#94a3b8; }
        .loading i { font-size:24px; margin-bottom:10px; display:block; }

        @media (max-width:600px) {
            .date-grid, .form-2col { grid-template-columns:1fr; }
            .hero-content { padding:24px 16px 22px; }
            .hero-nombre  { font-size:20px; }
        }
    </style>
</head>
<body>

<!-- Hero -->
<div class="hero" id="heroEl">
    <div class="hero-portada" id="heroPortada"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-logo" id="heroLogo"><i class="fas fa-hotel" style="color:#6366f1;"></i></div>
        <div class="hero-info">
            <div class="hero-nombre" id="heroNombre">Cargando…</div>
            <div class="hero-dir" id="heroDir"></div>
            <div class="hero-badges" id="heroBadges"></div>
        </div>
    </div>
</div>

<div class="container" id="appContainer">
    <div class="loading"><i class="fas fa-spinner fa-spin"></i>Cargando disponibilidad…</div>
</div>

<script>
const NEGOCIO_ID = <?= $negocioId ?>;
const API = '<?= $appRoot ?>/api/hospedaje/reserva-publica.php';

let negocio  = null;
let habSelId = null;
let busqueda = { checkin:'', checkout:'', noches:0 };
let TIPO_HAB = { simple:'Simple', doble:'Doble', triple:'Triple', suite:'Suite', cabaña:'Cabaña', otro:'Otro' };
let AMEN_ICON = { wifi:'📶', tv:'📺', ac:'❄️', jacuzzi:'🛁', minibar:'🍸', desayuno:'☕', parking:'🅿️', safe:'🔒' };

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    try {
        const r = await fetch(`${API}?negocio_id=${NEGOCIO_ID}`).then(x=>x.json());
        if (!r.success) { showError(r.message || 'Negocio no encontrado'); return; }
        negocio = r.data.negocio;
        renderHero();
        renderApp();
    } catch { showError('Error de conexión'); }
}

function renderHero() {
    document.title = `Reserva Online — ${negocio.nombre}`;
    document.getElementById('heroNombre').textContent = negocio.nombre;
    if (negocio.imagen_portada) {
        document.getElementById('heroPortada').style.backgroundImage = `url(${negocio.imagen_portada})`;
    }
    const logo = document.getElementById('heroLogo');
    if (negocio.logo) {
        logo.innerHTML = `<img src="${negocio.logo}" alt="logo">`;
    }
    const dir = [negocio.direccion, negocio.ciudad, negocio.provincia].filter(Boolean).join(', ');
    if (dir) document.getElementById('heroDir').innerHTML = `<i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>${dir}`;
    let badges = '';
    if (negocio.instagram) badges += `<a href="https://instagram.com/${negocio.instagram}" target="_blank" class="hero-badge badge-ig"><i class="fab fa-instagram"></i>@${negocio.instagram}</a>`;
    if (negocio.whatsapp)  badges += `<a href="https://wa.me/${negocio.whatsapp.replace(/\D/g,'')}" target="_blank" class="hero-badge badge-wa"><i class="fab fa-whatsapp"></i>WhatsApp</a>`;
    document.getElementById('heroBadges').innerHTML = badges;
}

function renderApp() {
    document.getElementById('appContainer').innerHTML = `
        <!-- Step 1: Fechas -->
        <div class="step active" id="step1">
            <div class="step-header">
                <div class="step-num">1</div>
                <div class="step-title">Elegí tus fechas</div>
                <div class="step-summary" id="s1sum"></div>
            </div>
            <div class="step-body">
                <div class="date-grid" style="margin-top:14px;">
                    <div class="form-group">
                        <label>Check-in</label>
                        <input type="date" class="form-control" id="inCheckin" min="${hoyStr()}">
                    </div>
                    <div class="form-group">
                        <label>Check-out</label>
                        <input type="date" class="form-control" id="inCheckout">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Personas</label>
                    <select class="form-control" id="inPersonas" style="max-width:160px;">
                        ${[1,2,3,4,5,6].map(n=>`<option value="${n}">${n} persona${n>1?'s':''}</option>`).join('')}
                    </select>
                </div>
                <button class="btn btn-primary" onclick="buscarDisponibilidad()">
                    <i class="fas fa-search"></i> Ver habitaciones disponibles
                </button>
            </div>
        </div>

        <!-- Step 2: Habitaciones -->
        <div class="step" id="step2">
            <div class="step-header">
                <div class="step-num">2</div>
                <div class="step-title">Elegí tu habitación</div>
                <div class="step-summary" id="s2sum"></div>
            </div>
            <div class="step-body">
                <div id="habListado"><div class="loading"><i class="fas fa-spinner fa-spin"></i>Buscando disponibilidad…</div></div>
            </div>
        </div>

        <!-- Step 3: Tus datos -->
        <div class="step" id="step3">
            <div class="step-header">
                <div class="step-num">3</div>
                <div class="step-title">Tus datos</div>
                <div class="step-summary" id="s3sum"></div>
            </div>
            <div class="step-body">
                <div id="resumenCard"></div>
                <div class="form-2col" style="margin-bottom:12px;">
                    <div class="form-group">
                        <label>Nombre completo <span style="color:#ef4444;">*</span></label>
                        <input type="text" class="form-control" id="cNombre" placeholder="Ej: Ana García">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" class="form-control" id="cTelefono" placeholder="+54 9 11…">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Email</label>
                    <input type="email" class="form-control" id="cEmail" placeholder="correo@ejemplo.com">
                </div>
                <button class="btn btn-success" id="btnConfirmar" onclick="confirmar()">
                    <i class="fas fa-check"></i> Confirmar reserva
                </button>
            </div>
        </div>
    `;

    // Fecha por defecto: hoy → mañana
    const hoy = new Date();
    const man = new Date(hoy); man.setDate(man.getDate()+1);
    document.getElementById('inCheckin').value  = fmt(hoy);
    document.getElementById('inCheckout').value = fmt(man);
}

async function buscarDisponibilidad() {
    const ci = document.getElementById('inCheckin').value;
    const co = document.getElementById('inCheckout').value;
    if (!ci || !co || ci >= co) { alert('Seleccioná fechas válidas (check-out debe ser posterior al check-in)'); return; }

    busqueda = { checkin: ci, checkout: co, noches: Math.round((new Date(co)-new Date(ci))/86400000) };

    // Avanzar al step 2
    activarStep(2);
    document.getElementById('s1sum').textContent = `${fmtFecha(ci)} → ${fmtFecha(co)} (${busqueda.noches} noche${busqueda.noches>1?'s':''})`;
    document.getElementById('habListado').innerHTML = `<div class="loading"><i class="fas fa-spinner fa-spin"></i>Buscando…</div>`;
    scrollA('step2');

    try {
        const r = await fetch(`${API}?negocio_id=${NEGOCIO_ID}&checkin=${ci}&checkout=${co}`).then(x=>x.json());
        if (!r.success) { document.getElementById('habListado').innerHTML = `<p style="color:#ef4444;padding:20px;">${r.message}</p>`; return; }
        renderHabitaciones(r.data.habitaciones || []);
    } catch { document.getElementById('habListado').innerHTML = `<p style="color:#ef4444;padding:20px;">Error de conexión</p>`; }
}

function renderHabitaciones(habs) {
    if (!habs.length) {
        document.getElementById('habListado').innerHTML = `<div class="loading"><i class="fas fa-hotel"></i>No hay habitaciones registradas.</div>`;
        return;
    }
    const disp = habs.filter(h=>h.disponible);
    const nodisp = habs.filter(h=>!h.disponible);
    const lista = [...disp, ...nodisp];

    document.getElementById('habListado').innerHTML = `
        <p style="font-size:13px;color:#64748b;margin-bottom:4px;">${disp.length} habitación${disp.length!==1?'es':''} disponible${disp.length!==1?'s':''} para ${busqueda.noches} noche${busqueda.noches>1?'s':''}</p>
        <div class="hab-grid">
            ${lista.map(h => {
                const amens = (h.amenities||[]).map(a=>`<span class="amen-tag">${AMEN_ICON[a]||''} ${a}</span>`).join('');
                const precio = '$' + Number(h.precio_noche).toLocaleString('es-AR',{minimumFractionDigits:0});
                const total  = '$' + Number(h.total||0).toLocaleString('es-AR',{minimumFractionDigits:0});
                return `<div class="hab-card ${h.disponible?'':'ocupada'}" id="hcard-${h.id}" onclick="selHab(${h.id})">
                    ${!h.disponible?'<div class="hab-no-disp">Sin disponibilidad</div>':''}
                    <div class="check-sel"><i class="fas fa-check"></i></div>
                    <div class="hab-tipo-chip">${TIPO_HAB[h.tipo]||h.tipo}</div>
                    <div class="hab-nombre">Hab. ${esc(h.numero)}${h.nombre?' — '+esc(h.nombre):''}</div>
                    <div class="hab-meta">
                        <span><i class="fas fa-users"></i> hasta ${h.capacidad} personas</span>
                        ${h.piso?`<span><i class="fas fa-layer-group"></i> Piso ${esc(h.piso)}</span>`:''}
                    </div>
                    ${amens?`<div class="hab-amenities">${amens}</div>`:''}
                    <div class="hab-price">${total} <small>/${busqueda.noches} noche${busqueda.noches>1?'s':''} · ${precio}/noche</small></div>
                </div>`;
            }).join('')}
        </div>
    `;
}

function selHab(id) {
    habSelId = id;
    document.querySelectorAll('.hab-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('hcard-'+id).classList.add('selected');

    // Buscar hab en DOM para obtener datos
    setTimeout(() => {
        activarStep(3);
        renderResumen(id);
        document.getElementById('s2sum').textContent = 'Habitación seleccionada';
        scrollA('step3');
    }, 150);
}

function renderResumen(habId) {
    // Rebuild from the DOM-rendered card (we passed the data in the template)
    // We re-fetch from API could work, but we already have the data - pass it via closure
    // Actually we need to find the hab data. Let's store it.
    const card = document.getElementById('hcard-'+habId);
    const nombre = card.querySelector('.hab-nombre').textContent;
    const price  = card.querySelector('.hab-price').textContent.split('/')[0].trim();

    document.getElementById('resumenCard').innerHTML = `
        <div class="resumen-card">
            <div class="res-row"><span>${negocio.nombre}</span><span>${esc(nombre)}</span></div>
            <div class="res-row"><span>Check-in</span><span>${fmtFecha(busqueda.checkin)}</span></div>
            <div class="res-row"><span>Check-out</span><span>${fmtFecha(busqueda.checkout)}</span></div>
            <div class="res-row"><span>Noches</span><span>${busqueda.noches}</span></div>
            <div class="res-total"><span>Total</span><span>${price}</span></div>
        </div>
    `;
}

async function confirmar() {
    const nombre   = document.getElementById('cNombre').value.trim();
    const telefono = document.getElementById('cTelefono').value.trim();
    const email    = document.getElementById('cEmail').value.trim();

    if (!nombre) { alert('Ingresá tu nombre completo'); return; }

    const btn = document.getElementById('btnConfirmar');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reservando…';

    try {
        const r = await fetch(API, {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                negocio_id:    NEGOCIO_ID,
                habitacion_id: habSelId,
                huesped_nombre:   nombre,
                huesped_telefono: telefono,
                huesped_email:    email,
                checkin_fecha:    busqueda.checkin,
                checkin_hora:     '14:00',
                checkout_fecha:   busqueda.checkout,
                checkout_hora:    '10:00',
                personas: parseInt(document.getElementById('inPersonas').value)||1,
                tipo_estadia: 'noche',
            })
        }).then(x=>x.json());

        if (!r.success) {
            alert(r.message || 'No se pudo completar la reserva. Intentá con otras fechas.');
            btn.disabled=false; btn.innerHTML='<i class="fas fa-check"></i> Confirmar reserva';
            return;
        }

        mostrarSuccess(r.data, nombre);
    } catch {
        alert('Error de conexión. Por favor intentá nuevamente.');
        btn.disabled=false; btn.innerHTML='<i class="fas fa-check"></i> Confirmar reserva';
    }
}

function mostrarSuccess(data, nombre) {
    const waBtn = data.negocio_wa ? `
        <a href="https://wa.me/${data.negocio_wa.replace(/\D/g,'')}?text=${encodeURIComponent(`Hola! Acabo de reservar en ${data.negocio} del ${fmtFecha(data.checkin)} al ${fmtFecha(data.checkout)}. Mi nombre es ${nombre}. ¡Gracias!`)}" target="_blank" class="btn btn-success" style="margin-top:4px;">
            <i class="fab fa-whatsapp"></i> Confirmar por WhatsApp
        </a>` : '';

    document.getElementById('appContainer').innerHTML = `
        <div class="step active" style="padding:0;">
            <div class="success-screen">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <div class="success-title">¡Reserva confirmada!</div>
                <div class="success-sub">Reserva #${data.id} — ${data.negocio}</div>
                <div class="success-details">
                    <div class="det-row"><span class="det-lbl">Huésped</span><span class="det-val">${esc(nombre)}</span></div>
                    <div class="det-row"><span class="det-lbl">Check-in</span><span class="det-val">${fmtFecha(data.checkin)}</span></div>
                    <div class="det-row"><span class="det-lbl">Check-out</span><span class="det-val">${fmtFecha(data.checkout)}</span></div>
                    <div class="det-row"><span class="det-lbl">Noches</span><span class="det-val">${data.noches}</span></div>
                    <div class="det-row"><span class="det-lbl">Total</span><span class="det-val" style="color:#6366f1;font-size:16px;">$${Number(data.total).toLocaleString('es-AR',{minimumFractionDigits:0})}</span></div>
                </div>
                <p style="font-size:13px;color:#64748b;margin-bottom:16px;">El hotel se pondrá en contacto para confirmar tu reserva. Podés comunicarte por WhatsApp si tenés alguna consulta.</p>
                ${waBtn}
            </div>
        </div>
    `;
}

// ── Helpers UI ────────────────────────────────────────────────────────────────
function activarStep(n) {
    for (let i=1; i<=3; i++) {
        const s = document.getElementById('step'+i);
        if (!s) continue;
        if (i < n)  { s.classList.remove('active'); s.classList.add('done'); }
        if (i === n){ s.classList.add('active'); s.classList.remove('done'); }
        if (i > n)  { s.classList.remove('active','done'); }
    }
}

function scrollA(id) {
    setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior:'smooth', block:'start' }), 100);
}

function hoyStr() { return new Date().toISOString().slice(0,10); }
function fmt(d)   { return d.toISOString().slice(0,10); }
function fmtFecha(f) {
    if (!f) return '-';
    const [y,m,d] = f.split('-');
    return `${d}/${m}/${y}`;
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showError(msg) {
    document.getElementById('appContainer').innerHTML = `<div style="text-align:center;padding:60px 24px;"><i class="fas fa-exclamation-circle" style="font-size:48px;color:#ef4444;margin-bottom:16px;display:block;"></i><h2 style="margin-bottom:8px;">${msg}</h2></div>`;
}

init();
</script>
</body>
</html>
