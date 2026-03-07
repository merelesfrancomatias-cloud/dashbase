<?php
/**
 * install_production.php — Instalador web para producción
 * 
 * Ejecutar UNA SOLA VEZ después de subir el proyecto:
 *   https://dashtienda.com/install_production.php
 * 
 * ⚠️  ELIMINAR este archivo del servidor una vez finalizado.
 */

// Clave de seguridad para evitar ejecución accidental
define('INSTALL_KEY', 'dashinstall2026');

$key = $_GET['key'] ?? '';
if ($key !== INSTALL_KEY) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:#ef4444;">Acceso denegado.<br>Usá: install_production.php?key=dashinstall2026</h2>');
}

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$errors   = [];
$success  = [];
$warnings = [];

// ─────────────────────────────────────────────────────────────
// Función helper: ejecutar SQL con mysqli (soporta multi-query)
// ─────────────────────────────────────────────────────────────
function runSQL(string $sql, string $label): array {
    $mysqli = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? '',
        $_ENV['DB_NAME'] ?? 'dash_crm',
        (int)($_ENV['DB_PORT'] ?? 3306)
    );
    if ($mysqli->connect_errno) {
        return ['ok' => false, 'msg' => "Conexión fallida: " . $mysqli->connect_error];
    }
    $mysqli->set_charset('utf8mb4');
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

    if (!$mysqli->multi_query($sql)) {
        $err = $mysqli->error;
        $mysqli->close();
        return ['ok' => false, 'msg' => $err];
    }
    do {
        if ($res = $mysqli->store_result()) $res->free();
    } while ($mysqli->more_results() && $mysqli->next_result());

    $errno = $mysqli->errno;
    $error = $mysqli->error;
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
    $mysqli->close();

    if ($errno) return ['ok' => false, 'msg' => $error];
    return ['ok' => true, 'msg' => ''];
}

// ─────────────────────────────────────────────────────────────
// PASO 1 — Verificar conexión a la base de datos
// ─────────────────────────────────────────────────────────────
try {
    $db   = new Database();
    $conn = $db->getConnection();
    $success[] = "✅ Conexión a la base de datos OK — <strong>" . ($_ENV['DB_NAME'] ?? '?') . "</strong> en <strong>" . ($_ENV['DB_HOST'] ?? '?') . "</strong>";
} catch (Exception $e) {
    $errors[] = "❌ No se pudo conectar a la BD: " . $e->getMessage();
    // Si no hay BD no tiene sentido seguir
    goto RENDER;
}

// ─────────────────────────────────────────────────────────────
// PASO 2 — Ejecutar migraciones de /database/migrations/ (schema)
// ─────────────────────────────────────────────────────────────
$conn->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$applied = $conn->query("SELECT filename FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

$schemaMigrations = glob(__DIR__ . '/database/migrations/*.sql');
sort($schemaMigrations);

foreach ($schemaMigrations as $file) {
    $filename = basename($file);
    if (in_array($filename, $applied)) {
        $warnings[] = "⚠️  <em>$filename</em> — ya aplicada, saltando";
        continue;
    }
    $sql    = file_get_contents($file);
    $result = runSQL($sql, $filename);
    if ($result['ok']) {
        $stmt = $conn->prepare("INSERT IGNORE INTO migrations (filename) VALUES (?)");
        $stmt->execute([$filename]);
        $success[] = "✅ Schema: <strong>$filename</strong>";
    } else {
        $errors[] = "❌ Error en <strong>$filename</strong>: " . $result['msg'];
    }
}

// ─────────────────────────────────────────────────────────────
// PASO 3 — Ejecutar migraciones de /config/migrations/ (rubros, datos)
// ─────────────────────────────────────────────────────────────
$dataMigrations = glob(__DIR__ . '/config/migrations/*.sql');
sort($dataMigrations);

foreach ($dataMigrations as $file) {
    $filename = 'config_' . basename($file);
    if (in_array($filename, $applied)) {
        $warnings[] = "⚠️  <em>$filename</em> — ya aplicada, saltando";
        continue;
    }
    $sql    = file_get_contents($file);
    $result = runSQL($sql, $filename);
    if ($result['ok']) {
        $stmt = $conn->prepare("INSERT IGNORE INTO migrations (filename) VALUES (?)");
        $stmt->execute([$filename]);
        $success[] = "✅ Datos: <strong>$filename</strong>";
    } else {
        // Las migraciones de datos pueden tener warnings que no son críticos
        $warnings[] = "⚠️  <em>$filename</em> — " . $result['msg'];
    }
}

// ─────────────────────────────────────────────────────────────
// PASO 4 — Verificar que la tabla rubros tiene datos
// ─────────────────────────────────────────────────────────────
try {
    $count = $conn->query("SELECT COUNT(*) FROM rubros")->fetchColumn();
    if ($count > 0) {
        $success[] = "✅ Tabla <strong>rubros</strong> tiene $count registros";
    } else {
        $errors[] = "❌ Tabla rubros está vacía — las migraciones de datos fallaron";
    }
} catch (Exception $e) {
    $errors[] = "❌ Tabla rubros no existe: " . $e->getMessage();
}

// ─────────────────────────────────────────────────────────────
// PASO 5 — Verificar columnas críticas en negocios
// ─────────────────────────────────────────────────────────────
$neededCols = ['rubro_id', 'activo', 'bloqueado', 'estado_suscripcion', 'plan_id', 'trial_hasta'];
$existingCols = $conn->query("SHOW COLUMNS FROM negocios")->fetchAll(PDO::FETCH_COLUMN);

foreach ($neededCols as $col) {
    if (in_array($col, $existingCols)) {
        $success[] = "✅ Columna <code>negocios.$col</code> existe";
    } else {
        // Intentar agregar la columna automáticamente
        $alterMap = [
            'rubro_id'           => "ALTER TABLE negocios ADD COLUMN rubro_id INT NULL AFTER nombre",
            'activo'             => "ALTER TABLE negocios ADD COLUMN activo TINYINT(1) DEFAULT 1",
            'bloqueado'          => "ALTER TABLE negocios ADD COLUMN bloqueado TINYINT(1) DEFAULT 0",
            'bloqueado_motivo'   => "ALTER TABLE negocios ADD COLUMN bloqueado_motivo TEXT NULL",
            'estado_suscripcion' => "ALTER TABLE negocios ADD COLUMN estado_suscripcion VARCHAR(20) DEFAULT 'trial'",
            'plan_id'            => "ALTER TABLE negocios ADD COLUMN plan_id INT NULL",
            'trial_hasta'        => "ALTER TABLE negocios ADD COLUMN trial_hasta DATE NULL",
            'fecha_vencimiento'  => "ALTER TABLE negocios ADD COLUMN fecha_vencimiento DATE NULL",
        ];
        if (isset($alterMap[$col])) {
            try {
                $conn->exec($alterMap[$col]);
                $success[] = "✅ Columna <code>negocios.$col</code> agregada automáticamente";
            } catch (Exception $e) {
                $warnings[] = "⚠️  No se pudo agregar <code>negocios.$col</code>: " . $e->getMessage();
            }
        } else {
            $warnings[] = "⚠️  Columna <code>negocios.$col</code> no existe";
        }
    }
}

// ─────────────────────────────────────────────────────────────
// PASO 6 — Verificar tabla planes (necesaria para el login)
// ─────────────────────────────────────────────────────────────
try {
    $planesCount = $conn->query("SELECT COUNT(*) FROM planes")->fetchColumn();
    if ($planesCount > 0) {
        $success[] = "✅ Tabla <strong>planes</strong> tiene $planesCount registros";
    } else {
        // Insertar plan free por defecto
        $conn->exec("INSERT IGNORE INTO planes (id, nombre, nombre_display, precio, activo) VALUES 
            (1, 'free',  'Plan Gratuito', 0, 1),
            (2, 'pro',   'Plan Pro',     9999, 1),
            (3, 'enterprise', 'Enterprise', 19999, 1)");
        $success[] = "✅ Planes por defecto insertados";
    }
} catch (Exception $e) {
    // Tabla no existe, crearla
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS planes (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            nombre       VARCHAR(50)  NOT NULL UNIQUE,
            nombre_display VARCHAR(100),
            precio       DECIMAL(10,2) DEFAULT 0,
            activo       TINYINT(1) DEFAULT 1,
            creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->exec("INSERT IGNORE INTO planes (id, nombre, nombre_display, precio, activo) VALUES 
            (1, 'free',  'Plan Gratuito', 0, 1),
            (2, 'pro',   'Plan Pro',     9999, 1),
            (3, 'enterprise', 'Enterprise', 19999, 1)");
        $success[] = "✅ Tabla <strong>planes</strong> creada con datos por defecto";
    } catch (Exception $e2) {
        $warnings[] = "⚠️  No se pudo crear tabla planes: " . $e2->getMessage();
    }
}

// ─────────────────────────────────────────────────────────────
// PASO 7 — Verificar que existe el gimnasio demo (opcional)
// ─────────────────────────────────────────────────────────────
try {
    $demoCount = $conn->query("SELECT COUNT(*) FROM negocios WHERE nombre LIKE '%demo%' OR nombre LIKE '%Demo%'")->fetchColumn();
    if ($demoCount > 0) {
        $success[] = "✅ Negocio demo detectado ($demoCount)";
    } else {
        $warnings[] = "⚠️  No hay negocio demo — podés crear uno desde <a href='register.php' target='_blank'>register.php</a>";
    }
} catch (Exception $e) {
    $warnings[] = "⚠️  No se pudo verificar negocios demo";
}

// ─────────────────────────────────────────────────────────────
// PASO 8 — Verificar .env
// ─────────────────────────────────────────────────────────────
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $success[] = "✅ Archivo <strong>.env</strong> encontrado";
    $appUrl = $_ENV['APP_URL'] ?? '';
    if (strpos($appUrl, 'localhost') !== false) {
        $warnings[] = "⚠️  APP_URL todavía apunta a localhost (<code>$appUrl</code>) — cambiarlo a <code>https://dashtienda.com</code>";
    } else {
        $success[] = "✅ APP_URL configurado: <code>$appUrl</code>";
    }
    $appEnv = $_ENV['APP_ENV'] ?? '';
    if ($appEnv !== 'production') {
        $warnings[] = "⚠️  APP_ENV = <code>$appEnv</code> — cambiarlo a <code>production</code>";
    }
} else {
    $errors[] = "❌ No existe el archivo <strong>.env</strong> — copiá <code>.env.example</code> como <code>.env</code> y completá los datos";
}

RENDER:
$totalErrors   = count($errors);
$totalWarnings = count($warnings);
$totalSuccess  = count($success);
$allOk = $totalErrors === 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DASH — Instalación de Producción</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0b1120; color: #e8edf5; min-height: 100vh; padding: 40px 20px; }
  .wrap { max-width: 760px; margin: 0 auto; }
  .header { text-align: center; margin-bottom: 36px; }
  .logo { font-size: 32px; font-weight: 900; color: #0FD186; letter-spacing: -1px; }
  .subtitle { color: #8896aa; margin-top: 6px; font-size: 14px; }
  .status-bar { display: flex; gap: 16px; justify-content: center; margin-bottom: 32px; flex-wrap: wrap; }
  .stat { background: #0e1626; border: 1px solid rgba(255,255,255,.07); border-radius: 12px; padding: 14px 24px; text-align: center; }
  .stat-n { font-size: 28px; font-weight: 800; line-height: 1; }
  .stat-l { font-size: 12px; color: #8896aa; margin-top: 4px; font-weight: 600; }
  .ok-n  { color: #0FD186; }
  .err-n { color: #ef4444; }
  .wrn-n { color: #f59e0b; }
  .section { background: #0e1626; border: 1px solid rgba(255,255,255,.07); border-radius: 16px; padding: 24px; margin-bottom: 20px; }
  .section h3 { font-size: 14px; font-weight: 700; margin-bottom: 14px; text-transform: uppercase; letter-spacing: .6px; color: #8896aa; }
  .item { padding: 8px 0; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,.04); line-height: 1.5; }
  .item:last-child { border-bottom: none; }
  code { background: rgba(15,209,134,.1); color: #0FD186; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
  a { color: #0FD186; }
  .banner { border-radius: 14px; padding: 20px 24px; text-align: center; margin-bottom: 24px; font-size: 16px; font-weight: 700; }
  .banner-ok  { background: rgba(15,209,134,.12); border: 1px solid rgba(15,209,134,.25); color: #0FD186; }
  .banner-err { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.25);  color: #ef4444; }
  .delete-warn { background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.3); border-radius: 12px; padding: 16px 20px; margin-top: 24px; font-size: 13px; color: #f59e0b; line-height: 1.6; }
  .delete-warn strong { display: block; font-size: 15px; margin-bottom: 6px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">DASH</div>
    <div class="subtitle">Instalador de producción — dashtienda.com</div>
  </div>

  <div class="status-bar">
    <div class="stat"><div class="stat-n ok-n"><?= $totalSuccess ?></div><div class="stat-l">Correctos</div></div>
    <div class="stat"><div class="stat-n wrn-n"><?= $totalWarnings ?></div><div class="stat-l">Advertencias</div></div>
    <div class="stat"><div class="stat-n err-n"><?= $totalErrors ?></div><div class="stat-l">Errores</div></div>
  </div>

  <?php if ($allOk): ?>
    <div class="banner banner-ok">✅ Instalación completada sin errores — el sistema está listo</div>
  <?php else: ?>
    <div class="banner banner-err">❌ Hay <?= $totalErrors ?> error(es) que necesitás resolver</div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
  <div class="section">
    <h3>❌ Errores</h3>
    <?php foreach ($errors as $e): ?>
      <div class="item"><?= $e ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($warnings)): ?>
  <div class="section">
    <h3>⚠️ Advertencias</h3>
    <?php foreach ($warnings as $w): ?>
      <div class="item"><?= $w ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
  <div class="section">
    <h3>✅ Pasos completados</h3>
    <?php foreach ($success as $s): ?>
      <div class="item"><?= $s ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($allOk): ?>
  <div class="delete-warn">
    <strong>⚠️ IMPORTANTE — Eliminá este archivo ahora</strong>
    Conectate por FTP/cPanel y borrá <code>install_production.php</code> del servidor.<br>
    Dejarlo expuesto es un riesgo de seguridad.
  </div>
  <?php endif; ?>

</div>
</body>
</html>
