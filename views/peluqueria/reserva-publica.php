<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Turno</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --accent: #8b5cf6;
            --accent-dark: #7c3aed;
            --accent-light: rgba(139,92,246,.1);
            --accent-mid: rgba(139,92,246,.18);
            --text: #0f172a;
            --sub: #475569;
            --muted: #94a3b8;
            --border: #e2e8f0;
            --card: #ffffff;
            --bg: #f8fafc;
            --success: #16a34a;
            --radius: 18px;
        }
        html { scroll-behavior: smooth; }
        body {
            background: var(--bg);
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--text);
            min-height: 100vh;
        }

        /* ── HERO ── */
        .hero {
            position: relative;
            width: 100%;
            min-height: 220px;
            background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 60%, #7c3aed 100%);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding-bottom: 72px;
        }
        .hero-portada {
            position: absolute; inset: 0;
            background-size: cover; background-position: center;
            opacity: .35; pointer-events: none;
        }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(15,7,40,.3) 0%, rgba(15,7,40,.7) 100%);
        }
        .hero-content {
            position: relative; z-index: 2;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            text-align: center; padding: 0 20px;
        }
        .hero-logo-wrap {
            width: 88px; height: 88px;
            border-radius: 24px;
            border: 3px solid rgba(255,255,255,.25);
            overflow: hidden;
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(12px);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 32px rgba(0,0,0,.3);
        }
        .hero-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .hero-logo-icon { font-size: 36px; color: rgba(255,255,255,.85); }
        .hero-nombre {
            font-size: 26px; font-weight: 900; color: #fff;
            letter-spacing: -.4px; line-height: 1.15;
            text-shadow: 0 2px 12px rgba(0,0,0,.4);
        }
        .hero-sub {
            font-size: 13px; color: rgba(255,255,255,.7); font-weight: 500;
            display: flex; align-items: center; gap: 6px;
        }
        .hero-badges {
            display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-top: 2px;
        }
        .hero-badge {
            display: flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.15); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 20px; padding: 5px 12px;
            font-size: 12px; color: rgba(255,255,255,.9); font-weight: 600;
            text-decoration: none; transition: background .15s;
        }
        .hero-badge:hover { background: rgba(255,255,255,.25); color: #fff; }

        /* ── BODY ── */
        .page-body {
            max-width: 520px; margin: 0 auto;
            padding: 0 16px 64px;
            position: relative;
        }

        /* ── STEP CARD ── */
        .step-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            margin-bottom: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
            transition: opacity .25s, transform .25s;
        }
        .step-card.locked {
            opacity: .5; pointer-events: none;
            transform: scale(.985);
        }
        .step-header {
            display: flex; align-items: center; gap: 12px; margin-bottom: 18px;
        }
        .step-num {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--accent); color: #fff;
            font-size: 13px; font-weight: 800;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .step-card.locked .step-num { background: var(--muted); }
        .step-title { font-size: 15px; font-weight: 700; }
        .step-done-label {
            margin-left: auto; font-size: 11px; font-weight: 700;
            color: var(--success); display: flex; align-items: center; gap: 4px;
        }

        /* ── SERVICIOS ── */
        .servicios-list { display: flex; flex-direction: column; gap: 8px; }
        .serv-btn {
            width: 100%; text-align: left;
            padding: 14px 16px; border-radius: 14px;
            border: 2px solid var(--border); background: transparent;
            cursor: pointer; transition: all .15s;
            display: flex; align-items: center; gap: 14px;
        }
        .serv-btn:hover { border-color: var(--accent); background: var(--accent-light); }
        .serv-btn.selected {
            border-color: var(--accent); background: var(--accent-light);
            box-shadow: 0 0 0 3px var(--accent-mid);
        }
        .serv-dot {
            width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        }
        .serv-info { flex: 1; }
        .serv-nombre { font-size: 14px; font-weight: 700; color: var(--text); }
        .serv-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .serv-precio {
            font-size: 17px; font-weight: 900; color: var(--accent); white-space: nowrap;
        }
        .serv-check {
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--accent); color: #fff;
            display: none; align-items: center; justify-content: center;
            font-size: 11px; flex-shrink: 0;
        }
        .serv-btn.selected .serv-check { display: flex; }

        /* Categoría label */
        .cat-label {
            font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .8px;
            color: var(--muted); margin: 14px 0 6px;
        }
        .cat-label:first-child { margin-top: 0; }

        /* ── FECHA ── */
        .fecha-input {
            width: 100%; padding: 13px 16px;
            border-radius: 12px; border: 2px solid var(--border);
            font-size: 15px; font-weight: 600; color: var(--text);
            background: var(--card); outline: none; transition: border .15s;
            font-family: inherit;
        }
        .fecha-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-mid); }

        /* ── SLOTS ── */
        .slots-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; }
        @media (max-width: 380px) { .slots-grid { grid-template-columns: repeat(3,1fr); } }
        .slot-btn {
            padding: 12px 4px; border-radius: 12px; border: 2px solid var(--border);
            background: transparent; font-size: 13px; font-weight: 700; cursor: pointer;
            text-align: center; transition: all .15s; color: var(--text); font-family: inherit;
            line-height: 1.2;
        }
        .slot-btn .slot-fin { font-size: 10px; font-weight: 500; color: var(--muted); display: block; margin-top: 1px; }
        .slot-btn:hover { border-color: var(--accent); background: var(--accent-light); color: var(--accent); }
        .slot-btn.selected { border-color: var(--accent); background: var(--accent); color: #fff; }
        .slot-btn.selected .slot-fin { color: rgba(255,255,255,.7); }
        .no-slots {
            text-align: center; padding: 28px 16px; color: var(--muted);
            font-size: 13px; line-height: 1.6;
        }
        .no-slots i { font-size: 28px; display: block; margin-bottom: 8px; opacity: .5; }

        /* ── FORMULARIO ── */
        .form-field { margin-bottom: 14px; }
        .form-field:last-child { margin-bottom: 0; }
        .form-label {
            display: block; font-size: 11px; font-weight: 800; color: var(--sub);
            text-transform: uppercase; letter-spacing: .6px; margin-bottom: 7px;
        }
        .form-input {
            width: 100%; padding: 13px 16px;
            border-radius: 12px; border: 2px solid var(--border);
            font-size: 15px; color: var(--text); background: var(--card);
            outline: none; transition: border .15s, box-shadow .15s; font-family: inherit;
        }
        .form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-mid); }
        .form-input::placeholder { color: var(--muted); }

        /* ── RESUMEN ── */
        .resumen-card {
            background: linear-gradient(135deg, var(--accent) 0%, #7c3aed 100%);
            border-radius: 16px; padding: 20px; color: #fff;
            margin-bottom: 14px; box-shadow: 0 8px 24px rgba(139,92,246,.3);
        }
        .resumen-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; opacity: .75; margin-bottom: 12px; }
        .resumen-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,.15);
        }
        .resumen-row:last-child { border: none; padding-bottom: 0; }
        .resumen-key { font-size: 13px; opacity: .8; }
        .resumen-val { font-size: 14px; font-weight: 700; }
        .resumen-precio-val { font-size: 20px; font-weight: 900; }

        /* ── BOTÓN RESERVAR ── */
        .btn-reservar {
            width: 100%; padding: 17px;
            background: linear-gradient(135deg, var(--accent) 0%, #7c3aed 100%);
            color: #fff; border: none; border-radius: 14px;
            font-size: 16px; font-weight: 800; cursor: pointer; font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: opacity .15s, transform .1s;
            box-shadow: 0 8px 24px rgba(139,92,246,.35);
            letter-spacing: -.2px;
        }
        .btn-reservar:hover { opacity: .92; transform: translateY(-1px); }
        .btn-reservar:active { transform: translateY(0); }
        .btn-reservar:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; transform: none; }

        /* ── ERROR ── */
        .error-msg {
            background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; font-weight: 600; margin-bottom: 12px; display: none;
        }

        /* ── LOADING ── */
        .loading-row { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 24px; color: var(--muted); font-size: 13px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin .8s linear infinite; display: inline-block; }

        /* ── SUCCESS ── */
        .success-wrap {
            display: none; max-width: 520px; margin: 0 auto; padding: 24px 16px 64px;
        }
        .success-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 24px; overflow: hidden;
            box-shadow: 0 4px 32px rgba(0,0,0,.06);
        }
        .success-top {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            padding: 36px 24px; text-align: center; color: #fff;
        }
        .success-icon-wrap {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(255,255,255,.2); margin: 0 auto 14px;
            display: flex; align-items: center; justify-content: center; font-size: 32px;
        }
        .success-top h2 { font-size: 22px; font-weight: 900; margin-bottom: 4px; }
        .success-top p { font-size: 14px; opacity: .85; }
        .success-body { padding: 24px; }
        .success-detail { display: flex; flex-direction: column; gap: 0; }
        .success-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid var(--border);
        }
        .success-row:last-child { border: none; }
        .success-key { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
        .success-val { font-size: 14px; font-weight: 700; color: var(--text); }
        .success-actions { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .btn-wa {
            flex: 1; padding: 14px; border-radius: 12px;
            background: #16a34a; color: #fff; border: none;
            font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            text-decoration: none; transition: opacity .15s;
        }
        .btn-wa:hover { opacity: .9; }
        .btn-nueva {
            flex: 1; padding: 14px; border-radius: 12px;
            background: var(--accent-light); color: var(--accent); border: 2px solid var(--accent);
            font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .15s;
        }
        .btn-nueva:hover { background: var(--accent); color: #fff; }

        /* ── FLOATING CARD (inicial) ── */
        .intro-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 18px 20px;
            margin-bottom: 14px; display: flex; align-items: center; gap: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .intro-icon {
            width: 44px; height: 44px; border-radius: 14px;
            background: var(--accent-light); color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .intro-text { flex: 1; }
        .intro-title { font-size: 14px; font-weight: 700; }
        .intro-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }

        /* Responsive */
        @media (min-width: 600px) {
            .hero { min-height: 260px; padding-bottom: 80px; }
            .hero-nombre { font-size: 30px; }
        }
    </style>
</head>
<body>
<?php
$negocioIdPhp = isset($_GET['negocio']) ? (int)$_GET['negocio'] : 0;
// App root derived from script path — avoids hardcoding /DASHBASE
$appRoot = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/');
?>

<!-- HERO (se llena con JS) -->
<div class="hero" id="heroSection">
    <div class="hero-portada" id="heroPortada"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" id="heroContent">
        <div class="hero-logo-wrap" id="heroLogoWrap">
            <i class="fas fa-scissors hero-logo-icon"></i>
        </div>
        <div class="hero-nombre" id="heroNombre">Cargando…</div>
        <div class="hero-sub" id="heroSub"></div>
        <div class="hero-badges" id="heroBadges"></div>
    </div>
</div>

<!-- SUCCESS -->
<div class="success-wrap" id="successWrap">
    <div class="success-card">
        <div class="success-top">
            <div class="success-icon-wrap">✅</div>
            <h2>¡Reserva confirmada!</h2>
            <p>Te esperamos. Guardá los datos de tu turno.</p>
        </div>
        <div class="success-body">
            <div class="success-detail" id="successDetail"></div>
            <div class="success-actions" id="successActions"></div>
        </div>
    </div>
</div>

<!-- BOOKING FLOW -->
<div class="page-body" id="bookingFlow">
    <div class="error-msg" id="errGlobal"></div>

    <!-- Intro -->
    <div class="intro-card" id="introCard" style="display:none;">
        <div class="intro-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="intro-text">
            <div class="intro-title">Reservá tu turno online</div>
            <div class="intro-sub">Elegí el servicio, la fecha y el horario que prefieras</div>
        </div>
    </div>

    <!-- Paso 1 -->
    <div class="step-card" id="step1">
        <div class="step-header">
            <div class="step-num">1</div>
            <div class="step-title">¿Qué servicio querés?</div>
            <div class="step-done-label" id="step1done" style="display:none;"><i class="fas fa-check"></i> Listo</div>
        </div>
        <div id="serviciosContainer">
            <div class="loading-row"><i class="fas fa-circle-notch spin"></i> Cargando servicios…</div>
        </div>
    </div>

    <!-- Paso 2 -->
    <div class="step-card locked" id="step2">
        <div class="step-header">
            <div class="step-num">2</div>
            <div class="step-title">¿Qué día?</div>
            <div class="step-done-label" id="step2done" style="display:none;"><i class="fas fa-check"></i> Listo</div>
        </div>
        <input type="date" class="fecha-input" id="fechaInput" min="<?= date('Y-m-d') ?>">
    </div>

    <!-- Paso 3 -->
    <div class="step-card locked" id="step3">
        <div class="step-header">
            <div class="step-num">3</div>
            <div class="step-title">¿A qué hora?</div>
            <div class="step-done-label" id="step3done" style="display:none;"><i class="fas fa-check"></i> Listo</div>
        </div>
        <div id="slotsContainer">
            <div class="no-slots"><i class="fas fa-clock"></i>Elegí primero el servicio y la fecha</div>
        </div>
    </div>

    <!-- Paso 4 -->
    <div class="step-card locked" id="step4">
        <div class="step-header">
            <div class="step-num">4</div>
            <div class="step-title">Tus datos</div>
        </div>
        <div class="form-field">
            <label class="form-label">Nombre completo *</label>
            <input type="text" class="form-input" id="inputNombre" placeholder="Ej: María García" autocomplete="name">
        </div>
        <div class="form-field">
            <label class="form-label">WhatsApp / Teléfono (opcional)</label>
            <input type="tel" class="form-input" id="inputTelefono" placeholder="Ej: 11-4512-3456" autocomplete="tel">
        </div>
    </div>

    <!-- Resumen + CTA -->
    <div id="ctaWrap" style="display:none;">
        <div class="resumen-card" id="resumenCard"></div>
        <div class="error-msg" id="errReserva"></div>
        <button class="btn-reservar" id="btnReservar" onclick="confirmarReserva()">
            <i class="fas fa-calendar-check"></i>
            Confirmar reserva
        </button>
    </div>
</div>

<script>
const API         = '../../api/peluqueria/reserva-publica.php';
const BASE_URL    = '<?= $appRoot ?>';
const NEGOCIO_ID  = <?= $negocioIdPhp ?>;

let negocioData   = null;
let serviciosData = [];
let selServicio   = null;
let selFecha      = '';
let selHora       = '';
let selHoraFin    = '';

// ── INIT ─────────────────────────────────────────────────────────────────────
(async () => {
    if (!NEGOCIO_ID) {
        show('errGlobal', 'Falta el parámetro <code>?negocio=ID</code> en la URL.');
        return;
    }
    try {
        const r = await fetch(`${API}?negocio_id=${NEGOCIO_ID}`);
        const d = await r.json();
        if (!d.success) { show('errGlobal', d.message || 'Negocio no encontrado'); return; }
        negocioData   = d.data.negocio;
        serviciosData = d.data.servicios || [];
        renderHero();
        renderServicios();
        document.getElementById('introCard').style.display = 'flex';
    } catch(e) {
        show('errGlobal', 'Error de conexión. Intentá más tarde.');
    }
})();

// ── HERO ─────────────────────────────────────────────────────────────────────
function renderHero() {
    const n = negocioData;
    document.title = `Reservar turno — ${n.nombre}`;

    // Portada
    if (n.imagen_portada) {
        const portada = document.getElementById('heroPortada');
        portada.style.backgroundImage = `url('${BASE_URL}/public/uploads/${n.imagen_portada}')`;
        portada.style.opacity = '.4';
    }

    // Logo
    const logoWrap = document.getElementById('heroLogoWrap');
    if (n.logo) {
        logoWrap.innerHTML = `<img src="${BASE_URL}/public/uploads/${n.logo}" alt="${esc(n.nombre)}" onerror="this.parentNode.innerHTML='<i class=\\'fas fa-scissors hero-logo-icon\\'></i>'">`;
    }

    // Nombre
    document.getElementById('heroNombre').textContent = n.nombre;

    // Sub (dirección)
    const sub = document.getElementById('heroSub');
    if (n.ciudad || n.direccion) {
        sub.innerHTML = `<i class="fas fa-map-marker-alt" style="font-size:11px;"></i> ${esc([n.direccion, n.ciudad].filter(Boolean).join(', '))}`;
    } else { sub.style.display = 'none'; }

    // Badges (redes/contacto)
    const badges = document.getElementById('heroBadges');
    const items = [];
    if (n.instagram) items.push(`<a class="hero-badge" href="https://instagram.com/${n.instagram.replace('@','')}" target="_blank"><i class="fab fa-instagram"></i> ${esc(n.instagram.replace('@',''))}</a>`);
    if (n.whatsapp) {
        const tel = n.whatsapp.replace(/\D/g,'');
        items.push(`<a class="hero-badge" href="https://wa.me/${tel}" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>`);
    }
    if (n.telefono && !n.whatsapp) items.push(`<span class="hero-badge"><i class="fas fa-phone"></i> ${esc(n.telefono)}</span>`);
    badges.innerHTML = items.join('');
}

// ── SERVICIOS ────────────────────────────────────────────────────────────────
function renderServicios() {
    const cont = document.getElementById('serviciosContainer');
    if (!serviciosData.length) {
        cont.innerHTML = '<div class="no-slots"><i class="fas fa-scissors"></i>Sin servicios disponibles</div>';
        return;
    }

    // Agrupar por categoría
    const cats = {};
    serviciosData.forEach(s => {
        const c = s.categoria || 'General';
        if (!cats[c]) cats[c] = [];
        cats[c].push(s);
    });

    let html = '<div class="servicios-list">';
    Object.entries(cats).forEach(([cat, items]) => {
        if (Object.keys(cats).length > 1) html += `<div class="cat-label">${esc(cat)}</div>`;
        items.forEach(s => {
            const color = s.color || '#8b5cf6';
            html += `<button class="serv-btn" id="serv_${s.id}" onclick="seleccionarServicio(${s.id})">
                <div class="serv-dot" style="background:${esc(color)};"></div>
                <div class="serv-info">
                    <div class="serv-nombre">${esc(s.nombre)}</div>
                    <div class="serv-meta"><i class="fas fa-clock" style="font-size:10px;"></i> ${s.duracion_min} min</div>
                </div>
                <div class="serv-precio">$${Number(s.precio).toLocaleString('es-AR')}</div>
                <div class="serv-check"><i class="fas fa-check"></i></div>
            </button>`;
        });
    });
    html += '</div>';
    cont.innerHTML = html;
}

function seleccionarServicio(id) {
    selServicio = serviciosData.find(s => s.id == id);
    document.querySelectorAll('.serv-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('serv_' + id).classList.add('selected');
    document.getElementById('step1done').style.display = 'flex';

    unlock('step2');
    // Scroll suave al paso 2
    setTimeout(() => document.getElementById('step2').scrollIntoView({ behavior:'smooth', block:'nearest' }), 100);
    checkFechaServicio();
}

// ── FECHA ─────────────────────────────────────────────────────────────────────
document.getElementById('fechaInput').addEventListener('change', async function() {
    selFecha   = this.value;
    selHora    = '';
    selHoraFin = '';
    document.getElementById('step2done').style.display = 'flex';
    document.getElementById('step3done').style.display = 'none';
    document.getElementById('ctaWrap').style.display   = 'none';
    checkFechaServicio();
});

async function checkFechaServicio() {
    if (!selServicio || !selFecha) return;
    unlock('step3');
    await cargarSlots();
    setTimeout(() => document.getElementById('step3').scrollIntoView({ behavior:'smooth', block:'nearest' }), 100);
}

// ── SLOTS ─────────────────────────────────────────────────────────────────────
async function cargarSlots() {
    const cont = document.getElementById('slotsContainer');
    cont.innerHTML = '<div class="loading-row"><i class="fas fa-circle-notch spin"></i> Buscando horarios disponibles…</div>';
    try {
        const r = await fetch(`${API}?negocio_id=${NEGOCIO_ID}&fecha=${selFecha}&servicio_id=${selServicio.id}`);
        const d = await r.json();
        if (!d.success) { cont.innerHTML = `<div class="no-slots"><i class="fas fa-exclamation-circle"></i>${d.message}</div>`; return; }
        const slots = d.data.slots || [];
        if (!slots.length) {
            cont.innerHTML = '<div class="no-slots"><i class="fas fa-calendar-times"></i>Sin disponibilidad para este día.<br>Probá con otra fecha.</div>';
            return;
        }
        cont.innerHTML = `<div class="slots-grid">${
            slots.map(s => `<button class="slot-btn" id="slot_${s.hora.replace(':','')}" onclick="seleccionarSlot('${s.hora}','${s.hora_fin}')">
                ${s.hora}
                <span class="slot-fin">${s.hora_fin}</span>
            </button>`).join('')
        }</div>`;
    } catch(e) {
        cont.innerHTML = '<div class="no-slots"><i class="fas fa-wifi"></i>Error de conexión</div>';
    }
}

function seleccionarSlot(hora, horaFin) {
    selHora = hora; selHoraFin = horaFin;
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    const btn = document.getElementById('slot_' + hora.replace(':',''));
    if (btn) btn.classList.add('selected');
    document.getElementById('step3done').style.display = 'flex';

    unlock('step4');
    actualizarResumen();
    setTimeout(() => document.getElementById('step4').scrollIntoView({ behavior:'smooth', block:'nearest' }), 100);
}

// ── RESUMEN ──────────────────────────────────────────────────────────────────
function actualizarResumen() {
    if (!selServicio || !selFecha || !selHora) { document.getElementById('ctaWrap').style.display = 'none'; return; }

    const fechaFmt = selFecha.split('-').reverse().join('/');
    const diasNombre = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const dt = new Date(selFecha + 'T12:00:00');
    const diaStr = diasNombre[dt.getDay()];

    document.getElementById('resumenCard').innerHTML = `
        <div class="resumen-title"><i class="fas fa-clipboard-list" style="margin-right:6px;"></i>Resumen de tu reserva</div>
        <div class="resumen-row"><span class="resumen-key">Servicio</span><span class="resumen-val">${esc(selServicio.nombre)}</span></div>
        <div class="resumen-row"><span class="resumen-key">Fecha</span><span class="resumen-val">${diaStr}, ${fechaFmt}</span></div>
        <div class="resumen-row"><span class="resumen-key">Horario</span><span class="resumen-val">${selHora} – ${selHoraFin} <span style="opacity:.6;font-size:11px;">(${selServicio.duracion_min} min)</span></span></div>
        <div class="resumen-row"><span class="resumen-key">Precio</span><span class="resumen-precio-val">$${Number(selServicio.precio).toLocaleString('es-AR')}</span></div>
    `;
    document.getElementById('ctaWrap').style.display = 'block';
    setTimeout(() => document.getElementById('ctaWrap').scrollIntoView({ behavior:'smooth', block:'nearest' }), 100);
}

// ── CONFIRMAR ────────────────────────────────────────────────────────────────
async function confirmarReserva() {
    const nombre   = document.getElementById('inputNombre').value.trim();
    const telefono = document.getElementById('inputTelefono').value.trim();
    const errEl    = document.getElementById('errReserva');
    hide('errReserva');

    if (!nombre) { show('errReserva', '⚠️ Ingresá tu nombre para continuar.'); document.getElementById('inputNombre').focus(); return; }
    if (!selServicio || !selFecha || !selHora) { show('errReserva', 'Completá todos los pasos'); return; }

    const btn = document.getElementById('btnReservar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch spin"></i> Reservando…';

    try {
        const r = await fetch(API, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                negocio_id: NEGOCIO_ID, servicio_id: selServicio.id,
                cliente_nombre: nombre, cliente_telefono: telefono,
                fecha: selFecha, hora_inicio: selHora
            })
        });
        const d = await r.json();
        if (d.success) {
            mostrarSuccess(nombre, telefono, d.data);
        } else {
            show('errReserva', d.message || 'Error al reservar. Intentá de nuevo.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirmar reserva';
        }
    } catch(e) {
        show('errReserva', 'Error de conexión. Verificá tu internet e intentá de nuevo.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirmar reserva';
    }
}

// ── SUCCESS ──────────────────────────────────────────────────────────────────
function mostrarSuccess(nombre, telefono, data) {
    document.getElementById('bookingFlow').style.display = 'none';
    const wrap = document.getElementById('successWrap');
    wrap.style.display = 'block';
    wrap.scrollIntoView({ behavior:'smooth' });

    const fechaFmt = selFecha.split('-').reverse().join('/');
    const diasNombre = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const dt = new Date(selFecha + 'T12:00:00');

    document.getElementById('successDetail').innerHTML = `
        <div class="success-row"><span class="success-key">Negocio</span><span class="success-val">${esc(negocioData.nombre)}</span></div>
        <div class="success-row"><span class="success-key">Servicio</span><span class="success-val">${esc(selServicio.nombre)}</span></div>
        <div class="success-row"><span class="success-key">Fecha</span><span class="success-val">${diasNombre[dt.getDay()]}, ${fechaFmt}</span></div>
        <div class="success-row"><span class="success-key">Horario</span><span class="success-val">${selHora} – ${selHoraFin}</span></div>
        <div class="success-row"><span class="success-key">Nombre</span><span class="success-val">${esc(nombre)}</span></div>
        ${telefono ? `<div class="success-row"><span class="success-key">Teléfono</span><span class="success-val">${esc(telefono)}</span></div>` : ''}
        <div class="success-row"><span class="success-key">Precio</span><span class="success-val" style="color:#8b5cf6;font-size:16px;font-weight:900;">$${Number(selServicio.precio).toLocaleString('es-AR')}</span></div>
    `;

    // Acciones
    const actions = [];
    // WA del negocio para confirmación
    const negWa = data.negocio_wa || negocioData?.whatsapp;
    if (negWa) {
        const tel = negWa.replace(/\D/g,'');
        const msg = encodeURIComponent(`Hola! Acabo de reservar un turno de ${selServicio.nombre} para el ${fechaFmt} a las ${selHora}. Mi nombre es ${nombre}.`);
        actions.push(`<a class="btn-wa" href="https://wa.me/${tel}?text=${msg}" target="_blank"><i class="fab fa-whatsapp"></i> Confirmar por WhatsApp</a>`);
    }
    actions.push(`<button class="btn-nueva" onclick="location.reload()"><i class="fas fa-plus"></i> Nueva reserva</button>`);
    document.getElementById('successActions').innerHTML = actions.join('');
}

// ── HELPERS ──────────────────────────────────────────────────────────────────
function unlock(id) { document.getElementById(id).classList.remove('locked'); }
function show(id, msg) { const el = document.getElementById(id); el.innerHTML = msg; el.style.display = 'block'; }
function hide(id) { document.getElementById(id).style.display = 'none'; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
