<?php
session_start();
if (!isset($_SESSION['negocio_id'])) { header('Location: ../auth/login.php'); exit; }
$base = '/DASHBASE';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Caja del Día — Restaurant</title>
<link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css">
<link rel="stylesheet" href="<?= $base ?>/public/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ── Hero ── */
.caja-hero { background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:26px 28px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
.hero-left { display:flex;align-items:center;gap:16px; }
.hero-icon { width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0; }
.hero-icon.abierta  { background:rgba(16,185,129,.12);color:#10b981; }
.hero-icon.cerrada  { background:rgba(100,116,139,.1);color:var(--text-muted); }
.hero-icon.loading  { background:rgba(99,102,241,.1);color:#6366f1; }
.hero-info h2 { font-size:20px;font-weight:700;color:var(--text);margin:0 0 3px; }
.hero-info p  { font-size:13px;color:var(--text-muted);margin:0; }
.estado-pill { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;margin-top:6px; }
.estado-pill.abierta { background:rgba(16,185,129,.15);color:#10b981; }
.estado-pill.cerrada { background:rgba(100,116,139,.12);color:#64748b; }

/* ── KPI grid ── */
.kpi-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px; }
.kpi { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px 20px;position:relative;overflow:hidden; }
.kpi::before { content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--kc,var(--primary)); }
.kpi-lbl { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:6px; }
.kpi-val { font-size:1.75rem;font-weight:800;color:var(--text);line-height:1; }
.kpi-sub { font-size:12px;color:var(--text-muted);margin-top:4px; }

/* ── Dos columnas ── */
.two-col { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px; }
@media(max-width:768px){ .two-col { grid-template-columns:1fr; } }

/* ── Cards ── */
.sec-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden; }
.sec-head { padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;justify-content:space-between; }
.sec-head i { color:var(--primary); }

/* ── Tabla comandas ── */
.cmd-table { width:100%;border-collapse:collapse;font-size:13px; }
.cmd-table th { padding:9px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);border-bottom:1px solid var(--border);background:rgba(0,0,0,.02); }
.cmd-table td { padding:11px 16px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle; }
.cmd-table tr:last-child td { border-bottom:none; }
.cmd-table tr:hover td { background:rgba(var(--primary-rgb),.04); }
.mesa-badge { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(var(--primary-rgb),.12);color:var(--primary);font-weight:800;font-size:12px; }

/* ── Métodos de pago ── */
.pago-row { display:flex;align-items:center;gap:12px;padding:13px 20px;border-bottom:1px solid var(--border); }
.pago-row:last-child { border-bottom:none; }
.pago-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
.pago-lbl { flex:1;font-size:13px;font-weight:600;text-transform:capitalize;color:var(--text); }
.pago-bar-wrap { flex:2;padding:0 8px; }
.pago-bar { height:6px;border-radius:3px; }
.pago-total { font-size:13px;font-weight:700;color:var(--text); }
.pago-pct { font-size:11px;color:var(--text-muted);min-width:34px;text-align:right; }

/* ── Botones ── */
.btn-abrir { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-size:14px;font-weight:700;background:#10b981;color:#fff;transition:.15s; }
.btn-abrir:hover { background:#059669; }
.btn-cerrar { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-size:14px;font-weight:700;background:#ef4444;color:#fff;transition:.15s; }
.btn-cerrar:hover { background:#dc2626; }

/* ── Modal ── */
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px; }
.modal-overlay.open { display:flex; }
.modal { background:var(--surface);border-radius:16px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;border:1px solid var(--border);box-shadow:0 20px 60px rgba(0,0,0,.25); }
.modal-header { padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
.modal-header h3 { margin:0;font-size:17px;font-weight:700;color:var(--text); }
.modal-close { background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:18px;padding:4px; }
.modal-body { padding:24px; }
.modal-footer { padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end; }
.form-g { margin-bottom:16px; }
.form-g label { display:block;font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px; }
.form-g input, .form-g select, .form-g textarea { width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);box-sizing:border-box; }
.form-g input:focus, .form-g select:focus { outline:none;border-color:var(--primary); }
.cierre-resumen { background:rgba(0,0,0,.04);border-radius:10px;padding:16px;margin-bottom:16px; }
.cierre-row { display:flex;justify-content:space-between;font-size:13px;padding:5px 0;color:var(--text-muted); }
.cierre-row.total { font-weight:700;color:var(--text);border-top:1px solid var(--border);margin-top:6px;padding-top:10px; }
.dif-positiva { color:#10b981; } .dif-negativa { color:#ef4444; }

/* ── Empty / spin ── */
.rep-empty { text-align:center;padding:40px;color:var(--text-muted); }
.rep-empty i { font-size:2rem;display:block;margin-bottom:10px;opacity:.35; }
.spin { animation:spin .8s linear infinite; } @keyframes spin { to { transform:rotate(360deg); } }

/* ── Toast ── */
.toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:12px;padding:14px 18px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:10px;max-width:300px;font-size:13px; }
.toast.show { display:flex; }
.toast.err { border-left-color:#ef4444; }
</style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>
<div class="dashboard-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">

<!-- Hero -->
<div class="caja-hero" id="cajaHero">
    <div class="hero-left">
        <div class="hero-icon loading" id="heroIcon"><i class="fas fa-circle-notch fa-spin"></i></div>
        <div class="hero-info">
            <h2 id="heroTitulo">Cargando caja...</h2>
            <p id="heroSub">—</p>
            <div class="estado-pill" id="estadoPill"></div>
        </div>
    </div>
    <div id="heroAcciones"></div>
</div>

<!-- KPIs -->
<div class="kpi-grid" id="kpiGrid">
    <div class="kpi" style="grid-column:1/-1"><div class="rep-empty"><i class="fas fa-circle-notch spin"></i><p>Cargando...</p></div></div>
</div>

<!-- Comandas + Métodos de pago -->
<div class="two-col">
    <div class="sec-card">
        <div class="sec-head">
            <span><i class="fas fa-receipt"></i> Comandas del día</span>
            <span id="cntComandas" style="font-size:12px;color:var(--text-muted);"></span>
        </div>
        <div id="listaCmdas" style="max-height:380px;overflow-y:auto;">
            <div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div>
        </div>
    </div>
    <div class="sec-card">
        <div class="sec-head"><span><i class="fas fa-credit-card"></i> Por método de pago</span></div>
        <div id="listaPagos">
            <div class="rep-empty"><i class="fas fa-circle-notch spin"></i></div>
        </div>
    </div>
</div>

</main>
</div>

<!-- Modal Abrir Caja -->
<div class="modal-overlay" id="modalAbrir">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-lock-open" style="color:#10b981;margin-right:8px;"></i>Abrir Caja</h3>
            <button class="modal-close" onclick="cerrar('modalAbrir')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-g">
                <label>Fondo inicial (efectivo en caja) *</label>
                <input type="number" id="aFondo" placeholder="0.00" min="0" step="100">
            </div>
            <div class="form-g">
                <label>Observaciones</label>
                <textarea id="aObs" rows="2" style="resize:vertical;" placeholder="Opcional..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrar('modalAbrir')">Cancelar</button>
            <button class="btn-abrir" onclick="abrirCaja()"><i class="fas fa-lock-open"></i> Abrir Caja</button>
        </div>
    </div>
</div>

<!-- Modal Cerrar Caja -->
<div class="modal-overlay" id="modalCerrar">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-lock" style="color:#ef4444;margin-right:8px;"></i>Cerrar Caja</h3>
            <button class="modal-close" onclick="cerrar('modalCerrar')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="cierre-resumen" id="cierreResumen"></div>
            <div class="form-g">
                <label>Dinero real contado en caja *</label>
                <input type="number" id="cMonto" placeholder="0.00" min="0" step="100" oninput="calcDif()">
            </div>
            <div id="difBox" style="padding:10px 14px;border-radius:8px;font-size:13px;font-weight:700;margin-bottom:14px;display:none;"></div>
            <div class="form-g">
                <label>Observaciones</label>
                <textarea id="cObs" rows="2" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrar('modalCerrar')">Cancelar</button>
            <button class="btn-cerrar" onclick="cerrarCaja()"><i class="fas fa-lock"></i> Cerrar Caja</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-check-circle" style="color:var(--primary)"></i><span id="toastMsg"></span></div>

<script>
const API_CAJA    = '/DASHBASE/api/caja/index.php';
const API_CMDAS   = '/DASHBASE/api/restaurant/comandas.php';
const PAGO_COLORS = { efectivo:'#10b981', tarjeta:'#6366f1', transferencia:'#3b82f6', debito:'#8b5cf6', mercadopago:'#0ea5e9', qr:'#14b8a6' };

let cajaActual = null, montoEsperado = 0;

async function init() {
    await Promise.all([cargarCaja(), cargarComandasHoy()]);
}

// ── Caja ─────────────────────────────────────────────────
async function cargarCaja() {
    try {
        const r = await fetch(`${API_CAJA}?activa=1`);
        const j = await r.json();
        if (j.success && j.data) {
            cajaActual = j.data;
            renderCajaAbierta(j.data);
        } else {
            renderCajaCerrada();
        }
    } catch(e) { renderCajaCerrada(); }
}

function renderCajaAbierta(c) {
    cajaActual = c;
    montoEsperado = parseFloat(c.monto_inicial) + parseFloat(c.monto_ventas) - parseFloat(c.monto_gastos);
    document.getElementById('heroIcon').className = 'hero-icon abierta';
    document.getElementById('heroIcon').innerHTML = '<i class="fas fa-cash-register"></i>';
    document.getElementById('heroTitulo').textContent = 'Caja Abierta';
    document.getElementById('heroSub').textContent = `Abierta el ${fmtTs(c.fecha_apertura)}`;
    const pill = document.getElementById('estadoPill');
    pill.className = 'estado-pill abierta'; pill.innerHTML = '● Abierta';
    document.getElementById('heroAcciones').innerHTML = `<button class="btn-cerrar" onclick="abrirModalCerrar()"><i class="fas fa-lock"></i> Cerrar Caja</button>`;

    const pagos = c.detalle_pagos || [];
    const totalPagos = pagos.reduce((s,p) => s + parseFloat(p.total), 0);

    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi" style="--kc:#10b981">
            <div class="kpi-lbl">Fondo inicial</div>
            <div class="kpi-val">${$m(c.monto_inicial)}</div>
            <div class="kpi-sub">al abrir</div>
        </div>
        <div class="kpi" style="--kc:#6366f1">
            <div class="kpi-val">${$m(c.monto_ventas)}</div>
            <div class="kpi-lbl">Ventas del día</div>
            <div class="kpi-sub">${pagos.length} métodos</div>
        </div>
        <div class="kpi" style="--kc:#f59e0b">
            <div class="kpi-lbl">Gastos</div>
            <div class="kpi-val">${$m(c.monto_gastos)}</div>
            <div class="kpi-sub">registrados</div>
        </div>
        <div class="kpi" style="--kc:#0ea5e9">
            <div class="kpi-lbl">Total esperado</div>
            <div class="kpi-val">${$m(montoEsperado)}</div>
            <div class="kpi-sub">fondo + ventas − gastos</div>
        </div>
    `;
    renderPagos(pagos, totalPagos);
}

function renderCajaCerrada() {
    cajaActual = null;
    document.getElementById('heroIcon').className = 'hero-icon cerrada';
    document.getElementById('heroIcon').innerHTML = '<i class="fas fa-lock"></i>';
    document.getElementById('heroTitulo').textContent = 'Caja Cerrada';
    document.getElementById('heroSub').textContent = 'No hay caja abierta hoy';
    const pill = document.getElementById('estadoPill');
    pill.className = 'estado-pill cerrada'; pill.innerHTML = '● Cerrada';
    document.getElementById('heroAcciones').innerHTML = `<button class="btn-abrir" onclick="document.getElementById('modalAbrir').classList.add('open');document.getElementById('aFondo').value=''"><i class="fas fa-lock-open"></i> Abrir Caja</button>`;
    document.getElementById('kpiGrid').innerHTML = `
        <div class="kpi" style="--kc:#64748b;grid-column:1/-1">
            <div class="rep-empty" style="padding:20px"><i class="fas fa-lock"></i><p>Abrí la caja para ver los datos del día</p></div>
        </div>`;
    document.getElementById('listaPagos').innerHTML = `<div class="rep-empty"><i class="fas fa-credit-card"></i><p>Abrí la caja primero</p></div>`;
}

// ── Comandas del día ──────────────────────────────────────
async function cargarComandasHoy() {
    try {
        const hoy = new Date().toISOString().slice(0,10);
        const r   = await fetch(`${API_CMDAS}?fecha=${hoy}&estado=cerrada`);
        const j   = await r.json();
        const lista = Array.isArray(j.data) ? j.data : (j.data?.comandas || []);
        document.getElementById('cntComandas').textContent = lista.length + ' comandas';
        if (!lista.length) {
            document.getElementById('listaCmdas').innerHTML = `<div class="rep-empty"><i class="fas fa-receipt"></i><p>Sin comandas cerradas hoy</p></div>`;
            return;
        }
        const BASE = window.APP_BASE ?? '/DASHBASE';
        let html = '<table class="cmd-table"><thead><tr><th>Mesa</th><th>Hora</th><th>Personas</th><th style="text-align:right">Total</th><th></th></tr></thead><tbody>';
        lista.forEach(c => {
            html += `<tr>
                <td><span class="mesa-badge">${esc(c.mesa_numero ?? c.numero ?? '—')}</span></td>
                <td style="color:var(--text-muted);font-size:12px;">${fmtHora(c.cerrada_at)}</td>
                <td style="font-size:13px;">${c.personas ?? '—'}</td>
                <td style="text-align:right;font-weight:700;">${$m(c.total)}</td>
                <td><button onclick="imprimirRecibo(${c.id})" style="background:none;border:1px solid var(--border);border-radius:6px;padding:4px 10px;cursor:pointer;font-size:12px;color:var(--text-muted);" title="Imprimir recibo"><i class="fas fa-print"></i></button></td>
            </tr>`;
        });
        const total = lista.reduce((s,c) => s + parseFloat(c.total||0), 0);
        html += `</tbody><tfoot><tr style="background:rgba(0,0,0,.03)"><td colspan="4" style="padding:10px 16px;font-weight:700;">Total</td><td style="text-align:right;font-weight:800;padding:10px 16px;">${$m(total)}</td><td></td></tr></tfoot></table>`;
        document.getElementById('listaCmdas').innerHTML = html;
    } catch(e) {
        document.getElementById('listaCmdas').innerHTML = `<div class="rep-empty"><i class="fas fa-exclamation-circle"></i><p>Error al cargar comandas</p></div>`;
    }
}

// ── Render métodos de pago ────────────────────────────────
function renderPagos(pagos, totalGeneral) {
    if (!pagos.length) {
        document.getElementById('listaPagos').innerHTML = `<div class="rep-empty"><i class="fas fa-credit-card"></i><p>Sin ventas registradas</p></div>`;
        return;
    }
    let html = '';
    pagos.forEach(p => {
        const pct   = totalGeneral > 0 ? Math.round(parseFloat(p.total)/totalGeneral*100) : 0;
        const color = pagoColor(p.metodo_pago);
        html += `<div class="pago-row">
            <div class="pago-dot" style="background:${color}"></div>
            <div class="pago-lbl">${esc(p.metodo_pago||'otro')}</div>
            <div class="pago-bar-wrap"><div class="pago-bar" style="width:${pct}%;background:${color}"></div></div>
            <div class="pago-total">${$m(p.total)}</div>
            <div class="pago-pct">${pct}%</div>
        </div>`;
    });
    document.getElementById('listaPagos').innerHTML = html;
}

// ── Abrir caja ────────────────────────────────────────────
async function abrirCaja() {
    const fondo = parseFloat(document.getElementById('aFondo').value) || 0;
    try {
        const r = await fetch(API_CAJA, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ monto_inicial: fondo })
        });
        const j = await r.json();
        if (j.success) {
            cerrar('modalAbrir');
            toast('Caja abierta');
            await cargarCaja();
            await cargarComandasHoy();
        } else {
            toast(j.message || 'Error', true);
        }
    } catch(e) { toast('Error de conexión', true); }
}

// ── Cerrar caja ───────────────────────────────────────────
function abrirModalCerrar() {
    if (!cajaActual) return;
    document.getElementById('cierreResumen').innerHTML = `
        <div class="cierre-row"><span>Fondo inicial</span><span>${$m(cajaActual.monto_inicial)}</span></div>
        <div class="cierre-row"><span>Ventas del día</span><span>+ ${$m(cajaActual.monto_ventas)}</span></div>
        <div class="cierre-row"><span>Gastos</span><span>− ${$m(cajaActual.monto_gastos)}</span></div>
        <div class="cierre-row total"><span>Total esperado</span><span>${$m(montoEsperado)}</span></div>`;
    document.getElementById('cMonto').value = '';
    document.getElementById('difBox').style.display = 'none';
    document.getElementById('modalCerrar').classList.add('open');
}

function calcDif() {
    const real = parseFloat(document.getElementById('cMonto').value) || 0;
    const dif  = real - montoEsperado;
    const box  = document.getElementById('difBox');
    if (real > 0) {
        box.style.display = 'block';
        if (Math.abs(dif) < 0.01) {
            box.style.background = 'rgba(16,185,129,.1)';
            box.innerHTML = `<span class="dif-positiva">✓ Sin diferencia</span>`;
        } else if (dif > 0) {
            box.style.background = 'rgba(16,185,129,.1)';
            box.innerHTML = `<span class="dif-positiva">↑ Sobrante: ${$m(dif)}</span>`;
        } else {
            box.style.background = 'rgba(239,68,68,.1)';
            box.innerHTML = `<span class="dif-negativa">↓ Faltante: ${$m(Math.abs(dif))}</span>`;
        }
    } else {
        box.style.display = 'none';
    }
}

async function cerrarCaja() {
    if (!cajaActual) return;
    const montoReal = parseFloat(document.getElementById('cMonto').value);
    if (isNaN(montoReal) || montoReal < 0) { toast('Ingresá el monto real contado', true); return; }
    try {
        const r = await fetch(API_CAJA, {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ caja_id: cajaActual.id, monto_real: montoReal, observaciones: document.getElementById('cObs').value })
        });
        const j = await r.json();
        if (j.success) {
            cerrar('modalCerrar');
            toast('Caja cerrada correctamente');
            await cargarCaja();
        } else {
            toast(j.message || 'Error al cerrar', true);
        }
    } catch(e) { toast('Error de conexión', true); }
}

// ── Imprimir recibo ───────────────────────────────────────
function imprimirRecibo(comandaId) {
    const BASE = window.APP_BASE ?? '/DASHBASE';
    window.open(`${BASE}/views/restaurant/ticket.php?tipo=recibo&id=${comandaId}`, '_blank', 'width=420,height=680');
}

// ── Helpers ───────────────────────────────────────────────
function cerrar(id) { document.getElementById(id).classList.remove('open'); }
function pagoColor(m) {
    m = (m||'').toLowerCase();
    for (const [k,v] of Object.entries(PAGO_COLORS)) if (m.includes(k)) return v;
    return '#94a3b8';
}
function $m(v)    { return '$' + parseFloat(v||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function esc(s)   { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtTs(ts){ if (!ts) return '—'; const d=new Date(ts.replace(' ','T')); return d.toLocaleDateString('es-AR',{day:'2-digit',month:'2-digit'}) + ' ' + d.toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit'}); }
function fmtHora(ts){ if (!ts) return '—'; const d=new Date(ts.replace(' ','T')); return d.toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit'}); }
function toast(msg, err=false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast show' + (err?' err':'');
    setTimeout(() => t.classList.remove('show'), 3200);
}

document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target===m) m.classList.remove('open'); }));
document.addEventListener('keydown', e => { if (e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>m.classList.remove('open')); });

init();
</script>
</body>
</html>
