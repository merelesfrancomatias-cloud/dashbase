<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=dashbase_local;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. Rubro canchas
$rubro = $pdo->query("SELECT id FROM rubros WHERE slug='canchas'")->fetch();
if ($rubro) {
    echo "Rubro canchas existe: id=" . $rubro['id'] . "\n";
    $rubroId = $rubro['id'];
} else {
    $pdo->exec("INSERT INTO rubros (nombre, slug, descripcion, icono, color) VALUES ('Alquiler de Canchas','canchas','Gestion de canchas deportivas','fa-futbol','#16a34a')");
    $rubroId = $pdo->lastInsertId();
    echo "Rubro canchas creado: id=$rubroId\n";
}

// 2. Negocio
$negocio = $pdo->query("SELECT id FROM negocios WHERE nombre='La Cancha Sportiva'")->fetch();
if ($negocio) {
    echo "Negocio existe: id=" . $negocio['id'] . "\n";
    $negocioId = $negocio['id'];
} else {
    $stmt = $pdo->prepare("INSERT INTO negocios (nombre, rubro_id, activo) VALUES (?, ?, 1)");
    $stmt->execute(['La Cancha Sportiva', $rubroId]);
    $negocioId = $pdo->lastInsertId();
    echo "Negocio creado: id=$negocioId\n";
}

// 3. Usuario - la columna es 'usuario' (no 'username')
$existeUser = $pdo->query("SELECT id FROM usuarios WHERE usuario='canchasdemo'")->fetch();
if ($existeUser) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE usuarios SET password=?, activo=1, negocio_id=? WHERE usuario='canchasdemo'")->execute([$hash, $negocioId]);
    echo "Usuario canchasdemo ya existia - password y negocio actualizados\n";
} else {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (negocio_id, nombre, apellido, usuario, email, password, rol, activo) VALUES (?, 'Demo', 'Canchas', 'canchasdemo', 'canchasdemo@demo.com', ?, 'admin', 1)");
    $stmt->execute([$negocioId, $hash]);
    echo "Usuario canchasdemo CREADO. ID=" . $pdo->lastInsertId() . "\n";
}

// 4. Verificar
$check = $pdo->query("SELECT id, usuario, negocio_id, activo FROM usuarios WHERE usuario='canchasdemo'")->fetch();
echo "Verificacion: id=" . $check['id'] . " usuario=" . $check['usuario'] . " negocio_id=" . $check['negocio_id'] . " activo=" . $check['activo'] . "\n";

// 5. Probar password
$userRow = $pdo->query("SELECT password FROM usuarios WHERE usuario='canchasdemo'")->fetch();
$ok = password_verify('admin123', $userRow['password']);
echo "Verify admin123: " . ($ok ? 'CORRECTO ✓' : 'FALLO ✗') . "\n";
echo "\n==> Login: canchasdemo / admin123\n";
