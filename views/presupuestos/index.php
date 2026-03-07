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
    <title>Presupuestos — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        /* ── Stats cards ── */
        .pres-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .pres-stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 16px;
            display: flex; flex-direction: column; gap: 6px;
        }
        .pres-stat-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; margin-bottom: 4px;
        }
        .pres-stat-n { font-size: 26px; font-weight: 900; color: var(--text-primary); line-height: 1; }
        .pres-stat-l { font-size: 12px; color: var(--text-secondary); font-weight: 600; }

        /* ── Barra de herramientas ── */
        .pres-toolbar {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .pres-search {
            flex: 1; min-width: 200px;
            position: relative;
        }
        .pres-search input {
            width: 100%; padding: 10px 14px 10px 38px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; background: var(--surface); color: var(--text-primary);
            outline: none;
        }
        .pres-search input:focus { border-color: var(--primary); }
        .pres-search > i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }

        /* ── Filtros estado ── */
        .estado-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
        .estado-tab {
            padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
            cursor: pointer; border: 1.5px solid var(--border);
            background: var(--surface); color: var(--text-secondary);
            transition: all .15s; white-space: nowrap;
        }
        .estado-tab.active, .estado-tab:hover {
            border-color: var(--primary); color: var(--primary); background: var(--primary-light);
        }

        /* ── Tabla ── */
        .pres-table-wrap {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); overflow: hidden;
        }
        table.pres-table { width: 100%; border-collapse: collapse; }
        .pres-table thead th {
            background: var(--background); padding: 12px 16px;
            text-align: left; font-size: 11px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .5px; color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
        }
        .pres-table tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
        .pres-table tbody tr:last-child { border-bottom: none; }
        .pres-table tbody tr:hover { background: var(--background); }
        .pres-table td { padding: 13px 16px; font-size: 14px; vertical-align: middle; }

        .pres-num { font-weight: 800; color: var(--primary); }
        .pres-cliente { font-weight: 600; }
        .pres-tel { font-size: 12px; color: var(--text-secondary); }
        .pres-total { font-weight: 800; font-size: 15px; }
        .pres-vence { font-size: 12px; color: var(--text-secondary); }
        .pres-vence.vencido { color: var(--error); }

        /* Badges de estado */
        .badge-estado {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 800; white-space: nowrap;
        }
        .badge-borrador  { background: #f1f5f9; color: #64748b; }
        .badge-enviado   { background: #eff6ff; color: #3b82f6; }
        .badge-aprobado  { background: #f0fdf4; color: #16a34a; }
        .badge-rechazado { background: #fef2f2; color: #dc2626; }
        .badge-vencido   { background: #fff7ed; color: #ea580c; }

        .pres-actions { display: flex; gap: 6px; }
        .pres-btn {
            width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--border);
            background: var(--surface); cursor: pointer; font-size: 13px;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s; color: var(--text-secondary);
        }
        .pres-btn:hover { border-color: var(--primary); color: var(--primary); }
        .pres-btn.danger:hover { border-color: var(--error); color: var(--error); }
        .pres-btn.success:hover { border-color: var(--success); color: var(--success); }

        .pres-empty {
            text-align: center; padding: 60px 20px;
            color: var(--text-secondary);
        }
        .pres-empty i { font-size: 48px; opacity: .25; display: block; margin-bottom: 14px; }

        /* ══════════════════════════
           MODAL NUEVO PRESUPUESTO
        ══════════════════════════ */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1000;
            align-items: flex-start; justify-content: center;
            padding: 20px; overflow-y: auto;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: var(--surface); border-radius: 20px;
            width: 100%; max-width: 760px;
            margin: auto;
            animation: slideUp .25s ease;
            overflow: visible;
        }
        @keyframes slideUp { from { transform:translateY(30px); opacity:0; } to { transform:translateY(0); opacity:1; } }

        .modal-head {
            padding: 22px 28px;
            background: linear-gradient(135deg, #0FD186, #0AB871);
            color: white;
            display: flex; align-items: center; justify-content: space-between;
            border-radius: 20px 20px 0 0;
        }
        .modal-head h3 { font-size: 17px; font-weight: 800; }
        .modal-close {
            background: rgba(255,255,255,.2); border: none; color: white;
            width: 34px; height: 34px; border-radius: 50%;
            cursor: pointer; font-size: 16px;
            display: flex; align-items: center; justify-content: center;
        }

        .modal-body { padding: 24px 28px; display: flex; flex-direction: column; gap: 18px; overflow-y: auto; max-height: 72vh; }

        /* Grid 2 columnas */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-grid.triple { grid-template-columns: 1fr 1fr 1fr; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 700;
            color: var(--text-secondary); text-transform: uppercase;
            letter-spacing: .4px; margin-bottom: 6px;
        }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; outline: none; background: var(--background);
            color: var(--text-primary); font-family: inherit;
        }
        .form-control:focus { border-color: var(--primary); background: var(--surface); }

        /* Buscador de productos */
        .prod-search-wrap { position: relative; }
        .prod-dropdown {
            display: none; position: absolute; width: 100%; top: 48px;
            background: var(--surface); border: 1.5px solid var(--border);
            border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,.22);
            z-index: 9999; max-height: 300px; overflow-y: auto;
        }
        .prod-dropdown.show { display: block; }
        .prod-opt {
            padding: 10px 14px; cursor: pointer;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid var(--border);
            transition: background .12s;
        }
        .prod-opt:last-child { border-bottom: none; }
        .prod-opt:hover { background: var(--primary-light); }
        .prod-opt-avatar {
            width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 900; color: #fff;
            overflow: hidden;
        }
        .prod-opt-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
        .prod-opt-info { flex: 1; min-width: 0; }
        .prod-opt-nombre { font-size: 13px; font-weight: 700; color: var(--text-primary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .prod-opt-cat { font-size: 11px; color: var(--text-secondary); margin-top: 2px; }
        .prod-opt-precio { font-size: 14px; font-weight: 800; color: var(--primary); white-space: nowrap; }
        .prod-no-results { padding: 16px; text-align: center; color: var(--text-secondary); font-size: 13px; }

        /* Tabla de items */
        .items-section { border: 1.5px solid var(--border); border-radius: 12px; overflow: hidden; }
        .items-head {
            background: var(--background); padding: 10px 14px;
            display: grid; grid-template-columns: 2fr 80px 120px 70px 90px 32px;
            gap: 8px; font-size: 11px; font-weight: 800;
            text-transform: uppercase; color: var(--text-secondary);
            letter-spacing: .4px;
        }
        .item-row {
            display: grid; grid-template-columns: 2fr 80px 120px 70px 90px 32px;
            gap: 8px; padding: 8px 14px; align-items: center;
            border-top: 1px solid var(--border);
        }
        .item-prod-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .item-avatar {
            width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 900; color: #fff;
            overflow: hidden;
        }
        .item-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
        .item-desc-wrap { flex: 1; min-width: 0; }
        .item-desc-name { font-size: 12px; font-weight: 700; color: var(--text-primary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .item-desc-input {
            width: 100%; padding: 5px 8px;
            border: 1.5px solid var(--border); border-radius: 7px;
            font-size: 12px; background: var(--background); outline: none;
            color: var(--text-primary);
        }
        .item-desc-input:focus { border-color: var(--primary); }
        .item-row input[type="number"] {
            width: 100%; padding: 6px 8px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; background: var(--background); outline: none;
            color: var(--text-primary); text-align: center;
        }
        .item-row input[type="number"]:focus { border-color: var(--primary); }
        .item-subtotal { font-size: 13px; font-weight: 800; color: var(--primary); text-align: right; white-space: nowrap; }
        .item-del {
            background: none; border: none; color: var(--error);
            cursor: pointer; font-size: 15px; text-align: center; padding: 4px;
            border-radius: 6px; transition: background .12s;
        }
        .item-del:hover { background: rgba(239,68,68,.1); }
        .items-add-row {
            padding: 10px 14px; border-top: 1px solid var(--border);
            display: flex; gap: 8px;
        }

        /* Totales del modal */
        .modal-totales {
            background: var(--background); border-radius: 12px;
            padding: 14px 18px; display: flex; flex-direction: column; gap: 8px;
        }
        .tot-row { display: flex; justify-content: space-between; font-size: 14px; }
        .tot-row.grand { font-size: 18px; font-weight: 800; color: var(--text-primary); padding-top: 8px; border-top: 1px solid var(--border); margin-top: 4px; }
        .tot-desc-input {
            padding: 4px 10px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 14px; width: 110px; text-align: right; background: var(--surface);
            outline: none;
        }
        .tot-desc-input:focus { border-color: var(--primary); }

        .modal-footer {
            padding: 16px 28px; border-top: 1px solid var(--border);
            background: var(--background);
            display: flex; gap: 10px; justify-content: flex-end;
            border-radius: 0 0 20px 20px;
        }

        /* ── Modal detalle/imprimir ── */
        .detail-box { padding: 24px 28px; }
        .detail-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 24px;
        }
        .detail-empresa { font-size: 20px; font-weight: 900; color: var(--text-primary); }
        .detail-num { font-size: 24px; font-weight: 900; color: var(--primary); }
        .detail-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .detail-field label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); }
        .detail-field p { font-size: 14px; font-weight: 600; margin-top: 2px; }
        .detail-items table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .detail-items th {
            background: var(--background); padding: 10px 12px; text-align: left;
            font-size: 11px; font-weight: 800; text-transform: uppercase;
            color: var(--text-secondary); border-bottom: 1px solid var(--border);
        }
        .detail-items td {
            padding: 10px 12px; border-bottom: 1px solid var(--border);
            font-size: 13px; vertical-align: middle;
        }
        .detail-items tr:last-child td { border-bottom: none; }

        @media print {
            .modal-head, .modal-footer, .pres-toolbar, .pres-stats,
            .estado-tabs, aside, .bottom-nav, .header, .fab-print { display: none !important; }
            .modal-overlay { position: static !important; background: none !important; }
            .modal-box { box-shadow: none !important; max-width: 100% !important; }
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .main-content { padding: 12px !important; }
            .pres-stats { grid-template-columns: repeat(2, 1fr); }
            .pres-toolbar { flex-direction: column; align-items: stretch; }
            .pres-table thead .hide-mobile { display: none; }
            .pres-table td.hide-mobile { display: none; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid.triple { grid-template-columns: 1fr 1fr; }
            .items-head { grid-template-columns: 1fr 60px 90px 55px 70px 32px; font-size: 10px; }
            .item-row   { grid-template-columns: 1fr 60px 90px 55px 70px 32px; }
            .modal-body { padding: 16px; }
            .modal-footer { padding: 12px 16px; }
            .detail-meta { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .items-head, .item-row { grid-template-columns: 1fr 50px 80px 44px 60px 28px; }
            .item-avatar { display: none; }
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content" style="flex:1; padding:24px; overflow-y:auto;">
        <?php include '../includes/header.php'; ?>

        <!-- Título -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <h2 style="font-size:22px;font-weight:800;color:var(--text-primary);">
                    <i class="fas fa-file-invoice-dollar" style="color:var(--primary);margin-right:8px;"></i>Presupuestos
                </h2>
                <p style="font-size:13px;color:var(--text-secondary);margin-top:2px;">Cotizaciones y presupuestos para clientes</p>
            </div>
            <button class="btn btn-primary" onclick="abrirNuevo()">
                <i class="fas fa-plus"></i> Nuevo presupuesto
            </button>
        </div>

        <!-- Stats -->
        <div class="pres-stats" id="presStats">
            <div class="pres-stat">
                <div class="pres-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="pres-stat-n" id="st_total">0</div>
                <div class="pres-stat-l">Total</div>
            </div>
            <div class="pres-stat">
                <div class="pres-stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-paper-plane"></i></div>
                <div class="pres-stat-n" id="st_enviados">0</div>
                <div class="pres-stat-l">Enviados</div>
            </div>
            <div class="pres-stat">
                <div class="pres-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-check-circle"></i></div>
                <div class="pres-stat-n" id="st_aprobados">0</div>
                <div class="pres-stat-l">Aprobados</div>
            </div>
            <div class="pres-stat">
                <div class="pres-stat-icon" style="background:#f0fdf4;color:var(--primary);"><i class="fas fa-dollar-sign"></i></div>
                <div class="pres-stat-n" id="st_monto">$0</div>
                <div class="pres-stat-l">Monto aprobado</div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="pres-toolbar">
            <div class="pres-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por número o cliente…" oninput="filtrar()">
            </div>
            <div class="estado-tabs" id="estadoTabs">
                <button class="estado-tab active" data-estado="" onclick="filtrarEstado('',this)">Todos</button>
                <button class="estado-tab" data-estado="borrador" onclick="filtrarEstado('borrador',this)">Borrador</button>
                <button class="estado-tab" data-estado="enviado" onclick="filtrarEstado('enviado',this)">Enviado</button>
                <button class="estado-tab" data-estado="aprobado" onclick="filtrarEstado('aprobado',this)">Aprobado</button>
                <button class="estado-tab" data-estado="rechazado" onclick="filtrarEstado('rechazado',this)">Rechazado</button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="pres-table-wrap">
            <table class="pres-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th class="hide-mobile">Fecha</th>
                        <th class="hide-mobile">Vence</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="presBody">
                    <tr><td colspan="7" class="pres-empty"><i class="fas fa-spinner fa-spin"></i><br>Cargando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ══════════════════ MODAL NUEVO/EDITAR ══════════════════ -->
<div class="modal-overlay" id="modalNuevo">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-file-invoice-dollar" style="margin-right:8px;"></i>Nuevo Presupuesto</h3>
            <button class="modal-close" onclick="cerrarModal('modalNuevo')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <!-- Cliente -->
            <div>
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;color:var(--text-secondary);letter-spacing:.4px;margin-bottom:10px;">Cliente</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre del cliente *</label>
                        <input type="text" id="f_cliente_nombre" class="form-control" placeholder="Nombre o empresa" list="clientesList" oninput="sugerirClientes(this.value)">
                        <datalist id="clientesList"></datalist>
                        <input type="hidden" id="f_cliente_id">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="f_cliente_tel" class="form-control" placeholder="Ej: 221 555-1234">
                    </div>
                </div>
            </div>

            <!-- Fechas y notas -->
            <div class="form-grid triple">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" id="f_fecha" class="form-control">
                </div>
                <div class="form-group">
                    <label>Válido hasta</label>
                    <input type="date" id="f_fecha_venc" class="form-control">
                </div>
                <div class="form-group" style="grid-column:span 1;">
                    <label>Notas internas</label>
                    <input type="text" id="f_notas" class="form-control" placeholder="Obra, condiciones, etc.">
                </div>
            </div>

            <!-- Buscador de productos -->
            <div>
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;color:var(--text-secondary);letter-spacing:.4px;margin-bottom:10px;">Ítems</div>
                <div class="prod-search-wrap">
                    <input type="text" id="prodSearch" class="form-control" placeholder="🔍 Buscar producto para agregar…" oninput="buscarProdModal(this.value)" autocomplete="off">
                    <div class="prod-dropdown" id="prodDropdown"></div>
                </div>
            </div>

            <!-- Tabla de ítems -->
            <div class="items-section">
                <div class="items-head">
                    <span>Producto / Descripción</span><span>Cant.</span><span>Precio unit.</span><span>Desc%</span><span>Subtotal</span><span></span>
                </div>
                <div id="itemsContainer"></div>
                <div class="items-add-row">
                    <button class="btn btn-outline btn-sm" onclick="agregarItemManual()" style="font-size:12px;">
                        <i class="fas fa-plus"></i> Agregar ítem manual
                    </button>
                </div>
            </div>

            <!-- Totales -->
            <div class="modal-totales">
                <div class="tot-row">
                    <span>Subtotal</span>
                    <strong id="tot_sub">$0</strong>
                </div>
                <div class="tot-row">
                    <span>Descuento global</span>
                    <span>$<input type="number" class="tot-desc-input" id="tot_desc" value="0" min="0" oninput="recalcTotal()"></span>
                </div>
                <div class="tot-row grand">
                    <span>TOTAL</span>
                    <strong id="tot_total">$0</strong>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="cerrarModal('modalNuevo')">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarPresupuesto()">
                <i class="fas fa-save"></i> Guardar presupuesto
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════ MODAL DETALLE ══════════════════ -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-file-invoice-dollar" style="margin-right:8px;"></i>Detalle del Presupuesto</h3>
            <button class="modal-close" onclick="cerrarModal('modalDetalle')"><i class="fas fa-times"></i></button>
        </div>
        <div class="detail-box" id="detalleContenido"></div>
        <div class="modal-footer" id="detalleFooter"></div>
    </div>
</div>

<script>
const BASE = window.APP_BASE;
let presupuestosData = [];
let productosData    = [];
let clientesData     = [];
let estadoFiltro     = '';
let itemsForm        = [];   // [{descripcion, cantidad, precio_unit, descuento_item, producto_id}]

/* ══════════════════════════════════════
   INIT
══════════════════════════════════════ */
async function init() {
    await Promise.all([cargarPresupuestos(), cargarProductos(), cargarClientes()]);
}

async function cargarPresupuestos() {
    const r = await fetch(`${BASE}/api/presupuestos/index.php`);
    const d = await r.json();
    if (!d.success) return;
    presupuestosData = d.data.presupuestos || [];
    renderStats(d.data.stats);
    renderTabla(presupuestosData);
}

async function cargarProductos() {
    const r = await fetch(`${BASE}/api/productos/index.php?activo=1&limit=500`);
    const d = await r.json();
    productosData = d.data?.productos || [];
}

async function cargarClientes() {
    const r = await fetch(`${BASE}/api/clientes/index.php`);
    const d = await r.json();
    clientesData = d.data?.clientes || d.data || [];
}

/* ══════════════════════════════════════
   STATS
══════════════════════════════════════ */
function renderStats(s) {
    if (!s) return;
    document.getElementById('st_total').textContent    = s.total || 0;
    document.getElementById('st_enviados').textContent = s.enviados || 0;
    document.getElementById('st_aprobados').textContent= s.aprobados || 0;
    document.getElementById('st_monto').textContent    = fmtMoney(s.monto_aprobado || 0);
}

/* ══════════════════════════════════════
   TABLA
══════════════════════════════════════ */
function renderTabla(data) {
    const tbody = document.getElementById('presBody');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="7"><div class="pres-empty">
            <i class="fas fa-file-invoice-dollar"></i>
            No hay presupuestos todavía.<br>
            <button class="btn btn-primary btn-sm" style="margin-top:14px;" onclick="abrirNuevo()"><i class="fas fa-plus"></i> Crear el primero</button>
        </div></td></tr>`;
        return;
    }

    tbody.innerHTML = data.map(p => {
        const hoy    = new Date().toISOString().split('T')[0];
        const vencida = p.fecha_vencimiento && p.fecha_vencimiento < hoy && p.estado !== 'aprobado';
        return `<tr>
            <td><span class="pres-num">${esc(p.numero)}</span></td>
            <td>
                <div class="pres-cliente">${esc(p.cliente_nombre || '—')}</div>
                ${p.cliente_tel ? `<div class="pres-tel"><i class="fas fa-phone" style="font-size:10px;"></i> ${esc(p.cliente_tel)}</div>` : ''}
            </td>
            <td class="hide-mobile" style="font-size:13px;">${fmtFecha(p.fecha)}</td>
            <td class="hide-mobile"><span class="pres-vence ${vencida?'vencido':''}">${p.fecha_vencimiento ? fmtFecha(p.fecha_vencimiento) : '—'}</span></td>
            <td><strong class="pres-total">${fmtMoney(p.total)}</strong></td>
            <td><span class="badge-estado badge-${p.estado}">${iconEstado(p.estado)} ${labelEstado(p.estado)}</span></td>
            <td>
                <div class="pres-actions">
                    <button class="pres-btn" onclick="verDetalle(${p.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                    <button class="pres-btn success" onclick="convertirVenta(${p.id},'${esc(p.numero)}')" title="Convertir a venta"><i class="fas fa-shopping-cart"></i></button>
                    <button class="pres-btn danger" onclick="eliminar(${p.id},'${esc(p.numero)}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

/* ══════════════════════════════════════
   FILTROS
══════════════════════════════════════ */
function filtrar() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    let data = presupuestosData;
    if (estadoFiltro) data = data.filter(p => p.estado === estadoFiltro);
    if (q) data = data.filter(p =>
        (p.numero||'').toLowerCase().includes(q) ||
        (p.cliente_nombre||'').toLowerCase().includes(q)
    );
    renderTabla(data);
}

function filtrarEstado(est, el) {
    estadoFiltro = est;
    document.querySelectorAll('.estado-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    filtrar();
}

/* ══════════════════════════════════════
   NUEVO PRESUPUESTO — FORMULARIO
══════════════════════════════════════ */
function abrirNuevo() {
    itemsForm = [];
    document.getElementById('f_cliente_nombre').value = '';
    document.getElementById('f_cliente_tel').value    = '';
    document.getElementById('f_cliente_id').value     = '';
    document.getElementById('f_notas').value          = '';
    document.getElementById('tot_desc').value         = '0';
    document.getElementById('prodSearch').value       = '';

    const hoy = new Date().toISOString().split('T')[0];
    const d15 = new Date(); d15.setDate(d15.getDate()+15);
    document.getElementById('f_fecha').value      = hoy;
    document.getElementById('f_fecha_venc').value = d15.toISOString().split('T')[0];

    renderItemsForm();
    document.getElementById('modalNuevo').classList.add('show');
    document.getElementById('f_cliente_nombre').focus();
}

function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }

/* Sugerir clientes con datalist */
function sugerirClientes(q) {
    const dl = document.getElementById('clientesList');
    const matches = clientesData.filter(c =>
        (c.nombre + ' ' + (c.apellido||'')).toLowerCase().includes(q.toLowerCase())
    ).slice(0, 8);
    dl.innerHTML = matches.map(c =>
        `<option value="${esc(c.nombre + ' ' + (c.apellido||''))}" data-id="${c.id}" data-tel="${c.telefono||''}">`
    ).join('');
    // Si hay match exacto
    const match = clientesData.find(c => (c.nombre + ' ' + (c.apellido||'')).trim() === q.trim());
    if (match) {
        document.getElementById('f_cliente_id').value = match.id;
        if (!document.getElementById('f_cliente_tel').value) {
            document.getElementById('f_cliente_tel').value = match.telefono || '';
        }
    }
}

/* Buscar producto */
let prodTimer = null;
function buscarProdModal(q) {
    clearTimeout(prodTimer);
    const drop = document.getElementById('prodDropdown');
    if (!q.trim()) { drop.classList.remove('show'); return; }
    prodTimer = setTimeout(() => {
        const res = productosData.filter(p =>
            p.nombre.toLowerCase().includes(q.toLowerCase()) ||
            (p.categoria_nombre||'').toLowerCase().includes(q.toLowerCase())
        ).slice(0, 12);

        if (!res.length) {
            drop.innerHTML = `<div class="prod-no-results"><i class="fas fa-search"></i> Sin resultados para "<em>${esc(q)}</em>"</div>`;
            drop.classList.add('show');
            return;
        }

        drop.innerHTML = res.map(p => {
            const color  = p.categoria_color || '#0FD186';
            const inicial = (p.nombre||'?')[0].toUpperCase();
            const avatarStyle = `background:${color};`;
            const avatarHtml  = p.foto
                ? `<img src="${window.APP_BASE}/public/uploads/productos/${esc(p.foto)}" alt="">`
                : inicial;

            return `<div class="prod-opt" onclick="agregarItemDesde(${p.id},${JSON.stringify(p.nombre)},${p.precio_venta},${JSON.stringify(p.foto||'')},${JSON.stringify(color)},${JSON.stringify(p.categoria_nombre||'')})">
                <div class="prod-opt-avatar" style="${avatarStyle}">${avatarHtml}</div>
                <div class="prod-opt-info">
                    <div class="prod-opt-nombre">${esc(p.nombre)}</div>
                    <div class="prod-opt-cat">${esc(p.categoria_nombre||'Sin categoría')} · Stock: ${p.stock||0}</div>
                </div>
                <div class="prod-opt-precio">${fmtMoney(p.precio_venta)}</div>
            </div>`;
        }).join('');
        drop.classList.add('show');
    }, 120);
}

function agregarItemDesde(prodId, nombre, precio, foto, color, catNombre) {
    itemsForm.push({ producto_id: prodId, descripcion: nombre, cantidad: 1, precio_unit: precio, descuento_item: 0, _foto: foto, _color: color, _cat: catNombre });
    document.getElementById('prodSearch').value = '';
    document.getElementById('prodDropdown').classList.remove('show');
    renderItemsForm();
}

function agregarItemManual() {
    itemsForm.push({ producto_id: null, descripcion: '', cantidad: 1, precio_unit: 0, descuento_item: 0, _foto: '', _color: '#64748b', _cat: '' });
    renderItemsForm();
    setTimeout(() => {
        const inputs = document.querySelectorAll('#itemsContainer .item-desc-input');
        if (inputs.length) inputs[inputs.length-1].focus();
    }, 50);
}

function renderItemsForm() {
    const cont = document.getElementById('itemsContainer');
    if (!itemsForm.length) {
        cont.innerHTML = `<div style="padding:24px;text-align:center;color:var(--text-secondary);font-size:13px;">
            <i class="fas fa-search" style="margin-right:6px;opacity:.5;"></i>Buscá un producto o agregá un ítem manual
        </div>`;
        recalcTotal();
        return;
    }
    cont.innerHTML = itemsForm.map((it, i) => {
        const color   = it._color || '#64748b';
        const inicial = (it.descripcion||'?')[0].toUpperCase();
        const avatarHtml = it._foto
            ? `<img src="${window.APP_BASE}/public/uploads/productos/${esc(it._foto)}" alt="">`
            : inicial;

        return `<div class="item-row" id="item_row_${i}">
            <div class="item-prod-cell">
                <div class="item-avatar" style="background:${color};">${avatarHtml}</div>
                <div class="item-desc-wrap">
                    <input type="text" class="item-desc-input" value="${esc(it.descripcion)}" placeholder="Descripción"
                        data-i="${i}" oninput="updateItem(${i},'descripcion',this.value)">
                    ${it._cat ? `<div style="font-size:10px;color:var(--text-secondary);margin-top:2px;">${esc(it._cat)}</div>` : ''}
                </div>
            </div>
            <input type="number" value="${it.cantidad}" min="0.01" step="any" data-i="${i}" oninput="updateItem(${i},'cantidad',parseFloat(this.value)||1)">
            <input type="number" value="${it.precio_unit}" min="0" step="any" data-i="${i}" oninput="updateItem(${i},'precio_unit',parseFloat(this.value)||0)">
            <input type="number" value="${it.descuento_item}" min="0" max="100" step="any" placeholder="0" data-i="${i}" oninput="updateItem(${i},'descuento_item',parseFloat(this.value)||0)">
            <span class="item-subtotal" id="isub_${i}">${fmtMoney(calcSubItem(it))}</span>
            <button class="item-del" onclick="quitarItem(${i})" title="Quitar"><i class="fas fa-times"></i></button>
        </div>`;
    }).join('');
    recalcTotal();
}

function updateItem(i, campo, val) {
    itemsForm[i][campo] = val;
    const el = document.getElementById(`isub_${i}`);
    if (el) el.textContent = fmtMoney(calcSubItem(itemsForm[i]));
    recalcTotal();
}

function quitarItem(i) {
    itemsForm.splice(i, 1);
    renderItemsForm();
}

function calcSubItem(it) {
    return it.precio_unit * it.cantidad * (1 - (it.descuento_item || 0) / 100);
}

function recalcTotal() {
    const sub  = itemsForm.reduce((acc, it) => acc + calcSubItem(it), 0);
    const desc = parseFloat(document.getElementById('tot_desc').value) || 0;
    document.getElementById('tot_sub').textContent   = fmtMoney(sub);
    document.getElementById('tot_total').textContent = fmtMoney(sub - desc);
}

/* ══════════════════════════════════════
   GUARDAR
══════════════════════════════════════ */
async function guardarPresupuesto() {
    const clienteNombre = document.getElementById('f_cliente_nombre').value.trim();
    const clienteId     = document.getElementById('f_cliente_id').value;

    if (!clienteNombre) { showToast('Ingresá el nombre del cliente', 'error'); return; }
    if (!itemsForm.length) { showToast('Agregá al menos un ítem', 'error'); return; }
    if (itemsForm.some(it => !it.descripcion.trim())) { showToast('Completá todas las descripciones', 'error'); return; }

    const btn = document.querySelector('#modalNuevo .btn-primary');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

    const body = {
        cliente_nombre: clienteNombre,
        cliente_id: clienteId || null,
        cliente_tel: document.getElementById('f_cliente_tel').value.trim(),
        fecha: document.getElementById('f_fecha').value,
        fecha_vencimiento: document.getElementById('f_fecha_venc').value,
        notas: document.getElementById('f_notas').value.trim(),
        descuento: parseFloat(document.getElementById('tot_desc').value) || 0,
        items: itemsForm.map(it => ({...it, descripcion: it.descripcion.trim()}))
    };

    try {
        const r = await fetch(`${BASE}/api/presupuestos/index.php`, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(body)
        });
        const d = await r.json();
        if (d.success) {
            cerrarModal('modalNuevo');
            showToast(`Presupuesto ${d.data.numero} creado ✓`, 'success');
            await cargarPresupuestos();
        } else {
            showToast(d.message || 'Error al guardar', 'error');
        }
    } catch(e) { showToast('Error de conexión', 'error'); }

    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar presupuesto';
}

/* ══════════════════════════════════════
   VER DETALLE
══════════════════════════════════════ */
async function verDetalle(id) {
    const r = await fetch(`${BASE}/api/presupuestos/index.php?id=${id}`);
    const d = await r.json();
    if (!d.success) return;
    const p = d.data;

    const items = (p.items || []).map(it => `
        <tr>
            <td>${esc(it.descripcion)}</td>
            <td style="text-align:center;">${it.cantidad}</td>
            <td style="text-align:right;">${fmtMoney(it.precio_unit)}</td>
            <td style="text-align:center;">${it.descuento_item > 0 ? it.descuento_item+'%' : '—'}</td>
            <td style="text-align:right;font-weight:700;">${fmtMoney(it.subtotal)}</td>
        </tr>
    `).join('');

    document.getElementById('detalleContenido').innerHTML = `
        <div class="detail-header">
            <div>
                <div class="detail-empresa"><i class="fas fa-tools" style="color:var(--primary);margin-right:8px;"></i>Ferretería</div>
                <div style="color:var(--text-secondary);font-size:13px;margin-top:3px;">Presupuesto de materiales y servicios</div>
            </div>
            <div style="text-align:right;">
                <div class="detail-num">${esc(p.numero)}</div>
                <span class="badge-estado badge-${p.estado}">${iconEstado(p.estado)} ${labelEstado(p.estado)}</span>
            </div>
        </div>

        <div class="detail-meta">
            <div class="detail-field"><label>Cliente</label><p>${esc(p.cliente_nombre || '—')}</p></div>
            <div class="detail-field"><label>Teléfono</label><p>${esc(p.cliente_tel || '—')}</p></div>
            <div class="detail-field"><label>Fecha emisión</label><p>${fmtFecha(p.fecha)}</p></div>
            <div class="detail-field"><label>Válido hasta</label><p>${p.fecha_vencimiento ? fmtFecha(p.fecha_vencimiento) : '—'}</p></div>
            ${p.notas ? `<div class="detail-field" style="grid-column:span 2;"><label>Notas</label><p>${esc(p.notas)}</p></div>` : ''}
        </div>

        <div class="detail-items">
            <table>
                <thead><tr><th>Descripción</th><th style="text-align:center;">Cant.</th><th style="text-align:right;">Precio unit.</th><th style="text-align:center;">Dto.</th><th style="text-align:right;">Subtotal</th></tr></thead>
                <tbody>${items}</tbody>
            </table>
            <div style="display:flex;justify-content:flex-end;">
                <div style="min-width:240px;display:flex;flex-direction:column;gap:6px;">
                    <div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;">
                        <span>Subtotal</span><span>${fmtMoney(p.subtotal)}</span>
                    </div>
                    ${parseFloat(p.descuento) > 0 ? `<div style="display:flex;justify-content:space-between;font-size:14px;padding:6px 0;color:var(--error);">
                        <span>Descuento</span><span>−${fmtMoney(p.descuento)}</span>
                    </div>` : ''}
                    <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:900;padding:10px 0;border-top:2px solid var(--border);margin-top:4px;">
                        <span>TOTAL</span><span style="color:var(--primary);">${fmtMoney(p.total)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Footer con acciones
    document.getElementById('detalleFooter').innerHTML = `
        <button class="btn btn-outline" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
        <button class="btn btn-outline" onclick="cambiarEstado(${p.id},'enviado')" ${p.estado==='aprobado'?'disabled':''}><i class="fas fa-paper-plane"></i> Marcar enviado</button>
        <button class="btn btn-outline" style="border-color:var(--success);color:var(--success);" onclick="convertirVenta(${p.id},'${esc(p.numero)}')"><i class="fas fa-shopping-cart"></i> Convertir a venta</button>
        <button class="btn btn-primary" onclick="cerrarModal('modalDetalle')">Cerrar</button>
    `;

    document.getElementById('modalDetalle').classList.add('show');
}

/* ══════════════════════════════════════
   ACCIONES
══════════════════════════════════════ */
async function cambiarEstado(id, estado) {
    await fetch(`${BASE}/api/presupuestos/index.php`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id, estado})
    });
    showToast('Estado actualizado', 'success');
    cerrarModal('modalDetalle');
    await cargarPresupuestos();
}

async function convertirVenta(id, numero) {
    if (!confirm(`¿Convertir presupuesto ${numero} en venta?`)) return;
    const r = await fetch(`${BASE}/api/presupuestos/index.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({action:'convertir_venta', presupuesto_id: id})
    });
    const d = await r.json();
    if (d.success) {
        showToast(`Venta #${d.data.venta_id} creada ✓`, 'success');
        cerrarModal('modalDetalle');
        await cargarPresupuestos();
    } else {
        showToast(d.message, 'error');
    }
}

async function eliminar(id, numero) {
    if (!confirm(`¿Eliminar presupuesto ${numero}?`)) return;
    await fetch(`${BASE}/api/presupuestos/index.php?id=${id}`, {method:'DELETE'});
    showToast('Eliminado', 'success');
    await cargarPresupuestos();
}

/* ══════════════════════════════════════
   UTILS
══════════════════════════════════════ */
document.addEventListener('click', e => {
    if (!e.target.closest('.prod-search-wrap')) {
        document.getElementById('prodDropdown').classList.remove('show');
    }
});

function iconEstado(e) {
    const map = {borrador:'📝', enviado:'📤', aprobado:'✅', rechazado:'❌', vencido:'⏰'};
    return map[e] || '';
}
function labelEstado(e) {
    const map = {borrador:'Borrador', enviado:'Enviado', aprobado:'Aprobado', rechazado:'Rechazado', vencido:'Vencido'};
    return map[e] || e;
}
function fmtMoney(n) {
    return new Intl.NumberFormat('es-AR', {style:'currency', currency:'ARS'}).format(n||0);
}
function fmtFecha(f) {
    if (!f) return '—';
    const [y,m,d] = f.split('-');
    return `${d}/${m}/${y}`;
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
        background:${type==='success'?'#0FD186':'#F56565'};color:white;
        padding:12px 24px;border-radius:10px;font-weight:600;font-size:14px;
        z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2);`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

/* Cerrar dropdown al click fuera */
document.addEventListener('click', e => {
    if (!e.target.closest('.prod-search-wrap')) {
        document.getElementById('prodDropdown').classList.remove('show');
    }
});

init();
</script>
</body>
</html>
