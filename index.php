<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — DASH</title>
    <link rel="manifest" href="/DASHBASE/manifest.json">
    <meta name="theme-color" content="#0FD186">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/splash.css">
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
            --error:        #FF6B6B;
            --transition:   all 0.25s cubic-bezier(.4,0,.2,1);
        }
        [data-theme="light"] {
            --bg:        #F0F6FF;
            --surface:   #FFFFFF;
            --surface-2: #F8FAFC;
            --surface-3: #EFF6FF;
            --text:      #0F172A;
            --muted:     #64748B;
            --border:    #CBD5E1;
            --primary-glow: rgba(15,209,134,.18);
        }
        [data-theme="light"] body  { background: linear-gradient(135deg, #dbeafe 0%, #f0fdf4 45%, #fef9c3 100%); }
        [data-theme="light"] .login-right { background: #FFFFFF; }
        [data-theme="light"] .theme-toggle { background:#FFFFFF; box-shadow:0 2px 12px rgba(15,23,42,.1); }
        [data-theme="light"] .login-wrap { box-shadow:0 32px 80px rgba(15,23,42,.13), 0 0 0 1px rgba(203,213,225,.6); }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .theme-toggle {
            position: fixed; top: 18px; right: 18px; z-index: 999;
            width: 44px; height: 44px; border-radius: 12px;
            border: 1px solid var(--border); background: var(--surface);
            color: var(--muted); font-size: 17px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition);
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
        }
        .theme-toggle:hover { color: var(--primary); border-color: var(--primary); }
        [data-theme="light"] .icon-moon { display: none; }
        [data-theme="dark"]  .icon-sun  { display: none; }

        /* ── Layout split ── */
        .login-wrap {
            width: 100%; max-width: 900px;
            display: grid; grid-template-columns: 360px 1fr;
            background: var(--surface);
            border-radius: 24px;
            box-shadow: 0 40px 80px rgba(0,0,0,.55);
            overflow: hidden; min-height: 560px;
            border: 1px solid var(--border);
            animation: slideUp .5s cubic-bezier(.16,1,.3,1);
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(28px) scale(.98); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }

        /* ── Panel izquierdo ── */
        .login-left {
            background: linear-gradient(160deg, #071a11 0%, #0A0F1E 55%, #0d1326 100%);
            padding: 40px 32px;
            display: flex; flex-direction: column; justify-content: space-between;
            color: white; position: relative; overflow: hidden;
        }
        .login-left::before {
            content:''; position:absolute; width:300px; height:300px; border-radius:50%;
            background:radial-gradient(circle, rgba(15,209,134,.22) 0%, transparent 65%);
            top:-70px; right:-70px; pointer-events:none;
        }
        .login-left::after {
            content:''; position:absolute; width:240px; height:240px; border-radius:50%;
            background:radial-gradient(circle, rgba(99,102,241,.15) 0%, transparent 65%);
            bottom:-60px; left:-60px; pointer-events:none;
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
        .brand-logo { margin-bottom:24px; }
        .brand-logo-img { height:50px; width:auto; object-fit:contain; display:block; }
        .brand-tagline { font-size:24px; font-weight:900; line-height:1.25; margin-bottom:8px; letter-spacing:-.5px; }
        .brand-tagline .hl {
            background:linear-gradient(135deg, var(--primary), #5eead4);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .brand-desc { font-size:12px; color:rgba(255,255,255,.55); line-height:1.65; margin-bottom:22px; }

        /* ── Mockup ── */
        .app-mockup {
            position:relative; z-index:1;
            background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
            border-radius:14px; overflow:hidden;
            box-shadow:0 12px 40px rgba(0,0,0,.5), 0 0 0 1px rgba(15,209,134,.08);
        }
        .mock-topbar {
            background:rgba(255,255,255,.06); padding:7px 12px;
            display:flex; align-items:center; gap:6px; border-bottom:1px solid rgba(255,255,255,.07);
        }
        .mock-dot { width:7px; height:7px; border-radius:50%; }
        .mock-tab {
            margin-left:8px; background:rgba(15,209,134,.18); border-radius:5px;
            padding:2px 10px; font-size:9px; font-weight:700; color:var(--primary);
        }
        .mock-body { padding:12px; }
        .mock-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin-bottom:10px; }
        .mock-stat { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.07); border-radius:9px; padding:8px 8px 7px; }
        .mock-stat-label { font-size:8px; color:rgba(255,255,255,.4); margin-bottom:3px; }
        .mock-stat-val { font-size:13px; font-weight:800; color:#fff; }
        .mock-stat-val.green { color:var(--primary); }
        .mock-stat-badge { font-size:7px; color:var(--primary); background:rgba(15,209,134,.12); border-radius:4px; padding:1px 4px; display:inline-block; margin-top:2px; }
        .mock-chart { background:rgba(255,255,255,.03); border-radius:8px; padding:8px 10px; border:1px solid rgba(255,255,255,.06); margin-bottom:8px; }
        .mock-chart-title { font-size:8px; color:rgba(255,255,255,.4); margin-bottom:6px; font-weight:600; }
        .mock-bars { display:flex; align-items:flex-end; gap:4px; height:36px; }
        .mock-bar { flex:1; border-radius:3px 3px 0 0; }
        .mock-list { display:flex; flex-direction:column; gap:5px; }
        .mock-list-item {
            display:flex; align-items:center; justify-content:space-between;
            background:rgba(255,255,255,.04); border-radius:7px; padding:5px 8px;
            border:1px solid rgba(255,255,255,.05);
        }
        .mock-list-left { display:flex; align-items:center; gap:7px; }
        .mock-list-avatar { width:20px; height:20px; border-radius:6px; flex-shrink:0; }
        .mock-list-name { font-size:9px; font-weight:600; color:rgba(255,255,255,.8); }
        .mock-list-sub  { font-size:8px; color:rgba(255,255,255,.35); }
        .mock-list-amount { font-size:10px; font-weight:700; color:var(--primary); }
        .mock-pill {
            font-size:7px; padding:2px 6px; border-radius:20px; font-weight:700;
            background:rgba(15,209,134,.15); color:var(--primary); border:1px solid rgba(15,209,134,.2);
        }

        .left-footer {
            position:relative; z-index:1;
            background:rgba(15,209,134,.08); border:1px solid rgba(15,209,134,.2);
            border-radius:12px; padding:12px 16px;
            font-size:12px; color:rgba(255,255,255,.8);
            display:flex; align-items:center; gap:10px; margin-top:20px;
        }
        .left-footer-icon {
            width:36px; height:36px; border-radius:9px; flex-shrink:0;
            background:rgba(15,209,134,.15); border:1px solid rgba(15,209,134,.2);
            display:flex; align-items:center; justify-content:center;
            font-size:16px; color:var(--primary);
        }
        .left-footer strong { font-size:13px; font-weight:800; display:block; color:#fff; margin-bottom:1px; }

        /* ── Panel derecho ── */
        .login-right {
            padding: 48px 44px;
            display: flex; flex-direction: column; justify-content: center;
            background: var(--surface); position: relative; overflow: hidden;
        }

        /* Decoración sutil de fondo en el panel derecho */
        .login-right::before {
            content:''; position:absolute; top:-60px; right:-60px;
            width:200px; height:200px; border-radius:50%;
            background: radial-gradient(circle, rgba(15,209,134,.06) 0%, transparent 70%);
            pointer-events:none;
        }

        /* ── Cabecera del formulario ── */
        .login-header { margin-bottom:28px; }
        .login-badge {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(15,209,134,.1); border:1px solid rgba(15,209,134,.2);
            border-radius:20px; padding:4px 12px; font-size:11px; font-weight:700;
            color:var(--primary); letter-spacing:.3px; margin-bottom:14px;
        }
        .login-badge i { font-size:10px; }
        .login-title {
            font-size:28px; font-weight:900; color:var(--text);
            letter-spacing:-.6px; line-height:1.15; margin-bottom:6px;
        }
        .login-title span {
            background:linear-gradient(135deg, var(--primary), #5eead4);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .login-sub { font-size:13.5px; color:var(--muted); line-height:1.5; }

        /* ── Campos ── */
        .fg { display:flex; flex-direction:column; gap:7px; margin-bottom:14px; }
        .fg label {
            font-size:11.5px; font-weight:700; color:var(--muted);
            text-transform:uppercase; letter-spacing:.6px;
            display:flex; align-items:center; gap:5px;
        }
        .fi-wrap { position:relative; }
        .fi-wrap > .fi-icon {
            position:absolute; left:14px; top:50%; transform:translateY(-50%);
            color:var(--muted); font-size:14px; pointer-events:none;
            transition: color .2s;
        }
        .fi-wrap:focus-within > .fi-icon { color:var(--primary); }
        .fi {
            width:100%; border:1.5px solid var(--border); border-radius:12px;
            padding:13px 44px 13px 42px; font-size:14.5px; font-family:inherit;
            color:var(--text); background:var(--surface-2); outline:none;
            transition:var(--transition);
        }
        .fi:focus { border-color:var(--primary); box-shadow:0 0 0 4px var(--primary-glow); background:var(--surface-3); }
        .fi::placeholder { color:rgba(148,163,184,.35); }
        [data-theme="light"] .fi { background:#F8FAFC; border-color:#CBD5E1; color:#0F172A; }
        [data-theme="light"] .fi:focus { background:#FFFFFF; border-color:var(--primary); }

        /* Ojo para contraseña */
        .fi-eye {
            position:absolute; right:13px; top:50%; transform:translateY(-50%);
            color:var(--muted); font-size:14px; cursor:pointer; padding:4px;
            transition: color .2s; border:none; background:none;
            display:flex; align-items:center;
        }
        .fi-eye:hover { color:var(--primary); }

        /* ── Opciones extras ── */
        .login-options {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:20px; margin-top:-2px;
        }
        .remember-label {
            display:flex; align-items:center; gap:8px; cursor:pointer;
            font-size:13px; color:var(--muted); user-select:none;
        }
        .remember-label input[type="checkbox"] { display:none; }
        .custom-check {
            width:17px; height:17px; border-radius:5px;
            border:1.5px solid var(--border); background:var(--surface-2);
            display:flex; align-items:center; justify-content:center;
            transition:var(--transition); flex-shrink:0;
        }
        .remember-label input:checked + .custom-check {
            background:var(--primary); border-color:var(--primary);
        }
        .remember-label input:checked + .custom-check::after {
            content:''; width:5px; height:9px; border:2px solid #0A0F1E;
            border-left:none; border-top:none; transform:rotate(45deg) translateY(-1px);
            display:block;
        }
        .forgot-link {
            font-size:13px; font-weight:600; color:var(--primary);
            text-decoration:none; opacity:.85; transition:opacity .2s;
        }
        .forgot-link:hover { opacity:1; text-decoration:underline; }

        /* ── Botón principal ── */
        .btn-login {
            width:100%; padding:14px; border:none; border-radius:12px;
            font-size:15px; font-weight:800; font-family:inherit; cursor:pointer;
            background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color:#0A0F1E; letter-spacing:.2px;
            box-shadow: 0 4px 20px var(--primary-glow), 0 1px 0 rgba(255,255,255,.15) inset;
            display:flex; align-items:center; justify-content:center; gap:8px;
            transition:var(--transition); position:relative; overflow:hidden;
        }
        .btn-login::after {
            content:''; position:absolute; inset:0;
            background:linear-gradient(to bottom, rgba(255,255,255,.12), transparent);
            pointer-events:none;
        }
        .btn-login:hover    { transform:translateY(-2px); box-shadow:0 10px 32px rgba(15,209,134,.45); filter:brightness(1.06); }
        .btn-login:active   { transform:translateY(0); filter:brightness(.98); }
        .btn-login:disabled { opacity:.5; cursor:not-allowed; transform:none; filter:none; box-shadow:none; }

        .btn-google {
            width:100%; padding:12px; border:1.5px solid var(--border); border-radius:12px;
            background:var(--surface-2); color:var(--text); font-size:14px; font-weight:700;
            font-family:inherit; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
            transition:var(--transition); margin-top:10px;
        }
        .btn-google:hover { border-color:var(--primary); color:var(--primary); box-shadow:0 0 0 3px var(--primary-glow); }
        .btn-google:disabled { opacity:.55; cursor:not-allowed; }

        .google-btn-shell {
            width:100%; margin-top:10px; padding:0;
            border:none; border-radius:12px;
            background:transparent;
            display:flex; justify-content:center;
            position:relative;
        }
        .google-btn-shell-inner { width:100%; display:flex; justify-content:center; }
        .google-btn-mask {
            position:absolute;
            left:10px;
            top:50%;
            transform:translateY(-50%);
            width:46px;
            height:34px;
            border-radius:8px;
            background:#202124;
            pointer-events:none;
            z-index:2;
            display:none;
        }
        .google-btn-overlay-icon {
            position:absolute;
            left:23px;
            top:50%;
            transform:translateY(-50%);
            color:#EA4335;
            font-size:19px;
            pointer-events:none;
            z-index:3;
            display:none;
        }
        .google-btn-shell.dark .google-btn-mask,
        .google-btn-shell.dark .google-btn-overlay-icon {
            display:block;
        }

        /* ── Divider y botón registro ── */
        .divider {
            display:flex; align-items:center; gap:12px; margin:22px 0 16px;
            color:var(--muted); font-size:12px;
        }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:var(--border); }

        .btn-register {
            width:100%; padding:13px; border-radius:12px;
            border:1.5px solid var(--border); background:var(--surface-2);
            color:var(--text); font-size:14px; font-weight:700; font-family:inherit;
            cursor:pointer; display:flex; align-items:center; justify-content:center; gap:9px;
            text-decoration:none; transition:var(--transition);
        }
        .btn-register:hover {
            border-color:var(--primary); color:var(--primary);
            background:rgba(15,209,134,.05);
            box-shadow:0 0 0 3px var(--primary-glow);
        }
        .btn-register-icon {
            width:28px; height:28px; border-radius:8px;
            background:rgba(15,209,134,.12); border:1px solid rgba(15,209,134,.2);
            display:flex; align-items:center; justify-content:center;
            font-size:13px; color:var(--primary); flex-shrink:0;
        }
        .btn-register-text { display:flex; flex-direction:column; align-items:flex-start; }
        .btn-register-text small { font-size:10px; font-weight:500; color:var(--muted); line-height:1; margin-bottom:2px; }
        .btn-register-text span  { font-size:13px; font-weight:700; line-height:1; }

        /* ── Alert ── */
        .alert {
            padding:12px 14px; border-radius:10px; font-size:13px; font-weight:500;
            display:none; align-items:center; gap:9px; margin-bottom:18px;
            animation:slideDown .25s ease;
        }
        @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .alert.show { display:flex; }
        .alert-error   { background:rgba(255,107,107,.1); color:var(--error); border:1px solid rgba(255,107,107,.25); }
        .alert-success { background:rgba(15,209,134,.1); color:var(--primary); border:1px solid rgba(15,209,134,.25); }

        /* ── Footer del formulario ── */
        .login-form-footer {
            margin-top:20px; text-align:center;
            font-size:11.5px; color:var(--muted); line-height:1.6;
        }
        .login-form-footer a { color:var(--primary); font-weight:600; text-decoration:none; }
        .login-form-footer a:hover { text-decoration:underline; }

        .hidden { display:none !important; }
        .spinner { width:18px; height:18px; border:3px solid rgba(0,0,0,.2); border-top-color:#0A0F1E; border-radius:50%; animation:spin .7s linear infinite; }
        @keyframes spin { to{ transform:rotate(360deg); } }

        /* ── Logo móvil (solo visible en mobile) ── */
        .mobile-logo {
            display:none; justify-content:center; margin-bottom:24px;
        }
        .mobile-logo img { height:44px; width:auto; }

        /* ── Hero móvil (banner completo solo en mobile) ── */
        .mobile-hero {
            display: none;
            background: linear-gradient(160deg, #071a11 0%, #0A0F1E 60%, #0d1326 100%);
            margin: -40px -24px 28px;
            padding: 32px 24px 26px;
            position: relative; overflow: hidden;
        }
        .mobile-hero::before {
            content:''; position:absolute; width:220px; height:220px; border-radius:50%;
            background:radial-gradient(circle, rgba(15,209,134,.25) 0%, transparent 65%);
            top:-60px; right:-40px; pointer-events:none;
        }
        .mobile-hero::after {
            content:''; position:absolute; width:160px; height:160px; border-radius:50%;
            background:radial-gradient(circle, rgba(99,102,241,.18) 0%, transparent 65%);
            bottom:-40px; left:-30px; pointer-events:none;
        }
        /* línea de acento superior */
        .mobile-hero-accent {
            position:absolute; top:0; left:50%; transform:translateX(-50%);
            width:60%; height:1px;
            background:linear-gradient(90deg, transparent, var(--primary), transparent);
        }
        /* grid tenue de fondo */
        .mobile-hero-grid {
            position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size:36px 36px; pointer-events:none;
        }
        .mobile-hero-inner { position:relative; z-index:1; }
        .mobile-hero-top {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:16px;
        }
        .mobile-hero-logo img { height:40px; width:auto; }
        .mobile-hero-secure {
            display:flex; align-items:center; gap:6px;
            background:rgba(15,209,134,.1); border:1px solid rgba(15,209,134,.2);
            border-radius:20px; padding:4px 10px;
            font-size:10.5px; font-weight:700; color:var(--primary);
        }
        .mobile-hero-tagline {
            font-size:20px; font-weight:900; color:#fff;
            letter-spacing:-.4px; line-height:1.2; margin-bottom:4px;
        }
        .mobile-hero-tagline .hl {
            background:linear-gradient(135deg, var(--primary), #5eead4);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .mobile-hero-sub {
            font-size:12px; color:rgba(255,255,255,.5); margin-bottom:16px; line-height:1.5;
        }
        /* mini stats en fila */
        .mobile-hero-stats {
            display:grid; grid-template-columns:repeat(3,1fr); gap:8px;
        }
        .mhs-card {
            background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08);
            border-radius:10px; padding:10px 8px 9px; text-align:center;
        }
        .mhs-icon {
            width:26px; height:26px; border-radius:7px; margin:0 auto 6px;
            display:flex; align-items:center; justify-content:center; font-size:12px;
        }
        .mhs-val { font-size:14px; font-weight:800; color:#fff; line-height:1; margin-bottom:2px; }
        .mhs-val.green { color:var(--primary); }
        .mhs-label { font-size:9px; color:rgba(255,255,255,.4); font-weight:500; }

        /* ── Responsive tablet ── */
        @media (max-width: 860px) {
            .login-wrap { max-width:740px; grid-template-columns: 300px 1fr; }
            .login-left  { padding:32px 22px; }
            .login-right { padding:40px 32px; }
            .login-title { font-size:24px; }
        }

        /* ── Responsive móvil ── */
        @media (max-width: 660px) {
            body { padding: 0; align-items: stretch; }
            .login-wrap {
                grid-template-columns:1fr; border-radius:0;
                min-height:100vh; box-shadow:none;
            }
            .login-left  { display:none; }
            .login-right {
                padding:40px 24px 36px;
                justify-content:flex-start;
                min-height:100vh;
            }
            .mobile-hero { display:block; }
            .login-header { margin-bottom:20px; }
            .login-badge  { display:none; } /* el hero ya da contexto */
            .login-title  { font-size:24px; }
            .fi           { font-size:16px; } /* evita zoom en iOS */
            .btn-login    { padding:15px; font-size:15px; }
        }

        @media (max-width: 400px) {
            .login-right { padding:32px 18px 28px; }
            .mobile-hero { margin: -32px -18px 24px; padding:28px 18px 22px; }
            .login-options { flex-direction:column; align-items:flex-start; gap:10px; }
            .mobile-hero-tagline { font-size:18px; }
        }
    </style>
</head>
<body>

<!-- Splash Screen -->
<div id="splashScreen" class="splash-screen">
    <div class="splash-content">
        <img src="public/img/Splash.png" alt="DASH" class="splash-logo">
        <div class="splash-loader"></div>
    </div>
</div>

<button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
    <i class="fas fa-moon icon-moon"></i>
    <i class="fas fa-sun icon-sun"></i>
</button>

<div class="login-wrap">

    <!-- Panel izquierdo -->
    <div class="login-left">
        <div class="top-accent"></div>
        <div class="left-grid"></div>
        <div class="left-content">
            <div class="brand-logo">
                <img src="public/img/DASHLOGOSF.png" alt="DASH" class="brand-logo-img">
            </div>
            <div class="brand-tagline">Bienvenido<br><span class="hl">de vuelta.</span></div>
            <div class="brand-desc">Tu sistema de gestión te está esperando. Ingresá y retomá donde lo dejaste.</div>

            <!-- Mockup -->
            <div class="app-mockup">
                <div class="mock-topbar">
                    <div class="mock-dot" style="background:#ff6b6b;"></div>
                    <div class="mock-dot" style="background:#f59e0b;"></div>
                    <div class="mock-dot" style="background:#0FD186;"></div>
                    <div class="mock-tab">Dashboard</div>
                </div>
                <div class="mock-body">
                    <div class="mock-stats">
                        <div class="mock-stat">
                            <div class="mock-stat-label">Ventas hoy</div>
                            <div class="mock-stat-val green">$84.200</div>
                            <div class="mock-stat-badge">↑ +12%</div>
                        </div>
                        <div class="mock-stat">
                            <div class="mock-stat-label">Productos</div>
                            <div class="mock-stat-val">247</div>
                            <div class="mock-stat-badge" style="background:rgba(99,102,241,.15);color:#818cf8;">activos</div>
                        </div>
                        <div class="mock-stat">
                            <div class="mock-stat-label">Clientes</div>
                            <div class="mock-stat-val">1.380</div>
                            <div class="mock-stat-badge" style="background:rgba(245,158,11,.12);color:#f59e0b;">↑ 8 nuevos</div>
                        </div>
                    </div>
                    <div class="mock-chart">
                        <div class="mock-chart-title">VENTAS ESTA SEMANA</div>
                        <div class="mock-bars">
                            <div class="mock-bar" style="height:40%;background:rgba(15,209,134,.35);"></div>
                            <div class="mock-bar" style="height:60%;background:rgba(15,209,134,.45);"></div>
                            <div class="mock-bar" style="height:45%;background:rgba(15,209,134,.35);"></div>
                            <div class="mock-bar" style="height:80%;background:rgba(15,209,134,.6);"></div>
                            <div class="mock-bar" style="height:55%;background:rgba(15,209,134,.45);"></div>
                            <div class="mock-bar" style="height:70%;background:rgba(15,209,134,.55);"></div>
                            <div class="mock-bar" style="height:100%;background:var(--primary);box-shadow:0 0 8px rgba(15,209,134,.5);"></div>
                        </div>
                    </div>
                    <div class="mock-list">
                        <div class="mock-list-item">
                            <div class="mock-list-left">
                                <div class="mock-list-avatar" style="background:linear-gradient(135deg,#0FD186,#0AB871);"></div>
                                <div>
                                    <div class="mock-list-name">Coca Cola 2.25L</div>
                                    <div class="mock-list-sub">Bebidas · x12 vendidas</div>
                                </div>
                            </div>
                            <div>
                                <div class="mock-list-amount">$2.800</div>
                                <div class="mock-pill">+18%</div>
                            </div>
                        </div>
                        <div class="mock-list-item">
                            <div class="mock-list-left">
                                <div class="mock-list-avatar" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"></div>
                                <div>
                                    <div class="mock-list-name">Corte de cabello</div>
                                    <div class="mock-list-sub">Servicios · x8 hoy</div>
                                </div>
                            </div>
                            <div>
                                <div class="mock-list-amount">$6.400</div>
                                <div class="mock-pill" style="background:rgba(99,102,241,.15);color:#818cf8;border-color:rgba(99,102,241,.2);">Top</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="left-footer">
            <div class="left-footer-icon"><i class="fas fa-shield-halved"></i></div>
            <div>
                <strong>Acceso seguro</strong>
                Tus datos están protegidos y encriptados.
            </div>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="login-right">

        <!-- Hero banner solo visible en mobile -->
        <div class="mobile-hero">
            <div class="mobile-hero-accent"></div>
            <div class="mobile-hero-grid"></div>
            <div class="mobile-hero-inner">
                <div class="mobile-hero-top">
                    <div class="mobile-hero-logo">
                        <img src="public/img/DASHLOGOSF.png" alt="DASH">
                    </div>
                    <div class="mobile-hero-secure">
                        <i class="fas fa-shield-halved"></i> Acceso seguro
                    </div>
                </div>
                <div class="mobile-hero-tagline">Bienvenido<br><span class="hl">de vuelta.</span></div>
                <div class="mobile-hero-sub">Tu sistema de gestión listo para vos.</div>
                <div class="mobile-hero-stats">
                    <div class="mhs-card">
                        <div class="mhs-icon" style="background:rgba(15,209,134,.15);"><i class="fas fa-chart-line" style="color:var(--primary);"></i></div>
                        <div class="mhs-val green">$84k</div>
                        <div class="mhs-label">Ventas hoy</div>
                    </div>
                    <div class="mhs-card">
                        <div class="mhs-icon" style="background:rgba(99,102,241,.15);"><i class="fas fa-box" style="color:#818cf8;"></i></div>
                        <div class="mhs-val">247</div>
                        <div class="mhs-label">Productos</div>
                    </div>
                    <div class="mhs-card">
                        <div class="mhs-icon" style="background:rgba(245,158,11,.12);"><i class="fas fa-users" style="color:#f59e0b;"></i></div>
                        <div class="mhs-val">1.3k</div>
                        <div class="mhs-label">Clientes</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cabecera -->
        <div class="login-header">
            <div class="login-badge">
                <i class="fas fa-circle-dot"></i> Sistema de gestión
            </div>
            <div class="login-title">Iniciar <span>sesión</span></div>
            <div class="login-sub">Ingresá tus credenciales para acceder a tu negocio.</div>
        </div>

        <div id="alertContainer" class="alert alert-error hidden"></div>

        <form id="loginForm" autocomplete="off">
            <div class="fg">
                <label><i class="fas fa-at" style="font-size:10px;"></i> Usuario</label>
                <div class="fi-wrap">
                    <i class="fas fa-at fi-icon"></i>
                    <input class="fi" type="text" id="usuario" placeholder="Tu usuario o email" autocomplete="off" required>
                </div>
            </div>
            <div class="fg">
                <label><i class="fas fa-lock" style="font-size:10px;"></i> Contraseña</label>
                <div class="fi-wrap">
                    <i class="fas fa-lock fi-icon"></i>
                    <input class="fi" type="password" id="password" placeholder="••••••••" autocomplete="new-password" required>
                    <button type="button" class="fi-eye" id="togglePass" tabindex="-1" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Opciones: recordarme + olvidé contraseña -->
            <div class="login-options">
                <label class="remember-label">
                    <input type="checkbox" id="rememberMe">
                    <div class="custom-check"></div>
                    Recordarme
                </label>
                <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <span id="btnText"><i class="fas fa-arrow-right-to-bracket"></i> &nbsp;Ingresar ahora</span>
                <div id="btnSpinner" class="spinner hidden"></div>
            </button>

            <button type="button" class="btn-google" id="googleLoginBtn" style="display:none;">
                <i class="fab fa-google"></i> Ingresar con Google
            </button>

            <div id="googleLoginShell" class="google-btn-shell dark" style="display:none;">
                <span class="google-btn-mask" aria-hidden="true"></span>
                <i class="fab fa-google google-btn-overlay-icon" aria-hidden="true"></i>
                <div id="googleLoginRender" class="google-btn-shell-inner"></div>
            </div>
        </form>

        <div class="divider"><span>¿Todavía no tenés cuenta?</span></div>

        <a href="register.php" class="btn-register">
            <div class="btn-register-icon"><i class="fas fa-store"></i></div>
            <div class="btn-register-text">
                <small>Es gratis · 14 días de prueba</small>
                <span>Registrar mi negocio</span>
            </div>
        </a>

        <div class="login-form-footer">
            Al ingresar aceptás nuestros <a href="#">Términos de uso</a> y <a href="#">Política de privacidad</a>.
        </div>
    </div>
</div>

<script src="public/js/splash.js"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>
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
function togglePassword() {
    const inp  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    const show = inp.type === 'password';
    inp.type   = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
<script src="public/js/login.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
        });
    }
</script>
</body>
</html>
 