<?php
/**
 * Instalador del módulo Canchas Deportivas
 * Ejecutar en: http://localhost/DASHBASE/instalar_canchas.php
 */

// Cargar configuración
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}

$host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbname = $_ENV['DB_NAME'] ?? 'dashbase_local';
$user   = $_ENV['DB_USER'] ?? 'root';
$pass   = $_ENV['DB_PASS'] ?? '';
if (empty($dbname)) $dbname = 'dashbase_local';

$results = [];

function ok($msg)  { return ['type'=>'ok',   'msg'=>$msg]; }
function err($msg) { return ['type'=>'error', 'msg'=>$msg]; }
function info($msg){ return ['type'=>'info',  'msg'=>$msg]; }

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $results[] = ok("Conexión a base de datos OK ({$dbname})");

    // 1. Tabla canchas
    $pdo->exec("CREATE TABLE IF NOT EXISTS canchas (
        id           INT(11) NOT NULL AUTO_INCREMENT,
        negocio_id   INT(11) NOT NULL,
        nombre       VARCHAR(100) NOT NULL,
        deporte      VARCHAR(60) DEFAULT NULL,
        descripcion  TEXT,
        precio_hora  DECIMAL(10,2) DEFAULT 0.00,
        capacidad    INT(11) DEFAULT 0,
        activo       TINYINT(1) DEFAULT 1,
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_negocio (negocio_id),
        KEY idx_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = ok("Tabla `canchas` creada/verificada");

    // 2. Tabla reservas_canchas
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservas_canchas (
        id                 INT(11) NOT NULL AUTO_INCREMENT,
        cancha_id          INT(11) NOT NULL,
        fecha              DATE NOT NULL,
        hora_inicio        TIME NOT NULL,
        hora_fin           TIME NOT NULL,
        cliente_nombre     VARCHAR(120) DEFAULT NULL,
        cliente_telefono   VARCHAR(40) DEFAULT NULL,
        monto              DECIMAL(10,2) DEFAULT 0.00,
        metodo_pago        ENUM('efectivo','transferencia','tarjeta') DEFAULT 'efectivo',
        estado             ENUM('confirmada','pendiente','cancelada') DEFAULT 'confirmada',
        notas              TEXT,
        created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cancha_fecha (cancha_id, fecha),
        KEY idx_fecha (fecha),
        KEY idx_estado (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = ok("Tabla `reservas_canchas` creada/verificada");

    // 3. Insertar el rubro si no existe
    $stmtRubro = $pdo->prepare("SELECT id FROM rubros WHERE slug='canchas' LIMIT 1");
    $stmtRubro->execute();
    if (!$stmtRubro->fetchColumn()) {
        $pdo->exec("INSERT INTO rubros (slug, nombre, descripcion, icono, color, orden)
            VALUES ('canchas','Alquiler de Canchas','Alquiler de canchas de fútbol, pádel, vóley, tenis, básquet y otros deportes','fa-futbol','#16a34a',16)
            ON DUPLICATE KEY UPDATE nombre=VALUES(nombre)");
        $results[] = ok("Rubro 'canchas' insertado en tabla rubros");
    } else {
        $results[] = info("Rubro 'canchas' ya existía en la BD");
    }

    // 4. Crear negocio demo si no existe
    $stmtDemo = $pdo->prepare("SELECT u.id FROM usuarios u JOIN negocios n ON n.id=u.negocio_id JOIN rubros r ON r.id=n.rubro_id WHERE u.usuario='canchasdemo' LIMIT 1");
    $stmtDemo->execute();
    if (!$stmtDemo->fetchColumn()) {
        $rubroId = $pdo->query("SELECT id FROM rubros WHERE slug='canchas' LIMIT 1")->fetchColumn();
        if ($rubroId) {
            $pdo->prepare("INSERT INTO negocios (nombre, rubro_id, email, telefono, activo) VALUES (?,?,?,?,1)")
                ->execute(['La Cancha Sportiva', $rubroId, 'demo@canchas.com', '11-9999-0000']);
            $negId = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO usuarios (negocio_id, nombre, apellido, usuario, password, rol, activo) VALUES (?,?,?,?,?,?,1)")
                ->execute([$negId, 'Demo', 'Canchas', 'canchasdemo', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
            $userId = $pdo->lastInsertId();
            $results[] = ok("Usuario demo creado: canchasdemo / admin123");

            // Canchas demo
            $pdo->prepare("INSERT INTO canchas (negocio_id,nombre,deporte,descripcion,precio_hora,capacidad) VALUES
                (?,?,?,?,?,?), (?,?,?,?,?,?), (?,?,?,?,?,?)")
                ->execute([
                    $negId,'Cancha 1','Fútbol 5','Pasto sintético, iluminación LED, vestuarios',3500,10,
                    $negId,'Cancha 2','Fútbol 7','Pasto natural, iluminación, estacionamiento',4500,14,
                    $negId,'Pádel','Pádel','Cancha de pádel techada con paredes de vidrio',2500,4,
                ]);
            $results[] = ok("3 canchas demo insertadas");
        }
    } else {
        // Reset password
        $pdo->prepare("UPDATE usuarios SET password=? WHERE usuario='canchasdemo'")
            ->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
        $results[] = info("Usuario canchasdemo ya existía — password reseteada a admin123");
    }

    // 5. Verificar archivos
    $archivos = [
        'api/canchas/canchas.php',
        'api/canchas/reservas.php',
        'views/canchas/canchas.php',
        'views/canchas/reservas.php',
        'views/includes/sidebar.php',
    ];
    foreach ($archivos as $arch) {
        $path = __DIR__ . '/' . $arch;
        if (file_exists($path)) $results[] = ok("Archivo encontrado: {$arch}");
        else                    $results[] = err("Archivo NO encontrado: {$arch}");
    }

} catch (Exception $e) {
    $results[] = err("Error fatal: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Instalador Canchas</title>
<style>
body{font-family:system-ui,sans-serif;background:#f1f5f9;padding:40px 20px;color:#1e293b;}
.box{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);max-width:640px;margin:0 auto;padding:32px;}
h1{margin:0 0 4px;font-size:22px;}
.sub{color:#64748b;margin-bottom:24px;font-size:14px;}
.item{display:flex;gap:10px;align-items:flex-start;padding:8px 12px;border-radius:8px;margin-bottom:6px;font-size:14px;}
.ok   {background:#f0fdf4;color:#15803d;}
.error{background:#fef2f2;color:#dc2626;}
.info {background:#eff6ff;color:#2563eb;}
.icon{font-size:16px;margin-top:1px;flex-shrink:0;}
.btn{display:inline-block;margin-top:20px;padding:12px 24px;background:#16a34a;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;}
</style>
</head>
<body>
<div class="box">
    <h1>⚽ Instalador — Módulo Canchas</h1>
    <p class="sub">Canchas deportivas para alquiler por hora</p>
    <?php foreach ($results as $r): ?>
        <div class="item <?= $r['type'] ?>">
            <span class="icon"><?= $r['type']==='ok'?'✅':($r['type']==='error'?'❌':'ℹ️') ?></span>
            <span><?= htmlspecialchars($r['msg']) ?></span>
        </div>
    <?php endforeach; ?>
    <a class="btn" href="index.php">← Ir al login</a>
</div>
</body>
</html>
