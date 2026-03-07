<?php
session_start();

$base = rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/');

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Negocio - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        /* ── HERO ── */
        .perfil-hero {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .perfil-hero-left { display:flex; align-items:center; gap:20px; }
        .perfil-logo-wrap {
            position: relative; cursor: pointer;
        }
        .perfil-logo {
            width: 72px; height: 72px; border-radius: 18px;
            object-fit: cover;
            border: 2px solid var(--border);
            background: var(--background);
        }
        .perfil-logo-badge {
            position: absolute; bottom:-4px; right:-4px;
            width: 24px; height: 24px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; border: 2px solid var(--surface);
        }
        .perfil-hero-info h1 { font-size:22px; font-weight:700; color:var(--text-primary); margin:0 0 4px; }
        .perfil-hero-info p  { font-size:14px; color:var(--text-secondary); margin:0; }
        .perfil-hero-right { display:flex; gap:10px; align-items:center; }

        /* ── TABS ── */
        .tabs-bar {
            display: flex; gap: 4px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 5px;
            margin-bottom: 20px;
            overflow-x: auto;
        }
        .tab-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 9px 18px; border-radius: 10px;
            border: none; background: transparent;
            color: var(--text-secondary);
            font-size: 13px; font-weight: 500; cursor: pointer;
            white-space: nowrap; transition: all .2s;
        }
        .tab-btn i { font-size: 14px; }
        .tab-btn:hover   { background: var(--background); color: var(--text-primary); }
        .tab-btn.active  { background: var(--primary); color: #fff; }

        /* ── TAB PANELS ── */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── PANEL CARD ── */
        .panel-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; overflow: hidden; margin-bottom: 20px;
        }
        .panel-header {
            padding: 18px 24px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .panel-header-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(99,102,241,.1); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .panel-header h3 { font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0; }
        .panel-body { padding: 24px; }

        /* ── FORM ── */
        .form-grid-2 {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
        }
        .form-grid-3 {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;
        }
        .form-span-2 { grid-column: span 2; }
        .fg { display: flex; flex-direction: column; gap: 6px; }
        .fg label { font-size: 13px; font-weight: 500; color: var(--text-secondary); }
        .fg label .req { color: #ef4444; margin-left: 2px; }
        .fi {
            padding: 10px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px;
            background: var(--background); color: var(--text-primary);
            width: 100%; transition: border-color .2s, box-shadow .2s;
            font-family: inherit;
        }
        .fi:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        }
        .fi::placeholder { color: var(--text-secondary); opacity: .5; }
        select.fi { cursor: pointer; }
        textarea.fi { resize: vertical; min-height: 80px; }

        /* ── LOGO UPLOAD ── */
        .logo-upload-area {
            display: flex; gap: 28px; align-items: center;
        }
        .logo-preview-big {
            width: 140px; height: 140px; border-radius: 16px;
            border: 2px dashed var(--border);
            background: var(--background);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0; cursor: pointer;
            transition: border-color .2s;
        }
        .logo-preview-big:hover { border-color: var(--primary); }
        .logo-preview-big img { width:100%; height:100%; object-fit:contain; }
        .logo-upload-info { flex: 1; }
        .logo-upload-info h4 { font-size:15px; font-weight:600; color:var(--text-primary); margin:0 0 6px; }
        .logo-upload-info p  { font-size:13px; color:var(--text-secondary); margin:0 0 14px; line-height:1.5; }
        .logo-btn-row { display:flex; gap:10px; flex-wrap:wrap; }

        /* ── HORARIOS ── */
        .horarios-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;
        }
        .horario-row {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border: 1.5px solid var(--border);
            border-radius: 12px; background: var(--background);
            transition: border-color .2s;
        }
        .horario-row.active-day { border-color: var(--primary); background: rgba(99,102,241,.04); }
        .horario-toggle { position: relative; width: 36px; height: 20px; flex-shrink: 0; }
        .horario-toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0; border-radius: 20px;
            background: var(--border); cursor: pointer; transition: .3s;
        }
        .toggle-slider::before {
            content: ''; position: absolute;
            width: 14px; height: 14px; border-radius: 50%;
            background: #fff; left: 3px; top: 3px; transition: .3s;
        }
        .horario-toggle input:checked + .toggle-slider { background: var(--primary); }
        .horario-toggle input:checked + .toggle-slider::before { transform: translateX(16px); }
        .horario-day { font-size: 13px; font-weight: 600; color: var(--text-primary); width: 75px; flex-shrink: 0; }
        .horario-times { display: flex; align-items: center; gap: 6px; flex: 1; }
        .horario-times input[type="time"] {
            flex: 1; padding: 5px 8px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 13px;
            background: var(--surface); color: var(--text-primary);
            transition: border-color .2s;
        }
        .horario-times input[type="time"]:focus { outline: none; border-color: var(--primary); }
        .horario-times span { font-size: 12px; color: var(--text-secondary); }

        /* ── TICKETS SWITCHES ── */
        .ticket-switches { display: flex; flex-direction: column; gap: 12px; }
        .switch-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 16px; border: 1.5px solid var(--border);
            border-radius: 12px; background: var(--background);
        }
        .switch-info h4 { font-size:14px; font-weight:600; color:var(--text-primary); margin:0 0 2px; }
        .switch-info p  { font-size:12px; color:var(--text-secondary); margin:0; }
        .switch-toggle  { position:relative; width:44px; height:24px; flex-shrink:0; }
        .switch-toggle input { opacity:0; width:0; height:0; }
        .switch-slider {
            position:absolute; inset:0; border-radius:24px;
            background:var(--border); cursor:pointer; transition:.3s;
        }
        .switch-slider::before {
            content:''; position:absolute;
            width:18px; height:18px; border-radius:50%;
            background:#fff; left:3px; top:3px; transition:.3s;
        }
        .switch-toggle input:checked + .switch-slider { background:var(--primary); }
        .switch-toggle input:checked + .switch-slider::before { transform:translateX(20px); }

        /* ── SAVE BAR (sticky) ── */
        .save-bar {
            position: sticky; bottom: 20px; z-index: 100;
            display: flex; justify-content: flex-end;
            pointer-events: none;
        }
        .save-bar .btn { pointer-events: all; box-shadow: 0 8px 24px rgba(99,102,241,.35); }

        /* ── LOADING SKELETON ── */
        .skeleton {
            background: linear-gradient(90deg, var(--border) 25%, var(--background) 50%, var(--border) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 8px; height: 38px;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        /* ── RESPONSIVE ── */
        @media(max-width:768px) {
            .form-grid-2 { grid-template-columns: 1fr; }
            .form-grid-3 { grid-template-columns: 1fr 1fr; }
            .form-span-2 { grid-column: span 1; }
            .logo-upload-area { flex-direction: column; }
            .perfil-hero { flex-direction: column; align-items: flex-start; }
            .perfil-hero-right { width: 100%; }
            .perfil-hero-right .btn { flex: 1; justify-content: center; }
            .container { padding-bottom: 90px !important; }
            .tabs-bar { gap: 2px; padding: 4px; }
            .tab-btn { padding: 8px 12px; font-size: 12px; }
        }
        @media(max-width:480px) {
            .form-grid-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <div id="alertContainer" class="alert hidden"></div>

        <div class="container">

            <!-- ── HERO ── -->
            <div class="perfil-hero" id="perfilHero">
                <div class="perfil-hero-left">
                    <div class="perfil-logo-wrap" onclick="document.getElementById('logoInput').click()">
                        <img src="../../public/img/no-image.svg" alt="Logo" id="logoHero" class="perfil-logo">
                        <div class="perfil-logo-badge"><i class="fas fa-camera"></i></div>
                    </div>
                    <div class="perfil-hero-info">
                        <h1 id="heroNombre">Mi Negocio</h1>
                        <p id="heroRubro">Completá tu perfil para que los datos aparezcan en los tickets</p>
                    </div>
                </div>
                <div class="perfil-hero-right">
                    <button class="btn btn-primary" id="btnGuardar" onclick="perfilModule.guardarCambios()">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>

            <!-- ── TABS ── -->
            <div class="tabs-bar">
                <button class="tab-btn active" data-tab="general">
                    <i class="fas fa-building"></i> General
                </button>
                <button class="tab-btn" data-tab="contacto">
                    <i class="fas fa-phone"></i> Contacto
                </button>
                <button class="tab-btn" data-tab="ubicacion">
                    <i class="fas fa-map-marker-alt"></i> Ubicación
                </button>
                <button class="tab-btn" data-tab="logo">
                    <i class="fas fa-image"></i> Logo
                </button>
                <button class="tab-btn" data-tab="carta">
                    <i class="fas fa-store"></i> Carta Digital
                </button>
                <button class="tab-btn" data-tab="tickets">
                    <i class="fas fa-receipt"></i> Tickets
                </button>
                <button class="tab-btn" data-tab="horarios">
                    <i class="fas fa-clock"></i> Horarios
                </button>
            </div>

            <!-- ══════════ TAB: GENERAL ══════════ -->
            <div class="tab-panel active" id="tab-general">
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-building"></i></div>
                        <h3>Información General</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-grid-2" style="gap:16px;">
                            <div class="fg form-span-2">
                                <label>Nombre del Negocio<span class="req">*</span></label>
                                <input type="text" id="nombre_negocio" class="fi" placeholder="Mi Negocio S.A.">
                            </div>
                            <div class="fg form-span-2">
                                <label>Razón Social</label>
                                <input type="text" id="razon_social" class="fi" placeholder="Mi Negocio Sociedad Anónima">
                            </div>
                            <div class="fg">
                                <label>CUIT / RUT</label>
                                <input type="text" id="cuit" class="fi" placeholder="20-12345678-9">
                            </div>
                            <div class="fg">
                                <label>Condición IVA</label>
                                <select id="condicion_iva" class="fi">
                                    <option value="">Seleccionar...</option>
                                    <option value="responsable_inscripto">Responsable Inscripto</option>
                                    <option value="monotributo">Monotributo</option>
                                    <option value="exento">Exento</option>
                                    <option value="consumidor_final">Consumidor Final</option>
                                </select>
                            </div>
                            <div class="fg form-span-2">
                                <label>Rubro / Actividad</label>
                                <input type="text" id="rubro" class="fi" placeholder="Ej: Comercio minorista, Gastronomía...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB: CONTACTO ══════════ -->
            <div class="tab-panel" id="tab-contacto">
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-phone"></i></div>
                        <h3>Datos de Contacto</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-grid-2" style="gap:16px;">
                            <div class="fg">
                                <label><i class="fas fa-phone" style="color:#10b981;margin-right:6px;"></i>Teléfono</label>
                                <input type="text" id="telefono" class="fi" placeholder="+54 11 1234-5678">
                            </div>
                            <div class="fg">
                                <label><i class="fab fa-whatsapp" style="color:#25d366;margin-right:6px;"></i>WhatsApp</label>
                                <input type="text" id="whatsapp" class="fi" placeholder="+54 9 11 1234-5678">
                            </div>
                            <div class="fg form-span-2">
                                <label><i class="fas fa-envelope" style="color:var(--primary);margin-right:6px;"></i>Email</label>
                                <input type="email" id="email" class="fi" placeholder="contacto@minegocio.com">
                            </div>
                            <div class="fg form-span-2">
                                <label><i class="fas fa-globe" style="color:#3b82f6;margin-right:6px;"></i>Sitio Web</label>
                                <input type="url" id="sitio_web" class="fi" placeholder="https://www.minegocio.com">
                            </div>
                            <div class="fg">
                                <label><i class="fab fa-instagram" style="color:#e1306c;margin-right:6px;"></i>Instagram</label>
                                <input type="text" id="instagram" class="fi" placeholder="@minegocio">
                            </div>
                            <div class="fg">
                                <label><i class="fab fa-facebook" style="color:#1877f2;margin-right:6px;"></i>Facebook</label>
                                <input type="text" id="facebook" class="fi" placeholder="facebook.com/minegocio">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB: UBICACIÓN ══════════ -->
            <div class="tab-panel" id="tab-ubicacion">
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <h3>Dirección</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-grid-2" style="gap:16px;">
                            <div class="fg form-span-2">
                                <label>Calle y Número</label>
                                <input type="text" id="direccion" class="fi" placeholder="Av. Corrientes 1234">
                            </div>
                            <div class="fg">
                                <label>Ciudad</label>
                                <input type="text" id="ciudad" class="fi" placeholder="Buenos Aires">
                            </div>
                            <div class="fg">
                                <label>Provincia / Estado</label>
                                <input type="text" id="provincia" class="fi" placeholder="CABA">
                            </div>
                            <div class="fg">
                                <label>Código Postal</label>
                                <input type="text" id="codigo_postal" class="fi" placeholder="C1043">
                            </div>
                            <div class="fg">
                                <label>País</label>
                                <input type="text" id="pais" class="fi" placeholder="Argentina">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB: LOGO ══════════ -->
            <div class="tab-panel" id="tab-logo">
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-image"></i></div>
                        <h3>Logo del Negocio</h3>
                    </div>
                    <div class="panel-body">
                        <div class="logo-upload-area">
                            <div class="logo-preview-big" onclick="document.getElementById('logoInput').click()" title="Clic para cambiar logo">
                                <img src="../../public/img/no-image.svg" alt="Logo" id="logoImg">
                            </div>
                            <div class="logo-upload-info">
                                <h4>Logo del Negocio</h4>
                                <p>
                                    Aparecerá en tickets, reportes y la tienda online.<br>
                                    <strong>Recomendado:</strong> 500×500px, formato PNG o JPG.<br>
                                    <strong>Tamaño máximo:</strong> 2 MB.
                                </p>
                                <div class="logo-btn-row">
                                    <input type="file" id="logoInput" accept="image/*" style="display:none;" onchange="perfilModule.subirLogo(event)">
                                    <button class="btn btn-primary" onclick="document.getElementById('logoInput').click()">
                                        <i class="fas fa-upload"></i> Subir Logo
                                    </button>
                                    <button class="btn btn-secondary" id="btnEliminarLogo" onclick="perfilModule.eliminarLogo()">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                                <p id="logoStatus" style="font-size:12px;color:var(--text-secondary);margin-top:10px;"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB: CARTA DIGITAL ══════════ -->
            <div class="tab-panel" id="tab-carta">
                <!-- Imagen de portada -->
                <div class="panel-card" style="margin-bottom:16px;">
                    <div class="panel-header">
                        <div class="panel-header-icon" style="background:rgba(15,209,134,.1);color:var(--primary)"><i class="fas fa-panorama"></i></div>
                        <h3>Imagen de Portada</h3>
                    </div>
                    <div class="panel-body">
                        <div class="logo-upload-area">
                            <!-- Preview de portada -->
                            <div id="portadaPreviewWrap" onclick="document.getElementById('portadaInput').click()" title="Clic para cambiar portada"
                                 style="width:220px;height:130px;border-radius:14px;border:2px dashed var(--border);background:var(--background);
                                        display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;
                                        cursor:pointer;transition:border-color .2s;position:relative;">
                                <img id="portadaImg" src="" alt="Portada" style="width:100%;height:100%;object-fit:cover;display:none;">
                                <div id="portadaPlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--text-secondary);">
                                    <i class="fas fa-panorama" style="font-size:28px;opacity:.4;"></i>
                                    <span style="font-size:12px;opacity:.6;">Sin imagen</span>
                                </div>
                            </div>
                            <div class="logo-upload-info">
                                <h4>Imagen de Portada de la Carta</h4>
                                <p>
                                    Se muestra como <strong>fondo del banner principal</strong> en tu carta digital.<br>
                                    <strong>Recomendado:</strong> imagen apaisada, 1280×480px o similar.<br>
                                    <strong>Formatos:</strong> JPG, PNG o WebP. <strong>Máximo:</strong> 5 MB.
                                </p>
                                <div class="logo-btn-row">
                                    <input type="file" id="portadaInput" accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none;" onchange="perfilModule.subirPortada(event)">
                                    <button class="btn btn-primary" onclick="document.getElementById('portadaInput').click()">
                                        <i class="fas fa-upload"></i> Subir Portada
                                    </button>
                                    <button class="btn btn-secondary" id="btnEliminarPortada" onclick="perfilModule.eliminarPortada()" style="display:none;">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                                <p id="portadaStatus" style="font-size:12px;color:var(--text-secondary);margin-top:10px;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- QR + Link + Control -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon" style="background:rgba(15,209,134,.1);color:var(--primary)"><i class="fas fa-qrcode"></i></div>
                        <h3>Acceso QR a la Carta</h3>
                    </div>
                    <div class="panel-body">
                        <div style="display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap;">

                            <!-- QR visual -->
                            <div style="flex-shrink:0;text-align:center;">
                                <div id="qrContainer" style="width:160px;height:160px;border:2px solid var(--border);border-radius:14px;background:var(--background);display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                    <i class="fas fa-qrcode" style="font-size:48px;color:var(--border);"></i>
                                </div>
                                <p style="font-size:11px;color:var(--text-secondary);margin-top:8px;">Escaneá para abrir la carta</p>
                            </div>

                            <!-- Info + controles -->
                            <div style="flex:1;min-width:220px;">
                                <!-- Toggle carta activa -->
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border:1.5px solid var(--border);border-radius:12px;background:var(--background);margin-bottom:16px;">
                                    <div>
                                        <div style="font-size:14px;font-weight:600;color:var(--text-primary);">Carta activa</div>
                                        <div style="font-size:12px;color:var(--text-secondary);">Activá o desactivá el acceso público</div>
                                    </div>
                                    <label class="switch-toggle">
                                        <input type="checkbox" id="cartaActivaToggle" onchange="perfilModule.toggleCarta(this.checked)">
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>

                                <!-- Link copiable -->
                                <div style="margin-bottom:16px;">
                                    <label style="font-size:13px;font-weight:500;color:var(--text-secondary);display:block;margin-bottom:6px;">Enlace de la carta</label>
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <input type="text" id="cartaLink" class="fi" readonly style="font-size:12px;cursor:pointer;background:var(--background);" value="Cargando...">
                                        <button class="btn btn-secondary" onclick="perfilModule.copiarLink()" style="flex-shrink:0;" title="Copiar enlace">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                    <a id="linkCartaDigital" href="#" target="_blank" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
                                        <i class="fas fa-external-link-alt"></i> Abrir carta
                                    </a>
                                    <button class="btn btn-secondary" onclick="perfilModule.regenerarToken()" title="Genera un nuevo QR e invalida el anterior">
                                        <i class="fas fa-sync-alt"></i> Nuevo QR
                                    </button>
                                </div>
                                <p style="font-size:11px;color:var(--text-secondary);margin-top:10px;">
                                    <i class="fas fa-info-circle"></i> Al generar un nuevo QR, el anterior deja de funcionar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB: TICKETS ══════════ -->
            <div class="tab-panel" id="tab-tickets">
                <div class="panel-card" style="margin-bottom:16px;">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-receipt"></i></div>
                        <h3>Mensaje en Ticket</h3>
                    </div>
                    <div class="panel-body">
                        <div class="fg">
                            <label>Mensaje de cierre del ticket</label>
                            <textarea id="mensaje_ticket" class="fi" rows="3" placeholder="¡Gracias por su compra! Vuelva pronto 😊"></textarea>
                            <span style="font-size:12px;color:var(--text-secondary);">Este mensaje aparece al final de cada ticket impreso.</span>
                        </div>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-sliders-h"></i></div>
                        <h3>Opciones de Visualización</h3>
                    </div>
                    <div class="panel-body">
                        <div class="ticket-switches">
                            <div class="switch-row">
                                <div class="switch-info">
                                    <h4>Mostrar Logo</h4>
                                    <p>Imprime el logo del negocio en la cabecera del ticket</p>
                                </div>
                                <label class="switch-toggle">
                                    <input type="checkbox" id="mostrar_logo_ticket">
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                            <div class="switch-row">
                                <div class="switch-info">
                                    <h4>Mostrar Dirección</h4>
                                    <p>Incluye la dirección del negocio en el ticket</p>
                                </div>
                                <label class="switch-toggle">
                                    <input type="checkbox" id="mostrar_direccion_ticket">
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                            <div class="switch-row">
                                <div class="switch-info">
                                    <h4>Mostrar CUIT</h4>
                                    <p>Muestra el CUIT / RUT en el encabezado del ticket</p>
                                </div>
                                <label class="switch-toggle">
                                    <input type="checkbox" id="mostrar_cuit_ticket">
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB: HORARIOS ══════════ -->
            <div class="tab-panel" id="tab-horarios">
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-header-icon"><i class="fas fa-clock"></i></div>
                        <h3>Horarios de Atención</h3>
                    </div>
                    <div class="panel-body">
                        <div class="horarios-grid">
                            <?php
                            $dias = [
                                'lunes'     => 'Lunes',
                                'martes'    => 'Martes',
                                'miercoles' => 'Miércoles',
                                'jueves'    => 'Jueves',
                                'viernes'   => 'Viernes',
                                'sabado'    => 'Sábado',
                                'domingo'   => 'Domingo',
                            ];
                            $defActivo = ['lunes','martes','miercoles','jueves','viernes'];
                            foreach ($dias as $key => $label):
                                $activo = in_array($key, $defActivo) ? 'checked' : '';
                            ?>
                            <div class="horario-row <?= $activo ? 'active-day' : '' ?>" id="horario-row-<?= $key ?>">
                                <label class="horario-toggle">
                                    <input type="checkbox" id="<?= $key ?>_activo" <?= $activo ?>
                                           onchange="perfilModule.toggleHorario('<?= $key ?>', this.checked)">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="horario-day"><?= $label ?></span>
                                <div class="horario-times">
                                    <input type="time" id="<?= $key ?>_desde" value="09:00">
                                    <span>—</span>
                                    <input type="time" id="<?= $key ?>_hasta" value="18:00">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── SAVE BAR ── -->
            <div class="save-bar">
                <button class="btn btn-primary" onclick="perfilModule.guardarCambios()" style="padding:12px 28px;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>

        </div>
    </main>

    <script src="../../public/js/perfil.js?v=<?= time() ?>"></script>
    <script>
        window.APP_BASE = '<?= $base ?>';

        // ── TABS ──
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            });
        });

        // ── ALERT ──
        let alertTimeout;
        function showAlert(message, type = 'info') {
            const el = document.getElementById('alertContainer');
            if (alertTimeout) clearTimeout(alertTimeout);
            const icons = { error:'exclamation-circle', success:'check-circle', warning:'exclamation-triangle', info:'info-circle' };
            el.className = `alert alert-${type}`;
            el.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i><span>${message}</span>`;
            el.classList.remove('hidden');
            alertTimeout = setTimeout(() => el.classList.add('hidden'), 3500);
        }

        // ── AUTH ──
        async function checkAuth() {
            try {
                const res = await fetch('../../api/auth/check.php', { credentials:'include' });
                const d   = await res.json();
                if (!d.success) { window.location.href='../../index.php'; return false; }
                const user = JSON.parse(localStorage.getItem('user')||'{}');
                const nombre = user.nombre || 'Usuario';
                document.getElementById('userName').textContent  = nombre;
                document.getElementById('userRole').textContent  = user.rol==='admin'?'Administrador':'Empleado';
                document.getElementById('userAvatar').textContent = nombre.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                if (user.rol !== 'admin') document.querySelectorAll('.admin-only').forEach(el=>el.style.display='none');
                return true;
            } catch { window.location.href='../../index.php'; return false; }
        }

        // Logout — manejado por header.php

        document.addEventListener('DOMContentLoaded', async () => {
            const ok = await checkAuth();
            if (ok) perfilModule = new PerfilModule();
        });
    </script>
</body>
</html>
