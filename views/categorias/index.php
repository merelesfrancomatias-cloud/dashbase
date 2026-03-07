<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Solo admin puede acceder
if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - DASH CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../../public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="../../public/css/components.css?v=<?= filemtime(__DIR__ . '/../../public/css/components.css') ?>">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/header.php'; ?>

        <!-- Alert Container -->
        <div id="alertContainer" class="alert hidden"></div>

        <div class="container">
            <div class="content-card">
                <div class="section-header">
                    <button class="btn btn-primary" id="btnNuevaCategoria">
                        <i class="fas fa-plus"></i>
                        Nueva Categoría
                    </button>
                </div>

                <!-- Contenedor de categorías -->
                <div id="categoriasContainer">
                    <div class="text-center" style="padding: 60px 20px;">
                        <div class="spinner-border" style="width: 40px; height: 40px; border-width: 4px; border-color: var(--primary); border-top-color: transparent;"></div>
                        <p style="margin-top: 20px; color: var(--text-secondary);">Cargando categorías...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Nueva/Editar Categoría -->
    <div id="modalCategoria" class="modal-overlay hidden">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Nueva Categoría</h3>
                <button class="modal-close" id="btnCerrarModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCategoria">
                    <input type="hidden" id="categoriaId">
                    
                    <div class="form-group">
                        <label for="nombre" class="form-label required">Nombre</label>
                        <input type="text" id="nombre" class="form-control" placeholder="Ej: Bebidas, Comida, etc." required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" class="form-control" rows="3" placeholder="Descripción opcional"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Color</label>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <label class="color-option">
                                <input type="radio" name="color" value="#FF5252" checked>
                                <span class="color-circle" style="background: #FF5252;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#2196F3">
                                <span class="color-circle" style="background: #2196F3;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#4CAF50">
                                <span class="color-circle" style="background: #4CAF50;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#FFC107">
                                <span class="color-circle" style="background: #FFC107;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#9C27B0">
                                <span class="color-circle" style="background: #9C27B0;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#FF9800">
                                <span class="color-circle" style="background: #FF9800;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#00BCD4">
                                <span class="color-circle" style="background: #00BCD4;"></span>
                            </label>
                            <label class="color-option">
                                <input type="radio" name="color" value="#E91E63">
                                <span class="color-circle" style="background: #E91E63;"></span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="btnCancelarModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarCategoria">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
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

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            categoriasModule = new CategoriasModule();
        });

        // Botón cancelar modal
        document.getElementById('btnCancelarModal')?.addEventListener('click', () => {
            categoriasModule.hideModal();
        });
    </script>
    <script src="../../public/js/categorias.js?v=<?= filemtime(__DIR__ . '/../../public/js/categorias.js') ?>"></script>
</body>
</html>
