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
    <title>Punto de Venta - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <link rel="stylesheet" href="../../public/css/ventas.css?v=<?= filemtime(__DIR__ . '/../../public/css/ventas.css') ?>">
    <style>
        /* Estilos para el escáner de código de barras */
        #barcodeScanner {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        #interactive {
            width: 100%;
            height: 480px;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: #000;
        }

        #interactive video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #interactive canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 400px;
            height: 200px;
            border: 3px solid #00C9A7;
            border-radius: 12px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
            pointer-events: none;
            z-index: 10;
        }

        .scanner-overlay::before,
        .scanner-overlay::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border-color: #00C9A7;
            border-style: solid;
        }

        .scanner-overlay::before {
            top: -3px;
            left: -3px;
            border-width: 3px 0 0 3px;
            border-top-left-radius: 12px;
        }

        .scanner-overlay::after {
            top: -3px;
            right: -3px;
            border-width: 3px 3px 0 0;
            border-top-right-radius: 12px;
        }

        .scanner-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #00C9A7, transparent);
            animation: scan 2s infinite;
            z-index: 11;
        }

        @keyframes scan {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(200px); }
        }

        .scanner-status {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            z-index: 12;
        }

        .btn-camera {
            background: var(--success);
            color: white;
            border: none;
            padding: 0;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 201, 167, 0.3);
        }

        .btn-camera:hover {
            background: #00b894;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 201, 167, 0.4);
        }

        .btn-camera i {
            font-size: 18px;
        }

        /* Botones de método de pago en carrito */
        .pm-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 8px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            background: var(--bg-secondary);
            transition: all .18s;
            user-select: none;
            text-align: center;
        }
        .pm-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--bg-card);
        }
        .pm-btn--active,
        input[name="metodo_pago"]:checked + .pm-btn {
            border-color: var(--primary) !important;
            background: var(--primary) !important;
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(0, 201, 167, .35);
        }
        .pm-btn i {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php 
    include '../includes/sidebar.php';
    ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <div class="container" id="mainContainer">
            <div class="ventas-layout">
                <!-- Panel de productos -->
                <div class="content-card productos-panel">
                    <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
                        <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-search"></i> Buscar Productos
                        </h3>
                        <div id="cajaInfo"></div>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <div style="position: relative; display: flex; gap: 10px;">
                            <div style="position: relative; flex: 1;">
                                <i class="fas fa-barcode" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                <input 
                                    type="text" 
                                    id="searchProducto" 
                                    class="form-control" 
                                    placeholder="Buscar por nombre o escanear código de barras..."
                                    style="padding-left: 45px; font-size: 15px;"
                                    autofocus
                                >
                            </div>
                            <button type="button" class="btn-camera" id="btnScanner" title="Escanear código de barras con cámara">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <small style="color: var(--text-secondary); margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Presiona Enter para agregar el producto al carrito o usa la cámara para escanear
                        </small>
                    </div>

                    <!-- Accesos rápidos: más vendidos / más stock -->
                    <div id="topProductosSection" style="margin-bottom: 18px; display:none;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <span style="font-size:13px; font-weight:700; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.5px;">
                                <i class="fas fa-fire" style="color:#f59e0b; margin-right:5px;"></i> Más vendidos
                            </span>
                            <button id="btnVerTodosProductos" style="background:none; border:none; color:var(--primary); font-size:12px; cursor:pointer; font-weight:600; padding:2px 6px;">
                                Ver todos <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div id="topProductosGrid" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
                    </div>

                    <div class="productos-grid" id="productosGrid">
                        <!-- Los productos se cargan dinámicamente -->
                    </div>
                </div>

                <!-- Panel del carrito -->
                <div class="carrito-container" id="carritoContainer">
                    <div class="content-card carrito-card">
                        <h3 style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="fas fa-shopping-cart"></i> Carrito</span>
                            <div style="display: flex; gap: 8px;">
                                <button class="btn btn-secondary btn-sm" id="btnLimpiarCarrito" style="font-size: 13px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-secondary btn-sm" id="btnCerrarCarrito" onclick="toggleCart()" style="font-size: 13px; display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </h3>

                        <div class="carrito-items" id="carritoItems">
                            <!-- Los items del carrito se cargan dinámicamente -->
                        </div>

                        <!-- Método de pago — siempre visible -->
                        <div style="margin-bottom: 14px;">
                            <p style="font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px;">
                                <i class="fas fa-wallet"></i> Método de pago
                            </p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 7px;" id="paymentMethodsContainer">

                                <input type="radio" name="metodo_pago" value="efectivo" id="mp_efectivo" class="payment-method" checked hidden>
                                <label for="mp_efectivo" class="pm-btn pm-btn--active" data-value="efectivo">
                                    <i class="fas fa-money-bill-wave"></i> Efectivo
                                </label>

                                <input type="radio" name="metodo_pago" value="tarjeta_debito" id="mp_debito" class="payment-method" hidden>
                                <label for="mp_debito" class="pm-btn" data-value="tarjeta_debito">
                                    <i class="fas fa-credit-card"></i> Débito
                                </label>

                                <input type="radio" name="metodo_pago" value="tarjeta_credito" id="mp_credito" class="payment-method" hidden>
                                <label for="mp_credito" class="pm-btn" data-value="tarjeta_credito">
                                    <i class="fas fa-credit-card"></i> Crédito
                                </label>

                                <input type="radio" name="metodo_pago" value="transferencia" id="mp_transferencia" class="payment-method" hidden>
                                <label for="mp_transferencia" class="pm-btn" data-value="transferencia">
                                    <i class="fas fa-exchange-alt"></i> Transfer.
                                </label>

                                <input type="radio" name="metodo_pago" value="mercado_pago" id="mp_mercadopago" class="payment-method" hidden>
                                <label for="mp_mercadopago" class="pm-btn" data-value="mercado_pago" style="grid-column: span 2;">
                                    <i class="fas fa-qrcode"></i> Mercado Pago / QR
                                </label>

                            </div>
                        </div>

                        <!-- Descuento -->
                        <div style="margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                            <label for="descuento" style="font-weight: 600; font-size: 13px; white-space:nowrap; color:var(--text-secondary);">
                                <i class="fas fa-percentage"></i> Descuento ($)
                            </label>
                            <input 
                                type="number" 
                                id="descuento" 
                                class="form-control" 
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                style="padding: 8px 12px; flex:1;"
                            >
                        </div>

                        <!-- Totales -->
                        <div class="totales-section">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <strong id="subtotalAmount">$0.00</strong>
                            </div>
                            <div class="total-row">
                                <span>Descuento:</span>
                                <strong id="descuentoAmount" style="color: var(--error);">$0.00</strong>
                            </div>
                            <div class="total-row total-final">
                                <span style="font-size: 18px;">Total:</span>
                                <strong id="totalAmount" style="font-size: 24px; color: var(--primary);">$0.00</strong>
                            </div>
                        </div>

                        <!-- Botón de acción -->
                        <button class="btn btn-primary btn-procesar" id="btnProcesarVenta" disabled>
                            <i class="fas fa-check-circle"></i>
                            Finalizar Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Botón flotante del carrito (solo móvil) -->
    <button class="cart-toggle-btn" id="cartToggleBtn" onclick="toggleCart()">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-badge" id="cartBadge">0</span>
    </button>

    <!-- Modal del Escáner de Código de Barras -->
    <div id="scannerModal" class="modal-overlay hidden">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-camera"></i> Escanear Código de Barras
                </h3>
                <button class="modal-close" id="btnCloseScannerModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div id="barcodeScanner">
                    <div id="interactive" class="viewport">
                        <div class="scanner-overlay">
                            <div class="scanner-line"></div>
                        </div>
                        <div class="scanner-status">
                            <i class="fas fa-qrcode"></i> Apunta la cámara al código de barras
                        </div>
                    </div>
                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">
                        <i class="fas fa-info-circle"></i> El escaneo se realizará automáticamente cuando se detecte un código
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelScanner">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toggle carrito en móvil (ahora como modal)
        function toggleCart() {
            const carritoContainer = document.getElementById('carritoContainer');
            carritoContainer.classList.toggle('active');
            
            // Prevenir scroll del body cuando el modal está abierto
            if (carritoContainer.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Cerrar carrito al hacer clic en el fondo (overlay)
        document.addEventListener('click', (e) => {
            const carritoContainer = document.getElementById('carritoContainer');
            const carritoCard = document.querySelector('.carrito-card');
            const cartToggleBtn = document.getElementById('cartToggleBtn');
            
            if (window.innerWidth <= 768 && 
                carritoContainer.classList.contains('active') &&
                e.target === carritoContainer) {
                toggleCart();
            }
        });
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', (e) => {
            const carritoContainer = document.getElementById('carritoContainer');
            if (e.key === 'Escape' && carritoContainer.classList.contains('active')) {
                toggleCart();
            }
        });
        
        // Mostrar/ocultar botón cerrar según el tamaño de pantalla
        function updateCartUI() {
            const btnCerrarCarrito = document.getElementById('btnCerrarCarrito');
            if (window.innerWidth <= 768) {
                btnCerrarCarrito.style.display = 'inline-flex';
            } else {
                btnCerrarCarrito.style.display = 'none';
            }
        }
        
        // Actualizar UI al cambiar tamaño de ventana
        window.addEventListener('resize', updateCartUI);
        updateCartUI();

        // Funciones auxiliares
        function formatCurrency(value) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }

        // Función para mostrar alertas (solo una a la vez)
        let alertTimeout;
        let alertElement;
        
        function showAlert(message, type = 'info') {
            // Si ya existe un alert, removerlo
            if (alertElement) {
                alertElement.remove();
            }
            
            // Limpiar timeout anterior si existe
            if (alertTimeout) {
                clearTimeout(alertTimeout);
            }
            
            const alertClass = type === 'success' ? 'alert-success' : 
                             type === 'error' ? 'alert-error' : 
                             type === 'warning' ? 'alert-warning' : 'alert-info';
            
            alertElement = document.createElement('div');
            alertElement.className = `alert ${alertClass}`;
            alertElement.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                   type === 'error' ? 'exclamation-circle' : 
                                   type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(alertElement);
            
            // Ocultar después de 3 segundos
            alertTimeout = setTimeout(() => {
                alertElement.classList.add('hidden');
                setTimeout(() => {
                    alertElement.remove();
                    alertElement = null;
                }, 300);
            }, 3000);
        }

        // Inicializar módulo
        document.addEventListener('DOMContentLoaded', () => {
            puntoVentaModule = new PuntoVentaModule();
        });
    </script>
    
    <script src="../../public/js/ventas.js?v=<?= filemtime(__DIR__ . '/../../public/js/ventas.js') ?>"></script>
    <script>
    window.APP_BASE = '<?php echo rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/'); ?>';
    </script>
</body>
</html>
