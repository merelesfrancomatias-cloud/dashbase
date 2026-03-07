<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
$db = (new Database())->getConnection();
$cols = $db->query('DESCRIBE productos')->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";

// Try the actual query
try {
    $r = $db->query("SELECT p.*, c.nombre AS categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.negocio_id = 1 LIMIT 3");
    var_dump($r->fetchAll());
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
