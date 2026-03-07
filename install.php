<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Instalación - DASH CRM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #007AFF 0%, #5AC8FA 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        h1 {
            color: #007AFF;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #8E8E93;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .check-item {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            background: #F2F2F7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .check-item.success {
            background: rgba(52, 199, 89, 0.1);
            border: 1px solid rgba(52, 199, 89, 0.3);
        }
        
        .check-item.error {
            background: rgba(255, 59, 48, 0.1);
            border: 1px solid rgba(255, 59, 48, 0.3);
        }
        
        .check-label {
            font-weight: 600;
            color: #000;
        }
        
        .check-status {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }
        
        .check-status.success {
            background: #34C759;
            color: white;
        }
        
        .check-status.error {
            background: #FF3B30;
            color: white;
        }
        
        .credentials {
            background: #F2F2F7;
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
        }
        
        .credentials h3 {
            color: #007AFF;
            margin-bottom: 15px;
        }
        
        .credential-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .credential-label {
            font-weight: 600;
            width: 120px;
            color: #000;
        }
        
        .credential-value {
            color: #007AFF;
            font-family: 'Courier New', monospace;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #007AFF;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 30px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #0051D5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.4);
        }
        
        .info-box {
            background: rgba(0, 122, 255, 0.1);
            border: 1px solid rgba(0, 122, 255, 0.3);
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
        }
        
        .info-box h4 {
            color: #007AFF;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            margin-left: 20px;
            color: #000;
        }
        
        .info-box li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ DASH CRM - Instalación</h1>
        <p class="subtitle">Verificación del sistema</p>

        <?php
        $checks = [];
        $allSuccess = true;

        // Verificar PHP
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '7.4.0', '>=');
        $checks[] = [
            'label' => 'PHP Version (' . $phpVersion . ')',
            'status' => $phpOk
        ];
        if (!$phpOk) $allSuccess = false;

        // Verificar PDO
        $pdoOk = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        $checks[] = [
            'label' => 'PDO MySQL Extension',
            'status' => $pdoOk
        ];
        if (!$pdoOk) $allSuccess = false;

        // Verificar conexión a BD
        $dbOk = false;
        $dbMessage = '';
        try {
            require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();
            if ($conn) {
                $dbOk = true;
                $dbMessage = 'Conectado correctamente';
                
                // Verificar tablas
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $expectedTables = ['negocios', 'usuarios', 'productos', 'categorias', 'ventas', 'cajas', 'pedidos', 'gastos', 'permisos'];
                $tablesOk = count(array_intersect($expectedTables, $tables)) === count($expectedTables);
                
                $checks[] = [
                    'label' => 'Base de Datos (dash_crm)',
                    'status' => true
                ];
                
                $checks[] = [
                    'label' => 'Tablas de la BD (' . count($tables) . ' tablas)',
                    'status' => $tablesOk
                ];
                
                if (!$tablesOk) $allSuccess = false;
            }
        } catch (Exception $e) {
            $dbMessage = $e->getMessage();
            $checks[] = [
                'label' => 'Base de Datos',
                'status' => false
            ];
            $allSuccess = false;
        }

        // Verificar permisos de escritura
        $uploadsPath = 'public/uploads';
        $writableOk = is_writable($uploadsPath);
        $checks[] = [
            'label' => 'Permisos de escritura (uploads)',
            'status' => $writableOk
        ];
        if (!$writableOk) $allSuccess = false;

        // Verificar mod_rewrite
        $modRewriteOk = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : true;
        $checks[] = [
            'label' => 'Apache mod_rewrite',
            'status' => $modRewriteOk
        ];

        // Mostrar resultados
        foreach ($checks as $check) {
            $class = $check['status'] ? 'success' : 'error';
            $statusText = $check['status'] ? '✓ OK' : '✗ Error';
            echo '<div class="check-item ' . $class . '">';
            echo '<span class="check-label">' . $check['label'] . '</span>';
            echo '<span class="check-status ' . $class . '">' . $statusText . '</span>';
            echo '</div>';
        }
        ?>

        <?php if ($allSuccess): ?>
            <div class="credentials">
                <h3>🔐 Sistema Instalado Correctamente</h3>
                <p style="color: var(--text-secondary); margin-top: 10px;">
                    El sistema ha sido configurado exitosamente. Las credenciales de acceso han sido configuradas previamente.
                </p>
            </div>

            <a href="index.php" class="btn">🚀 Ir al Login</a>

            <div class="info-box">
                <h4>📝 Próximos Pasos:</h4>
                <ul>
                    <li>Inicia sesión con tus credenciales</li>
                    <li>Completa la información del negocio</li>
                    <li>Comienza a agregar productos y categorías</li>
                    <li>Configura a tus empleados si es necesario</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h4>⚠️ Acción Requerida:</h4>
                <ul>
                    <li>Verifica que XAMPP esté ejecutándose</li>
                    <li>Asegúrate de que MySQL esté activo</li>
                    <li>Importa el archivo database_schema.sql en phpMyAdmin</li>
                    <li>Verifica los permisos de la carpeta uploads</li>
                    <li>Recarga esta página después de corregir los errores</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
