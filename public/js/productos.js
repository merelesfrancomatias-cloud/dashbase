// Módulo de Productos
class ProductosModule {
    constructor() {
        this.productos = [];
        this.categorias = [];
        this.currentFoto = null;
        this.vista = 'grid'; // 'grid' | 'lista'
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadCategorias();
        await this.loadProductos();
    }

    setupEventListeners() {
        // Botón nuevo producto
        const btnNuevo = document.getElementById('btnNuevoProducto');
        if (btnNuevo) {
            btnNuevo.addEventListener('click', () => {
                console.log('Botón nuevo producto clickeado');
                this.showModal();
            });
        } else {
            console.error('No se encontró el botón btnNuevoProducto');
        }

        // Cerrar modal
        document.getElementById('btnCerrarModal')?.addEventListener('click', () => {
            this.hideModal();
        });

        // Guardar producto
        document.getElementById('formProducto')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveProducto();
        });

        // Upload foto
        document.getElementById('foto')?.addEventListener('change', (e) => {
            this.handleFotoUpload(e);
        });

        // Búsqueda
        document.getElementById('searchInput')?.addEventListener('input', (e) => {
            this.filterProductos(e.target.value);
        });

        // Filtro categoría
        document.getElementById('filtroCategoria')?.addEventListener('change', () => {
            this.loadProductos();
        });

        // Filtro stock bajo
        document.getElementById('filtroStockBajo')?.addEventListener('change', () => {
            this.loadProductos();
        });

        // Click fuera del modal
        document.getElementById('modalProducto')?.addEventListener('click', (e) => {
            if (e.target.id === 'modalProducto') {
                this.hideModal();
            }
        });
    }

    async loadCategorias() {
        try {
            const base = window.APP_BASE || '../..';
            const response = await fetch(`${base}/api/categorias/index.php`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();
            if (data.success) {
                this.categorias = data.data;
                this.renderCategoriasSelect();
                this.renderCategoriasFilter();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    renderCategoriasSelect() {
        const select = document.getElementById('categoria_id');
        if (!select) return;

        let html = '<option value="">Sin categoría</option>';
        this.categorias.forEach(cat => {
            html += `<option value="${cat.id}">${cat.nombre}</option>`;
        });
        select.innerHTML = html;
    }

    renderCategoriasFilter() {
        const select = document.getElementById('filtroCategoria');
        if (!select) return;

        let html = '<option value="">Todas las categorías</option>';
        this.categorias.forEach(cat => {
            html += `<option value="${cat.id}">${cat.nombre}</option>`;
        });
        select.innerHTML = html;
    }

    async loadProductos() {
        try {
            const base = window.APP_BASE || '../..';
            let url = `${base}/api/productos/index.php?`;
            
            const categoria = document.getElementById('filtroCategoria')?.value;
            if (categoria) url += `categoria=${categoria}&`;
            
            const stockBajo = document.getElementById('filtroStockBajo')?.checked;
            if (stockBajo) url += `stock_bajo=true&`;

            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success) {
                // Si la respuesta tiene estructura con productos y estadísticas
                if (data.data.productos) {
                    this.productos = data.data.productos;
                    this.updateStats(data.data.estadisticas);
                } else {
                    // Compatibilidad con respuesta antigua
                    this.productos = data.data;
                    this.updateStatsOld();
                }
                this.renderProductos(this.productos);
            } else {
                showAlert('Error al cargar productos: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión al cargar productos', 'error');
        }
    }

    updateStats(estadisticas) {
        if (estadisticas) {
            document.getElementById('stockBajo').textContent = estadisticas.stock_bajo;
            document.getElementById('totalProductos').textContent = estadisticas.total;
            document.getElementById('stockValorizado').textContent = formatCurrency(estadisticas.stock_valorizado);
        }
    }

    updateStatsOld() {
        const stockBajo = this.productos.filter(p => p.stock_bajo == 1).length;
        document.getElementById('stockBajo').textContent = stockBajo;
        document.getElementById('totalProductos').textContent = this.productos.length;
    }

    filterProductos(search) {
        const filtered = this.productos.filter(p => 
            p.nombre.toLowerCase().includes(search.toLowerCase()) ||
            (p.codigo_barras && p.codigo_barras.includes(search))
        );
        this.renderProductos(filtered);
    }

    renderProductos(productos) {
        const container = document.getElementById('productosContainer');
        
        if (productos.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-box-open"></i></div>
                    <h3 class="empty-state-title">No hay productos</h3>
                    <p class="empty-state-text">Comienza agregando tu primer producto al inventario</p>
                    <button class="btn btn-primary" onclick="productosModule.showModal()">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                </div>
            `;
            return;
        }

        if (this.vista === 'lista') {
            this._renderLista(container, productos);
        } else {
            this._renderGrid(container, productos);
        }
    }

    setVista(tipo) {
        this.vista = tipo;
        // Actualizar botones toggle
        const btnGrid  = document.getElementById('btnVistaGrid');
        const btnLista = document.getElementById('btnVistaLista');
        if (btnGrid && btnLista) {
            if (tipo === 'grid') {
                btnGrid.style.background  = 'var(--primary)';
                btnGrid.style.color       = '#fff';
                btnLista.style.background = 'var(--surface)';
                btnLista.style.color      = 'var(--text-secondary)';
            } else {
                btnLista.style.background = 'var(--primary)';
                btnLista.style.color      = '#fff';
                btnGrid.style.background  = 'var(--surface)';
                btnGrid.style.color       = 'var(--text-secondary)';
            }
        }
        this.renderProductos(this.productos);
    }

    _renderGrid(container, productos) {
        const base = window.APP_BASE || '../..';
        let html = '<div class="productos-grid-view">';

        productos.forEach(p => {
            const foto = p.foto
                ? `${base}/public/uploads/productos/${p.foto}`
                : `${base}/public/img/no-image.svg`;
            const stockOk    = p.stock_bajo != 1;
            const stockColor = stockOk ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;';
            const alertBadge = !stockOk ? '<span class="stock-alert"><i class="fas fa-exclamation"></i> Bajo</span>' : '';

            html += `
                <div class="producto-card-item">
                    ${alertBadge}
                    <img class="card-img" src="${foto}" alt="${p.nombre}"
                         onerror="this.src='${base}/public/img/no-image.svg'">
                    <div class="card-body">
                        <div class="card-nombre" title="${p.nombre}">${p.nombre}</div>
                        <div class="card-precio">${formatCurrency(p.precio_venta)}</div>
                        <span class="card-stock-badge" style="${stockColor}">
                            <i class="fas fa-cubes"></i> ${p.stock} ${p.unidad_medida || ''}
                        </span>
                        <div class="card-actions">
                            <button class="btn-edit-card" onclick="productosModule.editProducto(${p.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-del-card" onclick="productosModule.deleteProducto(${p.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    _renderLista(container, productos) {
        const base = window.APP_BASE || '../..';
        let html = `
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Código</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        productos.forEach(producto => {
            const fotoUrl   = producto.foto
                ? `${base}/public/uploads/productos/${producto.foto}`
                : `${base}/public/img/no-image.svg`;
            const stockClass  = producto.stock_bajo == 1 ? 'badge-danger' : 'badge-success';
            const precioVenta = formatCurrency(producto.precio_venta);

            html += `
                <tr>
                    <td>
                        <img src="${fotoUrl}" alt="${producto.nombre}"
                             style="width:50px;height:50px;object-fit:cover;border-radius:8px;"
                             onerror="this.src='${base}/public/img/no-image.svg'">
                    </td>
                    <td>
                        <strong>${producto.nombre}</strong>
                        ${producto.descripcion ? `<br><small style="color:var(--text-secondary)">${producto.descripcion}</small>` : ''}
                    </td>
                    <td>
                        ${producto.categoria_nombre
                            ? `<span class="badge" style="background:${producto.categoria_color}20;color:${producto.categoria_color}">${producto.categoria_nombre}</span>`
                            : '<span class="badge badge-secondary">Sin categoría</span>'}
                    </td>
                    <td>${producto.codigo_barras || '-'}</td>
                    <td><strong>${precioVenta}</strong></td>
                    <td>
                        <span class="badge ${stockClass}">
                            ${producto.stock} ${producto.unidad_medida}
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-10">
                            <button class="btn btn-secondary btn-sm btn-icon" onclick="productosModule.editProducto(${producto.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-icon" onclick="productosModule.deleteProducto(${producto.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    showModal(productoId = null) {
        console.log('showModal llamado', productoId);
        const modal = document.getElementById('modalProducto');
        const form = document.getElementById('formProducto');
        const title = document.getElementById('modalTitle');

        if (!modal || !form || !title) {
            console.error('No se encontraron elementos del modal');
            return;
        }

        form.reset();
        document.getElementById('productoId').value = '';
        this.currentFoto = null;
        document.getElementById('fotoPreview').innerHTML = '';

        if (productoId) {
            // Modo edición
            title.textContent = 'Editar Producto';
            const producto = this.productos.find(p => p.id == productoId);
            
            if (producto) {
                document.getElementById('productoId').value = producto.id;
                document.getElementById('nombre').value = producto.nombre;
                document.getElementById('descripcion').value = producto.descripcion || '';
                document.getElementById('categoria_id').value = producto.categoria_id || '';
                document.getElementById('codigo_barras').value = producto.codigo_barras || '';
                document.getElementById('precio_costo').value = producto.precio_costo;
                document.getElementById('precio_venta').value = producto.precio_venta;
                document.getElementById('stock').value = producto.stock;
                document.getElementById('stock_minimo').value = producto.stock_minimo;
                document.getElementById('unidad_medida').value = producto.unidad_medida;
                
                if (producto.foto) {
                    this.currentFoto = producto.foto;
                    const fotoUrl = `${window.APP_BASE}/public/uploads/productos/${producto.foto}`;
                    document.getElementById('fotoPreview').innerHTML = `
                        <img src="${fotoUrl}" style="max-width: 200px; border-radius: 12px; margin-top: 10px;">
                    `;
                }
            }
        } else {
            // Modo creación
            title.textContent = 'Nuevo Producto';
        }

        // Mostrar modal usando la clase 'show' en lugar de remover 'hidden'
        modal.classList.remove('hidden');
        modal.classList.add('show');
    }

    hideModal() {
        const modal = document.getElementById('modalProducto');
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }

    async handleFotoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            showAlert('La imagen es muy grande. Máximo 5MB', 'error');
            return;
        }

        // Validar tipo
        if (!file.type.match('image.*')) {
            showAlert('Solo se permiten imágenes', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('foto', file);

        try {
            const response = await fetch(`${window.APP_BASE}/api/productos/upload.php`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.currentFoto = data.data.filename;
                document.getElementById('fotoPreview').innerHTML = `
                    <img src="${data.data.url}" style="max-width: 200px; border-radius: 12px; margin-top: 10px;">
                `;
                showAlert('Foto subida correctamente', 'success');
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error al subir la foto', 'error');
        }
    }

    async saveProducto() {
        const productoId = document.getElementById('productoId').value;
        const nombre = document.getElementById('nombre').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();
        const categoria_id = document.getElementById('categoria_id').value;
        const codigo_barras = document.getElementById('codigo_barras').value.trim();
        const precio_costo = parseFloat(document.getElementById('precio_costo').value) || 0;
        const precio_venta = parseFloat(document.getElementById('precio_venta').value);
        const stock = parseInt(document.getElementById('stock').value) || 0;
        const stock_minimo = parseInt(document.getElementById('stock_minimo').value) || 0;
        const unidad_medida = document.getElementById('unidad_medida').value;

        if (!nombre || !precio_venta) {
            showAlert('Nombre y precio de venta son requeridos', 'error');
            return;
        }

        const submitBtn = document.getElementById('btnGuardarProducto');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="spinner-border"></div> Guardando...';

        try {
            const base = window.APP_BASE || '../..';
            const url = `${base}/api/productos/index.php`;
            const method = productoId ? 'PUT' : 'POST';
            const body = {
                nombre,
                descripcion,
                categoria_id: categoria_id || null,
                codigo_barras: codigo_barras || null,
                precio_costo,
                precio_venta,
                stock,
                stock_minimo,
                unidad_medida,
                foto: this.currentFoto
            };
            
            if (productoId) {
                body.id = productoId;
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
                await this.loadProductos();
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión al guardar producto', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    editProducto(id) {
        this.showModal(id);
    }

    async deleteProducto(id) {
        const producto = this.productos.find(p => p.id == id);
        
        if (!producto) return;

        if (!confirm(`¿Estás seguro de eliminar el producto "${producto.nombre}"?`)) {
            return;
        }

        try {
            const base = window.APP_BASE || '../..';
            const response = await fetch(`${base}/api/productos/index.php`, {
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
                await this.loadProductos();
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión al eliminar producto', 'error');
        }
    }
}

// Variable global
let productosModule;

// Función auxiliar para formatear moneda
function formatCurrency(value) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(value);
}
