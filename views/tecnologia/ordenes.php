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
    <title>Órdenes de Servicio — Tecnología</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --tec:#6366f1; --tec-light:rgba(99,102,241,.1); }

        .tec-toolbar { position:sticky;top:0;z-index:10;background:var(--surface);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
        .tec-toolbar h1 { margin:0;font-size:20px;font-weight:700;color:var(--text-primary); }
        .tec-toolbar p  { margin:0;font-size:12px;color:var(--text-secondary); }

        /* Stats pills */
        .stats-bar { display:flex;gap:10px;padding:16px 24px 0;flex-wrap:wrap; }
        .stat-pill { display:flex;align-items:center;gap:8px;padding:7px 14px;border-radius:20px;font-size:13px;font-weight:600;border:1.5px solid transparent;cursor:pointer;transition:all .15s; }
        .stat-pill .dot { width:9px;height:9px;border-radius:50%; }
        .sp-all      { background:var(--background);border-color:var(--border);color:var(--text-primary); }
        .sp-ingresado{ background:rgba(99,102,241,.1);border-color:#6366f1;color:#4f46e5; }
        .sp-reparando{ background:rgba(245,158,11,.1);border-color:#f59e0b;color:#d97706; }
        .sp-espera   { background:rgba(239,68,68,.1);border-color:#ef4444;color:#dc2626; }
        .sp-listo    { background:rgba(15,209,134,.1);border-color:#0FD186;color:#059669; }
        .sp-urgente  { background:rgba(239,68,68,.08);border-color:#ef4444;color:#dc2626; }
        .stat-pill.active { transform:scale(1.05);box-shadow:0 2px 10px rgba(0,0,0,.1); }

        /* Filtro */
        .filter-bar { padding:12px 24px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center; }
        .filter-input { flex:1;min-width:200px;padding:9px 14px 9px 36px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:10px center; }
        .filter-input:focus { outline:none;border-color:var(--tec); }

        /* Grid de órdenes */
        .ordenes-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;padding:16px 24px; }

        /* Card orden */
        .orden-card { background:var(--surface);border-radius:16px;border:2px solid var(--border);overflow:hidden;transition:all .2s; }
        .orden-card:hover { transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.08); }
        .orden-card.ingresado       { border-color:#6366f1; }
        .orden-card.diagnosticando  { border-color:#a78bfa; }
        .orden-card.esperando_repuesto { border-color:#ef4444; }
        .orden-card.en_reparacion   { border-color:#f59e0b; }
        .orden-card.listo           { border-color:#0FD186;box-shadow:0 0 0 2px rgba(15,209,134,.15); }
        .orden-card.entregado       { border-color:#6366f1;opacity:.7; }
        .orden-card.sin_reparacion  { border-color:#94a3b8;opacity:.6; }
        .orden-card.cancelado       { border-color:#ef4444;opacity:.5; }
        .orden-card.prio-urgente    { box-shadow:inset 3px 0 0 #ef4444; }
        .orden-card.prio-vip        { box-shadow:inset 3px 0 0 #f59e0b; }

        .oc-header { padding:13px 16px 10px;display:flex;align-items:flex-start;justify-content:space-between;gap:8px; }
        .oc-num    { font-size:11px;font-weight:800;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px; }
        .oc-cliente{ font-weight:700;font-size:14px;color:var(--text-primary); }
        .estado-badge { padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;flex-shrink:0; }
        .eb-ingresado      { background:rgba(99,102,241,.15);color:#4f46e5; }
        .eb-diagnosticando { background:rgba(167,139,250,.15);color:#7c3aed; }
        .eb-esperando_repuesto { background:rgba(239,68,68,.15);color:#dc2626; }
        .eb-en_reparacion  { background:rgba(245,158,11,.15);color:#d97706; }
        .eb-listo          { background:rgba(15,209,134,.15);color:#059669; }
        .eb-entregado      { background:rgba(99,102,241,.12);color:#6366f1; }
        .eb-sin_reparacion { background:rgba(148,163,184,.15);color:#64748b; }
        .eb-cancelado      { background:rgba(239,68,68,.12);color:#ef4444; }

        .oc-body { padding:0 16px 12px; }
        .oc-row  { display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-secondary);margin-bottom:5px; }
        .oc-row i { width:14px;text-align:center;color:var(--tec);flex-shrink:0; }
        .oc-row strong { color:var(--text-primary); }
        .oc-total { display:flex;justify-content:space-between;align-items:center;background:var(--background);border-radius:10px;padding:10px 12px;margin-top:10px; }
        .oc-total-num { font-size:16px;font-weight:800;color:var(--text-primary); }
        .oc-saldo { font-size:12px;font-weight:600;color:#ef4444; }
        .oc-saldo.ok { color:#059669; }
        .prio-chip { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700; }
        .prio-urgente { background:rgba(239,68,68,.12);color:#dc2626; }
        .prio-vip     { background:rgba(245,158,11,.12);color:#d97706; }

        .oc-footer { padding:10px 16px;border-top:1px solid var(--border);display:flex;gap:6px;flex-wrap:wrap; }
        .btn-est { padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--background);cursor:pointer;font-size:12px;color:var(--text-secondary);transition:all .15s;display:flex;align-items:center;gap:4px;white-space:nowrap; }
        .btn-est:hover         { background:var(--tec);color:#fff;border-color:var(--tec); }
        .btn-est.success:hover { background:#0FD186;border-color:#0FD186; }
        .btn-est.danger:hover  { background:#ef4444;border-color:#ef4444; }
        .btn-est.warn:hover    { background:#f59e0b;border-color:#f59e0b; }

        /* Modal */
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface);border-radius:20px;width:100%;max-width:640px;max-height:93vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--surface);z-index:2; }
        .modal-header h3 { margin:0;font-size:17px;font-weight:700; }
        .modal-close { background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;padding:4px 8px;border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block;font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px; }
        .fi { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text-primary);box-sizing:border-box; }
        .fi:focus { outline:none;border-color:var(--tec); }
        .fg-grid  { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
        .fg-grid3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px; }
        .sec-label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border); }
        .total-box { background:var(--background);border-radius:12px;padding:14px; }
        .total-row { display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;color:var(--text-secondary); }
        .total-row.grand { font-size:16px;font-weight:700;color:var(--text-primary);border-top:1px solid var(--border);padding-top:10px;margin-top:4px; }
        /* Selector cliente */
        .cli-select-list { max-height:150px;overflow-y:auto;border:1.5px solid var(--border);border-radius:10px; }
        .cli-select-item { padding:9px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--border);transition:background .1s; }
        .cli-select-item:last-child { border-bottom:none; }
        .cli-select-item:hover { background:var(--tec-light); }
        /* Cobrar modal */
        .modal-box.sm { max-width:400px; }
        /* Toast */
        .toast { position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#1e293b;color:white;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;opacity:0;transition:opacity .3s;white-space:nowrap;pointer-events:none; }
        .toast.show { opacity:1; }
        @media(max-width:600px) { .ordenes-grid { grid-template-columns:1fr;padding:12px;gap:12px; } .stats-bar,.filter-bar,.tec-toolbar { padding:12px; } }
    </style>
</head>
<body>
<script>window.APP_BASE='<?= $base ?>';</script>
<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content" style="flex:1;overflow-y:auto;padding:0;">
        <?php include '../includes/header.php'; ?>

        <div class="tec-toolbar">
            <div>
                <h1><i class="fas fa-tools" style="color:var(--tec);margin-right:8px;"></i>Órdenes de Servicio</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="clientes.php" class="btn btn-secondary" style="text-decoration:none;"><i class="fas fa-users"></i> Clientes</a>
                <button class="btn btn-primary" onclick="abrirModalOrden()"><i class="fas fa-plus"></i> Nueva Orden</button>
            </div>
        </div>

        <div class="stats-bar" id="statsBar">
            <div class="stat-pill sp-all active" onclick="setFiltro('')"          id="pill-all"><div class="dot" style="background:#64748b;"></div><span id="st-total">0</span> Todas</div>
            <div class="stat-pill sp-ingresado"  onclick="setFiltro('ingresado')" id="pill-ingresado"><div class="dot" style="background:#6366f1;"></div><span id="st-ing">0</span> Ingresadas</div>
            <div class="stat-pill sp-reparando"  onclick="setFiltro('en_reparacion')" id="pill-rep"><div class="dot" style="background:#f59e0b;"></div><span id="st-rep">0</span> Reparando</div>
            <div class="stat-pill sp-espera"     onclick="setFiltro('esperando_repuesto')" id="pill-esp"><div class="dot" style="background:#ef4444;"></div><span id="st-esp">0</span> Sin repuesto</div>
            <div class="stat-pill sp-listo"      onclick="setFiltro('listo')"     id="pill-listo"><div class="dot" style="background:#0FD186;"></div><span id="st-listo">0</span> Listos</div>
            <div class="stat-pill sp-urgente"    onclick="setFiltro('_urgente')"  id="pill-urg"><div class="dot" style="background:#ef4444;"></div><span id="st-urg">0</span> Urgentes</div>
        </div>

        <div class="filter-bar">
            <input class="filter-input" type="text" id="buscar" placeholder="Buscar por cliente, marca, modelo o serie…" oninput="aplicarFiltros()">
        </div>

        <div id="ordenContent">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin" style="font-size:28px;"></i></div>
        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Orden -->
<div class="modal-overlay" id="modalOrden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalOrdenTitulo"><i class="fas fa-tools" style="color:var(--tec);margin-right:8px;"></i>Nueva Orden</h3>
            <button class="modal-close" onclick="cerrarModalOrden()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ordId">

            <!-- Cliente -->
            <div class="sec-label"><i class="fas fa-user" style="margin-right:5px;"></i>Cliente</div>
            <div class="fg" id="fgCliSelect">
                <input class="fi" type="text" id="ordCliBuscar" placeholder="Buscar cliente…" oninput="buscarClientes(this.value)" autocomplete="off">
                <div class="cli-select-list" id="ordCliLista" style="display:none;margin-top:6px;"></div>
                <input type="hidden" id="ordCliId">
                <div id="ordCliSeleccionado" style="display:none;margin-top:8px;padding:8px 12px;background:var(--tec-light);border-radius:8px;font-size:13px;font-weight:600;color:var(--tec);display:flex;align-items:center;justify-content:space-between;">
                    <span id="ordCliNombre"></span>
                    <button onclick="limpiarCliente()" style="background:none;border:none;cursor:pointer;color:var(--tec);font-size:12px;">✕ cambiar</button>
                </div>
            </div>

            <!-- Equipo -->
            <div class="sec-label"><i class="fas fa-laptop" style="margin-right:5px;"></i>Equipo</div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Tipo <span style="color:#ef4444;">*</span></label>
                    <select class="fi" id="ordTipo">
                        <option value="celular">📱 Celular</option>
                        <option value="tablet">📲 Tablet</option>
                        <option value="notebook">💻 Notebook</option>
                        <option value="pc">🖥️ PC</option>
                        <option value="impresora">🖨️ Impresora</option>
                        <option value="tv">📺 TV</option>
                        <option value="consola">🎮 Consola</option>
                        <option value="otro">🔧 Otro</option>
                    </select>
                </div>
                <div class="fg"><label>Marca</label><input class="fi" type="text" id="ordMarca" placeholder="Samsung, Apple, HP…"></div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>Modelo</label><input class="fi" type="text" id="ordModelo" placeholder="Galaxy S21, iPhone 13…"></div>
                <div class="fg"><label>N° Serie / IMEI</label><input class="fi" type="text" id="ordSerie" placeholder="357…"></div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>Color</label><input class="fi" type="text" id="ordColor" placeholder="Negro, Blanco…"></div>
                <div class="fg"><label>Accesorios</label><input class="fi" type="text" id="ordAccesorios" placeholder="Cargador, estuche…"></div>
            </div>
            <div class="fg"><label>Contraseña / PIN</label><input class="fi" type="text" id="ordContrasena" placeholder="Solo si es necesario para reparar"></div>

            <!-- Falla -->
            <div class="sec-label"><i class="fas fa-exclamation-triangle" style="margin-right:5px;"></i>Falla y diagnóstico</div>
            <div class="fg"><label>Falla reportada <span style="color:#ef4444;">*</span></label><textarea class="fi" id="ordFalla" rows="2" style="resize:vertical;" placeholder="¿Qué le pasa según el cliente?"></textarea></div>
            <div class="fg"><label>Diagnóstico técnico</label><textarea class="fi" id="ordDiag" rows="2" style="resize:vertical;" placeholder="Lo que encontró el técnico…"></textarea></div>

            <!-- Precios -->
            <div class="sec-label"><i class="fas fa-dollar-sign" style="margin-right:5px;"></i>Presupuesto</div>
            <div class="fg-grid">
                <div class="fg"><label>Mano de obra</label><input class="fi" type="number" id="ordMO" placeholder="0" min="0" step="100" oninput="calcTotalOrden()"></div>
                <div class="fg"><label>Repuestos</label><input class="fi" type="number" id="ordRT" placeholder="0" min="0" step="100" oninput="calcTotalOrden()"></div>
            </div>
            <div class="total-box" style="margin-bottom:14px;">
                <div class="total-row"><span>Mano de obra</span><span id="totMO">$0</span></div>
                <div class="total-row"><span>Repuestos</span><span id="totRT">$0</span></div>
                <div class="total-row grand"><span>Total</span><span id="totTotal" style="color:var(--tec);">$0</span></div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>Seña / Adelanto</label><input class="fi" type="number" id="ordSena" placeholder="0" min="0" oninput="calcTotalOrden()"></div>
                <div class="fg"><label>Método de pago</label>
                    <select class="fi" id="ordMetodo">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta_debito">💳 Débito</option>
                        <option value="tarjeta_credito">💳 Crédito</option>
                        <option value="transferencia">🏦 Transferencia</option>
                        <option value="qr">📱 QR</option>
                    </select>
                </div>
            </div>

            <!-- Estado y fechas -->
            <div class="sec-label"><i class="fas fa-calendar" style="margin-right:5px;"></i>Gestión</div>
            <div class="fg-grid">
                <div class="fg"><label>Estado</label>
                    <select class="fi" id="ordEstado">
                        <option value="ingresado">Ingresado</option>
                        <option value="diagnosticando">Diagnosticando</option>
                        <option value="esperando_repuesto">Esperando repuesto</option>
                        <option value="en_reparacion">En reparación</option>
                    </select>
                </div>
                <div class="fg"><label>Prioridad</label>
                    <select class="fi" id="ordPrio">
                        <option value="normal">Normal</option>
                        <option value="urgente">🔴 Urgente</option>
                        <option value="vip">⭐ VIP</option>
                    </select>
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg"><label>Fecha ingreso</label><input class="fi" type="date" id="ordFI"></div>
                <div class="fg"><label>Fecha promesa</label><input class="fi" type="date" id="ordFP"></div>
            </div>
            <div class="fg"><label>Técnico asignado</label><input class="fi" type="text" id="ordTecnico" placeholder="Nombre del técnico…"></div>
            <div class="fg"><label>Observaciones</label><textarea class="fi" id="ordObs" rows="2" style="resize:vertical;"></textarea></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalOrden()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarOrden()"><i class="fas fa-save"></i> Guardar Orden</button>
        </div>
    </div>
</div>

<!-- Modal cobrar saldo -->
<div class="modal-overlay" id="modalCobrar">
    <div class="modal-box sm">
        <div class="modal-header">
            <h3><i class="fas fa-dollar-sign" style="color:#0FD186;margin-right:8px;"></i>Cobrar y Entregar</h3>
            <button class="modal-close" onclick="cerrarModalCobrar()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cobOrdId">
            <div style="text-align:center;padding:8px 0 16px;">
                <p id="cobClienteNom" style="font-size:15px;font-weight:700;margin:0 0 4px;"></p>
                <p style="font-size:13px;color:var(--text-secondary);margin:0 0 2px;">Equipo: <span id="cobEquipo" style="font-weight:600;"></span></p>
                <p style="font-size:13px;color:var(--text-secondary);margin:0;">Saldo pendiente:</p>
                <p id="cobMonto" style="font-size:32px;font-weight:800;color:#ef4444;margin:4px 0;"></p>
            </div>
            <div class="fg"><label>Método de pago</label>
                <select class="fi" id="cobMetodo">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="tarjeta_debito">💳 Débito</option>
                    <option value="tarjeta_credito">💳 Crédito</option>
                    <option value="transferencia">🏦 Transferencia</option>
                    <option value="qr">📱 QR</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalCobrar()">Cancelar</button>
            <button class="btn btn-primary" onclick="confirmarEntrega()" style="background:#0FD186;border-color:#0FD186;"><i class="fas fa-check"></i> Cobrar y Entregar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE      = '<?= $base ?>';
const API_ORD   = BASE + '/api/tecnologia/ordenes.php';
const API_CLI   = BASE + '/api/tecnologia/clientes.php';

let todasOrdenes = [];
let todosClientes = [];
let filtroEstado  = '';

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    let r, j;
    try {
        r = await fetch(API_ORD, {credentials:'include'});
        j = await r.json();
    } catch(e) {
        document.getElementById('ordenContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-plug" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i><p style="font-weight:600;">Error de conexión</p></div>`;
        return;
    }
    if (!j.success) {
        document.getElementById('ordenContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-lock" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i><p>${j.message}</p></div>`;
        return;
    }
    todasOrdenes = j.data.ordenes || [];
    const st = j.data.stats || {};
    const activos = (st.ingresados||0)+(st.en_reparacion||0)+(st.esperando_repuesto||0)+(st.listos||0);
    document.getElementById('subtitulo').textContent = `${st.total||0} órdenes · ${activos} activas`;
    document.getElementById('st-total').textContent = st.total  || 0;
    document.getElementById('st-ing').textContent   = st.ingresados || 0;
    document.getElementById('st-rep').textContent   = st.en_reparacion || 0;
    document.getElementById('st-esp').textContent   = st.esperando_repuesto || 0;
    document.getElementById('st-listo').textContent = st.listos || 0;
    document.getElementById('st-urg').textContent   = st.urgentes || 0;
    aplicarFiltros();

    const rc = await fetch(API_CLI, {credentials:'include'});
    const jc = await rc.json();
    if (jc.success) todosClientes = jc.data.clientes || [];

    // Pre-seleccionar cliente si viene por URL
    const urlCli = new URLSearchParams(window.location.search).get('cliente');
    if (urlCli) {
        const c = todosClientes.find(x => x.id == urlCli);
        if (c) setTimeout(() => { abrirModalOrden(); seleccionarCliente(c); }, 300);
    }
}

function setFiltro(e) {
    filtroEstado = e;
    document.querySelectorAll('.stat-pill').forEach(p => p.classList.remove('active'));
    const map = {'':'pill-all','ingresado':'pill-ingresado','en_reparacion':'pill-rep','esperando_repuesto':'pill-esp','listo':'pill-listo','_urgente':'pill-urg'};
    document.getElementById(map[e]||'pill-all')?.classList.add('active');
    aplicarFiltros();
}

function aplicarFiltros() {
    const q = (document.getElementById('buscar')?.value||'').toLowerCase().trim();
    let filtradas = todasOrdenes.filter(o => {
        if (filtroEstado === '_urgente') { if (o.prioridad !== 'urgente' || ['entregado','cancelado','sin_reparacion'].includes(o.estado)) return false; }
        else if (filtroEstado && o.estado !== filtroEstado) return false;
        if (q) {
            const h = ((o.cliente_nombre||'')+(o.equipo_marca||'')+(o.equipo_modelo||'')+(o.equipo_serie||'')).toLowerCase();
            if (!h.includes(q)) return false;
        }
        return true;
    });
    render(filtradas);
}

// ── Render ────────────────────────────────────────────────────────────────────
function render(lista) {
    const cont = document.getElementById('ordenContent');
    if (!lista.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);"><i class="fas fa-tools" style="font-size:48px;opacity:.12;display:block;margin-bottom:16px;"></i><p style="font-size:16px;font-weight:600;margin-bottom:16px;">No hay órdenes</p><button class="btn btn-primary" onclick="abrirModalOrden()"><i class="fas fa-plus"></i> Nueva Orden</button></div>`;
        return;
    }
    cont.innerHTML = `<div class="ordenes-grid">${lista.map(renderCard).join('')}</div>`;
}

function renderCard(o) {
    const ei = estadoInfo(o.estado);
    const saldo = parseFloat(o.saldo||0);
    const equipo = [tipoLabel(o.equipo_tipo), o.equipo_marca, o.equipo_modelo].filter(Boolean).join(' ');
    const prioBadge = o.prioridad !== 'normal' ? `<span class="prio-chip prio-${o.prioridad}">${o.prioridad==='urgente'?'🔴 URGENTE':'⭐ VIP'}</span>` : '';
    const btns = botonesCard(o);
    return `<div class="orden-card ${o.estado}${o.prioridad!=='normal'?' prio-'+o.prioridad:''}">
        <div class="oc-header">
            <div>
                <div class="oc-num">#${o.id} ${prioBadge}</div>
                <div class="oc-cliente">${esc(o.cliente_nombre)}</div>
            </div>
            <span class="estado-badge eb-${o.estado}">${ei}</span>
        </div>
        <div class="oc-body">
            <div class="oc-row"><i class="fas fa-${iconoEquipo(o.equipo_tipo)}"></i><strong>${esc(equipo||'—')}</strong></div>
            ${o.equipo_serie?`<div class="oc-row"><i class="fas fa-barcode"></i><span>${esc(o.equipo_serie)}</span></div>`:''}
            <div class="oc-row"><i class="fas fa-exclamation-triangle"></i><span style="font-size:11px;">${esc((o.falla_reportada||'').substring(0,60))}${(o.falla_reportada||'').length>60?'…':''}</span></div>
            ${o.fecha_promesa?`<div class="oc-row"><i class="fas fa-calendar-check"></i><span>Promesa: <strong>${formatFecha(o.fecha_promesa)}</strong></span></div>`:''}
            ${o.tecnico?`<div class="oc-row"><i class="fas fa-user-cog"></i><span>${esc(o.tecnico)}</span></div>`:''}
            ${o.cliente_tel?`<div class="oc-row"><i class="fas fa-phone"></i><a href="tel:${esc(o.cliente_tel)}" style="color:var(--tec);font-weight:600;">${esc(o.cliente_tel)}</a></div>`:''}
            <div class="oc-total">
                <div>
                    <div class="oc-total-num">${fmt(o.total)}</div>
                    ${o.seña>0?`<div style="font-size:11px;color:var(--text-secondary);">Seña: ${fmt(o.seña)}</div>`:''}
                </div>
                <div class="${saldo>0?'oc-saldo':'oc-saldo ok'}">
                    ${saldo>0?`<i class="fas fa-exclamation-circle"></i> Saldo: ${fmt(saldo)}`:'<i class="fas fa-check-circle"></i> Pagado'}
                </div>
            </div>
        </div>
        <div class="oc-footer">${btns}</div>
    </div>`;
}

function botonesCard(o) {
    let b = '';
    if (o.estado === 'ingresado')           b += `<button class="btn-est" onclick="cambiarEstado(${o.id},'diagnosticando')"><i class="fas fa-search"></i> Diagnosticar</button>`;
    if (o.estado === 'diagnosticando')      b += `<button class="btn-est warn" onclick="cambiarEstado(${o.id},'en_reparacion')"><i class="fas fa-wrench"></i> Reparar</button>`;
    if (o.estado === 'en_reparacion')       b += `<button class="btn-est success" onclick="cambiarEstado(${o.id},'listo')"><i class="fas fa-check"></i> Listo</button>`;
    if (o.estado === 'esperando_repuesto')  b += `<button class="btn-est warn" onclick="cambiarEstado(${o.id},'en_reparacion')"><i class="fas fa-wrench"></i> Llegó repuesto</button>`;
    if (o.estado === 'listo')               b += `<button class="btn-est success" onclick="entregar(${o.id})"><i class="fas fa-handshake"></i> Entregar</button>`;
    if (!['entregado','cancelado','sin_reparacion'].includes(o.estado)) {
        b += `<button class="btn-est" onclick="editarOrden(${o.id})" title="Editar"><i class="fas fa-edit"></i></button>`;
        b += `<button class="btn-est danger" onclick="cancelarOrden(${o.id})" title="Cancelar"><i class="fas fa-times"></i></button>`;
    }
    return b;
}

// ── Acciones ──────────────────────────────────────────────────────────────────
async function cambiarEstado(id, estado) {
    const r = await fetch(`${API_ORD}?id=${id}`, {method:'PUT',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({estado})});
    const j = await r.json();
    if (j.success) { toast(`Estado: ${estadoInfo(estado)} ✓`); init(); }
    else toast(j.message||'Error','error');
}

function entregar(id) {
    const o = todasOrdenes.find(x => x.id == id);
    if (!o) return;
    if (parseFloat(o.saldo||0) > 0) {
        document.getElementById('cobOrdId').value = id;
        document.getElementById('cobClienteNom').textContent = o.cliente_nombre||'';
        document.getElementById('cobEquipo').textContent = [tipoLabel(o.equipo_tipo), o.equipo_marca, o.equipo_modelo].filter(Boolean).join(' ')||'—';
        document.getElementById('cobMonto').textContent = fmt(o.saldo);
        document.getElementById('modalCobrar').classList.add('open');
    } else {
        cambiarEstado(id, 'entregado');
    }
}
function cerrarModalCobrar() { document.getElementById('modalCobrar').classList.remove('open'); }
async function confirmarEntrega() {
    const id     = document.getElementById('cobOrdId').value;
    const metodo = document.getElementById('cobMetodo').value;
    cerrarModalCobrar();
    const r = await fetch(`${API_ORD}?id=${id}`, {method:'PUT',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({estado:'entregado',metodo_pago:metodo})});
    const j = await r.json();
    if (j.success) { toast('Entregado y cobrado ✓'); init(); }
    else toast(j.message||'Error','error');
}
async function cancelarOrden(id) {
    if (!confirm('¿Cancelar esta orden?')) return;
    const r = await fetch(`${API_ORD}?id=${id}`, {method:'DELETE',credentials:'include'});
    const j = await r.json();
    if (j.success) { toast('Orden cancelada'); init(); }
    else toast(j.message||'Error','error');
}

// ── Modal Orden ───────────────────────────────────────────────────────────────
function abrirModalOrden(o=null) {
    document.getElementById('ordId').value       = o?o.id:'';
    document.getElementById('ordTipo').value     = o?(o.equipo_tipo||'celular'):'celular';
    document.getElementById('ordMarca').value    = o?(o.equipo_marca||''):'';
    document.getElementById('ordModelo').value   = o?(o.equipo_modelo||''):'';
    document.getElementById('ordSerie').value    = o?(o.equipo_serie||''):'';
    document.getElementById('ordColor').value    = o?(o.equipo_color||''):'';
    document.getElementById('ordAccesorios').value = o?(o.accesorios||''):'';
    document.getElementById('ordContrasena').value = o?(o.contrasena||''):'';
    document.getElementById('ordFalla').value    = o?(o.falla_reportada||''):'';
    document.getElementById('ordDiag').value     = o?(o.diagnostico||''):'';
    document.getElementById('ordMO').value       = o?(o.mano_obra||0):0;
    document.getElementById('ordRT').value       = o?(o.repuestos_total||0):0;
    document.getElementById('ordSena').value     = o?(o.seña||0):0;
    document.getElementById('ordMetodo').value   = o?(o.metodo_pago||'efectivo'):'efectivo';
    document.getElementById('ordEstado').value   = o?(o.estado||'ingresado'):'ingresado';
    document.getElementById('ordPrio').value     = o?(o.prioridad||'normal'):'normal';
    document.getElementById('ordFI').value       = o?(o.fecha_ingreso||hoy()):hoy();
    document.getElementById('ordFP').value       = o?(o.fecha_promesa||''):'';
    document.getElementById('ordTecnico').value  = o?(o.tecnico||''):'';
    document.getElementById('ordObs').value      = o?(o.observaciones||''):'';
    if (o) {
        const c = todosClientes.find(x=>x.id==o.cliente_id);
        if (c) seleccionarCliente(c); else limpiarCliente();
    } else limpiarCliente();
    document.getElementById('modalOrdenTitulo').innerHTML = o
        ? `<i class="fas fa-edit" style="color:var(--tec);margin-right:8px;"></i>Editar Orden #${o.id}`
        : `<i class="fas fa-tools" style="color:var(--tec);margin-right:8px;"></i>Nueva Orden`;
    calcTotalOrden();
    document.getElementById('modalOrden').classList.add('open');
}
function cerrarModalOrden() { document.getElementById('modalOrden').classList.remove('open'); document.getElementById('ordCliLista').style.display='none'; }
function editarOrden(id) { abrirModalOrden(todasOrdenes.find(o=>o.id==id)); }

// Búsqueda cliente en modal
function buscarClientes(q) {
    const lista = document.getElementById('ordCliLista');
    if (!q.trim()) { lista.style.display='none'; return; }
    const res = todosClientes.filter(c => (c.nombre+' '+c.apellido).toLowerCase().includes(q.toLowerCase()) || (c.dni||'').includes(q)).slice(0,8);
    if (!res.length) { lista.style.display='none'; return; }
    lista.innerHTML = res.map(c => `<div class="cli-select-item" onclick='seleccionarCliente(${JSON.stringify({id:c.id,nombre:c.nombre,apellido:c.apellido})})'>
        <strong>${esc(c.apellido)}, ${esc(c.nombre)}</strong>${c.dni?` <span style="color:var(--text-secondary);font-size:11px;">DNI: ${esc(c.dni)}</span>`:''}
    </div>`).join('');
    lista.style.display = 'block';
}
function seleccionarCliente(c) {
    document.getElementById('ordCliId').value = c.id;
    document.getElementById('ordCliNombre').textContent = `${c.apellido}, ${c.nombre}`;
    document.getElementById('ordCliSeleccionado').style.display = 'flex';
    document.getElementById('ordCliBuscar').style.display = 'none';
    document.getElementById('ordCliLista').style.display  = 'none';
}
function limpiarCliente() {
    document.getElementById('ordCliId').value = '';
    document.getElementById('ordCliBuscar').value = '';
    document.getElementById('ordCliBuscar').style.display = 'block';
    document.getElementById('ordCliSeleccionado').style.display = 'none';
    document.getElementById('ordCliLista').style.display = 'none';
}

function calcTotalOrden() {
    const mo  = parseFloat(document.getElementById('ordMO')?.value)||0;
    const rt  = parseFloat(document.getElementById('ordRT')?.value)||0;
    const total = mo + rt;
    document.getElementById('totMO').textContent    = fmt(mo);
    document.getElementById('totRT').textContent    = fmt(rt);
    document.getElementById('totTotal').textContent = fmt(total);
}

async function guardarOrden() {
    const id    = document.getElementById('ordId').value;
    const cliId = document.getElementById('ordCliId').value;
    const falla = document.getElementById('ordFalla').value.trim();
    if (!cliId) { toast('Seleccioná un cliente','error'); return; }
    if (!falla) { toast('Describí la falla reportada','error'); return; }
    const mo = parseFloat(document.getElementById('ordMO').value)||0;
    const rt = parseFloat(document.getElementById('ordRT').value)||0;
    const body = {
        cliente_id:      parseInt(cliId),
        equipo_tipo:     document.getElementById('ordTipo').value,
        equipo_marca:    document.getElementById('ordMarca').value.trim()||null,
        equipo_modelo:   document.getElementById('ordModelo').value.trim()||null,
        equipo_serie:    document.getElementById('ordSerie').value.trim()||null,
        equipo_color:    document.getElementById('ordColor').value.trim()||null,
        accesorios:      document.getElementById('ordAccesorios').value.trim()||null,
        contrasena:      document.getElementById('ordContrasena').value.trim()||null,
        falla_reportada: falla,
        diagnostico:     document.getElementById('ordDiag').value.trim()||null,
        mano_obra:       mo,
        repuestos_total: rt,
        total:           mo + rt,
        'seña':          parseFloat(document.getElementById('ordSena').value)||0,
        metodo_pago:     document.getElementById('ordMetodo').value,
        estado:          document.getElementById('ordEstado').value,
        prioridad:       document.getElementById('ordPrio').value,
        fecha_ingreso:   document.getElementById('ordFI').value||null,
        fecha_promesa:   document.getElementById('ordFP').value||null,
        tecnico:         document.getElementById('ordTecnico').value.trim()||null,
        observaciones:   document.getElementById('ordObs').value.trim()||null,
    };
    const r = await fetch(id?`${API_ORD}?id=${id}`:API_ORD, {method:id?'PUT':'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalOrden(); toast(id?'Orden actualizada ✓':'Orden creada ✓'); init(); }
    else toast(j.message||'Error al guardar','error');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoInfo(e) {
    return {ingresado:'Ingresado',diagnosticando:'Diagnosticando',esperando_repuesto:'Sin repuesto',en_reparacion:'En reparación',listo:'Listo ✓',entregado:'Entregado',sin_reparacion:'Sin reparación',cancelado:'Cancelado'}[e]||e;
}
function tipoLabel(t) { return {celular:'📱 Celular',tablet:'📲 Tablet',notebook:'💻 Notebook',pc:'🖥️ PC',impresora:'🖨️ Impresora',tv:'📺 TV',consola:'🎮 Consola',otro:'🔧 Otro'}[t]||t||'—'; }
function iconoEquipo(t) { return {celular:'mobile-alt',tablet:'tablet-alt',notebook:'laptop',pc:'desktop',impresora:'print',tv:'tv',consola:'gamepad',otro:'tools'}[t]||'tools'; }
function fmt(n)      { return '$' + Number(n||0).toLocaleString('es-AR',{minimumFractionDigits:0}); }
function esc(s)      { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function hoy()       { return new Date().toISOString().slice(0,10); }
function formatFecha(f) { if(!f) return ''; const [y,m,d]=f.split('-'); return `${d}/${m}/${y}`; }
function toast(msg, tipo='ok') { const t=document.getElementById('toast'); t.textContent=msg; t.style.background=tipo==='error'?'#ef4444':'#1e293b'; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2500); }

['modalOrden','modalCobrar'].forEach(id => {
    document.getElementById(id).addEventListener('click',e=>{ if(e.target.id===id) document.getElementById(id).classList.remove('open'); });
});
document.addEventListener('keydown',e=>{ if(e.key==='Escape'){ cerrarModalOrden(); cerrarModalCobrar(); } });
document.addEventListener('click',e=>{ if(!e.target.closest('#fgCliSelect')) document.getElementById('ordCliLista').style.display='none'; });
init();
</script>
</body>
</html>
