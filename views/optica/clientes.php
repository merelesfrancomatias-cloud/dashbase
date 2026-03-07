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
    <title>Clientes — Óptica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <style>
        :root { --opt:#0ea5e9; --opt-light:rgba(14,165,233,.1); }

        /* ── Toolbar ── */
        .opt-toolbar {
            position:sticky; top:0; z-index:10;
            background:var(--surface); border-bottom:1px solid var(--border);
            padding:14px 24px; display:flex; align-items:center;
            justify-content:space-between; flex-wrap:wrap; gap:12px;
        }
        .opt-toolbar h1 { margin:0; font-size:20px; font-weight:700; color:var(--text-primary); }
        .opt-toolbar p  { margin:0; font-size:12px; color:var(--text-secondary); }

        /* ── Stats ── */
        .stats-bar { display:flex; gap:12px; padding:16px 24px 0; flex-wrap:wrap; }
        .stat-pill {
            display:flex; align-items:center; gap:8px;
            padding:8px 16px; border-radius:20px; font-size:13px; font-weight:600;
            border:1.5px solid transparent;
        }
        .stat-pill .dot { width:10px; height:10px; border-radius:50%; }
        .stat-pill.total    { background:var(--background); border-color:var(--border); color:var(--text-primary); }
        .stat-pill.activos  { background:var(--opt-light); border-color:var(--opt); color:#0369a1; }
        .stat-pill.listos   { background:rgba(15,209,134,.1); border-color:#0FD186; color:#059669; }
        .stat-pill.laborat  { background:rgba(245,158,11,.1); border-color:#f59e0b; color:#d97706; }

        /* ── Search bar ── */
        .search-bar {
            padding:14px 24px 0;
            display:flex; gap:10px; flex-wrap:wrap;
        }
        .search-input {
            flex:1; min-width:200px; padding:9px 14px 9px 36px;
            border:1.5px solid var(--border); border-radius:10px;
            font-size:14px; background:var(--surface); color:var(--text-primary);
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:10px center;
        }
        .search-input:focus { outline:none; border-color:var(--opt); }

        /* ── Tabla clientes ── */
        .table-wrap { padding:16px 24px; }
        .cli-table { width:100%; border-collapse:collapse; font-size:13px; }
        .cli-table th {
            text-align:left; padding:10px 12px; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.5px; color:var(--text-secondary);
            border-bottom:2px solid var(--border); white-space:nowrap;
        }
        .cli-table td { padding:12px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        .cli-table tr:hover td { background:var(--background); }
        .cli-table tr:last-child td { border-bottom:none; }

        .cli-avatar {
            width:36px; height:36px; border-radius:50%;
            background:var(--opt-light); color:var(--opt);
            display:inline-flex; align-items:center; justify-content:center;
            font-weight:800; font-size:13px; flex-shrink:0;
        }
        .cli-name { font-weight:600; color:var(--text-primary); }
        .cli-sub  { font-size:11px; color:var(--text-secondary); margin-top:1px; }

        .badge-receta {
            display:inline-flex; align-items:center; gap:4px;
            padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;
            background:var(--opt-light); color:var(--opt);
        }
        .badge-pedido {
            display:inline-flex; align-items:center; gap:4px;
            padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;
            background:rgba(245,158,11,.1); color:#d97706;
        }
        .badge-none { color:var(--text-secondary); font-size:11px; }

        .btn-icon { padding:5px 8px; border-radius:8px; border:1px solid var(--border);
            background:transparent; cursor:pointer; color:var(--text-secondary);
            transition:all .15s; font-size:13px; }
        .btn-icon:hover { background:var(--opt); color:#fff; border-color:var(--opt); }
        .btn-icon.danger:hover { background:#ef4444; border-color:#ef4444; }

        /* ── Modal ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:600px; max-height:92vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-box.wide { max-width:720px; }
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

        /* Receta grid */
        .receta-grid {
            display:grid; grid-template-columns:1fr 1fr; gap:16px;
            background:var(--background); border-radius:12px; padding:16px; margin-bottom:14px;
        }
        .receta-ojo-label {
            font-size:11px; font-weight:800; text-transform:uppercase;
            letter-spacing:.6px; color:var(--text-secondary); margin-bottom:10px;
            display:flex; align-items:center; gap:6px;
        }
        .receta-row { display:flex; gap:8px; margin-bottom:8px; }
        .receta-row .fi { text-align:center; }

        /* Ficha cliente modal */
        .ficha-section { margin-bottom:20px; }
        .ficha-section h4 { font-size:12px; font-weight:800; text-transform:uppercase;
            letter-spacing:.6px; color:var(--text-secondary); margin:0 0 12px;
            padding-bottom:8px; border-bottom:1px solid var(--border); }
        .receta-card {
            background:var(--background); border-radius:12px; padding:14px;
            margin-bottom:10px; border-left:3px solid var(--opt);
        }
        .receta-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
        .receta-card-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; font-size:12px; }
        .rec-field { background:var(--surface); border-radius:8px; padding:6px 10px; }
        .rec-field span { display:block; font-size:10px; color:var(--text-secondary); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
        .rec-field strong { color:var(--text-primary); font-size:13px; }

        /* Toast */
        .toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; white-space:nowrap; pointer-events:none; }
        .toast.show { opacity:1; }

        @media (max-width:700px) {
            .table-wrap { padding:12px; overflow-x:auto; }
            .cli-table { min-width:500px; }
            .stats-bar { padding:12px; }
            .opt-toolbar { padding:12px; }
            .receta-grid { grid-template-columns:1fr; }
            .receta-card-grid { grid-template-columns:repeat(2,1fr); }
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
                <h1><i class="fas fa-eye" style="color:var(--opt);margin-right:8px;"></i>Clientes</h1>
                <p id="subtitulo">Cargando…</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="pedidos.php" class="btn btn-secondary" style="text-decoration:none;">
                    <i class="fas fa-glasses"></i> Pedidos
                </a>
                <button class="btn btn-primary" onclick="abrirModalCliente()">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-pill total"><div class="dot" style="background:#64748b;"></div><span id="st-total">0</span> Clientes</div>
            <div class="stat-pill activos"><div class="dot" style="background:var(--opt);"></div><span id="st-activos">0</span> Con pedidos activos</div>
            <div class="stat-pill listos"><div class="dot" style="background:#0FD186;"></div><span id="st-listos">0</span> Listos p/entregar</div>
            <div class="stat-pill laborat"><div class="dot" style="background:#f59e0b;"></div><span id="st-lab">0</span> En laboratorio</div>
        </div>

        <!-- Search -->
        <div class="search-bar">
            <input class="search-input" type="text" id="buscar" placeholder="Buscar por nombre, apellido, DNI, teléfono…" oninput="filtrar()">
        </div>

        <!-- Contenido -->
        <div id="cliContent" class="table-wrap">
            <div style="text-align:center;padding:60px;color:var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Cliente -->
<div class="modal-overlay" id="modalCliente">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalCliTitulo"><i class="fas fa-user-plus" style="color:var(--opt);margin-right:8px;"></i>Nuevo Cliente</h3>
            <button class="modal-close" onclick="cerrarModalCliente()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cliId">
            <div class="fg-grid">
                <div class="fg">
                    <label>Nombre <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="text" id="cliNombre" placeholder="Juan">
                </div>
                <div class="fg">
                    <label>Apellido <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="text" id="cliApellido" placeholder="García">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>DNI / Documento</label>
                    <input class="fi" type="text" id="cliDni" placeholder="12.345.678">
                </div>
                <div class="fg">
                    <label>Teléfono</label>
                    <input class="fi" type="tel" id="cliTel" placeholder="+54 9 11 …">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Email</label>
                    <input class="fi" type="email" id="cliEmail" placeholder="juan@ejemplo.com">
                </div>
                <div class="fg">
                    <label>Fecha de nacimiento</label>
                    <input class="fi" type="date" id="cliFnac">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Obra social / Prepaga</label>
                    <input class="fi" type="text" id="cliOs" placeholder="OSDE, Swiss Medical…">
                </div>
                <div class="fg">
                    <label>Nro. de afiliado</label>
                    <input class="fi" type="text" id="cliNaf" placeholder="123456789">
                </div>
            </div>
            <div class="fg">
                <label>Observaciones</label>
                <textarea class="fi" id="cliObs" rows="2" placeholder="Notas del cliente…" style="resize:vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalCliente()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarCliente()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Nueva Receta -->
<div class="modal-overlay" id="modalReceta">
    <div class="modal-box wide">
        <div class="modal-header">
            <h3 id="modalRecTitulo"><i class="fas fa-file-medical" style="color:var(--opt);margin-right:8px;"></i>Nueva Receta</h3>
            <button class="modal-close" onclick="cerrarModalReceta()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="recId">
            <input type="hidden" id="recClienteId">
            <div class="fg-grid" style="margin-bottom:14px;">
                <div class="fg">
                    <label>Tipo de receta</label>
                    <select class="fi" id="recTipo">
                        <option value="lejos">Visión Lejos</option>
                        <option value="cerca">Visión Cerca</option>
                        <option value="progresivo">Progresivo</option>
                        <option value="solar">Solar</option>
                        <option value="contacto">Lentes de Contacto</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Fecha de emisión <span style="color:#ef4444;">*</span></label>
                    <input class="fi" type="date" id="recFecha">
                </div>
            </div>

            <!-- Grid OD / OI -->
            <div class="receta-grid">
                <!-- OD -->
                <div>
                    <div class="receta-ojo-label"><i class="fas fa-circle" style="color:var(--opt);font-size:8px;"></i>Ojo Derecho (OD)</div>
                    <div class="fg-grid" style="gap:8px;">
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Esfera</label>
                            <input class="fi" type="number" id="odEsfera" placeholder="0.00" step="0.25">
                        </div>
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Cilindro</label>
                            <input class="fi" type="number" id="odCilindro" placeholder="0.00" step="0.25">
                        </div>
                    </div>
                    <div class="fg-grid" style="gap:8px;margin-top:8px;">
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Eje (0-180°)</label>
                            <input class="fi" type="number" id="odEje" placeholder="0" min="0" max="180">
                        </div>
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Adición</label>
                            <input class="fi" type="number" id="odAdicion" placeholder="0.00" step="0.25">
                        </div>
                    </div>
                    <div class="fg" style="margin-top:8px;">
                        <label style="font-size:11px;">AV (Agudeza visual)</label>
                        <input class="fi" type="text" id="odAv" placeholder="20/20">
                    </div>
                </div>
                <!-- OI -->
                <div>
                    <div class="receta-ojo-label"><i class="fas fa-circle" style="color:#8b5cf6;font-size:8px;"></i>Ojo Izquierdo (OI)</div>
                    <div class="fg-grid" style="gap:8px;">
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Esfera</label>
                            <input class="fi" type="number" id="oiEsfera" placeholder="0.00" step="0.25">
                        </div>
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Cilindro</label>
                            <input class="fi" type="number" id="oiCilindro" placeholder="0.00" step="0.25">
                        </div>
                    </div>
                    <div class="fg-grid" style="gap:8px;margin-top:8px;">
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Eje (0-180°)</label>
                            <input class="fi" type="number" id="oiEje" placeholder="0" min="0" max="180">
                        </div>
                        <div class="fg" style="margin:0;">
                            <label style="font-size:11px;">Adición</label>
                            <input class="fi" type="number" id="oiAdicion" placeholder="0.00" step="0.25">
                        </div>
                    </div>
                    <div class="fg" style="margin-top:8px;">
                        <label style="font-size:11px;">AV (Agudeza visual)</label>
                        <input class="fi" type="text" id="oiAv" placeholder="20/20">
                    </div>
                </div>
            </div>

            <!-- DNP / Altura -->
            <div class="fg-grid3">
                <div class="fg">
                    <label>DNP Ojo Derecho</label>
                    <input class="fi" type="number" id="dnpOd" placeholder="32.0" step="0.5">
                </div>
                <div class="fg">
                    <label>DNP Ojo Izquierdo</label>
                    <input class="fi" type="number" id="dnpOi" placeholder="32.0" step="0.5">
                </div>
                <div class="fg">
                    <label>Altura montaje</label>
                    <input class="fi" type="number" id="recAltura" placeholder="20.0" step="0.5">
                </div>
            </div>
            <div class="fg-grid">
                <div class="fg">
                    <label>Médico / Oftalmólogo</label>
                    <input class="fi" type="text" id="recMedico" placeholder="Dr. García">
                </div>
                <div class="fg">
                    <label>Vencimiento receta</label>
                    <input class="fi" type="date" id="recVencimiento">
                </div>
            </div>
            <div class="fg">
                <label>Observaciones</label>
                <textarea class="fi" id="recObs" rows="2" style="resize:vertical;" placeholder="Notas adicionales…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalReceta()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarReceta()"><i class="fas fa-save"></i> Guardar Receta</button>
        </div>
    </div>
</div>

<!-- Modal Ficha Cliente -->
<div class="modal-overlay" id="modalFicha">
    <div class="modal-box wide">
        <div class="modal-header">
            <h3 id="fichaTitle"><i class="fas fa-user" style="color:var(--opt);margin-right:8px;"></i>Ficha del cliente</h3>
            <button class="modal-close" onclick="cerrarModalFicha()">✕</button>
        </div>
        <div class="modal-body" id="fichaBody">
            <div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--opt);"></i></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalFicha()">Cerrar</button>
            <button class="btn btn-secondary" id="btnNuevaPedidoFicha" onclick=""><i class="fas fa-glasses"></i> Nuevo Pedido</button>
            <button class="btn btn-primary" id="btnNuevaRecetaFicha" onclick=""><i class="fas fa-file-medical"></i> Nueva Receta</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE       = '<?= $base ?>';
const API_CLI    = BASE + '/api/optica/clientes.php';
const API_REC    = BASE + '/api/optica/recetas.php';

let clientes = [];
let todosClientes = [];

// ── Init ──────────────────────────────────────────────────────────────────────
async function init() {
    let r, j;
    try {
        r = await fetch(API_CLI, {credentials:'include'});
        j = await r.json();
    } catch(e) {
        document.getElementById('cliContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);">
            <i class="fas fa-plug" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i>
            <p style="font-weight:600;">Error de conexión</p><p style="font-size:13px;">No se pudo conectar con la API</p></div>`;
        return;
    }
    if (!j.success) {
        document.getElementById('cliContent').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-secondary);">
            <i class="fas fa-lock" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px;"></i>
            <p style="font-weight:600;">${j.message}</p>
            <a href="../../index.php" class="btn btn-primary" style="margin-top:12px;">Iniciar sesión</a></div>`;
        return;
    }
    todosClientes = clientes = j.data.clientes || [];
    const st = j.data.stats || {};
    document.getElementById('subtitulo').textContent = `${st.total || 0} clientes registrados`;
    document.getElementById('st-total').textContent  = st.total   || 0;
    document.getElementById('st-activos').textContent= st.pedidos_activos        || 0;
    document.getElementById('st-listos').textContent = st.listos_para_entregar   || 0;
    document.getElementById('st-lab').textContent    = st.en_laboratorio         || 0;
    renderClientes();
}

function filtrar() {
    const q = document.getElementById('buscar').value.toLowerCase().trim();
    clientes = q ? todosClientes.filter(c =>
        (c.nombre+' '+c.apellido).toLowerCase().includes(q) ||
        (c.dni||'').toLowerCase().includes(q) ||
        (c.telefono||'').toLowerCase().includes(q)
    ) : todosClientes;
    renderClientes();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderClientes() {
    const cont = document.getElementById('cliContent');
    if (!clientes.length) {
        cont.innerHTML = `<div style="text-align:center;padding:60px 24px;color:var(--text-secondary);">
            <i class="fas fa-users" style="font-size:48px;opacity:.12;display:block;margin-bottom:16px;"></i>
            <p style="font-size:16px;font-weight:600;margin-bottom:8px;">No hay clientes registrados</p>
            <p style="font-size:13px;margin-bottom:20px;">Agregá tu primer cliente para empezar</p>
            <button class="btn btn-primary" onclick="abrirModalCliente()"><i class="fas fa-user-plus"></i> Nuevo Cliente</button>
        </div>`;
        return;
    }
    let html = `<table class="cli-table">
        <thead><tr>
            <th>Cliente</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Obra Social</th>
            <th>Recetas</th>
            <th>Pedidos activos</th>
            <th>Última receta</th>
            <th></th>
        </tr></thead><tbody>`;
    clientes.forEach(c => {
        const ini = (c.nombre[0]+(c.apellido[0]||'')).toUpperCase();
        html += `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="cli-avatar">${ini}</div>
                    <div>
                        <div class="cli-name">${esc(c.apellido)}, ${esc(c.nombre)}</div>
                        <div class="cli-sub">${esc(c.email||'')}</div>
                    </div>
                </div>
            </td>
            <td>${esc(c.dni||'—')}</td>
            <td>${c.telefono ? `<a href="tel:${esc(c.telefono)}" style="color:var(--opt);text-decoration:none;">${esc(c.telefono)}</a>` : '—'}</td>
            <td>${c.obra_social ? `<span style="font-size:12px;">${esc(c.obra_social)}</span>` : '<span class="badge-none">—</span>'}</td>
            <td><span class="badge-receta"><i class="fas fa-file-medical"></i>${c.total_recetas||0}</span></td>
            <td>${(c.pedidos_activos > 0) ? `<span class="badge-pedido"><i class="fas fa-glasses"></i>${c.pedidos_activos}</span>` : '<span class="badge-none">—</span>'}</td>
            <td>${c.ultima_receta ? formatFecha(c.ultima_receta) : '<span class="badge-none">—</span>'}</td>
            <td>
                <div style="display:flex;gap:6px;justify-content:flex-end;">
                    <button class="btn-icon" onclick="verFicha(${c.id})" title="Ver ficha"><i class="fas fa-eye"></i></button>
                    <button class="btn-icon" onclick="editarCliente(${c.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon" onclick="nuevaReceta(${c.id})" title="Nueva receta"><i class="fas fa-file-medical"></i></button>
                    <button class="btn-icon danger" onclick="eliminarCliente(${c.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    html += '</tbody></table>';
    cont.innerHTML = html;
}

// ── Modal Cliente ─────────────────────────────────────────────────────────────
function abrirModalCliente(c = null) {
    document.getElementById('cliId').value      = c ? c.id : '';
    document.getElementById('cliNombre').value  = c ? c.nombre : '';
    document.getElementById('cliApellido').value= c ? c.apellido : '';
    document.getElementById('cliDni').value     = c ? (c.dni||'') : '';
    document.getElementById('cliTel').value     = c ? (c.telefono||'') : '';
    document.getElementById('cliEmail').value   = c ? (c.email||'') : '';
    document.getElementById('cliFnac').value    = c ? (c.fecha_nac||'') : '';
    document.getElementById('cliOs').value      = c ? (c.obra_social||'') : '';
    document.getElementById('cliNaf').value     = c ? (c.nro_afiliado||'') : '';
    document.getElementById('cliObs').value     = c ? (c.observaciones||'') : '';
    document.getElementById('modalCliTitulo').innerHTML = c
        ? `<i class="fas fa-user-edit" style="color:var(--opt);margin-right:8px;"></i>Editar — ${esc(c.nombre)} ${esc(c.apellido)}`
        : `<i class="fas fa-user-plus" style="color:var(--opt);margin-right:8px;"></i>Nuevo Cliente`;
    document.getElementById('modalCliente').classList.add('open');
    setTimeout(() => document.getElementById('cliNombre').focus(), 100);
}
function cerrarModalCliente() { document.getElementById('modalCliente').classList.remove('open'); }
function editarCliente(id) {
    const c = todosClientes.find(x => x.id == id);
    if (c) abrirModalCliente(c);
}
async function guardarCliente() {
    const id      = document.getElementById('cliId').value;
    const nombre  = document.getElementById('cliNombre').value.trim();
    const apellido= document.getElementById('cliApellido').value.trim();
    if (!nombre)   { toast('El nombre es requerido', 'error'); return; }
    if (!apellido) { toast('El apellido es requerido', 'error'); return; }
    const body = {
        nombre, apellido,
        dni:         document.getElementById('cliDni').value.trim()  || null,
        telefono:    document.getElementById('cliTel').value.trim()   || null,
        email:       document.getElementById('cliEmail').value.trim() || null,
        fecha_nac:   document.getElementById('cliFnac').value         || null,
        obra_social: document.getElementById('cliOs').value.trim()    || null,
        nro_afiliado:document.getElementById('cliNaf').value.trim()   || null,
        observaciones:document.getElementById('cliObs').value.trim()  || null,
    };
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_CLI}?id=${id}` : API_CLI;
    const r = await fetch(url, {method, credentials:'include', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalCliente(); toast(id ? 'Cliente actualizado ✓' : 'Cliente creado ✓'); init(); }
    else toast(j.message || 'Error al guardar', 'error');
}
async function eliminarCliente(id) {
    const c = todosClientes.find(x => x.id == id);
    if (!confirm(`¿Eliminar a ${c?.nombre} ${c?.apellido}?`)) return;
    const r = await fetch(`${API_CLI}?id=${id}`, {method:'DELETE', credentials:'include'});
    const j = await r.json();
    if (j.success) { toast('Cliente eliminado'); init(); }
    else toast(j.message || 'Error', 'error');
}

// ── Modal Receta ──────────────────────────────────────────────────────────────
function nuevaReceta(clienteId) {
    document.getElementById('recId').value        = '';
    document.getElementById('recClienteId').value = clienteId;
    document.getElementById('recTipo').value      = 'lejos';
    document.getElementById('recFecha').value     = new Date().toISOString().slice(0,10);
    document.getElementById('recVencimiento').value = '';
    document.getElementById('recMedico').value    = '';
    document.getElementById('recObs').value       = '';
    ['odEsfera','odCilindro','odEje','odAdicion','odAv',
     'oiEsfera','oiCilindro','oiEje','oiAdicion','oiAv',
     'dnpOd','dnpOi','recAltura'].forEach(id => document.getElementById(id).value = '');
    const c = todosClientes.find(x => x.id == clienteId);
    document.getElementById('modalRecTitulo').innerHTML =
        `<i class="fas fa-file-medical" style="color:var(--opt);margin-right:8px;"></i>Nueva Receta — ${c ? esc(c.nombre+' '+c.apellido) : ''}`;
    document.getElementById('modalReceta').classList.add('open');
}
function cerrarModalReceta() { document.getElementById('modalReceta').classList.remove('open'); }
async function guardarReceta() {
    const fecha = document.getElementById('recFecha').value;
    if (!fecha) { toast('La fecha es requerida', 'error'); return; }
    const body = {
        cliente_id:        parseInt(document.getElementById('recClienteId').value),
        tipo:              document.getElementById('recTipo').value,
        fecha_emision:     fecha,
        fecha_vencimiento: document.getElementById('recVencimiento').value || null,
        medico:            document.getElementById('recMedico').value.trim() || null,
        observaciones:     document.getElementById('recObs').value.trim() || null,
        od_esfera:   parseNum('odEsfera'),  od_cilindro: parseNum('odCilindro'),
        od_eje:      parseInt2('odEje'),    od_adicion:  parseNum('odAdicion'),
        od_av:       document.getElementById('odAv').value.trim() || null,
        oi_esfera:   parseNum('oiEsfera'),  oi_cilindro: parseNum('oiCilindro'),
        oi_eje:      parseInt2('oiEje'),    oi_adicion:  parseNum('oiAdicion'),
        oi_av:       document.getElementById('oiAv').value.trim() || null,
        dnp_od:      parseNum('dnpOd'),     dnp_oi:      parseNum('dnpOi'),
        altura:      parseNum('recAltura'),
    };
    const id  = document.getElementById('recId').value;
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_REC}?id=${id}` : API_REC;
    const r = await fetch(url, {method, credentials:'include', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
    const j = await r.json();
    if (j.success) { cerrarModalReceta(); toast('Receta guardada ✓'); init(); }
    else toast(j.message || 'Error', 'error');
}

// ── Ficha Cliente ─────────────────────────────────────────────────────────────
async function verFicha(id) {
    document.getElementById('fichaBody').innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--opt);"></i></div>';
    document.getElementById('modalFicha').classList.add('open');
    const r = await fetch(`${API_CLI}?id=${id}`, {credentials:'include'});
    const j = await r.json();
    if (!j.success) { document.getElementById('fichaBody').innerHTML = '<p>Error al cargar</p>'; return; }
    const c = j.data;
    document.getElementById('fichaTitle').innerHTML =
        `<i class="fas fa-user" style="color:var(--opt);margin-right:8px;"></i>${esc(c.apellido)}, ${esc(c.nombre)}`;
    document.getElementById('btnNuevaRecetaFicha').onclick = () => { cerrarModalFicha(); nuevaReceta(id); };
    document.getElementById('btnNuevaPedidoFicha').onclick = () => { window.location.href='pedidos.php?cliente='+id; };

    const edad = c.fecha_nac ? calcEdad(c.fecha_nac) : null;
    let html = `<div class="ficha-section">
        <h4><i class="fas fa-id-card" style="margin-right:6px;"></i>Datos personales</h4>
        <div class="fg-grid3" style="font-size:13px;">
            <div><span style="color:var(--text-secondary);font-size:11px;">DNI</span><br><strong>${esc(c.dni||'—')}</strong></div>
            <div><span style="color:var(--text-secondary);font-size:11px;">Teléfono</span><br><strong>${esc(c.telefono||'—')}</strong></div>
            <div><span style="color:var(--text-secondary);font-size:11px;">Email</span><br><strong>${esc(c.email||'—')}</strong></div>
            <div><span style="color:var(--text-secondary);font-size:11px;">Nacimiento</span><br><strong>${c.fecha_nac ? formatFecha(c.fecha_nac)+(edad?` (${edad} años)`:'') : '—'}</strong></div>
            <div><span style="color:var(--text-secondary);font-size:11px;">Obra social</span><br><strong>${esc(c.obra_social||'—')}</strong></div>
            <div><span style="color:var(--text-secondary);font-size:11px;">Nro. afiliado</span><br><strong>${esc(c.nro_afiliado||'—')}</strong></div>
        </div>
        ${c.observaciones ? `<div style="margin-top:12px;padding:10px;background:var(--background);border-radius:8px;font-size:13px;color:var(--text-secondary);">${esc(c.observaciones)}</div>` : ''}
    </div>`;

    // Recetas
    html += `<div class="ficha-section">
        <h4><i class="fas fa-file-medical" style="margin-right:6px;"></i>Recetas (${c.recetas?.length || 0})</h4>`;
    if (!c.recetas?.length) {
        html += '<p style="color:var(--text-secondary);font-size:13px;">Sin recetas registradas.</p>';
    } else {
        c.recetas.forEach(rec => {
            const tipo = {lejos:'Lejos',cerca:'Cerca',progresivo:'Progresivo',solar:'Solar',contacto:'Contacto'}[rec.tipo]||rec.tipo;
            html += `<div class="receta-card">
                <div class="receta-card-header">
                    <span style="font-weight:700;font-size:13px;color:var(--opt);">${tipo}</span>
                    <span style="font-size:12px;color:var(--text-secondary);">${formatFecha(rec.fecha_emision)}${rec.medico?` · Dr. ${esc(rec.medico)}`:''}</span>
                </div>
                <div class="receta-card-grid">
                    <div class="rec-field"><span>OD Esf</span><strong>${fmtOpt(rec.od_esfera)}</strong></div>
                    <div class="rec-field"><span>OD Cil</span><strong>${fmtOpt(rec.od_cilindro)}</strong></div>
                    <div class="rec-field"><span>OD Eje</span><strong>${rec.od_eje ?? '—'}°</strong></div>
                    <div class="rec-field"><span>OD Add</span><strong>${fmtOpt(rec.od_adicion)}</strong></div>
                    <div class="rec-field"><span>OI Esf</span><strong>${fmtOpt(rec.oi_esfera)}</strong></div>
                    <div class="rec-field"><span>OI Cil</span><strong>${fmtOpt(rec.oi_cilindro)}</strong></div>
                    <div class="rec-field"><span>OI Eje</span><strong>${rec.oi_eje ?? '—'}°</strong></div>
                    <div class="rec-field"><span>OI Add</span><strong>${fmtOpt(rec.oi_adicion)}</strong></div>
                </div>
                ${rec.dnp_od||rec.dnp_oi||rec.altura?`<div style="margin-top:8px;font-size:12px;color:var(--text-secondary);">DNP OD: ${rec.dnp_od??'—'} · DNP OI: ${rec.dnp_oi??'—'} · Altura: ${rec.altura??'—'}</div>`:''}
                ${rec.observaciones?`<div style="margin-top:6px;font-size:12px;color:var(--text-secondary);">${esc(rec.observaciones)}</div>`:''}
            </div>`;
        });
    }
    html += '</div>';

    // Pedidos recientes
    html += `<div class="ficha-section">
        <h4><i class="fas fa-glasses" style="margin-right:6px;"></i>Pedidos recientes (${c.pedidos?.length || 0})</h4>`;
    if (!c.pedidos?.length) {
        html += '<p style="color:var(--text-secondary);font-size:13px;">Sin pedidos registrados.</p>';
    } else {
        c.pedidos.forEach(p => {
            const estadoInfo = estadoPedidoInfo(p.estado);
            html += `<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--background);border-radius:10px;margin-bottom:8px;font-size:13px;">
                <div>
                    <span style="font-weight:600;">${esc(p.armazon||'Sin armazón')}</span>
                    <span style="color:var(--text-secondary);font-size:11px;margin-left:8px;">${formatFecha(p.created_at?.slice(0,10))}</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-weight:700;">${fmt(p.total)}</span>
                    <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:${estadoInfo.bg};color:${estadoInfo.color};">${estadoInfo.label}</span>
                </div>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('fichaBody').innerHTML = html;
}
function cerrarModalFicha() { document.getElementById('modalFicha').classList.remove('open'); }

// ── Helpers ───────────────────────────────────────────────────────────────────
function estadoPedidoInfo(e) {
    const map = {
        presupuesto: {label:'Presupuesto', bg:'rgba(100,116,139,.15)', color:'#475569'},
        pendiente:   {label:'Pendiente',   bg:'rgba(245,158,11,.15)',  color:'#d97706'},
        laboratorio: {label:'Laboratorio', bg:'rgba(14,165,233,.15)',  color:'#0369a1'},
        listo:       {label:'Listo ✓',     bg:'rgba(15,209,134,.15)',  color:'#059669'},
        entregado:   {label:'Entregado',   bg:'rgba(99,102,241,.15)',  color:'#4f46e5'},
        cancelado:   {label:'Cancelado',   bg:'rgba(239,68,68,.15)',   color:'#dc2626'},
    };
    return map[e] || {label:e, bg:'var(--background)', color:'var(--text-secondary)'};
}
function fmtOpt(v) {
    if (v === null || v === undefined || v === '') return '—';
    const n = parseFloat(v);
    return (n >= 0 ? '+' : '') + n.toFixed(2);
}
function parseNum(id) {
    const v = document.getElementById(id)?.value;
    return (v !== '' && v !== null && v !== undefined) ? parseFloat(v) : null;
}
function parseInt2(id) {
    const v = document.getElementById(id)?.value;
    return (v !== '' && v !== null && v !== undefined) ? parseInt(v) : null;
}
function calcEdad(fnac) {
    const hoy = new Date(); const n = new Date(fnac);
    let edad = hoy.getFullYear() - n.getFullYear();
    if (hoy < new Date(hoy.getFullYear(), n.getMonth(), n.getDate())) edad--;
    return edad;
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

['modalCliente','modalReceta','modalFicha'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('open');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { cerrarModalCliente(); cerrarModalReceta(); cerrarModalFicha(); }
});

init();
</script>
</body>
</html>
