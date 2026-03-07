// Módulo de Categorías
class CategoriasModule {
    constructor() {
        this.categorias = [];
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadCategorias();
    }

    setupEventListeners() {
        // Botón nueva categoría
        const btnNueva = document.getElementById('btnNuevaCategoria');
        if (btnNueva) {
            btnNueva.addEventListener('click', () => {
                this.showModal();
            });
        }

        // Cerrar modal
        document.getElementById('btnCerrarModal')?.addEventListener('click', () => {
            this.hideModal();
        });

        // Guardar categoría
        document.getElementById('formCategoria')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveCategoria();
        });

        // Click fuera del modal
        document.getElementById('modalCategoria')?.addEventListener('click', (e) => {
            if (e.target.id === 'modalCategoria') {
                this.hideModal();
            }
        });
    }

    async loadCategorias() {
        try {
            const response = await fetch('../../api/categorias/index.php', {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success) {
                this.categorias = data.data;
                this.renderCategorias();
            } else {
                showAlert('Error al cargar categorías: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión al cargar categorías', 'error');
        }
    }

    renderCategorias() {
        const container = document.getElementById('categoriasContainer');
        
        if (this.categorias.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="empty-state-title">No hay categorías</h3>
                    <p class="empty-state-text">Comienza creando tu primera categoría para organizar tus productos</p>
                    <button class="btn btn-primary" onclick="categoriasModule.showModal()">
                        <i class="fas fa-plus"></i>
                        Nueva Categoría
                    </button>
                </div>
            `;
            return;
        }

        let html = `
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Color</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        this.categorias.forEach(categoria => {
            html += `
                <tr>
                    <td>
                        <div style="width: 30px; height: 30px; background: ${categoria.color}; border-radius: 8px;"></div>
                    </td>
                    <td><strong>${categoria.nombre}</strong></td>
                    <td>${categoria.descripcion || '-'}</td>
                    <td>
                        <span class="badge badge-primary">${categoria.total_productos} productos</span>
                    </td>
                    <td>
                        <div class="flex gap-10">
                            <button class="btn btn-secondary btn-sm btn-icon" onclick="categoriasModule.editCategoria(${categoria.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon" onclick="categoriasModule.deleteCategoria(${categoria.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = html;
    }

    showModal(categoriaId = null) {
        const modal = document.getElementById('modalCategoria');
        const form = document.getElementById('formCategoria');
        const title = document.getElementById('modalTitle');

        if (!modal || !form || !title) {
            console.error('No se encontraron elementos del modal');
            return;
        }

        form.reset();
        document.getElementById('categoriaId').value = '';

        if (categoriaId) {
            // Modo edición
            title.textContent = 'Editar Categoría';
            const categoria = this.categorias.find(c => c.id == categoriaId);
            
            if (categoria) {
                document.getElementById('categoriaId').value = categoria.id;
                document.getElementById('nombre').value = categoria.nombre;
                document.getElementById('descripcion').value = categoria.descripcion || '';
                // Seleccionar el color correcto
                const colorInput = document.querySelector(`input[name="color"][value="${categoria.color}"]`);
                if (colorInput) {
                    colorInput.checked = true;
                }
            }
        } else {
            // Modo creación
            title.textContent = 'Nueva Categoría';
            // Seleccionar el primer color por defecto
            const firstColor = document.querySelector('input[name="color"]');
            if (firstColor) {
                firstColor.checked = true;
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('show');
    }

    hideModal() {
        const modal = document.getElementById('modalCategoria');
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }

    async saveCategoria() {
        const categoriaId = document.getElementById('categoriaId').value;
        const nombre = document.getElementById('nombre').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();
        const colorInput = document.querySelector('input[name="color"]:checked');
        const color = colorInput ? colorInput.value : '#FF5252';

        if (!nombre) {
            showAlert('El nombre es requerido', 'error');
            return;
        }

        const submitBtn = document.getElementById('btnGuardarCategoria');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="spinner-border"></div> Guardando...';

        try {
            const url = '../../api/categorias/index.php';
            const method = categoriaId ? 'PUT' : 'POST';
            const body = { nombre, descripcion, color };
            
            if (categoriaId) {
                body.id = categoriaId;
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                this.hideModal();
                await this.loadCategorias();
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión al guardar categoría', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    editCategoria(id) {
        this.showModal(id);
    }

    async deleteCategoria(id) {
        const categoria = this.categorias.find(c => c.id == id);
        
        if (!categoria) return;

        if (!confirm(`¿Estás seguro de eliminar la categoría "${categoria.nombre}"?`)) {
            return;
        }

        try {
            const response = await fetch('../../api/categorias/index.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ id })
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                await this.loadCategorias();
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión al eliminar categoría', 'error');
        }
    }
}

// Inicializar módulo cuando se carga la página
let categoriasModule;
