<?php 
session_start();

// Verificar si hay sesión
if (!isset($_SESSION['user_id'])) {
    echo "<h1>❌ No hay sesión activa</h1>";
    echo "<p>Debes <a href='index.php'>iniciar sesión</a> primero</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard API</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .log { background: #000; color: #0f0; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; margin: 10px 0; white-space: pre-wrap; }
        .btn { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #45a049; }
        .stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 4px solid #4CAF50; }
        .stat-label { color: #666; font-size: 14px; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test Dashboard API</h1>
        
        <div style="background: #e3f2fd; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <strong>✅ Sesión activa:</strong><br>
            User ID: <?php echo $_SESSION['user_id']; ?><br>
            Nombre: <?php echo $_SESSION['nombre']; ?><br>
            Rol: <?php echo $_SESSION['rol']; ?>
        </div>

        <button class="btn" onclick="testAPI()">📊 Cargar Estadísticas del Dashboard</button>
        
        <div id="log" class="log">Esperando solicitud...</div>
        
        <div id="stats" class="stats" style="display: none;">
            <div class="stat-card">
                <div class="stat-label">💰 Ventas de Hoy</div>
                <div class="stat-value" id="ventasHoy">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">📦 Total Productos</div>
                <div class="stat-value" id="totalProductos">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">👥 Total Clientes</div>
                <div class="stat-value" id="totalClientes">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">📅 Ventas del Mes</div>
                <div class="stat-value" id="ventasMes">-</div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '/DASH4/api';
        
        function log(msg) {
            const logEl = document.getElementById('log');
            logEl.textContent += msg + '\n';
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }

        async function testAPI() {
            const logEl = document.getElementById('log');
            logEl.textContent = '';
            
            log('🔍 Iniciando test...');
            log('📡 URL: ' + API_URL + '/dashboard/stats.php');
            log('');
            
            try {
                log('⏳ Enviando solicitud...');
                const response = await fetch(API_URL + '/dashboard/stats.php', {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                log('📥 Respuesta recibida:');
                log('   Status: ' + response.status + ' ' + response.statusText);
                log('   Headers: ' + JSON.stringify([...response.headers.entries()]));
                log('');
                
                const responseText = await response.text();
                log('📄 Respuesta RAW:');
                log(responseText);
                log('');
                
                try {
                    const data = JSON.parse(responseText);
                    log('📊 Datos parseados:');
                    log(JSON.stringify(data, null, 2));
                    
                    if (data.success) {
                        log('');
                        log('✅ ¡API FUNCIONA CORRECTAMENTE!');
                        
                        // Mostrar stats
                        document.getElementById('stats').style.display = 'grid';
                        document.getElementById('ventasHoy').textContent = formatCurrency(data.data.ventas_hoy.monto);
                        document.getElementById('totalProductos').textContent = data.data.productos;
                        document.getElementById('totalClientes').textContent = data.data.clientes;
                        document.getElementById('ventasMes').textContent = formatCurrency(data.data.ventas_mes.monto);
                    } else {
                        log('');
                        log('❌ API retornó success=false');
                        log('Mensaje: ' + data.message);
                    }
                } catch (e) {
                    log('');
                    log('❌ Error al parsear JSON: ' + e.message);
                }
                
            } catch (error) {
                log('');
                log('❌ ERROR: ' + error.message);
                log('Stack: ' + error.stack);
            }
        }
    </script>
</body>
</html>
