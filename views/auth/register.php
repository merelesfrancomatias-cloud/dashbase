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
            --bg:           linear-gradient(135deg, #e0f2fe 0%, #f0fdf4 50%, #fef9c3 100%);
            --surface:      #FFFFFF;
            --surface-2:    #F8FAFC;
            --surface-3:    #EFF6FF;
            --text:         #0F172A;
            --muted:        #64748B;
            --border:       #CBD5E1;
            --shadow-card:  0 32px 80px rgba(15,23,42,.13), 0 2px 8px rgba(15,209,134,.08);
            --primary-glow: rgba(15,209,134,.18);
        }
        [data-theme="light"] body {
            background: linear-gradient(135deg, #dbeafe 0%, #f0fdf4 45%, #fef9c3 100%);
        }
        [data-theme="light"] .register-wrap {
            box-shadow: 0 32px 80px rgba(15,23,42,.13), 0 0 0 1px rgba(203,213,225,.6);
        }
        [data-theme="light"] .register-right {
            background: #FFFFFF;
        }
        [data-theme="light"] .fg .fi {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }
        [data-theme="light"] .fg .fi:focus {
            background: #FFFFFF;
            border-color: var(--primary);
        }
        [data-theme="light"] .rubro-card {
            border-color: rgba(203,213,225,.7);
        }
        [data-theme="light"] .rubro-card:hover {
            border-color: var(--primary);
        }
        [data-theme="light"] .resumen-box {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }
        [data-theme="light"] .btn-ghost {
            background: #F1F5F9;
            border-color: #CBD5E1;
            color: #475569;
        }
        [data-theme="light"] .btn-ghost:hover {
            background: #E2E8F0;
            color: #0F172A;
        }
        [data-theme="light"] .steps .step-num {
            background: #F1F5F9;
            border-color: #CBD5E1;
        }
        [data-theme="light"] .theme-toggle {
            background: #FFFFFF;
            box-shadow: 0 2px 12px rgba(15,23,42,.1);
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
            width: 100%; max-width: 1020px;
            display: grid; grid-template-columns: 380px 1fr;
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
            background: linear-gradient(160deg, #071a11 0%, #0A0F1E 55%, #0d1326 100%);
            padding: 36px 28px;
            display: flex; flex-direction: column;
            justify-content: space-between;
            color: white; position: relative; overflow: hidden;
        }
        .register-left::before {
            content:''; position:absolute; width:320px; height:320px; border-radius:50%;
            background:radial-gradient(circle, rgba(15,209,134,.22) 0%, transparent 65%);
            top:-80px; right:-80px; pointer-events:none;
        }
        .register-left::after {
            content:''; position:absolute; width:260px; height:260px; border-radius:50%;
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
        .brand-logo { display:flex; align-items:center; gap:10px; margin-bottom:20px; }
        .brand-logo-img { height:52px; width:auto; object-fit:contain; display:block; }
        .brand-tagline { font-size:22px; font-weight:900; line-height:1.25; margin-bottom:8px; letter-spacing:-.5px; }
        .brand-tagline .hl {
            background:linear-gradient(135deg, var(--primary), #5eead4);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .brand-desc { font-size:12px; color:rgba(255,255,255,.55); line-height:1.65; margin-bottom:18px; }

        /* ── Mockup de pantalla ── */
        .app-mockup {
            position:relative; z-index:1;
            background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
            border-radius:14px; overflow:hidden; margin-bottom:16px;
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
        .mock-stat {
            background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.07);
            border-radius:9px; padding:8px 8px 7px;
        }
        .mock-stat-label { font-size:8px; color:rgba(255,255,255,.4); margin-bottom:3px; }
        .mock-stat-val { font-size:13px; font-weight:800; color:#fff; }
        .mock-stat-val.green { color:var(--primary); }
        .mock-stat-badge { font-size:7px; color:var(--primary); background:rgba(15,209,134,.12); border-radius:4px; padding:1px 4px; display:inline-block; margin-top:2px; }
        .mock-chart { background:rgba(255,255,255,.03); border-radius:8px; padding:8px 10px; border:1px solid rgba(255,255,255,.06); margin-bottom:8px; }
        .mock-chart-title { font-size:8px; color:rgba(255,255,255,.4); margin-bottom:6px; font-weight:600; }
        .mock-bars { display:flex; align-items:flex-end; gap:4px; height:36px; }
        .mock-bar { flex:1; border-radius:3px 3px 0 0; opacity:.85; }
        .mock-list { display:flex; flex-direction:column; gap:5px; }
        .mock-list-item {
            display:flex; align-items:center; justify-content:space-between;
            background:rgba(255,255,255,.04); border-radius:7px; padding:5px 8px;
            border:1px solid rgba(255,255,255,.05);
        }
        .mock-list-left { display:flex; align-items:center; gap:7px; }
        .mock-list-avatar { width:20px; height:20px; border-radius:6px; flex-shrink:0; }
        .mock-list-name { font-size:9px; font-weight:600; color:rgba(255,255,255,.8); }
        .mock-list-sub { font-size:8px; color:rgba(255,255,255,.35); }
        .mock-list-amount { font-size:10px; font-weight:700; color:var(--primary); }
        .mock-pill {
            font-size:7px; padding:2px 6px; border-radius:20px; font-weight:700;
            background:rgba(15,209,134,.15); color:var(--primary); border:1px solid rgba(15,209,134,.2);
        }

        .trial-badge {
            position:relative; z-index:1;
            background:rgba(15,209,134,.08); border:1px solid rgba(15,209,134,.2);
            border-radius:12px; padding:12px 16px;
            font-size:12px; color:rgba(255,255,255,.8);
            display:flex; align-items:center; gap:10px;
        }
        .badge-icon {
            width:38px; height:38px; border-radius:9px;
            background:rgba(15,209,134,.15); border:1px solid rgba(15,209,134,.2);
            display:flex; align-items:center; justify-content:center;
            font-size:17px; color:var(--primary); flex-shrink:0;
        }
        .trial-badge strong { font-size:14px; font-weight:800; display:block; margin-bottom:1px; color:#fff; }

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
            display:grid; grid-template-columns:repeat(3,1fr); gap:10px;
            max-height:380px; overflow-y:auto; padding:2px;
        }
        .rubros-grid::-webkit-scrollbar { width:4px; }
        .rubros-grid::-webkit-scrollbar-track { background:transparent; }
        .rubros-grid::-webkit-scrollbar-thumb { background:var(--border); border-radius:4px; }
        .rubro-card {
            border:1.5px solid var(--border); border-radius:14px;
            padding:0; text-align:center; cursor:pointer;
            transition:var(--transition); background:var(--surface-2); user-select:none;
            display:flex; flex-direction:column; align-items:center;
            overflow:hidden; position:relative;
            height: 130px;
        }
        .rubro-card-bg {
            position:absolute; inset:0;
            background-size:cover; background-position:center;
            transition: transform .45s cubic-bezier(.4,0,.2,1), opacity .3s;
            opacity:.9;
        }
        .rubro-card:hover .rubro-card-bg    { transform:scale(1.07); opacity:1; }
        .rubro-card.selected .rubro-card-bg { opacity:1; }
        .rubro-card-overlay {
            position:absolute; inset:0;
            background:linear-gradient(to top, rgba(0,0,0,.88) 0%, rgba(0,0,0,.5) 45%, rgba(0,0,0,.05) 100%);
            transition: background .3s;
        }
        .rubro-card:hover .rubro-card-overlay    { background:linear-gradient(to top, rgba(0,0,0,.92) 0%, rgba(0,0,0,.55) 45%, rgba(0,0,0,.05) 100%); }
        .rubro-card.selected .rubro-card-overlay { background:linear-gradient(to top, rgba(5,100,65,.92) 0%, rgba(10,140,90,.5) 50%, rgba(0,0,0,.05) 100%); }
        .rubro-card-body {
            position:relative; z-index:1;
            padding:10px 10px 12px;
            display:flex; flex-direction:column; align-items:center;
            justify-content:flex-end; gap:4px;
            width:100%; height:100%;
        }
        /* texto en la parte inferior */
        .rubro-card-text { display:flex; flex-direction:column; align-items:center; gap:2px; }
        .rubro-card .rc-name  { font-size:12px; font-weight:900; color:#fff; line-height:1.25; text-shadow:0 1px 8px rgba(0,0,0,1); letter-spacing:.1px; }
        .rubro-card .rc-desc  { font-size:9.5px; color:rgba(255,255,255,.85); line-height:1.35; text-shadow:0 1px 6px rgba(0,0,0,1); }
        .rubro-card .rc-cats  { font-size:8.5px; color:rgba(255,255,255,.6); text-shadow:0 1px 4px rgba(0,0,0,1); margin-top:1px; }

        .rubro-card:hover   { border-color:var(--primary); transform:translateY(-3px); box-shadow:0 10px 28px rgba(0,0,0,.5); }
        .rubro-card.selected{ border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-glow), 0 10px 28px rgba(0,0,0,.5); }

        /* checkmark de selección */
        .rubro-check {
            position:absolute; top:8px; right:8px; z-index:3;
            width:20px; height:20px; border-radius:50%;
            background:var(--primary); border:2px solid #fff;
            display:none; align-items:center; justify-content:center;
            font-size:9px; color:#0A0F1E; font-weight:900;
        }
        .rubro-card.selected .rubro-check { display:flex; }

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
            .rubros-grid { grid-template-columns:repeat(2,1fr); max-height:360px; }
            .form-row { grid-template-columns:1fr; }
        }
        @media (max-width: 440px) {
            .rubros-grid { grid-template-columns:repeat(2,1fr); }
            .rubro-card  { height: 118px; }
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
                <img src="public/img/DASHLOGOSF.png" alt="DASH" class="brand-logo-img">
            </div>
            <div class="brand-tagline">Tu negocio,<br><span class="hl">bajo control.</span></div>
            <div class="brand-desc">Ventas, stock, caja, reportes y más — desde un solo lugar.</div>

            <!-- Mockup: Mini Dashboard -->
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
                            <div class="mock-stat-badge" style="background:rgba(99,102,241,.15);color:#818cf8;border-color:rgba(99,102,241,.2);">activos</div>
                        </div>
                        <div class="mock-stat">
                            <div class="mock-stat-label">Clientes</div>
                            <div class="mock-stat-val">1.380</div>
                            <div class="mock-stat-badge" style="background:rgba(245,158,11,.12);color:#f59e0b;border-color:rgba(245,158,11,.2);">↑ 8 nuevos</div>
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
    const RUBRO_IMGS = {
        1:  'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=400&q=75', // kiosco
        2:  'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?w=400&q=75', // indumentaria
        3:  'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?w=400&q=75', // ferreteria
        4:  'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&q=75', // restaurant
        5:  'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=400&q=75', // farmacia
        6:  'https://images.unsplash.com/photo-1593640408182-31c70c8268f5?w=400&q=75', // tecnologia
        7:  'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&q=75', // libreria
        8:  'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?w=400&q=75', // peluqueria
        9:  'https://images.unsplash.com/photo-1453227588063-bb302b62f50b?w=400&q=75', // veterinaria
        10: 'https://images.unsplash.com/photo-1509281373149-e957c6296406?w=400&q=75', // optica
        11: 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=400&q=75', // jugueteria
        12: 'https://images.unsplash.com/photo-1490750967868-88df5691cc27?w=400&q=75', // floreria
        13: 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&q=75', // panaderia
        14: 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400&q=75', // electrodomesticos
        15: 'https://images.unsplash.com/photo-1483721310020-03333e577078?w=400&q=75', // deportes
        16: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&q=75', // otro
        17: 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=400&q=75', // canchas
        18: 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&q=75', // supermercado
        19: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=400&q=75', // gym
        20: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&q=75', // hospedaje
    };
    const RUBRO_DESC = {
        1:  'Ventas al por menor, bebidas y snacks',
        2:  'Moda, calzado y accesorios',
        3:  'Herramientas, materiales y construcción',
        4:  'Restaurantes, bares y delivery',
        5:  'Medicamentos, cosmética y salud',
        6:  'Computadoras, celulares y electrónica',
        7:  'Útiles, libros y artículos de oficina',
        8:  'Cortes, tinturas y tratamientos',
        9:  'Consultas, vacunas y accesorios',
        10: 'Lentes, armazones y exámenes visuales',
        11: 'Juguetes y artículos infantiles',
        12: 'Flores, plantas y arreglos florales',
        13: 'Pan artesanal, tortas y repostería',
        14: 'Heladeras, lavarropas y pequeños electrodomésticos',
        15: 'Ropa, equipos y suplementos deportivos',
        16: 'Cualquier tipo de negocio o servicio',
        17: 'Reservas, turnos y gestión de canchas',
        18: 'Almacén grande con gran variedad de productos',
        19: 'Membresías, clases y rutinas de fitness',
        20: 'Habitaciones, reservas y servicios hoteleros',
    };

    const grid = document.getElementById('rubrosGrid');
    grid.innerHTML = rubros.map(r => {
        const img  = RUBRO_IMGS[r.id] || RUBRO_IMGS[16];
        const desc = RUBRO_DESC[r.id] || 'Gestión integral de tu negocio';
        return `
        <div class="rubro-card" id="rubro_${r.id}" onclick="seleccionarRubro(${r.id})">
            <div class="rubro-card-bg" style="background-image:url('${img}');"></div>
            <div class="rubro-card-overlay"></div>
            <div class="rubro-check"><i class="fas fa-check"></i></div>
            <div class="rubro-card-body">
                <div class="rubro-card-text">
                    <div class="rc-name">${r.nombre}</div>
                    <div class="rc-desc">${desc}</div>
                    <div class="rc-cats"><i class="fas fa-tag" style="font-size:7px;margin-right:3px;"></i>${r.total_categorias} categorías incluidas</div>
                </div>
            </div>
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
