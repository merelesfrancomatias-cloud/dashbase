// Módulo de Tienda Virtual
console.log('🛍️ Cargando módulo tienda.js...');

class TiendaVirtual {
    constructor() {
        this.productos = [];
        this.categorias = [];
        this.carrito = this.cargarCarrito();
        this.perfilNegocio = null;
        this.categoriaSeleccionada = '';
        this.autoScrollInterval = null;
        this.debounceTimer = null;
        this.init();
    }

    async init() {
        console.log('⚙️ Inicializando tienda virtual...');
        await this.cargarPerfilNegocio();
        await this.cargarCategorias();
        await this.cargarProductos();
        this.setupEventListeners();
        this.actualizarCarrito();
    }
    formatearPrecio(precio) {
        const precioEntero = Math.round(precio);
        return precioEntero.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    }
    async cargarPerfilNegocio() {
        if (!TIENDA_NEGOCIO_ID) return;
        try {
            const response = await fetch(`../../api/tienda/perfil.php?negocio_id=${TIENDA_NEGOCIO_ID}`);
            const data = await response.json();
            if (data.success) {
                this.perfilNegocio = data.data;

                // Actualizar hero con el nombre real del negocio
                const heroTitle = document.getElementById('heroTitle');
                const heroSubtitle = document.getElementById('heroSubtitle');
                if (heroTitle && this.perfilNegocio.nombre_negocio) {
                    heroTitle.textContent = `¡Bienvenido a ${this.perfilNegocio.nombre_negocio}!`;
                }
                if (heroSubtitle) {
                    heroSubtitle.textContent = 'Explorá nuestros productos y hacé tu pedido fácilmente por WhatsApp.';
                }

                // Imagen de portada como fondo del hero
                const heroBanner = document.querySelector('.hero-banner');
                if (heroBanner && this.perfilNegocio.imagen_portada) {
                    const portadaUrl = `../../public/uploads/portadas/${this.perfilNegocio.imagen_portada}`;
                    heroBanner.style.backgroundImage = `url('${portadaUrl}')`;
                    heroBanner.classList.add('has-portada');
                }

                // Actualizar logo si existe
                const logoImg = document.getElementById('logoImg');
                if (logoImg && this.perfilNegocio.logo) {
                    logoImg.src = `../../public/uploads/logos/${this.perfilNegocio.logo}`;
                    logoImg.onerror = () => { logoImg.src = '../../public/img/DASHLOGOSF.png'; };
                }

                // Activar botón flotante de WhatsApp con el número del negocio
                const wa = this.perfilNegocio.whatsapp || this.perfilNegocio.telefono || '';
                const waTel = wa.replace(/\D/g, '');
                const waFloat = document.getElementById('whatsappFloat');
                if (waFloat && waTel) {
                    waFloat.href = `https://wa.me/${waTel}`;
                    waFloat.target = '_blank';
                    waFloat.style.display = 'flex';
                }
            }
        } catch (error) {
            console.error('Error al cargar perfil:', error);
        }
    }

    async cargarCategorias() {
        if (!TIENDA_NEGOCIO_ID) return;
        try {
            this.showSkeletonCategorias();
            const response = await fetch(`../../api/tienda/categorias.php?negocio_id=${TIENDA_NEGOCIO_ID}`);
            const data = await response.json();
            
            if (data.success) {
                this.categorias = data.data || [];
                this.hideSkeletonCategorias();
                this.renderizarCategorias();
            }
        } catch (error) {
            console.error('Error al cargar categorías:', error);
            this.hideSkeletonCategorias();
        }
    }

    async cargarProductos() {
        if (!TIENDA_NEGOCIO_ID) {
            document.getElementById('productosGrid').style.display = 'none';
            document.getElementById('noResultados').style.display = 'block';
            document.getElementById('noResultados').innerHTML = '<i class="fas fa-store-slash"></i><p>No se encontró la tienda. Verificá el enlace.</p>';
            this.hideSkeletonProductos();
            return;
        }
        try {
            this.showSkeletonProductos();
            const response = await fetch(`../../api/tienda/productos.php?negocio_id=${TIENDA_NEGOCIO_ID}`);
            const data = await response.json();
            
            if (data.success) {
                this.productos = data.data || [];
                this.renderizarProductos(this.productos);
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
        } finally {
            this.hideSkeletonProductos();
        }
    }

    renderizarCategorias() {
        const carrusel = document.getElementById('categoriasCarrusel');
        
        // Eliminar todas las cards dinámicas (mantener solo la de "Todas")
        carrusel.querySelectorAll('[data-categoria]:not([data-categoria=""])').forEach(c => c.remove());

        // Asignar listener a "Todas" solo si aún no lo tiene
        const todasCard = carrusel.querySelector('[data-categoria=""]');
        if (todasCard && !todasCard._listenerAdded) {
            todasCard.addEventListener('click', () => this.seleccionarCategoria(''));
            todasCard._listenerAdded = true;
        }

        // Iconos para diferentes categorías
        const iconos = {
            'remeras': 'fa-tshirt',
            'pantalones': 'fa-jeans',
            'vestidos': 'fa-dress',
            'zapatos': 'fa-shoe-prints',
            'accesorios': 'fa-hat-cowboy',
            'camperas': 'fa-jacket',
            'shorts': 'fa-shorts',
            'buzos': 'fa-hoodie',
            'default': 'fa-tag'
        };
        
        this.categorias.forEach(cat => {
            const card = document.createElement('div');
            card.className = 'categoria-card';
            card.setAttribute('data-categoria', cat.id);
            
            // Buscar un ícono apropiado
            const nombreLower = cat.nombre.toLowerCase();
            let icono = iconos.default;
            for (const [key, value] of Object.entries(iconos)) {
                if (nombreLower.includes(key)) {
                    icono = value;
                    break;
                }
            }
            
            card.innerHTML = `
                <div class="categoria-icon">
                    <i class="fas ${icono}"></i>
                </div>
                <span>${cat.nombre}</span>
            `;
            
            card.addEventListener('click', () => this.seleccionarCategoria(cat.id));
            carrusel.appendChild(card);
        });
        
        // Actualizar estadística de categorías
        const totalCategorias = document.getElementById('totalCategorias');
        if (totalCategorias) {
            totalCategorias.textContent = this.categorias.length;
        }
        
        // Iniciar auto-scroll
        this.iniciarAutoScroll();
    }
    
    seleccionarCategoria(categoriaId) {
        // Actualizar cards activas
        document.querySelectorAll('.categoria-card').forEach(card => {
            card.classList.remove('active');
        });
        
        const cardSeleccionada = document.querySelector(`[data-categoria="${categoriaId}"]`);
        if (cardSeleccionada) {
            cardSeleccionada.classList.add('active');
        }
        
        // Filtrar productos
        this.categoriaSeleccionada = categoriaId;
        this.filtrarProductos();
    }
    
    scrollCategorias(direccion) {
        const carrusel = document.getElementById('categoriasCarrusel');
        const scrollAmount = 300;
        carrusel.scrollBy({
            left: direccion * scrollAmount,
            behavior: 'smooth'
        });
    }
    
    iniciarAutoScroll() {
        // Desactivar auto-scroll en dispositivos móviles
        if (window.innerWidth <= 768) return;

        const carrusel = document.getElementById('categoriasCarrusel');
        if (!carrusel || carrusel._scrollListenersAdded) {
            // Solo reiniciar el intervalo si ya se inicializó antes
            if (carrusel && this.autoScrollInterval) return;
        }

        let scrollPos = 0;
        let direction = 1;

        clearInterval(this.autoScrollInterval);
        this.autoScrollInterval = setInterval(() => {
            const maxScroll = carrusel.scrollWidth - carrusel.clientWidth;
            if (scrollPos >= maxScroll) direction = -1;
            else if (scrollPos <= 0)    direction = 1;
            scrollPos += direction * 2;
            carrusel.scrollLeft = scrollPos;
        }, 50);

        // Registrar listeners solo la primera vez
        if (!carrusel._scrollListenersAdded) {
            carrusel.addEventListener('mouseenter', () => clearInterval(this.autoScrollInterval));
            carrusel.addEventListener('touchstart',  () => clearInterval(this.autoScrollInterval), { passive: true });
            carrusel.addEventListener('mouseleave',  () => {
                clearInterval(this.autoScrollInterval);
                this.iniciarAutoScroll();
            });
            carrusel._scrollListenersAdded = true;
        }
    }

    renderizarProductos(productos) {
        const grid = document.getElementById('productosGrid');
        const noResultados = document.getElementById('noResultados');
        const count = document.getElementById('productoCount');
        
        if (productos.length === 0) {
            grid.style.display = 'none';
            noResultados.style.display = 'block';
            if (count) count.textContent = '0 productos';
            return;
        }
        
        grid.style.display = 'grid';
        noResultados.style.display = 'none';
        grid.innerHTML = '';
        
        productos.forEach(producto => {
            const card = this.crearProductoCard(producto);
            grid.appendChild(card);
        });
        
        if (count) count.textContent = `${productos.length} producto${productos.length !== 1 ? 's' : ''}`;

        // Actualizar estadísticas del hero
        const totalProductos = document.getElementById('totalProductos');
        if (totalProductos && productos === this.productos) {
            totalProductos.textContent = this.productos.length;
        }
    }

    crearProductoCard(producto) {
        const div = document.createElement('div');
        div.className = 'producto-card';
        
        const imagen = producto.imagen 
            ? `../../public/uploads/productos/${producto.imagen}`
            : '../../public/img/no-image.svg';
        
        const stock   = parseInt(producto.stock) || 0;
        const sinStock = stock <= 0;
        
        div.innerHTML = `
            <div class="producto-img-wrap">
                <img src="${imagen}" alt="${producto.nombre}" class="producto-imagen"
                     onerror="this.src='../../public/img/no-image.svg'">
                ${sinStock ? '<div class="producto-badge-sin-stock">Sin stock</div>' : ''}
            </div>
            <div class="producto-info">
                <div class="producto-categoria">${producto.categoria_nombre || 'General'}</div>
                <div class="producto-nombre">${producto.nombre}</div>
                ${producto.descripcion ? `<div class="producto-descripcion">${producto.descripcion}</div>` : ''}
                <div class="producto-footer">
                    <div class="producto-precio">$${this.formatearPrecio(producto.precio_venta)}</div>
                    ${!sinStock
                        ? `<button class="btn-agregar" onclick="tienda.agregarAlCarrito(${producto.id})">
                               <i class="fas fa-plus"></i> Agregar
                           </button>`
                        : '<span class="sin-stock-label">Sin stock</span>'
                    }
                </div>
                <div class="producto-stock ${sinStock ? 'sin-stock' : ''}">
                    ${sinStock ? 'No disponible' : `${stock} disponibles`}
                </div>
            </div>
        `;
        
        return div;
    }

    setupEventListeners() {
        // Búsqueda con debounce para ambos inputs
        const searchInput = document.getElementById('searchInput');
        const searchInputMobile = document.getElementById('searchInputMobile');
        
        const handleSearch = (e) => {
            // Limpiar el timer anterior
            clearTimeout(this.debounceTimer);
            
            // Sincronizar valores entre inputs
            const value = e.target.value;
            if (searchInput) searchInput.value = value;
            if (searchInputMobile) searchInputMobile.value = value;
            
            // Esperar 500ms después de que el usuario deje de escribir
            this.debounceTimer = setTimeout(() => {
                this.filtrarProductos();
            }, 500);
        };
        
        if (searchInput) {
            searchInput.addEventListener('input', handleSearch);
        }
        
        if (searchInputMobile) {
            searchInputMobile.addEventListener('input', handleSearch);
        }
    }

    filtrarProductos() {
        const searchInput = document.getElementById('searchInput');
        const searchInputMobile = document.getElementById('searchInputMobile');
        
        // Tomar el valor de cualquiera de los dos inputs que tenga contenido
        const searchTerm = (searchInput?.value || searchInputMobile?.value || '').toLowerCase();
        let productosFiltrados = this.productos;
        
        // Filtrar por búsqueda
        if (searchTerm) {
            productosFiltrados = productosFiltrados.filter(p => 
                p.nombre.toLowerCase().includes(searchTerm) ||
                (p.descripcion && p.descripcion.toLowerCase().includes(searchTerm))
            );
        }
        
        // Filtrar por categoría
        if (this.categoriaSeleccionada) {
            productosFiltrados = productosFiltrados.filter(p => 
                p.categoria_id == this.categoriaSeleccionada
            );
        }
        
        this.renderizarProductos(productosFiltrados);
    }

    agregarAlCarrito(productoId) {
        const producto = this.productos.find(p => p.id == productoId);
        if (!producto) return;
        
        const stock = parseInt(producto.stock) || 0;
        if (stock <= 0) {
            this.showNotification('Producto sin stock', 'error');
            return;
        }
        
        const itemExistente = this.carrito.find(item => item.id == productoId);
        
        if (itemExistente) {
            if (itemExistente.cantidad >= stock) {
                this.showNotification('No hay más stock disponible', 'warning');
                return;
            }
            itemExistente.cantidad++;
        } else {
            this.carrito.push({
                id: producto.id,
                nombre: producto.nombre,
                precio: parseFloat(producto.precio_venta),
                imagen: producto.imagen,
                cantidad: 1,
                stock: stock
            });
        }
        
        this.guardarCarrito();
        this.actualizarCarrito();
        this.showNotification('Producto agregado al carrito', 'success');
    }

    eliminarDelCarrito(productoId) {
        this.carrito = this.carrito.filter(item => item.id != productoId);
        this.guardarCarrito();
        this.actualizarCarrito();
    }

    cambiarCantidad(productoId, cambio) {
        const item = this.carrito.find(item => item.id == productoId);
        if (!item) return;
        
        const nuevaCantidad = item.cantidad + cambio;
        
        if (nuevaCantidad <= 0) {
            this.eliminarDelCarrito(productoId);
            return;
        }
        
        if (nuevaCantidad > item.stock) {
            this.showNotification('No hay más stock disponible', 'warning');
            return;
        }
        
        item.cantidad = nuevaCantidad;
        this.guardarCarrito();
        this.actualizarCarrito();
    }

    actualizarCarrito() {
        const carritoItems = document.getElementById('carritoItems');
        const carritoCount = document.getElementById('carritoCount');
        const totalPrecio = document.getElementById('totalPrecio');
        
        // Actualizar contador
        const totalItems = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
        carritoCount.textContent = totalItems;
        
        // Renderizar items
        if (this.carrito.length === 0) {
            carritoItems.innerHTML = `
                <div class="carrito-vacio">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Tu carrito está vacío</p>
                </div>
            `;
            totalPrecio.textContent = '$0';
            return;
        }
        
        carritoItems.innerHTML = '';
        let total = 0;
        
        this.carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            
            const imagen = item.imagen 
                ? `../../public/uploads/productos/${item.imagen}`
                : '../../public/img/no-image.svg';
            
            const itemDiv = document.createElement('div');
            itemDiv.className = 'carrito-item';
            itemDiv.innerHTML = `
                <img src="${imagen}" alt="${item.nombre}" class="carrito-item-img"
                     onerror="this.src='../../public/img/no-image.svg'">
                <div class="carrito-item-info">
                    <div class="carrito-item-nombre">${item.nombre}</div>
                    <div class="carrito-item-precio">$${this.formatearPrecio(item.precio)} c/u</div>
                    <div class="cantidad-control">
                        <button class="cantidad-btn" onclick="tienda.cambiarCantidad(${item.id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="cantidad-valor">${item.cantidad}</span>
                        <button class="cantidad-btn" onclick="tienda.cambiarCantidad(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn-eliminar" onclick="tienda.eliminarDelCarrito(${item.id})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            `;
            carritoItems.appendChild(itemDiv);
        });
        
        totalPrecio.textContent = `$${this.formatearPrecio(total)}`;
    }

    cargarCarrito() {
        const carrito = localStorage.getItem('tienda_carrito');
        return carrito ? JSON.parse(carrito) : [];
    }

    guardarCarrito() {
        localStorage.setItem('tienda_carrito', JSON.stringify(this.carrito));
    }

    limpiarCarrito() {
        if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
            this.carrito = [];
            this.guardarCarrito();
            this.actualizarCarrito();
            this.showNotification('Carrito vaciado', 'info');
        }
    }

    enviarPedidoWhatsApp() {
        if (this.carrito.length === 0) {
            this.showNotification('El carrito está vacío', 'warning');
            return;
        }
        
        let mensaje = '¡Hola! Me gustaría hacer el siguiente pedido:\n\n';
        let total = 0;
        
        this.carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            mensaje += `• ${item.nombre}\n`;
            mensaje += `  Cantidad: ${item.cantidad}\n`;
            mensaje += `  Precio: $${this.formatearPrecio(item.precio)} c/u\n`;
            mensaje += `  Subtotal: $${this.formatearPrecio(subtotal)}\n\n`;
        });
        
        mensaje += `*Total: $${this.formatearPrecio(total)}*`;
        
        const whatsapp = this.perfilNegocio?.whatsapp || '';
        const telefono = whatsapp.replace(/\D/g, '');
        
        if (!telefono) {
            this.showNotification('No hay número de WhatsApp configurado', 'error');
            return;
        }
        
        const url = `https://wa.me/${telefono}?text=${encodeURIComponent(mensaje)}`;
        window.open(url, '_blank');
    }

    showSkeletonProductos() {
        const grid = document.getElementById('productosGrid');
        const skeleton = document.getElementById('productosSkeleton');
        
        grid.style.display = 'none';
        skeleton.style.display = 'grid';
        skeleton.innerHTML = '';
        
        // Crear 8 skeleton cards
        for (let i = 0; i < 8; i++) {
            const skeletonCard = document.createElement('div');
            skeletonCard.className = 'skeleton-card';
            skeletonCard.innerHTML = `
                <div class="skeleton-image"></div>
                <div class="skeleton-content">
                    <div class="skeleton skeleton-text small" style="width: 30%; margin-bottom: 8px;"></div>
                    <div class="skeleton skeleton-text large" style="margin-bottom: 12px;"></div>
                    <div class="skeleton skeleton-text" style="width: 90%; margin-bottom: 6px;"></div>
                    <div class="skeleton skeleton-text" style="width: 70%; margin-bottom: 16px;"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        <div class="skeleton skeleton-text medium" style="height: 24px;"></div>
                        <div class="skeleton skeleton-text" style="width: 100px; height: 40px; border-radius: 20px;"></div>
                    </div>
                </div>
            `;
            skeleton.appendChild(skeletonCard);
        }
    }
    
    hideSkeletonProductos() {
        const grid = document.getElementById('productosGrid');
        const skeleton = document.getElementById('productosSkeleton');
        
        skeleton.style.display = 'none';
        grid.style.display = 'grid';
    }
    
    showSkeletonCategorias() {
        const carrusel = document.getElementById('categoriasCarrusel');
        const todasCard = carrusel.querySelector('[data-categoria=""]');
        
        // Limpiar solo las categorías dinámicas
        const dinamicas = carrusel.querySelectorAll('.categoria-card:not([data-categoria=""])');
        dinamicas.forEach(card => card.remove());
        
        // Agregar skeletons
        for (let i = 0; i < 5; i++) {
            const skeleton = document.createElement('div');
            skeleton.className = 'skeleton-categoria skeleton';
            carrusel.appendChild(skeleton);
        }
    }
    
    hideSkeletonCategorias() {
        const carrusel = document.getElementById('categoriasCarrusel');
        const skeletons = carrusel.querySelectorAll('.skeleton-categoria');
        skeletons.forEach(skeleton => skeleton.remove());
    }

    showNotification(mensaje, tipo = 'info') {
        const icons = { success:'fa-circle-check', error:'fa-circle-exclamation', warning:'fa-triangle-exclamation', info:'fa-circle-info' };
        const toast = document.createElement('div');
        toast.className = `toast-tienda ${tipo}`;
        toast.innerHTML = `<i class="fas ${icons[tipo] || icons.info}"></i> ${mensaje}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3200);
    }
}

// Funciones globales
function toggleCarrito() {
    const panel = document.getElementById('carritoPanel');
    const overlay = document.getElementById('carritoOverlay');
    
    panel.classList.toggle('active');
    overlay.classList.toggle('active');
}

function limpiarCarrito() {
    tienda.limpiarCarrito();
}

function enviarPedidoWhatsApp() {
    tienda.enviarPedidoWhatsApp();
}

// Inicializar cuando el DOM esté listo
let tienda;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        tienda = new TiendaVirtual();
    });
} else {
    tienda = new TiendaVirtual();
}
