<?php
session_start();

$base = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/');

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos - DASH CRM</title>
    <link rel="stylesheet" href="<?= $base ?>/public/css/dashboard.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= $base ?>/public/css/components.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/css/components.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modo oscuro para page-header */
        body.dark-mode .page-header {
            background: var(--surface) !important;
            border-color: var(--border) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
        }

        body.dark-mode .page-header h2 {
            color: var(--text-primary) !important;
        }

        body.dark-mode .page-header p {
            color: var(--text-secondary) !important;
        }

        body.dark-mode .page-header div[style*="background: #f3f4f6"] {
            background: var(--border) !important;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header" style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e5e7eb;">
                <div>
                    <h2 style="margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 12px; font-size: 24px; font-weight: 600;">
                        <div style="background: #f3f4f6; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #667eea;">
                            <i class="fas fa-money-bill-wave" style="font-size: 20px;"></i>
                        </div>
                        Gestión de Gastos
                    </h2>
                    <p style="margin: 10px 0 0 0; color: var(--text-secondary); font-size: 14px;">
                        Registra y controla todos los gastos del negocio
                    </p>
                </div>
                <div>
                    <button onclick="gastosModule.openModal()" class="btn btn-primary" style="box-shadow: 0 2px 6px rgba(102, 126, 234, 0.2);">
                        <i class="fas fa-plus"></i> Registrar Gasto
                    </button>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFE5E5; color: #FF4444;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Total de Gastos</p>
                        <h3 class="stat-value" id="totalGastos">0</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFE5E5; color: #FF4444;">
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
                        <p class="stat-label">Promedio por Gasto</p>
                        <h3 class="stat-value" id="promedioGasto">$0.00</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #E7E9FC; color: #6366F1;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Gastos del Mes</p>
                        <h3 class="stat-value" id="gastosMes">$0.00</h3>
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
                            <input type="date" class="form-control" id="fechaInicio">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-credit-card"></i> Método de Pago</label>
                            <select class="form-control" id="metodoPago">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta_debito">Tarjeta Débito</option>
                                <option value="tarjeta_credito">Tarjeta Crédito</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Categoría</label>
                            <select class="form-control" id="categoriaFiltro">
                                <option value="">Todas las categorías</option>
                                <option value="compra_mercaderia">Compra Mercadería</option>
                                <option value="servicios">Servicios</option>
                                <option value="salarios">Salarios</option>
                                <option value="alquiler">Alquiler</option>
                                <option value="impuestos">Impuestos</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button onclick="gastosModule.aplicarFiltros()" class="btn btn-primary" style="width: 100%; height: 44px;">
                                <i class="fas fa-search"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de gastos -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-list"></i> Lista de Gastos</h3>
                    <span id="conteoGastos" style="font-size: 14px; color: #666; font-weight: normal;">0 gastos registrados</span>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center; padding: 15px 10px;"># Gasto</th>
                                <th style="width: 130px; padding: 15px 10px;">Fecha</th>
                                <th style="padding: 15px 10px;">Descripción</th>
                                <th style="width: 150px; padding: 15px 10px;">Categoría</th>
                                <th style="width: 130px; text-align: right; padding: 15px 10px;">Monto</th>
                                <th style="width: 120px; text-align: center; padding: 15px 10px;">Método Pago</th>
                                <th style="width: 120px; text-align: center; padding: 15px 10px;">Comprobante</th>
                                <th style="width: 130px; text-align: center; padding: 15px 10px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="gastosTableBody">
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 50px; color: #999;">
                                    <i class="fas fa-money-bill-wave" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3; display: block;"></i>
                                    <p style="margin: 0; font-size: 14px;">No hay gastos registrados</p>
                                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #bbb;">Comienza registrando tu primer gasto</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Crear/Editar Gasto -->
    <div id="gastoModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modalTitle">
                    <i class="fas fa-money-bill-wave"></i> Registrar Gasto
                </h2>
                <button class="modal-close" onclick="gastosModule.closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="gastoForm">
                    <input type="hidden" id="gastoId">

                    <div class="form-group">
                        <label><i class="fas fa-file-alt"></i> Descripción / Concepto *</label>
                        <input type="text" class="form-control" id="descripcion" required
                               placeholder="Ej: Alquiler del local, compra de materiales...">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Monto *</label>
                            <input type="number" class="form-control" id="monto" required
                                   step="0.01" min="0" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Fecha *</label>
                            <input type="date" class="form-control" id="fecha" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Categoría</label>
                            <select class="form-control" id="categoriaId">
                                <option value="otros">Otros</option>
                                <option value="compra_mercaderia">Compra de Mercadería</option>
                                <option value="servicios">Servicios</option>
                                <option value="salarios">Salarios</option>
                                <option value="alquiler">Alquiler</option>
                                <option value="impuestos">Impuestos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-credit-card"></i> Método de Pago *</label>
                            <select class="form-control" id="metodoPagoForm" required>
                                <option value="">Seleccionar...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-receipt"></i> Comprobante</label>
                        <input type="text" class="form-control" id="comprobante" 
                               placeholder="Número de factura o comprobante (opcional)">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="gastosModule.closeModal()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="gastosModule.saveGasto()">
                    <i class="fas fa-save"></i> Guardar Gasto
                </button>
            </div>
        </div>
    </div>

    <script>window.APP_BASE = '<?= $base ?>';</script>
    <script src="<?= $base ?>/public/js/gastos.js?v=<?= time() ?>"></script>
    <script>
        const gastosModule = new GastosModule();
    </script>
</body>
</html>
