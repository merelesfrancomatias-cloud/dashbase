<?php
// ============================================================
// FIX GYMDEMO + TABLAS GYM — ejecutar en navegador
// http://localhost/DASHBASE/fix_gymdemo.php
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = [];
$errors  = [];

// ── CONEXIÓN ────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=dashbase_local;charset=utf8mb4",
        "root", "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $results[] = ['ok', 'Conexión a dashbase_local OK'];
} catch (Exception $e) {
    die('<pre style="color:red">❌ No se puede conectar a MySQL: ' . $e->getMessage() . '</pre>');
}

// ── VERIFICAR USUARIO GYMDEMO ────────────────────────────────
$stmt = $pdo->query("SELECT id, usuario, password, negocio_id, rol FROM usuarios WHERE usuario='gymdemo' LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Buscar negocio con rubro gimnasio
    $stmtN = $pdo->query("SELECT n.id FROM negocios n LEFT JOIN rubros r ON r.id=n.rubro_id WHERE r.slug='gimnasio' LIMIT 1");
    $negocio_id = $stmtN->fetchColumn();

    if (!$negocio_id) {
        // Crear rubro si no existe
        $pdo->exec("INSERT IGNORE INTO rubros (nombre, slug) VALUES ('Gimnasio / Fitness', 'gimnasio')");
        $rubro_id = $pdo->lastInsertId();
        if (!$rubro_id) {
            $r = $pdo->query("SELECT id FROM rubros WHERE slug='gimnasio'")->fetchColumn();
            $rubro_id = $r;
        }
        // Crear negocio
        $pdo->prepare("INSERT INTO negocios (nombre, rubro_id) VALUES ('FitZone Gym', ?)")->execute([$rubro_id]);
        $negocio_id = $pdo->lastInsertId();
        $results[] = ['ok', "Negocio FitZone Gym creado (id=$negocio_id)"];
    }

    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO usuarios (negocio_id, nombre, usuario, password, rol) VALUES (?, 'Admin Gym', 'gymdemo', ?, 'admin')")
        ->execute([$negocio_id, $hash]);
    $user_id = $pdo->lastInsertId();
    $results[] = ['ok', "Usuario gymdemo creado (id=$user_id, negocio_id=$negocio_id)"];
    $user = ['id' => $user_id, 'negocio_id' => $negocio_id, 'password' => $hash, 'usuario' => 'gymdemo'];
} else {
    $results[] = ['info', "Usuario gymdemo encontrado: id={$user['id']}, negocio_id={$user['negocio_id']}"];
}

// ── FIX PASSWORD ─────────────────────────────────────────────
$passwordOk = password_verify('admin123', $user['password']);
$results[]  = ['info', "Password 'admin123' válido: " . ($passwordOk ? 'SÍ ✅' : 'NO ❌ — corrigiendo...')];

if (!$passwordOk) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE usuarios SET password=? WHERE usuario='gymdemo'")->execute([$hash]);
    // Verificar que quedó bien
    $newPass = $pdo->query("SELECT password FROM usuarios WHERE usuario='gymdemo'")->fetchColumn();
    $ok2 = password_verify('admin123', $newPass);
    $results[] = [$ok2 ? 'ok' : 'error', "Password actualizado. Verificación: " . ($ok2 ? 'OK ✅' : 'FALLÓ ❌')];
}

// ── VERIFICAR NEGOCIO / RUBRO ────────────────────────────────
$stmtNeg = $pdo->prepare("SELECT n.id, n.nombre, r.slug FROM negocios n LEFT JOIN rubros r ON r.id=n.rubro_id WHERE n.id=?");
$stmtNeg->execute([$user['negocio_id']]);
$negocio = $stmtNeg->fetch(PDO::FETCH_ASSOC);

if ($negocio) {
    $results[] = ['info', "Negocio: '{$negocio['nombre']}', rubro slug='{$negocio['slug']}'"];
    if ($negocio['slug'] !== 'gimnasio') {
        // Fix: buscar o crear rubro gimnasio y asignarlo
        $pdo->exec("INSERT IGNORE INTO rubros (nombre, slug) VALUES ('Gimnasio / Fitness', 'gimnasio')");
        $rubroId = $pdo->query("SELECT id FROM rubros WHERE slug='gimnasio'")->fetchColumn();
        $pdo->prepare("UPDATE negocios SET rubro_id=? WHERE id=?")->execute([$rubroId, $negocio['id']]);
        $results[] = ['ok', "Rubro del negocio corregido a 'gimnasio' (rubro_id=$rubroId)"];
    }
} else {
    $errors[] = "Negocio id={$user['negocio_id']} no encontrado";
}

$negocio_id = $user['negocio_id'];

// ── TABLAS GYM ───────────────────────────────────────────────
$tablas = [
'gym_planes' => "CREATE TABLE IF NOT EXISTS gym_planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) DEFAULT 0,
    duracion_dias INT DEFAULT 30,
    clases_semana INT DEFAULT NULL,
    color VARCHAR(20) DEFAULT '#f97316',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'gym_socios' => "CREATE TABLE IF NOT EXISTS gym_socios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    telefono VARCHAR(50),
    fecha_nacimiento DATE,
    plan_id INT DEFAULT NULL,
    fecha_inicio DATE,
    fecha_vencimiento DATE,
    estado ENUM('activo','vencido','suspendido','inactivo') DEFAULT 'activo',
    foto VARCHAR(255),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'gym_asistencias' => "CREATE TABLE IF NOT EXISTS gym_asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    socio_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio_fecha (negocio_id, fecha),
    INDEX idx_socio (socio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'gym_clases' => "CREATE TABLE IF NOT EXISTS gym_clases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    instructor VARCHAR(100),
    dia_semana TINYINT DEFAULT 0,
    hora_inicio TIME DEFAULT '09:00:00',
    duracion_min INT DEFAULT 60,
    capacidad INT DEFAULT 20,
    color VARCHAR(20) DEFAULT '#f97316',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'gym_pagos' => "CREATE TABLE IF NOT EXISTS gym_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    socio_id INT NOT NULL,
    plan_id INT DEFAULT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0,
    fecha DATE NOT NULL,
    metodo ENUM('efectivo','transferencia','tarjeta','otro') DEFAULT 'efectivo',
    periodo_desde DATE,
    periodo_hasta DATE,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id),
    INDEX idx_socio (socio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tablas as $nombre => $sql) {
    try {
        $pdo->exec($sql);
        $check = $pdo->query("SHOW TABLES LIKE '$nombre'")->fetchColumn();
        $results[] = [$check ? 'ok' : 'error', "Tabla $nombre: " . ($check ? 'OK ✅' : 'FALLÓ ❌')];
    } catch (Exception $e) {
        $errors[] = "Error tabla $nombre: " . $e->getMessage();
    }
}

// ── DATOS DEMO ───────────────────────────────────────────────
// Planes
$cntPlanes = $pdo->prepare("SELECT COUNT(*) FROM gym_planes WHERE negocio_id=?");
$cntPlanes->execute([$negocio_id]);
if ($cntPlanes->fetchColumn() == 0) {
    $planesData = [
        ['Mensual Básico',    'Acceso libre horario regular',      8000,  30, 3,   '#3b82f6'],
        ['Mensual Full',      'Acceso ilimitado + clases grupales',12000, 30, null,'#f97316'],
        ['Trimestral',        '3 meses con descuento especial',    30000, 90, null,'#10b981'],
        ['Anual VIP',         'Plan anual todo incluido',          90000, 365,null,'#8b5cf6'],
        ['Semanal Prueba',    'Semana de prueba para nuevos',      2500,  7,  null,'#64748b'],
    ];
    $sp = $pdo->prepare("INSERT INTO gym_planes (negocio_id,nombre,descripcion,precio,duracion_dias,clases_semana,color) VALUES (?,?,?,?,?,?,?)");
    foreach ($planesData as $p) { $sp->execute([$negocio_id,$p[0],$p[1],$p[2],$p[3],$p[4],$p[5]]); }
    $results[] = ['ok', '5 planes demo insertados'];
} else {
    $results[] = ['info', 'Planes ya existen'];
}

// Socios
$cntSocios = $pdo->prepare("SELECT COUNT(*) FROM gym_socios WHERE negocio_id=?");
$cntSocios->execute([$negocio_id]);
if ($cntSocios->fetchColumn() == 0) {
    $pIds = $pdo->prepare("SELECT id FROM gym_planes WHERE negocio_id=? ORDER BY id LIMIT 4");
    $pIds->execute([$negocio_id]);
    $planIds = $pIds->fetchAll(PDO::FETCH_COLUMN);
    $p1=$planIds[0]??null; $p2=$planIds[1]??null; $p3=$planIds[2]??null; $p4=$planIds[3]??null;
    $sociosData = [
        ['Carlos','Rodríguez','carlos@mail.com','351-4001122',$p2,date('Y-m-d',strtotime('-5 days')),date('Y-m-d',strtotime('+25 days')),'activo'],
        ['María','González','maria@mail.com','351-4003344',$p1,date('Y-m-d',strtotime('-20 days')),date('Y-m-d',strtotime('+10 days')),'activo'],
        ['Lucas','Fernández','lucas@mail.com','351-4005566',$p3,date('Y-m-d',strtotime('-60 days')),date('Y-m-d',strtotime('+30 days')),'activo'],
        ['Sofía','Martínez','sofia@mail.com','351-4007788',$p2,date('Y-m-d',strtotime('-35 days')),date('Y-m-d',strtotime('-5 days')),'vencido'],
        ['Diego','López','diego@mail.com','351-4009900',$p1,date('Y-m-d',strtotime('-15 days')),date('Y-m-d',strtotime('+15 days')),'activo'],
        ['Valentina','Pérez','valentina@mail.com','351-4112233',$p2,date('Y-m-d',strtotime('-10 days')),date('Y-m-d',strtotime('+20 days')),'activo'],
        ['Matías','García','matias@mail.com','351-4114455',$p4,date('Y-m-d',strtotime('-80 days')),date('Y-m-d',strtotime('+285 days')),'activo'],
        ['Laura','Silva','laura@mail.com','351-4116677',$p1,date('Y-m-d',strtotime('-45 days')),date('Y-m-d',strtotime('-15 days')),'vencido'],
        ['Agustín','Romero','agustin@mail.com','351-4118899',$p2,date('Y-m-d',strtotime('-2 days')),date('Y-m-d',strtotime('+28 days')),'activo'],
        ['Camila','Torres','camila@mail.com','351-4220011',$p3,date('Y-m-d',strtotime('-50 days')),date('Y-m-d',strtotime('+40 days')),'activo'],
    ];
    $ss = $pdo->prepare("INSERT INTO gym_socios (negocio_id,nombre,apellido,email,telefono,plan_id,fecha_inicio,fecha_vencimiento,estado) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($sociosData as $s) { $ss->execute([$negocio_id,$s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6],$s[7]]); }
    $results[] = ['ok', '10 socios demo insertados'];
} else {
    $results[] = ['info', 'Socios ya existen'];
}

// Clases
$cntClases = $pdo->prepare("SELECT COUNT(*) FROM gym_clases WHERE negocio_id=?");
$cntClases->execute([$negocio_id]);
if ($cntClases->fetchColumn() == 0) {
    $clasesData = [
        ['Spinning','Prof. Marcos',0,'08:00:00',50,15,'#ef4444'],
        ['Yoga','Prof. Lucía',0,'10:00:00',60,12,'#10b981'],
        ['CrossFit','Prof. Diego',1,'07:00:00',60,20,'#f97316'],
        ['Zumba','Prof. Carla',2,'19:00:00',60,25,'#ec4899'],
        ['Pilates','Prof. Lucía',3,'10:00:00',55,10,'#8b5cf6'],
        ['BoxFit','Prof. Marcos',4,'18:00:00',60,15,'#ef4444'],
        ['Funcional','Prof. Diego',5,'09:00:00',60,20,'#3b82f6'],
        ['Stretching','Prof. Lucía',6,'10:00:00',45,15,'#06b6d4'],
    ];
    $sc = $pdo->prepare("INSERT INTO gym_clases (negocio_id,nombre,instructor,dia_semana,hora_inicio,duracion_min,capacidad,color) VALUES (?,?,?,?,?,?,?,?)");
    foreach ($clasesData as $c) { $sc->execute([$negocio_id,$c[0],$c[1],$c[2],$c[3],$c[4],$c[5],$c[6]]); }
    $results[] = ['ok', '8 clases demo insertadas'];
} else {
    $results[] = ['info', 'Clases ya existen'];
}

// Asistencias hoy
$cntAsist = $pdo->prepare("SELECT COUNT(*) FROM gym_asistencias WHERE negocio_id=? AND fecha=CURDATE()");
$cntAsist->execute([$negocio_id]);
if ($cntAsist->fetchColumn() == 0) {
    $sIds = $pdo->prepare("SELECT id FROM gym_socios WHERE negocio_id=? AND estado='activo' LIMIT 6");
    $sIds->execute([$negocio_id]);
    $socioIds = $sIds->fetchAll(PDO::FETCH_COLUMN);
    $sa = $pdo->prepare("INSERT INTO gym_asistencias (negocio_id,socio_id,fecha,hora) VALUES (?,?,CURDATE(),?)");
    $h = 7;
    foreach ($socioIds as $sid) { $sa->execute([$negocio_id,$sid,sprintf('%02d:00:00',$h++)]); }
    $results[] = ['ok', count($socioIds).' asistencias de hoy insertadas'];
} else {
    $results[] = ['info', 'Asistencias de hoy ya existen'];
}

// ── VERIFICAR ARCHIVOS API/GYM ───────────────────────────────
$BASE = __DIR__;
$archivos = [
    'api/gym/planes.php',
    'api/gym/socios.php',
    'api/gym/asistencias.php',
    'api/gym/clases.php',
    'api/gym/pagos.php',
    'views/gym/socios.php',
    'views/gym/clases.php',
    'views/gym/asistencias.php',
];
foreach ($archivos as $f) {
    $exists = file_exists("$BASE/$f");
    $results[] = [$exists ? 'ok' : 'error', "Archivo $f: " . ($exists ? 'EXISTS ✅' : 'FALTA ❌')];
}

// ── RESUMEN FINAL ────────────────────────────────────────────
$totalErrors = count($errors) + count(array_filter($results, fn($r) => $r[0] === 'error'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fix GymDemo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; color: #e2e8f0; font-family: system-ui, sans-serif; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { font-size: 26px; color: #f97316; margin-bottom: 6px; }
        .sub { color: #64748b; margin-bottom: 28px; font-size: 13px; }
        .item { padding: 9px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 5px; background: #1e293b; border-left: 4px solid #475569; }
        .item.ok    { border-color: #22c55e; }
        .item.error { border-color: #ef4444; background: rgba(239,68,68,.08); color: #fca5a5; }
        .item.info  { border-color: #3b82f6; }
        .cta { margin-top: 28px; background: rgba(249,115,22,.1); border: 1px solid rgba(249,115,22,.3); border-radius: 14px; padding: 24px; }
        .cta h2 { color: #f97316; margin-bottom: 16px; }
        .creds { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
        .cred { background: rgba(255,255,255,.05); border-radius: 8px; padding: 10px 14px; }
        .cred .l { font-size: 11px; color: #64748b; text-transform: uppercase; }
        .cred .v { font-size: 16px; font-weight: 700; color: #f1f5f9; margin-top: 3px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; background: #f97316; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; margin-right: 8px; margin-top: 4px; }
        .btn:hover { background: #ea6c0a; }
        .btn.sec { background: #1e293b; border: 1px solid rgba(255,255,255,.1); }
        .warn { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); border-radius: 10px; padding: 14px; margin-top: 16px; color: #fca5a5; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-dumbbell"></i> Fix GymDemo</h1>
    <p class="sub">Diagnóstico y reparación automática · <?= date('d/m/Y H:i:s') ?></p>

    <?php foreach ($results as [$type, $msg]): ?>
    <div class="item <?= $type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $err): ?>
    <div class="item error"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <div class="cta">
        <h2><?= $totalErrors === 0 ? '✅ Todo listo' : "⚠️ $totalErrors problema(s) encontrado(s)" ?></h2>
        <div class="creds">
            <div class="cred"><div class="l">Usuario</div><div class="v">gymdemo</div></div>
            <div class="cred"><div class="l">Contraseña</div><div class="v">admin123</div></div>
        </div>
        <a href="index.php" class="btn"><i class="fas fa-sign-in-alt"></i> Ir al Login</a>
        <?php if (file_exists(__DIR__.'/views/gym/socios.php')): ?>
        <a href="views/gym/socios.php" class="btn sec"><i class="fas fa-users"></i> Ver Socios</a>
        <?php endif; ?>

        <?php
        // Advertir si faltan archivos de vistas/api
        $faltanArchivos = array_filter($results, fn($r) => $r[0] === 'error' && str_contains($r[1], 'FALTA'));
        if (!empty($faltanArchivos)):
        ?>
        <div class="warn">
            <strong>⚠️ Faltan archivos PHP:</strong> Corrí primero el instalador
            <code>instalar_gym.php</code> o copiá los archivos del workspace a XAMPP.
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
