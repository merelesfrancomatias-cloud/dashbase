<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>Check-in — Kiosco</title>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<style>
* { box-sizing:border-box; margin:0; padding:0; -webkit-tap-highlight-color:transparent; }
html, body { height:100%; overflow:hidden; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#0f172a; color:#f1f5f9; user-select:none; }

/* ── Layout ── */
.kiosk { height:100vh; display:flex; flex-direction:column; }
.kiosk-header { padding:16px 28px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,.06); flex-shrink:0; }
.kiosk-title  { font-size:16px; font-weight:800; color:#f1f5f9; }
.kiosk-clock  { font-size:22px; font-weight:800; color:#f1f5f9; font-variant-numeric:tabular-nums; }
.kiosk-fecha  { font-size:12px; color:#64748b; text-align:right; }
.kiosk-body   { flex:1; display:flex; overflow:hidden; }

/* ── Panel izquierdo: escáner ── */
.scan-panel { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:32px; border-right:1px solid rgba(255,255,255,.06); gap:20px; }
.scan-title { font-size:18px; font-weight:700; color:#f1f5f9; text-align:center; }
.scan-sub   { font-size:13px; color:#64748b; text-align:center; }

#reader { width:260px; height:260px; border-radius:18px; overflow:hidden; border:3px solid rgba(249,115,22,.4); }
/* Ocultar elementos internos del scanner que no necesitamos */
#reader__dashboard_section_swaplink,
#reader__dashboard_section_csr button:last-child,
#html5-qrcode-anchor-scan-type-change { display:none !important; }
#html5-qrcode-button-camera-start { background:#f97316 !important; color:#fff !important; border:none !important; border-radius:8px !important; padding:8px 18px !important; font-weight:700 !important; cursor:pointer !important; }
#html5-qrcode-button-camera-stop { background:rgba(255,255,255,.1) !important; color:#94a3b8 !important; border:none !important; border-radius:8px !important; padding:6px 14px !important; cursor:pointer !important; }

.scan-orDivider { display:flex; align-items:center; gap:10px; width:260px; color:#475569; font-size:12px; }
.scan-orDivider::before, .scan-orDivider::after { content:''; flex:1; border-top:1px solid rgba(255,255,255,.08); }

.manual-form { display:flex; gap:8px; width:100%; max-width:320px; }
.manual-form input { flex:1; padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.06); color:#f1f5f9; font-size:14px; outline:none; }
.manual-form input:focus { border-color:rgba(249,115,22,.5); }
.manual-form input::placeholder { color:#475569; }
.btn-manual { padding:10px 18px; background:#f97316; color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; white-space:nowrap; }
.btn-manual:hover { background:#ea6c0a; }

/* ── Panel derecho: resultado + historial ── */
.result-panel { width:340px; flex-shrink:0; display:flex; flex-direction:column; padding:24px 20px; gap:16px; overflow-y:auto; }

.result-card { border-radius:16px; padding:20px; text-align:center; transition:.4s; }
.result-idle { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); }
.result-ok   { background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.25); }
.result-warn { background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.25); }
.result-err  { background:rgba(239,68,68,.1);   border:1px solid rgba(239,68,68,.2); }

.result-icon  { font-size:40px; margin-bottom:10px; }
.result-title { font-size:20px; font-weight:800; margin-bottom:4px; }
.result-name  { font-size:16px; font-weight:700; color:#f1f5f9; margin-bottom:2px; }
.result-sub   { font-size:13px; color:#94a3b8; }

/* Historial */
.hist-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#475569; }
.hist-list  { display:flex; flex-direction:column; gap:6px; overflow-y:auto; max-height:340px; }
.hist-item  { display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(255,255,255,.03); border-radius:10px; border:1px solid rgba(255,255,255,.05); }
.hist-avatar{ width:32px; height:32px; border-radius:50%; background:rgba(249,115,22,.15); color:#f97316; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0; }
.hist-nombre{ font-size:12px; font-weight:600; color:#f1f5f9; }
.hist-hora  { font-size:11px; color:#64748b; margin-left:auto; }
.hist-empty { text-align:center; padding:20px; color:#475569; font-size:13px; }

/* Cooldown overlay */
.cooldown { position:fixed; inset:0; background:rgba(0,0,0,.7); display:none; align-items:center; justify-content:center; z-index:100; }
.cooldown.show { display:flex; }
.cooldown-inner { text-align:center; }
.cooldown-num { font-size:80px; font-weight:900; color:#10b981; line-height:1; }
.cooldown-txt { font-size:16px; color:#94a3b8; margin-top:8px; }
</style>
</head>
<body>

<div class="kiosk">
    <div class="kiosk-header">
        <div>
            <div class="kiosk-title"><i class="fas fa-dumbbell" style="color:#f97316;margin-right:8px;"></i><span id="gymNombre">Gimnasio</span></div>
        </div>
        <div style="text-align:right;">
            <div class="kiosk-clock" id="reloj">00:00</div>
            <div class="kiosk-fecha"  id="fechaHoy"></div>
        </div>
    </div>

    <div class="kiosk-body">
        <!-- Escáner -->
        <div class="scan-panel">
            <div class="scan-title"><i class="fas fa-qrcode" style="color:#f97316;"></i> Escaneá tu QR</div>
            <div class="scan-sub">Colocá el código frente a la cámara</div>
            <div id="reader"></div>

            <div class="scan-orDivider">o ingresá tu nombre</div>

            <div class="manual-form">
                <input type="text" id="manualInput" placeholder="Buscar socio..." autocomplete="off">
                <button class="btn-manual" onclick="buscarManual()"><i class="fas fa-search"></i></button>
            </div>
            <div id="manualResultados" style="width:100%;max-width:320px;display:none;"></div>
        </div>

        <!-- Resultado + historial -->
        <div class="result-panel">
            <div class="result-card result-idle" id="resultCard">
                <div class="result-icon">👋</div>
                <div class="result-title" style="color:#64748b;">Esperando</div>
                <div class="result-sub">Escaneá tu QR para entrar</div>
            </div>

            <div class="hist-title">Últimas entradas</div>
            <div class="hist-list" id="histList">
                <div class="hist-empty">Sin registros aún</div>
            </div>
        </div>
    </div>
</div>

<!-- Overlay cooldown -->
<div class="cooldown" id="cooldown">
    <div class="cooldown-inner">
        <div class="cooldown-num" id="coolNum">3</div>
        <div class="cooldown-txt">Listo para el siguiente</div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
const BASE    = '/DASHBASE';
let scanning  = false;
let historial = [];

// ── Reloj ────────────────────────────────────────────────────────────────────
const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
const dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
function tick() {
    const n = new Date();
    document.getElementById('reloj').textContent   = n.toTimeString().substring(0, 5);
    document.getElementById('fechaHoy').textContent = `${dias[n.getDay()]}, ${n.getDate()} ${meses[n.getMonth()]}`;
}
tick(); setInterval(tick, 1000);

// ── Escáner QR ───────────────────────────────────────────────────────────────
const html5QrCode = new Html5Qrcode('reader');
html5QrCode.start(
    { facingMode: 'environment' },
    { fps: 10, qrbox: { width: 220, height: 220 } },
    async (decodedText) => {
        if (scanning) return;
        scanning = true;
        // Extraer token de la URL
        try {
            const url   = new URL(decodedText);
            const token = url.searchParams.get('token');
            if (token) await processToken(token);
            else       mostrarResultado('error', '✕', 'QR no válido', '');
        } catch(e) {
            mostrarResultado('error', '✕', 'QR no reconocido', '');
        }
        iniciarCooldown();
    },
    () => {}
).catch(err => {
    document.getElementById('reader').innerHTML = `<div style="padding:20px;text-align:center;color:#f97316;font-size:13px;">
        <i class="fas fa-camera" style="font-size:28px;margin-bottom:8px;display:block;"></i>
        Cámara no disponible.<br>Usá la búsqueda manual.
    </div>`;
});

// ── Procesar token ────────────────────────────────────────────────────────────
async function processToken(token) {
    try {
        const r = await fetch(`${BASE}/api/gym/checkin-publico.php?token=${encodeURIComponent(token)}`);
        const d = await r.json();

        if (!d.gimnasio || document.getElementById('gymNombre').textContent === 'Gimnasio') {
            if (d.gimnasio) document.getElementById('gymNombre').textContent = d.gimnasio;
        }

        if (!d.success && d.code === 'vencido') {
            mostrarResultado('err', '✕', 'Membresía vencida', d.nombre);
        } else if (!d.success && (d.code === 'suspendido' || d.code === 'inactivo')) {
            mostrarResultado('err', '✕', 'Membresía ' + d.code, d.nombre);
        } else if (!d.success) {
            mostrarResultado('err', '✕', 'QR no válido', '');
        } else if (d.code === 'ya_registrado') {
            mostrarResultado('warn', '⚠', 'Ya registrado', d.nombre, `Entró a las ${(d.hora||'').substring(0,5)}`);
        } else {
            mostrarResultado('ok', '✓', '¡Bienvenido!', d.nombre, d.plan || '');
            agregarHistorial(d.nombre, new Date().toTimeString().substring(0,5));
        }
    } catch(e) {
        mostrarResultado('err', '✕', 'Error de conexión', '');
    }
}

function mostrarResultado(tipo, icon, titulo, nombre, sub = '') {
    const card = document.getElementById('resultCard');
    card.className = `result-card result-${tipo === 'ok' ? 'ok' : tipo === 'warn' ? 'warn' : 'err'}`;
    const colors = { ok: '#10b981', warn: '#f59e0b', err: '#ef4444' };
    card.innerHTML = `
        <div class="result-icon">${icon}</div>
        <div class="result-title" style="color:${colors[tipo]||'#94a3b8'}">${titulo}</div>
        ${nombre ? `<div class="result-name">${nombre}</div>` : ''}
        ${sub     ? `<div class="result-sub">${sub}</div>`    : ''}
    `;
}

function agregarHistorial(nombre, hora) {
    historial.unshift({ nombre, hora });
    if (historial.length > 20) historial.pop();
    renderHistorial();
}

function renderHistorial() {
    if (!historial.length) return;
    document.getElementById('histList').innerHTML = historial.map(h => {
        const initials = h.nombre.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
        return `<div class="hist-item">
            <div class="hist-avatar">${initials}</div>
            <div class="hist-nombre">${h.nombre}</div>
            <div class="hist-hora">${h.hora}</div>
        </div>`;
    }).join('');
}

// ── Cooldown 3 segundos ──────────────────────────────────────────────────────
function iniciarCooldown() {
    let n = 3;
    const overlay = document.getElementById('cooldown');
    const num     = document.getElementById('coolNum');
    overlay.classList.add('show');
    num.textContent = n;
    const iv = setInterval(() => {
        n--;
        if (n <= 0) {
            clearInterval(iv);
            overlay.classList.remove('show');
            document.getElementById('resultCard').className = 'result-card result-idle';
            document.getElementById('resultCard').innerHTML = `<div class="result-icon">👋</div><div class="result-title" style="color:#64748b;">Esperando</div><div class="result-sub">Escaneá tu QR para entrar</div>`;
            scanning = false;
        } else {
            num.textContent = n;
        }
    }, 1000);
}

// ── Búsqueda manual ──────────────────────────────────────────────────────────
let manualTimeout;
document.getElementById('manualInput').addEventListener('input', function() {
    clearTimeout(manualTimeout);
    manualTimeout = setTimeout(() => buscarManual(), 400);
});
document.getElementById('manualInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') buscarManual();
});

async function buscarManual() {
    const q = document.getElementById('manualInput').value.trim();
    if (!q) { document.getElementById('manualResultados').style.display = 'none'; return; }
    try {
        const r = await fetch(`${BASE}/api/gym/socios.php?q=${encodeURIComponent(q)}&estado=activo`);
        const d = await r.json();
        const lista = (d.data?.socios || []).slice(0, 5);
        const cont = document.getElementById('manualResultados');
        if (!lista.length) {
            cont.style.display = 'block';
            cont.innerHTML = '<div style="padding:10px;text-align:center;font-size:12px;color:#64748b;">Sin resultados</div>';
            return;
        }
        cont.style.display = 'block';
        cont.innerHTML = `<div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;overflow:hidden;">` +
            lista.map(s => `
                <div onclick="checkinManual('${s.qr_token||''}', '${s.nombre} ${s.apellido}')"
                     style="padding:10px 14px;cursor:pointer;font-size:13px;color:#f1f5f9;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center;"
                     onmouseover="this.style.background='rgba(249,115,22,.1)'" onmouseout="this.style.background=''">
                    <span>${s.nombre} ${s.apellido}</span>
                    <span style="font-size:11px;color:#64748b;">${s.plan_nombre||''}</span>
                </div>`
            ).join('') + '</div>';
    } catch(e) {}
}

async function checkinManual(token, nombre) {
    if (scanning) return;
    scanning = true;
    document.getElementById('manualInput').value = '';
    document.getElementById('manualResultados').style.display = 'none';
    if (!token) {
        mostrarResultado('err', '✕', 'Sin QR asignado', nombre);
        iniciarCooldown();
        return;
    }
    await processToken(token);
    iniciarCooldown();
}
</script>
</body>
</html>
