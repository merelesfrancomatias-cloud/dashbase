<?php 
session_start();

$base = rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/');

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>

        /* ── HERO CAJA ── */
        .caja-hero {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .caja-hero-left { display:flex; align-items:center; gap:18px; }
        .caja-hero-icon {
            width:60px; height:60px; border-radius:16px;
            display:flex; align-items:center; justify-content:center;
            font-size:26px; flex-shrink:0;
        }
        .caja-hero-icon.open    { background:rgba(16,185,129,.12); color:#10b981; }
        .caja-hero-icon.closed  { background:rgba(107,114,128,.12); color:var(--text-secondary); }
        .caja-hero-icon.loading { background:rgba(99,102,241,.1);  color:var(--primary); }
        .caja-hero-info h2 { font-size:22px; font-weight:700; color:var(--text-primary); margin:0 0 4px; }
        .caja-hero-info p  { font-size:14px; color:var(--text-secondary); margin:0; }
        .caja-status-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:4px 12px; border-radius:20px;
            font-size:12px; font-weight:600; margin-top:8px;
        }
        .caja-status-badge.open   { background:rgba(16,185,129,.15); color:#10b981; }
        .caja-status-badge.closed { background:rgba(107,114,128,.12); color:var(--text-secondary); }
        .caja-status-badge .dot {
            width:7px; height:7px; border-radius:50%; background:currentColor;
        }
        .caja-status-badge.open .dot { animation:pulse-dot 1.5s infinite; }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.3} }

        /* ── KPI GRID ── */
        .kpi-grid {
            display:grid; grid-template-columns:repeat(4,1fr);
            gap:16px; margin-bottom:24px;
        }
        .kpi-card {
            background:var(--surface); border:1px solid var(--border);
            border-radius:16px; padding:20px;
            display:flex; flex-direction:column; gap:12px;
            transition:transform .2s, box-shadow .2s;
            position:relative; overflow:hidden;
        }
        .kpi-card::before {
            content:''; position:absolute; top:0; left:0; right:0;
            height:3px; border-radius:16px 16px 0 0;
        }
        .kpi-card.blue::before   { background:var(--primary); }
        .kpi-card.green::before  { background:#10b981; }
        .kpi-card.red::before    { background:#ef4444; }
        .kpi-card.purple::before { background:#8b5cf6; }
        .kpi-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.08); }
        .kpi-card-top { display:flex; align-items:center; justify-content:space-between; }
        .kpi-icon {
            width:44px; height:44px; border-radius:12px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .kpi-icon.blue   { background:rgba(99,102,241,.1);  color:var(--primary); }
        .kpi-icon.green  { background:rgba(16,185,129,.1);  color:#10b981; }
        .kpi-icon.red    { background:rgba(239,68,68,.1);   color:#ef4444; }
        .kpi-icon.purple { background:rgba(139,92,246,.1);  color:#8b5cf6; }
        .kpi-label { font-size:12px; color:var(--text-secondary); font-weight:500; text-transform:uppercase; letter-spacing:.5px; }
        .kpi-value { font-size:24px; font-weight:700; color:var(--text-primary); }
        .kpi-sub   { font-size:12px; color:var(--text-secondary); }

        /* ── DOS COLUMNAS ── */
        .caja-grid {
            display:grid; grid-template-columns:1fr 380px;
            gap:20px; align-items:start;
        }

        /* ── PANEL CARD ── */
        .panel-card {
            background:var(--surface); border:1px solid var(--border);
            border-radius:16px; overflow:hidden;
        }
        .panel-header {
            padding:18px 24px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        }
        .panel-title {
            display:flex; align-items:center; gap:10px;
            font-size:15px; font-weight:600; color:var(--text-primary);
        }
        .panel-title i { color:var(--primary); }
        .panel-body { padding:20px 24px; }

        /* ── MÉTODOS PAGO ── */
        .metodo-item {
            display:flex; align-items:center; gap:12px;
            padding:12px 0; border-bottom:1px solid var(--border);
        }
        .metodo-item:last-child { border-bottom:none; }
        .metodo-dot {
            width:36px; height:36px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-size:15px; flex-shrink:0;
        }
        .metodo-info { flex:1; min-width:0; }
        .metodo-name  { font-size:13px; font-weight:600; color:var(--text-primary); }
        .metodo-count { font-size:11px; color:var(--text-secondary); }
        .metodo-amount { font-size:14px; font-weight:700; }
        .progress-bar-wrap {
            background:var(--background); border-radius:8px;
            height:6px; overflow:hidden; margin-top:5px;
        }
        .progress-bar-fill { height:100%; border-radius:8px; transition:width .6s; }

        /* ── RESUMEN CIERRE ── */
        .resumen-item {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 0;
        }
        .resumen-item + .resumen-item { border-top:1px solid var(--border); }
        .resumen-item.total {
            padding-top:14px; margin-top:4px;
            border-top:2px solid var(--border) !important;
        }
        .resumen-item.total .resumen-label { font-weight:700; font-size:15px; }
        .resumen-item.total .resumen-val   { font-size:20px; font-weight:800; color:var(--primary); }
        .resumen-label { font-size:13px; color:var(--text-secondary); }
        .resumen-val   { font-size:14px; font-weight:600; color:var(--text-primary); }

        /* ── HISTORIAL ── */
        .hist-table { width:100%; border-collapse:collapse; }
        .hist-table th {
            padding:10px 14px; text-align:left;
            font-size:11px; font-weight:600; color:var(--text-secondary);
            text-transform:uppercase; letter-spacing:.5px;
            border-bottom:1px solid var(--border); background:var(--background);
        }
        .hist-table td {
            padding:12px 14px; font-size:13px;
            color:var(--text-primary); border-bottom:1px solid var(--border);
        }
        .hist-table tr:last-child td { border-bottom:none; }
        .hist-table tr:hover td { background:var(--background); }
        .user-cell { display:flex; align-items:center; gap:8px; }
        .user-avatar-sm {
            width:28px; height:28px; border-radius:8px;
            background:rgba(99,102,241,.1); color:var(--primary);
            display:flex; align-items:center; justify-content:center;
            font-size:10px; font-weight:700; flex-shrink:0;
        }
        .table-container { overflow-x:auto; }

        /* ── EMPTY STATE ── */
        .empty-caja {
            display:flex; flex-direction:column; align-items:center;
            justify-content:center; padding:60px 24px; text-align:center;
        }
        .empty-caja-icon {
            width:80px; height:80px; border-radius:24px;
            background:rgba(99,102,241,.1); color:var(--primary);
            display:flex; align-items:center; justify-content:center;
            font-size:36px; margin-bottom:20px;
        }
        .empty-caja h3 { font-size:20px; font-weight:700; color:var(--text-primary); margin:0 0 8px; }
        .empty-caja p  { font-size:14px; color:var(--text-secondary); margin:0 0 24px; }

        /* ── LOADING ── */
        .caja-loading {
            display:flex; flex-direction:column; align-items:center;
            justify-content:center; padding:80px 24px; gap:16px;
        }
        .spin {
            width:44px; height:44px;
            border:3px solid var(--border); border-top-color:var(--primary);
            border-radius:50%; animation:spin .8s linear infinite;
        }
        @keyframes spin { to{ transform:rotate(360deg); } }

        /* ── DIFERENCIA DINÁMICA ── */
        .diff-indicator {
            display:flex; align-items:center; gap:12px;
            padding:14px 18px; border-radius:12px;
            margin-top:14px; transition:background .3s, border .3s;
            border:1px solid var(--border); background:var(--background);
        }
        .diff-indicator.positive { background:rgba(16,185,129,.08); border-color:rgba(16,185,129,.3); }
        .diff-indicator.negative { background:rgba(239,68,68,.08);  border-color:rgba(239,68,68,.3); }
        .diff-indicator > i { font-size:22px; color:var(--text-secondary); }
        .diff-indicator.positive > i { color:#10b981; }
        .diff-indicator.negative > i { color:#ef4444; }
        .diff-text-label { font-size:12px; color:var(--text-secondary); }
        .diff-text-val   { font-size:18px; font-weight:700; color:var(--text-primary); }
        .diff-indicator.positive .diff-text-val { color:#10b981; }
        .diff-indicator.negative .diff-text-val { color:#ef4444; }

        /* ── MODAL ── */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.5); backdrop-filter:blur(4px);
            z-index:1000; align-items:center; justify-content:center; padding:20px;
        }
        .modal-overlay.show   { display:flex; }
        .modal-overlay.hidden { display:none !important; }
        .modal {
            background:var(--surface); border:1px solid var(--border);
            border-radius:20px; width:100%; max-width:480px;
            box-shadow:0 24px 64px rgba(0,0,0,.2);
            animation:modal-in .25s ease;
        }
        @keyframes modal-in {
            from{ transform:translateY(20px) scale(.97); opacity:0; }
            to  { transform:translateY(0)    scale(1);   opacity:1; }
        }
        .modal-header {
            padding:20px 24px 16px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        }
        .modal-header-left { display:flex; align-items:center; gap:12px; }
        .modal-header-icon {
            width:40px; height:40px; border-radius:12px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .modal-header-icon.green { background:rgba(16,185,129,.1); color:#10b981; }
        .modal-header-icon.red   { background:rgba(239,68,68,.1);  color:#ef4444; }
        .modal-title    { font-size:17px; font-weight:700; color:var(--text-primary); margin:0; }
        .modal-subtitle { font-size:12px; color:var(--text-secondary); margin:2px 0 0; }
        .modal-close {
            width:34px; height:34px; border-radius:10px;
            border:none; background:var(--background);
            color:var(--text-secondary); cursor:pointer; font-size:16px;
            display:flex; align-items:center; justify-content:center;
            transition:background .2s;
        }
        .modal-close:hover { background:var(--border); color:var(--text-primary); }
        .modal-body { padding:20px 24px; }
        .modal-footer {
            padding:16px 24px; border-top:1px solid var(--border);
            display:flex; gap:10px; justify-content:flex-end;
        }

        /* ── RESPONSIVE ── */
        @media(max-width:1100px) { .caja-grid{ grid-template-columns:1fr; } }
        @media(max-width:768px) {
            .kpi-grid { grid-template-columns:repeat(2,1fr); gap:12px; }
            .caja-hero { flex-direction:column; align-items:flex-start; gap:14px; }
            .caja-hero-right { width:100%; }
            .caja-hero-right .btn { width:100%; justify-content:center; }
            .container { padding-bottom:90px !important; }
        }
        @media(max-width:480px) {
            .kpi-value { font-size:18px; }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <div id="alertContainer" class="alert hidden"></div>

        <div class="container">
            <div id="cajaContainer">
                <div class="caja-loading">
                    <div class="spin"></div>
                    <p style="color:var(--text-secondary);font-size:14px;">Cargando caja...</p>
                </div>
            </div>
        </div>
    </main>

    <!-- ══ MODAL ABRIR CAJA ══ -->
    <div id="modalAbrirCaja" class="modal-overlay hidden">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-header-icon green"><i class="fas fa-unlock"></i></div>
                    <div>
                        <h3 class="modal-title">Abrir Caja</h3>
                        <p class="modal-subtitle">Ingresá el monto inicial del día</p>
                    </div>
                </div>
                <button class="modal-close btn-cancelar-modal"><i class="fas fa-times"></i></button>
            </div>
            <form id="formAbrirCaja">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="monto_inicial" class="form-label required">Monto Inicial</label>
                        <input type="number" id="monto_inicial" class="form-input"
                               step="0.01" min="0" placeholder="0.00" required autofocus
                               style="font-size:26px;font-weight:700;text-align:center;height:62px;letter-spacing:1px;">
                        <small style="color:var(--text-secondary);font-size:13px;margin-top:6px;display:block;text-align:center;">
                            Contá el efectivo disponible antes de empezar
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancelar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarAbrirCaja">
                        <i class="fas fa-unlock"></i> Abrir Caja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ MODAL CERRAR CAJA ══ -->
    <div id="modalCerrarCaja" class="modal-overlay hidden">
        <div class="modal" style="max-width:520px;">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-header-icon red"><i class="fas fa-lock"></i></div>
                    <div>
                        <h3 class="modal-title">Cerrar Caja</h3>
                        <p class="modal-subtitle">Verificá los montos antes de cerrar</p>
                    </div>
                </div>
                <button class="modal-close btn-cancelar-modal"><i class="fas fa-times"></i></button>
            </div>
            <form id="formCerrarCaja">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:16px;">

                    <!-- Resumen del día -->
                    <div style="background:var(--background);border-radius:14px;padding:18px;">
                        <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin:0 0 12px;">Resumen del día</p>
                        <div class="resumen-item">
                            <span class="resumen-label"><i class="fas fa-play-circle" style="width:16px;color:#6366f1;margin-right:6px;"></i>Monto inicial</span>
                            <span class="resumen-val" id="cierreMontoInicial">$0</span>
                        </div>
                        <div class="resumen-item">
                            <span class="resumen-label"><i class="fas fa-plus-circle" style="width:16px;color:#10b981;margin-right:6px;"></i>Ventas del día</span>
                            <span class="resumen-val" style="color:#10b981;" id="cierreMontoVentas">$0</span>
                        </div>
                        <div class="resumen-item">
                            <span class="resumen-label"><i class="fas fa-minus-circle" style="width:16px;color:#ef4444;margin-right:6px;"></i>Gastos del día</span>
                            <span class="resumen-val" style="color:#ef4444;" id="cierreMontoGastos">$0</span>
                        </div>
                        <div class="resumen-item total">
                            <span class="resumen-label">💰 Monto esperado</span>
                            <span class="resumen-val" id="cierreMontoEsperado">$0</span>
                        </div>
                    </div>

                    <!-- Input monto real -->
                    <div class="form-group" style="margin:0;">
                        <label for="monto_real" class="form-label required">
                            ¿Cuánto dinero contás físicamente en la caja?
                        </label>
                        <input type="number" id="monto_real" class="form-input"
                               step="0.01" min="0" placeholder="0.00" required
                               style="font-size:22px;font-weight:700;text-align:center;height:56px;">
                        <small style="color:var(--text-secondary);font-size:12px;margin-top:6px;display:block;text-align:center;line-height:1.5;">
                            Contá los billetes y monedas que tenés ahora en la caja.<br>
                            <strong>El monto esperado</strong> = Inicial + Ventas − Gastos ya descontados.
                        </small>
                        <button type="button" id="btnUsarEsperado"
                                style="margin-top:8px;width:100%;padding:8px;border:1px dashed var(--primary);background:transparent;color:var(--primary);border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                            <i class="fas fa-magic"></i> Usar monto esperado
                        </button>
                    </div>

                    <!-- Diferencia dinámica -->
                    <div class="diff-indicator" id="diffIndicator">
                        <i class="fas fa-equals"></i>
                        <div>
                            <div class="diff-text-label" id="diffLabel">Diferencia</div>
                            <div class="diff-text-val" id="cierreDiferencia">—</div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="form-group" style="margin:0;">
                        <label for="observaciones_cierre" class="form-label">
                            Observaciones <span style="color:var(--text-secondary);font-weight:400;">(opcional)</span>
                        </label>
                        <textarea id="observaciones_cierre" class="form-textarea"
                                  placeholder="Ej: Se encontró diferencia por…"
                                  style="min-height:70px;resize:vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancelar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnGuardarCerrarCaja">
                        <i class="fas fa-lock"></i> Confirmar Cierre
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../public/js/caja.js?v=<?= time() ?>"></script>
    <script>
        window.APP_BASE = '<?= $base ?>';

        let cajaModule;
        let alertTimeout;

        function showAlert(message, type = 'error') {
            const el = document.getElementById('alertContainer');
            if (alertTimeout) clearTimeout(alertTimeout);
            const icons = { error:'exclamation-circle', success:'check-circle', warning:'exclamation-triangle', info:'info-circle' };
            el.className = `alert alert-${type}`;
            el.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i><span>${message}</span>`;
            el.classList.remove('hidden');
            alertTimeout = setTimeout(() => el.classList.add('hidden'), 3500);
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('es-AR', { style:'currency', currency:'ARS' }).format(value || 0);
        }

        async function checkAuth() {
            try {
                const res  = await fetch('../../api/auth/check.php', { credentials:'include' });
                const data = await res.json();
                if (!data.success) { window.location.href = '../../index.php'; return false; }
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                const nombre = user.nombre || 'Usuario';
                document.getElementById('userName').textContent = nombre;
                document.getElementById('userRole').textContent = user.rol === 'admin' ? 'Administrador' : 'Empleado';
                document.getElementById('userAvatar').textContent = nombre.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
                if (user.rol !== 'admin') {
                    document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
                }
                return true;
            } catch { window.location.href = '../../index.php'; return false; }
        }

        // Logout — manejado por header.php

        document.addEventListener('DOMContentLoaded', async () => {
            const authOk = await checkAuth();
            if (authOk) cajaModule = new CajaModule();
        });
    </script>
</body>
</html>
