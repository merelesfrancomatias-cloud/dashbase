<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <div class="container">
            <div class="page-header">
                <div style="display: flex; gap: 10px;">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-cash-register"></i>
                        Ir al Punto de Venta
                    </a>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--primary-light); color: var(--primary);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Total de Ventas</p>
                        <h3 class="stat-value" id="totalVentas">0</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #D1F2EB; color: #00C9A7;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Monto Total</p>
                        <h3 class="stat-value" id="totalMonto">$0.00</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFF3CD; color: #FFC107;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Promedio por Venta</p>
                        <h3 class="stat-value" id="promedioVenta">$0.00</h3>
                    </div>
                </div>

                <div class="stat-card" style="grid-column: span 1;">
                    <div class="stat-icon" style="background: #E7E9FC; color: #6366F1;">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Por Método de Pago</p>
                        <div id="ventasPorMetodo" style="font-size: 12px; margin-top: 5px; max-height: 60px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-credit-card"></i> Método de Pago</label>
                            <select class="form-control" id="metodo_pago">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta_debito">Tarjeta Débito</option>
                                <option value="tarjeta_credito">Tarjeta Crédito</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button onclick="historialVentasModule.aplicarFiltros()" class="btn btn-primary" style="width: 100%; height: 44px;">
                                <i class="fas fa-search"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de ventas -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-list"></i> Lista de Ventas</h3>
                    <span id="conteoVentas" style="font-size: 14px; color: #666; font-weight: normal;">0 ventas encontradas</span>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="width: 100px; text-align: center; padding: 15px 10px;"># Venta</th>
                                <th style="width: 140px; padding: 15px 10px;">Fecha y Hora</th>
                                <th style="padding: 15px 10px;">Usuario</th>
                                <th style="width: 90px; text-align: center; padding: 15px 10px;">Productos</th>
                                <th style="width: 130px; text-align: right; padding: 15px 10px;">Subtotal</th>
                                <th style="width: 100px; text-align: right; padding: 15px 10px;">Descuento</th>
                                <th style="width: 130px; text-align: right; padding: 15px 10px;">Total</th>
                                <th style="width: 120px; text-align: center; padding: 15px 10px;">Método Pago</th>
                                <th style="width: 110px; text-align: center; padding: 15px 10px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ventasTableBody">
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 50px; color: #999;">
                                    <i class="fas fa-receipt" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3; display: block;"></i>
                                    <p style="margin: 0; font-size: 14px;">No hay ventas para mostrar</p>
                                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #bbb;">Intenta ajustar los filtros</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="../../public/js/historial-ventas.js?v=<?= filemtime(__DIR__ . '/../../public/js/historial-ventas.js') ?>"></script>
    <script>
        function formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }

        function showAlert(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : 
                             type === 'error' ? 'alert-error' : 
                             type === 'warning' ? 'alert-warning' : 'alert-info';
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                   type === 'error' ? 'exclamation-circle' : 
                                   type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.page-header'));
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            historialVentasModule = new HistorialVentasModule();
        });
    </script>
</body>
</html>
