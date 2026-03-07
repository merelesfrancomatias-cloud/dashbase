<?php
/**
 * Script de configuración y instalación de DASH CRM
 */

// Configuración de conexión sin especificar BD
$host = "localhost";
$username = "root";
$password = "";
$db_name = "dash_crm";

// Crear conexión sin seleccionar BD
$conn = new mysqli($host, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión: ' . $conn->connect_error
    ]));
}

// Intentar crear la base de datos
$sql = "CREATE DATABASE IF NOT EXISTS {$db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($conn->query($sql) !== TRUE) {
    die(json_encode([
        'success' => false,
        'message' => 'Error al crear BD: ' . $conn->error
    ]));
}

// Seleccionar la base de datos
$conn->select_db($db_name);

// Leer y ejecutar el archivo de esquema
$schema = file_get_contents(__DIR__ . '/config/database_schema.sql');

// Dividir por puntos y coma y ejecutar cada instrucción
$statements = array_filter(array_map('trim', explode(';', $schema)));

$errors = [];
foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    if (!$conn->query($statement)) {
        $errors[] = "Error: " . $conn->error . " - Consulta: " . substr($statement, 0, 50) . "...";
    }
}

$conn->close();

if (empty($errors)) {
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos instalada correctamente',
        'credentials' => [
            'usuario' => 'admin',
            'contraseña' => 'admin123'
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Se completó con errores',
        'errors' => $errors
    ]);
}
?>
