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
    <title>Productos - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <style>
        /* Modo oscuro para las secciones del modal */
        body.dark-mode .modal-body > div[style*="linear-gradient"] {
            background: var(--surface) !important;
            border-left-color: inherit !important;
        }

        body.dark-mode #margenGanancia {
            background: rgba(16, 185, 129, 0.15) !important;
        }

        /* Animación para el preview de imagen */
        #fotoPreview img {
            animation: fadeIn 0.3s ease;
            border-radius: 8px;
            max-width: 100%;
            height: auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Mejorar el input file */
        input[type="file"]::-webkit-file-upload-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 12px;
            transition: all 0.2s;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* ── Vista de tarjetas / iconos ── */
        .productos-grid-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
        }

        .producto-card-item {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: transform .18s, box-shadow .18s, border-color .18s;
            position: relative;
        }
        .producto-card-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,.1);
            border-color: var(--primary);
        }
        .producto-card-item .card-img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            display: block;
            background: var(--bg-secondary);
        }
        .producto-card-item .card-body {
            padding: 7px 9px 9px;
        }
        .producto-card-item .card-nombre {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }
        .producto-card-item .card-precio {
            font-size: 13px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 5px;
        }
        .producto-card-item .card-stock-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            margin-bottom: 6px;
        }
        .producto-card-item .card-actions {
            display: flex;
            gap: 5px;
        }
        .producto-card-item .card-actions button {
            flex: 1;
            padding: 5px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 12px;
            transition: opacity .15s;
        }
        .producto-card-item .card-actions button:hover { opacity: .8; }
        .producto-card-item .btn-edit-card {
            background: var(--primary-light, #e0f7f4);
            color: var(--primary);
        }
        .producto-card-item .btn-del-card {
            background: rgba(239,68,68,.1);
            color: var(--danger, #ef4444);
        }
        .producto-card-item .stock-alert {
            position: absolute;
            top: 6px;
            right: 6px;
            background: #ef4444;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <?php 
    include '../includes/sidebar.php';
    ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <!-- Alert Container -->
        <div id="alertContainer" class="alert hidden"></div>

        <div class="container">
            <!-- Contenedor principal -->
            <div class="content-card">
                <div class="section-header">
                    <button class="btn btn-primary" id="btnNuevoProducto">
                        <i class="fas fa-plus"></i>
                        Nuevo Producto
                    </button>
                </div>

                <!-- Estadísticas -->
                <div class="flex gap-20 mb-20" style="flex-wrap: wrap;">
                    <div class="stat-card" style="flex: 1; min-width: 200px; padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Total Productos</div>
                                <div style="font-size: 28px; font-weight: 700; color: var(--text-primary);" id="totalProductos">0</div>
                            </div>
                            <div style="width: 50px; height: 50px; background: var(--primary-light); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box" style="font-size: 24px; color: var(--primary);"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card" style="flex: 1; min-width: 200px; padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Stock Valorizado</div>
                                <div style="font-size: 28px; font-weight: 700; color: var(--success);" id="stockValorizado">$0.00</div>
                            </div>
                            <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-dollar-sign" style="font-size: 24px; color: var(--success);"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card" style="flex: 1; min-width: 200px; padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase; font-weight: 600;">Stock Bajo</div>
                                <div style="font-size: 28px; font-weight: 700; color: var(--danger);" id="stockBajo">0</div>
                            </div>
                            <div style="width: 50px; height: 50px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: var(--danger);"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y búsqueda -->
                <div class="flex-between mb-20" style="gap: 15px; flex-wrap: wrap;">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar producto o código...">
                    </div>
                    <div class="flex gap-10" style="align-items:center;">
                        <select id="filtroCategoria" class="form-select" style="width: auto;">
                            <option value="">Todas las categorías</option>
                        </select>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; cursor: pointer;">
                            <input type="checkbox" id="filtroStockBajo">
                            <span style="font-size: 14px; font-weight: 600;">Stock Bajo</span>
                        </label>
                        <!-- Toggle vista -->
                        <div style="display:flex; border:1px solid var(--border); border-radius:10px; overflow:hidden;">
                            <button id="btnVistaGrid" onclick="productosModule.setVista('grid')" title="Vista iconos"
                                style="padding:9px 13px; border:none; background:var(--primary); color:#fff; cursor:pointer; transition:all .2s;">
                                <i class="fas fa-th"></i>
                            </button>
                            <button id="btnVistaLista" onclick="productosModule.setVista('lista')" title="Vista lista"
                                style="padding:9px 13px; border:none; background:var(--surface); color:var(--text-secondary); cursor:pointer; transition:all .2s;">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de productos -->
                <div id="productosContainer">
                    <div class="text-center" style="padding: 60px 20px;">
                        <div class="spinner-border" style="width: 40px; height: 40px; border-width: 4px; border-color: var(--primary); border-top-color: transparent;"></div>
                        <p style="margin-top: 20px; color: var(--text-secondary);">Cargando productos...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Nuevo/Editar Producto -->
    <div id="modalProducto" class="modal-overlay hidden">
        <div class="modal" style="max-width: 800px;" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">
                    <i class="fas fa-box"></i> Nuevo Producto
                </h3>
                <button class="modal-close" id="btnCerrarModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formProducto">
                <div class="modal-body" style="padding: 30px;">
                    <input type="hidden" id="productoId">
                    
                    <!-- Información Básica -->
                    <div style="background: linear-gradient(135deg, #667eea15, #764ba215); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #667eea;">
                        <h4 style="margin: 0 0 20px 0; font-size: 16px; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: #667eea; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-info-circle" style="color: white; font-size: 16px;"></i>
                            </div>
                            Información Básica
                        </h4>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="nombre" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-tag" style="color: #667eea;"></i>
                                Nombre del Producto *
                            </label>
                            <input type="text" id="nombre" class="form-input" placeholder="Ej: Coca Cola 2L" required 
                                   style="font-size: 15px; padding: 14px 16px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="descripcion" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-align-left" style="color: #667eea;"></i>
                                Descripción
                            </label>
                            <textarea id="descripcion" class="form-textarea" placeholder="Descripción detallada del producto (opcional)" 
                                      style="min-height: 90px; font-size: 14px; padding: 14px 16px; resize: vertical;"></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="categoria_id" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-folder" style="color: #667eea;"></i>
                                    Categoría
                                </label>
                                <select id="categoria_id" class="form-select" style="padding: 14px 16px;">
                                    <option value="">Sin categoría</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="codigo_barras" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-barcode" style="color: #667eea;"></i>
                                    Código de Barras
                                </label>
                                <input type="text" id="codigo_barras" class="form-input" placeholder="Código opcional" 
                                       style="padding: 14px 16px;">
                            </div>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div style="background: linear-gradient(135deg, #10b98115, #0d927315); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #10b981;">
                        <h4 style="margin: 0 0 20px 0; font-size: 16px; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-dollar-sign" style="color: white; font-size: 16px;"></i>
                            </div>
                            Precios
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="precio_costo" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-money-bill-wave" style="color: #10b981;"></i>
                                    Precio Costo
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #10b981; font-weight: 600;">$</span>
                                    <input type="number" id="precio_costo" class="form-input" step="0.01" min="0" value="0" placeholder="0.00" 
                                           style="padding: 14px 16px 14px 30px;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="precio_venta" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-hand-holding-usd" style="color: #10b981;"></i>
                                    Precio Venta *
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #10b981; font-weight: 600;">$</span>
                                    <input type="number" id="precio_venta" class="form-input" step="0.01" min="0" placeholder="0.00" required 
                                           style="padding: 14px 16px 14px 30px;">
                                </div>
                            </div>
                        </div>

                        <div id="margenGanancia" style="margin-top: 15px; padding: 12px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: none;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 13px; color: var(--text-secondary); font-weight: 500;">Margen de Ganancia:</span>
                                <span id="margenTexto" style="font-size: 16px; font-weight: 700; color: #10b981;">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Inventario -->
                    <div style="background: linear-gradient(135deg, #f59e0b15, #d9770815); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #f59e0b;">
                        <h4 style="margin: 0 0 20px 0; font-size: 16px; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: #f59e0b; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-warehouse" style="color: white; font-size: 16px;"></i>
                            </div>
                            Inventario
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="stock" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-boxes" style="color: #f59e0b;"></i>
                                    Stock Actual
                                </label>
                                <input type="number" id="stock" class="form-input" min="0" value="0" 
                                       style="padding: 14px 16px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="stock_minimo" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
                                    Stock Mínimo
                                </label>
                                <input type="number" id="stock_minimo" class="form-input" min="0" value="0" 
                                       style="padding: 14px 16px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="unidad_medida" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-balance-scale" style="color: #f59e0b;"></i>
                                    Unidad
                                </label>
                                <select id="unidad_medida" class="form-select" style="padding: 14px 16px;">
                                    <option value="unidad">Unidad</option>
                                    <option value="kg">Kilogramo (kg)</option>
                                    <option value="litro">Litro</option>
                                    <option value="metro">Metro</option>
                                    <option value="caja">Caja</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div style="background: linear-gradient(135deg, #8b5cf615, #6366f115); padding: 20px; border-radius: 12px; border-left: 4px solid #8b5cf6;">
                        <h4 style="margin: 0 0 20px 0; font-size: 16px; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: #8b5cf6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="color: white; font-size: 16px;"></i>
                            </div>
                            Imagen del Producto
                        </h4>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="foto" class="form-label" style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-camera" style="color: #8b5cf6;"></i>
                                Subir Foto
                            </label>
                            <input type="file" id="foto" class="form-input" accept="image/*" 
                                   style="padding: 12px 16px; cursor: pointer;">
                            <small style="color: var(--text-secondary); font-size: 12px; margin-top: 6px; display: block;">
                                <i class="fas fa-info-circle"></i> Formatos: JPG, PNG. Tamaño máximo: 2MB
                            </small>
                            <div id="fotoPreview" style="margin-top: 15px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 20px 30px; background: #f8f9fa; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-secondary" id="btnCancelarModal" style="padding: 12px 24px;">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarProducto" style="padding: 12px 30px;">
                        <i class="fas fa-save"></i>
                        Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función para mostrar alertas (solo una a la vez)
        let alertTimeout;
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            
            // Limpiar timeout anterior si existe
            if (alertTimeout) {
                clearTimeout(alertTimeout);
            }
            
            // Actualizar contenido y mostrar
            alertContainer.className = `alert alert-${type}`;
            alertContainer.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 
                                   type === 'warning' ? 'exclamation-triangle' : 
                                   'check-circle'}"></i>
                <span>${message}</span>
            `;
            alertContainer.classList.remove('hidden');
            
            // Ocultar después de 3 segundos
            alertTimeout = setTimeout(() => {
                alertContainer.classList.add('hidden');
            }, 3000);
        }

        // Verificar autenticación
        async function checkAuth() {
            try {
                const response = await fetch('../../api/auth/check.php', {
                    method: 'GET',
                    credentials: 'include'
                });

                const data = await response.json();

                if (!data.success) {
                    window.location.href = '../../index.php';
                    return false;
                }
                
                // Cargar info del usuario
                const user = JSON.parse(localStorage.getItem('user') || '{}');
                document.getElementById('userName').textContent = user.nombre || 'Usuario';
                document.getElementById('userRole').textContent = user.rol === 'admin' ? 'Administrador' : 'Empleado';
                const iniciales = (user.nombre || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                document.getElementById('userAvatar').textContent = iniciales;

                // Ocultar menús admin si no es admin
                if (user.rol !== 'admin') {
                    document.querySelectorAll('.admin-only').forEach(el => {
                        el.style.display = 'none';
                    });
                }

                return true;
            } catch (error) {
                window.location.href = '../../index.php';
                return false;
            }
        }

        // Logout — manejado por header.php

        // Botón cancelar modal
        document.getElementById('btnCancelarModal')?.addEventListener('click', () => {
            document.getElementById('modalProducto').classList.add('hidden');
        });

        // Calcular margen de ganancia
        function calcularMargen() {
            const costo = parseFloat(document.getElementById('precio_costo').value) || 0;
            const venta = parseFloat(document.getElementById('precio_venta').value) || 0;
            const margenDiv = document.getElementById('margenGanancia');
            const margenTexto = document.getElementById('margenTexto');

            if (costo > 0 && venta > 0) {
                const margen = ((venta - costo) / costo * 100).toFixed(2);
                const ganancia = (venta - costo).toFixed(2);
                
                margenTexto.textContent = `${margen}% ($${ganancia})`;
                margenDiv.style.display = 'block';

                // Cambiar color según el margen
                if (margen < 10) {
                    margenTexto.style.color = '#ef4444'; // Rojo
                } else if (margen < 30) {
                    margenTexto.style.color = '#f59e0b'; // Amarillo
                } else {
                    margenTexto.style.color = '#10b981'; // Verde
                }
            } else {
                margenDiv.style.display = 'none';
            }
        }

        // Preview de imagen
        document.getElementById('foto')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('fotoPreview');
            
            if (file) {
                // Validar tamaño (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('La imagen no debe superar 2MB', 'error');
                    e.target.value = '';
                    preview.innerHTML = '';
                    return;
                }

                // Validar tipo
                if (!file.type.startsWith('image/')) {
                    showAlert('Solo se permiten archivos de imagen', 'error');
                    e.target.value = '';
                    preview.innerHTML = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.innerHTML = `
                        <div style="position: relative; display: inline-block; margin-top: 10px;">
                            <img src="${event.target.result}" alt="Preview" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 8px; 
                                        border: 2px solid var(--border); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <button type="button" onclick="document.getElementById('foto').value=''; document.getElementById('fotoPreview').innerHTML='';" 
                                    style="position: absolute; top: -8px; right: -8px; width: 28px; height: 28px; 
                                           background: #ef4444; color: white; border: none; border-radius: 50%; 
                                           cursor: pointer; display: flex; align-items: center; justify-content: center; 
                                           box-shadow: 0 2px 6px rgba(0,0,0,0.2); transition: all 0.2s;"
                                    onmouseover="this.style.transform='scale(1.1)'" 
                                    onmouseout="this.style.transform='scale(1)'">
                                <i class="fas fa-times" style="font-size: 12px;"></i>
                            </button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Listeners para calcular margen
        document.getElementById('precio_costo')?.addEventListener('input', calcularMargen);
        document.getElementById('precio_venta')?.addEventListener('input', calcularMargen);
    </script>

    <!-- 1. APP_BASE primero, antes de cargar productos.js -->
    <script>
        window.APP_BASE = '<?php echo rtrim(str_replace(str_replace(chr(92), chr(47), $_SERVER['DOCUMENT_ROOT']), '', str_replace(chr(92), chr(47), dirname(dirname(dirname(realpath(__FILE__)))))), '/'); ?>';
    </script>

    <!-- 2. Módulo de productos -->
    <script src="../../public/js/productos.js?v=<?php echo time(); ?>"></script>

    <!-- 3. Inicializar después de que el módulo esté cargado -->
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            if (await checkAuth()) {
                productosModule = new ProductosModule();
            }
        });
    </script>
</body>
</html>
