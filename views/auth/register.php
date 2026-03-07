<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — DASH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:      #0FD186;
            --primary-dark: #0AB871;
            --primary-glow: rgba(15,209,134,.22);
            --bg:           #0A0F1E;
            --surface:      #141B2D;
            --surface-2:    #1E2840;
            --surface-3:    #253252;
            --text:         #F1F5F9;
            --muted:        #94A3B8;
            --border:       rgba(255,255,255,.07);
            --success:      #0FD186;
            --error:        #FF6B6B;
            --warning:      #F59E0B;
            --shadow-card:  0 40px 80px rgba(0,0,0,.55);
            --transition:   all 0.25s cubic-bezier(.4,0,.2,1);
        }
        [data-theme="light"] {
            --bg:         #F0F4F8;
            --surface:    #FFFFFF;
            --surface-2:  #F8FAFC;
            --surface-3:  #EDF2F7;
            --text:       #1A202C;
            --muted:      #718096;
            --border:     #E2E8F0;
            --shadow-card:0 20px 60px rgba(0,0,0,.12);
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            transition: background .3s, color .3s;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Theme toggle ── */
        .theme-toggle {
            position: fixed; top: 18px; right: 18px; z-index: 999;
            width: 44px; height: 44px; border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted); font-size: 17px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition);
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
        }
        .theme-toggle:hover { color: var(--primary); border-color: var(--primary); box-shadow: 0 4px 20px var(--primary-glow); }
        [data-theme="light"] .icon-moon { display: none; }
        [data-theme="dark"]  .icon-sun  { display: none; }

        /* ── Layout ── */
        .register-wrap {
            width: 100%; max-width: 980px;
            display: grid; grid-template-columns: 340px 1fr;
            background: var(--surface);
            border-radius: 24px;
            box-shadow: var(--shadow-card);
            overflow: hidden; min-height: 640px;
            border: 1px solid var(--border);
            animation: slideUp .5s cubic-bezier(.16,1,.3,1);
            transition: background .3s, border-color .3s, box-shadow .3s;
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(32px) scale(.98); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }

        /* ── Panel izquierdo ── */
        .register-left {
            background: linear-gradient(160deg, #0d1e16 0%, #0A0F1E 60%, #0d1326 100%);
            padding: 44px 32px;
            display: flex; flex-direction: column;
            justify-content: space-between;
            color: white; position: relative; overflow: hidden;
        }
        .register-left::before {
            content:''; position:absolute; width:350px; height:350px; border-radius:50%;
            background:radial-gradient(circle, rgba(15,209,134,.18) 0%, transparent 65%);
            top:-100px; right:-100px; pointer-events:none;
        }
        .register-left::after {
            content:''; position:absolute; width:280px; height:280px; border-radius:50%;
            background:radial-gradient(circle, rgba(99,102,241,.12) 0%, transparent 65%);
            bottom:-80px; left:-80px; pointer-events:none;
        }
        .left-grid {
            position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size:40px 40px; pointer-events:none;
        }
        .top-accent {
            position:absolute; top:0; left:50%; transform:translateX(-50%);
            width:50%; height:1px;
            background:linear-gradient(90deg, transparent, var(--primary), transparent);
        }
        .left-content { position:relative; z-index:1; }
        .brand-logo { display:flex; align-items:center; gap:12px; margin-bottom:36px; }
        .brand-logo-box {
            width:44px; height:44px; border-radius:12px; overflow:hidden;
            background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12);
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .brand-logo-img { height:32px; width:auto; object-fit:contain; display:block; }
        .brand-logo-name {
            font-size:24px; font-weight:900; color:#fff; letter-spacing:-.5px;
            line-height:1;
        }
        .brand-logo-name span {
            display:block; font-size:10px; font-weight:500; color:rgba(255,255,255,.45);
            letter-spacing:.5px; text-transform:uppercase; margin-top:2px;
        }
        .brand-tagline { font-size:26px; font-weight:900; line-height:1.2; margin-bottom:12px; letter-spacing:-.5px; }
        .brand-tagline .hl {
            background:linear-gradient(135deg, var(--primary), #5eead4);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .brand-desc { font-size:13px; color:rgba(255,255,255,.6); line-height:1.7; margin-bottom:28px; }
        .brand-features { list-style:none; display:flex; flex-direction:column; gap:12px; }
        .brand-features li { display:flex; align-items:center; gap:12px; font-size:13px; color:rgba(255,255,255,.75); }
        .feat-icon {
            width:32px; height:32px; border-radius:9px;
            background:rgba(15,209,134,.12); border:1px solid rgba(15,209,134,.2);
            display:flex; align-items:center; justify-content:center;
            font-size:13px; color:var(--primary); flex-shrink:0;
        }
        .trial-badge {
            position:relative; z-index:1;
            background:rgba(15,209,134,.1); border:1px solid rgba(15,209,134,.25);
            border-radius:14px; padding:14px 18px;
            font-size:13px; color:rgba(255,255,255,.85);
            display:flex; align-items:center; gap:12px; margin-top:32px;
        }
        .badge-icon {
            width:42px; height:42px; border-radius:10px;
            background:rgba(15,209,134,.15); border:1px solid rgba(15,209,134,.2);
            display:flex; align-items:center; justify-content:center;
            font-size:18px; color:var(--primary); flex-shrink:0;
        }
        .trial-badge strong { font-size:15px; font-weight:800; display:block; margin-bottom:2px; color:#fff; }

        /* ── Panel derecho ── */
        .register-right {
            padding:40px 44px; overflow-y:auto;
            display:flex; flex-direction:column;
            background:var(--surface); transition:background .3s;
        }
        .form-title { font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.3px; }
        .form-subtitle { font-size:13px; color:var(--muted); margin-top:4px; }

        /* ── Steps ── */
        .steps { display:flex; align-items:center; margin:22px 0 24px; }
        .step { display:flex; align-items:center; gap:7px; font-size:12px; font-weight:600; color:var(--muted); white-space:nowrap; transition:color .25s; }
        .step-num {
            width:26px; height:26px; border-radius:50%;
            background:var(--surface-3); color:var(--muted);
            display:flex; align-items:center; justify-content:center;
            font-size:11px; font-weight:700; border:1px solid var(--border);
            transition:var(--transition);
        }
        .step.active .step-num { background:var(--primary); color:#0A0F1E; border-color:var(--primary); box-shadow:0 0 0 4px var(--primary-glow); }
        .step.done .step-num   { background:rgba(15,209,134,.15); color:var(--primary); border-color:rgba(15,209,134,.4); }
        .step.active { color:var(--primary); }
        .step.done   { color:var(--primary); }
        .step-line { flex:1; height:2px; background:var(--border); margin:0 8px; border-radius:2px; transition:background .4s; }
        .step-line.done { background:rgba(15,209,134,.4); }

        /* ── Step panels ── */
        .step-panel { display:none; flex-direction:column; gap:16px; }
        .step-panel.active { display:flex; }

        /* ── Rubros grid ── */
        .rubros-grid {
            display:grid; grid-template-columns:repeat(4,1fr); gap:8px;
            max-height:300px; overflow-y:auto; padding:2px;
        }
        .rubros-grid::-webkit-scrollbar { width:4px; }
        .rubros-grid::-webkit-scrollbar-track { background:transparent; }
        .rubros-grid::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
        .rubro-card {
            border:1.5px solid var(--border); border-radius:12px;
            padding:14px 8px 12px; text-align:center; cursor:pointer;
            transition:var(--transition); background:var(--surface-2); user-select:none;
            display:flex; flex-direction:column; align-items:center; gap:8px;
        }
        .rubro-card:hover { border-color:var(--primary); background:rgba(15,209,134,.06); transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.2); }
        .rubro-card.selected { border-color:var(--primary); background:rgba(15,209,134,.08); box-shadow:0 0 0 3px var(--primary-glow); }
        .rubro-icon-wrap {
            width:40px; height:40px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-size:17px; flex-shrink:0;
        }
        .rubro-card .rc-name { font-size:10.5px; font-weight:700; color:var(--text); line-height:1.3; }
        .rubro-card .rc-cats { font-size:9px; color:var(--muted); }

        /* ── Campos ── */
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:13px; }
        .fg { display:flex; flex-direction:column; gap:6px; }
        .fg label { font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; }
        .fi-wrap { position:relative; }
        .fi-wrap > i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:13px; pointer-events:none; }
        .fg .fi {
            border:1.5px solid var(--border); border-radius:10px;
            padding:11px 14px; font-size:14px; font-family:inherit;
            color:var(--text); background:var(--surface-2); outline:none;
            transition:var(--transition); width:100%;
        }
        .fi-wrap .fi { padding-left:36px; }
        .fg .fi:focus { border-color:var(--primary); box-shadow:0 0 0 4px var(--primary-glow); background:var(--surface-3); }
        .fg .fi::placeholder { color:rgba(148,163,184,.4); }

        /* ── Botones ── */
        .btn-row { display:flex; gap:10px; margin-top:8px; }
        .btn {
            flex:1; padding:12px 20px; border:none; border-radius:10px;
            font-size:14px; font-weight:700; font-family:inherit; cursor:pointer;
            transition:var(--transition); display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-primary {
            background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color:#0A0F1E; box-shadow:0 4px 16px var(--primary-glow);
        }
        .btn-primary:hover    { transform:translateY(-2px); box-shadow:0 8px 28px rgba(15,209,134,.4); filter:brightness(1.05); }
        .btn-primary:active   { transform:translateY(0); }
        .btn-primary:disabled { opacity:.5; cursor:not-allowed; transform:none; filter:none; box-shadow:none; }
        .btn-ghost { background:var(--surface-2); color:var(--muted); border:1.5px solid var(--border); flex:0 0 auto; padding:12px 16px; }
        .btn-ghost:hover { background:var(--surface-3); color:var(--text); border-color:var(--muted); }

        /* ── Resumen ── */
        .resumen-box {
            background:var(--surface-2); border:1px solid var(--border);
            border-radius:12px; padding:16px; font-size:13px;
            display:flex; flex-direction:column; gap:10px;
        }
        .resumen-row { display:flex; justify-content:space-between; align-items:center; }
        .resumen-row span:first-child { color:var(--muted); }
        .resumen-row span:last-child { font-weight:600; color:var(--text); }
        .rubro-preview { display:flex; align-items:center; gap:7px; font-weight:700; }
        .trial-pill {
            display:inline-flex; align-items:center; gap:5px;
            background:rgba(15,209,134,.1); color:var(--primary);
            border:1px solid rgba(15,209,134,.25); border-radius:20px;
            padding:3px 10px; font-size:12px; font-weight:700;
        }

        /* ── Alert ── */
        .alert { padding:11px 14px; border-radius:10px; font-size:13px; font-weight:500; display:none; align-items:center; gap:9px; animation:slideDown .25s ease; }
        @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .alert.show { display:flex; }
        .alert-error   { background:rgba(255,107,107,.1); color:var(--error);   border:1px solid rgba(255,107,107,.25); }
        .alert-success { background:rgba(15,209,134,.1);  color:var(--success); border:1px solid rgba(15,209,134,.25); }

        /* ── Link login ── */
        .login-link { font-size:13px; color:var(--muted); text-align:center; margin-top:16px; }
        .login-link a { color:var(--primary); font-weight:700; text-decoration:none; }
        .login-link a:hover { text-decoration:underline; }

        /* ── Success ── */
        #successScreen { display:none; flex-direction:column; align-items:center; justify-content:center; gap:16px; text-align:center; padding:20px 0; }
        .success-icon {
            width:76px; height:76px; border-radius:50%;
            background:rgba(15,209,134,.12); border:1px solid rgba(15,209,134,.25);
            color:var(--primary); font-size:32px;
            display:flex; align-items:center; justify-content:center;
            animation:popIn .4s cubic-bezier(0.175,0.885,0.32,1.275);
        }
        @keyframes popIn { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }
        .success-title { font-size:22px; font-weight:800; color:var(--text); }
        .success-sub   { font-size:14px; color:var(--muted); max-width:300px; line-height:1.65; }
        .trial-info {
            background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.25);
            border-radius:12px; padding:12px 18px; font-size:13px; color:var(--warning);
            display:flex; align-items:center; gap:10px;
        }

        /* ── Responsive ── */
        @media (max-width: 720px) {
            .register-wrap { grid-template-columns:1fr; border-radius:20px; }
            .register-left { display:none; }
            .register-right { padding:28px 20px; }
            .rubros-grid { grid-template-columns:repeat(3,1fr); max-height:260px; }
            .form-row { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
    <i class="fas fa-moon icon-moon"></i>
    <i class="fas fa-sun icon-sun"></i>
</button>

<div class="register-wrap">

    <!-- Panel izquierdo -->
    <div class="register-left">
        <div class="top-accent"></div>
        <div class="left-grid"></div>
        <div class="left-content">
            <div class="brand-logo">
                <div class="brand-logo-box">
                    <img src="public/img/DASHLOGOSF.png" alt="DASH" class="brand-logo-img">
                </div>
                <div class="brand-logo-name">
                    DASH
                    <span>Sistema de gestión</span>
                </div>
            </div>
            <div class="brand-tagline">Tu negocio,<br><span class="hl">bajo control.</span></div>
            <div class="brand-desc">Sistema de gestión para cualquier rubro. Ventas, stock, gastos, caja y reportes desde un solo lugar.</div>
            <ul class="brand-features">
                <li><div class="feat-icon"><i class="fas fa-bolt"></i></div> Listo para usar en minutos</li>
                <li><div class="feat-icon"><i class="fas fa-layer-group"></i></div> Multi-rubro con configuración automática</li>
                <li><div class="feat-icon"><i class="fas fa-chart-line"></i></div> Reportes en tiempo real</li>
                <li><div class="feat-icon"><i class="fas fa-user-shield"></i></div> Gestión de empleados y permisos</li>
                <li><div class="feat-icon"><i class="fas fa-server"></i></div> Datos 100% seguros en la nube</li>
            </ul>
        </div>
        <div class="trial-badge">
            <div class="badge-icon"><i class="fas fa-rocket"></i></div>
            <div>
                <strong>14 días gratis</strong>
                Sin tarjeta. Sin compromisos.
            </div>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="register-right">
        <div class="form-title">Crear cuenta</div>
        <div class="form-subtitle">Completá los 3 pasos para empezar</div>

        <div class="steps">
            <div class="step active" id="stepInd1">
                <div class="step-num">1</div>
                <span>Tu negocio</span>
            </div>
            <div class="step-line" id="stepLine1"></div>
            <div class="step" id="stepInd2">
                <div class="step-num">2</div>
                <span>Tu cuenta</span>
            </div>
            <div class="step-line" id="stepLine2"></div>
            <div class="step" id="stepInd3">
                <div class="step-num">3</div>
                <span>Confirmar</span>
            </div>
        </div>

        <div id="alertBox" class="alert"></div>

        <!-- PASO 1 -->
        <div class="step-panel active" id="panel1">
            <div class="fg">
                <label>Nombre del negocio *</label>
                <div class="fi-wrap">
                    <i class="fas fa-store"></i>
                    <input class="fi" type="text" id="nombre_negocio" placeholder="Ej: Almacén Don José" maxlength="100" autocomplete="organization">
                </div>
            </div>
            <div class="fg">
                <label>¿A qué rubro pertenece? *</label>
                <div id="rubrosGrid" class="rubros-grid">
                    <div style="text-align:center;padding:30px;color:var(--muted);grid-column:span 4;font-size:13px;">
                        <i class="fas fa-spinner fa-spin" style="color:var(--primary);"></i>&nbsp; Cargando rubros…
                    </div>
                </div>
            </div>
            <input type="hidden" id="rubro_id">
            <div class="btn-row">
                <button class="btn btn-primary" onclick="goStep(2)">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- PASO 2 -->
        <div class="step-panel" id="panel2">
            <div class="form-row">
                <div class="fg">
                    <label>Nombre *</label>
                    <div class="fi-wrap">
                        <i class="fas fa-user"></i>
                        <input class="fi" type="text" id="nombre" placeholder="Juan" autocomplete="given-name">
                    </div>
                </div>
                <div class="fg">
                    <label>Apellido *</label>
                    <div class="fi-wrap">
                        <i class="fas fa-user"></i>
                        <input class="fi" type="text" id="apellido" placeholder="Pérez" autocomplete="family-name">
                    </div>
                </div>
            </div>
            <div class="fg">
                <label>Email *</label>
                <div class="fi-wrap">
                    <i class="fas fa-envelope"></i>
                    <input class="fi" type="email" id="email" placeholder="juan@negocio.com" autocomplete="email">
                </div>
            </div>
            <div class="fg">
                <label>Usuario *</label>
                <div class="fi-wrap">
                    <i class="fas fa-at"></i>
                    <input class="fi" type="text" id="usuario" placeholder="juanperez" autocomplete="username" oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9_]/g,'')">
                </div>
            </div>
            <div class="fg">
                <label>Contraseña * (mín. 6 caracteres)</label>
                <div class="fi-wrap">
                    <i class="fas fa-lock"></i>
                    <input class="fi" type="password" id="password" placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>
            <div class="btn-row">
                <button class="btn btn-ghost" onclick="goStep(1)"><i class="fas fa-arrow-left"></i></button>
                <button class="btn btn-primary" onclick="goStep(3)">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- PASO 3 -->
        <div class="step-panel" id="panel3">
            <div class="fg">
                <label>Resumen de tu registro</label>
                <div class="resumen-box">
                    <div class="resumen-row"><span>Negocio</span><span id="res_negocio">—</span></div>
                    <div class="resumen-row"><span>Rubro</span><span id="res_rubro" class="rubro-preview">—</span></div>
                    <div class="resumen-row"><span>Admin</span><span id="res_admin">—</span></div>
                    <div class="resumen-row"><span>Email</span><span id="res_email">—</span></div>
                    <div class="resumen-row" style="border-top:1px solid var(--border);padding-top:9px;margin-top:2px;">
                        <span>Plan</span>
                        <span class="trial-pill"><i class="fas fa-check"></i> Trial gratuito · 14 días</span>
                    </div>
                </div>
            </div>
            <div style="font-size:12px;color:var(--muted);line-height:1.7;">
                Se crearán automáticamente <strong style="color:var(--text);" id="res_cats">las categorías</strong> para tu rubro y los métodos de pago base.
            </div>
            <div class="btn-row">
                <button class="btn btn-ghost" onclick="goStep(2)"><i class="fas fa-arrow-left"></i></button>
                <button class="btn btn-primary" id="btnRegistrar" onclick="registrar()">
                    <i class="fas fa-rocket"></i> Crear mi cuenta
                </button>
            </div>
        </div>

        <!-- SUCCESS -->
        <div id="successScreen">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <div class="success-title">¡Cuenta creada!</div>
            <div class="success-sub">Tu negocio está listo. Ya podés ingresar con tu usuario y contraseña.</div>
            <div class="trial-info">
                <i class="fas fa-calendar-check" style="font-size:18px;flex-shrink:0;"></i>
                Tu prueba gratuita vence el <strong id="trialFecha">—</strong>
            </div>
            <button class="btn btn-primary" style="max-width:240px;" onclick="irAlLogin()">
                <i class="fas fa-arrow-right-to-bracket"></i> Iniciar sesión
            </button>
        </div>

        <div class="login-link" id="loginLinkRow">
            ¿Ya tenés cuenta? <a href="javascript:void(0)" onclick="irAlLogin()">Ingresar</a>
        </div>
    </div>
</div>

<script>
(function() {
    const saved = localStorage.getItem('dash-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);
})();

function toggleTheme() {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('dash-theme', next);
}

const BASE = (function() {
    const parts = window.location.pathname.split('/').filter(Boolean);
    if (parts.length >= 1 && !parts[0].includes('.')) return '/' + parts[0];
    return '';
})();

let rubros = [];
let rubroSeleccionado = null;

async function cargarRubros() {
    try {
        const res  = await fetch(`${BASE}/api/rubros/index.php`);
        const data = await res.json();
        if (!data.success) return;
        rubros = data.data;
        renderRubros();
    } catch(e) {
        document.getElementById('rubrosGrid').innerHTML =
            '<div style="color:var(--error);grid-column:span 4;text-align:center;padding:20px;font-size:13px;"><i class="fas fa-exclamation-circle"></i> Error al cargar rubros</div>';
    }
}

function renderRubros() {
    const grid = document.getElementById('rubrosGrid');
    grid.innerHTML = rubros.map(r => {
        // Build a semi-transparent background color from r.color
        const hex = r.color || '#0FD186';
        const bg  = hex + '22';  // ~13% opacity trick for hex
        return `
        <div class="rubro-card" id="rubro_${r.id}" onclick="seleccionarRubro(${r.id})">
            <div class="rubro-icon-wrap" style="background:${bg};border:1px solid ${hex}44;">
                <i class="${r.icono}" style="color:${hex};"></i>
            </div>
            <div class="rc-name">${r.nombre}</div>
            <div class="rc-cats">${r.total_categorias} categorías</div>
        </div>`;
    }).join('');
}

function seleccionarRubro(id) {
    document.querySelectorAll('.rubro-card').forEach(c => c.classList.remove('selected'));
    document.getElementById(`rubro_${id}`)?.classList.add('selected');
    document.getElementById('rubro_id').value = id;
    rubroSeleccionado = rubros.find(r => r.id == id);
}

function goStep(step) {
    hideAlert();
    if (step === 2) {
        if (!document.getElementById('nombre_negocio').value.trim())
            return showAlert('Ingresá el nombre del negocio', 'error');
        if (!document.getElementById('rubro_id').value)
            return showAlert('Seleccioná el rubro de tu negocio', 'error');
    }
    if (step === 3) {
        const nombre   = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const email    = document.getElementById('email').value.trim();
        const usuario  = document.getElementById('usuario').value.trim();
        const password = document.getElementById('password').value;
        if (!nombre || !apellido || !email || !usuario || !password)
            return showAlert('Completá todos los campos', 'error');
        if (password.length < 6)
            return showAlert('La contraseña debe tener al menos 6 caracteres', 'error');
        if (!/^[a-z0-9_]+$/.test(usuario))
            return showAlert('El usuario solo puede tener letras minúsculas, números y _', 'error');
        document.getElementById('res_negocio').textContent = document.getElementById('nombre_negocio').value.trim();
        document.getElementById('res_admin').textContent   = nombre + ' ' + apellido;
        document.getElementById('res_email').textContent   = email;
        document.getElementById('res_rubro').innerHTML     = rubroSeleccionado
            ? `<i class="fas ${rubroSeleccionado.icono}" style="color:${rubroSeleccionado.color};margin-right:5px;"></i>${rubroSeleccionado.nombre}` : '—';
        document.getElementById('res_cats').textContent    = rubroSeleccionado
            ? `${rubroSeleccionado.total_categorias} categorías para ${rubroSeleccionado.nombre}` : 'las categorías';
    }
    [1,2,3].forEach(i => {
        const ind   = document.getElementById(`stepInd${i}`);
        const panel = document.getElementById(`panel${i}`);
        ind.className   = 'step' + (i === step ? ' active' : i < step ? ' done' : '');
        panel.className = 'step-panel' + (i === step ? ' active' : '');
        const num = ind.querySelector('.step-num');
        if (i < step) num.innerHTML = '<i class="fas fa-check" style="font-size:10px;"></i>';
        else num.textContent = i;
    });
    [1,2].forEach(i => {
        const line = document.getElementById(`stepLine${i}`);
        if (line) line.className = 'step-line' + (step > i ? ' done' : '');
    });
}

async function registrar() {
    const btn = document.getElementById('btnRegistrar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
    hideAlert();
    const body = {
        nombre_negocio: document.getElementById('nombre_negocio').value.trim(),
        rubro_id:       parseInt(document.getElementById('rubro_id').value),
        nombre:         document.getElementById('nombre').value.trim(),
        apellido:       document.getElementById('apellido').value.trim(),
        email:          document.getElementById('email').value.trim(),
        usuario:        document.getElementById('usuario').value.trim(),
        password:       document.getElementById('password').value,
    };
    try {
        const res  = await fetch(`${BASE}/api/auth/register.php`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
        });
        const data = await res.json();
        if (data.success) {
            [1,2,3].forEach(i => { document.getElementById(`panel${i}`).className = 'step-panel'; });
            const d = new Date(data.data.trial_hasta);
            document.getElementById('trialFecha').textContent =
                d.toLocaleDateString('es-AR', {day:'numeric',month:'long',year:'numeric'});
            document.getElementById('successScreen').style.display = 'flex';
            document.getElementById('loginLinkRow').style.display  = 'none';
        } else {
            showAlert(data.message || 'Error al registrar', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-rocket"></i> Crear mi cuenta';
        }
    } catch(e) {
        showAlert('Error de conexión. Intentá de nuevo.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-rocket"></i> Crear mi cuenta';
    }
}

function irAlLogin() { window.location.href = `${BASE}/index.php`; }
function showAlert(msg, type) {
    const el = document.getElementById('alertBox');
    el.className = `alert alert-${type} show`;
    el.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i> ${msg}`;
    el.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function hideAlert() { document.getElementById('alertBox').className = 'alert'; }

cargarRubros();
</script>
</body>
</html>
