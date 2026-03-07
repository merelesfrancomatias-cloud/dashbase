<?php
session_start();

// Simular sesión activa (usa tus datos reales de sesión)
if (!isset($_SESSION['user_id'])) {
    echo "⚠️ No hay sesión activa. Iniciando sesión de prueba...\n";
    // Necesitarás tener una sesión válida - modifica estos valores según tu base de datos
    // $_SESSION['user_id'] = 1;
    // $_SESSION['nombre'] = 'Admin';
    // $_SESSION['rol'] = 'admin';
}

echo "📋 Test de API Dashboard\n";
echo "========================\n\n";

require_once 'api/utils/Response.php';
require_once 'config/database.php';

try {
    echo "1️⃣ Verificando sesión...\n";
    if (!isset($_SESSION['user_id'])) {
        echo "   ❌ No hay sesión activa\n";
        echo "   💡 Debes iniciar sesión primero en el sistema\n";
        exit;
    }
    echo "   ✅ Sesión activa - User ID: " . $_SESSION['user_id'] . "\n\n";

    echo "2️⃣ Conectando a base de datos...\n";
    $db = new Database();
    $conn = $db->getConnection();
    echo "   ✅ Conexión exitosa\n\n";

    echo "3️⃣ Consultando productos...\n";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
    $productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   📦 Total productos: " . $productos . "\n\n";

    echo "4️⃣ Consultando clientes...\n";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
    $clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   👥 Total clientes: " . $clientes . "\n\n";

    echo "5️⃣ Consultando ventas de hoy...\n";
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total_ventas,
            COALESCE(SUM(total), 0) as total_monto
        FROM ventas 
        WHERE DATE(fecha_venta) = CURDATE()
    ");
    $ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   💰 Ventas hoy: " . $ventasHoy['total_ventas'] . " tickets\n";
    echo "   💵 Monto hoy: $" . number_format($ventasHoy['total_monto'], 2) . "\n\n";

    echo "6️⃣ Consultando ventas del mes...\n";
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total_ventas,
            COALESCE(SUM(total), 0) as total_monto
        FROM ventas 
        WHERE MONTH(fecha_venta) = MONTH(CURDATE()) 
        AND YEAR(fecha_venta) = YEAR(CURDATE())
    ");
    $ventasMes = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📅 Ventas mes: " . $ventasMes['total_ventas'] . " tickets\n";
    echo "   💵 Monto mes: $" . number_format($ventasMes['total_monto'], 2) . "\n\n";

    echo "✅ TODAS LAS CONSULTAS FUNCIONAN CORRECTAMENTE\n";
    echo "\n📊 Resumen:\n";
    echo "   - Productos: " . $productos . "\n";
    echo "   - Clientes: " . $clientes . "\n";
    echo "   - Ventas hoy: $" . number_format($ventasHoy['total_monto'], 2) . "\n";
    echo "   - Ventas mes: $" . number_format($ventasMes['total_monto'], 2) . "\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
