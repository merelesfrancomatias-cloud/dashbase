<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Check-in — Gimnasio</title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#0f172a; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }

  .card { background:#1e293b; border-radius:24px; padding:40px 32px; text-align:center; width:100%; max-width:400px; box-shadow:0 24px 64px rgba(0,0,0,.5); border:1px solid rgba(255,255,255,.08); }

  .gym-name { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#64748b; margin-bottom:28px; }

  .status-icon { width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:36px; margin:0 auto 20px; }
  .icon-ok      { background:rgba(16,185,129,.15); }
  .icon-warn    { background:rgba(245,158,11,.15); }
  .icon-error   { background:rgba(239,68,68,.15); }

  .status-title { font-size:26px; font-weight:800; margin-bottom:6px; }
  .title-ok    { color:#10b981; }
  .title-warn  { color:#f59e0b; }
  .title-error { color:#ef4444; }

  .socio-nombre { font-size:20px; font-weight:700; color:#f1f5f9; margin-bottom:4px; }
  .socio-plan   { font-size:14px; color:#64748b; margin-bottom:20px; }

  .info-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid rgba(255,255,255,.06); font-size:14px; }
  .info-row:last-child { border-bottom:none; }
  .info-lbl { color:#64748b; }
  .info-val { color:#f1f5f9; font-weight:600; }

  .hora-grande { font-size:48px; font-weight:800; color:#f1f5f9; letter-spacing:-2px; margin:16px 0 4px; font-variant-numeric:tabular-nums; }
  .fecha-txt { font-size:13px; color:#64748b; margin-bottom:24px; }

  .btn-volver { display:inline-flex; align-items:center; gap:8px; margin-top:28px; padding:10px 22px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:10px; color:#94a3b8; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; transition:.15s; }
  .btn-volver:hover { background:rgba(255,255,255,.1); color:#f1f5f9; }

  .spinner { width:40px; height:40px; border:3px solid rgba(255,255,255,.1); border-top-color:#10b981; border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 20px; }
  @keyframes spin { to { transform:rotate(360deg); } }

  .dias-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
  .dias-ok   { background:rgba(16,185,129,.15); color:#10b981; }
  .dias-warn { background:rgba(245,158,11,.15); color:#f59e0b; }
</style>
</head>
<body>

<div class="card" id="card">
  <div class="spinner" id="spinner"></div>
  <div style="color:#64748b;font-size:14px;">Verificando...</div>
</div>

<script>
const BASE   = '/DASHBASE';
const params = new URLSearchParams(location.search);
const token  = params.get('token') || '';

const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
const dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];

function fmtFecha(str) {
    if (!str) return '—';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
}

function diasRestantes(str) {
    if (!str) return null;
    const diff = Math.ceil((new Date(str) - new Date()) / 86400000);
    return diff;
}

function render(data) {
    const now = new Date();
    const horaStr  = now.toTimeString().substring(0, 5);
    const fechaStr = `${dias[now.getDay()]}, ${now.getDate()} ${meses[now.getMonth()]} ${now.getFullYear()}`;

    let iconClass, titleClass, titleTxt, extraHtml = '';

    if (!data.success && data.code === 'vencido') {
        iconClass  = 'icon-error'; titleClass = 'title-error'; titleTxt = 'Membresía vencida';
        extraHtml  = `<div class="info-row"><span class="info-lbl">Venció el</span><span class="info-val">${fmtFecha(data.vencimiento)}</span></div>`;
    } else if (!data.success && (data.code === 'suspendido' || data.code === 'inactivo')) {
        iconClass  = 'icon-error'; titleClass = 'title-error'; titleTxt = 'Membresía ' + data.code;
    } else if (!data.success) {
        iconClass  = 'icon-error'; titleClass = 'title-error'; titleTxt = 'QR no válido';
    } else if (data.code === 'ya_registrado') {
        iconClass  = 'icon-warn'; titleClass = 'title-warn'; titleTxt = 'Ya registrado';
        extraHtml  = `<div class="info-row"><span class="info-lbl">Hora de entrada</span><span class="info-val">${data.hora ? data.hora.substring(0,5) : '—'}</span></div>`;
    } else {
        iconClass  = 'icon-ok'; titleClass = 'title-ok'; titleTxt = '¡Bienvenido!';
        extraHtml  = `<div class="hora-grande">${horaStr}</div><div class="fecha-txt">${fechaStr}</div>`;
    }

    // Días restantes
    let diasPill = '';
    if (data.success && data.vencimiento) {
        const dr = diasRestantes(data.vencimiento);
        if (dr !== null && dr >= 0) {
            const cls = dr <= 7 ? 'dias-warn' : 'dias-ok';
            diasPill = `<span class="dias-pill ${cls}">${dr} día${dr !== 1 ? 's' : ''} restante${dr !== 1 ? 's' : ''}</span>`;
        }
    }

    const iconMap = {
        'icon-ok':    '✓',
        'icon-warn':  '⚠',
        'icon-error': '✕',
    };

    document.getElementById('card').innerHTML = `
        <div class="gym-name">${data.gimnasio || 'Gimnasio'}</div>
        <div class="status-icon ${iconClass}">${iconMap[iconClass]}</div>
        <div class="status-title ${titleClass}">${titleTxt}</div>
        ${data.nombre ? `<div class="socio-nombre">${data.nombre}</div>` : ''}
        ${data.plan   ? `<div class="socio-plan">${data.plan} ${diasPill}</div>` : ''}
        ${extraHtml}
        ${data.vencimiento && data.success && data.code !== 'ya_registrado'
            ? `<div class="info-row"><span class="info-lbl">Vence</span><span class="info-val">${fmtFecha(data.vencimiento)}</span></div>`
            : ''}
        <a class="btn-volver" onclick="window.close()" href="javascript:void(0)">Cerrar</a>
    `;
}

async function verificar() {
    if (!token) {
        render({ success: false, message: 'QR no válido', gimnasio: 'Gimnasio' });
        return;
    }
    try {
        const r = await fetch(`${BASE}/api/gym/checkin-publico.php?token=${encodeURIComponent(token)}`);
        const d = await r.json();
        render(d);
    } catch(e) {
        render({ success: false, message: 'Error de conexión', gimnasio: 'Gimnasio' });
    }
}

verificar();
</script>
</body>
</html>
