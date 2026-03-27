<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php'); exit;
}
$base = rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — Óptica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --opt:#0ea5e9; --opt-light:rgba(14,165,233,.1); }

        .opt-toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .opt-toolbar h1 { margin:0; font-size:20px; font-weight:700; color:var(--text-primary); }
        .opt-toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        /* Stats */
        .stats-bar { display:flex; gap:12px; padding:16px 24px 0; flex-wrap:wrap; }
        .stat-pill {
            display:flex; align-items:center; gap:8px;
            padding:8px 16px; border-radius:20px; font-size:13px; font-weight:600;
            border:1.5px solid transparent; cursor:pointer; transition:all .15s;
        }
        .stat-pill .dot { width:10px; height:10px; border-radius:50%; }
        .stat-pill.all      { background:var(--background); border-color:var(--border); color:var(--text-primary); }
        .stat-pill.pending  { background:rgba(245,158,11,.1);  border-color:#f59e0b; color:#d97706; }
        .stat-pill.lab      { background:var(--opt-light);      border-color:var(--opt); color:#0369a1; }
        .stat-pill.ready    { background:rgba(15,209,134,.1);   border-color:#0FD186; color:#059669; }
        .stat-pill.delivered{ background:rgba(99,102,241,.1);   border-color:#6366f1; color:#4f46e5; }
        .stat-pill.saldo    { background:rgba(239,68,68,.1);    border-color:#ef4444; color:#dc2626; }
        .stat-pill.active   { transform:scale(1.04); box-shadow:0 2px 10px rgba(0,0,0,.1); }

        /* Filtros */
        .filter-bar {
            padding:14px 24px 0;
            display:flex; gap:10px; flex-wrap:wrap; align-items:center;
        }
        .filter-input {
            flex:1; min-width:200px; padding:9px 14px 9px 36px;
            border:1.5px solid var(--border); border-radius:10px;
            font-size:14px; background:var(--surface); color:var(--text-primary);
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:10px center;
        }
        .filter-input:focus { outline:none; border-color:var(--opt); }

        /* Cards de pedidos */
        .pedidos-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));
            gap:16px; padding:16px 24px;
        }
        .pedido-card {
            background:var(--surface); border-radius:16px;
            border:1.5px solid var(--border);
            overflow:hidden; transition:all .2s;
        }
        .pedido-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.08); }
        .pedido-card.presupuesto { border-color:#94a3b8; }
        .pedido-card.pendiente   { border-color:#f59e0b; }
        .pedido-card.laboratorio { border-color:var(--opt); }
        .pedido-card.listo       { border-color:#0FD186; box-shadow:0 0 0 2px rgba(15,209,134,.15); }
        .pedido-card.entregado   { border-color:#6366f1; opacity:.75; }
        .pedido-card.cancelado   { border-color:#ef4444; opacity:.5; }

        .pc-header {
            padding:14px 16px 10px;
            display:flex; align-items:flex-start; justify-content:space-between; gap:8px;
        }
        .pc-cliente { font-weight:700; font-size:14px; color:var(--text-primary); }
        .pc-fecha   { font-size:11px; color:var(--text-secondary); margin-top:2px; }
        .estado-badge {
            padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700;
            white-space:nowrap; flex-shrink:0;
        }
        .badge-presupuesto { background:rgba(100,116,139,.15); color:#475569; }
        .badge-pendiente   { background:rgba(245,158,11,.15);  color:#d97706; }
        .badge-laboratorio { background:var(--opt-light);       color:#0369a1; }
        .badge-listo       { background:rgba(15,209,134,.15);   color:#059669; }
        .badge-entregado   { background:rgba(99,102,241,.15);   color:#4f46e5; }
        .badge-cancelado   { background:rgba(239,68,68,.15);    color:#dc2626; }

        .pc-body { padding:0 16px 12px; }
        .pc-row {
            display:flex; align-items:center; gap:6px;
            font-size:12px; color:var(--text-secondary); margin-bottom:5px;
        }
        .pc-row i { width:14px; text-align:center; color:var(--opt); flex-shrink:0; }
        .pc-row strong { color:var(--text-primary); }

        .pc-totales {
            display:flex; justify-content:space-between; align-items:center;
            background:var(--background); border-radius:10px; padding:10px 12px;
            margin-top:10px;
        }
        .pc-total-num { font-size:16px; font-weight:800; color:var(--text-primary); }
        .pc-saldo { font-size:12px; font-weight:600; color:#ef4444; }
        .pc-saldo.ok { color:#059669; }

        .pc-footer {
            padding:10px 16px; border-top:1px solid var(--border);
            display:flex; gap:6px; flex-wrap:wrap;
        }
        .btn-estado {
            padding:5px 10px; border-radius:8px; border:1px solid var(--border);
            background:var(--background); cursor:pointer; font-size:12px;
            color:var(--text-secondary); transition:all .15s; display:flex;
            align-items:center; gap:4px; white-space:nowrap;
        }
        .btn-estado:hover { background:var(--opt); color:#fff; border-color:var(--opt); }
        .btn-estado.success:hover { background:#0FD186; border-color:#0FD186; }
        .btn-estado.danger:hover  { background:#ef4444; border-color:#ef4444; }
        .btn-estado.purple:hover  { background:#8b5cf6; border-color:#8b5cf6; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:620px; max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-header { padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:var(--surface); z-index:2; }
        .modal-header h3 { margin:0; font-size:17px; font-weight:700; }
        .modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; padding:4px 8px; border-radius:8px; }
        .modal-close:hover { background:var(--background); }
        .modal-body { padding:20px 24px; }
        .modal-footer { padding:14px 24px 20px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); }
        .fg { margin-bottom:14px; }
        .fg label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:6px; }
        .fi { width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--surface); color:var(--text-primary); box-sizing:border-box; }
        .fi:focus { outline:none; border-color:var(--opt); }
        .fg-grid  { display:grid; grid-template-columns:1fr 1fr;     gap:12px; }
        .fg-grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }

        /* Selector cliente en modal */
        .cli-select-list { max-height:160px; overflow-y:auto; border:1.5px solid var(--border); border-radius:10px; }
        .cli-select-item { padding:9px 14px; cursor:pointer; font-size:13px; border-bottom:1px solid var(--border); transition:background .1s; }
        .cli-select-item:last-child { border-bottom:none; }
        .cli-select-item:hover, .cli-select-item.selected { background:var(--opt-light); }

        /* Totales en modal */
        .total-box { background:var(--background); border-radius:12px; padding:14px; }
        .total-row { display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px; color:var(--text-secondary); }
        .total-row.grand { font-size:16px; font-weight:700; color:var(--text-primary); border-top:1px solid var(--border); padding-top:10px; margin-top:4px; }

        /* Toast */
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; pointer-events:none; }
        .toast.show { opacity:1; }

        @media (max-width:600px) {
            .pedidos-grid { grid-template-columns:1fr; padding:12px; gap:12px; }
            .stats-bar { padding:12px; }
            .opt-toolbar { padding:12px; }
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
        <div class="opt-toolbar">
            <div>
                <h1><i class="fas fa-glasses" style="color:var(--opt);margin-right:8px;"></i>Pedidos</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="clientes.php" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <button class="btn btn-primary" onclick="abrirModalPedido()">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </button>
            </div>
        </div>

        <!-- Banner alertas -->
        <div id="alertasBanner" style="display:none;margin:12px 24px 0;border-radius:12px;padding:10px 16px;background:rgba(239,68,68,.07);border:1.5px solid rgba(239,68,68,.3);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;" id="alertasResumen"></div>
            <a href="caja.php#alertas" style="font-size:12px;font-weight:700;color:#dc2626;text-decoration:none;white-space:nowrap;">Ver todas <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- Stats / Filtros rápidos -->
        <div class="stats-bar" id="statsBar">
            <div class="stat-pill all active" onclick="setFiltroEstado('')" id="pill-all">
                <div class="dot" style="background:#64748b;"></div><span id="st-total">0</span> Todos
            </div>
            <div class="stat-pill pending" onclick="setFiltroEstado('pendiente')" id="pill-pendiente">
                <div class="dot" style="background:#f59e0b;"></div><span id="st-pend">0</span> Pendientes
            </div>
            <div class="stat-pill lab" onclick="setFiltroEstado('laboratorio')" id="pill-laboratorio">
                <div class="dot" style="background:var(--opt);"></div><span id="st-lab">0</span> Laboratorio
            </div>
            <div class="stat-pill ready" onclick="setFiltroEstado('listo')" id="pill-listo">
                <div class="dot" style="background:#0FD186;"></div><span id="st-listo">0</span> Listos
            </div>
            <div class="stat-pill delivered" onclick="setFiltroEstado('entregado')" id="pill-entregado">
                <div class="dot" style="background:#6366f1;"></div><span id="st-entregado">0</span> Entregados
            </div>
            <div class="stat-pill saldo" onclick="setFiltroEstado('_saldo')" id="pill-saldo">
                <div class="dot" style="background:#ef4444;"></div><span id="st-saldo">0</span> Con saldo
            </div>
        </div>

        <!-- Búsqueda -->
        <div class="filter-bar">
            <input class="filter-input" type="text" id="buscar" placeholder="Buscar por cliente o armazón…" oninput="filtrar()">
        </div>

        <!-- Contenido -->
        <div id="pedContent">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Pedido -->
<div class="modal-overlay" id="modalPedido">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalPedTitulo"><i class="fas fa-glasses" style="color:var(--opt);margin-right:8px;"></i>Nuevo Pedido</h3>
            <button class="modal-close" onclick="cerrarModalPedido()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="pedId">

            <!-- Selección cliente -->
            <div class="fg" id="fgClienteSelect">
                <label>Cliente <span style="color:#ef4444;">*</span></label>
                <input class="fi" type="text" id="pedClienteBuscar" placeholder="Buscar cliente…" oninput="buscarClientes(this.value)" autocomplete="off">
                <div class="cli-select-list" id="pedClienteLista" style="display:none; margin-top:6px;"></div>
                <input type="hidden" id="pedClienteId">
                <div id="pedClienteSeleccionado" style="display:none;margin-top:8px;padding:8px 12px;background:var(--opt-light);border-radius:8px;font-size:13px;font-weight:600;color:var(--opt);display:flex;align-items:center;justify-content:space-between;">
                    <span id="pedClienteNombre"></span>
                    <button onclick="limpiarClientePed()" style="background:none;border:none;cursor:pointer;color:var(--opt);font-size:12px;">✕ cambiar</button>
                </div>
            </div>

            <!-- Armazón -->
            <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                <i class="fas fa-glasses" style="margin-right:5px;"></i>Armazón
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Marca / Modelo</label>
                    <input class="fi" type="text" id="pedArmazon" placeholder="Ray-Ban RB2140…">
                </div>
                <div class="fg">
                    <label>Color</label>
                    <input class="fi" type="text" id="pedArmazonColor" placeholder="Negro, Tortuga…">
                </div>
            </div>
            <div class="fg">
                <label>Precio armazón</label>
                <input class="fi" type="number" id="pedArmazonPrecio" placeholder="0" min="0" step="100" oninput="calcTotalPed()">
            </div>

            <!-- Lentes -->
            <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                <i class="fas fa-circle" style="margin-right:5px;"></i>Lentes
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Tipo de lente</label>
                    <select class="fi" id="pedLenteTipo">
                        <option value="monofocal">Monofocal</option>
                        <option value="bifocal">Bifocal</option>
                        <option value="progresivo">Progresivo</option>
                        <option value="solar">Solar</option>
                        <option value="contacto">Lentes de contacto</option>
                        <option value="sin_lente">Sin lentes</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Material</label>
                    <input class="fi" type="text" id="pedLenteMat" placeholder="CR-39, Policarbonato…">
                </div>
            </div>
            <div class="fg">
                <label>Tratamiento</label>
                <input class="fi" type="text" id="pedLenteTrat" placeholder="Antirreflejo, Fotocromático, UV400…">
            </div>
            <div class="fg">
                <label>Precio lentes</label>
                <input class="fi" type="number" id="pedLentePrecio" placeholder="0" min="0" step="100" oninput="calcTotalPed()">
            </div>

            <!-- Laboratorio -->
            <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                <i class="fas fa-flask" style="margin-right:5px;"></i>Laboratorio
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Laboratorio</label>
                    <input class="fi" type="text" id="pedLab" placeholder="Laboratorio Visión…">
                </div>
                <div class="fg">
                    <label>Entrega estimada</label>
                    <input class="fi" type="date" id="pedFechaEst">
                </div>
            </div>

            <!-- Estado y pago -->
            <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--text-secondary);margin:16px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);">
                <i class="fas fa-dollar-sign" style="margin-right:5px;"></i>Pago
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Estado inicial</label>
                    <select class="fi" id="pedEstado">
                        <option value="presupuesto">Presupuesto</option>
                        <option value="pendiente" selected>Pendiente</option>
                        <option value="laboratorio">En laboratorio</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Descuento</label>
                    <input class="fi" type="number" id="pedDescuento" placeholder="0" min="0" oninput="calcTotalPed()">
                </div>
            </div>

            <!-- Totales -->
            <div class="total-box" style="margin-bottom:14px;">
                <div class="total-row"><span>Armazón</span><span id="totArmazon">$0</span></div>
                <div class="total-row"><span>Lentes</span><span id="totLentes">$0</span></div>
                <div class="total-row"><span>Descuento</span><span id="totDescuento" style="color:#ef4444;">- $0</span></div>
                <div class="total-row grand"><span>Total</span><span id="totTotal" style="color:var(--opt);">$0</span></div>
            </div>

            <div class="fg-grid">
                <div class="fg">
                    <label>Seña / Adelanto</label>
                    <input class="fi" type="number" id="pedSena" placeholder="0" min="0" oninput="calcTotalPed()">
                </div>
                <div class="fg">
                    <label>Método de pago</label>
                    <select class="fi" id="pedMetodo">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="tarjeta_debito">💳 Débito</option>
                        <option value="tarjeta_credito">💳 Crédito</option>
                        <option value="transferencia">🏦 Transferencia</option>
                        <option value="qr">📱 QR / Billetera</option>
                    </select>
                </div>
            </div>
            <div class="fg">
                <label>Observaciones</label>
                <textarea class="fi" id="pedObs" rows="2" style="resize:vertical;" placeholder="Notas adicionales…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalPedido()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarPedido()"><i class="fas fa-save"></i> Guardar Pedido</button>
        </div>
    </div>
</div>

<!-- Modal Cobrar saldo -->
<div class="modal-overlay" id="modalCobrar">
    <div class="modal-box" style="max-width:400px;">
        <div class="modal-header">
            <h3><i class="fas fa-dollar-sign" style="color:#0FD186;margin-right:8px;"></i>Cobrar Saldo</h3>
            <button class="modal-close" onclick="cerrarModalCobrar()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cobrarPedId">
            <div style="text-align:center;padding:8px 0 16px;">
                <p id="cobrarClienteNombre" style="font-size:15px;font-weight:700;margin:0 0 4px;"></p>
                <p style="font-size:13px;color:var(--text-secondary);margin:0;">Saldo pendiente:</p>
                <p id="cobrarMonto" style="font-size:32px;font-weight:800;color:#ef4444;margin:4px 0;"></p>
            </div>
            <div class="fg">
                <label>Método de pago</label>
                <select class="fi" id="cobrarMetodo">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="tarjeta_debito">💳 Débito</option>
                    <option value="tarjeta_credito">💳 Crédito</option>
                    <option value="transferencia">🏦 Transferencia</option>
                    <option value="qr">📱 QR / Billetera</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalCobrar()">Cancelar</button>
            <button class="btn btn-primary" onclick="confirmarCobro()" style="background:#0FD186;border-color:#0FD186;"><i class="fas fa-check"></i> Confirmar cobro y entregar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE       = '<?= $base ?>';
const API_PED    = BASE + '/api/optica/pedidos.php';
const API_CLI    = BASE + '/api/optica/clientes.php';
const API_ALERTA = BASE + '/api/optica/alertas.php';

let todosPedidos = [];
let pedidosFiltrados = [];
let todosClientes    = [];
let filtroEstado     = '';

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    let r, j;
    try {
        r = await fetch(API_PED, {credentials:'include'});
        j = await r.json();
    } catch(e) {
        document.getElementById('pedContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);">
            <i class="fas fa-plug" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i>
            <p style="font-weight:600;">Error de conexión</p></div>`;
        return;
    }
    if (!j.success) {
        document.getElementById('pedContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);">
            <i class="fas fa-lock" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i>
            <p style="font-weight:600;">${j.message}</p>
            <a href="../../index.php" class="btn btn-primary" style="margin-top:12px;">Iniciar sesión</a></div>`;
        return;
    }
    todosPedidos = j.data.pedidos || [];
    const st = j.data.stats || {};
    document.getElementById('subtitulo').textContent = `${st.total||0} pedidos en total`;
    document.getElementById('st-total').textContent    = st.total       || 0;
    document.getElementById('st-pend').textContent     = st.pendientes  || 0;
    document.getElementById('st-lab').textContent      = st.en_laboratorio || 0;
    document.getElementById('st-listo').textContent    = st.listos      || 0;
    document.getElementById('st-entregado').textContent= st.entregados  || 0;
    document.getElementById('st-saldo').textContent    = st.con_saldo   || 0;
    aplicarFiltros();

    // Banner de alertas (no bloquea)
    fetch(API_ALERTA, {credentials:'include'}).then(ra => ra.json()).then(ja => {
        if (!ja.success) return;
        const d = ja.data;
        const partes = [];
        if (d.listos_sin_entregar.count) partes.push(`<span style="display:flex;align-items:center;gap:5px;"><i class="fas fa-check-circle" style="color:#0FD186;"></i> <strong>${d.listos_sin_entregar.count}</strong> listos sin retirar</span>`);
        if (d.lab_retrasados.count)      partes.push(`<span style="display:flex;align-items:center;gap:5px;"><i class="fas fa-clock" style="color:#ef4444;"></i> <strong>${d.lab_retrasados.count}</strong> retrasados en lab</span>`);
        if (d.lab_sin_fecha.count)       partes.push(`<span style="display:flex;align-items:center;gap:5px;"><i class="fas fa-question-circle" style="color:#f59e0b;"></i> <strong>${d.lab_sin_fecha.count}</strong> sin fecha estimada</span>`);
        if (!partes.length) return;
        document.getElementById('alertasResumen').innerHTML = partes.join('<span style="color:var(--border);">|</span>');
        document.getElementById('alertasBanner').style.display = 'flex';
    }).catch(() => {});

    // Cargar clientes para el modal
    const rc = await fetch(API_CLI, {credentials:'include'});
    const jc = await rc.json();
    if (jc.success) todosClientes = jc.data.clientes || [];

    // Si viene ?cliente= en la URL, preseleccionar
    const urlParams = new URLSearchParams(window.location.search);
    const cliParam = urlParams.get('cliente');
    if (cliParam) {
        const c = todosClientes.find(x => x.id == cliParam);
        if (c) { setTimeout(() => { abrirModalPedido(); seleccionarCliente(c); }, 300); }
    }
}

function setFiltroEstado(e) {
    filtroEstado = e;
    document.querySelectorAll('.stat-pill').forEach(p => p.classList.remove('active'));
    const pillaId = e === '' ? 'pill-all' : (e === '_saldo' ? 'pill-saldo' : `pill-${e}`);
    document.getElementById(pillaId)?.classList.add('active');
    aplicarFiltros();
}

function aplicarFiltros() {
    const q = (document.getElementById('buscar')?.value || '').toLowerCase().trim();
    pedidosFiltrados = todosPedidos.filter(p => {
        if (filtroEstado === '_saldo') { if (parseFloat(p.saldo||0) <= 0) return false; }
        else if (filtroEstado && p.estado !== filtroEstado) return false;
        if (q) {
            const haystack = ((p.cliente_nombre||'')+(p.armazon||'')+(p.lente_tipo||'')).toLowerCase();
            if (!haystack.includes(q)) return false;
        }
        return true;
    });
    renderPedidos();
}
function filtrar() { aplicarFiltros(); }

// ── Render ────────────────────────────────────────────────────────────────────
function renderPedidos() {
    const cont = document.getElementById('pedContent');
    if (!pedidosFiltrados.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px 24px;color:var(--text-secondary);">
            <i class="fas fa-glasses" style="font-size:48px;opacity:.12;display:block;margin-bottom:16px;"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:8px;">No hay pedidos</p>
            <button class="btn btn-primary" onclick="abrirModalPedido()"><i class="fas fa-plus"></i> Nuevo Pedido</button>
        </div>`;
        return;
    }
    const html = `<div class="pedidos-grid">${pedidosFiltrados.map(renderCard).join('')}</div>`;
    cont.innerHTML = html;
}

function renderCard(p) {
    const ei = estadoInfo(p.estado);
    const saldo = parseFloat(p.saldo||0);
    const botones = botonesEstado(p);
    return `<div class="pedido-card ${p.estado}">
        <div class="pc-header">
            <div>
                <div class="pc-cliente">${esc(p.cliente_nombre)}</div>
                <div class="pc-fecha">${formatFecha(p.created_at?.slice(0,10))}${p.fecha_entrega_est?' · Entrega est: '+formatFecha(p.fecha_entrega_est):''}</div>
            </div>
            <span class="estado-badge badge-${p.estado}">${ei.label}</span>
        </div>
        <div class="pc-body">
            ${p.armazon?`<div class="pc-row"><i class="fas fa-glasses"></i><span><strong>${esc(p.armazon)}</strong>${p.armazon_color?' — '+esc(p.armazon_color):''}</span></div>`:''}
            <div class="pc-row"><i class="fas fa-circle"></i><span>${lenteTipoLabel(p.lente_tipo)}${p.lente_material?' · '+esc(p.lente_material):''}${p.lente_tratamiento?' · '+esc(p.lente_tratamiento):''}</span></div>
            ${p.laboratorio?`<div class="pc-row"><i class="fas fa-flask"></i><span>${esc(p.laboratorio)}</span></div>`:''}
            ${p.obra_social?`<div class="pc-row"><i class="fas fa-id-card"></i><span>${esc(p.obra_social)}</span></div>`:''}
            <div class="pc-totales">
                <div>
                    <div class="pc-total-num">${fmt(p.total)}</div>
                    ${p.seña>0?`<div style="font-size:11px;color:var(--text-secondary);">Seña: ${fmt(p.seña)}</div>`:''}
                </div>
                <div class="${saldo > 0 ? 'pc-saldo' : 'pc-saldo ok'}">
                    ${saldo > 0 ? `<i class="fas fa-exclamation-circle"></i> Saldo: ${fmt(saldo)}` : '<i class="fas fa-check-circle"></i> Pagado'}
                </div>
            </div>
            ${p.observaciones?`<div style="font-size:11px;color:var(--text-secondary);margin-top:8px;padding:6px 8px;background:var(--background);border-radius:8px;">${esc(p.observaciones)}</div>`:''}
        </div>
        <div class="pc-footer">${botones}</div>
    </div>`;
}

function botonesEstado(p) {
    let btns = '';
    if (p.estado === 'presupuesto') {
        btns += `<button class="btn-estado" onclick="cambiarEstado(${p.id},'pendiente')"><i class="fas fa-check"></i> Confirmar</button>`;
    }
    if (p.estado === 'pendiente') {
        btns += `<button class="btn-estado" onclick="cambiarEstado(${p.id},'laboratorio')"><i class="fas fa-flask"></i> Enviar lab.</button>`;
    }
    if (p.estado === 'laboratorio') {
        btns += `<button class="btn-estado success" onclick="cambiarEstado(${p.id},'listo')"><i class="fas fa-check"></i> Llegó</button>`;
    }
    if (p.estado === 'listo') {
        btns += `<button class="btn-estado success" onclick="entregar(${p.id})"><i class="fas fa-handshake"></i> Entregar</button>`;
    }
    if (!['entregado','cancelado'].includes(p.estado)) {
        btns += `<button class="btn-estado" onclick="editarPedido(${p.id})" title="Editar"><i class="fas fa-edit"></i></button>`;
        btns += `<button class="btn-estado danger" onclick="cancelarPedido(${p.id})" title="Cancelar"><i class="fas fa-times"></i></button>`;
    }
    if (p.estado === 'listo' && p.cliente_tel) {
        const telEsc = esc(p.cliente_tel);
        const nomEsc = esc(p.cliente_nombre||'');
        const armEsc = esc(p.armazon||'');
        btns += `<button class="btn-estado" style="background:#25d366;border-color:#25d366;color:#fff;" onclick="waCliente('${telEsc}','${nomEsc}','${armEsc}')" title="WhatsApp — listo para retirar"><i class="fab fa-whatsapp"></i></button>`;
    }
    if (!['cancelado'].includes(p.estado)) {
        btns += `<button class="btn-estado" onclick="imprimirFolio(${p.id})" title="Imprimir folio"><i class="fas fa-print"></i></button>`;
    }
    return btns;
}

// ── Cambios de estado ─────────────────────────────────────────────────────────
async function cambiarEstado(id, estado) {
    const r = await fetch(`${API_PED}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({estado})
    });
    const j = await r.json();
    if (j.success) { toast(`Estado: ${estadoInfo(estado).label} ✓`); init(); }
    else toast(j.message||'Error','error');
}

function entregar(id) {
    const p = todosPedidos.find(x => x.id == id);
    if (!p) return;
    const saldo = parseFloat(p.saldo||0);
    if (saldo > 0) {
        // Mostrar modal de cobro
        document.getElementById('cobrarPedId').value = id;
        document.getElementById('cobrarClienteNombre').textContent = p.cliente_nombre || '';
        document.getElementById('cobrarMonto').textContent = fmt(saldo);
        document.getElementById('modalCobrar').classList.add('open');
    } else {
        cambiarEstado(id, 'entregado');
    }
}
function cerrarModalCobrar() { document.getElementById('modalCobrar').classList.remove('open'); }
async function confirmarCobro() {
    const id     = document.getElementById('cobrarPedId').value;
    const metodo = document.getElementById('cobrarMetodo').value;
    cerrarModalCobrar();
    const r = await fetch(`${API_PED}?id=${id}`, {
        method:'PUT', credentials:'include',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({estado:'entregado', metodo_pago:metodo})
    });
    const j = await r.json();
    if (j.success) { toast('Pedido entregado y cobrado ✓'); init(); }
    else toast(j.message||'Error','error');
}

async function cancelarPedido(id) {
    if (!confirm('¿Cancelar este pedido?')) return;
    const r = await fetch(`${API_PED}?id=${id}`, {method:'DELETE', credentials:'include'});
    const j = await r.json();
    if (j.success) { toast('Pedido cancelado'); init(); }
    else toast(j.message||'Error','error');
}

// ── Modal Pedido ──────────────────────────────────────────────────────────────
function abrirModalPedido(p = null) {
    document.getElementById('pedId').value           = p ? p.id : '';
    document.getElementById('pedArmazon').value      = p ? (p.armazon||'') : '';
    document.getElementById('pedArmazonColor').value = p ? (p.armazon_color||'') : '';
    document.getElementById('pedArmazonPrecio').value= p ? (p.armazon_precio||0) : '';
    document.getElementById('pedLenteTipo').value    = p ? (p.lente_tipo||'monofocal') : 'monofocal';
    document.getElementById('pedLenteMat').value     = p ? (p.lente_material||'') : '';
    document.getElementById('pedLenteTrat').value    = p ? (p.lente_tratamiento||'') : '';
    document.getElementById('pedLentePrecio').value  = p ? (p.lente_precio||0) : '';
    document.getElementById('pedLab').value          = p ? (p.laboratorio||'') : '';
    document.getElementById('pedFechaEst').value     = p ? (p.fecha_entrega_est||'') : '';
    document.getElementById('pedEstado').value       = p ? p.estado : 'pendiente';
    document.getElementById('pedDescuento').value    = p ? (p.descuento||0) : 0;
    document.getElementById('pedSena').value         = p ? (p.seña||0) : 0;
    document.getElementById('pedMetodo').value       = p ? (p.metodo_pago||'efectivo') : 'efectivo';
    document.getElementById('pedObs').value          = p ? (p.observaciones||'') : '';

    if (p) {
        const c = todosClientes.find(x => x.id == p.cliente_id) || {id:p.cliente_id, nombre:p.cliente_nombre?.split(' ')[0]||'', apellido:p.cliente_nombre?.split(' ').slice(1).join(' ')||''};
        seleccionarCliente(c);
    } else {
        limpiarClientePed();
    }

    document.getElementById('modalPedTitulo').innerHTML = p
        ? `<i class="fas fa-edit" style="color:var(--opt);margin-right:8px;"></i>Editar Pedido`
        : `<i class="fas fa-glasses" style="color:var(--opt);margin-right:8px;"></i>Nuevo Pedido`;
    calcTotalPed();
    document.getElementById('modalPedido').classList.add('open');
}
function cerrarModalPedido() {
    document.getElementById('modalPedido').classList.remove('open');
    document.getElementById('pedClienteLista').style.display = 'none';
}
function editarPedido(id) {
    const p = todosPedidos.find(x => x.id == id);
    if (p) abrirModalPedido(p);
}

// Búsqueda de cliente en modal
function buscarClientes(q) {
    const lista = document.getElementById('pedClienteLista');
    if (!q.trim()) { lista.style.display = 'none'; return; }
    const resultados = todosClientes.filter(c =>
        (c.nombre+' '+c.apellido).toLowerCase().includes(q.toLowerCase()) ||
        (c.dni||'').includes(q)
    ).slice(0, 8);
    if (!resultados.length) { lista.style.display = 'none'; return; }
    lista.innerHTML = resultados.map(c =>
        `<div class="cli-select-item" onclick="seleccionarCliente({id:${c.id},nombre:'${esc(c.nombre)}',apellido:'${esc(c.apellido)}'})">
            <strong>${esc(c.apellido)}, ${esc(c.nombre)}</strong>
            ${c.dni ? `<span style="color:var(--text-secondary);font-size:11px;margin-left:6px;">DNI: ${esc(c.dni)}</span>` : ''}
        </div>`
    ).join('');
    lista.style.display = 'block';
}
function seleccionarCliente(c) {
    document.getElementById('pedClienteId').value = c.id;
    document.getElementById('pedClienteNombre').textContent = `${c.apellido}, ${c.nombre}`;
    document.getElementById('pedClienteSeleccionado').style.display = 'flex';
    document.getElementById('pedClienteBuscar').style.display = 'none';
    document.getElementById('pedClienteLista').style.display = 'none';
}
function limpiarClientePed() {
    document.getElementById('pedClienteId').value = '';
    document.getElementById('pedClienteBuscar').value = '';
    document.getElementById('pedClienteBuscar').style.display = 'block';
    document.getElementById('pedClienteSeleccionado').style.display = 'none';
    document.getElementById('pedClienteLista').style.display = 'none';
}

function calcTotalPed() {
    const arm  = parseFloat(document.getElementById('pedArmazonPrecio')?.value)||0;
    const len  = parseFloat(document.getElementById('pedLentePrecio')?.value)||0;
    const desc = parseFloat(document.getElementById('pedDescuento')?.value)||0;
    const total = arm + len - desc;
    document.getElementById('totArmazon').textContent   = fmt(arm);
    document.getElementById('totLentes').textContent    = fmt(len);
    document.getElementById('totDescuento').textContent = '- '+fmt(desc);
    document.getElementById('totTotal').textContent     = fmt(Math.max(0, total));
}

async function guardarPedido() {
    const cliId = document.getElementById('pedClienteId').value;
    if (!cliId) { toast('Seleccioná un cliente', 'error'); return; }
    const arm  = parseFloat(document.getElementById('pedArmazonPrecio').value)||0;
    const len  = parseFloat(document.getElementById('pedLentePrecio').value)||0;
    const desc = parseFloat(document.getElementById('pedDescuento').value)||0;
    const body = {
        cliente_id:        parseInt(cliId),
        armazon:           document.getElementById('pedArmazon').value.trim()||null,
        armazon_color:     document.getElementById('pedArmazonColor').value.trim()||null,
        armazon_precio:    arm,
        lente_tipo:        document.getElementById('pedLenteTipo').value,
        lente_material:    document.getElementById('pedLenteMat').value.trim()||null,
        lente_tratamiento: document.getElementById('pedLenteTrat').value.trim()||null,
        lente_precio:      len,
        descuento:         desc,
        total:             Math.max(0, arm + len - desc),
        'seña':            parseFloat(document.getElementById('pedSena').value)||0,
        metodo_pago:       document.getElementById('pedMetodo').value,
        estado:            document.getElementById('pedEstado').value,
        laboratorio:       document.getElementById('pedLab').value.trim()||null,
        fecha_entrega_est: document.getElementById('pedFechaEst').value||null,
        observaciones:     document.getElementById('pedObs').value.trim()||null,
    };
    const id     = document.getElementById('pedId').value;
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_PED}?id=${id}` : API_PED;
    const r = await fetch(url, {method, credentials:'include', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalPedido(); toast(id?'Pedido actualizado ✓':'Pedido creado ✓'); init(); }
    else toast(j.message||'Error al guardar','error');
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoInfo(e) {
    const m = {
        presupuesto:{label:'Presupuesto'}, pendiente:{label:'Pendiente'},
        laboratorio:{label:'Laboratorio'}, listo:{label:'Listo ✓'},
        entregado:{label:'Entregado'},     cancelado:{label:'Cancelado'},
    };
    return m[e] || {label:e};
}
function lenteTipoLabel(t) {
    return {monofocal:'Monofocal',bifocal:'Bifocal',progresivo:'Progresivo',solar:'Solar',contacto:'Contacto',sin_lente:'Sin lente'}[t]||t||'—';
}
function fmt(n)      { return '$' + Number(n||0).toLocaleString('es-AR', {minimumFractionDigits:0}); }
function esc(s)      { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatFecha(f) { if (!f) return ''; const [y,m,d] = f.split('-'); return `${d}/${m}/${y}`; }
function toast(msg, tipo='ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'error' ? '#ef4444' : '#1e293b';
    t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 2500);
}

// ── WA al cliente ──────────────────────────────────────────────────────────────
function waCliente(tel, nombre, armazon) {
    const num = tel.replace(/\D/g, '');
    const armTxt = armazon ? `, *${armazon}*` : '';
    const msg = `Hola ${nombre}! 👓 Te avisamos que tu pedido óptico${armTxt} ya está listo para retirar. Podés pasar cuando quieras. ¡Hasta pronto!`;
    window.open(`https://wa.me/${num}?text=${encodeURIComponent(msg)}`, '_blank');
}

// ── Imprimir folio ─────────────────────────────────────────────────────────────
async function imprimirFolio(id) {
    const r = await fetch(`${API_PED}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { toast('Error al cargar pedido', 'error'); return; }
    const p = j.data;
    const saldo = parseFloat(p.saldo||0);
    const fechaHoy = new Date().toLocaleDateString('es-AR',{day:'2-digit',month:'2-digit',year:'numeric'});
    const estadoColors = {listo:'#0FD186',laboratorio:'#0ea5e9',pendiente:'#f59e0b',presupuesto:'#94a3b8',entregado:'#6366f1',cancelado:'#ef4444'};
    const w = window.open('','_blank');
    w.document.write(`<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <title>Pedido #${p.id}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',sans-serif; color:#1e293b; padding:32px; max-width:580px; margin:0 auto; }
        .header { text-align:center; margin-bottom:24px; padding-bottom:16px; border-bottom:2px solid #0ea5e9; }
        .header h1 { font-size:22px; font-weight:800; color:#0ea5e9; }
        .header p  { font-size:12px; color:#64748b; margin-top:4px; }
        .pedido-num { display:inline-block; background:#0ea5e9; color:#fff; font-weight:800; font-size:13px; padding:4px 12px; border-radius:20px; margin-top:8px; }
        h3 { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#64748b; margin:20px 0 8px; padding-bottom:4px; border-bottom:1px solid #e2e8f0; }
        .row { display:flex; justify-content:space-between; padding:6px 0; font-size:13px; }
        .row .label { color:#64748b; }
        .row .value { font-weight:600; }
        .total-box { background:#f8fafc; border-radius:10px; padding:14px; margin-top:16px; }
        .total-box .row { border-bottom:1px solid #e2e8f0; }
        .total-box .row:last-child { border-bottom:none; font-size:16px; font-weight:800; color:#0ea5e9; }
        .saldo-box { background:#fef2f2; border:1.5px solid #ef4444; border-radius:10px; padding:12px 14px; margin-top:10px; text-align:center; }
        .saldo-box p { font-weight:700; color:#dc2626; font-size:15px; }
        .pagado-box { background:#f0fdf4; border:1.5px solid #22c55e; border-radius:10px; padding:10px 14px; margin-top:10px; text-align:center; font-weight:700; color:#166534; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; color:#fff; }
        .footer { margin-top:28px; text-align:center; font-size:11px; color:#94a3b8; }
        @media print { button { display:none; } }
    </style></head><body>
    <div class="header">
        <h1>👓 Óptica</h1>
        <p>Comprobante de Pedido</p>
        <span class="pedido-num">Pedido #${p.id}</span>
    </div>

    <h3>Cliente</h3>
    <div class="row"><span class="label">Nombre</span><span class="value">${fe(p.cliente_nombre)}</span></div>
    ${p.obra_social ? `<div class="row"><span class="label">Obra social</span><span class="value">${fe(p.obra_social)}</span></div>` : ''}
    <div class="row"><span class="label">Fecha del folio</span><span class="value">${fechaHoy}</span></div>
    <div class="row"><span class="label">Estado</span><span class="value"><span class="badge" style="background:${estadoColors[p.estado]||'#94a3b8'}">${estadoInfo(p.estado).label}</span></span></div>

    ${p.armazon || p.lente_tipo ? `
    <h3>Especificaciones</h3>
    ${p.armazon ? `<div class="row"><span class="label">Armazón</span><span class="value">${fe(p.armazon)}${p.armazon_color?' — '+fe(p.armazon_color):''}</span></div>` : ''}
    <div class="row"><span class="label">Tipo de lente</span><span class="value">${lenteTipoLabel(p.lente_tipo)}</span></div>
    ${p.lente_material ? `<div class="row"><span class="label">Material</span><span class="value">${fe(p.lente_material)}</span></div>` : ''}
    ${p.lente_tratamiento ? `<div class="row"><span class="label">Tratamiento</span><span class="value">${fe(p.lente_tratamiento)}</span></div>` : ''}
    ` : ''}

    ${p.laboratorio ? `
    <h3>Laboratorio</h3>
    <div class="row"><span class="label">Laboratorio</span><span class="value">${fe(p.laboratorio)}</span></div>
    ${p.fecha_entrega_est ? `<div class="row"><span class="label">Entrega estimada</span><span class="value">${formatFecha(p.fecha_entrega_est)}</span></div>` : ''}
    ` : ''}

    <h3>Pago</h3>
    <div class="total-box">
        <div class="row"><span class="label">Armazón</span><span class="value">${fmt(p.armazon_precio)}</span></div>
        <div class="row"><span class="label">Lentes</span><span class="value">${fmt(p.lente_precio)}</span></div>
        ${parseFloat(p.descuento||0)>0?`<div class="row"><span class="label">Descuento</span><span class="value" style="color:#ef4444;">- ${fmt(p.descuento)}</span></div>`:''}
        <div class="row"><span class="label">Total</span><span class="value">${fmt(p.total)}</span></div>
        ${parseFloat(p.seña||0)>0?`<div class="row"><span class="label">Seña abonada</span><span class="value">${fmt(p.seña)}</span></div>`:''}
    </div>
    ${saldo > 0
        ? `<div class="saldo-box"><p>Saldo pendiente: ${fmt(saldo)}</p></div>`
        : `<div class="pagado-box">✓ Pagado en su totalidad</div>`}

    ${p.observaciones ? `<h3>Observaciones</h3><p style="font-size:13px;padding:10px;background:#f8fafc;border-radius:8px;">${fe(p.observaciones)}</p>` : ''}

    <div class="footer"><p>Gracias por su confianza · ${fechaHoy}</p></div>
    <div style="margin-top:24px;text-align:center;">
        <button onclick="window.print()" style="background:#0ea5e9;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">🖨️ Imprimir</button>
    </div>
    </body></html>`);
    w.document.close();
}
function fe(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

['modalPedido','modalCobrar'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { cerrarModalPedido(); cerrarModalCobrar(); }
});
document.addEventListener('click', e => {
    if (!e.target.closest('#fgClienteSelect')) {
        document.getElementById('pedClienteLista').style.display = 'none';
    }
});

init();
</script>
</body>
</html>
