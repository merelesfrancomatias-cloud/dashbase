<?php
session_start();
if (!isset($_SESSION['negocio_id'])) { header('Location: ../auth/login.php'); exit; }

$base = '/DASHBASE';
require_once __DIR__ . '/../../config/database.php';
$pdo   = (new Database())->getConnection();
$nid   = (int)$_SESSION['negocio_id'];
$stmt  = $pdo->prepare("SELECT nombre, carta_token, carta_activa FROM negocios WHERE id=?");
$stmt->execute([$nid]);
$neg   = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no tiene token, generar uno
if (empty($neg['carta_token'])) {
    $token = bin2hex(random_bytes(32));
    $pdo->prepare("UPDATE negocios SET carta_token=? WHERE id=?")->execute([$token, $nid]);
    $neg['carta_token'] = $token;
}

$menuUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . $base . '/views/restaurant/menu-digital.php?token=' . $neg['carta_token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Menú Digital — Restaurant</title>
<link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css">
<link rel="stylesheet" href="<?= $base ?>/public/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
.qr-page { max-width:760px; margin:0 auto; }
.qr-card { background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:32px;margin-bottom:24px;text-align:center; }
.qr-title { font-size:20px;font-weight:700;color:var(--text);margin-bottom:4px; }
.qr-sub   { font-size:13px;color:var(--text-muted);margin-bottom:28px; }
#qrCanvas { display:inline-block;padding:16px;background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.1); }
.url-box  { margin-top:20px;display:flex;align-items:center;gap:8px;background:rgba(0,0,0,.04);border-radius:10px;padding:10px 14px; }
.url-text { flex:1;font-size:12px;color:var(--text-muted);word-break:break-all;text-align:left; }
.btn-copy { padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;transition:.15s; }
.btn-copy:hover { background:var(--primary);color:#fff;border-color:var(--primary); }
.btn-copy.copied { background:#10b981;color:#fff;border-color:#10b981; }

.actions-row { display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:20px; }
.btn-action { display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-size:13px;font-weight:700;transition:.15s; }
.btn-primary { background:var(--primary);color:#fff; }
.btn-primary:hover { filter:brightness(1.1); }
.btn-secondary { background:var(--surface);color:var(--text);border:1px solid var(--border); }
.btn-secondary:hover { border-color:var(--primary);color:var(--primary); }
.btn-danger { background:rgba(239,68,68,.1);color:#dc2626;border:1px solid rgba(239,68,68,.2); }
.btn-danger:hover { background:#dc2626;color:#fff; }

.info-card { background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 24px;margin-bottom:24px; }
.info-card h3 { font-size:14px;font-weight:700;color:var(--text);margin:0 0 14px;display:flex;align-items:center;gap:8px; }
.info-card h3 i { color:var(--primary); }
.info-row { display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px; }
.info-row:last-child { border-bottom:none; }
.info-lbl { color:var(--text-muted); }
.info-val { font-weight:600;color:var(--text); }
.pill-on  { background:rgba(16,185,129,.12);color:#10b981;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
.pill-off { background:rgba(100,116,139,.1);color:#64748b;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }

/* Print */
@media print {
    .no-print { display:none!important; }
    body { background:#fff; }
    .qr-card { border:none;box-shadow:none; }
}
</style>
</head>
<body>
<div class="dashboard-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">

<div class="qr-page">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;" class="no-print">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(var(--primary-rgb),.12);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:18px;">
            <i class="fas fa-qrcode"></i>
        </div>
        <div>
            <h1 style="margin:0;font-size:20px;font-weight:700;color:var(--text);">QR del Menú Digital</h1>
            <p style="margin:2px 0 0;font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($neg['nombre']) ?></p>
        </div>
    </div>

    <!-- QR Code -->
    <div class="qr-card">
        <div class="qr-title">Escaneá para ver el menú</div>
        <div class="qr-sub">Mostralo en la mesa, en la entrada o imprimilo</div>
        <div id="qrCanvas"></div>
        <div class="url-box">
            <div class="url-text" id="menuUrlText"><?= htmlspecialchars($menuUrl) ?></div>
            <button class="btn-copy" id="btnCopy" onclick="copiarUrl()"><i class="fas fa-copy"></i> Copiar</button>
        </div>
        <div class="actions-row no-print">
            <button class="btn-action btn-primary" onclick="window.open('<?= htmlspecialchars($menuUrl) ?>','_blank')">
                <i class="fas fa-external-link-alt"></i> Ver menú
            </button>
            <button class="btn-action btn-secondary" onclick="imprimirQR()">
                <i class="fas fa-print"></i> Imprimir QR
            </button>
            <button class="btn-action btn-secondary" onclick="descargarQR()">
                <i class="fas fa-download"></i> Descargar PNG
            </button>
        </div>
    </div>

    <!-- Info -->
    <div class="info-card no-print">
        <h3><i class="fas fa-info-circle"></i> Estado del menú digital</h3>
        <div class="info-row">
            <span class="info-lbl">Estado</span>
            <span class="info-val">
                <?= $neg['carta_activa'] ? '<span class="pill-on">● Activo</span>' : '<span class="pill-off">● Inactivo</span>' ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-lbl">URL del menú</span>
            <span class="info-val" style="font-size:11px;font-family:monospace;max-width:400px;word-break:break-all;"><?= htmlspecialchars($menuUrl) ?></span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Token de acceso</span>
            <span class="info-val" style="font-size:11px;font-family:monospace;color:var(--text-muted);"><?= htmlspecialchars(substr($neg['carta_token'],0,20)) ?>...</span>
        </div>
    </div>

    <!-- Tips -->
    <div class="info-card no-print">
        <h3><i class="fas fa-lightbulb"></i> Tips de uso</h3>
        <div class="info-row">
            <span style="font-size:13px;color:var(--text-muted)">📱 <strong>En las mesas</strong> — Imprimí el QR y pegalo en un soporte acrílico en cada mesa.</span>
        </div>
        <div class="info-row">
            <span style="font-size:13px;color:var(--text-muted)">📋 <strong>Tamaño recomendado</strong> — Imprimilo en 10×10 cm para que sea fácil de escanear desde 30 cm.</span>
        </div>
        <div class="info-row" style="border:none">
            <span style="font-size:13px;color:var(--text-muted)">🔗 <strong>Compartir</strong> — También podés compartir el link directamente por WhatsApp.</span>
        </div>
    </div>

</div>
</main>
</div>

<script>
const URL_MENU = <?= json_encode($menuUrl) ?>;

// Generar QR
new QRCode(document.getElementById('qrCanvas'), {
    text: URL_MENU,
    width:  240,
    height: 240,
    colorDark:  '#0f172a',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
});

function copiarUrl() {
    navigator.clipboard.writeText(URL_MENU).then(() => {
        const btn = document.getElementById('btnCopy');
        btn.textContent = '✓ Copiado';
        btn.classList.add('copied');
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copiar'; btn.classList.remove('copied'); }, 2000);
    });
}

function imprimirQR() { window.print(); }

function descargarQR() {
    const canvas = document.querySelector('#qrCanvas canvas');
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = 'qr-menu.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>
</body>
</html>
