<?php
// Simular POST agregar_item
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['HTTP_HOST'] = 'localhost';
$_COOKIE = [];

// Necesitamos una sesión válida — buscar un usuario
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
$pdo = (new Database())->getConnection();

// Obtener primer negocio y usuario
$u = $pdo->query("SELECT id, negocio_id FROM usuarios LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$u) { echo "No hay usuarios\n"; exit; }

// Obtener primera comanda abierta
$c = $pdo->query("SELECT id FROM restaurant_comandas WHERE estado='abierta' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$c) { echo "No hay comandas abiertas\n"; exit; }

// Obtener primer producto
$p = $pdo->query("SELECT id, nombre, precio_venta FROM productos WHERE negocio_id={$u['negocio_id']} LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$p) { echo "No hay productos\n"; exit; }

echo "Usuario: {$u['id']}, Negocio: {$u['negocio_id']}\n";
echo "Comanda: {$c['id']}\n";
echo "Producto: {$p['id']} - {$p['nombre']} - {$p['precio_venta']}\n\n";

// Simular la inserción directamente
try {
    $pdo->prepare("
        INSERT INTO restaurant_comanda_items 
            (comanda_id, negocio_id, producto_id, nombre_item, precio_unit, cantidad, subtotal, estado_cocina, observaciones, sector_cocina)
        VALUES (:cid, :nid, :pid, :nombre, :precio, 1, :subtotal, 'pendiente', '', 'cocina')
    ")->execute([
        ':cid' => $c['id'],
        ':nid' => $u['negocio_id'],
        ':pid' => $p['id'],
        ':nombre' => $p['nombre'],
        ':precio' => $p['precio_venta'],
        ':subtotal' => $p['precio_venta'],
    ]);
    echo "INSERT OK\n";

    // Verificar que el item se guardó
    $items = $pdo->query("SELECT * FROM restaurant_comanda_items WHERE comanda_id={$c['id']} ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $i) {
        echo "  Item #{$i['id']}: {$i['nombre_item']} x{$i['cantidad']} = {$i['subtotal']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Verificar si la comanda actualiza el subtotal
try {
    $pdo->prepare("
        UPDATE restaurant_comandas c SET
            subtotal = (SELECT COALESCE(SUM(subtotal),0) FROM restaurant_comanda_items WHERE comanda_id=:cid AND estado_cocina != 'cancelado'),
            total    = subtotal - descuento
        WHERE id = :cid
    ")->execute([':cid' => $c['id']]);
    echo "UPDATE subtotal OK\n";
    $com = $pdo->query("SELECT subtotal,total FROM restaurant_comandas WHERE id={$c['id']}")->fetch(PDO::FETCH_ASSOC);
    echo "  subtotal={$com['subtotal']}, total={$com['total']}\n";
} catch (Exception $e) {
    echo "ERROR UPDATE: " . $e->getMessage() . "\n";
}
