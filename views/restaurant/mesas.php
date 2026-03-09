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
    <title>Mesas — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root {
            --libre:     #0FD186;
            --ocupada:   #F56565;
            --reservada: #F6AD55;
            --inactiva:  #A0AEC0;
        }

        .restaurant-layout { display: flex; min-height: 100vh; }

        /* ── Toolbar ── */
        .salon-header {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            margin-bottom: 20px;
        }
        .salon-title { font-size: 20px; font-weight: 800; color: var(--text-primary); }

        /* ── Tabs sectores ── */
        .sector-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
        .sector-tab {
            padding: 7px 16px; border-radius: 20px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: 2px solid var(--border);
            background: var(--surface); color: var(--text-secondary);
            transition: var(--transition);
        }
        .sector-tab.active, .sector-tab:hover {
            border-color: var(--primary); color: var(--primary);
            background: var(--primary-light);
        }

        /* ── Leyenda ── */
        .leyenda { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
        .leyenda-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-secondary); }
        .leyenda-dot { width: 12px; height: 12px; border-radius: 4px; }

        /* ── Grid de mesas ── */
        .mesas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 14px;
        }

        /* ── Tarjeta de mesa ── */
        .mesa-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 2px solid var(--border);
            padding: 16px;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            text-align: center;
            user-select: none;
        }
        .mesa-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

        .mesa-card.libre     { border-color: var(--libre);     }
        .mesa-card.ocupada   { border-color: var(--ocupada);   background: #FFF5F5; }
        .mesa-card.reservada { border-color: var(--reservada); background: #FFFBEB; }
        .mesa-card.inactiva  { border-color: var(--inactiva);  opacity: .5; pointer-events: none; }

        .mesa-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin: 0 auto 10px;
        }
        .libre     .mesa-icon { background: #E0F9F4; color: var(--libre); }
        .ocupada   .mesa-icon { background: #FED7D7; color: var(--ocupada); }
        .reservada .mesa-icon { background: #FEEBC8; color: var(--reservada); }

        .mesa-numero { font-size: 18px; font-weight: 800; color: var(--text-primary); line-height: 1; }
        .mesa-cap    { font-size: 11px; color: var(--text-secondary); margin-top: 3px; }
        .mesa-badge  {
            position: absolute; top: -8px; right: -8px;
            background: var(--ocupada); color: white;
            border-radius: 10px; padding: 2px 8px;
            font-size: 10px; font-weight: 700;
        }
        .mesa-badge.lista { background: var(--libre); }

        .mesa-comanda {
            margin-top: 8px;
            background: rgba(0,0,0,.04);
            border-radius: 8px;
            padding: 6px;
            font-size: 11px;
            color: var(--text-secondary);
        }
        .mesa-comanda strong { color: var(--text-primary); font-size: 12px; }

        /* ── Panel lateral (comanda) ── */
        .comanda-panel {
            position: fixed; top: 0; right: -440px; height: 100vh; width: 420px;
            background: var(--surface);
            box-shadow: -4px 0 30px rgba(0,0,0,.12);
            transition: right .3s cubic-bezier(0.4,0,0.2,1);
            z-index: 1000;
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .comanda-panel.open { right: 0; }

        .cp-header {
            padding: 20px;
            background: linear-gradient(135deg, #0FD186, #0AB871);
            color: white;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .cp-header h3 { font-size: 17px; font-weight: 800; }
        .cp-header p  { font-size: 12px; opacity: .85; margin-top: 2px; }
        .cp-close {
            background: rgba(255,255,255,.2); border: none; color: white;
            width: 34px; height: 34px; border-radius: 50%;
            cursor: pointer; font-size: 16px;
            display: flex; align-items: center; justify-content: center;
        }

        .cp-body { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; }

        /* Buscar producto */
        .producto-search { position: relative; }
        .producto-search input {
            width: 100%; padding: 10px 14px 10px 38px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; outline: none; background: var(--background);
            color: var(--text-primary);
        }
        .producto-search input:focus { border-color: var(--primary); }
        .producto-search > i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }

        /* Categorías rápidas */
        .cat-chips {
            display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 6px;
        }
        .cat-chip {
            padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
            cursor: pointer; border: 1.5px solid var(--border);
            color: var(--text-secondary); background: var(--background);
            transition: all .15s; white-space: nowrap;
        }
        .cat-chip.active, .cat-chip:hover {
            border-color: var(--primary); color: var(--primary); background: var(--primary-light);
        }

        .productos-lista {
            display: none; position: absolute; width: 100%;
            background: var(--surface); border: 1.5px solid var(--border);
            border-radius: 10px; box-shadow: var(--shadow-md);
            max-height: 240px; overflow-y: auto; z-index: 10; top: 44px;
        }
        .productos-lista.show { display: block; }
        .prod-item {
            padding: 9px 14px; cursor: pointer; font-size: 13px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border);
        }
        .prod-item:hover { background: var(--primary-light); }
        .prod-item:last-child { border-bottom: none; }
        .prod-item-cat { font-size: 10px; color: var(--text-secondary); margin-top: 1px; }

        /* Items de la comanda */
        .comanda-items { display: flex; flex-direction: column; gap: 6px; }
        .ci-row {
            display: flex; align-items: center; gap: 10px;
            background: var(--background); border-radius: 10px; padding: 10px 12px;
            font-size: 13px;
        }
        .ci-nombre { flex: 1; font-weight: 600; color: var(--text-primary); }
        .ci-obs    { font-size: 11px; color: var(--text-secondary); }
        .ci-cant   {
            display: flex; align-items: center; gap: 6px;
        }
        .ci-cant button {
            width: 24px; height: 24px; border-radius: 6px; border: 1.5px solid var(--border);
            background: white; cursor: pointer; font-size: 14px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .ci-precio { font-weight: 700; color: var(--primary); min-width: 70px; text-align: right; }
        .ci-del {
            background: none; border: none; color: var(--error);
            cursor: pointer; font-size: 13px; padding: 4px;
        }
        .ci-estado {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
        }
        .ci-estado.pendiente     { background: var(--warning); }
        .ci-estado.en_preparacion{ background: var(--info); }
        .ci-estado.listo         { background: var(--success); }
        .ci-estado.entregado     { background: var(--text-secondary); }

        /* Total comanda */
        .cp-total {
            flex-shrink: 0; padding: 16px;
            border-top: 1px solid var(--border);
            background: var(--background);
        }
        .total-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; }
        .total-row.grand { font-size: 18px; font-weight: 800; color: var(--text-primary); }

        .cp-actions { display: flex; gap: 8px; margin-top: 12px; }
        .cp-btn {
            flex: 1; padding: 11px; border: none; border-radius: 10px;
            font-size: 13px; font-weight: 700; cursor: pointer; transition: var(--transition);
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .cp-btn-cocina { background: var(--warning); color: white; }
        .cp-btn-cobrar { background: linear-gradient(135deg, #0FD186, #0AB871); color: white; box-shadow: 0 4px 14px rgba(15,209,134,.3); }
        .cp-btn-cobrar:hover { transform: translateY(-1px); }
        .cp-btn-sec { background: var(--background); color: var(--text-secondary); border: 1.5px solid var(--border); flex: 0 0 auto; padding: 11px 14px; }

        /* Modal cobrar */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 2000;
            align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: white; border-radius: 16px; padding: 28px;
            width: 100%; max-width: 380px;
            animation: slideUp .25s ease;
        }
        @keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
        .modal-title { font-size: 18px; font-weight: 800; margin-bottom: 18px; }
        .modal-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; }
        .modal-row.total { font-size: 20px; font-weight: 800; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }

        /* Overlay oscuro cuando panel abierto */
        .panel-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 999; }
        .panel-overlay.show { display: block; }

        /* Botones de acción flotante */
        .fab-group { position: fixed; bottom: 28px; right: 28px; display: flex; flex-direction: column; gap: 10px; }
        .fab {
            width: 52px; height: 52px; border-radius: 50%;
            border: none; cursor: pointer; font-size: 18px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
            transition: var(--transition);
            color: white;
        }
        .fab:hover { transform: scale(1.08); }
        .fab-primary { background: linear-gradient(135deg, #0FD186, #0AB871); }
        .fab-blue    { background: #4299E1; }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-secondary); }
        .empty-state i { font-size: 40px; margin-bottom: 12px; opacity: .4; display: block; }

        /* ════ RESPONSIVE ════ */
        @media (max-width: 768px) {
            .main-content { padding: 12px !important; }
            .salon-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .salon-header > div:last-child { width: 100%; justify-content: flex-start; }
            .mesas-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
            .mesa-card { padding: 12px 8px; }
            .mesa-icon { width: 42px; height: 42px; font-size: 18px; }
            .mesa-numero { font-size: 15px; }
            .sector-tabs { gap: 6px; }
            .sector-tab { padding: 5px 12px; font-size: 12px; }
            /* Panel ocupa toda la pantalla en móvil */
            .comanda-panel { width: 100%; right: -100%; top: auto; bottom: 0; height: 92vh; border-radius: 20px 20px 0 0; }
            .comanda-panel.open { right: 0; bottom: 0; }
            .cp-header { border-radius: 20px 20px 0 0; padding: 16px; }
            .leyenda { display: none; } /* Ocultar leyenda en móvil para ahorrar espacio */
        }
        @media (max-width: 480px) {
            .mesas-grid { grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .mesa-comanda { display: none; } /* Solo mostrar icono/número en pantallas chicas */
        }
    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<div class="dashboard-layout">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content" style="flex:1; padding: 24px; overflow-y:auto;">
        <?php include '../includes/header.php'; ?>

        <div class="salon-header">
            <div>
                <div class="salon-title"><i class="fas fa-utensils" style="color:var(--primary);margin-right:8px;"></i>Salón</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-top:2px;" id="resumenEstados">Cargando mesas…</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div class="leyenda">
                    <div class="leyenda-item"><div class="leyenda-dot" style="background:var(--libre);"></div> Libre</div>
                    <div class="leyenda-item"><div class="leyenda-dot" style="background:var(--ocupada);"></div> Ocupada</div>
                    <div class="leyenda-item"><div class="leyenda-dot" style="background:var(--reservada);"></div> Reservada</div>
                </div>
                <button class="btn btn-primary btn-sm" onclick="abrirModalNuevaMesa()">
                    <i class="fas fa-plus"></i> Nueva mesa
                </button>
                <a href="<?= $base ?>/views/restaurant/reservas.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-calendar-alt"></i> Reservas
                </a>
                <a href="<?= $base ?>/views/restaurant/cocina.php" class="btn btn-outline btn-sm" target="_blank">
                    <i class="fas fa-fire"></i> Cocina
                </a>
            </div>
        </div>

        <!-- Tabs sectores -->
        <div class="sector-tabs" id="sectorTabs" style="margin-bottom:20px;">
            <div class="sector-tab active" data-sector="todos" onclick="filtrarSector('todos',this)">
                <i class="fas fa-th"></i> Todos
            </div>
        </div>

        <!-- Grid mesas -->
        <div class="mesas-grid" id="mesasGrid">
            <div class="empty-state" style="grid-column:span 6;">
                <i class="fas fa-spinner fa-spin"></i><br>Cargando mesas…
            </div>
        </div>
    </div>
</div>

<!-- Overlay -->
<div class="panel-overlay" id="panelOverlay" onclick="cerrarPanel()"></div>

<!-- Panel lateral comanda -->
<div class="comanda-panel" id="comandaPanel">
    <div class="cp-header">
        <div>
            <h3 id="cpTitulo">Mesa —</h3>
            <p id="cpSubtitulo">Sin comanda activa</p>
        </div>
        <button class="cp-close" onclick="cerrarPanel()"><i class="fas fa-times"></i></button>
    </div>

    <div class="cp-body">
        <!-- Buscar producto -->
        <div>
            <div class="cat-chips" id="catChips"></div>
            <div class="producto-search" id="buscadorWrap">
                <i class="fas fa-search"></i>
                <input type="text" id="buscadorProd" placeholder="Buscar plato o producto…" oninput="buscarProductos(this.value)" autocomplete="off">
                <div class="productos-lista" id="prodLista"></div>
            </div>
        </div>

        <!-- Ítems de la comanda -->
        <div class="comanda-items" id="comandaItems">
            <div class="empty-state"><i class="fas fa-clipboard"></i>Sin ítems todavía</div>
        </div>
    </div>

    <div class="cp-total">
        <div class="total-row"><span>Subtotal</span><span id="cpSubtotal">$0,00</span></div>
        <div class="total-row"><span>Descuento</span>
            <span>$<input type="number" id="cpDescuento" value="0" min="0" style="width:70px;border:1.5px solid var(--border);border-radius:6px;padding:2px 6px;text-align:right;" onchange="actualizarTotal()"></span>
        </div>
        <div class="total-row grand"><span>TOTAL</span><span id="cpTotal">$0,00</span></div>
        <div class="cp-actions">
            <button class="cp-btn cp-btn-sec" onclick="cerrarPanel()" title="Volver"><i class="fas fa-arrow-left"></i></button>
            <button class="cp-btn cp-btn-cocina" id="btnCocina" onclick="enviarCocina()"><i class="fas fa-fire"></i> Cocina</button>
            <button class="cp-btn cp-btn-cobrar" id="btnCobrar" onclick="abrirCobro()"><i class="fas fa-cash-register"></i> Cobrar</button>
        </div>
    </div>
</div>

<!-- Modal cobrar -->
<div class="modal-overlay" id="modalCobrar">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-cash-register" style="color:var(--primary);margin-right:8px;"></i>Cobrar</div>
        <div class="modal-row"><span>Mesa</span><span id="mc_mesa">—</span></div>
        <div class="modal-row"><span>Comanda</span><span id="mc_num">#—</span></div>
        <div class="modal-row"><span>Subtotal</span><span id="mc_sub">$0</span></div>
        <div class="modal-row"><span>Descuento</span><span id="mc_desc">$0</span></div>
        <div class="modal-row total"><span>Total</span><span id="mc_total">$0</span></div>
        <div style="margin-top:16px;">
            <label style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">Método de pago</label>
            <select id="mc_metodo" style="width:100%;margin-top:6px;padding:10px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta_debito">Tarjeta Débito</option>
                <option value="tarjeta_credito">Tarjeta Crédito</option>
                <option value="transferencia">Transferencia</option>
                <option value="mercado_pago">Mercado Pago</option>
            </select>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button class="cp-btn cp-btn-sec" style="flex:0 0 auto;padding:11px 18px;" onclick="cerrarModal()">Cancelar</button>
            <button class="cp-btn cp-btn-cobrar" onclick="confirmarCobro()"><i class="fas fa-check"></i> Confirmar cobro</button>
        </div>
    </div>
</div>

<!-- Modal nueva mesa -->
<div class="modal-overlay" id="modalMesa">
    <div class="modal-box">
        <div class="modal-title">Nueva Mesa</div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;display:block;margin-bottom:5px;">Número / Nombre *</label>
                <input type="text" id="nm_numero" class="form-control" placeholder="Ej: 11, VIP-1" style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;outline:none;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;display:block;margin-bottom:5px;">Sector</label>
                <select id="nm_sector" style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;"></select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;display:block;margin-bottom:5px;">Capacidad</label>
                <input type="number" id="nm_cap" value="4" min="1" style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;outline:none;">
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button class="cp-btn cp-btn-sec" style="flex:0 0 auto;padding:11px 18px;" onclick="document.getElementById('modalMesa').classList.remove('show')">Cancelar</button>
            <button class="cp-btn cp-btn-cobrar" onclick="crearMesa()"><i class="fas fa-plus"></i> Crear mesa</button>
        </div>
    </div>
</div>

<script>
const BASE      = window.APP_BASE;
let sectores    = [];
let mesas       = [];
let sectorActual = 'todos';
let mesaActual   = null;
let comandaActual = null;
let productosCache = [];
let timerPolling   = null;

/* ── INIT ─────────────────────────────────────────────── */
async function init() {
    await cargarMesas();
    startPolling();
}

function startPolling() {
    clearInterval(timerPolling);
    timerPolling = setInterval(cargarMesas, 15000);
}

/* ── CARGAR MESAS ─────────────────────────────────────── */
async function cargarMesas() {
    try {
        const r = await fetch(`${BASE}/api/restaurant/mesas.php`);
        const d = await r.json();
        if (!d.success) return;
        mesas    = d.data.mesas;
        sectores = d.data.sectores;
        renderTabs();
        renderMesas();
        actualizarResumen();
    } catch(e) { console.error(e); }
}

function renderTabs() {
    const cont = document.getElementById('sectorTabs');
    cont.innerHTML = `<div class="sector-tab ${sectorActual==='todos'?'active':''}" data-sector="todos" onclick="filtrarSector('todos',this)"><i class="fas fa-th"></i> Todos</div>`;
    sectores.forEach(s => {
        const active = sectorActual == s.id ? 'active' : '';
        cont.innerHTML += `<div class="sector-tab ${active}" data-sector="${s.id}" onclick="filtrarSector(${s.id},this)" style="${active?`border-color:${s.color};color:${s.color};background:#f0fff4`:''}">
            <i class="fas fa-map-marker-alt" style="color:${s.color};"></i> ${s.nombre}
        </div>`;
    });
}

function filtrarSector(id, el) {
    sectorActual = id;
    document.querySelectorAll('.sector-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    renderMesas();
}

function renderMesas() {
    const grid   = document.getElementById('mesasGrid');
    const filtro = sectorActual === 'todos' ? mesas : mesas.filter(m => m.sector_id == sectorActual);

    if (!filtro.length) {
        grid.innerHTML = `<div class="empty-state" style="grid-column:span 6;"><i class="fas fa-chair"></i><br>No hay mesas en este sector</div>`;
        return;
    }

    grid.innerHTML = filtro.map(m => {
        const est  = m.estado;
        const time = m.comanda_desde ? tiempoDesde(m.comanda_desde) : '';
        const badge = m.comanda_estado === 'lista' ? `<div class="mesa-badge lista">✓ Lista</div>` :
                      (m.comanda_estado === 'en_cocina' ? `<div class="mesa-badge">En cocina</div>` : '');

        const icon = est === 'libre' ? 'fa-chair' : est === 'ocupada' ? 'fa-users' : 'fa-calendar-check';

        return `<div class="mesa-card ${est}" onclick="abrirMesa(${m.id})">
            ${badge}
            <div class="mesa-icon"><i class="fas ${icon}"></i></div>
            <div class="mesa-numero">${m.numero}</div>
            <div class="mesa-cap"><i class="fas fa-user" style="font-size:10px;"></i> ${m.capacidad} personas</div>
            ${m.sector_nombre ? `<div class="mesa-cap">${m.sector_nombre}</div>` : ''}
            ${m.comanda_id ? `<div class="mesa-comanda">
                <strong>#${m.comanda_numero}</strong> · ${fmtMoney(m.comanda_total)}<br>
                <span style="color:var(--text-secondary)">${time}</span>
            </div>` : ''}
        </div>`;
    }).join('');
}

function actualizarResumen() {
    const libres    = mesas.filter(m => m.estado === 'libre').length;
    const ocupadas  = mesas.filter(m => m.estado === 'ocupada').length;
    const reservadas= mesas.filter(m => m.estado === 'reservada').length;
    document.getElementById('resumenEstados').innerHTML =
        `<span style="color:var(--libre)">● ${libres} libres</span> &nbsp;
         <span style="color:var(--ocupada)">● ${ocupadas} ocupadas</span> &nbsp;
         <span style="color:var(--reservada)">● ${reservadas} reservadas</span>`;
}

/* ── ABRIR MESA ───────────────────────────────────────── */
async function abrirMesa(mesaId) {
    const r = await fetch(`${BASE}/api/restaurant/mesas.php?id=${mesaId}`);
    const d = await r.json();
    if (!d.success) return;

    mesaActual = d.data;
    document.getElementById('cpTitulo').textContent = `Mesa ${d.data.numero}` + (d.data.nombre ? ` — ${d.data.nombre}` : '');

    if (d.data.comanda_id) {
        // Cargar comanda existente
        await cargarComanda(d.data.comanda_id);
    } else {
        // Mesa libre → abrir nueva comanda
        const cr = await fetch(`${BASE}/api/restaurant/comandas.php`, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'abrir', mesa_id: mesaId })
        });
        const cd = await cr.json();
        await cargarComanda(cd.data.id);
    }

    abrirPanel();
}

async function cargarComanda(id) {
    const r = await fetch(`${BASE}/api/restaurant/comandas.php?id=${id}`);
    const d = await r.json();
    if (!d.success) return;
    comandaActual = d.data;
    document.getElementById('cpSubtitulo').textContent = `Comanda #${d.data.numero} · ${d.data.personas} personas`;
    renderItems(d.data.items);
    actualizarTotalPanel();

    // Actualizar el total en la tarjeta de la mesa sin recargar todo
    const nuevoTotal = calcularSubtotalItems();
    const mesaEnArray = mesas.find(m => m.comanda_id == id);
    if (mesaEnArray) {
        mesaEnArray.comanda_total = nuevoTotal;
        renderMesas();
    }
}

/* ── RENDERIZAR ÍTEMS ─────────────────────────────────── */
function renderItems(items) {
    const cont = document.getElementById('comandaItems');
    if (!items || !items.length) {
        cont.innerHTML = `<div class="empty-state"><i class="fas fa-clipboard"></i>Agregá ítems desde el buscador</div>`;
        return;
    }
    cont.innerHTML = items.filter(i => i.estado_cocina !== 'cancelado').map(i => `
        <div class="ci-row" id="item_${i.id}">
            <div class="ci-estado ${i.estado_cocina}" title="${i.estado_cocina}"></div>
            <div style="flex:1;">
                <div class="ci-nombre">${esc(i.nombre_item)}</div>
                ${i.observaciones ? `<div class="ci-obs">${esc(i.observaciones)}</div>` : ''}
            </div>
            <div class="ci-cant">
                <button onclick="cambiarCantidad(${i.id},${i.cantidad - 1})">−</button>
                <span>${i.cantidad}</span>
                <button onclick="cambiarCantidad(${i.id},${i.cantidad + 1})">+</button>
            </div>
            <div class="ci-precio">${fmtMoney(i.subtotal)}</div>
            <button class="ci-del" onclick="cancelarItem(${i.id})" title="Cancelar ítem"><i class="fas fa-times"></i></button>
        </div>
    `).join('');
}

/* ── BUSCAR PRODUCTOS ─────────────────────────────────── */
let busqTimer = null;
let catActual  = '';

async function cargarProductosPanel() {
    if (productosCache.length) { renderCatChips(); mostrarTodosProductos(); return; }
    try {
        const r = await fetch(`${BASE}/api/productos/index.php?activo=1&limit=200`);
        const d = await r.json();
        productosCache = d.data?.productos || [];
        renderCatChips();
        mostrarTodosProductos();
    } catch(e) { console.error(e); }
}

function renderCatChips() {
    const cats = [...new Map(productosCache.map(p => [p.categoria_id, {id:p.categoria_id, nombre:p.categoria_nombre, color:p.categoria_color}])).values()];
    const cont = document.getElementById('catChips');
    cont.innerHTML = `<span class="cat-chip active" data-cat="" onclick="filtrarCategoria('',this)">Todos</span>` +
        cats.map(c => `<span class="cat-chip" data-cat="${c.id}" onclick="filtrarCategoria('${c.id}',this)"
            style="border-color:${c.color}20;">${c.nombre}</span>`).join('');
}

function filtrarCategoria(catId, el) {
    catActual = catId;
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('buscadorProd').value = '';
    mostrarTodosProductos();
}

function mostrarTodosProductos() {
    const lista = document.getElementById('prodLista');
    const filtrados = catActual
        ? productosCache.filter(p => p.categoria_id == catActual)
        : productosCache;
    if (!filtrados.length) { lista.classList.remove('show'); return; }
    lista.innerHTML = filtrados.map(p => `
        <div class="prod-item" onclick="agregarItem(${p.id},'${esc(p.nombre)}',${p.precio_venta})">
            <div>
                <div>${esc(p.nombre)}</div>
                <div class="prod-item-cat">${p.categoria_nombre || ''}</div>
            </div>
            <strong style="color:var(--primary);flex-shrink:0;">${fmtMoney(p.precio_venta)}</strong>
        </div>
    `).join('');
    lista.classList.add('show');
}

async function buscarProductos(q) {
    clearTimeout(busqTimer);
    if (!q) { mostrarTodosProductos(); return; }
    if (q.length < 1) { mostrarTodosProductos(); return; }
    busqTimer = setTimeout(() => {
        const lista = document.getElementById('prodLista');
        const filtrados = productosCache.filter(p =>
            p.nombre.toLowerCase().includes(q.toLowerCase()) &&
            (catActual === '' || p.categoria_id == catActual)
        );
        if (!filtrados.length) { lista.classList.remove('show'); return; }
        lista.innerHTML = filtrados.map(p => `
            <div class="prod-item" onclick="agregarItem(${p.id},'${esc(p.nombre)}',${p.precio_venta})">
                <div>
                    <div>${esc(p.nombre)}</div>
                    <div class="prod-item-cat">${p.categoria_nombre || ''}</div>
                </div>
                <strong style="color:var(--primary);flex-shrink:0;">${fmtMoney(p.precio_venta)}</strong>
            </div>
        `).join('');
        lista.classList.add('show');
    }, 150);
}

document.addEventListener('click', e => {
    if (!e.target.closest('.producto-search') && !e.target.closest('.cat-chips')) {
        document.getElementById('prodLista').classList.remove('show');
    }
});

async function agregarItem(prodId, nombre, precio) {
    if (!comandaActual) return;
    document.getElementById('prodLista').classList.remove('show');
    document.getElementById('buscadorProd').value = '';

    // Actualización optimista: mostrar el ítem y total antes de esperar al servidor
    if (!comandaActual.items) comandaActual.items = [];
    comandaActual.items.push({
        id: 'tmp_' + Date.now(), producto_id: prodId,
        nombre_item: nombre, precio_unit: precio,
        cantidad: 1, subtotal: parseFloat(precio),
        estado_cocina: 'pendiente', observaciones: ''
    });
    renderItems(comandaActual.items);
    actualizarTotalPanel();

    await fetch(`${BASE}/api/restaurant/comandas.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            action: 'agregar_item',
            comanda_id:  comandaActual.id,
            producto_id: prodId,
            nombre_item: nombre,
            precio_unit: precio,
            cantidad:    1,
            observaciones: '',
        })
    });
    // Sincronizar con el servidor para obtener IDs reales
    await cargarComanda(comandaActual.id);
}

async function cambiarCantidad(itemId, nuevaCant) {
    if (nuevaCant < 1) { cancelarItem(itemId); return; }
    const item = comandaActual.items.find(i => i.id == itemId);
    if (!item) return;

    // Actualización optimista
    item.cantidad = nuevaCant;
    item.subtotal = parseFloat(item.precio_unit) * nuevaCant;
    renderItems(comandaActual.items);
    actualizarTotalPanel();

    await fetch(`${BASE}/api/restaurant/comandas.php?item_id=${itemId}&comanda_id=${comandaActual.id}`, { method: 'DELETE' });
    await fetch(`${BASE}/api/restaurant/comandas.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            action: 'agregar_item', comanda_id: comandaActual.id,
            producto_id: item.producto_id, nombre_item: item.nombre_item,
            precio_unit: item.precio_unit, cantidad: nuevaCant,
            observaciones: item.observaciones, sector_cocina: item.sector_cocina
        })
    });
    await cargarComanda(comandaActual.id);
}

async function cancelarItem(itemId) {
    // Actualización optimista
    if (comandaActual && comandaActual.items) {
        const idx = comandaActual.items.findIndex(i => i.id == itemId);
        if (idx !== -1) comandaActual.items.splice(idx, 1);
        renderItems(comandaActual.items);
        actualizarTotalPanel();
    }
    await fetch(`${BASE}/api/restaurant/comandas.php?item_id=${itemId}&comanda_id=${comandaActual.id}`, { method: 'DELETE' });
    await cargarComanda(comandaActual.id);
}

/* ── TOTAL ────────────────────────────────────────────── */
function calcularSubtotalItems() {
    if (!comandaActual || !comandaActual.items) return 0;
    return comandaActual.items
        .filter(i => i.estado_cocina !== 'cancelado')
        .reduce((acc, i) => acc + parseFloat(i.subtotal || 0), 0);
}

function actualizarTotalPanel() {
    if (!comandaActual) return;
    const sub  = calcularSubtotalItems();
    const desc = parseFloat(document.getElementById('cpDescuento').value) || 0;
    document.getElementById('cpSubtotal').textContent = fmtMoney(sub);
    document.getElementById('cpTotal').textContent    = fmtMoney(sub - desc);
}

function actualizarTotal() { actualizarTotalPanel(); }

/* ── ENVIAR A COCINA ──────────────────────────────────── */
async function enviarCocina() {
    if (!comandaActual) return;
    const btn = document.getElementById('btnCocina');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    await fetch(`${BASE}/api/restaurant/comandas.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action: 'enviar_cocina', comanda_id: comandaActual.id })
    });
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-fire"></i> Cocina';
    await cargarComanda(comandaActual.id);
    showToast('Comanda enviada a cocina', 'success');
}

/* ── COBRAR ───────────────────────────────────────────── */
function abrirCobro() {
    if (!comandaActual || !mesaActual) return;
    const sub  = calcularSubtotalItems();
    const desc = parseFloat(document.getElementById('cpDescuento').value) || 0;
    document.getElementById('mc_mesa').textContent  = `Mesa ${mesaActual.numero}`;
    document.getElementById('mc_num').textContent   = `#${comandaActual.numero}`;
    document.getElementById('mc_sub').textContent   = fmtMoney(sub);
    document.getElementById('mc_desc').textContent  = fmtMoney(desc);
    document.getElementById('mc_total').textContent = fmtMoney(sub - desc);
    document.getElementById('modalCobrar').classList.add('show');
}

async function confirmarCobro() {
    const metodo  = document.getElementById('mc_metodo').value;
    const descuento = parseFloat(document.getElementById('cpDescuento').value) || 0;
    const r = await fetch(`${BASE}/api/restaurant/comandas.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            action: 'cerrar', comanda_id: comandaActual.id,
            metodo_pago: metodo, descuento: descuento
        })
    });
    const d = await r.json();
    if (d.success) {
        cerrarModal();
        cerrarPanel();
        showToast(`Cobrado ${fmtMoney(d.data.total)} — Venta #${d.data.venta_id}`, 'success');
        await cargarMesas();
    } else {
        showToast(d.message, 'error');
    }
}

/* ── PANEL ────────────────────────────────────────────── */
function abrirPanel() {
    document.getElementById('comandaPanel').classList.add('open');
    document.getElementById('panelOverlay').classList.add('show');
    clearInterval(timerPolling);
    // Cargar productos al abrir el panel
    cargarProductosPanel();
}
function cerrarPanel() {
    document.getElementById('comandaPanel').classList.remove('open');
    document.getElementById('panelOverlay').classList.remove('show');
    mesaActual = null; comandaActual = null;
    startPolling();
}
function cerrarModal() { document.getElementById('modalCobrar').classList.remove('show'); }

/* ── NUEVA MESA ───────────────────────────────────────── */
function abrirModalNuevaMesa() {
    const sel = document.getElementById('nm_sector');
    sel.innerHTML = sectores.map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');
    document.getElementById('modalMesa').classList.add('show');
}
async function crearMesa() {
    const numero = document.getElementById('nm_numero').value.trim();
    const sector = document.getElementById('nm_sector').value;
    const cap    = document.getElementById('nm_cap').value;
    if (!numero) return showToast('Ingresá el número de mesa', 'error');
    await fetch(`${BASE}/api/restaurant/mesas.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ numero, sector_id: sector, capacidad: cap })
    });
    document.getElementById('modalMesa').classList.remove('show');
    showToast('Mesa creada', 'success');
    await cargarMesas();
}

/* ── UTILS ────────────────────────────────────────────── */
function fmtMoney(n) {
    return new Intl.NumberFormat('es-AR', {style:'currency', currency:'ARS'}).format(n||0);
}
function tiempoDesde(ts) {
    const min = Math.floor((Date.now() - new Date(ts)) / 60000);
    if (min < 60) return `${min} min`;
    return `${Math.floor(min/60)}h ${min%60}m`;
}
function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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

init();
</script>
</body>
</html>
