<?php
// La cocina es una pantalla independiente — no requiere sidebar ni header normal
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
    <title>Cocina KDS — DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        :root {
            --bg:        #0A0E14;
            --surface:   #13181F;
            --surface2:  #1C2230;
            --surface3:  #232B3A;
            --border:    #2A3344;
            --text:      #E8EFF8;
            --muted:     #6B7A99;
            --green:     #0FD186;
            --green-dim: rgba(15,209,134,.12);
            --yellow:    #F6C344;
            --yellow-dim:rgba(246,195,68,.12);
            --orange:    #FF7A30;
            --orange-dim:rgba(255,122,48,.12);
            --red:       #FF4D6A;
            --red-dim:   rgba(255,77,106,.12);
            --blue:      #4DA6FF;
            --blue-dim:  rgba(77,166,255,.12);
            --radius:    12px;
            --radius-lg: 16px;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ════════════════════════════════
           TOP BAR
        ════════════════════════════════ */
        .kds-topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            height: 64px;
            flex-shrink: 0;
            gap: 20px;
        }

        /* Logo / título */
        .kds-title {
            display: flex; align-items: center; gap: 10px;
            font-size: 17px; font-weight: 800; color: var(--text);
            letter-spacing: -.3px;
        }
        .kds-title-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #FF7A30, #FF4D6A);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(255,122,48,.35);
        }
        .kds-title span { color: var(--muted); font-weight: 400; font-size: 13px; margin-left: 2px; }

        /* Contadores centrales */
        .kds-counters {
            display: flex; align-items: center; gap: 6px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 6px 8px;
        }
        .kds-cnt {
            display: flex; align-items: center; gap: 7px;
            padding: 6px 14px; border-radius: 8px;
            min-width: 90px;
        }
        .kds-cnt-icon {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; flex-shrink: 0;
        }
        .kds-cnt.pend  .kds-cnt-icon { background: var(--yellow-dim); color: var(--yellow); }
        .kds-cnt.prep  .kds-cnt-icon { background: var(--blue-dim);   color: var(--blue); }
        .kds-cnt.listo .kds-cnt-icon { background: var(--green-dim);  color: var(--green); }
        .kds-cnt-info {}
        .kds-cnt-n { font-size: 20px; font-weight: 900; line-height: 1; }
        .kds-cnt.pend  .kds-cnt-n { color: var(--yellow); }
        .kds-cnt.prep  .kds-cnt-n { color: var(--blue); }
        .kds-cnt.listo .kds-cnt-n { color: var(--green); }
        .kds-cnt-l { font-size: 10px; text-transform: uppercase; color: var(--muted); font-weight: 700; letter-spacing: .4px; }

        .kds-cnt-divider { width: 1px; height: 28px; background: var(--border); }

        /* Derecha topbar */
        .kds-topbar-right {
            display: flex; align-items: center; gap: 10px;
        }
        .kds-clock {
            font-size: 20px; font-weight: 300; color: var(--text);
            font-variant-numeric: tabular-nums;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 10px; padding: 6px 14px;
            letter-spacing: 1px;
        }
        .kds-icon-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--surface2); border: 1px solid var(--border);
            color: var(--muted); cursor: pointer; font-size: 15px;
            display: flex; align-items: center; justify-content: center;
            transition: all .2s; text-decoration: none;
        }
        .kds-icon-btn:hover { border-color: var(--text); color: var(--text); }
        .kds-icon-btn.sound-on { border-color: var(--green); color: var(--green); background: var(--green-dim); }

        /* ════════════════════════════════
           FILTROS / TABS
        ════════════════════════════════ */
        .kds-filters {
            display: flex; align-items: center; gap: 6px;
            padding: 10px 20px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            overflow-x: auto; flex-shrink: 0;
        }
        .kds-filters::-webkit-scrollbar { display: none; }

        .kds-filter {
            display: flex; align-items: center; gap: 7px;
            padding: 7px 16px; border-radius: 10px; cursor: pointer;
            border: 1px solid var(--border); background: transparent;
            color: var(--muted); font-size: 12px; font-weight: 700;
            white-space: nowrap; transition: all .2s;
            letter-spacing: .2px;
        }
        .kds-filter-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--muted); transition: background .2s; }
        .kds-filter.active, .kds-filter:hover {
            border-color: var(--green); color: var(--green);
            background: var(--green-dim);
        }
        .kds-filter.active .kds-filter-dot,
        .kds-filter:hover  .kds-filter-dot { background: var(--green); }

        .kds-refresh {
            margin-left: auto; font-size: 11px; color: var(--muted);
            white-space: nowrap; display: flex; align-items: center; gap: 5px;
        }
        .kds-refresh-dot {
            width: 6px; height: 6px; border-radius: 50%; background: var(--green);
            animation: blink 2s infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

        /* ════════════════════════════════
           BOARD (columnas)
        ════════════════════════════════ */
        .kds-board {
            display: flex; gap: 14px; padding: 16px 20px;
            flex: 1;
            overflow-x: auto;
            overflow-y: hidden;
            align-items: flex-start;
        }
        .kds-board::-webkit-scrollbar { height: 5px; }
        .kds-board::-webkit-scrollbar-track { background: transparent; }
        .kds-board::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        /* ── Columna ── */
        .kds-col {
            width: 300px; flex-shrink: 0;
            display: flex; flex-direction: column; gap: 10px;
            height: 100%; overflow-y: auto;
        }
        .kds-col::-webkit-scrollbar { width: 4px; }
        .kds-col::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        .kds-col-header {
            background: var(--surface2);
            border-radius: var(--radius);
            padding: 12px 16px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 1;
            border: 1px solid var(--border);
            flex-shrink: 0;
        }
        .kds-col-title { display: flex; align-items: center; gap: 10px; }
        .kds-col-emoji { font-size: 18px; }
        .kds-col-name  { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text); }
        .kds-col-sub   { font-size: 10px; color: var(--muted); margin-top: 2px; font-weight: 600; }
        .kds-col-badge {
            min-width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 900;
            background: var(--surface3);
        }

        /* ════════════════════════════════
           TICKET
        ════════════════════════════════ */
        .kds-ticket {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 0;
            transition: transform .15s, box-shadow .15s;
            overflow: hidden;
        }
        .kds-ticket:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.3); }

        /* Banda de color superior según tiempo */
        .kds-ticket-band {
            height: 4px; width: 100%;
        }
        .tiempo-ok      .kds-ticket-band { background: linear-gradient(90deg, var(--green), #0ab871); }
        .tiempo-medio   .kds-ticket-band { background: linear-gradient(90deg, var(--yellow), #e5a800); }
        .tiempo-alto    .kds-ticket-band { background: linear-gradient(90deg, var(--orange), #e05a00); }
        .tiempo-critico .kds-ticket-band { background: linear-gradient(90deg, var(--red), #c0002a); animation: pulseBar 1.2s infinite; }
        @keyframes pulseBar { 0%,100%{opacity:1} 50%{opacity:.4} }

        .kds-ticket-body { padding: 14px 16px; }

        /* Cabecera del ticket */
        .kds-ticket-head {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 12px;
        }
        .kds-mesa-badge {
            display: flex; align-items: center; gap: 8px;
        }
        .kds-mesa-num {
            background: var(--surface3); border: 1px solid var(--border);
            border-radius: 8px; padding: 4px 10px;
            font-size: 13px; font-weight: 800; color: var(--text);
            letter-spacing: .3px;
        }
        .kds-comanda-num { font-size: 11px; color: var(--muted); font-weight: 600; }

        /* Timer badge */
        .kds-timer {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 800;
            padding: 5px 10px; border-radius: 8px;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }
        .kds-timer i { font-size: 10px; }
        .tiempo-ok      .kds-timer { background: var(--green-dim);  color: var(--green); }
        .tiempo-medio   .kds-timer { background: var(--yellow-dim); color: var(--yellow); }
        .tiempo-alto    .kds-timer { background: var(--orange-dim); color: var(--orange); }
        .tiempo-critico .kds-timer { background: var(--red-dim);    color: var(--red); }

        /* Nombre del plato */
        .kds-plato {
            margin-bottom: 8px;
        }
        .kds-plato-cant {
            display: inline-flex; align-items: center; justify-content: center;
            width: 26px; height: 26px; border-radius: 7px;
            background: var(--orange-dim); color: var(--orange);
            font-size: 13px; font-weight: 900; margin-right: 8px;
            vertical-align: middle;
        }
        .kds-plato-nombre {
            font-size: 17px; font-weight: 800; color: var(--text);
            line-height: 1.3;
        }

        /* Observaciones */
        .kds-obs {
            display: flex; align-items: flex-start; gap: 7px;
            background: rgba(246,195,68,.08); border: 1px solid rgba(246,195,68,.2);
            border-radius: 8px; padding: 7px 10px;
            margin-bottom: 8px;
        }
        .kds-obs i { color: var(--yellow); font-size: 11px; margin-top: 2px; flex-shrink: 0; }
        .kds-obs span { font-size: 12px; color: var(--yellow); font-style: italic; line-height: 1.4; }

        /* Mozo */
        .kds-mozo {
            display: flex; align-items: center; gap: 6px;
            font-size: 11px; color: var(--muted); margin-bottom: 2px;
        }
        .kds-mozo i { font-size: 10px; }

        /* Separador */
        .kds-divider { height: 1px; background: var(--border); margin: 12px 0; }

        /* Botones de acción */
        .kds-btns { display: flex; gap: 8px; }
        .kds-btn {
            flex: 1; padding: 10px 8px; border: none; border-radius: 10px;
            font-size: 12px; font-weight: 800; cursor: pointer;
            transition: all .15s; display: flex; align-items: center;
            justify-content: center; gap: 6px; letter-spacing: .2px;
        }
        .kds-btn:active { transform: scale(.97); }
        .kds-btn-prep {
            background: var(--blue-dim); color: var(--blue);
            border: 1px solid rgba(77,166,255,.25);
        }
        .kds-btn-prep:hover { background: var(--blue); color: #000; border-color: var(--blue); }
        .kds-btn-listo {
            background: var(--green-dim); color: var(--green);
            border: 1px solid rgba(15,209,134,.25);
        }
        .kds-btn-listo:hover { background: var(--green); color: #000; border-color: var(--green); }
        .kds-btn-done {
            width: 100%; padding: 10px; border-radius: 10px; border: none;
            background: rgba(15,209,134,.06); color: var(--green);
            font-size: 12px; font-weight: 800; display: flex;
            align-items: center; justify-content: center; gap: 7px;
            letter-spacing: .2px; cursor: default;
            border: 1px solid rgba(15,209,134,.15);
        }

        /* ════════════════════════════════
           ESTADOS VACÍOS
        ════════════════════════════════ */
        .kds-todo-vacio {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; flex: 1; color: var(--muted);
            gap: 16px;
        }
        .kds-vacio-icon {
            width: 80px; height: 80px; border-radius: 24px;
            background: var(--surface2); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; opacity: .5;
        }
        .kds-todo-vacio h3 { font-size: 20px; font-weight: 700; color: var(--text); opacity: .5; }
        .kds-todo-vacio p  { font-size: 13px; opacity: .4; }

        /* ════════════════════════════════
           NOTIFICACIÓN FLASH
        ════════════════════════════════ */
        .kds-notif {
            position: fixed; top: 74px; right: 20px;
            background: var(--surface2); border: 1px solid var(--green);
            color: var(--green);
            padding: 12px 20px; border-radius: 12px;
            font-weight: 700; font-size: 13px;
            z-index: 999; box-shadow: 0 8px 30px rgba(0,0,0,.4);
            animation: slideInRight .25s ease;
            display: flex; align-items: center; gap: 8px;
        }
        .kds-notif-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        @keyframes slideInRight { from { transform:translateX(40px); opacity:0; } to { transform:translateX(0); opacity:1; } }

    </style>
</head>
<body>
<script>window.APP_BASE = '<?= $base ?>';</script>

<!-- TOP BAR -->
<div class="kds-topbar">

    <!-- Izquierda: logo + título -->
    <div class="kds-title">
        <div class="kds-title-icon">🔥</div>
        Cocina KDS
        <span>/ Pantalla de producción</span>
    </div>

    <!-- Centro: contadores -->
    <div class="kds-counters">
        <div class="kds-cnt pend">
            <div class="kds-cnt-icon"><i class="fas fa-hourglass-start"></i></div>
            <div class="kds-cnt-info">
                <div class="kds-cnt-n" id="cnt_pendiente">0</div>
                <div class="kds-cnt-l">Pendientes</div>
            </div>
        </div>
        <div class="kds-cnt-divider"></div>
        <div class="kds-cnt prep">
            <div class="kds-cnt-icon"><i class="fas fa-fire-burner"></i></div>
            <div class="kds-cnt-info">
                <div class="kds-cnt-n" id="cnt_preparacion">0</div>
                <div class="kds-cnt-l">En prep.</div>
            </div>
        </div>
        <div class="kds-cnt-divider"></div>
        <div class="kds-cnt listo">
            <div class="kds-cnt-icon"><i class="fas fa-bell-concierge"></i></div>
            <div class="kds-cnt-info">
                <div class="kds-cnt-n" id="cnt_listo">0</div>
                <div class="kds-cnt-l">Listos</div>
            </div>
        </div>
    </div>

    <!-- Derecha: controles -->
    <div class="kds-topbar-right">
        <div class="kds-clock" id="reloj">--:--:--</div>
        <button class="kds-icon-btn" id="soundBtn" onclick="toggleSonido()" title="Sonido">
            <i class="fas fa-volume-xmark"></i>
        </button>
        <a href="<?= $base ?>/views/restaurant/mesas.php" class="kds-icon-btn" title="Volver al Salón">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

</div>

<!-- FILTROS -->
<div class="kds-filters">
    <button class="kds-filter active" data-sector="todos" onclick="filtrarSector('todos',this)">
        <span class="kds-filter-dot"></span> Todos
    </button>
    <div id="sectorFiltros" style="display:contents;"></div>
    <div class="kds-refresh" id="lastRefresh">
        <span class="kds-refresh-dot"></span> Actualizando…
    </div>
</div>

<!-- BOARD -->
<div class="kds-board" id="kdsBoard">
    <div class="kds-todo-vacio" style="width:100%;">
        <div class="kds-vacio-icon">⏳</div>
        <h3>Cargando…</h3>
    </div>
</div>

<script>
const BASE      = window.APP_BASE;
let sectorFiltro = 'todos';
let sectorData   = [];
let sonidoOn     = false;
let prevItemIds  = new Set();
let pollingTimer = null;
let timerReloj   = null;

/* ── RELOJ ── */
function startReloj() {
    function tick() {
        const n = new Date();
        document.getElementById('reloj').textContent =
            String(n.getHours()).padStart(2,'0') + ':' +
            String(n.getMinutes()).padStart(2,'0') + ':' +
            String(n.getSeconds()).padStart(2,'0');
    }
    tick();
    timerReloj = setInterval(tick, 1000);
}

/* ── POLLING ── */
async function poll() {
    try {
        const url = sectorFiltro === 'todos'
            ? `${BASE}/api/restaurant/cocina.php`
            : `${BASE}/api/restaurant/cocina.php?sector=${sectorFiltro}`;
        const r = await fetch(url);
        const d = await r.json();
        if (!d.success) return;

        // Detectar nuevos ítems
        const currentIds = new Set(d.data.items.map(i => i.id));
        if (prevItemIds.size > 0) {
            const nuevos = [...currentIds].filter(id => !prevItemIds.has(id));
            if (nuevos.length) {
                notificar(`${nuevos.length} nuevo${nuevos.length>1?'s':''} ítem${nuevos.length>1?'s':''}`);
                if (sonidoOn) beep();
            }
        }
        prevItemIds = currentIds;

        renderSectorFiltros(d.data.sectores_activos);
        renderBoard(d.data.items);
        actualizarContadores(d.data.items);

/* ── ACTUALIZAR REFRESH LABEL ── */
        const now = new Date();
        const hh  = String(now.getHours()).padStart(2,'0');
        const mm  = String(now.getMinutes()).padStart(2,'0');
        const ss  = String(now.getSeconds()).padStart(2,'0');
        document.getElementById('lastRefresh').innerHTML =
            `<span class="kds-refresh-dot"></span> Actualizado ${hh}:${mm}:${ss}`;    } catch(e) {
        console.error(e);
    }
}

function startPolling() {
    poll();
    pollingTimer = setInterval(poll, 10000);
}

/* ── FILTROS ── */
function renderSectorFiltros(sectores) {
    if (!sectores) return;
    sectorData = sectores;
    const cont = document.getElementById('sectorFiltros');
    cont.innerHTML = sectores.map(s => `
        <button class="kds-filter ${sectorFiltro===s.slug?'active':''}" data-sector="${s.slug}" onclick="filtrarSector('${s.slug}',this)">
            <span class="kds-filter-dot"></span>
            ${iconSector(s.slug)} ${s.nombre}
        </button>
    `).join('');
}

function filtrarSector(sec, el) {
    sectorFiltro = sec;
    document.querySelectorAll('.kds-filter').forEach(f => f.classList.remove('active'));
    el.classList.add('active');
    poll();
}

/* ── BOARD ── */
function renderBoard(items) {
    const board = document.getElementById('kdsBoard');
    if (!items || !items.length) {
        board.innerHTML = `
            <div class="kds-todo-vacio" style="width:100%;">
                <div class="kds-vacio-icon">✅</div>
                <h3>Todo al día</h3>
                <p>Sin ítems pendientes en cocina</p>
            </div>`;
        return;
    }

    // Agrupar por sector_cocina
    const grupos = {};
    items.forEach(item => {
        const sec = item.sector_cocina || 'principal';
        if (!grupos[sec]) grupos[sec] = [];
        grupos[sec].push(item);
    });

    board.innerHTML = Object.entries(grupos).map(([sec, secItems]) => {
        const pendientes = secItems.filter(i => i.estado_cocina === 'pendiente').length;
        const enPrep     = secItems.filter(i => i.estado_cocina === 'en_preparacion').length;
        const emoji      = emojiSector(sec);
        const badgeColor = pendientes > 0 ? 'var(--yellow)' : (enPrep > 0 ? 'var(--blue)' : 'var(--green)');

        return `<div class="kds-col">
            <div class="kds-col-header">
                <div class="kds-col-title">
                    <span class="kds-col-emoji">${emoji}</span>
                    <div>
                        <div class="kds-col-name">${nombreSector(sec)}</div>
                        <div class="kds-col-sub">${pendientes} pend. · ${enPrep} en prep.</div>
                    </div>
                </div>
                <div class="kds-col-badge" style="color:${badgeColor};">${secItems.length}</div>
            </div>
            ${secItems.map(item => renderTicket(item)).join('')}
        </div>`;
    }).join('');
}

function emojiSector(sec) {
    const map = {
        principal:'🍳', cocina:'🍳', parrilla:'🔥', grill:'🔥',
        barra:'🍺', bar:'🍺', bebidas:'🥤', fria:'🧊', postre:'🍰',
        pasteleria:'🍰', sushi:'🍣', pizza:'🍕', pasta:'🍝'
    };
    const k = (sec||'').toLowerCase();
    for (const [key, val] of Object.entries(map)) if (k.includes(key)) return val;
    return '🍽️';
}

function iconSector(slug) {
    const map = { parrilla:'🔥', barra:'🍺', bar:'🍺', bebidas:'🥤', fria:'🧊', postre:'🍰' };
    for (const [k,v] of Object.entries(map)) if ((slug||'').includes(k)) return v;
    return '';
}

function renderTicket(item) {
    const min      = item.minutos_espera || 0;
    const nivel    = min < 5 ? 'ok' : min < 10 ? 'medio' : min < 15 ? 'alto' : 'critico';
    const timerTxt = min < 60 ? `${min}m` : `${Math.floor(min/60)}h${min%60}m`;

    const cantBadge = item.cantidad > 1
        ? `<span class="kds-plato-cant">×${item.cantidad}</span>`
        : '';

    const btnPrep = item.estado_cocina === 'pendiente'
        ? `<button class="kds-btn kds-btn-prep" onclick="cambiarEstado(${item.id},'en_preparacion')">
               <i class="fas fa-fire-burner"></i> Preparando
           </button>`
        : '';

    const btnListo = item.estado_cocina !== 'listo'
        ? `<button class="kds-btn kds-btn-listo" onclick="cambiarEstado(${item.id},'listo')">
               <i class="fas fa-check"></i> Listo
           </button>`
        : `<div class="kds-btn-done">
               <i class="fas fa-bell-concierge"></i> Listo — esperando entrega
           </div>`;

    const obsHtml = item.observaciones
        ? `<div class="kds-obs">
               <i class="fas fa-circle-exclamation"></i>
               <span>${esc(item.observaciones)}</span>
           </div>`
        : '';

    const mozoHtml = item.mozo_nombre
        ? `<div class="kds-mozo">
               <i class="fas fa-user-tie"></i> ${esc(item.mozo_nombre)}
           </div>`
        : '';

    return `<div class="kds-ticket tiempo-${nivel}" id="ticket_${item.id}">
        <div class="kds-ticket-band"></div>
        <div class="kds-ticket-body">
            <div class="kds-ticket-head">
                <div class="kds-mesa-badge">
                    <span class="kds-mesa-num">Mesa ${esc(item.mesa_numero)}</span>
                    <span class="kds-comanda-num">#${item.comanda_numero}</span>
                </div>
                <div class="kds-timer">
                    <i class="fas fa-clock"></i> ${timerTxt}
                </div>
            </div>

            <div class="kds-plato">
                ${cantBadge}<span class="kds-plato-nombre">${esc(item.nombre_item)}</span>
            </div>

            ${obsHtml}
            ${mozoHtml}

            <div class="kds-divider"></div>
            <div class="kds-btns">
                ${btnPrep}
                ${btnListo}
            </div>
        </div>
    </div>`;
}

/* ── ACCIÓN ── */
async function cambiarEstado(itemId, nuevoEstado) {
    const el = document.getElementById(`ticket_${itemId}`);
    if (el) el.style.opacity = '.5';

    await fetch(`${BASE}/api/restaurant/cocina.php`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ item_id: itemId, estado: nuevoEstado })
    });
    await poll();
}

/* ── CONTADORES ── */
function actualizarContadores(items) {
    document.getElementById('cnt_pendiente').textContent  = items.filter(i=>i.estado_cocina==='pendiente').length;
    document.getElementById('cnt_preparacion').textContent= items.filter(i=>i.estado_cocina==='en_preparacion').length;
    document.getElementById('cnt_listo').textContent      = items.filter(i=>i.estado_cocina==='listo').length;
}

/* ── SONIDO ── */
function toggleSonido() {
    sonidoOn = !sonidoOn;
    const btn = document.getElementById('soundBtn');
    if (sonidoOn) {
        btn.classList.add('sound-on');
        btn.innerHTML = '<i class="fas fa-volume-high"></i>';
        btn.title = 'Sonido ON';
    } else {
        btn.classList.remove('sound-on');
        btn.innerHTML = '<i class="fas fa-volume-xmark"></i>';
        btn.title = 'Sonido OFF';
    }
}
function beep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain= ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 880;
        osc.type = 'sine';
        gain.gain.setValueAtTime(.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .4);
        osc.start(); osc.stop(ctx.currentTime + .4);
    } catch(e) {}
}

/* ── NOTIF ── */
function notificar(msg) {
    const n = document.createElement('div');
    n.className = 'kds-notif';
    n.innerHTML = `<span class="kds-notif-dot"></span> ${msg}`;
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}

/* ── UTILS ── */
const SECTOR_NOMBRES = {
    principal: 'Cocina Principal',
    parrilla:  'Parrilla',
    barra:     'Barra / Bebidas',
    fria:      'Postres / Fría',
};
function nombreSector(slug) { return SECTOR_NOMBRES[slug] || slug; }
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── INIT ── */
startReloj();
startPolling();
</script>
</body>
</html>
