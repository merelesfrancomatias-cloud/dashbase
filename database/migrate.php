<?php
/**
 * migrate.php — Ejecutor de migraciones
 * 
 * Uso desde consola (raíz del proyecto):
 *   /Applications/XAMPP/bin/php database/migrate.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Recopilar migraciones de AMBAS carpetas
$schemaFiles = glob(__DIR__ . '/migrations/*.sql') ?: [];
$dataFiles   = array_map(
    fn($f) => 'config:' . $f,
    glob(__DIR__ . '/../config/migrations/*.sql') ?: []
);

sort($schemaFiles);
sort($dataFiles);

$allFiles = array_merge($schemaFiles, $dataFiles);

if (empty($allFiles)) {
    echo "No hay migraciones para ejecutar.\n";
    exit(0);
}

try {
    $db   = new Database();
    $conn = $db->getConnection();

    // Crear tabla de control de migraciones si no existe
    $conn->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            filename   VARCHAR(255) NOT NULL UNIQUE,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Obtener migraciones ya aplicadas
    $applied = $conn->query("SELECT filename FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

    $pendientes = 0;

    foreach ($allFiles as $entry) {
        // Soporte para entradas 'config:ruta'
        $isConfig = str_starts_with($entry, 'config:');
        $file     = $isConfig ? substr($entry, 7) : $entry;
        $filename = ($isConfig ? 'config_' : '') . basename($file);

        if (in_array($filename, $applied)) {
            echo "  [OK] $filename (ya aplicada)\n";
            continue;
        }

        echo "  [>>] Aplicando $filename ... ";

        $sql = file_get_contents($file);

        // Si el SQL contiene bloques condicionales [MERGE_IF_EXISTS:tabla],
        // verificar si la tabla existe; si no, eliminar ese bloque.
        $sql = preg_replace_callback(
            '/--\s*\[MERGE_IF_EXISTS:(\w+)\].*?--\s*\[\/MERGE_IF_EXISTS\]/s',
            function ($matches) use ($conn) {
                $tabla = $matches[1];
                $exists = $conn->query(
                    "SELECT COUNT(*) FROM information_schema.TABLES
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $conn->quote($tabla)
                )->fetchColumn();
                if ($exists) {
                    // Quitar solo los marcadores, dejar el SQL interior
                    $inner = preg_replace('/--\s*\[MERGE_IF_EXISTS:\w+\]|--\s*\[\/MERGE_IF_EXISTS\]/', '', $matches[0]);
                    return $inner;
                }
                // Tabla no existe → omitir todo el bloque
                return "-- Bloque MERGE_IF_EXISTS:$tabla omitido (tabla no existe)";
            },
            $sql
        );

        // Dividir en statements individuales, ignorar líneas vacías y comentarios
        // Se usa mysqli para soportar multi-statement (PREPARE/EXECUTE/SET @var)
        $dsn = $_ENV['DB_HOST'] ?? 'localhost';
        $mysqli = new mysqli(
            $_ENV['DB_HOST']     ?? 'localhost',
            $_ENV['DB_USER']     ?? 'root',
            $_ENV['DB_PASS']     ?? '',
            $_ENV['DB_NAME']     ?? 'dash4',
            (int)($_ENV['DB_PORT'] ?? 3306)
        );
        if ($mysqli->connect_errno) {
            throw new Exception("mysqli connect error: " . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');

        if (!$mysqli->multi_query($sql)) {
            throw new Exception($mysqli->error);
        }
        // Consumir todos los result sets
        do {
            if ($res = $mysqli->store_result()) {
                $res->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());

        if ($mysqli->errno) {
            throw new Exception("Error en migración: " . $mysqli->error);
        }
        $mysqli->close();

        // Registrar migración
        $stmt = $conn->prepare("INSERT INTO migrations (filename) VALUES (?)");
        $stmt->execute([$filename]);

        echo "OK\n";
        $pendientes++;
    }

    if ($pendientes === 0) {
        echo "\nTodo al día — no había migraciones pendientes.\n";
    } else {
        echo "\n$pendientes migración(es) aplicada(s) correctamente.\n";
    }

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}
