<?php
session_start();

$base = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(dirname(realpath(__FILE__))))), '/');

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php');
    exit;
}

// Verificar que sea admin
if ($_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base . '/views/dashboard/index.php');
    exit;
}

// Detectar rubro del negocio (mismo patrón que sidebar)
$_rubroSlug = '';
$_esRestaurant = false;
$_esferreteria = false;
if (isset($_SESSION['negocio_id'])) {
    try {
        require_once dirname(dirname(dirname(__FILE__))) . '/config/database.php';
        $_pdoEmp = (new Database())->getConnection();
        $stmtEmp = $_pdoEmp->prepare("SELECT r.slug FROM negocios n LEFT JOIN rubros r ON r.id = n.rubro_id WHERE n.id = ?");
        $stmtEmp->execute([(int)$_SESSION['negocio_id']]);
        $_rubroSlug = $stmtEmp->fetchColumn() ?: '';
        $_esRestaurant   = in_array($_rubroSlug, ['gastronomia','bar','restaurant','cafeteria','panaderia','comida_rapida']);
        $_esferreteria   = in_array($_rubroSlug, ['ferreteria','construccion','tecnologia','electrodomesticos','otro']);
        $_esSupermercado = in_array($_rubroSlug, ['supermercado','almacen']);
        $_esPeluqueria   = in_array($_rubroSlug, ['peluqueria']);
        $_esGimnasio     = in_array($_rubroSlug, ['gimnasio']);
        $_esCanchas      = in_array($_rubroSlug, ['canchas']);
    } catch (Exception $_e) {}
}
$_esSupermercado = $_esSupermercado ?? false;
$_esPeluqueria   = $_esPeluqueria   ?? false;
$_esGimnasio     = $_esGimnasio     ?? false;
$_esCanchas      = $_esCanchas      ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - DASH CRM</title>
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos adicionales para el modal de empleados */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.2s ease;
            padding: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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
<body data-rubro="<?= htmlspecialchars($_rubroSlug) ?>">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header" style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e5e7eb;">
                <div>
                    <h2 style="margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 12px; font-size: 24px; font-weight: 600;">
                        <div style="background: #f3f4f6; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f5576c;">
                            <i class="fas fa-users" style="font-size: 20px;"></i>
                        </div>
                        Gestión de Empleados
                    </h2>
                    <p style="margin: 10px 0 0 0; color: var(--text-secondary); font-size: 14px;">
                        Administra los usuarios y empleados del negocio
                    </p>
                </div>
                <div>
                    <button onclick="empleadosModule.openModal()" class="btn btn-primary" style="box-shadow: 0 2px 6px rgba(102, 126, 234, 0.2);">
                        <i class="fas fa-user-plus"></i> Nuevo Empleado
                    </button>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e8f4fd; color: var(--primary);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Total Empleados</p>
                        <h3 class="stat-value" id="totalEmpleados">0</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #D1F2EB; color: #00C9A7;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Activos</p>
                        <h3 class="stat-value" id="empleadosActivos">0</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #E7E9FC; color: #6366F1;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Administradores</p>
                        <h3 class="stat-value" id="administradores">0</h3>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFF3CD; color: #FFC107;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-label">Vendedores</p>
                        <h3 class="stat-value" id="vendedores">0</h3>
                    </div>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h3><i class="fas fa-search"></i> Búsqueda y Filtros</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="Nombre, email o teléfono...">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-user-tag"></i> Rol</label>
                            <select class="form-control" id="filtroRol">
                                <option value="">Todos los roles</option>
                                <option value="admin">Administrador</option>
                                <option value="vendedor">Vendedor</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-toggle-on"></i> Estado</label>
                            <select class="form-control" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button onclick="empleadosModule.aplicarFiltros()" class="btn btn-primary" style="height: 44px;">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de empleados -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-list"></i> Lista de Empleados</h3>
                    <span id="conteoEmpleados" style="font-size: 14px; color: #666; font-weight: normal;">0 empleados</span>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center; padding: 15px 10px;">ID</th>
                                <th style="padding: 15px 10px;">Nombre</th>
                                <th style="padding: 15px 10px;">Email</th>
                                <th style="width: 130px; padding: 15px 10px;">Teléfono</th>
                                <th style="width: 130px; text-align: center; padding: 15px 10px;">Rol</th>
                                <th style="width: 120px; text-align: center; padding: 15px 10px;">Estado</th>
                                <th style="width: 140px; padding: 15px 10px;">Fecha Registro</th>
                                <th style="width: 150px; text-align: center; padding: 15px 10px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="empleadosTableBody">
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 50px; color: #999;">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3; display: block;"></i>
                                    <p style="margin: 0; font-size: 14px;">No hay empleados registrados</p>
                                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #bbb;">Agrega tu primer empleado</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Crear/Editar Empleado -->
    <div id="empleadoModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h2 id="modalTitle">
                    <i class="fas fa-user-plus"></i> Nuevo Empleado
                </h2>
                <span class="close" onclick="empleadosModule.closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="empleadoForm">
                    <input type="hidden" id="empleadoId">
                    
                    <!-- Información Personal -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 15px 0; font-size: 16px; color: var(--text-primary);">
                            <i class="fas fa-user"></i> Información Personal
                        </h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> Nombre *</label>
                            <input type="text" class="form-control" id="nombre" required 
                                   placeholder="Ej: Juan">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> Apellido *</label>
                            <input type="text" class="form-control" id="apellido" required 
                                   placeholder="Ej: Pérez">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="email" 
                                       placeholder="ejemplo@correo.com">
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" 
                                       placeholder="1234567890">
                            </div>
                        </div>
                    </div>

                    <!-- Acceso al Sistema -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 15px 0; font-size: 16px; color: var(--text-primary);">
                            <i class="fas fa-key"></i> Acceso al Sistema
                        </h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user-circle"></i> Usuario *</label>
                            <input type="text" class="form-control" id="usuario" required 
                                   placeholder="nombre.usuario">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Contraseña <span id="passwordLabel">*</span></label>
                            <input type="password" class="form-control" id="password" 
                                   placeholder="Mínimo 6 caracteres">
                            <small style="color: #666; font-size: 12px;" id="passwordHelp">
                                * Campo obligatorio para nuevos empleados. Dejar vacío para no cambiar.
                            </small>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-user-tag"></i> Rol *</label>
                            <select class="form-control" id="rol" required onchange="empleadosModule.togglePermisos()">
                                <option value="">Seleccionar...</option>
                                <option value="admin">Administrador (Acceso Completo)</option>
                                <option value="empleado">Empleado (Permisos Personalizados)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-toggle-on"></i> Estado</label>
                            <select class="form-control" id="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Permisos (solo para empleados) -->
                    <div id="permisosSection" style="background: #f8f9fa; padding: 15px; border-radius: 8px; display: none;">
                        <h3 style="margin: 0 0 15px 0; font-size: 16px; color: var(--text-primary);">
                            <i class="fas fa-shield-alt"></i> Permisos del Empleado
                        </h3>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 15px;">
                            Selecciona los permisos específicos para este empleado
                        </p>

                        <!-- Productos -->
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #667eea;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #667eea;">
                                <i class="fas fa-box"></i> Productos
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_productos" checked>
                                    <span style="font-size: 13px;">Ver productos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_crear_productos">
                                    <span style="font-size: 13px;">Crear productos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_editar_productos">
                                    <span style="font-size: 13px;">Editar productos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_eliminar_productos">
                                    <span style="font-size: 13px;">Eliminar productos</span>
                                </label>
                            </div>
                        </div>

                        <!-- Ventas -->
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #00C9A7;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #00C9A7;">
                                <i class="fas fa-shopping-cart"></i> Ventas
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_ventas" checked>
                                    <span style="font-size: 13px;">Ver ventas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_crear_ventas" checked>
                                    <span style="font-size: 13px;">Crear ventas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_cancelar_ventas">
                                    <span style="font-size: 13px;">Cancelar ventas</span>
                                </label>
                            </div>
                        </div>

                        <!-- Gastos -->
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #FF4444;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #FF4444;">
                                <i class="fas fa-money-bill-wave"></i> Gastos
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_gastos">
                                    <span style="font-size: 13px;">Ver gastos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_crear_gastos">
                                    <span style="font-size: 13px;">Registrar gastos</span>
                                </label>
                            </div>
                        </div>

                        <!-- Caja -->
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #FFA500;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #FFA500;">
                                <i class="fas fa-cash-register"></i> Caja
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_caja" checked>
                                    <span style="font-size: 13px;">Abrir/Cerrar caja</span>
                                </label>
                            </div>
                        </div>

                        <!-- Empleados -->
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #f5576c;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #f5576c;">
                                <i class="fas fa-users"></i> Empleados
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_empleados">
                                    <span style="font-size: 13px;">Ver empleados</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_crear_empleados">
                                    <span style="font-size: 13px;">Crear/Editar empleados</span>
                                </label>
                            </div>
                        </div>

                        <!-- Reportes -->
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #4facfe;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #4facfe;">
                                <i class="fas fa-chart-line"></i> Reportes
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_reportes">
                                    <span style="font-size: 13px;">Ver reportes</span>
                                </label>
                            </div>
                        </div>

                        <!-- Módulo específico según rubro -->
                        <?php if ($_esRestaurant): ?>
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #FF7A30;">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #FF7A30;">
                                <i class="fas fa-utensils"></i> Restaurant
                            </h4>
                            <p style="font-size: 11px; color: #999; margin: 0 0 10px 0;">Salón de mesas, reservas y cocina KDS</p>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_mesas">
                                    <span style="font-size: 13px;">Ver salón / mesas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_mesas">
                                    <span style="font-size: 13px;">Gestionar mesas y comandas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_reservas">
                                    <span style="font-size: 13px;">Ver reservas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_reservas">
                                    <span style="font-size: 13px;">Crear / editar reservas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_cocina">
                                    <span style="font-size: 13px;">Ver pantalla cocina (KDS)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_cocina">
                                    <span style="font-size: 13px;">Marcar ítems en cocina</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_carta">
                                    <span style="font-size: 13px;">Editar carta / menú</span>
                                </label>
                            </div>
                        </div>

                        <?php elseif ($_esferreteria): ?>
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #f59e0b;">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #f59e0b;">
                                <i class="fas fa-tools"></i> Ferretería
                            </h4>
                            <p style="font-size: 11px; color: #999; margin: 0 0 10px 0;">Presupuestos, proveedores y órdenes de compra</p>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_presupuestos">
                                    <span style="font-size: 13px;">Ver presupuestos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_crear_presupuestos">
                                    <span style="font-size: 13px;">Crear / editar presupuestos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_aprobar_presupuestos">
                                    <span style="font-size: 13px;">Aprobar y convertir a venta</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_stock">
                                    <span style="font-size: 13px;">Ver alertas de stock bajo</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_proveedores">
                                    <span style="font-size: 13px;">Ver proveedores</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_proveedores">
                                    <span style="font-size: 13px;">Crear / editar proveedores</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_ordenes">
                                    <span style="font-size: 13px;">Ver órdenes de compra</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_ordenes">
                                    <span style="font-size: 13px;">Crear / gestionar órdenes</span>
                                </label>
                            </div>
                        </div>

                        <?php elseif ($_esSupermercado): ?>
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #0FD186;">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #0FD186;">
                                <i class="fas fa-store"></i> Supermercado
                            </h4>
                            <p style="font-size: 11px; color: #999; margin: 0 0 10px 0;">Stock, proveedores, órdenes de compra y etiquetas</p>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_stock">
                                    <span style="font-size: 13px;">Ver control de stock</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_stock">
                                    <span style="font-size: 13px;">Ajustar stock manualmente</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_proveedores">
                                    <span style="font-size: 13px;">Ver proveedores</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_proveedores">
                                    <span style="font-size: 13px;">Crear / editar proveedores</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_ordenes">
                                    <span style="font-size: 13px;">Ver órdenes de compra</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_ordenes">
                                    <span style="font-size: 13px;">Crear / recibir órdenes</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_imprimir_etiquetas">
                                    <span style="font-size: 13px;">Imprimir etiquetas / balanza</span>
                                </label>
                            </div>
                        </div>

                        <?php elseif ($_esPeluqueria): ?>
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #8b5cf6;">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #8b5cf6;">
                                <i class="fas fa-scissors"></i> Peluquería
                            </h4>
                            <p style="font-size: 11px; color: #999; margin: 0 0 10px 0;">Agenda de turnos, servicios y clientes</p>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_agenda">
                                    <span style="font-size: 13px;">Ver agenda de turnos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_agenda">
                                    <span style="font-size: 13px;">Crear / cancelar turnos</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_servicios">
                                    <span style="font-size: 13px;">Ver servicios</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_servicios">
                                    <span style="font-size: 13px;">Crear / editar servicios</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_clientes_pelu">
                                    <span style="font-size: 13px;">Ver clientes</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_clientes_pelu">
                                    <span style="font-size: 13px;">Crear / editar clientes</span>
                                </label>
                            </div>
                        </div>

                        <?php elseif ($_esGimnasio): ?>
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #f97316;">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #f97316;">
                                <i class="fas fa-dumbbell"></i> Gimnasio
                            </h4>
                            <p style="font-size: 11px; color: #999; margin: 0 0 10px 0;">Socios, clases, asistencias y pagos</p>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_socios">
                                    <span style="font-size: 13px;">Ver socios</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_socios">
                                    <span style="font-size: 13px;">Crear / editar socios</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_clases">
                                    <span style="font-size: 13px;">Ver horario de clases</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_clases">
                                    <span style="font-size: 13px;">Crear / editar clases</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_registrar_asistencias">
                                    <span style="font-size: 13px;">Registrar asistencias</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_pagos_gym">
                                    <span style="font-size: 13px;">Ver pagos / cuotas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_registrar_pagos_gym">
                                    <span style="font-size: 13px;">Registrar pagos de socios</span>
                                </label>
                            </div>
                        </div>

                        <?php elseif ($_esCanchas): ?>
                        <div style="margin-bottom: 15px; padding: 12px; background: white; border-radius: 6px; border-left: 3px solid #16a34a;">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #16a34a;">
                                <i class="fas fa-futbol"></i> Canchas
                            </h4>
                            <p style="font-size: 11px; color: #999; margin: 0 0 10px 0;">Reservas, canchas, clientes y caja del día</p>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_reservas_canchas">
                                    <span style="font-size: 13px;">Ver reservas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_reservas_canchas">
                                    <span style="font-size: 13px;">Crear / cancelar reservas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_canchas">
                                    <span style="font-size: 13px;">Ver mis canchas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_canchas">
                                    <span style="font-size: 13px;">Crear / editar canchas</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_ver_clientes_canchas">
                                    <span style="font-size: 13px;">Ver clientes</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_gestionar_clientes_canchas">
                                    <span style="font-size: 13px;">Crear / editar clientes</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" id="perm_caja_canchas">
                                    <span style="font-size: 13px;">Gestionar caja del día</span>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="empleadosModule.closeModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="empleadosModule.saveEmpleado()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <script src="../../public/js/empleados.js?v=<?= time() ?>"></script>
    <script>
        window.APP_BASE = '<?= $base ?>';
        const empleadosModule = new EmpleadosModule();
    </script>
</body>
</html>
