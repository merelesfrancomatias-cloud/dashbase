<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja del Día - Canchas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        .date-nav { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
        .date-nav button { width:36px;height:36px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;color:var(--text-primary);font-size:14px;transition:var(--transition); }
        .date-nav button:hover { background:var(--primary);color:white;border-color:var(--primary); }
        .date-nav input[type=date] { padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text-primary); }
        .date-nav input[type=date]:focus { outline:none;border-color:var(--primary); }
        .date-label { font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap; }
        .metodo-badge { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .metodo-efectivo     { background:rgba(22,163,74,.12);color:#16a34a; }
        .metodo-transferencia{ background:rgba(59,130,246,.12);color:#3b82f6; }
        .metodo-tarjeta      { background:rgba(139,92,246,.12);color:#8b5cf6; }
        .cancha-row { display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border); }
        .cancha-row:last-child { border-bottom:none; }
        .progress-bar { height:6px;border-radius:3px;background:var(--border);overflow:hidden;margin-top:4px; }
        .progress-fill { height:100%;background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:3px;transition:width .4s; }
        .resumen-metodos { display:grid;grid-template-columns:repeat(3,1fr);gap:12px; }
        .metodo-card { background:var(--background);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center; }
        .metodo-card .monto { font-size:18px;font-weight:700; }
        .metodo-card .label { font-size:11px;color:var(--text-secondary);margin-top:4px; }
        table { width:100%;border-collapse:collapse;font-size:14px; }
        th { text-align:left;padding:10px 14px;background:var(--background);color:var(--text-secondary);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border); }
        td { padding:12px 14px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }
        .bar-chart { display:flex;align-items:flex-end;gap:8px;height:80px;padding-top:8px; }
        .bar-col { flex:1;display:flex;flex-direction:column;align-items:center;gap:4px; }
        .bar-rect { width:100%;border-radius:4px 4px 0 0;background:linear-gradient(180deg,#16a34a,#22c55e);transition:height .4s;min-height:2px; }
        .bar-date { font-size:10px;color:var(--text-secondary); }
        .bar-val  { font-size:10px;color:var(--text-secondary); }
        .empty-day { text-align:center;padding:40px;color:var(--text-secondary); }
        .empty-day i { font-size:36px;opacity:.25;display:block;margin-bottom:12px; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <!-- Page header -->
            <div class="page-header" style="background:var(--surface);padding:22px 24px;border-radius:14px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,.07);border:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                <div>
                    <h1 style="margin:0;font-size:22px;color:var(--text-primary);font-weight:700;">
                        <i class="fas fa-cash-register" style="color:#16a34a;margin-right:8px;"></i>Caja del Día
                    </h1>
                    <p style="margin:4px 0 0;color:var(--text-secondary);font-size:14px;" id="subtitulofecha">Resumen de ingresos</p>
                </div>
                <div class="date-nav">
                    <button onclick="cambiarDia(-1)" title="Día anterior"><i class="fas fa-chevron-left"></i></button>
                    <input type="date" id="inputFecha" onchange="cargarCaja()">
                    <button onclick="cambiarDia(1)" title="Día siguiente"><i class="fas fa-chevron-right"></i></button>
                    <button onclick="irHoy()" style="width:auto;padding:0 12px;font-size:13px;font-weight:600;" title="Hoy">Hoy</button>
                </div>
            </div>

            <!-- Stats principales -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;" id="statsRow">
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><div class="stat-value" id="st-ingresos">$0</div><div class="stat-label">Ingresos del día</div></div></div>
                <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div><div class="stat-info"><div class="stat-value" id="st-total">0</div><div class="stat-label">Total reservas</div></div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-info"><div class="stat-value" id="st-confirmadas">0</div><div class="stat-label">Confirmadas</div></div></div>
                <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div class="stat-info"><div class="stat-value" id="st-pendientes">0</div><div class="stat-label">Pendientes</div></div></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;" class="responsive-grid">

                <!-- Métodos de pago -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-wallet" style="color:#16a34a;margin-right:6px;"></i>Métodos de Pago</h3>
                    </div>
                    <div class="card-body">
                        <div class="resumen-metodos" id="metodosGrid">
                            <div class="metodo-card"><div class="monto" style="color:#16a34a;" id="met-efectivo">$0</div><div class="label"><i class="fas fa-money-bill-wave"></i> Efectivo</div></div>
                            <div class="metodo-card"><div class="monto" style="color:#3b82f6;" id="met-transferencia">$0</div><div class="label"><i class="fas fa-mobile-alt"></i> Transferencia</div></div>
                            <div class="metodo-card"><div class="monto" style="color:#8b5cf6;" id="met-tarjeta">$0</div><div class="label"><i class="fas fa-credit-card"></i> Tarjeta</div></div>
                        </div>
                    </div>
                </div>

                <!-- Por cancha -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-futbol" style="color:#16a34a;margin-right:6px;"></i>Ingresos por Cancha</h3>
                    </div>
                    <div class="card-body" id="porCanchaBody">
                        <p style="color:var(--text-secondary);text-align:center;padding:20px;">Sin datos</p>
                    </div>
                </div>
            </div>

            <!-- Gráfico 7 días -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar" style="color:#16a34a;margin-right:6px;"></i>Últimos 7 días</h3>
                </div>
                <div class="card-body">
                    <div class="bar-chart" id="barChart">
                        <p style="color:var(--text-secondary);margin:auto;">Cargando...</p>
                    </div>
                </div>
            </div>

            <!-- Detalle reservas confirmadas -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title"><i class="fas fa-list" style="color:#16a34a;margin-right:6px;"></i>Detalle de Reservas</h3>
                    <span id="badge-detalle" style="font-size:12px;color:var(--text-secondary);"></span>
                </div>
                <div class="card-body" id="detalleBody">
                    <div class="empty-day"><i class="fas fa-receipt"></i><p>Sin reservas confirmadas</p></div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media(max-width:768px) { .responsive-grid { grid-template-columns:1fr !important; } }
</style>

<script>
const fmt = n => '$' + Number(n || 0).toLocaleString('es-AR', {minimumFractionDigits:0});

function hoyStr() {
    const h = new Date();
    return h.getFullYear() + '-' + String(h.getMonth()+1).padStart(2,'0') + '-' + String(h.getDate()).padStart(2,'0');
}

function irHoy() {
    document.getElementById('inputFecha').value = hoyStr();
    cargarCaja();
}

function cambiarDia(d) {
    const inp = document.getElementById('inputFecha');
    const dt  = new Date(inp.value + 'T00:00:00');
    dt.setDate(dt.getDate() + d);
    inp.value = dt.getFullYear() + '-' + String(dt.getMonth()+1).padStart(2,'0') + '-' + String(dt.getDate()).padStart(2,'0');
    cargarCaja();
}

async function cargarCaja() {
    const fecha = document.getElementById('inputFecha').value;
    const r = await fetch(`../../api/canchas/caja.php?fecha=${fecha}`);
    const j = await r.json();
    if (!j.success) return;
    const { totales, porCancha, detalle, semana } = j.data;

    // Subtítulo
    const dt = new Date(fecha + 'T00:00:00');
    const opciones = {weekday:'long', day:'numeric', month:'long', year:'numeric'};
    const labelFecha = dt.toLocaleDateString('es-AR', opciones);
    document.getElementById('subtitulofecha').textContent = labelFecha.charAt(0).toUpperCase() + labelFecha.slice(1);

    // Stats
    document.getElementById('st-ingresos').textContent    = fmt(totales.ingresos_total);
    document.getElementById('st-total').textContent       = totales.total_reservas || 0;
    document.getElementById('st-confirmadas').textContent = totales.confirmadas || 0;
    document.getElementById('st-pendientes').textContent  = totales.pendientes || 0;

    // Métodos
    document.getElementById('met-efectivo').textContent      = fmt(totales.efectivo);
    document.getElementById('met-transferencia').textContent = fmt(totales.transferencia);
    document.getElementById('met-tarjeta').textContent       = fmt(totales.tarjeta);

    // Por cancha
    const maxIngreso = Math.max(...porCancha.map(c => parseFloat(c.ingresos || 0)), 1);
    document.getElementById('porCanchaBody').innerHTML = porCancha.length
        ? porCancha.map(c => {
            const pct = Math.round((parseFloat(c.ingresos || 0) / maxIngreso) * 100);
            return `
            <div class="cancha-row">
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary);">${esc(c.cancha_nombre)}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">${esc(c.deporte)} · ${c.reservas} reserva${c.reservas != 1 ? 's' : ''}</div>
                    <div class="progress-bar"><div class="progress-fill" style="width:${pct}%;"></div></div>
                </div>
                <div style="margin-left:16px;font-weight:700;color:${parseFloat(c.ingresos) > 0 ? '#16a34a' : 'var(--text-secondary)'};">${fmt(c.ingresos)}</div>
            </div>`;
        }).join('')
        : '<p style="text-align:center;color:var(--text-secondary);padding:16px;">Sin canchas</p>';

    // Gráfico de barras
    const maxBar = Math.max(...semana.map(s => parseFloat(s.ingresos || 0)), 1);
    document.getElementById('barChart').innerHTML = semana.length
        ? semana.map(s => {
            const h = Math.max(Math.round((parseFloat(s.ingresos || 0) / maxBar) * 68), 2);
            const esHoy = s.fecha === hoyStr();
            const d = new Date(s.fecha + 'T00:00:00');
            const lbl = d.toLocaleDateString('es-AR', {day:'2-digit',month:'2-digit'});
            return `
            <div class="bar-col">
                <div class="bar-val">${s.ingresos > 0 ? fmt(s.ingresos).replace('$','$') : ''}</div>
                <div class="bar-rect" style="height:${h}px;opacity:${esHoy ? 1 : 0.55};${esHoy ? 'box-shadow:0 2px 8px rgba(22,163,74,.4)' : ''}"></div>
                <div class="bar-date" style="font-weight:${esHoy ? 700 : 400};color:${esHoy ? '#16a34a' : ''}">${lbl}</div>
            </div>`;
        }).join('')
        : '<p style="color:var(--text-secondary);margin:auto;">Sin datos en los últimos 7 días</p>';

    // Detalle de reservas
    document.getElementById('badge-detalle').textContent = detalle.length + ' reserva' + (detalle.length !== 1 ? 's' : '') + ' confirmada' + (detalle.length !== 1 ? 's' : '');
    if (!detalle.length) {
        document.getElementById('detalleBody').innerHTML = '<div class="empty-day"><i class="fas fa-receipt"></i><p>Sin reservas confirmadas este día</p></div>';
    } else {
        document.getElementById('detalleBody').innerHTML = `
        <div style="overflow-x:auto;">
        <table>
            <thead><tr>
                <th>Cancha</th><th>Horario</th><th>Cliente</th><th>Teléfono</th><th>Monto</th><th>Método</th>
            </tr></thead>
            <tbody>
            ${detalle.map(r => `
                <tr>
                    <td><strong>${esc(r.cancha_nombre)}</strong><br><span style="font-size:11px;color:var(--text-secondary);">${esc(r.deporte)}</span></td>
                    <td style="white-space:nowrap;">${r.hora_inicio.slice(0,5)} – ${r.hora_fin.slice(0,5)}</td>
                    <td>${esc(r.cliente_nombre || '—')}</td>
                    <td style="color:var(--text-secondary);">${esc(r.cliente_telefono || '—')}</td>
                    <td style="font-weight:700;color:#16a34a;">${fmt(r.monto)}</td>
                    <td><span class="metodo-badge metodo-${r.metodo_pago}">${r.metodo_pago}</span></td>
                </tr>
            `).join('')}
            </tbody>
            <tfoot>
                <tr style="background:var(--background);">
                    <td colspan="4" style="font-weight:700;padding:12px 14px;">Total</td>
                    <td style="font-weight:700;color:#16a34a;padding:12px 14px;">${fmt(detalle.reduce((s,r) => s + parseFloat(r.monto || 0), 0))}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        </div>`;
    }
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Init
document.getElementById('inputFecha').value = hoyStr();
cargarCaja();
</script>
</body>
</html>
