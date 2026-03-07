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
    <title>Reservas — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --hosp:#6366f1; --hosp-light:rgba(99,102,241,.1); }

        .toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .toolbar h1 { margin:0; font-size:20px; font-weight:700; }
        .toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        /* filtros */
        .filtros { display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:14px 24px; background:var(--surface); border-bottom:1px solid var(--border); }
        .fi-sm { padding:7px 11px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; background:var(--background); color:var(--text-primary); }
        .fi-sm:focus { outline:none; border-color:var(--hosp); }
        .btn-f { padding:7px 14px; border-radius:9px; border:1.5px solid var(--border); background:var(--background); cursor:pointer; font-size:13px; color:var(--text-secondary); }
        .btn-f.on { background:var(--hosp-light); border-color:var(--hosp); color:var(--hosp); font-weight:600; }

        /* tabla */
        .res-table-wrap { overflow-x:auto; padding:16px 24px; }
        table.res-table { width:100%; border-collapse:separate; border-spacing:0 6px; min-width:800px; }
        table.res-table thead th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); padding:8px 12px; background:var(--background); }
        table.res-table thead th:first-child { border-radius:8px 0 0 8px; }
        table.res-table thead th:last-child  { border-radius:0 8px 8px 0; }
        table.res-table tbody tr { background:var(--surface); cursor:pointer; transition:.15s; }
        table.res-table tbody tr:hover { box-shadow:0 2px 12px rgba(0,0,0,.08); }
        table.res-table tbody td { padding:12px; border-top:1px solid var(--border); border-bottom:1px solid var(--border); font-size:13px; color:var(--text-primary); }
        table.res-table tbody td:first-child { border-left:1px solid var(--border); border-radius:10px 0 0 10px; padding-left:16px; }
        table.res-table tbody td:last-child  { border-right:1px solid var(--border); border-radius:0 10px 10px 0; padding-right:16px; }

        .badge {
            display:inline-block; padding:3px 10px; border-radius:20px;
            font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px;
        }
        .badge-reservada    { background:rgba(99,102,241,.12);  color:#4f46e5; }
        .badge-checkin      { background:rgba(15,209,134,.12);  color:#059669; }
        .badge-checkout     { background:rgba(100,116,139,.12); color:#475569; }
        .badge-cancelada    { background:rgba(239,68,68,.12);   color:#dc2626; }

        .accion-btn { padding:5px 9px; border-radius:7px; border:1.5px solid var(--border); background:var(--background); cursor:pointer; font-size:12px; color:var(--text-secondary); }
        .accion-btn:hover { background:var(--hosp); color:#fff; border-color:var(--hosp); }
        .accion-btn.g:hover { background:#0FD186; border-color:#0FD186; }
        .accion-btn.r:hover { background:#ef4444; border-color:#ef4444; }

        /* Modal detalle */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); flex-wrap:wrap; }
        .det-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px; }
        .det-row:last-child { border-bottom:none; }
        .det-row .lbl { color:var(--text-secondary); font-weight:500; }
        .det-row .val { font-weight:600; color:var(--text-primary); text-align:right; }
        .det-total { display:flex; justify-content:space-between; padding:12px 0 0; font-size:16px; font-weight:700; border-top:2px solid var(--border); }

        .empty-msg { text-align:center; padding:60px 24px; color:var(--text-secondary); }
        .empty-msg i { font-size:48px; opacity:.15; display:block; margin-bottom:16px; }

        /* ── Formulario modal ── */
        .fg { margin-bottom:14px; }
        .fg label {
            display:block; font-size:12px; font-weight:700;
            color:var(--text-secondary); margin-bottom:6px;
            text-transform:uppercase; letter-spacing:.5px;
        }
        .fi {
            width:100%; padding:10px 13px; border:1.5px solid var(--border);
            border-radius:10px; font-size:14px; background:var(--surface);
            color:var(--text-primary); box-sizing:border-box; transition:border-color .15s;
            font-family:inherit;
        }
        .fi:focus { outline:none; border-color:var(--hosp); box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        .fi-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .fi-grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }

        /* ── Sección del modal ── */
        .msec {
            background:var(--background); border-radius:14px;
            padding:16px; margin-bottom:14px;
        }
        .msec-title {
            font-size:11px; font-weight:800; text-transform:uppercase;
            letter-spacing:.6px; color:var(--text-secondary);
            margin:0 0 12px; display:flex; align-items:center; gap:6px;
        }
        .msec-title i { color:var(--hosp); }

        /* ── Resumen de precio ── */
        .precio-card {
            background: linear-gradient(135deg, var(--hosp) 0%, #4f46e5 100%);
            border-radius:14px; padding:16px 20px; margin-bottom:14px; color:#fff;
        }
        .precio-card-row {
            display:flex; justify-content:space-between; align-items:center;
            font-size:13px; opacity:.85; margin-bottom:6px;
        }
        .precio-card-total {
            display:flex; justify-content:space-between; align-items:center;
            font-size:20px; font-weight:800;
            border-top:1px solid rgba(255,255,255,.25); padding-top:10px; margin-top:4px;
        }

        /* ── Footer con gradiente sutil ── */
        .modal-footer-nueva {
            padding:16px 24px 20px; display:flex; gap:10px;
            justify-content:flex-end; flex-wrap:wrap;
            border-top:1px solid var(--border);
            background: linear-gradient(to bottom, var(--surface), var(--background));
            border-radius: 0 0 20px 20px;
        }

        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; pointer-events:none; white-space:nowrap; }
        .toast.show { opacity:1; }

        @media (max-width:700px) {
            .res-table-wrap { padding:10px; }
            .filtros { padding:10px 12px; }
            .toolbar { padding:12px; }
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
        <div class="toolbar">
            <div>
                <h1><i class="fas fa-calendar-check" style="color:var(--hosp);margin-right:8px;"></i>Reservas</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="habitaciones.php" class="btn btn-secondary" style="text-decoration:none;"><i class="fas fa-bed"></i> Habitaciones</a>
                <button class="btn btn-primary" onclick="abrirModalNueva()"><i class="fas fa-plus"></i> Nueva Reserva</button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros">
            <select class="fi-sm" id="fEstado" onchange="cargar()">
                <option value="">Todos los estados</option>
                <option value="reservada">Reservadas</option>
                <option value="checkin">En hotel (checkin)</option>
                <option value="checkout">Checkout</option>
                <option value="cancelada">Canceladas</option>
            </select>
            <input type="date" class="fi-sm" id="fDesde" onchange="cargar()" placeholder="Desde">
            <input type="date" class="fi-sm" id="fHasta" onchange="cargar()" placeholder="Hasta">
            <button class="btn-f" onclick="limpiarFiltros()"><i class="fas fa-times"></i> Limpiar</button>
        </div>

        <!-- Tabla -->
        <div class="res-table-wrap">
            <table class="res-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Habitación</th>
                        <th>Huésped</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                    <th>Estadia</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="resBody">
                <tr><td colspan="9" class="empty-msg"><i class="fas fa-spinner fa-spin" style="font-size:24px;opacity:.4;"></i></td></tr>
            </tbody>
        </table>
        </div><!-- /res-table-wrap -->

    </div><!-- /main-content -->
</div><!-- /dashboard-layout -->

<!-- Modal nueva reserva -->
<div class="modal-overlay" id="modalNueva">
    <div class="modal-box" style="max-width:600px;">

        <!-- Header con gradiente -->
        <div class="modal-header" style="background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);border-radius:20px 20px 0 0;border-bottom:none;padding:22px 24px 18px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h3 style="margin:0;color:#fff;font-size:17px;font-weight:700;">Nueva Reserva</h3>
                    <p style="margin:0;color:rgba(255,255,255,.7);font-size:12px;">Completá los datos del huésped</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalNueva()" style="color:rgba(255,255,255,.7);font-size:22px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:.15s;" onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background='transparent'">✕</button>
        </div>

        <div class="modal-body" style="padding:20px 24px 0;">

            <!-- Habitación -->
            <div class="msec">
                <p class="msec-title"><i class="fas fa-bed"></i> Habitación</p>
                <div class="fg" style="margin-bottom:0;">
                    <select class="fi" id="nHabId" onchange="onHabChange()" style="font-size:14px;">
                        <option value="">Seleccioná una habitación…</option>
                    </select>
                </div>
            </div>

            <!-- Huésped -->
            <div class="msec">
                <p class="msec-title"><i class="fas fa-user"></i> Datos del huésped</p>
                <div class="fg">
                    <label>Nombre completo <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="text" id="nNombre" placeholder="Ej: Juan García">
                </div>
                <div class="fi-grid2">
                    <div class="fg" style="margin-bottom:0;">
                        <label>DNI / Documento</label>
                        <input class="fi" type="text" id="nDni" placeholder="12.345.678">
                    </div>
                    <div class="fg" style="margin-bottom:0;">
                        <label>Teléfono</label>
                        <input class="fi" type="tel" id="nTel" placeholder="+54 9 11…">
                    </div>
                </div>
            </div>

            <!-- Estadía -->
            <div class="msec">
                <p class="msec-title"><i class="fas fa-calendar-alt"></i> Estadía</p>
                <div class="fi-grid2" style="margin-bottom:12px;">
                    <div class="fg" style="margin-bottom:0;">
                        <label>Tipo de estadía</label>
                        <select class="fi" id="nTipo" onchange="calcNueva()">
                            <option value="noche">🌙 Por noche</option>
                            <option value="hora">⏰ Por hora</option>
                            <option value="semana">📅 Por semana</option>
                        </select>
                    </div>
                    <div class="fg" style="margin-bottom:0;">
                        <label>Personas</label>
                        <input class="fi" type="number" id="nPersonas" value="1" min="1" max="20">
                    </div>
                </div>
                <div class="fi-grid2" style="margin-bottom:12px;">
                    <div>
                        <label class="fg" style="margin-bottom:6px;">
                            <span style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">Check-in <span style="color:#ef4444;">*</span></span>
                        </label>
                        <div style="display:flex;gap:6px;">
                            <input class="fi" type="date" id="nFechaIn" oninput="calcNueva()" style="flex:1.4;">
                            <input class="fi" type="time" id="nHoraIn" value="14:00" style="flex:1;">
                        </div>
                    </div>
                    <div>
                        <label class="fg" style="margin-bottom:6px;">
                            <span style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">Check-out <span style="color:#ef4444;">*</span></span>
                        </label>
                        <div style="display:flex;gap:6px;">
                            <input class="fi" type="date" id="nFechaOut" oninput="calcNueva()" style="flex:1.4;">
                            <input class="fi" type="time" id="nHoraOut" value="10:00" style="flex:1;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de precio -->
            <div class="precio-card">
                <div class="precio-card-row">
                    <span>Precio unitario</span>
                    <span id="nPrecioUnit" style="font-weight:600;">$0</span>
                </div>
                <div class="precio-card-row">
                    <span id="nUnidadesLabel">Noches</span>
                    <span id="nUnidades" style="font-weight:600;">0</span>
                </div>
                <div class="precio-card-total">
                    <span>Total</span>
                    <span id="nTotal">$0</span>
                </div>
            </div>

            <!-- Seña y obs -->
            <div class="fi-grid2" style="margin-bottom:12px;">
                <div class="fg" style="margin-bottom:0;">
                    <label>Seña / Adelanto</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-weight:600;">$</span>
                        <input class="fi" type="number" id="nSena" placeholder="0" min="0" style="padding-left:24px;">
                    </div>
                </div>
                <div class="fg" style="margin-bottom:0;">
                    <label>Método de pago</label>
                    <select class="fi" id="nMetodo">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta_debito">💳 Débito</option>
                        <option value="tarjeta_credito">💳 Crédito</option>
                        <option value="transferencia">🏦 Transferencia</option>
                        <option value="qr">📱 QR / Billetera</option>
                    </select>
                </div>
            </div>
            <div class="fg" style="margin-bottom:14px;">
                <label>Observaciones</label>
                <input class="fi" type="text" id="nObs" placeholder="Notas adicionales…">
            </div>

        </div><!-- /modal-body -->

        <div class="modal-footer-nueva">
            <button class="btn btn-secondary" onclick="cerrarModalNueva()">Cancelar</button>
            <button class="btn btn-secondary" onclick="guardarNueva('reservada')" style="border-color:var(--hosp);color:var(--hosp);">
                <i class="fas fa-calendar-check"></i> Solo reservar
            </button>
            <button class="btn btn-primary" onclick="guardarNueva('checkin')" style="background:linear-gradient(135deg,#0FD186,#059669);border:none;box-shadow:0 4px 12px rgba(15,209,134,.35);">
                <i class="fas fa-sign-in-alt"></i> Check-in ahora
            </button>
        </div>

    </div>
</div>

<!-- Modal detalle -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-calendar-check" style="color:var(--hosp);margin-right:8px;"></i>Detalle de Reserva <span id="detId" style="color:var(--text-secondary);font-weight:400;"></span></h3>
            <button class="modal-close" onclick="cerrarDetalle()">✕</button>
        </div>
        <div class="modal-body" id="detBody"></div>
        <div class="modal-footer" id="detFooter"></div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE    = '<?= $base ?>';
const API_RES = BASE + '/api/hospedaje/reservas.php';
const API_HAB = BASE + '/api/hospedaje/habitaciones.php';
let reservas     = [];
let habitaciones = [];

// Cargar habitaciones para el select
async function cargarHabitaciones() {
    const r = await fetch(API_HAB, {credentials:'include'});
    const j = await r.json();
    if (j.success) habitaciones = j.data.habitaciones || [];
}

async function cargar() {
    const params = new URLSearchParams();
    const est = document.getElementById('fEstado').value;
    const des = document.getElementById('fDesde').value;
    const has = document.getElementById('fHasta').value;
    if (est) params.append('estado', est);
    if (des) params.append('desde',  des);
    if (has) params.append('hasta',  has);
    const url = API_RES + (params.toString() ? '?' + params.toString() : '');
    const r   = await fetch(url, {credentials:'include'});
    const j   = await r.json();
    if (!j.success) { toast('Error al cargar', 'error'); return; }
    reservas = j.data || [];
    document.getElementById('subtitulo').textContent = `${reservas.length} reserva${reservas.length !== 1 ? 's' : ''}`;
    renderTabla();
}

function renderTabla() {
    const tbody = document.getElementById('resBody');
    if (!reservas.length) {
        tbody.innerHTML = `<tr><td colspan="9">
            <div class="empty-msg"><i class="fas fa-calendar-times"></i>
            <p style="font-size:15px;font-weight:600;margin:0 0 6px;">No hay reservas</p>
            <p style="font-size:13px;margin:0;">Cambiá los filtros o hacé un check-in desde Habitaciones.</p>
            </div></td></tr>`;
        return;
    }
    tbody.innerHTML = reservas.map(r => {
        const tipo_label = {noche:'Noche',hora:'Hora',semana:'Semana'}[r.tipo_estadia] || r.tipo_estadia;
        return `<tr onclick="verDetalle(${r.id})">
            <td style="color:var(--text-secondary);font-weight:600;">#${r.id}</td>
            <td><strong>${esc(r.hab_numero||r.habitacion_id)}</strong></td>
            <td>
                <div style="font-weight:600;">${esc(r.huesped_nombre)}</div>
                ${r.huesped_telefono ? `<div style="font-size:11px;color:var(--text-secondary);">${esc(r.huesped_telefono)}</div>` : ''}
            </td>
            <td>${fmtFecha(r.checkin_fecha)}<br><small style="color:var(--text-secondary);">${r.checkin_hora||''}</small></td>
            <td>${fmtFecha(r.checkout_fecha)}<br><small style="color:var(--text-secondary);">${r.checkout_hora||''}</small></td>
            <td>${r.noches} × ${tipo_label}</td>
            <td style="font-weight:700;">${fmt(r.total)}</td>
            <td><span class="badge badge-${r.estado}">${estadoLabel(r.estado)}</span></td>
            <td onclick="event.stopPropagation()">
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                    <button class="accion-btn" onclick="verDetalle(${r.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                    ${r.estado === 'reservada' ? `<button class="accion-btn g" onclick="cambiarEstado(${r.id},'checkin')" title="Check-in"><i class="fas fa-sign-in-alt"></i></button>` : ''}
                    ${r.estado === 'checkin'   ? `<button class="accion-btn" onclick="cambiarEstado(${r.id},'checkout')" title="Check-out" style="background:rgba(239,68,68,.1);color:#dc2626;border-color:#ef4444;"><i class="fas fa-sign-out-alt"></i></button>` : ''}
                    ${r.estado !== 'checkout' && r.estado !== 'cancelada' ? `<button class="accion-btn r" onclick="cancelar(${r.id})" title="Cancelar"><i class="fas fa-times"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');
}

async function cambiarEstado(id, estado) {
    const labels = {checkin:'Check-in', checkout:'Check-out'};
    if (!confirm(`¿Confirmar ${labels[estado] || estado}?`)) return;
    const r = await fetch(`${API_RES}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({estado})
    });
    const j = await r.json();
    if (j.success) { toast(`${labels[estado] || estado} realizado ✓`); cerrarDetalle(); cargar(); }
    else toast(j.message || 'Error', 'error');
}

async function cancelar(id) {
    if (!confirm('¿Cancelar esta reserva?')) return;
    await cambiarEstado(id, 'cancelada');
}

function verDetalle(id) {
    const r = reservas.find(x => x.id == id);
    if (!r) return;
    document.getElementById('detId').textContent = `#${r.id}`;
    const tipo_label = {noche:'Noche',hora:'Hora',semana:'Semana'}[r.tipo_estadia] || r.tipo_estadia;
    document.getElementById('detBody').innerHTML = `
        <div style="margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <span class="badge badge-${r.estado}" style="font-size:12px;padding:5px 14px;">${estadoLabel(r.estado)}</span>
            <span style="font-size:13px;color:var(--text-secondary);">Hab. ${esc(r.hab_numero||r.habitacion_id)}</span>
        </div>
        <div class="det-row"><span class="lbl">Huésped</span><span class="val">${esc(r.huesped_nombre)}</span></div>
        ${r.huesped_dni ? `<div class="det-row"><span class="lbl">Documento</span><span class="val">${esc(r.huesped_dni)}</span></div>` : ''}
        ${r.huesped_telefono ? `<div class="det-row"><span class="lbl">Teléfono</span><span class="val">${esc(r.huesped_telefono)}</span></div>` : ''}
        ${r.huesped_email ? `<div class="det-row"><span class="lbl">Email</span><span class="val">${esc(r.huesped_email)}</span></div>` : ''}
        <div class="det-row"><span class="lbl">Check-in</span><span class="val">${fmtFecha(r.checkin_fecha)} ${r.checkin_hora||''}</span></div>
        <div class="det-row"><span class="lbl">Check-out</span><span class="val">${fmtFecha(r.checkout_fecha)} ${r.checkout_hora||''}</span></div>
        <div class="det-row"><span class="lbl">Tipo estadía</span><span class="val">${tipo_label}</span></div>
        <div class="det-row"><span class="lbl">Personas</span><span class="val">${r.personas}</span></div>
        <div class="det-row"><span class="lbl">Precio unitario</span><span class="val">${fmt(r.precio_unitario)}</span></div>
        <div class="det-row"><span class="lbl">Unidades</span><span class="val">${r.noches}</span></div>
        ${r['seña'] > 0 ? `<div class="det-row"><span class="lbl">Seña / Adelanto</span><span class="val" style="color:#0FD186;">${fmt(r['seña'])}</span></div>` : ''}
        ${r.observaciones ? `<div class="det-row"><span class="lbl">Observaciones</span><span class="val" style="max-width:250px;text-align:right;">${esc(r.observaciones)}</span></div>` : ''}
        <div class="det-total"><span>Total</span><span style="color:var(--hosp);">${fmt(r.total)}</span></div>
    `;
    const footer = document.getElementById('detFooter');
    let btns = `<button class="btn btn-secondary" onclick="cerrarDetalle()">Cerrar</button>`;
    if (r.estado === 'reservada')
        btns += `<button class="btn btn-primary" onclick="cambiarEstado(${r.id},'checkin')" style="background:#0FD186;border-color:#0FD186;"><i class="fas fa-sign-in-alt"></i> Check-in</button>`;
    if (r.estado === 'checkin')
        btns += `<button class="btn btn-primary" onclick="cambiarEstado(${r.id},'checkout')" style="background:#ef4444;border-color:#ef4444;"><i class="fas fa-sign-out-alt"></i> Check-out</button>`;
    if (r.estado !== 'checkout' && r.estado !== 'cancelada')
        btns += `<button class="btn btn-secondary" onclick="cancelar(${r.id})" style="color:#dc2626;border-color:#ef4444;"><i class="fas fa-times"></i> Cancelar</button>`;
    footer.innerHTML = btns;
    document.getElementById('modalDetalle').classList.add('open');
}

function cerrarDetalle() { document.getElementById('modalDetalle').classList.remove('open'); }

function limpiarFiltros() {
    ['fEstado','fDesde','fHasta'].forEach(id => document.getElementById(id).value = '');
    cargar();
}

function estadoLabel(e) {
    return {reservada:'Reservada',checkin:'En hotel',checkout:'Checkout',cancelada:'Cancelada'}[e] || e;
}

// ── Modal Nueva Reserva ───────────────────────────────────────────────────────
function abrirModalNueva() {
    // Poblar select de habitaciones (libres primero)
    const sel = document.getElementById('nHabId');
    sel.innerHTML = '<option value="">Seleccioná una habitación…</option>';
    const libres = habitaciones.filter(h => h.estado === 'libre' || h.estado === 'reservada');
    const otras  = habitaciones.filter(h => h.estado !== 'libre' && h.estado !== 'reservada');
    if (libres.length) {
        const og = document.createElement('optgroup'); og.label = 'Disponibles';
        libres.forEach(h => {
            const o = document.createElement('option');
            o.value = h.id;
            o.textContent = `Hab. ${h.numero}${h.nombre ? ' — ' + h.nombre : ''} · $${Number(h.precio_noche||0).toLocaleString('es-AR')}/noche`;
            og.appendChild(o);
        });
        sel.appendChild(og);
    }
    if (otras.length) {
        const og = document.createElement('optgroup'); og.label = 'Otros estados';
        otras.forEach(h => {
            const o = document.createElement('option');
            o.value = h.id;
            o.textContent = `Hab. ${h.numero} (${h.estado})`;
            og.appendChild(o);
        });
        sel.appendChild(og);
    }
    // Resetear campos
    ['nNombre','nDni','nTel','nSena','nObs'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('nPersonas').value = 1;
    document.getElementById('nTipo').value     = 'noche';
    const hoy    = new Date();
    const manana = new Date(hoy); manana.setDate(manana.getDate() + 1);
    document.getElementById('nFechaIn').value  = hoy.toISOString().slice(0,10);
    document.getElementById('nFechaOut').value = manana.toISOString().slice(0,10);
    calcNueva();
    document.getElementById('modalNueva').classList.add('open');
    setTimeout(() => document.getElementById('nHabId').focus(), 100);
}

function cerrarModalNueva() { document.getElementById('modalNueva').classList.remove('open'); }

function onHabChange() { calcNueva(); }

function calcNueva() {
    const habId  = document.getElementById('nHabId').value;
    const hab    = habitaciones.find(h => h.id == habId);
    const tipo   = document.getElementById('nTipo').value;
    const fi     = document.getElementById('nFechaIn').value;
    const fo     = document.getElementById('nFechaOut').value;
    let precio   = 0;
    if (hab) precio = tipo === 'hora' ? (parseFloat(hab.precio_hora)||0) : parseFloat(hab.precio_noche)||0;
    let unidades = 1;
    if (fi && fo) {
        const dias = Math.max(1, Math.round((new Date(fo) - new Date(fi)) / 86400000));
        unidades   = tipo === 'semana' ? Math.max(1, Math.ceil(dias/7)) : dias;
    }
    const label = tipo === 'hora' ? 'Horas' : tipo === 'semana' ? 'Semanas' : 'Noches';
    document.getElementById('nPrecioUnit').textContent    = fmt(precio);
    document.getElementById('nUnidades').textContent      = unidades;
    document.getElementById('nUnidadesLabel').textContent = label;
    document.getElementById('nTotal').textContent         = fmt(precio * unidades);
}

async function guardarNueva(estado) {
    const habId  = document.getElementById('nHabId').value;
    const nombre = document.getElementById('nNombre').value.trim();
    const fi     = document.getElementById('nFechaIn').value;
    const fo     = document.getElementById('nFechaOut').value;
    if (!habId)  { toast('Seleccioná una habitación', 'error'); return; }
    if (!nombre) { toast('Ingresá el nombre del huésped', 'error'); return; }
    if (!fi||!fo){ toast('Completá las fechas', 'error'); return; }

    const hab      = habitaciones.find(h => h.id == habId);
    const tipo     = document.getElementById('nTipo').value;
    const dias     = Math.max(1, Math.round((new Date(fo) - new Date(fi)) / 86400000));
    const noches   = tipo === 'semana' ? Math.max(1, Math.ceil(dias/7)) : dias;
    const precio   = tipo === 'hora' ? (parseFloat(hab?.precio_hora)||0) : parseFloat(hab?.precio_noche)||0;
    const total    = precio * noches;

    const body = {
        habitacion_id:    parseInt(habId),
        huesped_nombre:   nombre,
        huesped_dni:      document.getElementById('nDni').value.trim(),
        huesped_telefono: document.getElementById('nTel').value.trim(),
        tipo_estadia:     tipo,
        checkin_fecha:    fi,
        checkin_hora:     document.getElementById('nHoraIn').value,
        checkout_fecha:   fo,
        checkout_hora:    document.getElementById('nHoraOut').value,
        personas:         parseInt(document.getElementById('nPersonas').value)||1,
        noches,
        precio_unitario:  precio,
        total,
        'seña':           parseFloat(document.getElementById('nSena').value)||0,
        metodo_pago:      document.getElementById('nMetodo').value,
        observaciones:    document.getElementById('nObs').value.trim(),
        estado,
    };

    const r = await fetch(API_RES, {
        method:'POST', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(body)
    });
    const j = await r.json();
    if (j.success) {
        cerrarModalNueva();
        toast(estado === 'checkin' ? 'Check-in realizado ✓' : 'Reserva creada ✓');
        await cargarHabitaciones();
        cargar();
    } else {
        toast(j.message || 'Error al guardar', 'error');
    }
}
function fmt(n)    { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s)    { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmtFecha(f) {
    if (!f) return '-';
    const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`;
}
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

document.getElementById('modalDetalle').addEventListener('click', e => {
    if (e.target.id === 'modalDetalle') cerrarDetalle();
});
document.getElementById('modalNueva').addEventListener('click', e => {
    if (e.target.id === 'modalNueva') cerrarModalNueva();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') { cerrarDetalle(); cerrarModalNueva(); } });

cargarHabitaciones();
cargar();
</script>
</body>
</html>
