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
    <title>Habitaciones — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --hosp: #6366f1; --hosp-light: rgba(99,102,241,.1); }

        .hosp-layout { display:flex; min-height:100vh; }

        /* ── Toolbar ── */
        .hosp-toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .hosp-toolbar h1 { margin:0; font-size:20px; font-weight:700; color:var(--text-primary); }
        .hosp-toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        /* ── Stats bar ── */
        .stats-bar {
            display:flex; gap:12px; padding:16px 24px 0; flex-wrap:wrap;
        }
        .stat-pill {
            display:flex; align-items:center; gap:8px;
            padding:8px 16px; border-radius:20px; font-size:13px; font-weight:600;
            border:1.5px solid transparent;
        }
        .stat-pill .dot { width:10px; height:10px; border-radius:50%; }
        .stat-pill.total  { background:var(--background); border-color:var(--border); color:var(--text-primary); }
        .stat-pill.libre  { background:rgba(15,209,134,.1); border-color:#0FD186; color:#059669; }
        .stat-pill.ocupada{ background:rgba(239,68,68,.1);  border-color:#ef4444; color:#dc2626; }
        .stat-pill.limpieza{background:rgba(245,158,11,.1); border-color:#f59e0b; color:#d97706; }
        .stat-pill.mantenimiento{background:rgba(100,116,139,.1);border-color:#64748b;color:#475569;}

        /* ── Grid de habitaciones ── */
        .hab-grid {
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap:16px; padding:20px 24px;
        }

        /* ── Card habitación ── */
        .hab-card {
            background:var(--surface); border-radius:18px;
            border:2px solid var(--border);
            padding:0; overflow:hidden;
            transition:all .2s; cursor:pointer;
            display:flex; flex-direction:column;
        }
        .hab-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.1); }
        .hab-card.libre       { border-color:#0FD186; }
        .hab-card.ocupada     { border-color:#ef4444; }
        .hab-card.limpieza    { border-color:#f59e0b; }
        .hab-card.mantenimiento{ border-color:#64748b; }
        .hab-card.bloqueada   { border-color:#94a3b8; opacity:.6; }

        .hab-card-header {
            padding:14px 16px 10px;
            display:flex; align-items:center; justify-content:space-between;
        }
        .hab-numero {
            font-size:22px; font-weight:800; color:var(--text-primary);
            line-height:1;
        }
        .hab-estado-badge {
            font-size:10px; font-weight:700; padding:3px 10px;
            border-radius:20px; text-transform:uppercase; letter-spacing:.5px;
        }
        .badge-libre        { background:rgba(15,209,134,.15); color:#059669; }
        .badge-ocupada      { background:rgba(239,68,68,.15);  color:#dc2626; }
        .badge-limpieza     { background:rgba(245,158,11,.15); color:#d97706; }
        .badge-mantenimiento{ background:rgba(100,116,139,.15);color:#475569; }
        .badge-bloqueada    { background:rgba(148,163,184,.15);color:#64748b; }

        .hab-card-body { padding:0 16px 14px; flex:1; }
        .hab-tipo  { font-size:11px; color:var(--text-secondary); font-weight:600;
                     text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .hab-info  { font-size:12px; color:var(--text-secondary); display:flex; gap:10px; flex-wrap:wrap; }
        .hab-info span { display:flex; align-items:center; gap:4px; }

        .hab-huesped {
            font-size:12px; font-weight:600; color:var(--text-primary);
            background:var(--background); border-radius:8px;
            padding:6px 10px; margin:6px 0 0;
            display:flex; align-items:center; gap:6px;
        }
        .hab-checkout {
            font-size:11px; color:var(--text-secondary);
            display:flex; align-items:center; gap:4px; margin-top:4px;
        }

        .hab-card-footer {
            padding:10px 16px; border-top:1px solid var(--border);
            display:flex; gap:6px; justify-content:flex-end;
        }
        .btn-hab {
            padding:5px 10px; border-radius:8px; border:1px solid var(--border);
            background:var(--background); cursor:pointer; font-size:12px;
            color:var(--text-secondary); transition:all .15s; display:flex;
            align-items:center; gap:4px;
        }
        .btn-hab:hover { background:var(--hosp); color:#fff; border-color:var(--hosp); }
        .btn-hab.danger:hover { background:#ef4444; border-color:#ef4444; }
        .btn-hab.success:hover{ background:#0FD186; border-color:#0FD186; }

        /* ── Sección piso ── */
        .piso-section { padding:0 24px; }
        .piso-label {
            font-size:11px; font-weight:800; text-transform:uppercase;
            letter-spacing:.8px; color:var(--text-secondary);
            padding:16px 0 6px; border-bottom:1px solid var(--border);
            margin-bottom:0;
        }

        /* ── Modal ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px; }
        .fi { width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-primary); box-sizing:border-box; }
        .fi:focus { outline:none; border-color:var(--hosp); }
        .fg-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .fg-grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
        .amenities-list { display:flex; flex-wrap:wrap; gap:8px; }
        .amen-chip {
            padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
            border:1.5px solid var(--border); cursor:pointer; background:var(--background);
            color:var(--text-secondary); transition:all .15s; user-select:none;
        }
        .amen-chip.on { background:var(--hosp-light); border-color:var(--hosp); color:var(--hosp); }

        /* Toast */
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; pointer-events:none; }
        .toast.show { opacity:1; }

        @media (max-width:600px) {
            .hab-grid { grid-template-columns:repeat(2,1fr); gap:10px; padding:12px; }
            .stats-bar { padding:12px; }
            .hosp-toolbar { padding:12px; }
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content" style="flex:1; overflow-y:auto; padding:0;">
        <?php include '../includes/header.php'; ?>

        <!-- Toolbar -->
        <div class="hosp-toolbar">
            <div>
                <h1><i class="fas fa-bed" style="color:var(--hosp);margin-right:8px;"></i>Habitaciones</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="reservas.php" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-calendar-alt"></i> Reservas
                </a>
                <button class="btn btn-primary" onclick="abrirModalHab()">
                    <i class="fas fa-plus"></i> Nueva Habitación
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-bar" id="statsBar">
            <div class="stat-pill total"><div class="dot" style="background:#64748b;"></div><span id="st-total">0</span> Total</div>
            <div class="stat-pill libre"><div class="dot" style="background:#0FD186;"></div><span id="st-libre">0</span> Libres</div>
            <div class="stat-pill ocupada"><div class="dot" style="background:#ef4444;"></div><span id="st-ocupada">0</span> Ocupadas</div>
            <div class="stat-pill limpieza"><div class="dot" style="background:#f59e0b;"></div><span id="st-limpieza">0</span> Limpieza</div>
            <div class="stat-pill mantenimiento"><div class="dot" style="background:#64748b;"></div><span id="st-mant">0</span> Mant.</div>
        </div>

        <!-- Contenido -->
        <div id="habContent">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            </div>
        </div>

    </div><!-- /main-content -->
</div><!-- /dashboard-layout -->

<!-- Modal habitación -->
<div class="modal-overlay" id="modalHab">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalHabTitulo"><i class="fas fa-bed" style="color:var(--hosp);margin-right:8px;"></i>Nueva Habitación</h3>
            <button class="modal-close" onclick="cerrarModalHab()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="hId">
            <div class="fg-grid">
                <div class="fg">
                    <label>Número / Código <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="text" id="hNumero" placeholder="101, A2, Suite 1…">
                </div>
                <div class="fg">
                    <label>Piso / Sector</label>
                    <input class="fi" type="text" id="hPiso" placeholder="Planta baja, 1°, 2°…">
                </div>
            </div>
            <div class="fg">
                <label>Nombre / Descripción corta</label>
                <input class="fi" type="text" id="hNombre" placeholder="Suite presidencial, Cabaña del bosque…">
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Tipo</label>
                    <select class="fi" id="hTipo">
                        <option value="simple">Simple (1 cama)</option>
                        <option value="doble" selected>Doble (2 camas)</option>
                        <option value="triple">Triple</option>
                        <option value="suite">Suite</option>
                        <option value="cabaña">Cabaña</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Capacidad (personas)</label>
                    <input class="fi" type="number" id="hCapacidad" value="2" min="1" max="20">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Precio por noche <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="number" id="hPrecioNoche" placeholder="5000" min="0" step="100" oninput="calcTotal()">
                </div>
                <div class="fg">
                    <label>Precio por hora <small style="color:var(--text-secondary);">(motel)</small></label>
                    <input class="fi" type="number" id="hPrecioHora" placeholder="0" min="0" step="50">
                </div>
            </div>
            <div class="fg">
                <label>Amenities / Servicios</label>
                <div class="amenities-list" id="amenList">
                    <span class="amen-chip" data-val="wifi" onclick="toggleAmen(this)"><i class="fas fa-wifi"></i> WiFi</span>
                    <span class="amen-chip" data-val="tv" onclick="toggleAmen(this)"><i class="fas fa-tv"></i> TV</span>
                    <span class="amen-chip" data-val="aire" onclick="toggleAmen(this)"><i class="fas fa-snowflake"></i> Aire AC</span>
                    <span class="amen-chip" data-val="jacuzzi" onclick="toggleAmen(this)"><i class="fas fa-hot-tub"></i> Jacuzzi</span>
                    <span class="amen-chip" data-val="minibar" onclick="toggleAmen(this)"><i class="fas fa-wine-bottle"></i> Minibar</span>
                    <span class="amen-chip" data-val="desayuno" onclick="toggleAmen(this)"><i class="fas fa-coffee"></i> Desayuno</span>
                    <span class="amen-chip" data-val="estacionamiento" onclick="toggleAmen(this)"><i class="fas fa-parking"></i> Estac.</span>
                    <span class="amen-chip" data-val="caja_fuerte" onclick="toggleAmen(this)"><i class="fas fa-lock"></i> Caja fuerte</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalHab()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarHab()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal check-in rápido -->
<div class="modal-overlay" id="modalCheckin">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalCITitulo"><i class="fas fa-sign-in-alt" style="color:#0FD186;margin-right:8px;"></i>Check-in — Hab. <span id="ciHabNumero"></span></h3>
            <button class="modal-close" onclick="cerrarModalCI()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ciHabId">
            <div class="fg">
                <label>Nombre del huésped <span style="color:#ef4444;">*</span></label>
                <input class="fi" type="text" id="ciNombre" placeholder="Juan García">
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>DNI / Documento</label>
                    <input class="fi" type="text" id="ciDni" placeholder="12.345.678">
                </div>
                <div class="fg">
                    <label>Teléfono</label>
                    <input class="fi" type="tel" id="ciTel" placeholder="+54 9 11…">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Tipo de estadía</label>
                    <select class="fi" id="ciTipo" onchange="calcTotal()">
                        <option value="noche">Por noche</option>
                        <option value="hora">Por hora</option>
                        <option value="semana">Por semana</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Personas</label>
                    <input class="fi" type="number" id="ciPersonas" value="1" min="1" max="20">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Check-in</label>
                    <input class="fi" type="date" id="ciFechaIn" oninput="calcTotal()">
                </div>
                <div class="fg">
                    <label>Hora entrada</label>
                    <input class="fi" type="time" id="ciHoraIn" value="14:00">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Check-out</label>
                    <input class="fi" type="date" id="ciFechaOut" oninput="calcTotal()">
                </div>
                <div class="fg">
                    <label>Hora salida</label>
                    <input class="fi" type="time" id="ciHoraOut" value="10:00">
                </div>
            </div>
            <div style="background:var(--background);border-radius:12px;padding:14px;margin-top:6px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-secondary);margin-bottom:6px;">
                    <span>Precio unitario</span><span id="ciPrecioUnit">$0</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-secondary);margin-bottom:6px;">
                    <span id="ciUnidadesLabel">Noches</span><span id="ciUnidades">0</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:var(--text-primary);border-top:1px solid var(--border);padding-top:8px;margin-top:4px;">
                    <span>Total</span><span id="ciTotal" style="color:var(--hosp);">$0</span>
                </div>
            </div>
            <div class="fg" style="margin-top:12px;">
                <label>Seña / Adelanto</label>
                <input class="fi" type="number" id="ciSena" placeholder="0" min="0">
            </div>
            <div class="fg">
                <label>Método de pago</label>
                <select class="fi" id="ciMetodo">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="tarjeta_debito">💳 Tarjeta débito</option>
                    <option value="tarjeta_credito">💳 Tarjeta crédito</option>
                    <option value="transferencia">🏦 Transferencia</option>
                    <option value="qr">📱 QR / Billetera</option>
                </select>
            </div>
            <div class="fg">
                <label>Observaciones</label>
                <textarea class="fi" id="ciObs" rows="2" placeholder="Notas adicionales…" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalCI()">Cancelar</button>
            <button class="btn btn-secondary" onclick="guardarCheckin('reservada')"><i class="fas fa-calendar-check"></i> Solo reservar</button>
            <button class="btn btn-primary" onclick="guardarCheckin('checkin')" style="background:#0FD186;border-color:#0FD186;"><i class="fas fa-sign-in-alt"></i> Check-in ahora</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const BASE       = '<?= $base ?>';
const API_HAB    = BASE + '/api/hospedaje/habitaciones.php';
const API_RES    = BASE + '/api/hospedaje/reservas.php';

let habitaciones = [];
let habActual    = null; // para el modal de check-in

// ── Init ─────────────────────────────────────────────────────────────────────
async function init() {
    const r  = await fetch(API_HAB, {credentials:'include'});
    const j  = await r.json();
    if (!j.success) { toast('Error al cargar habitaciones', 'error'); return; }
    habitaciones = j.data.habitaciones || [];
    const stats  = j.data.stats || {};
    document.getElementById('subtitulo').textContent =
        `${stats.total || 0} habitaciones · ${stats.ocupada || 0} ocupadas · ${stats.libre || 0} libres`;
    document.getElementById('st-total').textContent   = stats.total   || 0;
    document.getElementById('st-libre').textContent   = stats.libre   || 0;
    document.getElementById('st-ocupada').textContent = stats.ocupada || 0;
    document.getElementById('st-limpieza').textContent= stats.limpieza|| 0;
    document.getElementById('st-mant').textContent    = stats.mantenimiento || 0;
    renderHabitaciones();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderHabitaciones() {
    const cont = document.getElementById('habContent');
    if (!habitaciones.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px 24px;color:var(--text-secondary);">
            <i class="fas fa-bed" style="font-size:48px;opacity:.15;display:block;margin-bottom:16px;"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:8px;">No hay habitaciones cargadas</p>
            <p style="font-size:13px;margin-bottom:20px;">Agregá tu primera habitación para empezar</p>
            <button class="btn btn-primary" onclick="abrirModalHab()"><i class="fas fa-plus"></i> Nueva Habitación</button>
        </div>`;
        return;
    }

    // Agrupar por piso
    const pisos = {};
    habitaciones.forEach(h => {
        const p = h.piso || 'Sin piso asignado';
        if (!pisos[p]) pisos[p] = [];
        pisos[p].push(h);
    });

    let html = '';
    Object.entries(pisos).forEach(([piso, habs]) => {
        html += `<div class="piso-section">
            <div class="piso-label"><i class="fas fa-layer-group" style="margin-right:6px;"></i>${esc(piso)}</div>
            <div class="hab-grid">
                ${habs.map(renderCard).join('')}
            </div>
        </div>`;
    });
    cont.innerHTML = html;
}

function renderCard(h) {
    const estado  = h.estado || 'libre';
    const amen    = parseAmen(h.amenities);
    const tipoLabel = {simple:'Simple',doble:'Doble',triple:'Triple',suite:'Suite','cabaña':'Cabaña',otro:'Otro'}[h.tipo] || h.tipo;

    let huesped = '';
    if (h.huesped_nombre && estado === 'ocupada') {
        huesped = `<div class="hab-huesped"><i class="fas fa-user"></i>${esc(h.huesped_nombre)}</div>`;
        if (h.checkout_fecha) {
            huesped += `<div class="hab-checkout"><i class="fas fa-sign-out-alt"></i>Checkout: ${formatFecha(h.checkout_fecha)}</div>`;
        }
    }

    const amenHtml = amen.slice(0,4).map(a => `<i class="fas ${amenIcon(a)}" title="${a}" style="font-size:11px;opacity:.5;"></i>`).join(' ');

    return `<div class="hab-card ${estado}" onclick="abrirAcciones(${h.id})">
        <div class="hab-card-header">
            <div class="hab-numero">${esc(h.numero)}</div>
            <span class="hab-estado-badge badge-${estado}">${estadoLabel(estado)}</span>
        </div>
        <div class="hab-card-body">
            <div class="hab-tipo">${tipoLabel} · ${h.capacidad} <i class="fas fa-user" style="font-size:10px;"></i></div>
            <div class="hab-info">
                <span><i class="fas fa-moon"></i>${fmt(h.precio_noche)}/noche</span>
                ${h.precio_hora ? `<span><i class="fas fa-clock"></i>${fmt(h.precio_hora)}/hora</span>` : ''}
            </div>
            ${huesped}
            ${amenHtml ? `<div style="margin-top:8px;display:flex;gap:6px;">${amenHtml}</div>` : ''}
        </div>
        <div class="hab-card-footer" onclick="event.stopPropagation()">
            ${estado === 'libre' || estado === 'reservada'
                ? `<button class="btn-hab success" onclick="abrirModalCI(${h.id})" title="Check-in"><i class="fas fa-sign-in-alt"></i></button>`
                : ''}
            ${estado === 'ocupada'
                ? `<button class="btn-hab" onclick="hacerCheckout(${h.id})" title="Checkout" style="background:rgba(239,68,68,.1);color:#dc2626;border-color:#ef4444;"><i class="fas fa-sign-out-alt"></i></button>`
                : ''}
            ${estado === 'limpieza'
                ? `<button class="btn-hab success" onclick="cambiarEstado(${h.id},'libre')" title="Marcar libre"><i class="fas fa-check"></i> Lista</button>`
                : ''}
            <button class="btn-hab" onclick="editarHab(${h.id})" title="Editar"><i class="fas fa-edit"></i></button>
        </div>
    </div>`;
}

// ── Cambiar estado ────────────────────────────────────────────────────────────
async function cambiarEstado(id, estado) {
    const r = await fetch(`${API_HAB}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({estado})
    });
    const j = await r.json();
    if (j.success) { toast(`Estado: ${estadoLabel(estado)} ✓`); init(); }
    else toast(j.message || 'Error', 'error');
}

async function hacerCheckout(habId) {
    const h = habitaciones.find(x => x.id == habId);
    if (!h || !h.reserva_id) {
        // Sin reserva activa registrada, solo cambiar estado
        await cambiarEstado(habId, 'limpieza');
        return;
    }
    if (!confirm(`¿Confirmar checkout de ${h.huesped_nombre}?`)) return;
    const r = await fetch(`${API_RES}?id=${h.reserva_id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({estado:'checkout'})
    });
    const j = await r.json();
    if (j.success) { toast('Checkout realizado ✓'); init(); }
    else toast(j.message || 'Error', 'error');
}

function abrirAcciones(id) {
    const h = habitaciones.find(x => x.id == id);
    if (!h) return;
    if (h.estado === 'libre') abrirModalCI(id);
    else editarHab(id);
}

// ── Modal Habitación ──────────────────────────────────────────────────────────
function abrirModalHab(h = null) {
    document.getElementById('hId').value      = h ? h.id : '';
    document.getElementById('hNumero').value  = h ? h.numero : '';
    document.getElementById('hPiso').value    = h ? (h.piso||'') : '';
    document.getElementById('hNombre').value  = h ? (h.nombre||'') : '';
    document.getElementById('hTipo').value    = h ? h.tipo : 'doble';
    document.getElementById('hCapacidad').value = h ? h.capacidad : 2;
    document.getElementById('hPrecioNoche').value = h ? h.precio_noche : '';
    document.getElementById('hPrecioHora').value  = h ? (h.precio_hora||'') : '';
    // Amenities
    const amen = parseAmen(h ? h.amenities : null);
    document.querySelectorAll('.amen-chip').forEach(c => {
        c.classList.toggle('on', amen.includes(c.dataset.val));
    });
    document.getElementById('modalHabTitulo').innerHTML = h
        ? `<i class="fas fa-edit" style="color:var(--hosp);margin-right:8px;"></i>Editar Hab. ${esc(h.numero)}`
        : `<i class="fas fa-bed" style="color:var(--hosp);margin-right:8px;"></i>Nueva Habitación`;
    document.getElementById('modalHab').classList.add('open');
    setTimeout(() => document.getElementById('hNumero').focus(), 100);
}

function cerrarModalHab() { document.getElementById('modalHab').classList.remove('open'); }

function editarHab(id) {
    const h = habitaciones.find(x => x.id == id);
    if (h) abrirModalHab(h);
}

function toggleAmen(el) { el.classList.toggle('on'); }

async function guardarHab() {
    const id     = document.getElementById('hId').value;
    const numero = document.getElementById('hNumero').value.trim();
    const pnoche = parseFloat(document.getElementById('hPrecioNoche').value) || 0;
    const phora  = parseFloat(document.getElementById('hPrecioHora').value)  || 0;
    if (!numero) { toast('Ingresá el número de habitación', 'error'); return; }
    if (!pnoche && !phora) { toast('Ingresá al menos un precio', 'error'); return; }

    const amenities = [...document.querySelectorAll('.amen-chip.on')].map(c => c.dataset.val);
    const body = {
        numero,
        nombre:       document.getElementById('hNombre').value.trim(),
        tipo:         document.getElementById('hTipo').value,
        piso:         document.getElementById('hPiso').value.trim(),
        capacidad:    parseInt(document.getElementById('hCapacidad').value) || 2,
        precio_noche: pnoche,
        precio_hora:  phora || null,
        amenities,
    };
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_HAB}?id=${id}` : API_HAB;
    const r = await fetch(url, { method, credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body) });
    const j = await r.json();
    if (j.success) { cerrarModalHab(); toast(id ? 'Habitación actualizada ✓' : 'Habitación creada ✓'); init(); }
    else toast(j.message || 'Error al guardar', 'error');
}

// ── Modal Check-in ────────────────────────────────────────────────────────────
function abrirModalCI(habId) {
    habActual = habitaciones.find(h => h.id == habId);
    if (!habActual) return;
    document.getElementById('ciHabId').value    = habId;
    document.getElementById('ciHabNumero').textContent = habActual.numero;
    document.getElementById('ciNombre').value   = '';
    document.getElementById('ciDni').value      = '';
    document.getElementById('ciTel').value      = '';
    document.getElementById('ciPersonas').value = 1;
    document.getElementById('ciTipo').value     = habActual.precio_hora ? 'hora' : 'noche';
    document.getElementById('ciSena').value     = '';
    document.getElementById('ciMetodo').value   = 'efectivo';
    document.getElementById('ciObs').value      = '';
    // Fechas por defecto: hoy → mañana
    const hoy     = new Date();
    const manana  = new Date(hoy); manana.setDate(manana.getDate() + 1);
    document.getElementById('ciFechaIn').value  = fmtDate(hoy);
    document.getElementById('ciFechaOut').value = fmtDate(manana);
    calcTotal();
    document.getElementById('modalCheckin').classList.add('open');
    setTimeout(() => document.getElementById('ciNombre').focus(), 100);
}

function cerrarModalCI() { document.getElementById('modalCheckin').classList.remove('open'); }

function calcTotal() {
    if (!habActual) return;
    const tipo   = document.getElementById('ciTipo')?.value || 'noche';
    const fi     = document.getElementById('ciFechaIn')?.value;
    const fo     = document.getElementById('ciFechaOut')?.value;
    if (!fi || !fo) return;
    const dias   = Math.max(1, Math.round((new Date(fo) - new Date(fi)) / 86400000));
    let precio   = tipo === 'hora' ? (parseFloat(habActual.precio_hora)||0) : parseFloat(habActual.precio_noche)||0;
    let unidades = tipo === 'semana' ? Math.max(1, Math.ceil(dias/7)) : dias;
    const label  = tipo === 'hora' ? 'Horas' : tipo === 'semana' ? 'Semanas' : 'Noches';
    const total  = precio * unidades;
    const el = (id) => document.getElementById(id);
    if (el('ciPrecioUnit'))    el('ciPrecioUnit').textContent   = fmt(precio);
    if (el('ciUnidades'))      el('ciUnidades').textContent     = unidades;
    if (el('ciUnidadesLabel')) el('ciUnidadesLabel').textContent= label;
    if (el('ciTotal'))         el('ciTotal').textContent        = fmt(total);
}

async function guardarCheckin(estado) {
    const habId  = document.getElementById('ciHabId').value;
    const nombre = document.getElementById('ciNombre').value.trim();
    const fi     = document.getElementById('ciFechaIn').value;
    const fo     = document.getElementById('ciFechaOut').value;
    if (!nombre) { toast('Ingresá el nombre del huésped', 'error'); return; }
    if (!fi || !fo) { toast('Completá las fechas', 'error'); return; }

    const tipo     = document.getElementById('ciTipo').value;
    const dias     = Math.max(1, Math.round((new Date(fo) - new Date(fi)) / 86400000));
    const noches   = tipo === 'semana' ? Math.max(1, Math.ceil(dias/7)) : dias;
    const precio   = tipo === 'hora' ? (parseFloat(habActual?.precio_hora)||0) : parseFloat(habActual?.precio_noche)||0;
    const total    = precio * noches;

    const body = {
        habitacion_id:    parseInt(habId),
        huesped_nombre:   nombre,
        huesped_dni:      document.getElementById('ciDni').value.trim(),
        huesped_telefono: document.getElementById('ciTel').value.trim(),
        tipo_estadia:     tipo,
        checkin_fecha:    fi,
        checkin_hora:     document.getElementById('ciHoraIn').value,
        checkout_fecha:   fo,
        checkout_hora:    document.getElementById('ciHoraOut').value,
        personas:         parseInt(document.getElementById('ciPersonas').value)||1,
        noches,
        precio_unitario:  precio,
        total,
        'seña':           parseFloat(document.getElementById('ciSena').value)||0,
        metodo_pago:      document.getElementById('ciMetodo').value,
        observaciones:    document.getElementById('ciObs').value.trim(),
        estado,
    };

    const r = await fetch(API_RES, {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) {
        cerrarModalCI();
        toast(estado === 'checkin' ? 'Check-in realizado ✓' : 'Reserva creada ✓');
        init();
    } else {
        toast(j.message || 'Error', 'error');
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoLabel(e) {
    return {libre:'Libre',ocupada:'Ocupada',limpieza:'Limpieza',mantenimiento:'Mantenimiento',bloqueada:'Bloqueada'}[e] || e;
}
function amenIcon(a) {
    return {wifi:'fa-wifi',tv:'fa-tv',aire:'fa-snowflake',jacuzzi:'fa-hot-tub',minibar:'fa-wine-bottle',desayuno:'fa-coffee',estacionamiento:'fa-parking',caja_fuerte:'fa-lock'}[a] || 'fa-star';
}
function parseAmen(raw) {
    if (!raw) return [];
    try { return JSON.parse(raw); } catch { return []; }
}
function fmt(n)     { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s)     { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtDate(d) { return d.toISOString().slice(0,10); }
function formatFecha(f) {
    if (!f) return '';
    const [y,m,d] = f.split('-');
    return `${d}/${m}/${y}`;
}
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

// Cerrar modales con Esc / clic fuera
['modalHab','modalCheckin'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { cerrarModalHab(); cerrarModalCI(); }
});

init();
</script>
</body>
</html>
