<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes — Veterinaria</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --vet:#84cc16; --vet-dark:#65a30d; --vet-light:rgba(132,204,22,.1); }

        /* ── Toolbar ── */
        .vet-toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .vet-toolbar h1 { margin:0; font-size:20px; font-weight:700; }
        .vet-toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        /* ── Stats ── */
        .stats-bar { display:flex; gap:10px; padding:16px 24px 0; flex-wrap:wrap; }
        .stat-pill {
            display:flex; align-items:center; gap:8px;
            padding:7px 14px; border-radius:20px; font-size:13px; font-weight:600;
            border:1.5px solid var(--border); background:var(--background); color:var(--text-primary);
        }
        .stat-pill.verde  { background:var(--vet-light);           border-color:var(--vet);    color:var(--vet-dark); }
        .stat-pill.amarillo{ background:rgba(234,179,8,.1);         border-color:#eab308;       color:#a16207; }
        .stat-pill.indigo  { background:rgba(99,102,241,.1);        border-color:#6366f1;       color:#4338ca; }
        .stat-pill.rojo    { background:rgba(239,68,68,.1);         border-color:#ef4444;       color:#dc2626; }

        /* ── Filtros ── */
        .filtros-bar {
            padding:14px 24px 0; display:flex; gap:10px; flex-wrap:wrap; align-items:center;
        }
        .fi-search {
            flex:1; min-width:200px; padding:9px 14px 9px 36px;
            border:1.5px solid var(--border); border-radius:10px;
            font-size:14px; background:var(--surface); color:var(--text-primary);
        }
        .fi-search:focus { outline:none; border-color:var(--vet); }
        .search-wrap { position:relative; flex:1; min-width:200px; }
        .search-wrap i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-secondary); font-size:13px; }
        .chip-filter {
            padding:6px 14px; border-radius:20px; border:1.5px solid var(--border);
            background:var(--background); font-size:12px; font-weight:600;
            cursor:pointer; color:var(--text-secondary); transition:all .15s;
        }
        .chip-filter.on { background:var(--vet-light); border-color:var(--vet); color:var(--vet-dark); }

        /* ── Grid pacientes ── */
        .pac-grid {
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap:16px; padding:20px 24px;
        }

        /* ── Card paciente ── */
        .pac-card {
            background:var(--surface); border-radius:18px;
            border:2px solid var(--border); overflow:hidden;
            transition:all .2s; cursor:pointer;
            display:flex; flex-direction:column;
        }
        .pac-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.1); border-color:var(--vet); }

        .pac-card-top {
            padding:16px; display:flex; gap:12px; align-items:flex-start;
        }
        .pac-avatar {
            width:52px; height:52px; border-radius:14px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-size:24px; font-weight:700;
        }
        .pac-avatar.perro   { background:rgba(234,179,8,.15);  color:#d97706; }
        .pac-avatar.gato    { background:rgba(99,102,241,.15); color:#6366f1; }
        .pac-avatar.ave     { background:rgba(59,130,246,.15); color:#3b82f6; }
        .pac-avatar.conejo  { background:rgba(236,72,153,.15); color:#ec4899; }
        .pac-avatar.reptil  { background:rgba(132,204,22,.15); color:#65a30d; }
        .pac-avatar.otro    { background:rgba(100,116,139,.15);color:#475569; }

        .pac-info { flex:1; min-width:0; }
        .pac-nombre { font-size:16px; font-weight:700; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pac-especie { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); margin-top:2px; }
        .pac-raza { font-size:12px; color:var(--text-secondary); margin-top:2px; }

        .pac-badges { display:flex; gap:5px; flex-wrap:wrap; margin-top:6px; }
        .badge-sm {
            font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;
            text-transform:uppercase; letter-spacing:.4px;
        }
        .badge-macho   { background:rgba(59,130,246,.12); color:#2563eb; }
        .badge-hembra  { background:rgba(236,72,153,.12); color:#db2777; }
        .badge-estr    { background:rgba(132,204,22,.12); color:#65a30d; }

        .pac-card-body {
            padding:0 16px 14px; border-top:1px solid var(--border);
        }
        .pac-duenio {
            display:flex; align-items:center; gap:6px; padding:10px 0 6px;
            font-size:13px; color:var(--text-primary); font-weight:600;
        }
        .pac-duenio i { color:var(--text-secondary); font-size:11px; }
        .pac-meta {
            display:flex; gap:14px; font-size:11px; color:var(--text-secondary); flex-wrap:wrap;
        }
        .pac-meta span { display:flex; align-items:center; gap:4px; }

        .pac-card-footer {
            padding:8px 14px; border-top:1px solid var(--border);
            display:flex; gap:6px; justify-content:flex-end;
        }
        .btn-sm {
            padding:5px 10px; border-radius:8px; border:1px solid var(--border);
            background:var(--background); cursor:pointer; font-size:12px;
            color:var(--text-secondary); transition:all .15s; display:flex; align-items:center; gap:4px;
        }
        .btn-sm:hover { background:var(--vet); color:#fff; border-color:var(--vet); }
        .btn-sm.danger:hover { background:#ef4444; border-color:#ef4444; }

        /* ── Alerta vacunas ── */
        .vac-alerta {
            margin:14px 24px 0;
            background:rgba(234,179,8,.08); border:1.5px solid #eab308;
            border-radius:14px; padding:12px 16px;
        }
        .vac-alerta-title { font-size:12px; font-weight:800; color:#a16207; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; display:flex; align-items:center; gap:6px; }
        .vac-alerta-item { display:flex; justify-content:space-between; align-items:center; font-size:13px; padding:4px 0; }
        .vac-alerta-item .pac  { font-weight:600; color:var(--text-primary); }
        .vac-alerta-item .dias { font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px; }
        .dias.urgente { background:rgba(239,68,68,.12); color:#dc2626; }
        .dias.pronto  { background:rgba(234,179,8,.12);  color:#a16207; }
        .dias.ok      { background:rgba(132,204,22,.12); color:#65a30d; }

        /* ── Modales ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:600px; max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer-bar { padding:14px 24px 20px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block; font-size:12px; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .fi { width:100%; padding:10px 13px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-primary); box-sizing:border-box; transition:border-color .15s; }
        .fi:focus { outline:none; border-color:var(--vet); box-shadow:0 0 0 3px rgba(132,204,22,.12); }
        .fg-grid  { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .fg-grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
        .msec { background:var(--background); border-radius:14px; padding:16px; margin-bottom:14px; }
        .msec-title { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:var(--text-secondary); margin:0 0 12px; display:flex; align-items:center; gap:6px; }
        .msec-title i { color:var(--vet); }
        .toggle-chip {
            display:inline-flex; align-items:center; gap:6px; padding:7px 14px;
            border-radius:20px; border:1.5px solid var(--border); cursor:pointer;
            font-size:13px; font-weight:600; color:var(--text-secondary);
            background:var(--surface); transition:all .15s; user-select:none;
        }
        .toggle-chip.on { background:var(--vet-light); border-color:var(--vet); color:var(--vet-dark); }

        /* Toast */
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; pointer-events:none; }
        .toast.show { opacity:1; }

        @media(max-width:600px){
            .pac-grid { grid-template-columns:1fr; padding:12px; gap:10px; }
            .filtros-bar { padding:10px 12px 0; }
            .vet-toolbar { padding:12px; }
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
        <?php include '../includes/header.php'; ?>

        <!-- Toolbar -->
        <div class="vet-toolbar">
            <div>
                <h1><i class="fas fa-paw" style="color:var(--vet);margin-right:8px;"></i>Pacientes</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="agenda.php" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-calendar-alt"></i> Agenda
                </a>
                <button class="btn btn-primary" onclick="abrirModalPac()">
                    <i class="fas fa-plus"></i> Nuevo Paciente
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-bar" id="statsBar">
            <div class="stat-pill verde"><i class="fas fa-paw"></i> <span id="st-total">0</span> Pacientes</div>
            <div class="stat-pill amarillo"><i class="fas fa-dog"></i> <span id="st-perros">0</span> Perros</div>
            <div class="stat-pill indigo"><i class="fas fa-cat"></i> <span id="st-gatos">0</span> Gatos</div>
            <div class="stat-pill rojo"><i class="fas fa-calendar-day"></i> <span id="st-turnos">0</span> Turnos hoy</div>
        </div>

        <!-- Alerta vacunas próximas -->
        <div class="vac-alerta" id="vacAlerta" style="display:none;">
            <div class="vac-alerta-title"><i class="fas fa-syringe"></i> Vacunas próximas a vencer</div>
            <div id="vacAlertaList"></div>
        </div>

        <!-- Filtros -->
        <div class="filtros-bar">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input class="fi-search" type="text" id="searchInput" placeholder="Buscar por nombre, dueño o teléfono…" oninput="filtrar()">
            </div>
            <span class="chip-filter on" data-especie="" onclick="setEspecie(this, '')">Todos</span>
            <span class="chip-filter" data-especie="perro" onclick="setEspecie(this,'perro')"><i class="fas fa-dog"></i> Perros</span>
            <span class="chip-filter" data-especie="gato" onclick="setEspecie(this,'gato')"><i class="fas fa-cat"></i> Gatos</span>
            <span class="chip-filter" data-especie="ave" onclick="setEspecie(this,'ave')"><i class="fas fa-dove"></i> Aves</span>
            <span class="chip-filter" data-especie="otro" onclick="setEspecie(this,'otro')"><i class="fas fa-paw"></i> Otros</span>
        </div>

        <!-- Contenido -->
        <div id="pacContent" style="padding-bottom:24px;">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Nuevo/Editar Paciente ═══ -->
<div class="modal-overlay" id="modalPac">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#84cc16,#65a30d);border-radius:20px 20px 0 0;border-bottom:none;padding:20px 24px 16px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;">
                    <i class="fas fa-paw"></i>
                </div>
                <div>
                    <h3 id="modalPacTitulo" style="margin:0;color:#fff;font-size:16px;font-weight:700;">Nuevo Paciente</h3>
                    <p style="margin:0;color:rgba(255,255,255,.7);font-size:11px;">Ficha de la mascota</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalPac()" style="color:rgba(255,255,255,.8);">✕</button>
        </div>
        <input type="hidden" id="pId">
        <div class="modal-body">

            <!-- Especie chips -->
            <div class="msec">
                <p class="msec-title"><i class="fas fa-paw"></i> Especie</p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="toggle-chip on" data-val="perro"  onclick="selEspecie(this)">🐶 Perro</span>
                    <span class="toggle-chip"    data-val="gato"   onclick="selEspecie(this)">🐱 Gato</span>
                    <span class="toggle-chip"    data-val="ave"    onclick="selEspecie(this)">🐦 Ave</span>
                    <span class="toggle-chip"    data-val="conejo" onclick="selEspecie(this)">🐰 Conejo</span>
                    <span class="toggle-chip"    data-val="reptil" onclick="selEspecie(this)">🦎 Reptil</span>
                    <span class="toggle-chip"    data-val="otro"   onclick="selEspecie(this)">🐾 Otro</span>
                </div>
            </div>

            <!-- Datos mascota -->
            <div class="msec">
                <p class="msec-title"><i class="fas fa-id-card"></i> Datos de la mascota</p>
                <div class="fg">
                    <label>Nombre <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="text" id="pNombre" placeholder="Ej: Firulais">
                </div>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;">
                        <label>Raza</label>
                        <input class="fi" type="text" id="pRaza" placeholder="Labrador, Persa…">
                    </div>
                    <div class="fg" style="margin-bottom:0;">
                        <label>Color / Pelaje</label>
                        <input class="fi" type="text" id="pColor" placeholder="Negro, dorado…">
                    </div>
                </div>
                <div class="fg-grid" style="margin-top:12px;">
                    <div class="fg" style="margin-bottom:0;">
                        <label>Sexo</label>
                        <select class="fi" id="pSexo">
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                            <option value="desconocido">No especificado</option>
                        </select>
                    </div>
                    <div class="fg" style="margin-bottom:0;">
                        <label>Fecha de nacimiento</label>
                        <input class="fi" type="date" id="pFechaNac">
                    </div>
                </div>
                <div style="display:flex;gap:12px;margin-top:12px;align-items:center;">
                    <div class="fg" style="margin-bottom:0;flex:1;">
                        <label>Peso (kg)</label>
                        <input class="fi" type="number" id="pPeso" placeholder="0.0" step="0.1" min="0">
                    </div>
                    <div style="padding-top:22px;">
                        <span class="toggle-chip" id="chipEstr" onclick="this.classList.toggle('on')"><i class="fas fa-check-circle"></i> Esterilizado/a</span>
                    </div>
                </div>
            </div>

            <!-- Dueño -->
            <div class="msec">
                <p class="msec-title"><i class="fas fa-user"></i> Datos del dueño</p>
                <div class="fg">
                    <label>Nombre completo <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="text" id="pDuenio" placeholder="María González">
                </div>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;">
                        <label>Teléfono</label>
                        <input class="fi" type="tel" id="pTel" placeholder="+54 9 11…">
                    </div>
                    <div class="fg" style="margin-bottom:0;">
                        <label>Email</label>
                        <input class="fi" type="email" id="pEmail" placeholder="mail@ejemplo.com">
                    </div>
                </div>
                <div class="fg" style="margin-top:12px;margin-bottom:0;">
                    <label>Dirección</label>
                    <input class="fi" type="text" id="pDir" placeholder="Av. Siempreviva 742…">
                </div>
            </div>

            <!-- Observaciones -->
            <div class="fg">
                <label>Observaciones / Alergias conocidas</label>
                <textarea class="fi" id="pObs" rows="2" placeholder="Alérgico a la penicilina, condición crónica…" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer-bar">
            <button class="btn btn-secondary" onclick="cerrarModalPac()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarPac()" style="background:linear-gradient(135deg,#84cc16,#65a30d);border:none;">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- ═══ Modal Ficha Paciente (detalle + historial) ═══ -->
<div class="modal-overlay" id="modalFicha">
    <div class="modal-box" style="max-width:680px;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="fichaAvatar" class="pac-avatar perro" style="width:44px;height:44px;border-radius:12px;font-size:20px;display:flex;align-items:center;justify-content:center;">🐶</div>
                <div>
                    <h3 id="fichaNombre" style="margin:0;font-size:17px;font-weight:700;">-</h3>
                    <p id="fichaSubtitulo" style="margin:0;font-size:12px;color:var(--text-secondary);">-</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalFicha()">✕</button>
        </div>
        <div class="modal-body" id="fichaBody" style="padding:16px 24px;">
            <div style="text-align:center;padding:30px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const BASE     = '<?= $base ?>';
const API_PAC  = BASE + '/api/veterinaria/pacientes.php';
const API_CONS = BASE + '/api/veterinaria/consultas.php';
const API_VAC  = BASE + '/api/veterinaria/vacunas.php';

let pacientes     = [];
let filtroEspecie = '';

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    let r, j;
    try {
        r = await fetch(API_PAC, {credentials:'include'});
        j = await r.json();
    } catch(e) {
        document.getElementById('pacContent').innerHTML = `<div style="text-align:center;padding:60px 24px;color:var(--text-secondary);">
            <i class="fas fa-exclamation-triangle" style="font-size:48px;color:#ef4444;opacity:.5;display:block;margin-bottom:16px;"></i>
            <p style="font-size:15px;font-weight:600;margin-bottom:8px;color:#dc2626;">Error de conexión</p>
            <p style="font-size:13px;">No se pudo conectar con la API. Verificá que el servidor esté activo.</p>
        </div>`;
        document.getElementById('subtitulo').textContent = 'Error de conexión';
        return;
    }
    if (!j.success) {
        document.getElementById('pacContent').innerHTML = `<div style="text-align:center;padding:60px 24px;color:var(--text-secondary);">
            <i class="fas fa-lock" style="font-size:48px;color:#f59e0b;opacity:.5;display:block;margin-bottom:16px;"></i>
            <p style="font-size:15px;font-weight:600;margin-bottom:8px;">${j.message || 'Sin acceso'}</p>
            <p style="font-size:13px;margin-bottom:20px;">Tu sesión puede haber expirado.</p>
            <a href="../../index.php" class="btn btn-primary">Iniciar sesión</a>
        </div>`;
        document.getElementById('subtitulo').textContent = j.message || 'No autorizado';
        return;
    }

    pacientes = j.data.pacientes || [];
    const stats = j.data.stats || {};
    const prox  = j.data.proximas_vacunas || [];

    document.getElementById('subtitulo').textContent =
        `${stats.total || 0} pacientes registrados`;
    document.getElementById('st-total').textContent  = stats.total   || 0;
    document.getElementById('st-perros').textContent = stats.perros  || 0;
    document.getElementById('st-gatos').textContent  = stats.gatos   || 0;
    document.getElementById('st-turnos').textContent = stats.turnos_hoy || 0;

    // Alerta vacunas
    if (prox.length) {
        document.getElementById('vacAlerta').style.display = '';
        const hoy = new Date();
        document.getElementById('vacAlertaList').innerHTML = prox.map(v => {
            const dias = Math.round((new Date(v.proxima_dosis) - hoy) / 86400000);
            const cls  = dias <= 3 ? 'urgente' : dias <= 10 ? 'pronto' : 'ok';
            const lbl  = dias === 0 ? 'Hoy' : dias < 0 ? `Vencida` : `En ${dias}d`;
            return `<div class="vac-alerta-item">
                <div>
                    <span class="pac">${esc(v.pac_nombre)}</span>
                    <span style="color:var(--text-secondary);font-size:12px;margin-left:8px;">${esc(v.nombre)}</span>
                </div>
                <span class="dias ${cls}">${lbl}</span>
            </div>`;
        }).join('');
    } else {
        document.getElementById('vacAlerta').style.display = 'none';
    }

    filtrar();
}

function filtrar() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    let lista = pacientes;
    if (filtroEspecie) lista = lista.filter(p => p.especie === filtroEspecie);
    if (q) lista = lista.filter(p =>
        (p.nombre||'').toLowerCase().includes(q) ||
        (p.duenio_nombre||'').toLowerCase().includes(q) ||
        (p.duenio_telefono||'').includes(q)
    );
    renderPacientes(lista);
}

function setEspecie(el, val) {
    document.querySelectorAll('.chip-filter').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
    filtroEspecie = val;
    filtrar();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderPacientes(lista) {
    const cont = document.getElementById('pacContent');
    if (!lista.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px 24px;color:var(--text-secondary);">
            <i class="fas fa-paw" style="font-size:48px;opacity:.15;display:block;margin-bottom:16px;"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:8px;">No hay pacientes</p>
            <p style="font-size:13px;margin-bottom:20px;">Registrá tu primer paciente para empezar</p>
            <button class="btn btn-primary" onclick="abrirModalPac()"><i class="fas fa-plus"></i> Nuevo Paciente</button>
        </div>`;
        return;
    }
    cont.innerHTML = `<div class="pac-grid">${lista.map(renderCard).join('')}</div>`;
}

const especieEmoji = {perro:'🐶',gato:'🐱',ave:'🐦',conejo:'🐰',reptil:'🦎',otro:'🐾'};
const especieLabel = {perro:'Perro',gato:'Gato',ave:'Ave',conejo:'Conejo',reptil:'Reptil',otro:'Otro'};

function renderCard(p) {
    const emoji = especieEmoji[p.especie] || '🐾';
    const edad  = p.edad_anios != null ? `${p.edad_anios} año${p.edad_anios===1?'':'s'}` : '';
    const uc    = p.ultima_consulta ? `Últ. consulta: ${fmtFecha(p.ultima_consulta)}` : 'Sin consultas';
    const pv    = p.proxima_vacuna  ? `<span style="color:#a16207;"><i class="fas fa-syringe"></i> Vacuna: ${fmtFecha(p.proxima_vacuna)}</span>` : '';

    return `<div class="pac-card" onclick="abrirFicha(${p.id})">
        <div class="pac-card-top">
            <div class="pac-avatar ${p.especie}">${emoji}</div>
            <div class="pac-info">
                <div class="pac-nombre">${esc(p.nombre)}</div>
                <div class="pac-especie">${especieLabel[p.especie] || p.especie}${p.raza ? ' · ' + esc(p.raza) : ''}</div>
                <div class="pac-badges">
                    ${p.sexo !== 'desconocido' ? `<span class="badge-sm badge-${p.sexo}">${p.sexo}</span>` : ''}
                    ${p.esterilizado ? `<span class="badge-sm badge-estr"><i class="fas fa-check"></i> Estéril</span>` : ''}
                    ${edad ? `<span class="badge-sm" style="background:var(--background);color:var(--text-secondary);">${edad}</span>` : ''}
                </div>
            </div>
        </div>
        <div class="pac-card-body">
            <div class="pac-duenio"><i class="fas fa-user"></i>${esc(p.duenio_nombre)}</div>
            <div class="pac-meta">
                ${p.duenio_telefono ? `<span><i class="fas fa-phone"></i>${esc(p.duenio_telefono)}</span>` : ''}
                ${p.peso ? `<span><i class="fas fa-weight"></i>${p.peso} kg</span>` : ''}
                <span><i class="fas fa-history"></i>${uc}</span>
                ${pv}
            </div>
        </div>
        <div class="pac-card-footer" onclick="event.stopPropagation()">
            <button class="btn-sm" onclick="abrirModalConsulta(${p.id},'${esc(p.nombre)}')" title="Nueva consulta">
                <i class="fas fa-stethoscope"></i> Consulta
            </button>
            <button class="btn-sm" onclick="editarPac(${p.id})" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
        </div>
    </div>`;
}

// ── Modal Paciente ────────────────────────────────────────────────────────────
function abrirModalPac(p = null) {
    document.getElementById('pId').value       = p ? p.id : '';
    document.getElementById('pNombre').value   = p ? p.nombre : '';
    document.getElementById('pRaza').value     = p ? (p.raza||'') : '';
    document.getElementById('pColor').value    = p ? (p.color||'') : '';
    document.getElementById('pSexo').value     = p ? p.sexo : 'macho';
    document.getElementById('pFechaNac').value = p ? (p.fecha_nacimiento||'') : '';
    document.getElementById('pPeso').value     = p ? (p.peso||'') : '';
    document.getElementById('pDuenio').value   = p ? p.duenio_nombre : '';
    document.getElementById('pTel').value      = p ? (p.duenio_telefono||'') : '';
    document.getElementById('pEmail').value    = p ? (p.duenio_email||'') : '';
    document.getElementById('pDir').value      = p ? (p.duenio_direccion||'') : '';
    document.getElementById('pObs').value      = p ? (p.observaciones||'') : '';
    const estr = document.getElementById('chipEstr');
    p && p.esterilizado ? estr.classList.add('on') : estr.classList.remove('on');
    // Especie
    document.querySelectorAll('[data-val]').forEach(c => {
        c.classList.toggle('on', c.dataset.val === (p ? p.especie : 'perro'));
    });
    document.getElementById('modalPacTitulo').textContent = p ? `Editar — ${p.nombre}` : 'Nuevo Paciente';
    document.getElementById('modalPac').classList.add('open');
    setTimeout(() => document.getElementById('pNombre').focus(), 100);
}

function cerrarModalPac() { document.getElementById('modalPac').classList.remove('open'); }

function selEspecie(el) {
    document.querySelectorAll('[data-val]').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
}

function editarPac(id) {
    const p = pacientes.find(x => x.id == id);
    if (p) abrirModalPac(p);
}

async function guardarPac() {
    const id     = document.getElementById('pId').value;
    const nombre = document.getElementById('pNombre').value.trim();
    const duenio = document.getElementById('pDuenio').value.trim();
    const especie = document.querySelector('[data-val].on')?.dataset.val || 'perro';
    if (!nombre) { toast('Ingresá el nombre de la mascota', 'error'); return; }
    if (!duenio) { toast('Ingresá el nombre del dueño', 'error'); return; }

    const body = {
        nombre, especie,
        raza:             document.getElementById('pRaza').value.trim()   || null,
        color:            document.getElementById('pColor').value.trim()  || null,
        sexo:             document.getElementById('pSexo').value,
        fecha_nacimiento: document.getElementById('pFechaNac').value      || null,
        esterilizado:     document.getElementById('chipEstr').classList.contains('on') ? 1 : 0,
        peso:             parseFloat(document.getElementById('pPeso').value) || null,
        duenio_nombre:    duenio,
        duenio_telefono:  document.getElementById('pTel').value.trim()    || null,
        duenio_email:     document.getElementById('pEmail').value.trim()  || null,
        duenio_direccion: document.getElementById('pDir').value.trim()    || null,
        observaciones:    document.getElementById('pObs').value.trim()    || null,
    };
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_PAC}?id=${id}` : API_PAC;
    const r = await fetch(url, {method, credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalPac(); toast(id ? 'Paciente actualizado ✓' : 'Paciente creado ✓'); init(); }
    else toast(j.message || 'Error', 'error');
}

// ── Modal Ficha ───────────────────────────────────────────────────────────────
async function abrirFicha(id) {
    document.getElementById('modalFicha').classList.add('open');
    document.getElementById('fichaBody').innerHTML = '<div style="text-align:center;padding:30px;"><i class="fas fa-spinner fa-spin"></i></div>';
    const r = await fetch(`${API_PAC}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error', 'error'); return; }
    const p = j.data;

    document.getElementById('fichaNombre').textContent    = p.nombre;
    document.getElementById('fichaSubtitulo').textContent = `${especieLabel[p.especie]||p.especie}${p.raza?' · '+p.raza:''} — ${p.duenio_nombre}`;
    document.getElementById('fichaAvatar').textContent    = especieEmoji[p.especie] || '🐾';
    document.getElementById('fichaAvatar').className      = `pac-avatar ${p.especie}`;

    const cons = (p.consultas || []);
    const vacs = (p.vacunas   || []);

    document.getElementById('fichaBody').innerHTML = `
        <!-- Info rápida -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:18px;">
            ${infoChip('fa-weight','Peso', p.peso ? p.peso+' kg' : '—')}
            ${infoChip('fa-birthday-cake','Edad', p.edad_anios != null ? p.edad_anios+' años' : '—')}
            ${infoChip('fa-venus-mars','Sexo', {macho:'Macho',hembra:'Hembra',desconocido:'—'}[p.sexo]||'—')}
            ${infoChip('fa-check-circle','Esterilizado', p.esterilizado ? 'Sí':'No')}
            ${infoChip('fa-phone','Teléfono', p.duenio_telefono||'—')}
        </div>

        <!-- Botón nueva consulta -->
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
            <button class="btn btn-primary" onclick="abrirModalConsulta(${p.id},'${esc(p.nombre)}')" style="background:linear-gradient(135deg,#84cc16,#65a30d);border:none;">
                <i class="fas fa-stethoscope"></i> Nueva consulta
            </button>
            <button class="btn btn-secondary" onclick="abrirModalVacuna(${p.id},'${esc(p.nombre)}')">
                <i class="fas fa-syringe"></i> Registrar vacuna
            </button>
            <button class="btn btn-secondary" onclick="cerrarModalFicha();editarPac(${p.id})">
                <i class="fas fa-edit"></i> Editar ficha
            </button>
        </div>

        <!-- Historial consultas -->
        <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin-bottom:10px;">
            <i class="fas fa-history" style="color:var(--vet);margin-right:5px;"></i>Historial de consultas (${cons.length})
        </div>
        ${cons.length ? cons.map(c => `
        <div style="background:var(--background);border-radius:12px;padding:12px 14px;margin-bottom:8px;border-left:3px solid ${tipoColor(c.tipo)};">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:8px;background:${tipoColor(c.tipo)}20;color:${tipoColor(c.tipo)};">${tipoLabel(c.tipo)}</span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-primary);">${c.motivo ? esc(c.motivo) : ''}</span>
                </div>
                <span style="font-size:11px;color:var(--text-secondary);">${fmtFecha(c.fecha)} ${(c.hora||'').slice(0,5)}</span>
            </div>
            ${c.diagnostico ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:3px;"><b>Diag:</b> ${esc(c.diagnostico)}</div>` : ''}
            ${c.tratamiento ? `<div style="font-size:12px;color:var(--text-secondary);"><b>Trat:</b> ${esc(c.tratamiento)}</div>` : ''}
            ${c.medicamentos ? `<div style="font-size:12px;color:var(--text-secondary);"><b>Meds:</b> ${esc(c.medicamentos)}</div>` : ''}
            <div style="display:flex;gap:14px;margin-top:6px;font-size:11px;color:var(--text-secondary);">
                ${c.peso_consulta ? `<span><i class="fas fa-weight"></i> ${c.peso_consulta} kg</span>` : ''}
                ${c.temperatura   ? `<span><i class="fas fa-thermometer-half"></i> ${c.temperatura}°C</span>` : ''}
                ${c.monto > 0     ? `<span style="color:#059669;font-weight:700;"><i class="fas fa-dollar-sign"></i> ${fmt(c.monto)}</span>` : ''}
                ${c.proximo_turno ? `<span style="color:#6366f1;"><i class="fas fa-calendar-check"></i> Próx: ${fmtFecha(c.proximo_turno)}</span>` : ''}
            </div>
        </div>`).join('') : '<p style="font-size:13px;color:var(--text-secondary);text-align:center;padding:16px 0;">Sin consultas registradas</p>'}

        <!-- Vacunas -->
        <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin:16px 0 10px;">
            <i class="fas fa-syringe" style="color:#eab308;margin-right:5px;"></i>Cartilla de vacunas (${vacs.length})
        </div>
        ${vacs.length ? `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;">
            ${vacs.map(v => {
                const hoy2 = new Date();
                const prx  = v.proxima_dosis ? new Date(v.proxima_dosis) : null;
                const dias2= prx ? Math.round((prx - hoy2)/86400000) : null;
                const clr  = !prx ? '#64748b' : dias2 < 0 ? '#ef4444' : dias2 <= 10 ? '#f59e0b' : '#84cc16';
                return `<div style="background:var(--background);border-radius:10px;padding:10px 12px;border-left:3px solid ${clr};">
                    <div style="font-size:13px;font-weight:700;color:var(--text-primary);">${esc(v.nombre)}</div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">${fmtFecha(v.fecha_aplicacion)}</div>
                    ${v.proxima_dosis ? `<div style="font-size:11px;margin-top:3px;color:${clr};font-weight:600;"><i class="fas fa-redo"></i> Próx: ${fmtFecha(v.proxima_dosis)}${dias2 !== null ? ` (${dias2 < 0 ? 'vencida' : dias2===0?'hoy':'en '+dias2+'d'})` : ''}</div>` : ''}
                </div>`;
            }).join('')}
        </div>` : '<p style="font-size:13px;color:var(--text-secondary);text-align:center;padding:16px 0;">Sin vacunas registradas</p>'}
    `;
}

function cerrarModalFicha() { document.getElementById('modalFicha').classList.remove('open'); }

function infoChip(icon, label, val) {
    return `<div style="background:var(--background);border-radius:10px;padding:10px 12px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-secondary);margin-bottom:3px;"><i class="fas ${icon}" style="color:var(--vet);margin-right:4px;"></i>${label}</div>
        <div style="font-size:14px;font-weight:700;color:var(--text-primary);">${val}</div>
    </div>`;
}

// ── Modal Nueva Consulta ──────────────────────────────────────────────────────
let consultaPacId = null;
function abrirModalConsulta(pacId, pacNombre) {
    consultaPacId = pacId;
    // Crear modal on-the-fly si no existe
    let m = document.getElementById('modalConsulta');
    if (!m) {
        m = document.createElement('div');
        m.id        = 'modalConsulta';
        m.className = 'modal-overlay';
        m.innerHTML = buildModalConsulta();
        document.body.appendChild(m);
    }
    document.getElementById('consHoy').value   = fmtDateToday();
    document.getElementById('consPacNombre').textContent = pacNombre;
    m.classList.add('open');
    setTimeout(() => document.getElementById('consMotivo')?.focus(), 100);
}
function cerrarModalConsulta() { document.getElementById('modalConsulta')?.classList.remove('open'); }

function buildModalConsulta() {
    return `<div class="modal-box" style="max-width:580px;">
        <div class="modal-header" style="background:linear-gradient(135deg,#0FD186,#059669);border-radius:20px 20px 0 0;border-bottom:none;padding:20px 24px 16px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;"><i class="fas fa-stethoscope"></i></div>
                <div>
                    <h3 style="margin:0;color:#fff;font-size:16px;font-weight:700;">Nueva Consulta</h3>
                    <p id="consPacNombre" style="margin:0;color:rgba(255,255,255,.75);font-size:11px;">-</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalConsulta()" style="color:rgba(255,255,255,.8);">✕</button>
        </div>
        <div class="modal-body">
            <div class="msec">
                <p class="msec-title" style="--vet:#0FD186;"><i class="fas fa-calendar" style="color:#0FD186;"></i> Fecha y tipo</p>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;"><label>Fecha</label><input class="fi" type="date" id="consHoy"></div>
                    <div class="fg" style="margin-bottom:0;"><label>Hora</label><input class="fi" type="time" id="consHora" value="09:00"></div>
                </div>
                <div class="fg" style="margin-top:12px;margin-bottom:0;">
                    <label>Tipo de atención</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;">
                        <span class="toggle-chip on" data-tipo="consulta" onclick="selTipo(this)">🩺 Consulta</span>
                        <span class="toggle-chip" data-tipo="vacuna" onclick="selTipo(this)">💉 Vacuna</span>
                        <span class="toggle-chip" data-tipo="cirugia" onclick="selTipo(this)">🔪 Cirugía</span>
                        <span class="toggle-chip" data-tipo="baño" onclick="selTipo(this)">🛁 Baño</span>
                        <span class="toggle-chip" data-tipo="control" onclick="selTipo(this)">✅ Control</span>
                        <span class="toggle-chip" data-tipo="urgencia" onclick="selTipo(this)">🚨 Urgencia</span>
                    </div>
                </div>
            </div>
            <div class="msec">
                <p class="msec-title"><i class="fas fa-stethoscope" style="color:#0FD186;"></i> Atención</p>
                <div class="fg"><label>Motivo de consulta</label><input class="fi" type="text" id="consMotivo" placeholder="Vómitos, revisión anual, herida…"></div>
                <div class="fg"><label>Diagnóstico</label><textarea class="fi" id="consDiag" rows="2" placeholder="Diagnóstico…" style="resize:vertical;"></textarea></div>
                <div class="fg"><label>Tratamiento</label><textarea class="fi" id="consTrat" rows="2" placeholder="Indicaciones…" style="resize:vertical;"></textarea></div>
                <div class="fg" style="margin-bottom:0;"><label>Medicamentos</label><input class="fi" type="text" id="consMeds" placeholder="Ej: Amoxicilina 250mg, 1 comp/12hs x 7 días"></div>
            </div>
            <div class="msec">
                <p class="msec-title"><i class="fas fa-ruler" style="color:#0FD186;"></i> Mediciones</p>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;"><label>Peso (kg)</label><input class="fi" type="number" id="consPeso" step="0.1" min="0" placeholder="0.0"></div>
                    <div class="fg" style="margin-bottom:0;"><label>Temperatura (°C)</label><input class="fi" type="number" id="consTemp" step="0.1" min="35" max="42" placeholder="38.5"></div>
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Próximo turno</label>
                    <input class="fi" type="date" id="consProx">
                </div>
                <div class="fg">
                    <label>Monto</label>
                    <input class="fi" type="number" id="consMonto" placeholder="0" min="0">
                </div>
            </div>
            <div class="fg" style="margin-bottom:0;">
                <label>Método de pago</label>
                <select class="fi" id="consMetodo">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="tarjeta_debito">💳 Débito</option>
                    <option value="tarjeta_credito">💳 Crédito</option>
                    <option value="transferencia">🏦 Transferencia</option>
                    <option value="qr">📱 QR</option>
                </select>
            </div>
        </div>
        <div class="modal-footer-bar">
            <button class="btn btn-secondary" onclick="cerrarModalConsulta()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarConsulta()" style="background:linear-gradient(135deg,#0FD186,#059669);border:none;">
                <i class="fas fa-save"></i> Guardar consulta
            </button>
        </div>
    </div>`;
}

function selTipo(el) {
    document.querySelectorAll('[data-tipo]').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
}

async function guardarConsulta() {
    const fecha = document.getElementById('consHoy').value;
    if (!fecha) { toast('Ingresá la fecha', 'error'); return; }
    const tipo = document.querySelector('[data-tipo].on')?.dataset.tipo || 'consulta';
    const body = {
        paciente_id:   consultaPacId,
        fecha,
        hora:          document.getElementById('consHora').value,
        tipo,
        motivo:        document.getElementById('consMotivo').value.trim()  || null,
        diagnostico:   document.getElementById('consDiag').value.trim()    || null,
        tratamiento:   document.getElementById('consTrat').value.trim()    || null,
        medicamentos:  document.getElementById('consMeds').value.trim()    || null,
        peso_consulta: parseFloat(document.getElementById('consPeso').value) || null,
        temperatura:   parseFloat(document.getElementById('consTemp').value) || null,
        proximo_turno: document.getElementById('consProx').value            || null,
        monto:         parseFloat(document.getElementById('consMonto').value)|| 0,
        metodo_pago:   document.getElementById('consMetodo').value,
        estado:        'atendido',
    };
    const r = await fetch(API_CONS, {method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalConsulta(); toast('Consulta registrada ✓'); init(); }
    else toast(j.message || 'Error', 'error');
}

// ── Modal Vacuna ──────────────────────────────────────────────────────────────
let vacunaPacId = null;
function abrirModalVacuna(pacId, pacNombre) {
    vacunaPacId = pacId;
    let m = document.getElementById('modalVacuna');
    if (!m) {
        m = document.createElement('div');
        m.id        = 'modalVacuna';
        m.className = 'modal-overlay';
        m.innerHTML = `<div class="modal-box" style="max-width:480px;">
            <div class="modal-header">
                <h3><i class="fas fa-syringe" style="color:#eab308;margin-right:8px;"></i>Registrar Vacuna — <span id="vacPacNombre"></span></h3>
                <button class="modal-close" onclick="cerrarModalVacuna()">✕</button>
            </div>
            <div class="modal-body">
                <div class="fg"><label>Nombre / Tipo de vacuna <span style="color:#ef4444;">*</span></label><input class="fi" type="text" id="vacNombre" placeholder="Antirrábica, Séxtuple, Leucemia felina…"></div>
                <div class="fg-grid">
                    <div class="fg" style="margin-bottom:0;"><label>Fecha de aplicación <span style="color:#ef4444;">*</span></label><input class="fi" type="date" id="vacFecha"></div>
                    <div class="fg" style="margin-bottom:0;"><label>Próxima dosis</label><input class="fi" type="date" id="vacProx"></div>
                </div>
                <div class="fg-grid" style="margin-top:12px;">
                    <div class="fg" style="margin-bottom:0;"><label>Lote</label><input class="fi" type="text" id="vacLote" placeholder="ABC123"></div>
                    <div class="fg" style="margin-bottom:0;"><label>Veterinario</label><input class="fi" type="text" id="vacVet" placeholder="Dr. Rodríguez"></div>
                </div>
            </div>
            <div class="modal-footer-bar">
                <button class="btn btn-secondary" onclick="cerrarModalVacuna()">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarVacuna()" style="background:#eab308;border-color:#eab308;">
                    <i class="fas fa-syringe"></i> Guardar
                </button>
            </div>
        </div>`;
        document.body.appendChild(m);
    }
    document.getElementById('vacPacNombre').textContent = pacNombre;
    document.getElementById('vacFecha').value = fmtDateToday();
    document.getElementById('vacNombre').value = '';
    document.getElementById('vacProx').value   = '';
    document.getElementById('vacLote').value   = '';
    document.getElementById('vacVet').value    = '';
    m.classList.add('open');
    setTimeout(() => document.getElementById('vacNombre')?.focus(), 100);
}
function cerrarModalVacuna() { document.getElementById('modalVacuna')?.classList.remove('open'); }

async function guardarVacuna() {
    const nombre = document.getElementById('vacNombre').value.trim();
    const fecha  = document.getElementById('vacFecha').value;
    if (!nombre) { toast('Ingresá el nombre de la vacuna', 'error'); return; }
    if (!fecha)  { toast('Ingresá la fecha', 'error'); return; }
    const body = {
        paciente_id:      vacunaPacId,
        nombre,
        fecha_aplicacion: fecha,
        proxima_dosis:    document.getElementById('vacProx').value  || null,
        lote:             document.getElementById('vacLote').value.trim() || null,
        veterinario:      document.getElementById('vacVet').value.trim()  || null,
    };
    const r = await fetch(API_VAC, {method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalVacuna(); toast('Vacuna registrada ✓'); init(); }
    else toast(j.message || 'Error', 'error');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function tipoColor(t) {
    return {consulta:'#6366f1',vacuna:'#eab308',cirugia:'#ef4444',baño:'#3b82f6',grooming:'#3b82f6',control:'#0FD186',urgencia:'#f97316'}[t] || '#64748b';
}
function tipoLabel(t) {
    return {consulta:'Consulta',vacuna:'Vacuna',cirugia:'Cirugía',baño:'Baño',grooming:'Grooming',control:'Control',urgencia:'Urgencia'}[t] || t;
}
function fmt(n)       { return '$' + Number(n||0).toLocaleString('es-AR'); }
function esc(s)       { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtFecha(f)  { if (!f) return '—'; const [y,m,d]=f.slice(0,10).split('-'); return `${d}/${m}/${y}`; }
function fmtDateToday(){ return new Date().toISOString().slice(0,10); }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

// Cerrar con Esc / clic fuera
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        cerrarModalPac(); cerrarModalFicha();
        cerrarModalConsulta(); cerrarModalVacuna();
    }
});
['modalPac','modalFicha'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});

init();
</script>
</body>
</html>
