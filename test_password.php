<?php
// Script temporal para verificar contraseñas

$hash_db = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

$passwords_to_test = [
    'admin123',
    'password',
    'admin',
    '123456',
    'dash123',
    ''
];

echo "<h2>Verificación de Contraseñas</h2>";
echo "<p>Hash en BD: <code>$hash_db</code></p>";
echo "<hr>";

foreach ($passwords_to_test as $password) {
    $result = password_verify($password, $hash_db);
    $status = $result ? '✅ CORRECTA' : '❌ Incorrecta';
    echo "<p>Contraseña: '<strong>$password</strong>' - $status</p>";
}

echo "<hr>";
echo "<h3>Generar nuevo hash para 'admin123':</h3>";
$new_hash = password_hash('admin123', PASSWORD_BCRYPT);
echo "<p>Nuevo hash: <code>$new_hash</code></p>";

echo "<hr>";
echo "<h3>Consulta SQL para actualizar:</h3>";
echo "<pre>";
echo "UPDATE usuarios SET password = '$new_hash' WHERE usuario = 'admin';";
echo "</pre>";
?>
