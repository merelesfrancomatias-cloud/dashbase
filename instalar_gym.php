<?php
/**
 * INSTALADOR MÓDULO GIMNASIO - DASHBASE
 * Acceder desde: http://localhost/DASHBASE/instalar_gym.php
 */

$BASE   = __DIR__;
$errors = [];
$ok     = [];

// ─── DB ─────────────────────────────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=dashbase_local;charset=utf8mb4", "root", "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $ok[] = "✅ Conexión a BD ok";
} catch (Exception $e) {
    die("❌ No se pudo conectar a la BD: " . $e->getMessage());
}

// ─── TABLAS ─────────────────────────────────────────────────────────────────
$tablas = [
"gym_planes" => "CREATE TABLE IF NOT EXISTS gym_planes (
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

"gym_socios" => "CREATE TABLE IF NOT EXISTS gym_socios (
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

"gym_asistencias" => "CREATE TABLE IF NOT EXISTS gym_asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    socio_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio_fecha (negocio_id, fecha),
    INDEX idx_socio (socio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"gym_clases" => "CREATE TABLE IF NOT EXISTS gym_clases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negocio_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    instructor VARCHAR(100),
    dia_semana TINYINT DEFAULT 0 COMMENT '0=Lun,1=Mar,2=Mie,3=Jue,4=Vie,5=Sab,6=Dom',
    hora_inicio TIME DEFAULT '09:00:00',
    duracion_min INT DEFAULT 60,
    capacidad INT DEFAULT 20,
    color VARCHAR(20) DEFAULT '#f97316',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_negocio (negocio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"gym_pagos" => "CREATE TABLE IF NOT EXISTS gym_pagos (
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
        $ok[] = "✅ Tabla <b>$nombre</b> creada/verificada";
    } catch (Exception $e) {
        $errors[] = "❌ Error creando $nombre: " . $e->getMessage();
    }
}

// ─── FIX PASSWORD gymdemo ────────────────────────────────────────────────────
try {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE usuario = 'gymdemo'");
    $stmt->execute([$hash]);
    if ($stmt->rowCount() > 0) {
        $ok[] = "✅ Password de <b>gymdemo</b> actualizado → <b>admin123</b>";
    } else {
        $errors[] = "⚠️ No se encontró usuario 'gymdemo' — verificar BD";
    }
} catch (Exception $e) {
    $errors[] = "❌ Error actualizando password: " . $e->getMessage();
}

// ─── DATOS DEMO ──────────────────────────────────────────────────────────────
$stmtNeg = $pdo->prepare("SELECT negocio_id FROM usuarios WHERE usuario='gymdemo' LIMIT 1");
$stmtNeg->execute();
$negocio_id = $stmtNeg->fetchColumn();

if ($negocio_id) {
    // Planes demo
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM gym_planes WHERE negocio_id=?");
    $stmtCheck->execute([$negocio_id]);
    if ($stmtCheck->fetchColumn() == 0) {
        $planes = [
            ['Mensual Básico',    'Acceso libre en horario regular',    8000,  30, 3,  '#3b82f6'],
            ['Mensual Full',      'Acceso ilimitado + clases grupales', 12000, 30, null,'#f97316'],
            ['Trimestral',        '3 meses con descuento especial',     30000, 90, null,'#10b981'],
            ['Anual VIP',         'Plan anual todo incluido',           90000, 365,null,'#8b5cf6'],
            ['Semanal Prueba',    'Semana de prueba para nuevos socios',2500,  7,  null,'#64748b'],
        ];
        $stmtPlan = $pdo->prepare("INSERT INTO gym_planes (negocio_id,nombre,descripcion,precio,duracion_dias,clases_semana,color) VALUES (?,?,?,?,?,?,?)");
        foreach ($planes as $p) {
            $stmtPlan->execute([$negocio_id, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]]);
        }
        $ok[] = "✅ 5 planes demo insertados";
    } else {
        $ok[] = "ℹ️ Planes ya existen";
    }

    // Socios demo
    $stmtCheck2 = $pdo->prepare("SELECT COUNT(*) FROM gym_socios WHERE negocio_id=?");
    $stmtCheck2->execute([$negocio_id]);
    if ($stmtCheck2->fetchColumn() == 0) {
        $stmtPIds = $pdo->prepare("SELECT id FROM gym_planes WHERE negocio_id=? ORDER BY id LIMIT 4");
        $stmtPIds->execute([$negocio_id]);
        $planIds = $stmtPIds->fetchAll(PDO::FETCH_COLUMN);
        $p1 = $planIds[0] ?? null; $p2 = $planIds[1] ?? null;
        $p3 = $planIds[2] ?? null; $p4 = $planIds[3] ?? null;

        $socios = [
            ['Carlos',   'Rodríguez',  'carlos@mail.com',    '351-4001122', $p2, date('Y-m-d',strtotime('-5 days')),  date('Y-m-d',strtotime('+25 days')), 'activo'],
            ['María',    'González',   'maria@mail.com',     '351-4003344', $p1, date('Y-m-d',strtotime('-20 days')), date('Y-m-d',strtotime('+10 days')), 'activo'],
            ['Lucas',    'Fernández',  'lucas@mail.com',     '351-4005566', $p3, date('Y-m-d',strtotime('-60 days')), date('Y-m-d',strtotime('+30 days')), 'activo'],
            ['Sofía',    'Martínez',   'sofia@mail.com',     '351-4007788', $p2, date('Y-m-d',strtotime('-35 days')), date('Y-m-d',strtotime('-5 days')),  'vencido'],
            ['Diego',    'López',      'diego@mail.com',     '351-4009900', $p1, date('Y-m-d',strtotime('-15 days')), date('Y-m-d',strtotime('+15 days')), 'activo'],
            ['Valentina','Pérez',      'valentina@mail.com', '351-4112233', $p2, date('Y-m-d',strtotime('-10 days')), date('Y-m-d',strtotime('+20 days')), 'activo'],
            ['Matías',   'García',     'matias@mail.com',    '351-4114455', $p4, date('Y-m-d',strtotime('-80 days')), date('Y-m-d',strtotime('+285 days')),'activo'],
            ['Laura',    'Silva',      'laura@mail.com',     '351-4116677', $p1, date('Y-m-d',strtotime('-45 days')), date('Y-m-d',strtotime('-15 days')), 'vencido'],
            ['Agustín',  'Romero',     'agustin@mail.com',   '351-4118899', $p2, date('Y-m-d',strtotime('-2 days')),  date('Y-m-d',strtotime('+28 days')), 'activo'],
            ['Camila',   'Torres',     'camila@mail.com',    '351-4220011', $p3, date('Y-m-d',strtotime('-50 days')), date('Y-m-d',strtotime('+40 days')), 'activo'],
            ['Nicolás',  'Díaz',       '',                   '351-4222233', $p1, date('Y-m-d',strtotime('-5 days')),  date('Y-m-d',strtotime('+25 days')), 'activo'],
            ['Florencia','Moreno',     'flor@mail.com',      '351-4224455', $p2, date('Y-m-d',strtotime('-1 days')),  date('Y-m-d',strtotime('+29 days')), 'activo'],
            ['Sebastián','Jiménez',    '',                   '351-4226677', null,date('Y-m-d',strtotime('-3 days')),  null,                                'activo'],
            ['Ana',      'Ruiz',       'ana@mail.com',       '351-4228899', $p1, date('Y-m-d',strtotime('-40 days')), date('Y-m-d',strtotime('-10 days')), 'suspendido'],
            ['Facundo',  'Vargas',     'facu@mail.com',      '351-4330011', $p2, date('Y-m-d',strtotime('-3 days')),  date('Y-m-d',strtotime('+27 days')), 'activo'],
        ];
        $stmtSocio = $pdo->prepare("INSERT INTO gym_socios (negocio_id,nombre,apellido,email,telefono,plan_id,fecha_inicio,fecha_vencimiento,estado) VALUES (?,?,?,?,?,?,?,?,?)");
        foreach ($socios as $s) {
            $stmtSocio->execute([$negocio_id,$s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6],$s[7]]);
        }
        $ok[] = "✅ 15 socios demo insertados";
    } else {
        $ok[] = "ℹ️ Socios ya existen";
    }

    // Clases demo
    $stmtCheck3 = $pdo->prepare("SELECT COUNT(*) FROM gym_clases WHERE negocio_id=?");
    $stmtCheck3->execute([$negocio_id]);
    if ($stmtCheck3->fetchColumn() == 0) {
        $clases = [
            ['Spinning',   'Prof. Marcos',  0, '08:00:00', 50, 15, '#ef4444'],
            ['Yoga',       'Prof. Lucía',   0, '10:00:00', 60, 12, '#10b981'],
            ['CrossFit',   'Prof. Diego',   1, '07:00:00', 60, 20, '#f97316'],
            ['Zumba',      'Prof. Carla',   2, '19:00:00', 60, 25, '#ec4899'],
            ['Pilates',    'Prof. Lucía',   3, '10:00:00', 55, 10, '#8b5cf6'],
            ['BoxFit',     'Prof. Marcos',  4, '18:00:00', 60, 15, '#ef4444'],
            ['Funcional',  'Prof. Diego',   5, '09:00:00', 60, 20, '#3b82f6'],
            ['Stretching', 'Prof. Lucía',   6, '10:00:00', 45, 15, '#06b6d4'],
        ];
        $stmtClase = $pdo->prepare("INSERT INTO gym_clases (negocio_id,nombre,instructor,dia_semana,hora_inicio,duracion_min,capacidad,color) VALUES (?,?,?,?,?,?,?,?)");
        foreach ($clases as $c) {
            $stmtClase->execute([$negocio_id,$c[0],$c[1],$c[2],$c[3],$c[4],$c[5],$c[6]]);
        }
        $ok[] = "✅ 8 clases demo insertadas";
    } else {
        $ok[] = "ℹ️ Clases ya existen";
    }

    // Asistencias hoy
    $stmtCheck4 = $pdo->prepare("SELECT COUNT(*) FROM gym_asistencias WHERE negocio_id=? AND fecha=CURDATE()");
    $stmtCheck4->execute([$negocio_id]);
    if ($stmtCheck4->fetchColumn() == 0) {
        $stmtSociosIds = $pdo->prepare("SELECT id FROM gym_socios WHERE negocio_id=? AND estado='activo' ORDER BY id LIMIT 8");
        $stmtSociosIds->execute([$negocio_id]);
        $socioIds = $stmtSociosIds->fetchAll(PDO::FETCH_COLUMN);
        $stmtAsist = $pdo->prepare("INSERT INTO gym_asistencias (negocio_id,socio_id,fecha,hora) VALUES (?,?,CURDATE(),?)");
        $hora = 7;
        foreach ($socioIds as $sid) {
            $stmtAsist->execute([$negocio_id, $sid, sprintf('%02d:00:00', $hora)]);
            $hora++;
        }
        $ok[] = "✅ " . count($socioIds) . " asistencias de hoy insertadas";
    } else {
        $ok[] = "ℹ️ Asistencias de hoy ya existen";
    }
} else {
    $errors[] = "⚠️ No se encontró usuario gymdemo";
}

// ─── CREAR ARCHIVOS ──────────────────────────────────────────────────────────
function writeFile($path, $content) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            return "❌ No se pudo crear directorio: $dir (verificar permisos)";
        }
    }
    if (@file_put_contents($path, $content) !== false) {
        return "✅ Archivo creado: <b>" . str_replace(__DIR__.'/', '', $path) . "</b>";
    }
    return "❌ Error escribiendo: " . str_replace(__DIR__.'/', '', $path) . " (verificar permisos)";
}

// ═══════════════════════════════════════════════════════════════════════════
// API/GYM/PLANES.PHP
// ═══════════════════════════════════════════════════════════════════════════
$apiPlanes = <<<'EOF'
<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();
$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM gym_planes WHERE negocio_id=? AND activo=1 ORDER BY precio ASC");
    $stmt->execute([$negocio_id]);
    Response::success('ok', $stmt->fetchAll(PDO::FETCH_ASSOC));
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre = trim($data['nombre'] ?? '');
    if (!$nombre) Response::error('Nombre requerido', 400);
    $stmt = $db->prepare("INSERT INTO gym_planes (negocio_id,nombre,descripcion,precio,duracion_dias,clases_semana,color) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$negocio_id, $nombre, trim($data['descripcion']??''), (float)($data['precio']??0), (int)($data['duracion_dias']??30), !empty($data['clases_semana'])?(int)$data['clases_semana']:null, $data['color']??'#f97316']);
    Response::success('Plan creado', ['id' => $db->lastInsertId()]);
}
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $fields = []; $params = [];
    foreach (['nombre','descripcion','precio','duracion_dias','clases_semana','color','activo'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f=?"; $params[] = $data[$f] === '' ? null : $data[$f]; }
    }
    if (empty($fields)) Response::error('Nada que actualizar', 400);
    $params[] = $id; $params[] = $negocio_id;
    $db->prepare("UPDATE gym_planes SET ".implode(',',$fields)." WHERE id=? AND negocio_id=?")->execute($params);
    Response::success('Plan actualizado');
}
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("UPDATE gym_planes SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id,$negocio_id]);
    Response::success('Plan desactivado');
}
EOF;

// ═══════════════════════════════════════════════════════════════════════════
// API/GYM/SOCIOS.PHP
// ═══════════════════════════════════════════════════════════════════════════
$apiSocios = <<<'EOF'
<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();
$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $estado = $_GET['estado'] ?? '';
    $buscar = $_GET['q'] ?? '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT s.*, p.nombre AS plan_nombre FROM gym_socios s LEFT JOIN gym_planes p ON p.id = s.plan_id WHERE s.id = ? AND s.negocio_id = ?");
        $stmt->execute([$id, $negocio_id]);
        $socio = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$socio) Response::error('Socio no encontrado', 404);
        Response::success('ok', $socio);
    }
    $where = "s.negocio_id = ?";
    $params = [$negocio_id];
    if ($estado) { $where .= " AND s.estado = ?"; $params[] = $estado; }
    if ($buscar) {
        $where .= " AND (s.nombre LIKE ? OR s.apellido LIKE ? OR s.email LIKE ? OR s.telefono LIKE ?)";
        $b = "%$buscar%";
        $params = array_merge($params, [$b, $b, $b, $b]);
    }
    $stmt = $db->prepare("SELECT s.*, p.nombre AS plan_nombre, p.precio AS plan_precio, DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes FROM gym_socios s LEFT JOIN gym_planes p ON p.id = s.plan_id WHERE $where ORDER BY s.apellido, s.nombre");
    $stmt->execute($params);
    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmtStats = $db->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN estado='activo' THEN 1 ELSE 0 END) AS activos, SUM(CASE WHEN estado='vencido' THEN 1 ELSE 0 END) AS vencidos, SUM(CASE WHEN estado='suspendido' THEN 1 ELSE 0 END) AS suspendidos, SUM(CASE WHEN estado='activo' AND DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 1 ELSE 0 END) AS por_vencer FROM gym_socios WHERE negocio_id = ?");
    $stmtStats->execute([$negocio_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    Response::success('ok', ['socios' => $socios, 'stats' => $stats]);
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre = trim($data['nombre'] ?? '');
    $apellido = trim($data['apellido'] ?? '');
    if (!$nombre || !$apellido) Response::error('Nombre y apellido requeridos', 400);
    $plan_id = !empty($data['plan_id']) ? (int)$data['plan_id'] : null;
    $fecha_ini = $data['fecha_inicio'] ?? date('Y-m-d');
    $fecha_venc = null;
    if ($plan_id) {
        $planStmt = $db->prepare("SELECT duracion_dias FROM gym_planes WHERE id=? AND negocio_id=?");
        $planStmt->execute([$plan_id, $negocio_id]);
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
        if ($plan) $fecha_venc = date('Y-m-d', strtotime($fecha_ini . ' +' . $plan['duracion_dias'] . ' days'));
    }
    $stmt = $db->prepare("INSERT INTO gym_socios (negocio_id,nombre,apellido,email,telefono,plan_id,fecha_inicio,fecha_vencimiento,estado,notas) VALUES (?,?,?,?,?,?,?,?,'activo',?)");
    $stmt->execute([$negocio_id, $nombre, $apellido, trim($data['email']??''), trim($data['telefono']??''), $plan_id, $fecha_ini, $fecha_venc, trim($data['notas']??'')]);
    $id = $db->lastInsertId();
    if (!empty($data['monto']) && $data['monto'] > 0) {
        $stmtPago = $db->prepare("INSERT INTO gym_pagos (negocio_id,socio_id,plan_id,monto,fecha,metodo,periodo_desde,periodo_hasta) VALUES (?,?,?,?,?,?,?,?)");
        $stmtPago->execute([$negocio_id, $id, $plan_id, (float)$data['monto'], date('Y-m-d'), $data['metodo']??'efectivo', $fecha_ini, $fecha_venc]);
    }
    Response::success('Socio creado', ['id' => $id]);
}
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $fields = []; $params = [];
    foreach (['nombre','apellido','email','telefono','plan_id','fecha_inicio','fecha_vencimiento','estado','notas'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f] === '' ? null : $data[$f]; }
    }
    if (empty($fields)) Response::error('Nada que actualizar', 400);
    $params[] = $id; $params[] = $negocio_id;
    $db->prepare("UPDATE gym_socios SET " . implode(', ', $fields) . " WHERE id=? AND negocio_id=?")->execute($params);
    Response::success('Socio actualizado');
}
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("UPDATE gym_socios SET estado='inactivo' WHERE id=? AND negocio_id=?")->execute([$id, $negocio_id]);
    Response::success('Socio desactivado');
}
EOF;

// ═══════════════════════════════════════════════════════════════════════════
// API/GYM/ASISTENCIAS.PHP
// ═══════════════════════════════════════════════════════════════════════════
$apiAsistencias = <<<'EOF'
<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();
$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $socio_id = isset($_GET['socio_id']) ? (int)$_GET['socio_id'] : 0;
    if ($socio_id > 0) {
        $stmt = $db->prepare("SELECT a.*, CONCAT(s.nombre,' ',s.apellido) AS socio_nombre FROM gym_asistencias a LEFT JOIN gym_socios s ON s.id = a.socio_id WHERE a.negocio_id=? AND a.socio_id=? ORDER BY a.fecha DESC, a.hora DESC LIMIT 50");
        $stmt->execute([$negocio_id, $socio_id]);
        Response::success('ok', $stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    $stmt = $db->prepare("SELECT a.id, a.socio_id, a.fecha, a.hora, CONCAT(s.nombre,' ',s.apellido) AS socio_nombre, s.estado AS socio_estado, p.nombre AS plan_nombre FROM gym_asistencias a LEFT JOIN gym_socios s ON s.id = a.socio_id LEFT JOIN gym_planes p ON p.id = s.plan_id WHERE a.negocio_id=? AND a.fecha=? ORDER BY a.hora ASC");
    $stmt->execute([$negocio_id, $fecha]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM gym_asistencias WHERE negocio_id=? AND fecha=?");
    $stmtCount->execute([$negocio_id, $fecha]);
    Response::success('ok', ['asistencias' => $asistencias, 'total' => (int)$stmtCount->fetchColumn(), 'fecha' => $fecha]);
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $socio_id = (int)($data['socio_id'] ?? 0);
    $fecha = $data['fecha'] ?? date('Y-m-d');
    $hora  = $data['hora']  ?? date('H:i:s');
    if (!$socio_id) Response::error('socio_id requerido', 400);
    $stmtV = $db->prepare("SELECT id, CONCAT(nombre,' ',apellido) AS nombre_completo, estado, fecha_vencimiento FROM gym_socios WHERE id=? AND negocio_id=?");
    $stmtV->execute([$socio_id, $negocio_id]);
    $socio = $stmtV->fetch(PDO::FETCH_ASSOC);
    if (!$socio) Response::error('Socio no encontrado', 404);
    $stmtDup = $db->prepare("SELECT id FROM gym_asistencias WHERE negocio_id=? AND socio_id=? AND fecha=?");
    $stmtDup->execute([$negocio_id, $socio_id, $fecha]);
    if ($stmtDup->fetch()) Response::error('El socio ya registró asistencia hoy', 409);
    $stmt = $db->prepare("INSERT INTO gym_asistencias (negocio_id,socio_id,fecha,hora) VALUES (?,?,?,?)");
    $stmt->execute([$negocio_id, $socio_id, $fecha, $hora]);
    Response::success('Asistencia registrada', ['id' => $db->lastInsertId(), 'socio' => $socio['nombre_completo'], 'estado' => $socio['estado'], 'vencimiento' => $socio['fecha_vencimiento']]);
}
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("DELETE FROM gym_asistencias WHERE id=? AND negocio_id=?")->execute([$id, $negocio_id]);
    Response::success('Asistencia eliminada');
}
EOF;

// ═══════════════════════════════════════════════════════════════════════════
// API/GYM/CLASES.PHP
// ═══════════════════════════════════════════════════════════════════════════
$apiClases = <<<'EOF'
<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();
$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM gym_clases WHERE negocio_id=? AND activo=1 ORDER BY dia_semana, hora_inicio");
    $stmt->execute([$negocio_id]);
    Response::success('ok', $stmt->fetchAll(PDO::FETCH_ASSOC));
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $nombre = trim($data['nombre'] ?? '');
    if (!$nombre) Response::error('Nombre requerido', 400);
    $stmt = $db->prepare("INSERT INTO gym_clases (negocio_id,nombre,instructor,dia_semana,hora_inicio,duracion_min,capacidad,color) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$negocio_id, $nombre, trim($data['instructor']??''), (int)($data['dia_semana']??0), $data['hora_inicio']??'09:00', (int)($data['duracion_min']??60), (int)($data['capacidad']??20), $data['color']??'#f97316']);
    Response::success('Clase creada', ['id' => $db->lastInsertId()]);
}
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $fields = []; $params = [];
    foreach (['nombre','instructor','dia_semana','hora_inicio','duracion_min','capacidad','color','activo'] as $f) {
        if (isset($data[$f])) { $fields[] = "$f=?"; $params[] = $data[$f]; }
    }
    if (empty($fields)) Response::error('Nada que actualizar', 400);
    $params[] = $id; $params[] = $negocio_id;
    $db->prepare("UPDATE gym_clases SET ".implode(',',$fields)." WHERE id=? AND negocio_id=?")->execute($params);
    Response::success('Clase actualizada');
}
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 400);
    $db->prepare("UPDATE gym_clases SET activo=0 WHERE id=? AND negocio_id=?")->execute([$id,$negocio_id]);
    Response::success('Clase desactivada');
}
EOF;

// ═══════════════════════════════════════════════════════════════════════════
// API/GYM/PAGOS.PHP
// ═══════════════════════════════════════════════════════════════════════════
$apiPagos = <<<'EOF'
<?php
require_once __DIR__ . '/../../api/bootstrap.php';
Auth::check();
$negocio_id = $_SESSION['negocio_id'];
$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $socio_id = isset($_GET['socio_id']) ? (int)$_GET['socio_id'] : 0;
    $mes = $_GET['mes'] ?? date('Y-m');
    $where = "p.negocio_id=?"; $params = [$negocio_id];
    if ($socio_id) { $where .= " AND p.socio_id=?"; $params[] = $socio_id; }
    if ($mes) { $where .= " AND DATE_FORMAT(p.fecha,'%Y-%m')=?"; $params[] = $mes; }
    $stmt = $db->prepare("SELECT p.*, CONCAT(s.nombre,' ',s.apellido) AS socio_nombre, pl.nombre AS plan_nombre FROM gym_pagos p LEFT JOIN gym_socios s ON s.id = p.socio_id LEFT JOIN gym_planes pl ON pl.id = p.plan_id WHERE $where ORDER BY p.fecha DESC");
    $stmt->execute($params);
    $stmtTot = $db->prepare("SELECT SUM(monto) FROM gym_pagos WHERE negocio_id=? AND DATE_FORMAT(fecha,'%Y-%m')=?");
    $stmtTot->execute([$negocio_id, $mes]);
    Response::success('ok', ['pagos' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total_mes' => (float)($stmtTot->fetchColumn() ?? 0)]);
}
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $socio_id = (int)($data['socio_id'] ?? 0);
    $plan_id  = !empty($data['plan_id']) ? (int)$data['plan_id'] : null;
    $monto    = (float)($data['monto'] ?? 0);
    $fecha    = $data['fecha'] ?? date('Y-m-d');
    $metodo   = $data['metodo'] ?? 'efectivo';
    if (!$socio_id || $monto <= 0) Response::error('socio_id y monto requeridos', 400);
    $periodo_desde = $data['periodo_desde'] ?? $fecha;
    $periodo_hasta = $data['periodo_hasta'] ?? null;
    if ($plan_id && !$periodo_hasta) {
        $planStmt = $db->prepare("SELECT duracion_dias FROM gym_planes WHERE id=?");
        $planStmt->execute([$plan_id]);
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
        if ($plan) $periodo_hasta = date('Y-m-d', strtotime($periodo_desde . ' +' . $plan['duracion_dias'] . ' days'));
    }
    $stmt = $db->prepare("INSERT INTO gym_pagos (negocio_id,socio_id,plan_id,monto,fecha,metodo,periodo_desde,periodo_hasta,notas) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$negocio_id,$socio_id,$plan_id,$monto,$fecha,$metodo,$periodo_desde,$periodo_hasta,trim($data['notas']??'')]);
    if ($plan_id && $periodo_hasta) {
        $db->prepare("UPDATE gym_socios SET estado='activo', plan_id=?, fecha_inicio=?, fecha_vencimiento=? WHERE id=? AND negocio_id=?")->execute([$plan_id,$periodo_desde,$periodo_hasta,$socio_id,$negocio_id]);
    }
    Response::success('Pago registrado', ['id' => $db->lastInsertId()]);
}
EOF;

// Escribir archivos API
$ok[] = writeFile("$BASE/api/gym/planes.php",      $apiPlanes);
$ok[] = writeFile("$BASE/api/gym/socios.php",      $apiSocios);
$ok[] = writeFile("$BASE/api/gym/asistencias.php", $apiAsistencias);
$ok[] = writeFile("$BASE/api/gym/clases.php",      $apiClases);
$ok[] = writeFile("$BASE/api/gym/pagos.php",       $apiPagos);

// ═══════════════════════════════════════════════════════════════════════════
// VIEWS/GYM/SOCIOS.PHP
// ═══════════════════════════════════════════════════════════════════════════
ob_start(); ?>
<?php echo '<?php'; ?>

session_start();
if (!isset($_SESSION['negocio_id'])) { header('Location: ../../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socios - Gimnasio</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--gym-color:#f97316;--gym-dark:#ea6c0a}
        .gym-header{background:linear-gradient(135deg,#1c1917 0%,#292524 100%);border-bottom:3px solid var(--gym-color);padding:20px 24px;display:flex;align-items:center;gap:16px}
        .gym-header-icon{width:48px;height:48px;background:var(--gym-color);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff}
        .gym-header h1{margin:0;font-size:22px;color:#fff}.gym-header p{margin:2px 0 0;font-size:13px;color:#a8a29e}
        .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;padding:20px 24px}
        .stat-card{background:var(--card-bg,#1e293b);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:4px;border:1px solid rgba(255,255,255,.06)}
        .stat-card .stat-val{font-size:28px;font-weight:700;color:#f1f5f9}.stat-card .stat-lbl{font-size:12px;color:#94a3b8}
        .stat-card.activos .stat-val{color:#22c55e}.stat-card.vencidos .stat-val{color:#ef4444}.stat-card.suspendidos .stat-val{color:#f59e0b}.stat-card.por-vencer .stat-val{color:#f97316}
        .toolbar{padding:0 24px 16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center}
        .search-box{flex:1;min-width:200px;position:relative}
        .search-box input{width:100%;background:var(--input-bg,#1e293b);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:10px 14px 10px 40px;color:#f1f5f9;font-size:14px;box-sizing:border-box}
        .search-box i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b}
        .filter-tabs{display:flex;gap:6px;flex-wrap:wrap}
        .filter-tab{padding:8px 14px;border-radius:20px;border:1px solid rgba(255,255,255,.1);background:transparent;color:#94a3b8;cursor:pointer;font-size:13px;transition:all .2s}
        .filter-tab.active,.filter-tab:hover{background:var(--gym-color);color:#fff;border-color:var(--gym-color)}
        .btn-add{background:var(--gym-color);color:#fff;border:none;padding:10px 18px;border-radius:10px;cursor:pointer;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;transition:background .2s;white-space:nowrap}
        .btn-add:hover{background:var(--gym-dark)}
        .socios-table-wrap{padding:0 24px 24px;overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:14px}
        th{text-align:left;padding:10px 14px;background:rgba(255,255,255,.04);color:#64748b;font-weight:600;border-bottom:1px solid rgba(255,255,255,.06);white-space:nowrap}
        td{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);color:#e2e8f0;vertical-align:middle}
        tr:hover td{background:rgba(255,255,255,.03)}
        .badge-estado{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600}
        .badge-activo{background:rgba(34,197,94,.15);color:#22c55e}.badge-vencido{background:rgba(239,68,68,.15);color:#ef4444}
        .badge-suspendido{background:rgba(245,158,11,.15);color:#f59e0b}.badge-inactivo{background:rgba(100,116,139,.15);color:#64748b}
        .dias-badge{display:inline-block;padding:3px 8px;border-radius:8px;font-size:12px;font-weight:600}
        .dias-ok{background:rgba(34,197,94,.15);color:#22c55e}.dias-warn{background:rgba(249,115,22,.15);color:#f97316}.dias-venc{background:rgba(239,68,68,.15);color:#ef4444}
        .btn-row{display:flex;gap:6px}.btn-icon{width:32px;height:32px;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:all .2s}
        .btn-edit{background:rgba(59,130,246,.15);color:#3b82f6}.btn-edit:hover{background:#3b82f6;color:#fff}
        .btn-renew{background:rgba(34,197,94,.15);color:#22c55e}.btn-renew:hover{background:#22c55e;color:#fff}
        .btn-asist{background:rgba(249,115,22,.15);color:#f97316}.btn-asist:hover{background:#f97316;color:#fff}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center;padding:20px}
        .modal-overlay.open{display:flex}
        .modal{background:#1e293b;border-radius:16px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.5)}
        .modal-header{padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between}
        .modal-header h2{margin:0;font-size:18px;color:#f1f5f9}
        .modal-close{background:none;border:none;color:#64748b;cursor:pointer;font-size:20px;padding:4px}.modal-close:hover{color:#f1f5f9}
        .modal-body{padding:24px}.modal-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.08);display:flex;gap:10px;justify-content:flex-end}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.form-grid .full{grid-column:1/-1}
        .form-group label{display:block;margin-bottom:6px;font-size:13px;color:#94a3b8}
        .form-group input,.form-group select,.form-group textarea{width:100%;box-sizing:border-box;background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:10px 12px;color:#f1f5f9;font-size:14px}
        .form-group textarea{resize:vertical;min-height:80px}
        .btn-primary{background:var(--gym-color);color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600}
        .btn-cancel{background:rgba(255,255,255,.08);color:#94a3b8;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px}
        .pago-section{margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.08)}
        .pago-section h3{margin:0 0 12px;font-size:14px;color:#94a3b8}
        .empty-state{text-align:center;padding:60px 20px;color:#475569}.empty-state i{font-size:48px;margin-bottom:16px;display:block}
        .loading{text-align:center;padding:40px;color:#64748b}
        .toast{position:fixed;bottom:24px;right:24px;z-index:9999;background:#1e293b;color:#f1f5f9;border-radius:12px;padding:14px 20px;box-shadow:0 8px 32px rgba(0,0,0,.4);display:none;align-items:center;gap:12px;max-width:320px;border-left:4px solid var(--gym-color)}
        .toast.show{display:flex}.toast.error{border-color:#ef4444}
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="gym-header">
            <div class="gym-header-icon"><i class="fas fa-dumbbell"></i></div>
            <div><h1>Socios</h1><p>Gestión de membresías y estados</p></div>
        </div>
        <div class="stats-row">
            <div class="stat-card"><span class="stat-val" id="statTotal">—</span><span class="stat-lbl">Total socios</span></div>
            <div class="stat-card activos"><span class="stat-val" id="statActivos">—</span><span class="stat-lbl">Activos</span></div>
            <div class="stat-card vencidos"><span class="stat-val" id="statVencidos">—</span><span class="stat-lbl">Vencidos</span></div>
            <div class="stat-card suspendidos"><span class="stat-val" id="statSuspendidos">—</span><span class="stat-lbl">Suspendidos</span></div>
            <div class="stat-card por-vencer"><span class="stat-val" id="statPorVencer">—</span><span class="stat-lbl">Vencen pronto</span></div>
        </div>
        <div class="toolbar">
            <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Buscar por nombre, email o teléfono..." oninput="onSearch(this.value)"></div>
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="setFiltro('',this)">Todos</button>
                <button class="filter-tab" onclick="setFiltro('activo',this)">Activos</button>
                <button class="filter-tab" onclick="setFiltro('vencido',this)">Vencidos</button>
                <button class="filter-tab" onclick="setFiltro('suspendido',this)">Suspendidos</button>
            </div>
            <button class="btn-add" onclick="abrirModalNuevo()"><i class="fas fa-plus"></i> Nuevo Socio</button>
        </div>
        <div class="socios-table-wrap">
            <div id="loadingState" class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            <table id="sociosTable" style="display:none">
                <thead><tr><th>#</th><th>Socio</th><th>Teléfono</th><th>Plan</th><th>Estado</th><th>Vencimiento</th><th>Acciones</th></tr></thead>
                <tbody id="sociosTbody"></tbody>
            </table>
            <div id="emptyState" class="empty-state" style="display:none"><i class="fas fa-users-slash"></i><p>No se encontraron socios</p></div>
        </div>
    </div>
</div>
<!-- Modal Socio -->
<div class="modal-overlay" id="modalSocio">
    <div class="modal">
        <div class="modal-header"><h2 id="modalTitle">Nuevo Socio</h2><button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button></div>
        <div class="modal-body">
            <input type="hidden" id="socioId">
            <div class="form-grid">
                <div class="form-group"><label>Nombre *</label><input type="text" id="fNombre"></div>
                <div class="form-group"><label>Apellido *</label><input type="text" id="fApellido"></div>
                <div class="form-group"><label>Email</label><input type="email" id="fEmail"></div>
                <div class="form-group"><label>Teléfono</label><input type="text" id="fTelefono"></div>
                <div class="form-group"><label>Plan</label><select id="fPlan"></select></div>
                <div class="form-group"><label>Fecha inicio</label><input type="date" id="fFechaInicio"></div>
                <div class="form-group" id="estadoGroup" style="display:none"><label>Estado</label><select id="fEstado"><option value="activo">Activo</option><option value="vencido">Vencido</option><option value="suspendido">Suspendido</option><option value="inactivo">Inactivo</option></select></div>
                <div class="form-group full"><label>Notas</label><textarea id="fNotas"></textarea></div>
            </div>
            <div class="pago-section" id="pagoSection">
                <h3><i class="fas fa-dollar-sign"></i> Pago inicial (opcional)</h3>
                <div class="form-grid">
                    <div class="form-group"><label>Monto</label><input type="number" id="fMonto" step="0.01"></div>
                    <div class="form-group"><label>Método</label><select id="fMetodo"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option></select></div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn-cancel" onclick="cerrarModal()">Cancelar</button><button class="btn-primary" onclick="guardarSocio()"><i class="fas fa-save"></i> Guardar</button></div>
    </div>
</div>
<!-- Modal Renovar -->
<div class="modal-overlay" id="modalRenovar">
    <div class="modal">
        <div class="modal-header"><h2>Renovar Membresía</h2><button class="modal-close" onclick="cerrarModalRenovar()"><i class="fas fa-times"></i></button></div>
        <div class="modal-body">
            <input type="hidden" id="renovarSocioId">
            <p id="renovarSocioNombre" style="color:#f1f5f9;margin:0 0 16px;font-size:16px;font-weight:600;"></p>
            <div class="form-grid">
                <div class="form-group"><label>Plan</label><select id="rPlan"></select></div>
                <div class="form-group"><label>Fecha inicio</label><input type="date" id="rFechaInicio"></div>
                <div class="form-group"><label>Monto *</label><input type="number" id="rMonto" step="0.01"></div>
                <div class="form-group"><label>Método</label><select id="rMetodo"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option></select></div>
                <div class="form-group full"><label>Notas</label><input type="text" id="rNotas"></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn-cancel" onclick="cerrarModalRenovar()">Cancelar</button><button class="btn-primary" onclick="confirmarRenovar()"><i class="fas fa-rotate"></i> Renovar</button></div>
    </div>
</div>
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>
<script>
let socios=[],planes=[],filtroEstado='',searchTimeout;
async function init(){await cargarPlanes();cargarSocios();}
async function cargarPlanes(){try{const r=await fetch('../../api/gym/planes.php');const d=await r.json();if(d.success){planes=d.data;const opts='<option value="">Sin plan</option>'+planes.map(p=>`<option value="${p.id}" data-precio="${p.precio}">${p.nombre} - $${parseFloat(p.precio).toLocaleString('es-AR')}</option>`).join('');document.getElementById('fPlan').innerHTML=opts;document.getElementById('rPlan').innerHTML=opts;}}catch(e){console.error('Error cargando planes:',e);}}
async function cargarSocios(q=''){document.getElementById('loadingState').style.display='block';document.getElementById('sociosTable').style.display='none';document.getElementById('emptyState').style.display='none';try{let url=`../../api/gym/socios.php?estado=${filtroEstado}`;if(q)url+=`&q=${encodeURIComponent(q)}`;const r=await fetch(url);const d=await r.json();document.getElementById('loadingState').style.display='none';if(d.success){socios=d.data.socios||[];const stats=d.data.stats||{};document.getElementById('statTotal').textContent=stats.total||0;document.getElementById('statActivos').textContent=stats.activos||0;document.getElementById('statVencidos').textContent=stats.vencidos||0;document.getElementById('statSuspendidos').textContent=stats.suspendidos||0;document.getElementById('statPorVencer').textContent=stats.por_vencer||0;renderTabla(socios);}}catch(e){document.getElementById('loadingState').style.display='none';document.getElementById('emptyState').style.display='block';console.error(e);}}
function renderTabla(lista){const tbody=document.getElementById('sociosTbody');if(!lista.length){document.getElementById('emptyState').style.display='block';return;}document.getElementById('sociosTable').style.display='table';const badges={activo:'<span class="badge-estado badge-activo">● Activo</span>',vencido:'<span class="badge-estado badge-vencido">● Vencido</span>',suspendido:'<span class="badge-estado badge-suspendido">● Suspendido</span>',inactivo:'<span class="badge-estado badge-inactivo">● Inactivo</span>'};tbody.innerHTML=lista.map((s,i)=>{let db='—';if(s.fecha_vencimiento){const dias=parseInt(s.dias_restantes);if(dias<0)db=`<span class="dias-badge dias-venc">Vencido</span>`;else if(dias<=7)db=`<span class="dias-badge dias-warn">${dias}d</span>`;else db=`<span class="dias-badge dias-ok">${dias}d</span>`;}return`<tr><td style="color:#475569">${i+1}</td><td><div style="font-weight:600;color:#f1f5f9">${s.nombre} ${s.apellido}</div><div style="font-size:12px;color:#64748b">${s.email||'—'}</div></td><td>${s.telefono||'—'}</td><td>${s.plan_nombre?`<span style="color:#f97316;font-weight:500">${s.plan_nombre}</span>`:'<span style="color:#475569">Sin plan</span>'}</td><td>${badges[s.estado]||s.estado}</td><td>${s.fecha_vencimiento?`${s.fecha_vencimiento}<br>${db}`:'—'}</td><td><div class="btn-row"><button class="btn-icon btn-asist" title="Asistencia" onclick="regAsist(${s.id},'${s.nombre} ${s.apellido}')"><i class="fas fa-fingerprint"></i></button><button class="btn-icon btn-renew" title="Renovar" onclick="abrirRenovar(${s.id},'${s.nombre} ${s.apellido}',${s.plan_id||0})"><i class="fas fa-rotate"></i></button><button class="btn-icon btn-edit" title="Editar" onclick="abrirEditar(${s.id})"><i class="fas fa-pen"></i></button></div></td></tr>`;}).join('');}
function onSearch(v){clearTimeout(searchTimeout);searchTimeout=setTimeout(()=>cargarSocios(v),400);}
function setFiltro(e,btn){filtroEstado=e;document.querySelectorAll('.filter-tab').forEach(b=>b.classList.remove('active'));btn.classList.add('active');cargarSocios(document.getElementById('searchInput').value);}
function abrirModalNuevo(){document.getElementById('modalTitle').textContent='Nuevo Socio';document.getElementById('socioId').value='';['fNombre','fApellido','fEmail','fTelefono','fNotas','fMonto'].forEach(id=>document.getElementById(id).value='');document.getElementById('fPlan').value='';document.getElementById('fFechaInicio').value=new Date().toISOString().split('T')[0];document.getElementById('estadoGroup').style.display='none';document.getElementById('pagoSection').style.display='block';document.getElementById('modalSocio').classList.add('open');}
function abrirEditar(id){const s=socios.find(x=>x.id==id);if(!s)return;document.getElementById('modalTitle').textContent='Editar Socio';document.getElementById('socioId').value=s.id;document.getElementById('fNombre').value=s.nombre;document.getElementById('fApellido').value=s.apellido;document.getElementById('fEmail').value=s.email||'';document.getElementById('fTelefono').value=s.telefono||'';document.getElementById('fPlan').value=s.plan_id||'';document.getElementById('fFechaInicio').value=s.fecha_inicio||'';document.getElementById('fNotas').value=s.notas||'';document.getElementById('fEstado').value=s.estado;document.getElementById('estadoGroup').style.display='block';document.getElementById('pagoSection').style.display='none';document.getElementById('modalSocio').classList.add('open');}
function cerrarModal(){document.getElementById('modalSocio').classList.remove('open');}
async function guardarSocio(){const id=document.getElementById('socioId').value;const nombre=document.getElementById('fNombre').value.trim();const apellido=document.getElementById('fApellido').value.trim();if(!nombre||!apellido){showToast('Nombre y apellido requeridos',true);return;}const body={nombre,apellido,email:document.getElementById('fEmail').value.trim(),telefono:document.getElementById('fTelefono').value.trim(),plan_id:document.getElementById('fPlan').value||null,fecha_inicio:document.getElementById('fFechaInicio').value,notas:document.getElementById('fNotas').value.trim()};if(id){body.id=id;body.estado=document.getElementById('fEstado').value;}else{const monto=parseFloat(document.getElementById('fMonto').value);if(monto>0){body.monto=monto;body.metodo=document.getElementById('fMetodo').value;}}const r=await fetch('../../api/gym/socios.php',{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});const d=await r.json();if(d.success){showToast(id?'Socio actualizado':'Socio creado');cerrarModal();cargarSocios(document.getElementById('searchInput').value);}else showToast(d.message||'Error',true);}
function abrirRenovar(id,nombre,planId){document.getElementById('renovarSocioId').value=id;document.getElementById('renovarSocioNombre').textContent=nombre;document.getElementById('rPlan').value=planId||'';document.getElementById('rFechaInicio').value=new Date().toISOString().split('T')[0];document.getElementById('rMonto').value='';document.getElementById('rNotas').value='';if(planId){const opt=document.querySelector(`#rPlan option[value="${planId}"]`);if(opt)document.getElementById('rMonto').value=opt.dataset.precio;}document.getElementById('modalRenovar').classList.add('open');}
document.getElementById('rPlan').addEventListener('change',function(){const opt=this.options[this.selectedIndex];if(opt&&opt.dataset.precio)document.getElementById('rMonto').value=opt.dataset.precio;});
function cerrarModalRenovar(){document.getElementById('modalRenovar').classList.remove('open');}
async function confirmarRenovar(){const socio_id=document.getElementById('renovarSocioId').value;const plan_id=document.getElementById('rPlan').value;const monto=parseFloat(document.getElementById('rMonto').value);const fecha=document.getElementById('rFechaInicio').value;const metodo=document.getElementById('rMetodo').value;const notas=document.getElementById('rNotas').value;if(!monto||monto<=0){showToast('Ingresá el monto',true);return;}const r=await fetch('../../api/gym/pagos.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({socio_id,plan_id,monto,fecha,metodo,notas,periodo_desde:fecha})});const d=await r.json();if(d.success){showToast('Membresía renovada');cerrarModalRenovar();cargarSocios(document.getElementById('searchInput').value);}else showToast(d.message||'Error',true);}
async function regAsist(socio_id,nombre){const hora=new Date().toTimeString().split(' ')[0].substring(0,5);const r=await fetch('../../api/gym/asistencias.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({socio_id,fecha:new Date().toISOString().split('T')[0],hora})});const d=await r.json();if(d.success)showToast('Asistencia de '+nombre+' registrada');else showToast(d.message||'Error',true);}
function showToast(msg,error=false){const t=document.getElementById('toast');document.getElementById('toastMsg').textContent=msg;t.className='toast show'+(error?' error':'');setTimeout(()=>t.classList.remove('show'),3500);}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){cerrarModal();cerrarModalRenovar();}});
document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',e=>{if(e.target===m){cerrarModal();cerrarModalRenovar();}}));
init();
</script>
</body>
</html>
<?php
$viewSocios = ob_get_clean();
$ok[] = writeFile("$BASE/views/gym/socios.php", $viewSocios);

// ═══════════════════════════════════════════════════════════════════════════
// VIEWS/GYM/CLASES.PHP
// ═══════════════════════════════════════════════════════════════════════════
ob_start(); ?>
<?php echo '<?php'; ?>

session_start();
if (!isset($_SESSION['negocio_id'])) { header('Location: ../../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clases - Gimnasio</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--gym-color:#f97316;--gym-dark:#ea6c0a}
        .gym-header{background:linear-gradient(135deg,#1c1917,#292524);border-bottom:3px solid var(--gym-color);padding:20px 24px;display:flex;align-items:center;gap:16px}
        .gym-header-icon{width:48px;height:48px;background:var(--gym-color);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff}
        .gym-header h1{margin:0;font-size:22px;color:#fff}.gym-header p{margin:2px 0 0;font-size:13px;color:#a8a29e}
        .page-toolbar{padding:20px 24px;display:flex;justify-content:space-between;align-items:center}
        .page-toolbar h2{margin:0;color:#f1f5f9;font-size:16px}
        .btn-add{background:var(--gym-color);color:#fff;border:none;padding:10px 18px;border-radius:10px;cursor:pointer;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px}
        .btn-add:hover{background:var(--gym-dark)}
        .schedule-wrap{padding:0 24px 24px;overflow-x:auto}
        .schedule-grid{display:grid;grid-template-columns:repeat(7,minmax(140px,1fr));gap:12px;min-width:900px}
        .day-col{display:flex;flex-direction:column;gap:8px}
        .day-header{background:rgba(255,255,255,.06);border-radius:10px;padding:10px;text-align:center;font-size:13px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
        .day-header.today-col{background:rgba(249,115,22,.2);color:var(--gym-color);border:1px solid rgba(249,115,22,.4)}
        .clase-card{border-radius:10px;padding:12px;cursor:pointer;transition:transform .15s,opacity .15s;position:relative}
        .clase-card:hover{transform:translateY(-2px);opacity:.9}
        .clase-card .cn{font-weight:700;font-size:14px;color:#fff;margin-bottom:4px}
        .clase-card .ch{font-size:12px;color:rgba(255,255,255,.8);display:flex;align-items:center;gap:4px}
        .clase-card .ci{font-size:11px;color:rgba(255,255,255,.7);margin-top:4px;display:flex;align-items:center;gap:4px}
        .clase-card .cc{font-size:11px;color:rgba(255,255,255,.7);margin-top:2px;display:flex;align-items:center;gap:4px}
        .btn-del{position:absolute;top:6px;right:6px;background:rgba(0,0,0,.3);border:none;border-radius:6px;width:22px;height:22px;color:rgba(255,255,255,.7);cursor:pointer;font-size:10px;display:none;align-items:center;justify-content:center}
        .clase-card:hover .btn-del{display:flex}.btn-del:hover{background:rgba(239,68,68,.7);color:#fff}
        .empty-day{text-align:center;padding:20px 10px;color:#334155;font-size:12px;border:1px dashed rgba(255,255,255,.06);border-radius:10px}
        .btn-add-c{background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.1);color:#475569;border-radius:8px;padding:8px;cursor:pointer;font-size:12px;width:100%;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:4px}
        .btn-add-c:hover{border-color:var(--gym-color);color:var(--gym-color)}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center;padding:20px}
        .modal-overlay.open{display:flex}
        .modal{background:#1e293b;border-radius:16px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.5)}
        .modal-header{padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between}
        .modal-header h2{margin:0;font-size:18px;color:#f1f5f9}
        .modal-close{background:none;border:none;color:#64748b;cursor:pointer;font-size:20px}
        .modal-body{padding:24px}.modal-footer{padding:16px 24px;border-top:1px solid rgba(255,255,255,.08);display:flex;gap:10px;justify-content:flex-end}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.form-grid .full{grid-column:1/-1}
        .form-group label{display:block;margin-bottom:6px;font-size:13px;color:#94a3b8}
        .form-group input,.form-group select{width:100%;box-sizing:border-box;background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:10px 12px;color:#f1f5f9;font-size:14px}
        .color-grid{display:flex;gap:8px;flex-wrap:wrap}
        .color-opt{width:28px;height:28px;border-radius:50%;cursor:pointer;border:3px solid transparent;transition:border-color .2s}
        .color-opt.selected{border-color:#fff}
        .btn-primary{background:var(--gym-color);color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600}
        .btn-cancel{background:rgba(255,255,255,.08);color:#94a3b8;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px}
        .loading{text-align:center;padding:60px;color:#64748b}
        .toast{position:fixed;bottom:24px;right:24px;z-index:9999;background:#1e293b;color:#f1f5f9;border-radius:12px;padding:14px 20px;box-shadow:0 8px 32px rgba(0,0,0,.4);display:none;align-items:center;gap:12px;max-width:300px;border-left:4px solid var(--gym-color)}
        .toast.show{display:flex}.toast.error{border-color:#ef4444}
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="gym-header">
            <div class="gym-header-icon"><i class="fas fa-calendar-week"></i></div>
            <div><h1>Horario de Clases</h1><p>Grilla semanal de actividades grupales</p></div>
        </div>
        <div class="page-toolbar"><h2>Semana actual</h2><button class="btn-add" onclick="abrirModal()"><i class="fas fa-plus"></i> Nueva Clase</button></div>
        <div class="schedule-wrap">
            <div id="loadingState" class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            <div id="scheduleGrid" class="schedule-grid" style="display:none"></div>
        </div>
    </div>
</div>
<div class="modal-overlay" id="modalClase">
    <div class="modal">
        <div class="modal-header"><h2 id="modalTitle">Nueva Clase</h2><button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button></div>
        <div class="modal-body">
            <input type="hidden" id="claseId">
            <div class="form-grid">
                <div class="form-group full"><label>Nombre *</label><input type="text" id="fNombre" placeholder="Spinning, Yoga, CrossFit..."></div>
                <div class="form-group full"><label>Instructor</label><input type="text" id="fInstructor"></div>
                <div class="form-group"><label>Día</label><select id="fDia"><option value="0">Lunes</option><option value="1">Martes</option><option value="2">Miércoles</option><option value="3">Jueves</option><option value="4">Viernes</option><option value="5">Sábado</option><option value="6">Domingo</option></select></div>
                <div class="form-group"><label>Hora</label><input type="time" id="fHora" value="09:00"></div>
                <div class="form-group"><label>Duración (min)</label><input type="number" id="fDuracion" value="60"></div>
                <div class="form-group"><label>Capacidad</label><input type="number" id="fCapacidad" value="20"></div>
                <div class="form-group full"><label>Color</label><div class="color-grid" id="colorGrid"></div><input type="hidden" id="fColor" value="#f97316"></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn-cancel" onclick="cerrarModal()">Cancelar</button><button class="btn-primary" onclick="guardarClase()"><i class="fas fa-save"></i> Guardar</button></div>
    </div>
</div>
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>
<script>
const DIAS=['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
const COLS=['#f97316','#ef4444','#3b82f6','#10b981','#8b5cf6','#ec4899','#f59e0b','#06b6d4','#64748b'];
let clases=[],selColor='#f97316';
function hoy(){const d=new Date().getDay();return d===0?6:d-1;}
function initColores(){document.getElementById('colorGrid').innerHTML=COLS.map(c=>`<div class="color-opt ${c===selColor?'selected':''}" style="background:${c}" onclick="pickColor('${c}',this)"></div>`).join('');}
function pickColor(c,el){selColor=c;document.getElementById('fColor').value=c;document.querySelectorAll('.color-opt').forEach(x=>x.classList.remove('selected'));el.classList.add('selected');}
async function cargarClases(){const r=await fetch('../../api/gym/clases.php');const d=await r.json();document.getElementById('loadingState').style.display='none';document.getElementById('scheduleGrid').style.display='grid';if(d.success){clases=d.data;renderGrilla();}}
function renderGrilla(){const h=hoy();const g=document.getElementById('scheduleGrid');g.innerHTML=DIAS.map((dia,i)=>{const cd=clases.filter(c=>parseInt(c.dia_semana)===i).sort((a,b)=>a.hora_inicio.localeCompare(b.hora_inicio));const ch=cd.length?cd.map(c=>`<div class="clase-card" style="background:${c.color}" onclick="abrirEditar(${c.id})"><div class="cn">${c.nombre}</div><div class="ch"><i class="fas fa-clock" style="font-size:10px"></i> ${c.hora_inicio.substring(0,5)} (${c.duracion_min}')</div>${c.instructor?`<div class="ci"><i class="fas fa-user" style="font-size:10px"></i> ${c.instructor}</div>`:''}<div class="cc"><i class="fas fa-users" style="font-size:10px"></i> Máx.${c.capacidad}</div><button class="btn-del" onclick="event.stopPropagation();delClase(${c.id})"><i class="fas fa-times"></i></button></div>`).join(''):'<div class="empty-day">Sin clases</div>';return`<div class="day-col"><div class="day-header ${i===h?'today-col':''}">${dia}${i===h?' ●':''}</div>${ch}<button class="btn-add-c" onclick="abrirModalDia(${i})"><i class="fas fa-plus"></i> Agregar</button></div>`;}).join('');}
function abrirModal(dia=null){document.getElementById('modalTitle').textContent='Nueva Clase';document.getElementById('claseId').value='';document.getElementById('fNombre').value='';document.getElementById('fInstructor').value='';document.getElementById('fDia').value=dia!==null?dia:hoy();document.getElementById('fHora').value='09:00';document.getElementById('fDuracion').value=60;document.getElementById('fCapacidad').value=20;selColor='#f97316';initColores();document.getElementById('modalClase').classList.add('open');}
function abrirModalDia(dia){abrirModal(dia);}
function abrirEditar(id){const c=clases.find(x=>x.id==id);if(!c)return;document.getElementById('modalTitle').textContent='Editar Clase';document.getElementById('claseId').value=c.id;document.getElementById('fNombre').value=c.nombre;document.getElementById('fInstructor').value=c.instructor||'';document.getElementById('fDia').value=c.dia_semana;document.getElementById('fHora').value=c.hora_inicio.substring(0,5);document.getElementById('fDuracion').value=c.duracion_min;document.getElementById('fCapacidad').value=c.capacidad;selColor=c.color||'#f97316';initColores();document.getElementById('modalClase').classList.add('open');}
function cerrarModal(){document.getElementById('modalClase').classList.remove('open');}
async function guardarClase(){const id=document.getElementById('claseId').value;const nombre=document.getElementById('fNombre').value.trim();if(!nombre){showToast('Nombre requerido',true);return;}const body={nombre,instructor:document.getElementById('fInstructor').value.trim(),dia_semana:parseInt(document.getElementById('fDia').value),hora_inicio:document.getElementById('fHora').value,duracion_min:parseInt(document.getElementById('fDuracion').value),capacidad:parseInt(document.getElementById('fCapacidad').value),color:document.getElementById('fColor').value};if(id)body.id=id;const r=await fetch('../../api/gym/clases.php',{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});const d=await r.json();if(d.success){showToast(id?'Clase actualizada':'Clase creada');cerrarModal();cargarClases();}else showToast(d.message||'Error',true);}
async function delClase(id){if(!confirm('¿Eliminar esta clase?'))return;const r=await fetch(`../../api/gym/clases.php?id=${id}`,{method:'DELETE'});const d=await r.json();if(d.success){showToast('Clase eliminada');cargarClases();}else showToast(d.message||'Error',true);}
function showToast(msg,e=false){const t=document.getElementById('toast');document.getElementById('toastMsg').textContent=msg;t.className='toast show'+(e?' error':'');setTimeout(()=>t.classList.remove('show'),3500);}
document.addEventListener('keydown',e=>{if(e.key==='Escape')cerrarModal();});
document.getElementById('modalClase').addEventListener('click',e=>{if(e.target.id==='modalClase')cerrarModal();});
initColores();cargarClases();
</script>
</body>
</html>
<?php
$viewClases = ob_get_clean();
$ok[] = writeFile("$BASE/views/gym/clases.php", $viewClases);

// ═══════════════════════════════════════════════════════════════════════════
// VIEWS/GYM/ASISTENCIAS.PHP
// ═══════════════════════════════════════════════════════════════════════════
ob_start(); ?>
<?php echo '<?php'; ?>

session_start();
if (!isset($_SESSION['negocio_id'])) { header('Location: ../../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias - Gimnasio</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--gym-color:#f97316;--gym-dark:#ea6c0a}
        .gym-header{background:linear-gradient(135deg,#1c1917,#292524);border-bottom:3px solid var(--gym-color);padding:20px 24px;display:flex;align-items:center;gap:16px}
        .gym-header-icon{width:48px;height:48px;background:var(--gym-color);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff}
        .gym-header h1{margin:0;font-size:22px;color:#fff}.gym-header p{margin:2px 0 0;font-size:13px;color:#a8a29e}
        .checkin-section{padding:20px 24px;display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start}
        @media(max-width:900px){.checkin-section{grid-template-columns:1fr}}
        .checkin-panel{background:#1e293b;border-radius:16px;padding:24px;border:1px solid rgba(255,255,255,.06)}
        .checkin-panel h2{margin:0 0 20px;font-size:16px;color:#f1f5f9;display:flex;align-items:center;gap:10px}
        .date-nav{display:flex;align-items:center;gap:12px;margin-bottom:24px}
        .date-nav button{background:rgba(255,255,255,.08);border:none;color:#94a3b8;width:36px;height:36px;border-radius:10px;cursor:pointer;font-size:14px}
        .date-nav button:hover{background:rgba(255,255,255,.15);color:#f1f5f9}
        .date-nav .dd{flex:1;text-align:center;color:#f1f5f9;font-weight:600;font-size:15px}
        .search-socio{position:relative;margin-bottom:12px}
        .search-socio input{width:100%;box-sizing:border-box;background:#0f172a;border:2px solid rgba(255,255,255,.1);border-radius:12px;padding:14px 14px 14px 44px;color:#f1f5f9;font-size:15px;transition:border-color .2s}
        .search-socio input:focus{outline:none;border-color:var(--gym-color)}
        .search-socio i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#64748b;font-size:16px}
        .search-results{background:#0f172a;border-radius:10px;border:1px solid rgba(255,255,255,.08);max-height:200px;overflow-y:auto;margin-bottom:12px;display:none}
        .search-results.show{display:block}
        .sri{padding:12px 16px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;justify-content:space-between}
        .sri:hover{background:rgba(249,115,22,.08)}.sri:last-child{border-bottom:none}
        .sri-name{color:#f1f5f9;font-weight:500;font-size:14px}.sri-plan{color:#64748b;font-size:12px}
        .badge-sm{padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-sm.activo{background:rgba(34,197,94,.15);color:#22c55e}.badge-sm.vencido{background:rgba(239,68,68,.15);color:#ef4444}.badge-sm.suspendido{background:rgba(245,158,11,.15);color:#f59e0b}
        .btn-checkin{width:100%;background:var(--gym-color);color:#fff;border:none;padding:14px;border-radius:12px;font-size:16px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .2s}
        .btn-checkin:hover{background:var(--gym-dark)}.btn-checkin:disabled{background:#374151;cursor:not-allowed}
        .day-panel{background:#1e293b;border-radius:16px;border:1px solid rgba(255,255,255,.06);overflow:hidden}
        .dph{padding:16px 20px;background:rgba(249,115,22,.08);border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between}
        .dph h3{margin:0;font-size:15px;color:#f1f5f9}
        .cb{background:var(--gym-color);color:#fff;border-radius:20px;padding:4px 12px;font-size:13px;font-weight:700}
        .asist-list{max-height:500px;overflow-y:auto}
        .ai{padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;gap:12px}
        .ai:last-child{border-bottom:none}
        .av{width:36px;height:36px;border-radius:50%;background:rgba(249,115,22,.15);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:var(--gym-color);flex-shrink:0}
        .ainf{flex:1;min-width:0}.aname{color:#f1f5f9;font-size:14px;font-weight:500}.ahora{color:#64748b;font-size:12px}
        .adel{background:none;border:none;color:#374151;cursor:pointer;font-size:13px;transition:color .2s;padding:4px}
        .adel:hover{color:#ef4444}
        .empty-day{text-align:center;padding:40px 20px;color:#475569}.empty-day i{font-size:36px;margin-bottom:12px;display:block}
        .toast{position:fixed;bottom:24px;right:24px;z-index:9999;background:#1e293b;color:#f1f5f9;border-radius:12px;padding:14px 20px;box-shadow:0 8px 32px rgba(0,0,0,.4);display:none;align-items:center;gap:12px;max-width:300px;border-left:4px solid var(--gym-color)}
        .toast.show{display:flex}.toast.error{border-color:#ef4444}.toast.warn{border-color:#f59e0b}
        .sel-info{background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.3);border-radius:10px;padding:12px;margin-bottom:12px;display:none}
        .sel-info.show{display:block}
        .sel-info .sn{color:#f1f5f9;font-weight:600;font-size:15px}.sel-info .sp{color:#94a3b8;font-size:13px;margin-top:2px}.sel-info .ss{margin-top:6px}
    </style>
</head>
<body>
<div class="app-layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="gym-header">
            <div class="gym-header-icon"><i class="fas fa-fingerprint"></i></div>
            <div><h1>Control de Asistencias</h1><p>Check-in diario de socios</p></div>
        </div>
        <div class="checkin-section">
            <div class="checkin-panel">
                <h2><i class="fas fa-calendar-day" style="color:var(--gym-color)"></i> Registrar entrada</h2>
                <div class="date-nav">
                    <button onclick="cambiarFecha(-1)"><i class="fas fa-chevron-left"></i></button>
                    <div class="dd" id="fechaDisplay">—</div>
                    <button onclick="cambiarFecha(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="search-socio">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchSocio" placeholder="Buscar socio por nombre..." oninput="buscarSocio(this.value)" autocomplete="off">
                </div>
                <div class="search-results" id="searchResults"></div>
                <div class="sel-info" id="selInfo">
                    <div class="sn" id="selName"></div>
                    <div class="sp" id="selPlan"></div>
                    <div class="ss" id="selStatus"></div>
                </div>
                <button class="btn-checkin" id="btnCheckin" disabled onclick="registrarEntrada()">
                    <i class="fas fa-fingerprint"></i> Registrar Entrada
                </button>
            </div>
            <div class="day-panel">
                <div class="dph"><h3><i class="fas fa-list-check"></i> Entradas del día</h3><span class="cb" id="counter">0</span></div>
                <div class="asist-list" id="asistList"><div class="empty-day"><i class="fas fa-person-walking"></i><p>Sin entradas aún</p></div></div>
            </div>
        </div>
    </div>
</div>
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>
<script>
let fa=new Date().toISOString().split('T')[0],ss=null,st;
function fmt(f){const[y,m,d]=f.split('-');const D=['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];const M=['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];const dt=new Date(f+'T12:00:00');const hoy=f===new Date().toISOString().split('T')[0];return`${hoy?'Hoy, ':''}${D[dt.getDay()]} ${d} de ${M[parseInt(m)-1]} de ${y}`;}
function cambiarFecha(d){const dt=new Date(fa+'T12:00:00');dt.setDate(dt.getDate()+d);fa=dt.toISOString().split('T')[0];document.getElementById('fechaDisplay').textContent=fmt(fa);clearSel();cargarAsistencias();}
function clearSel(){ss=null;document.getElementById('searchSocio').value='';document.getElementById('searchResults').classList.remove('show');document.getElementById('selInfo').classList.remove('show');document.getElementById('btnCheckin').disabled=true;}
async function buscarSocio(q){clearTimeout(st);if(q.length<2){document.getElementById('searchResults').classList.remove('show');return;}st=setTimeout(async()=>{const r=await fetch(`../../api/gym/socios.php?q=${encodeURIComponent(q)}`);const d=await r.json();if(d.success){const lista=d.data.socios||[];const div=document.getElementById('searchResults');div.innerHTML=lista.length?lista.slice(0,8).map(s=>`<div class="sri" onclick='selSocio(${JSON.stringify(s)})'><div><div class="sri-name">${s.nombre} ${s.apellido}</div><div class="sri-plan">${s.plan_nombre||'Sin plan'} · ${s.telefono||''}</div></div><span class="badge-sm ${s.estado}">${s.estado}</span></div>`).join(''):'<div style="padding:12px 16px;color:#64748b;font-size:13px">Sin resultados</div>';div.classList.add('show');}},300);}
function selSocio(s){ss=s;document.getElementById('searchSocio').value=`${s.nombre} ${s.apellido}`;document.getElementById('searchResults').classList.remove('show');document.getElementById('selName').textContent=`${s.nombre} ${s.apellido}`;document.getElementById('selPlan').textContent=s.plan_nombre?`Plan: ${s.plan_nombre}`:'Sin plan activo';let sh='';if(s.estado==='activo'){const dias=parseInt(s.dias_restantes);sh=`<span class="badge-sm activo">Activo</span>${dias>=0?` · Vence en ${dias}d`:'`'}`;}else if(s.estado==='vencido'){sh=`<span class="badge-sm vencido">Membresía vencida</span>`;}else{sh=`<span class="badge-sm suspendido">${s.estado}</span>`;}document.getElementById('selStatus').innerHTML=sh;document.getElementById('selInfo').classList.add('show');document.getElementById('btnCheckin').disabled=false;}
async function registrarEntrada(){if(!ss)return;const hora=new Date().toTimeString().split(' ')[0].substring(0,5);const r=await fetch('../../api/gym/asistencias.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({socio_id:ss.id,fecha:fa,hora})});const d=await r.json();if(d.success){d.data.estado==='vencido'?showToast(`⚠️ ${d.data.socio} (membresía VENCIDA)`,'warn'):showToast(`✅ Entrada: ${d.data.socio}`);clearSel();cargarAsistencias();}else showToast(d.message||'Error','error');}
async function cargarAsistencias(){const r=await fetch(`../../api/gym/asistencias.php?fecha=${fa}`);const d=await r.json();if(d.success){const lista=d.data.asistencias||[];document.getElementById('counter').textContent=d.data.total||0;const div=document.getElementById('asistList');div.innerHTML=lista.length?lista.map(a=>`<div class="ai"><div class="av">${a.socio_nombre?a.socio_nombre.charAt(0).toUpperCase():'?'}</div><div class="ainf"><div class="aname">${a.socio_nombre||'?'}</div><div class="ahora"><i class="fas fa-clock" style="font-size:10px"></i> ${a.hora.substring(0,5)} · ${a.plan_nombre||'Sin plan'}</div></div><button class="adel" onclick="delAsist(${a.id})"><i class="fas fa-times"></i></button></div>`).join(''):'<div class="empty-day"><i class="fas fa-person-walking"></i><p>Sin entradas registradas</p></div>';}}
async function delAsist(id){if(!confirm('¿Eliminar este registro?'))return;const r=await fetch(`../../api/gym/asistencias.php?id=${id}`,{method:'DELETE'});const d=await r.json();if(d.success){showToast('Registro eliminado');cargarAsistencias();}else showToast(d.message||'Error','error');}
function showToast(msg,type=''){const t=document.getElementById('toast');document.getElementById('toastMsg').textContent=msg;t.className=`toast show${type?' '+type:''}`;setTimeout(()=>t.classList.remove('show'),4000);}
document.addEventListener('click',e=>{if(!e.target.closest('.search-socio')&&!e.target.closest('.search-results'))document.getElementById('searchResults').classList.remove('show');});
document.getElementById('fechaDisplay').textContent=fmt(fa);
cargarAsistencias();
</script>
</body>
</html>
<?php
$viewAsistencias = ob_get_clean();
$ok[] = writeFile("$BASE/views/gym/asistencias.php", $viewAsistencias);

// ─── Verificar password ──────────────────────────────────────────────────────
try {
    $stmtV = $pdo->prepare("SELECT password FROM usuarios WHERE usuario='gymdemo'");
    $stmtV->execute();
    $row = $stmtV->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify('admin123', $row['password'])) {
        $ok[] = "✅ Verificación: <b>gymdemo / admin123</b> funciona correctamente";
    } else {
        $errors[] = "❌ El hash del password no verifica";
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Gimnasio - DASHBASE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:#0f172a;color:#e2e8f0;font-family:system-ui,sans-serif;padding:40px 20px;min-height:100vh}
        .container{max-width:720px;margin:0 auto}
        h1{font-size:28px;color:#f97316;margin-bottom:8px;display:flex;align-items:center;gap:12px}
        .sub{color:#64748b;margin-bottom:32px;font-size:14px}
        .section{font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:1px;margin:24px 0 10px}
        .results{display:flex;flex-direction:column;gap:6px}
        .ri{padding:10px 14px;border-radius:8px;font-size:14px;background:#1e293b;border-left:4px solid #475569}
        .ri.ok{border-color:#22c55e}.ri.err{border-color:#ef4444;background:rgba(239,68,68,.06)}.ri.info{border-color:#3b82f6}
        .cta{background:linear-gradient(135deg,rgba(249,115,22,.15),rgba(249,115,22,.05));border:1px solid rgba(249,115,22,.4);border-radius:16px;padding:28px;margin-top:32px}
        .cta h2{color:#f97316;font-size:20px;margin-bottom:16px}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
        .ii{background:rgba(255,255,255,.05);border-radius:10px;padding:12px 16px}
        .ii .l{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
        .ii .v{font-size:16px;font-weight:700;color:#f1f5f9;margin-top:4px}
        .cta-btn{display:inline-flex;align-items:center;gap:10px;background:#f97316;color:#fff;text-decoration:none;padding:12px 24px;border-radius:10px;font-size:15px;font-weight:700;margin-right:8px;margin-top:8px;transition:background .2s}
        .cta-btn:hover{background:#ea6c0a}
        .cta-btn.sec{background:#1e293b;border:1px solid rgba(255,255,255,.1)}
        @media(max-width:600px){.info-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-dumbbell"></i> Instalador Módulo Gimnasio</h1>
    <p class="sub">DASHBASE · <?= date('d/m/Y H:i:s') ?></p>

    <div class="section">Base de Datos &amp; Archivos</div>
    <div class="results">
        <?php foreach ($ok as $msg): $cls = strpos($msg,'ℹ️')!==false?'info':'ok'; ?>
        <div class="ri <?= $cls ?>"><?= $msg ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $msg): ?>
        <div class="ri err"><?= $msg ?></div>
        <?php endforeach; ?>
    </div>

    <?php $total_err = count($errors); ?>
    <div class="cta">
        <h2><?= $total_err === 0 ? '✅ Instalación completada con éxito' : '⚠️ Instalación con ' . $total_err . ' advertencia(s)' ?></h2>
        <div class="info-grid">
            <div class="ii"><div class="l">Usuario</div><div class="v">gymdemo</div></div>
            <div class="ii"><div class="l">Contraseña</div><div class="v">admin123</div></div>
            <div class="ii"><div class="l">Negocio</div><div class="v">FitZone Gym</div></div>
            <div class="ii"><div class="l">Estado</div><div class="v" style="color:#22c55e">✅ Listo</div></div>
        </div>
        <a href="index.php" class="cta-btn"><i class="fas fa-sign-in-alt"></i> Ir al Login</a>
        <a href="views/gym/socios.php" class="cta-btn sec"><i class="fas fa-users"></i> Ver Socios</a>
    </div>
</div>
</body>
</html>
