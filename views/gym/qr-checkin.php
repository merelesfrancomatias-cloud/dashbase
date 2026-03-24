<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../index.php'); exit; }
$base = '/DASHBASE';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Check-in — Gimnasio</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
<link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
.qr-page { max-width:1100px; margin:0 auto; }

/* Kiosk banner */
.kiosk-banner { background:linear-gradient(135deg,#1e293b,#0f172a); border:1px solid rgba(249,115,22,.3); border-radius:16px; padding:24px 28px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.kiosk-info h2 { margin:0 0 4px; font-size:18px; font-weight:800; color:#f1f5f9; }
.kiosk-info p  { margin:0; font-size:13px; color:#94a3b8; }
.btn-kiosk { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:#f97316; color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; transition:.15s; }
.btn-kiosk:hover { background:#ea6c0a; }

/* Live feed */
.live-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; margin-bottom:24px; align-items:start; }
@media(max-width:900px) { .live-grid { grid-template-columns:1fr; } }

.feed-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.feed-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.feed-title { font-size:14px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px; }
.live-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; animation:pulse 1.4s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

.feed-list { max-height:420px; overflow-y:auto; }
.feed-row { display:flex; align-items:center; gap:12px; padding:12px 20px; border-bottom:1px solid var(--border); animation:slideIn .3s ease; }
.feed-row:last-child { border-bottom:none; }
@keyframes slideIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.feed-avatar { width:36px; height:36px; border-radius:50%; background:rgba(249,115,22,.15); color:#f97316; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; flex-shrink:0; }
.feed-nombre { font-size:13px; font-weight:600; color:var(--text-primary); }
.feed-hora   { font-size:12px; color:var(--text-secondary); }
.feed-plan   { font-size:11px; color:var(--text-secondary); }
.feed-empty  { text-align:center; padding:48px 20px; color:var(--text-secondary); }
.feed-empty i{ font-size:36px; opacity:.3; display:block; margin-bottom:10px; }

/* Stats rápidas */
.stat-mini { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:18px 20px; }
.stat-mini-num { font-size:2rem; font-weight:800; color:var(--text-primary); line-height:1; }
.stat-mini-lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary); margin-top:5px; }
.stats-col { display:flex; flex-direction:column; gap:12px; }

/* Grid QR tarjetas */
.qr-cards-section { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.qr-cards-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
.qr-cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; padding:20px; }
.qr-card-item { border:1px solid var(--border); border-radius:12px; padding:16px; text-align:center; cursor:pointer; transition:.15s; }
.qr-card-item:hover { border-color:var(--primary); background:rgba(var(--primary-rgb),.04); }
.qr-card-nombre { font-size:12px; font-weight:700; color:var(--text-primary); margin-top:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.qr-card-plan   { font-size:11px; color:var(--text-secondary); }
.qr-canvas-mini { display:inline-block; background:#fff; border-radius:8px; padding:6px; }

/* Modal QR */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; padding:20px; }
.modal-overlay.open { display:flex; }
.modal { background:var(--surface); border-radius:16px; width:100%; max-width:380px; box-shadow:0 20px 60px rgba(0,0,0,.2); border:1px solid var(--border); }
.modal-header { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.modal-header h3 { margin:0; font-size:16px; font-weight:700; color:var(--text-primary); }
.modal-close { background:none; border:none; cursor:pointer; color:var(--text-secondary); font-size:18px; }
.modal-close:hover { color:var(--error); }
.modal-body { padding:22px; text-align:center; }
.modal-footer { padding:14px 22px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:center; }
.qr-wrap { display:inline-block; padding:14px; background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.1); }

@media print {
    .no-print { display:none !important; }
    .qr-cards-grid { grid-template-columns:repeat(4,1fr); }
    .qr-card-item { border:1px solid #ddd; page-break-inside:avoid; }
    body, .main-content, .app-layout { background:#fff !important; }
}
</style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">
        <div class="qr-page">

            <!-- Header -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;" class="no-print">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,.12);display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:20px;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">QR Check-in</h1>
                    <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Control de acceso y gestión de códigos QR</p>
                </div>
            </div>

            <!-- Banner kiosko -->
            <div class="kiosk-banner no-print">
                <div class="kiosk-info">
                    <h2><i class="fas fa-tablet-screen-button" style="color:#f97316;margin-right:8px;"></i>Modo Kiosco</h2>
                    <p>Abrí en una tablet en la entrada del gimnasio. Los socios escanean su QR y se registra la asistencia automáticamente.</p>
                </div>
                <a href="kiosko.php" target="_blank" class="btn-kiosk">
                    <i class="fas fa-expand"></i> Abrir Kiosco
                </a>
            </div>

            <!-- Live feed + stats -->
            <div class="live-grid no-print">
                <div class="feed-card">
                    <div class="feed-header">
                        <div class="feed-title">
                            <div class="live-dot"></div>
                            Entradas de hoy
                        </div>
                        <span id="feedCount" style="font-size:13px;color:var(--text-secondary);font-weight:600;">— asistencias</span>
                    </div>
                    <div class="feed-list" id="feedList">
                        <div class="feed-empty"><i class="fas fa-person-walking-arrow-right"></i><p>Cargando...</p></div>
                    </div>
                </div>

                <div class="stats-col">
                    <div class="stat-mini">
                        <div class="stat-mini-num" id="statHoy">—</div>
                        <div class="stat-mini-lbl"><i class="fas fa-sun" style="color:#f97316;"></i> Ingresos hoy</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-num" id="statSemana">—</div>
                        <div class="stat-mini-lbl"><i class="fas fa-calendar-week" style="color:#6366f1;"></i> Esta semana</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-num" id="statMes">—</div>
                        <div class="stat-mini-lbl"><i class="fas fa-calendar" style="color:#22c55e;"></i> Este mes</div>
                    </div>
                    <div class="stat-mini" id="ultimoBox" style="display:none;">
                        <div class="stat-mini-num" id="statUltimo" style="font-size:1.4rem;">—</div>
                        <div class="stat-mini-lbl"><i class="fas fa-clock" style="color:#94a3b8;"></i> Último check-in</div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas QR para imprimir -->
            <div class="qr-cards-section">
                <div class="qr-cards-header">
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-id-card" style="color:#6366f1;"></i> Tarjetas QR de socios
                    </div>
                    <div style="display:flex;gap:8px;" class="no-print">
                        <div style="position:relative;">
                            <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                            <input type="text" id="searchQR" class="form-control" placeholder="Buscar socio..." oninput="filtrarTarjetas(this.value)" style="padding-left:36px;margin:0;width:200px;">
                        </div>
                        <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir todas</button>
                    </div>
                </div>
                <div class="qr-cards-grid" id="qrCardsGrid">
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                </div>
            </div>

        </div>
        </div>
    </div>
</div>

<!-- Modal QR ampliado -->
<div class="modal-overlay" id="modalQR">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalNombre"></h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="qr-wrap"><div id="qrGrande"></div></div>
            <p style="margin:12px 0 0;font-size:12px;color:var(--text-secondary);" id="modalPlan"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="descargarQR()"><i class="fas fa-download"></i> PNG</button>
            <button class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
const BASE = '<?= $base ?>';
let todosSocios = [], feedInterval;

async function init() {
    await Promise.all([cargarFeed(), cargarTarjetas()]);
    feedInterval = setInterval(cargarFeed, 15000); // refresh cada 15s
}

// ── Live feed ────────────────────────────────────────────────────────────────
async function cargarFeed() {
    try {
        const hoy = new Date().toISOString().split('T')[0];
        const r = await fetch(`${BASE}/api/gym/asistencias.php?fecha=${hoy}`);
        const d = await r.json();
        if (!d.success) return;

        const lista = d.data.asistencias || [];
        const total = d.data.total || 0;
        document.getElementById('feedCount').textContent = `${total} asistencia${total !== 1 ? 's' : ''}`;
        document.getElementById('statHoy').textContent = total;

        if (!lista.length) {
            document.getElementById('feedList').innerHTML = '<div class="feed-empty"><i class="fas fa-person-walking-arrow-right"></i><p>Sin entradas hoy</p></div>';
        } else {
            // Ordenar por hora desc (más reciente primero)
            const sorted = [...lista].sort((a,b) => (b.hora||'').localeCompare(a.hora||''));
            document.getElementById('feedList').innerHTML = sorted.map(a => {
                const initials = (a.socio_nombre||'?').split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
                const estadoBadge = a.socio_estado === 'vencido'
                    ? '<span style="font-size:10px;background:rgba(239,68,68,.12);color:#ef4444;padding:1px 6px;border-radius:6px;margin-left:4px;">Vencido</span>'
                    : '';
                return `<div class="feed-row">
                    <div class="feed-avatar">${initials}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="feed-nombre">${a.socio_nombre||'—'}${estadoBadge}</div>
                        <div class="feed-plan">${a.plan_nombre||''}</div>
                    </div>
                    <div class="feed-hora">${(a.hora||'').substring(0,5)}</div>
                </div>`;
            }).join('');

            document.getElementById('statUltimo').textContent = sorted[0].hora.substring(0,5);
            document.getElementById('ultimoBox').style.display = 'block';
        }

        // Stats semana y mes
        const lunes = (() => { const d = new Date(); d.setDate(d.getDate() - d.getDay() + 1); return d.toISOString().split('T')[0]; })();
        const primerMes = new Date(); primerMes.setDate(1);
        const [rSem, rMes] = await Promise.all([
            fetch(`${BASE}/api/gym/asistencias.php?desde=${lunes}&hasta=${hoy}`).then(r=>r.json()),
            fetch(`${BASE}/api/gym/asistencias.php?desde=${primerMes.toISOString().split('T')[0]}&hasta=${hoy}`).then(r=>r.json()),
        ]);
        if (rSem.success) document.getElementById('statSemana').textContent = rSem.data.total || 0;
        if (rMes.success) document.getElementById('statMes').textContent    = rMes.data.total || 0;

    } catch(e) {}
}

// ── Tarjetas QR ──────────────────────────────────────────────────────────────
async function cargarTarjetas() {
    try {
        const r = await fetch(`${BASE}/api/gym/socios.php?estado=activo`);
        const d = await r.json();
        if (!d.success) return;
        todosSocios = (d.data.socios || []).filter(s => s.qr_token);
        renderTarjetas(todosSocios);
    } catch(e) {}
}

function renderTarjetas(lista) {
    const grid = document.getElementById('qrCardsGrid');
    if (!lista.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-secondary);">Sin socios activos con QR</div>';
        return;
    }
    grid.innerHTML = lista.map(s => `
        <div class="qr-card-item" onclick="abrirQR(${s.id})" title="${s.nombre} ${s.apellido}">
            <div class="qr-canvas-mini" id="mini_${s.id}"></div>
            <div class="qr-card-nombre">${s.nombre} ${s.apellido}</div>
            <div class="qr-card-plan">${s.plan_nombre||'Sin plan'}</div>
        </div>
    `).join('');

    // Generar QR mini para cada socio
    lista.forEach(s => {
        const url = `${location.origin}${BASE}/views/gym/checkin.php?token=${s.qr_token}`;
        try {
            new QRCode(document.getElementById(`mini_${s.id}`), {
                text: url, width: 90, height: 90,
                colorDark: '#0f172a', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch(e) {}
    });
}

function filtrarTarjetas(q) {
    const filtrado = q
        ? todosSocios.filter(s => `${s.nombre} ${s.apellido}`.toLowerCase().includes(q.toLowerCase()))
        : todosSocios;
    renderTarjetas(filtrado);
}

// ── Modal QR grande ──────────────────────────────────────────────────────────
let qrModalInstance = null;
function abrirQR(id) {
    const s = todosSocios.find(x => x.id == id);
    if (!s) return;
    document.getElementById('modalNombre').textContent = `${s.nombre} ${s.apellido}`;
    document.getElementById('modalPlan').textContent   = s.plan_nombre || '';
    const canvas = document.getElementById('qrGrande');
    canvas.innerHTML = '';
    const url = `${location.origin}${BASE}/views/gym/checkin.php?token=${s.qr_token}`;
    qrModalInstance = new QRCode(canvas, {
        text: url, width: 200, height: 200,
        colorDark: '#0f172a', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    document.getElementById('modalQR').classList.add('open');
}

function cerrarModal() { document.getElementById('modalQR').classList.remove('open'); }

function descargarQR() {
    const c = document.querySelector('#qrGrande canvas');
    if (!c) return;
    const a = document.createElement('a');
    a.download = `qr-${document.getElementById('modalNombre').textContent.replace(/ /g,'-')}.png`;
    a.href = c.toDataURL('image/png');
    a.click();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalQR').addEventListener('click', e => { if (e.target === document.getElementById('modalQR')) cerrarModal(); });

init();
</script>
</body>
</html>
