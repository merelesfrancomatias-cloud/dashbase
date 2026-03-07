<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/');
$clienteFiltro = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios — Electrodomésticos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --elec:#0891b2; --elec-light:rgba(8,145,178,.1); }
        .elec-toolbar { position:sticky;top:0;z-index:10;background:var(--surface);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
        .elec-toolbar h1 { margin:0;font-size:20px;font-weight:700;color:var(--text-primary); }
        .elec-toolbar p  { margin:0;font-size:12px;color:var(--text-secondary); }
        .stats-bar { display:flex;gap:8px;padding:14px 24px 0;flex-wrap:wrap; }
        .sp { display:flex;align-items:center;gap:7px;padding:7px 13px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid transparent;cursor:pointer;transition:all .15s; }
        .sp .dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
        .sp-all       { background:var(--background);border-color:var(--border);color:var(--text-primary); }
        .sp-ingresado { background:rgba(8,145,178,.1);border-color:#0891b2;color:#0369a1; }
        .sp-diag      { background:rgba(99,102,241,.1);border-color:#6366f1;color:#4f46e5; }
        .sp-espera    { background:rgba(245,158,11,.1);border-color:#f59e0b;color:#d97706; }
        .sp-reparando { background:rgba(239,68,68,.1);border-color:#ef4444;color:#dc2626; }
        .sp-listo     { background:rgba(15,209,134,.1);border-color:#0FD186;color:#059669; }
        .sp-entregado { background:rgba(100,116,139,.1);border-color:#94a3b8;color:#64748b; }
        .sp.active    { transform:scale(1.04);box-shadow:0 2px 10px rgba(0,0,0,.1); }
        .filter-bar { padding:12px 24px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center; }
        .filter-input { flex:1;min-width:200px;padding:9px 14px 9px 36px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:10px center; }
        .filter-input:focus { outline:none;border-color:var(--elec); }
        .cards-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:14px;padding:14px 24px 24px; }
        .srv-card { background:var(--surface);border-radius:14px;border:2px solid var(--border);overflow:hidden;transition:all .2s;cursor:pointer; }
        .srv-card:hover { transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.08); }
        .srv-card.ingresado      { border-color:#0891b2; }
        .srv-card.diagnosticando { border-color:#6366f1; }
        .srv-card.esperando_repuesto { border-color:#f59e0b; }
        .srv-card.en_reparacion  { border-color:#ef4444; }
        .srv-card.listo          { border-color:#0FD186; }
        .srv-card.entregado      { border-color:#94a3b8; opacity:.7; }
        .srv-card.cancelado      { border-color:#94a3b8; opacity:.5; }
        .card-head { padding:12px 14px 10px;display:flex;align-items:center;justify-content:space-between;gap:8px; }
        .card-num  { font-size:11px;font-weight:700;color:var(--text-secondary);letter-spacing:.5px; }
        .card-badge { padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px; }
        .badge-ingresado      { background:rgba(8,145,178,.12);color:#0369a1; }
        .badge-diagnosticando { background:rgba(99,102,241,.12);color:#4f46e5; }
        .badge-esperando_repuesto { background:rgba(245,158,11,.12);color:#d97706; }
        .badge-en_reparacion  { background:rgba(239,68,68,.12);color:#dc2626; }
        .badge-listo          { background:rgba(15,209,134,.12);color:#059669; }
        .badge-entregado      { background:rgba(100,116,139,.12);color:#64748b; }
        .badge-cancelado      { background:rgba(100,116,139,.1);color:#94a3b8; }
        .badge-sin_reparacion { background:rgba(239,68,68,.08);color:#9f1239; }
        .card-body { padding:0 14px 12px; }
        .card-art  { font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:3px;display:flex;align-items:center;gap:6px; }
        .card-marca { font-size:12px;color:var(--text-secondary);margin-bottom:8px; }
        .card-cli  { display:flex;align-items:center;gap:7px;padding:7px 10px;background:var(--background);border-radius:8px;margin-bottom:8px; }
        .card-cli span { font-size:13px;font-weight:600;color:var(--text-primary); }
        .card-falla { font-size:12px;color:var(--text-secondary);line-height:1.4;margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
        .card-footer { display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--text-secondary); }
        .garantia-pill { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;background:rgba(15,209,134,.1);color:#059669;border-radius:10px;font-size:10px;font-weight:700; }
        /* Modal */
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface);border-radius:20px;width:100%;max-width:640px;max-height:94vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:18px 24px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--surface);z-index:2; }
        .modal-header h3 { margin:0;font-size:17px;font-weight:700; }
        .modal-close { background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;padding:4px 8px;border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:18px 24px; }
        .modal-footer { padding:14px 24px 18px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border); }
        .section-title { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-secondary);margin:16px 0 10px; }
        .fg { margin-bottom:12px; }
        .fg label { display:block;font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:5px; }
        .fi { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);box-sizing:border-box; }
        .fi:focus { outline:none;border-color:var(--elec); }
        .fg-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
        .fg-grid3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px; }
        .estado-btns { display:flex;flex-wrap:wrap;gap:8px; }
        .est-btn { padding:7px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid var(--border);background:var(--background);cursor:pointer;transition:all .15s;color:var(--text-secondary); }
        .est-btn:hover, .est-btn.sel { transform:scale(1.04); }
        .est-btn.ingresado.sel      { background:rgba(8,145,178,.15);border-color:#0891b2;color:#0369a1; }
        .est-btn.diagnosticando.sel { background:rgba(99,102,241,.15);border-color:#6366f1;color:#4f46e5; }
        .est-btn.esperando_repuesto.sel { background:rgba(245,158,11,.15);border-color:#f59e0b;color:#d97706; }
        .est-btn.en_reparacion.sel  { background:rgba(239,68,68,.15);border-color:#ef4444;color:#dc2626; }
        .est-btn.listo.sel          { background:rgba(15,209,134,.15);border-color:#0FD186;color:#059669; }
        .est-btn.entregado.sel      { background:rgba(100,116,139,.15);border-color:#94a3b8;color:#64748b; }
        .est-btn.sin_reparacion.sel { background:rgba(239,68,68,.08);border-color:#f43f5e;color:#9f1239; }
        .garantia-check { display:flex;align-items:center;gap:10px;padding:10px;background:rgba(15,209,134,.05);border:1.5px solid rgba(15,209,134,.2);border-radius:10px;cursor:pointer; }
        .garantia-check input[type=checkbox] { width:18px;height:18px;accent-color:#0FD186;cursor:pointer; }
        .garantia-check label { font-size:13px;font-weight:600;color:#059669;cursor:pointer; }
        .toast { position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#1e293b;color:white;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;opacity:0;transition:opacity .3s;white-space:nowrap;pointer-events:none; }
        .toast.show { opacity:1; }
        @media(max-width:600px) { .filter-bar,.stats-bar,.elec-toolbar { padding:12px; } .cards-grid { padding:12px; } .fg-grid,.fg-grid3 { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<script>window.APP_BASE='<?= $base ?>';window.CLI_FILTRO=<?= $clienteFiltro ?>;</script>
<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
        <?php include '../includes/header.php'; ?>
        <div class="elec-toolbar">
            <div>
                <h1><i class="fas fa-screwdriver-wrench" style="color:var(--elec);margin-right:8px;"></i>Órdenes de Servicio</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="clientes.php" class="btn btn-secondary" style="text-decoration:none;"><i class="fas fa-users"></i> Clientes</a>
                <button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus"></i> Nueva Orden</button>
            </div>
        </div>

        <!-- Filtros por estado -->
        <div class="stats-bar" id="statBar">
            <div class="sp sp-all active" onclick="filtrarEstado('')"><div class="dot" style="background:#94a3b8;"></div><span id="st-total">0</span> Todos</div>
            <div class="sp sp-ingresado"  onclick="filtrarEstado('ingresado')"><div class="dot" style="background:#0891b2;"></div><span id="st-ingresado">0</span> Ingresados</div>
            <div class="sp sp-diag"       onclick="filtrarEstado('diagnosticando')"><div class="dot" style="background:#6366f1;"></div><span id="st-diag">0</span> Diagnóstico</div>
            <div class="sp sp-espera"     onclick="filtrarEstado('esperando_repuesto')"><div class="dot" style="background:#f59e0b;"></div><span id="st-espera">0</span> Esp. repuesto</div>
            <div class="sp sp-reparando"  onclick="filtrarEstado('en_reparacion')"><div class="dot" style="background:#ef4444;"></div><span id="st-rep">0</span> En reparación</div>
            <div class="sp sp-listo"      onclick="filtrarEstado('listo')"><div class="dot" style="background:#0FD186;"></div><span id="st-listo">0</span> Listos</div>
            <div class="sp sp-entregado"  onclick="filtrarEstado('entregado')"><div class="dot" style="background:#94a3b8;"></div><span id="st-entregado">0</span> Entregados</div>
        </div>

        <div class="filter-bar">
            <input class="filter-input" type="text" id="buscar" placeholder="Buscar por número, artículo, marca o cliente…" oninput="filtrar()">
        </div>

        <div id="mainContent">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin" style="font-size:28px;"></i></div>
        </div>
    </div>
</div>

<!-- Modal nueva/editar orden -->
<div class="modal-overlay" id="modalSrv">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="mTitulo"><i class="fas fa-plus-circle" style="color:var(--elec);margin-right:8px;"></i>Nueva Orden</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="srvId">

            <div class="section-title"><i class="fas fa-user"></i> Datos del cliente</div>
            <div class="fg-grid">
                <div class="fg"><label>Nombre del cliente <span style="color:#ef4444;">*</span></label><input class="fi" id="cliNombre" placeholder="Juan García"></div>
                <div class="fg"><label>Teléfono</label><input class="fi" id="cliTel" type="tel" placeholder="+54 9 11…"></div>
            </div>

            <div class="section-title"><i class="fas fa-tv"></i> Equipo</div>
            <div class="fg"><label>Artículo / Equipo <span style="color:#ef4444;">*</span></label><input class="fi" id="articulo" placeholder="Ej: Lavarropas, Smart TV 55', Heladera…"></div>
            <div class="fg-grid3">
                <div class="fg"><label>Marca</label>
                    <input class="fi" id="marca" list="marcas-list" placeholder="Samsung, LG…">
                    <datalist id="marcas-list">
                        <option value="Samsung"><option value="LG"><option value="Whirlpool">
                        <option value="Philips"><option value="Electrolux"><option value="Drean">
                        <option value="Mabe"><option value="Ariston"><option value="Bosch">
                        <option value="Candy"><option value="Daewoo"><option value="BGH">
                        <option value="Hisense"><option value="Noblex"><option value="TCL">
                    </datalist>
                </div>
                <div class="fg"><label>Modelo</label><input class="fi" id="modelo" placeholder="WA12J2000AW…"></div>
                <div class="fg"><label>N° de serie</label><input class="fi" id="serie" placeholder="SN12345…"></div>
            </div>

            <!-- Garantía -->
            <div class="garantia-check" onclick="toggleGarantia()">
                <input type="checkbox" id="enGarantia" onclick="event.stopPropagation();" onchange="toggleGarantia()">
                <label for="enGarantia"><i class="fas fa-shield-alt"></i> Ingresa en garantía</label>
            </div>
            <div id="garantiaFecha" style="display:none;margin-top:10px;">
                <div class="fg"><label>Vence garantía</label><input class="fi" type="date" id="venceGarantia"></div>
            </div>

            <div class="section-title"><i class="fas fa-exclamation-triangle"></i> Falla y presupuesto</div>
            <div class="fg"><label>Falla declarada por el cliente</label><textarea class="fi" id="fallaDeclarada" rows="2" style="resize:vertical;" placeholder="Ej: No enciende, hace ruido extraño…"></textarea></div>
            <div class="fg-grid">
                <div class="fg"><label>Presupuesto ($)</label><input class="fi" type="number" id="presupuesto" placeholder="0.00"></div>
                <div class="fg"><label>Técnico asignado</label><input class="fi" id="tecnico" placeholder="Nombre del técnico"></div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>Fecha ingreso</label><input class="fi" type="date" id="fechaIngreso"></div>
                <div class="fg"><label>Prometido para</label><input class="fi" type="date" id="fechaPrometida"></div>
            </div>

            <div class="section-title"><i class="fas fa-traffic-light"></i> Estado</div>
            <div class="estado-btns" id="estadoBtns">
                <button class="est-btn ingresado"         onclick="selEstado('ingresado')">📥 Ingresado</button>
                <button class="est-btn diagnosticando"    onclick="selEstado('diagnosticando')">🔍 Diagnóstico</button>
                <button class="est-btn esperando_repuesto" onclick="selEstado('esperando_repuesto')">⏳ Esp. repuesto</button>
                <button class="est-btn en_reparacion"     onclick="selEstado('en_reparacion')">🔧 En reparación</button>
                <button class="est-btn listo"             onclick="selEstado('listo')">✅ Listo</button>
                <button class="est-btn entregado"         onclick="selEstado('entregado')">📦 Entregado</button>
                <button class="est-btn sin_reparacion"    onclick="selEstado('sin_reparacion')">❌ Sin reparación</button>
            </div>

            <div class="fg" style="margin-top:14px;"><label>Observaciones internas</label><textarea class="fi" id="observaciones" rows="2" style="resize:vertical;"></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardar()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
<script>
const BASE = '<?= $base ?>';
const API  = BASE + '/api/electrodomesticos/servicios.php';
let todos = [], filtrados = [];
let estadoActivo = '', estadoSelModal = 'ingresado';

const ESTADOS_LABEL = {
    ingresado:'Ingresado', diagnosticando:'Diagnóstico', esperando_repuesto:'Esp. repuesto',
    en_reparacion:'En reparación', listo:'Listo', entregado:'Entregado',
    cancelado:'Cancelado', sin_reparacion:'Sin reparación'
};

async function init() {
    const url = CLI_FILTRO ? `${API}?cliente=${CLI_FILTRO}` : API;
    const r = await fetch(url, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { document.getElementById('mainContent').innerHTML=`<div style="text-align:center;padding:60px;color:var(--text-secondary);"><p>${j.message}</p></div>`; return; }
    todos = j.data.servicios || [];
    const st = j.data.stats || {};
    document.getElementById('subtitulo').textContent = `${st.total||0} órdenes en total`;
    document.getElementById('st-total').textContent     = st.total||0;
    document.getElementById('st-ingresado').textContent = st.ingresado||0;
    document.getElementById('st-diag').textContent      = st.diagnosticando||0;
    document.getElementById('st-espera').textContent    = st.esperando_repuesto||0;
    document.getElementById('st-rep').textContent       = st.en_reparacion||0;
    document.getElementById('st-listo').textContent     = st.listo||0;
    document.getElementById('st-entregado').textContent = st.entregado||0;
    filtrar();
    // Fecha por defecto
    document.getElementById('fechaIngreso').value = new Date().toISOString().slice(0,10);
}

function filtrarEstado(est) {
    estadoActivo = est;
    document.querySelectorAll('.stats-bar .sp').forEach(el => el.classList.remove('active'));
    const idx = ['','ingresado','diagnosticando','esperando_repuesto','en_reparacion','listo','entregado'];
    const btn = document.querySelectorAll('.stats-bar .sp')[idx.indexOf(est)];
    if (btn) btn.classList.add('active');
    filtrar();
}

function filtrar() {
    const q = (document.getElementById('buscar').value||'').toLowerCase().trim();
    filtrados = todos.filter(s => {
        const matchEst = !estadoActivo || s.estado === estadoActivo;
        const matchQ   = !q || (s.numero+s.articulo+s.marca+s.cliente_nombre+s.modelo+s.numero_serie).toLowerCase().includes(q);
        return matchEst && matchQ;
    });
    render();
}

function render() {
    const cont = document.getElementById('mainContent');
    if (!filtrados.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);">
            <i class="fas fa-screwdriver-wrench" style="font-size:48px;opacity:.1;display:block;margin-bottom:16px;"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:16px;">No hay órdenes</p>
            <button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus"></i> Nueva Orden</button>
        </div>`;
        return;
    }
    cont.innerHTML = `<div class="cards-grid">${filtrados.map(s => `
        <div class="srv-card ${s.estado}" onclick="editar(${s.id})">
            <div class="card-head">
                <span class="card-num">${esc(s.numero)}</span>
                <div style="display:flex;align-items:center;gap:6px;">
                    ${s.en_garantia?'<span class="garantia-pill"><i class="fas fa-shield-alt"></i> Garantía</span>':''}
                    <span class="card-badge badge-${s.estado}">${ESTADOS_LABEL[s.estado]||s.estado}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="card-art">
                    <i class="fas fa-tv" style="color:var(--elec);font-size:14px;"></i>
                    ${esc(s.articulo)}
                </div>
                ${(s.marca||s.modelo)?`<div class="card-marca">${[s.marca,s.modelo].filter(Boolean).join(' · ')}</div>`:''}
                ${s.cliente_nombre?`<div class="card-cli">
                    <i class="fas fa-user" style="color:var(--elec);font-size:11px;"></i>
                    <span>${esc(s.cliente_nombre)}</span>
                    ${s.cliente_tel?`<a href="tel:${esc(s.cliente_tel)}" onclick="event.stopPropagation()" style="margin-left:auto;color:var(--elec);font-size:12px;font-weight:600;">${esc(s.cliente_tel)}</a>`:''}
                </div>`:''}
                ${s.falla_declarada?`<div class="card-falla">"${esc(s.falla_declarada)}"</div>`:''}
                <div class="card-footer">
                    <span><i class="fas fa-calendar-alt"></i> ${s.fecha_ingreso||'—'}</span>
                    ${s.fecha_prometida?`<span><i class="fas fa-clock"></i> Para: ${s.fecha_prometida}</span>`:''}
                    ${s.presupuesto?`<span style="font-weight:700;color:var(--elec);">$${parseFloat(s.presupuesto).toLocaleString('es-AR')}</span>`:''}
                </div>
            </div>
        </div>`).join('')}</div>`;
}

function selEstado(est) {
    estadoSelModal = est;
    document.querySelectorAll('.est-btn').forEach(b => b.classList.remove('sel'));
    document.querySelector(`.est-btn.${est}`)?.classList.add('sel');
}

function toggleGarantia() {
    const cb = document.getElementById('enGarantia');
    if(event.target !== cb) cb.checked = !cb.checked;
    document.getElementById('garantiaFecha').style.display = cb.checked ? 'block' : 'none';
}

function abrirModal(s=null) {
    document.getElementById('srvId').value         = s?s.id:'';
    document.getElementById('cliNombre').value     = s?(s.cliente_nombre||''):'';
    document.getElementById('cliTel').value        = s?(s.cliente_tel||''):'';
    document.getElementById('articulo').value      = s?(s.articulo||''):'';
    document.getElementById('marca').value         = s?(s.marca||''):'';
    document.getElementById('modelo').value        = s?(s.modelo||''):'';
    document.getElementById('serie').value         = s?(s.numero_serie||''):'';
    document.getElementById('fallaDeclarada').value= s?(s.falla_declarada||''):'';
    document.getElementById('presupuesto').value   = s?(s.presupuesto||''):'';
    document.getElementById('tecnico').value       = s?(s.tecnico||''):'';
    document.getElementById('fechaIngreso').value  = s?(s.fecha_ingreso||new Date().toISOString().slice(0,10)):new Date().toISOString().slice(0,10);
    document.getElementById('fechaPrometida').value= s?(s.fecha_prometida||''):'';
    document.getElementById('observaciones').value = s?(s.observaciones||''):'';
    const garantia = s?!!parseInt(s.en_garantia):false;
    document.getElementById('enGarantia').checked  = garantia;
    document.getElementById('garantiaFecha').style.display = garantia?'block':'none';
    document.getElementById('venceGarantia').value = s?(s.vence_garantia||''):'';
    selEstado(s?(s.estado||'ingresado'):'ingresado');
    document.getElementById('mTitulo').innerHTML = s
        ? `<i class="fas fa-edit" style="color:var(--elec);margin-right:8px;"></i>Editar Orden`
        : `<i class="fas fa-plus-circle" style="color:var(--elec);margin-right:8px;"></i>Nueva Orden`;
    document.getElementById('modalSrv').classList.add('open');
    setTimeout(()=>document.getElementById('cliNombre').focus(),100);
}

function cerrarModal() { document.getElementById('modalSrv').classList.remove('open'); }
function editar(id)    { abrirModal(todos.find(s=>s.id==id)); }

async function guardar() {
    const id       = document.getElementById('srvId').value;
    const articulo = document.getElementById('articulo').value.trim();
    if (!articulo) { toast('El artículo es obligatorio','error'); return; }
    const body = {
        cliente_nombre:  document.getElementById('cliNombre').value.trim()||null,
        cliente_tel:     document.getElementById('cliTel').value.trim()||null,
        articulo,
        marca:           document.getElementById('marca').value.trim()||null,
        modelo:          document.getElementById('modelo').value.trim()||null,
        numero_serie:    document.getElementById('serie').value.trim()||null,
        falla_declarada: document.getElementById('fallaDeclarada').value.trim()||null,
        presupuesto:     document.getElementById('presupuesto').value||null,
        en_garantia:     document.getElementById('enGarantia').checked?1:0,
        vence_garantia:  document.getElementById('venceGarantia').value||null,
        estado:          estadoSelModal,
        fecha_ingreso:   document.getElementById('fechaIngreso').value,
        fecha_prometida: document.getElementById('fechaPrometida').value||null,
        tecnico:         document.getElementById('tecnico').value.trim()||null,
        observaciones:   document.getElementById('observaciones').value.trim()||null,
    };
    const r = await fetch(id?`${API}?id=${id}`:API, {method:id?'PUT':'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const j = await r.json();
    if (j.success) {
        cerrarModal();
        toast(id?`Orden actualizada ✓`:`Orden ${j.data?.numero||''} creada ✓`);
        init();
    } else toast(j.message||'Error','error');
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function toast(msg,tipo='ok') { const t=document.getElementById('toast'); t.textContent=msg; t.style.background=tipo==='error'?'#ef4444':'#1e293b'; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500); }

document.getElementById('modalSrv').addEventListener('click',e=>{ if(e.target.id==='modalSrv') cerrarModal(); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') cerrarModal(); });
init();
</script>
</body>
</html>
