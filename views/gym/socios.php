<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socios - Gimnasio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        .badge-estado { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600; }
        .badge-activo    { background:rgba(72,187,120,.15);color:#22c55e; }
        .badge-vencido   { background:rgba(245,101,101,.15);color:#ef4444; }
        .badge-suspendido{ background:rgba(246,173,85,.15);color:#f59e0b; }
        .badge-inactivo  { background:rgba(160,174,192,.15);color:#94a3b8; }
        .dias-ok   { background:rgba(72,187,120,.12);color:#22c55e;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:600; }
        .dias-warn { background:rgba(246,173,85,.15);color:#f59e0b;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:600; }
        .dias-venc { background:rgba(245,101,101,.12);color:#ef4444;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:600; }
        .filter-tabs { display:flex;gap:6px;flex-wrap:wrap; }
        .filter-tab { padding:7px 14px;border-radius:20px;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;font-size:13px;font-family:inherit;transition:var(--transition); }
        .filter-tab.active,.filter-tab:hover { background:var(--primary);color:#fff;border-color:var(--primary); }
        .table-wrap { overflow-x:auto; }
        table { width:100%;border-collapse:collapse;font-size:14px; }
        th { text-align:left;padding:11px 16px;background:var(--background);color:var(--text-secondary);font-weight:600;border-bottom:1px solid var(--border);white-space:nowrap;font-size:12px;text-transform:uppercase;letter-spacing:.5px; }
        td { padding:13px 16px;border-bottom:1px solid var(--border);color:var(--text-primary);vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:var(--background); }
        .action-btns { display:flex;gap:6px; }
        .btn-table { width:34px;height:34px;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:var(--transition); }
        .btn-asist { background:rgba(249,115,22,.1);color:#f97316; }
        .btn-asist:hover { background:#f97316;color:#fff; }
        .btn-renew { background:rgba(72,187,120,.1);color:#22c55e; }
        .btn-renew:hover { background:#22c55e;color:#fff; }
        .btn-edit  { background:rgba(66,153,225,.1);color:#4299e1; }
        .btn-edit:hover  { background:#4299e1;color:#fff; }
        .btn-qr    { background:rgba(99,102,241,.1);color:#6366f1; }
        .btn-qr:hover    { background:#6366f1;color:#fff; }
        /* Modal QR */
        .qr-wrap { display:inline-block;padding:14px;background:#fff;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.12); }
        .qr-url  { margin-top:14px;background:rgba(0,0,0,.04);border-radius:8px;padding:8px 12px;font-size:11px;color:var(--text-secondary);word-break:break-all;text-align:left; }
        /* Banner por vencer */
        .vencer-banner { background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:14px;padding:16px 20px;margin-bottom:24px; }
        .vencer-banner-title { font-size:13px;font-weight:700;color:#d97706;margin-bottom:12px;display:flex;align-items:center;gap:7px; }
        .vencer-item { display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(245,158,11,.1);font-size:13px; }
        .vencer-item:last-child { border-bottom:none; }
        .wa-btn { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(37,211,102,.12);color:#22c55e;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap; }
        .wa-btn:hover { background:#22c55e;color:#fff; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface);border-radius:16px;width:100%;max-width:560px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);border:1px solid var(--border); }
        .modal-header { padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
        .modal-header h3 { margin:0;font-size:17px;color:var(--text-primary); }
        .modal-close { background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:18px;padding:4px; }
        .modal-close:hover { color:var(--error); }
        .modal-body { padding:24px; }
        .modal-footer { padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end; }
        .form-2col { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
        .form-2col .full { grid-column:1/-1; }
        .pago-section { margin-top:16px;padding-top:16px;border-top:1px solid var(--border); }
        .pago-section h4 { margin:0 0 12px;font-size:13px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.5px; }
        .empty-state { text-align:center;padding:60px 20px;color:var(--text-secondary); }
        .empty-state i { font-size:44px;margin-bottom:14px;display:block;opacity:.4; }
        .search-bar { position:relative; }
        .search-bar input { padding-left:40px; }
        .search-bar i { position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:14px; }
        .toast { position:fixed;bottom:24px;right:24px;z-index:9999;background:var(--surface);color:var(--text-primary);border-radius:12px;padding:14px 20px;box-shadow:0 8px 30px rgba(0,0,0,.15);display:none;align-items:center;gap:12px;max-width:320px;border:1px solid var(--border);border-left:4px solid var(--primary); }
        .toast.show { display:flex; }
        .toast.error { border-left-color:var(--error); }
        body.dark-mode th { background:rgba(255,255,255,.03); }
        body.dark-mode tr:hover td { background:rgba(255,255,255,.03); }
        body.dark-mode .filter-tab { border-color:rgba(255,255,255,.1); }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="container">

            <div class="page-header" style="background:var(--surface);padding:22px 24px;border-radius:14px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,.07);border:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:48px;height:48px;background:rgba(249,115,22,.12);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#f97316;">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text-primary);">Socios</h1>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary);">Gestión de membresías y estados</p>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="abrirModalNuevo()">
                    <i class="fas fa-plus"></i> Nuevo Socio
                </button>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:20px;margin-bottom:24px;">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div class="stat-info"><p class="stat-label">Total socios</p><h3 class="stat-value" id="statTotal">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info"><p class="stat-label">Activos</p><h3 class="stat-value" id="statActivos">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-info"><p class="stat-label">Vencidos</p><h3 class="stat-value" id="statVencidos">—</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                    <div class="stat-info"><p class="stat-label">Por vencer</p><h3 class="stat-value" id="statPorVencer">—</h3></div>
                </div>
            </div>

            <!-- Banner socios por vencer -->
            <div class="vencer-banner" id="bannerVencer" style="display:none;">
                <div class="vencer-banner-title"><i class="fas fa-triangle-exclamation"></i> Socios que vencen en los próximos 7 días</div>
                <div id="vencerLista"></div>
            </div>

            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="setFiltro('',this)">Todos</button>
                        <button class="filter-tab" onclick="setFiltro('activo',this)">Activos</button>
                        <button class="filter-tab" onclick="setFiltro('vencido',this)">Vencidos</button>
                        <button class="filter-tab" onclick="setFiltro('suspendido',this)">Suspendidos</button>
                    </div>
                    <div class="search-bar" style="min-width:220px;">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar socio..." oninput="onSearch(this.value)" style="margin:0;">
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <div id="loadingState" style="text-align:center;padding:40px;color:var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    <div class="table-wrap">
                        <table id="sociosTable" style="display:none;">
                            <thead><tr>
                                <th>#</th><th>Socio</th><th>Teléfono</th>
                                <th>Plan</th><th>Estado</th><th>Vencimiento</th><th>QR</th><th>Acciones</th>
                            </tr></thead>
                            <tbody id="sociosTbody"></tbody>
                        </table>
                    </div>
                    <div id="emptyState" class="empty-state" style="display:none;">
                        <i class="fas fa-users-slash"></i><p>No se encontraron socios</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Socio -->
<div class="modal-overlay" id="modalSocio">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nuevo Socio</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="socioId">
            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="fNombre">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" id="fApellido">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="fEmail">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="fTelefono">
                </div>
                <div class="form-group">
                    <label class="form-label">Plan</label>
                    <select class="form-control" id="fPlan"></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" class="form-control" id="fFechaInicio">
                </div>
                <div class="form-group full" id="estadoGroup" style="display:none;">
                    <label class="form-label">Estado</label>
                    <select class="form-control" id="fEstado">
                        <option value="activo">Activo</option>
                        <option value="vencido">Vencido</option>
                        <option value="suspendido">Suspendido</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Notas</label>
                    <textarea class="form-control" id="fNotas" rows="2" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="pago-section" id="pagoSection">
                <h4><i class="fas fa-dollar-sign" style="color:var(--primary);margin-right:6px;"></i> Pago inicial (opcional)</h4>
                <div class="form-2col">
                    <div class="form-group">
                        <label class="form-label">Monto</label>
                        <input type="number" class="form-control" id="fMonto" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Método</label>
                        <select class="form-control" id="fMetodo">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarSocio()"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Renovar -->
<div class="modal-overlay" id="modalRenovar">
    <div class="modal">
        <div class="modal-header">
            <h3>Renovar Membresía</h3>
            <button class="modal-close" onclick="cerrarModalRenovar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="renovarSocioId">
            <p id="renovarSocioNombre" style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:16px;"></p>
            <div class="form-2col">
                <div class="form-group">
                    <label class="form-label">Plan</label>
                    <select class="form-control" id="rPlan"></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" class="form-control" id="rFechaInicio">
                </div>
                <div class="form-group">
                    <label class="form-label">Monto *</label>
                    <input type="number" class="form-control" id="rMonto" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Método</label>
                    <select class="form-control" id="rMetodo">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalRenovar()">Cancelar</button>
            <button class="btn btn-primary" onclick="confirmarRenovar()"><i class="fas fa-rotate"></i> Renovar</button>
        </div>
    </div>
</div>

<!-- Modal QR -->
<div class="modal-overlay" id="modalQR">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <h3><i class="fas fa-qrcode" style="color:#6366f1;margin-right:8px;"></i>QR Check-in</h3>
            <button class="modal-close" onclick="cerrarModalQR()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <p id="qrSocioNombre" style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;"></p>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:20px;">El socio escanea este código para hacer check-in</p>
            <div class="qr-wrap"><div id="qrCanvas"></div></div>
            <div class="qr-url" id="qrUrlText"></div>
        </div>
        <div class="modal-footer" style="justify-content:center;gap:10px;">
            <button class="btn btn-secondary" onclick="descargarQR()"><i class="fas fa-download"></i> Descargar PNG</button>
            <button class="btn btn-secondary" onclick="imprimirQR()"><i class="fas fa-print"></i> Imprimir</button>
            <button class="btn btn-secondary" onclick="cerrarModalQR()">Cerrar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas fa-check-circle" style="color:var(--primary);"></i><span id="toastMsg"></span></div>

<script>
let socios = [], planes = [], filtroEstado = '', searchTimeout;
const BASE = '/DASHBASE';

async function init() {
    await cargarPlanes();
    cargarSocios();
}

async function cargarPlanes() {
    try {
        const r = await fetch('../../api/gym/planes.php');
        const d = await r.json();
        if (d.success) {
            planes = d.data;
            const opts = '<option value="">Sin plan</option>' +
                planes.map(p => `<option value="${p.id}" data-precio="${p.precio}">${p.nombre} — $${parseFloat(p.precio).toLocaleString('es-AR')}</option>`).join('');
            document.getElementById('fPlan').innerHTML = opts;
            document.getElementById('rPlan').innerHTML = opts;
        }
    } catch(e) {}
}

async function cargarSocios(q = '') {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('sociosTable').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    try {
        let url = `../../api/gym/socios.php?estado=${filtroEstado}`;
        if (q) url += `&q=${encodeURIComponent(q)}`;
        const r = await fetch(url);
        const d = await r.json();
        document.getElementById('loadingState').style.display = 'none';
        if (d.success) {
            socios = d.data.socios || [];
            const st = d.data.stats || {};
            document.getElementById('statTotal').textContent    = st.total    || 0;
            document.getElementById('statActivos').textContent  = st.activos  || 0;
            document.getElementById('statVencidos').textContent = st.vencidos || 0;
            document.getElementById('statPorVencer').textContent= st.por_vencer || 0;
            renderTabla(socios);
        }
    } catch(e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
    }
}

function renderTabla(lista) {
    if (!lista.length) { document.getElementById('emptyState').style.display = 'block'; return; }
    document.getElementById('sociosTable').style.display = 'table';
    const badges = {
        activo:    '<span class="badge-estado badge-activo">● Activo</span>',
        vencido:   '<span class="badge-estado badge-vencido">● Vencido</span>',
        suspendido:'<span class="badge-estado badge-suspendido">● Suspendido</span>',
        inactivo:  '<span class="badge-estado badge-inactivo">● Inactivo</span>',
    };
    document.getElementById('sociosTbody').innerHTML = lista.map((s, i) => {
        let diasBadge = '—';
        if (s.fecha_vencimiento) {
            const dias = parseInt(s.dias_restantes);
            if (dias < 0)     diasBadge = `<span class="dias-venc">Vencido</span>`;
            else if (dias<=7) diasBadge = `<span class="dias-warn">${dias}d</span>`;
            else              diasBadge = `<span class="dias-ok">${dias}d</span>`;
        }
        return `<tr>
            <td style="color:var(--text-secondary);font-size:13px;">${i+1}</td>
            <td>
                <div style="font-weight:600;">${s.nombre} ${s.apellido}</div>
                <div style="font-size:12px;color:var(--text-secondary);">${s.email||'—'}</div>
            </td>
            <td>${s.telefono||'—'}</td>
            <td>${s.plan_nombre?`<span style="color:var(--primary);font-weight:600;">${s.plan_nombre}</span>`:'<span style="color:var(--text-secondary);">Sin plan</span>'}</td>
            <td>${badges[s.estado]||s.estado}</td>
            <td>${s.fecha_vencimiento?`<div style="font-size:13px;">${s.fecha_vencimiento}</div><div>${diasBadge}</div>`:'—'}</td>
            <td>
                ${s.qr_token
                    ? `<button class="btn-table btn-qr" title="Ver QR" onclick="abrirQR(${s.id})"><i class="fas fa-qrcode"></i></button>`
                    : '<span style="color:var(--text-secondary);font-size:11px;">—</span>'}
            </td>
            <td>
                <div class="action-btns">
                    <button class="btn-table btn-asist" title="Check-in" onclick="regAsist(${s.id},'${s.nombre} ${s.apellido}')"><i class="fas fa-fingerprint"></i></button>
                    <button class="btn-table btn-renew" title="Renovar"  onclick="abrirRenovar(${s.id},'${s.nombre} ${s.apellido}',${s.plan_id||0})"><i class="fas fa-rotate"></i></button>
                    <button class="btn-table btn-edit"  title="Editar"   onclick="abrirEditar(${s.id})"><i class="fas fa-pen"></i></button>
                </div>
            </td>
        </tr>`;
    }).join('');

    // Banner por vencer (dentro de 7 días)
    const porVencer = socios.filter(s => s.estado === 'activo' && s.dias_restantes !== null && parseInt(s.dias_restantes) >= 0 && parseInt(s.dias_restantes) <= 7);
    const banner = document.getElementById('bannerVencer');
    if (porVencer.length) {
        document.getElementById('vencerLista').innerHTML = porVencer.map(s => {
            const tel = (s.telefono || '').replace(/\D/g,'');
            const waLink = tel ? `<a class="wa-btn" href="https://wa.me/${tel}?text=${encodeURIComponent('Hola '+s.nombre+'! Tu membresía vence el '+s.fecha_vencimiento+'. ¿Querés renovar?')}" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>` : '';
            return `<div class="vencer-item">
                <div>
                    <span style="font-weight:600;color:var(--text-primary);">${s.nombre} ${s.apellido}</span>
                    <span style="color:var(--text-secondary);margin-left:8px;font-size:12px;">${s.plan_nombre||''}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:12px;font-weight:700;color:#d97706;">Vence: ${s.fecha_vencimiento}</span>
                    ${waLink}
                </div>
            </div>`;
        }).join('');
        banner.style.display = 'block';
    } else {
        banner.style.display = 'none';
    }
}

function onSearch(v) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => cargarSocios(v), 400);
}

function setFiltro(e, btn) {
    filtroEstado = e;
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cargarSocios(document.getElementById('searchInput').value);
}

function abrirModalNuevo() {
    document.getElementById('modalTitle').textContent = 'Nuevo Socio';
    document.getElementById('socioId').value = '';
    ['fNombre','fApellido','fEmail','fTelefono','fNotas','fMonto'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fPlan').value = '';
    document.getElementById('fFechaInicio').value = new Date().toISOString().split('T')[0];
    document.getElementById('estadoGroup').style.display = 'none';
    document.getElementById('pagoSection').style.display = 'block';
    document.getElementById('modalSocio').classList.add('open');
}

function abrirEditar(id) {
    const s = socios.find(x => x.id == id);
    if (!s) return;
    document.getElementById('modalTitle').textContent = 'Editar Socio';
    document.getElementById('socioId').value  = s.id;
    document.getElementById('fNombre').value  = s.nombre;
    document.getElementById('fApellido').value= s.apellido;
    document.getElementById('fEmail').value   = s.email || '';
    document.getElementById('fTelefono').value= s.telefono || '';
    document.getElementById('fPlan').value    = s.plan_id || '';
    document.getElementById('fFechaInicio').value = s.fecha_inicio || '';
    document.getElementById('fNotas').value   = s.notas || '';
    document.getElementById('fEstado').value  = s.estado;
    document.getElementById('estadoGroup').style.display = 'block';
    document.getElementById('pagoSection').style.display = 'none';
    document.getElementById('modalSocio').classList.add('open');
}

function cerrarModal() { document.getElementById('modalSocio').classList.remove('open'); }

async function guardarSocio() {
    const id      = document.getElementById('socioId').value;
    const nombre  = document.getElementById('fNombre').value.trim();
    const apellido= document.getElementById('fApellido').value.trim();
    if (!nombre || !apellido) { showToast('Nombre y apellido son requeridos', true); return; }
    const body = {
        nombre, apellido,
        email:        document.getElementById('fEmail').value.trim(),
        telefono:     document.getElementById('fTelefono').value.trim(),
        plan_id:      document.getElementById('fPlan').value || null,
        fecha_inicio: document.getElementById('fFechaInicio').value,
        notas:        document.getElementById('fNotas').value.trim(),
    };
    if (id) {
        body.id     = id;
        body.estado = document.getElementById('fEstado').value;
    } else {
        const monto = parseFloat(document.getElementById('fMonto').value);
        if (monto > 0) { body.monto = monto; body.metodo = document.getElementById('fMetodo').value; }
    }
    try {
        const r = await fetch('../../api/gym/socios.php', {method: id?'PUT':'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
        const d = await r.json();
        if (d.success) { showToast(id?'Socio actualizado':'Socio creado'); cerrarModal(); cargarSocios(document.getElementById('searchInput').value); }
        else showToast(d.message||'Error al guardar', true);
    } catch(e) { showToast('Error de conexión', true); }
}

function abrirRenovar(id, nombre, planId) {
    document.getElementById('renovarSocioId').value = id;
    document.getElementById('renovarSocioNombre').textContent = nombre;
    document.getElementById('rPlan').value = planId || '';
    document.getElementById('rFechaInicio').value = new Date().toISOString().split('T')[0];
    document.getElementById('rMonto').value = '';
    if (planId) {
        const opt = document.querySelector(`#rPlan option[value="${planId}"]`);
        if (opt && opt.dataset.precio) document.getElementById('rMonto').value = opt.dataset.precio;
    }
    document.getElementById('modalRenovar').classList.add('open');
}

document.getElementById('rPlan').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt && opt.dataset.precio) document.getElementById('rMonto').value = opt.dataset.precio;
});

function cerrarModalRenovar() { document.getElementById('modalRenovar').classList.remove('open'); }

async function confirmarRenovar() {
    const socio_id = document.getElementById('renovarSocioId').value;
    const plan_id  = document.getElementById('rPlan').value;
    const monto    = parseFloat(document.getElementById('rMonto').value);
    const fecha    = document.getElementById('rFechaInicio').value;
    const metodo   = document.getElementById('rMetodo').value;
    if (!monto || monto <= 0) { showToast('Ingresá el monto', true); return; }
    try {
        const r = await fetch('../../api/gym/pagos.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({socio_id, plan_id, monto, fecha, metodo, periodo_desde: fecha})});
        const d = await r.json();
        if (d.success) { showToast('Membresía renovada'); cerrarModalRenovar(); cargarSocios(document.getElementById('searchInput').value); }
        else showToast(d.message||'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

async function regAsist(socio_id, nombre) {
    const hora = new Date().toTimeString().substring(0, 5);
    try {
        const r = await fetch('../../api/gym/asistencias.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({socio_id, fecha: new Date().toISOString().split('T')[0], hora})});
        const d = await r.json();
        if (d.success) showToast('Check-in: ' + nombre);
        else showToast(d.message||'Error', true);
    } catch(e) { showToast('Error de conexión', true); }
}

function showToast(msg, error = false) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.className = 'toast show' + (error ? ' error' : '');
    setTimeout(() => t.classList.remove('show'), 3500);
}

// ── QR Check-in ──────────────────────────────────────────────────────────────
let qrInstance = null;

function abrirQR(id) {
    const s = socios.find(x => x.id == id);
    if (!s || !s.qr_token) return;

    const url = `${location.origin}${BASE}/views/gym/checkin.php?token=${s.qr_token}`;
    document.getElementById('qrSocioNombre').textContent = `${s.nombre} ${s.apellido}`;
    document.getElementById('qrUrlText').textContent = url;

    // Limpiar QR anterior
    const canvas = document.getElementById('qrCanvas');
    canvas.innerHTML = '';
    qrInstance = new QRCode(canvas, {
        text: url, width: 200, height: 200,
        colorDark: '#0f172a', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    document.getElementById('modalQR').classList.add('open');
}

function cerrarModalQR() { document.getElementById('modalQR').classList.remove('open'); }

function descargarQR() {
    const c = document.querySelector('#qrCanvas canvas');
    if (!c) return;
    const a = document.createElement('a');
    a.download = 'qr-checkin.png';
    a.href = c.toDataURL('image/png');
    a.click();
}

function imprimirQR() {
    const c = document.querySelector('#qrCanvas canvas');
    const nombre = document.getElementById('qrSocioNombre').textContent;
    if (!c) return;
    const w = window.open('', '_blank', 'width=400,height=500');
    w.document.write(`<!DOCTYPE html><html><head><title>QR Check-in</title>
        <style>body{font-family:sans-serif;text-align:center;padding:30px;}h2{margin-bottom:8px;}p{color:#64748b;font-size:13px;margin-bottom:20px;}@media print{button{display:none;}}</style>
        </head><body>
        <h2>${nombre}</h2><p>Escaneá para registrar tu entrada</p>
        <img src="${c.toDataURL()}" width="200" height="200"><br>
        <button onclick="window.print()" style="margin-top:20px;padding:8px 20px;cursor:pointer;">Imprimir</button>
        </body></html>`);
    w.document.close();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') { cerrarModal(); cerrarModalRenovar(); cerrarModalQR(); } });
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) { cerrarModal(); cerrarModalRenovar(); cerrarModalQR(); } }));

init();
</script>
</body>
</html>
