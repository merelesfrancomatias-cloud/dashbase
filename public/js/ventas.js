// Módulo de Punto de Venta
console.log('ventas.js cargado - versión 1762010986');

class PuntoVentaModule {
    constructor() {
        console.log('PuntoVentaModule constructor llamado');
        this.productos = [];
        this.carrito = [];
        this.cajaActiva = null;
        this.init();
    }

    async init() {
        await this.checkCajaActiva();
        if (this.cajaActiva) {
            await this.loadProductosGrid();
            this.setupEventListeners();
            this.renderCarrito();
        }
    }

    setupEventListeners() {
        // Búsqueda de productos
        const searchProducto = document.getElementById('searchProducto');
        if (searchProducto) {
            searchProducto.addEventListener('input', (e) => {
                this.filterProductos(e.target.value);
            });

            // Buscar por código de barras
            searchProducto.addEventListener('keypress', async (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    await this.buscarPorCodigo(e.target.value);
                }
            });
        }

        // Método de pago — actualizar estado visual del botón activo
        document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                // Quitar clase activa de todos los labels
                document.querySelectorAll('.pm-btn').forEach(l => l.classList.remove('pm-btn--active'));
                // Activar el label correspondiente
                const label = document.querySelector(`label[for="${e.target.id}"]`);
                if (label) label.classList.add('pm-btn--active');
                this.updateTotal();
            });
        });

        // Descuento
        const descuento = document.getElementById('descuento');
        if (descuento) {
            descuento.addEventListener('input', () => {
                this.updateTotal();
            });
        }

        // Procesar venta
        const btnProcesarVenta = document.getElementById('btnProcesarVenta');
        if (btnProcesarVenta) {
            btnProcesarVenta.addEventListener('click', () => {
                this.procesarVenta();
            });
        }

        // Limpiar carrito
        const btnLimpiarCarrito = document.getElementById('btnLimpiarCarrito');
        if (btnLimpiarCarrito) {
            btnLimpiarCarrito.addEventListener('click', () => {
                if (confirm('¿Limpiar el carrito?')) {
                    this.carrito = [];
                    this.renderCarrito();
                }
            });
        }
    }

    async checkCajaActiva() {
        try {
            const base = window.APP_BASE || '../..';
            const response = await fetch(`${base}/api/caja/index.php?activa=true`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success && data.data) {
                this.cajaActiva = data.data;
                this.renderCajaInfo();
            } else {
                this.renderSinCaja();
            }
        } catch (error) {
            console.error('Error:', error);
            this.renderSinCaja();
        }
    }

    renderCajaInfo() {
        const container = document.getElementById('cajaInfo');
        container.innerHTML = `
            <div class="flex-between">
                <span><i class="fas fa-cash-register"></i> Caja Abierta</span>
                <span class="badge badge-success">Activa</span>
            </div>
        `;
    }

    renderSinCaja() {
        const container = document.getElementById('mainContainer');
        container.innerHTML = `
            <div class="content-card">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="empty-state-title">Caja Cerrada</h3>
                    <p class="empty-state-text">Debes abrir una caja antes de realizar ventas</p>
                    <a href="../caja/index.php" class="btn btn-primary">
                        <i class="fas fa-cash-register"></i>
                        Ir a Caja
                    </a>
                </div>
            </div>
        `;
    }

    async loadProductosGrid() {
        try {
            const base = window.APP_BASE || '../..';

            // 1. Obtener top vendidos (hasta 12)
            const rTop = await fetch(`${base}/api/ventas/index.php?top_productos=1&limit=12`, {
                credentials: 'include'
            });
            const dTop = await rTop.json();
            const topList = (dTop.success && Array.isArray(dTop.data)) ? dTop.data : [];
            const topIds = new Set(topList.map(p => String(p.id)));

            // Marcar los top
            topList.forEach(p => { p._esTop = true; });

            // 2. Obtener catálogo completo (para búsqueda) y para rellenar hasta 20
            const rAll = await fetch(`${base}/api/productos/index.php`, {
                credentials: 'include'
            });
            const dAll = await rAll.json();
            const allList = Array.isArray(dAll.data?.productos ?? dAll.data) ?
                (dAll.data?.productos ?? dAll.data) : [];

            // 3. Extras: del catálogo que no estén en top, hasta completar 20
            const extras = allList
                .filter(p => !topIds.has(String(p.id)))
                .slice(0, Math.max(0, 20 - topList.length));

            // 4. Lista final para el grid (top primero + extras)
            const gridList = [...topList, ...extras];

            // 5. Lista completa para búsqueda (incluye todos)
            this.productos = allList.length > 0 ? allList : gridList;
            // Propagar marca _esTop a la lista completa
            topIds.forEach(id => {
                const p = this.productos.find(x => String(x.id) === id);
                if (p) p._esTop = true;
            });

            // 6. Ocultar sección separada de top (ya no se usa)
            const topSection = document.getElementById('topProductosSection');
            if (topSection) topSection.style.display = 'none';

            // 7. Renderizar única grilla
            this.renderProductos(gridList);

        } catch (error) {
            console.error('Error cargando productos:', error);
        }
    }

    // Mantener loadProductos como alias para búsqueda/filtrado que llama a renderProductos
    async loadProductos() {
        await this.loadProductosGrid();
    }

    async loadTopProductos() {
        // Ya integrado en loadProductosGrid — no-op
    }

    renderTopProductos(productos) {
        const container = document.getElementById('topProductosGrid');
        if (!container) return;
        const base = window.APP_BASE || '../..';
        let html = '';
        productos.forEach(p => {
            const foto = p.foto
                ? `${base}/public/uploads/productos/${p.foto}`
                : `${base}/public/img/no-image.svg`;
            const vendidos = p.total_vendido > 0
                ? `<span style="background:#f59e0b;color:#fff;border-radius:20px;font-size:10px;padding:1px 7px;font-weight:700;">${p.total_vendido} vend.</span>`
                : `<span style="background:#6c757d;color:#fff;border-radius:20px;font-size:10px;padding:1px 7px;">stk: ${p.stock}</span>`;
            html += `
                <div onclick="puntoVentaModule.agregarAlCarrito(${JSON.stringify(p).replace(/"/g, '&quot;')})"
                     title="${p.nombre} — $${parseFloat(p.precio_venta).toFixed(2)}"
                     style="display:flex;align-items:center;gap:8px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;padding:7px 10px;cursor:pointer;transition:all .2s;min-width:0;max-width:200px;"
                     onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-light, #e8f5f2)';"
                     onmouseout="this.style.borderColor='var(--border-color)';this.style.background='var(--bg-secondary)';">
                    <img src="${foto}" alt="${p.nombre}"
                         style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;"
                         onerror="this.src='${base}/public/img/no-image.svg'">
                    <div style="overflow:hidden;min-width:0;">
                        <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-primary);">${p.nombre}</div>
                        <div style="font-size:11px;color:var(--success);font-weight:700;">$${parseFloat(p.precio_venta).toFixed(2)}</div>
                        <div>${vendidos}</div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;

        // Botón "Ver todos" — oculta/muestra la grilla completa
        const btnVerTodos = document.getElementById('btnVerTodosProductos');
        if (btnVerTodos) {
            btnVerTodos.addEventListener('click', () => {
                const grid = document.getElementById('productosGrid');
                if (grid) {
                    grid.style.display = grid.style.display === 'none' ? '' : 'none';
                    btnVerTodos.innerHTML = grid.style.display === 'none'
                        ? 'Ver todos <i class="fas fa-chevron-down"></i>'
                        : 'Ocultar <i class="fas fa-chevron-up"></i>';
                }
            });
        }
    }

    filterProductos(search) {
        const filtered = this.productos.filter(p => 
            p.nombre.toLowerCase().includes(search.toLowerCase()) ||
            (p.codigo_barras && p.codigo_barras.includes(search))
        );
        this.renderProductos(filtered);
    }

    async buscarPorCodigo(codigo) {
        if (!codigo) return;

        const producto = this.productos.find(p => p.codigo_barras === codigo);
        
        if (producto) {
            this.agregarAlCarrito(producto);
            document.getElementById('searchProducto').value = '';
            document.getElementById('searchProducto').focus();
        } else {
            showAlert('Producto no encontrado', 'error');
        }
    }

    renderProductos(productos) {
        const container = document.getElementById('productosGrid');
        const base = window.APP_BASE || '../..';
        
        if (productos.length === 0) {
            container.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:40px;color:#888;">
                    <i class="fas fa-search" style="font-size:48px;margin-bottom:15px;opacity:0.5;display:block;"></i>
                    <p>No se encontraron productos</p>
                </div>
            `;
            return;
        }

        const precio = (p) => typeof formatCurrency === 'function'
            ? formatCurrency(p)
            : '$' + parseFloat(p || 0).toLocaleString('es-AR', {minimumFractionDigits:2});

        let html = '';
        productos.forEach(producto => {
            const fotoUrl = producto.foto
                ? `${base}/public/uploads/productos/${producto.foto}`
                : `${base}/public/img/no-image.svg`;
            const sinStock = producto.stock <= 0;
            const nombre = (producto.nombre || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');

            html += `
                <div onclick="puntoVentaModule.agregarAlCarrito(${JSON.stringify(producto).replace(/"/g, '&quot;')})"
                     title="${nombre}"
                     style="background:#fff;border-radius:10px;border:2px solid #e2e8f0;cursor:pointer;position:relative;display:flex;flex-direction:column;${sinStock ? 'opacity:.65;' : ''}"
                     onmouseover="this.style.borderColor='#0fd186';this.style.boxShadow='0 4px 12px rgba(15,209,134,.25)'"
                     onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    ${sinStock ? '<span style="position:absolute;top:4px;right:4px;background:#ef4444;color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:6px;z-index:2;">Sin stock</span>' : ''}
                    ${producto._esTop && producto.total_vendido > 0 ? `<span style="position:absolute;top:4px;left:4px;background:#f59e0b;color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:6px;z-index:2;">🔥${producto.total_vendido}</span>` : ''}
                    <div style="width:100%;padding-top:75%;position:relative;background:#f1f5f9;flex-shrink:0;border-radius:8px 8px 0 0;overflow:hidden;">
                        <img src="${fotoUrl}" alt="${nombre}"
                             onerror="this.src='${base}/public/img/no-image.svg'"
                             loading="lazy"
                             style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;">
                    </div>
                    <div style="padding:6px 8px 8px;display:flex;flex-direction:column;gap:2px;min-width:0;">
                        <span style="font-size:12px;font-weight:700;color:#1e293b;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3;">${nombre}</span>
                        <span style="font-size:13px;font-weight:800;color:#059669;display:block;line-height:1.3;">${precio(producto.precio_venta)}</span>
                        <span style="font-size:10px;color:#94a3b8;display:block;"><i class="fas fa-cubes" style="font-size:9px;margin-right:2px;"></i>${producto.stock}</span>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    agregarAlCarrito(producto) {
        const itemExistente = this.carrito.find(item => item.producto_id === producto.id);
        
        if (itemExistente) {
            // Permitir agregar más productos sin validar stock
            itemExistente.cantidad++;
        } else {
            this.carrito.push({
                producto_id: producto.id,
                nombre: producto.nombre,
                precio_unitario: parseFloat(producto.precio_venta),
                cantidad: 1,
                stock: producto.stock
            });
        }
        
        this.renderCarrito();
        showAlert(`${producto.nombre} agregado al carrito`, 'success');
    }

    renderCarrito() {
        const container = document.getElementById('carritoItems');
        
        // Actualizar badge del carrito móvil
        const cartBadge = document.getElementById('cartBadge');
        const totalItems = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
        if (cartBadge) {
            cartBadge.textContent = totalItems;
            cartBadge.style.display = totalItems > 0 ? 'flex' : 'none';
        }
        
        if (this.carrito.length === 0) {
            container.innerHTML = `
                <div class="text-center" style="padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Carrito vacío</p>
                </div>
            `;
            this.updateTotal();
            return;
        }

        let html = '';
        this.carrito.forEach((item, index) => {
            html += `
                <div class="carrito-item">
                    <div class="carrito-item-header">
                        <div class="carrito-item-info">
                            <h5>${item.nombre}</h5>
                            <p>${formatCurrency(item.precio_unitario)} x ${item.cantidad}</p>
                        </div>
                        <div class="carrito-item-total">
                            ${formatCurrency(item.precio_unitario * item.cantidad)}
                        </div>
                    </div>
                    <div class="carrito-item-actions">
                        <div class="carrito-item-controls">
                            <button class="btn-icon-sm" onclick="puntoVentaModule.cambiarCantidad(${index}, -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="cantidad-display">${item.cantidad}</span>
                            <button class="btn-icon-sm" onclick="puntoVentaModule.cambiarCantidad(${index}, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button class="btn-icon-sm btn-delete" onclick="puntoVentaModule.eliminarItem(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        this.updateTotal();
    }

    cambiarCantidad(index, cambio) {
        const item = this.carrito[index];
        const nuevaCantidad = item.cantidad + cambio;
        
        if (nuevaCantidad <= 0) {
            this.eliminarItem(index);
            return;
        }
        
        // Permitir cambiar cantidad sin validar stock
        item.cantidad = nuevaCantidad;
        this.renderCarrito();
    }

    eliminarItem(index) {
        this.carrito.splice(index, 1);
        this.renderCarrito();
    }

    updateTotal() {
        const subtotal = this.carrito.reduce((sum, item) => sum + (item.precio_unitario * item.cantidad), 0);
        const descuentoEl = document.getElementById('descuento');
        const descuento = descuentoEl ? parseFloat(descuentoEl.value) || 0 : 0;
        const total = subtotal - descuento;

        document.getElementById('subtotalAmount').textContent = formatCurrency(subtotal);
        document.getElementById('descuentoAmount').textContent = formatCurrency(descuento);
        document.getElementById('totalAmount').textContent = formatCurrency(total);

        // Habilitar/deshabilitar botón
        const btnProcesar = document.getElementById('btnProcesarVenta');
        btnProcesar.disabled = this.carrito.length === 0;
    }

    async procesarVenta() {
        if (this.carrito.length === 0) {
            showAlert('El carrito está vacío', 'error');
            return;
        }

        const metodoPagoChecked = document.querySelector('input[name="metodo_pago"]:checked');
        const metodoPago = metodoPagoChecked ? metodoPagoChecked.value : 'efectivo';
        const descuentoEl = document.getElementById('descuento');
        const descuento = descuentoEl ? parseFloat(descuentoEl.value) || 0 : 0;

        const btnProcesar = document.getElementById('btnProcesarVenta');
        const originalText = btnProcesar.innerHTML;
        btnProcesar.disabled = true;
        btnProcesar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        try {
            const base = window.APP_BASE || '../..';
            const response = await fetch(`${base}/api/ventas/index.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    items: this.carrito,
                    metodo_pago: metodoPago,
                    descuento: descuento
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('¡Venta procesada exitosamente!', 'success');

                const ventaData = {
                    id: data.data.venta_id,
                    items: [...this.carrito],
                    subtotal: this.carrito.reduce((s, i) => s + (i.precio_unitario * i.cantidad), 0),
                    descuento: descuento,
                    total: this.carrito.reduce((s, i) => s + (i.precio_unitario * i.cantidad), 0) - descuento,
                    metodo_pago: metodoPago,
                    fecha: new Date()
                };

                this.mostrarModalImpresion(ventaData);

                this.carrito = [];
                this.renderCarrito();
                document.getElementById('descuento').value = '';
                document.getElementById('searchProducto').value = '';
                await this.loadProductos();
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        } finally {
            btnProcesar.disabled = false;
            btnProcesar.innerHTML = originalText;
        }
    }

    // Eliminado: mostrarModalMetodoPago y confirmarVenta ya no son necesarios
    //  porque el método de pago está siempre visible en el carrito.

    mostrarModalImpresion(ventaData) {
        // Crear modal de impresión
        const modalHTML = `
            <div class="modal-overlay show" id="modalImpresion">
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h2><i class="fas fa-receipt"></i> Venta Completada</h2>
                        <span class="close" onclick="document.getElementById('modalImpresion').remove()">&times;</span>
                    </div>
                    <div class="modal-body" style="text-align: center;">
                        <div style="font-size: 48px; color: var(--success); margin: 20px 0;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 style="margin: 0 0 10px 0;">¡Venta Exitosa!</h3>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary); margin: 20px 0;">
                            ${formatCurrency(ventaData.total)}
                        </p>
                        <p style="color: var(--text-secondary); margin-bottom: 30px;">
                            Ticket #${ventaData.id}
                        </p>
                        <button class="btn btn-primary btn-block" onclick="puntoVentaModule.imprimirTicket(${JSON.stringify(ventaData).replace(/"/g, '&quot;')})">
                            <i class="fas fa-print"></i> Imprimir Ticket
                        </button>
                        <button class="btn btn-secondary btn-block" onclick="document.getElementById('modalImpresion').remove()" style="margin-top: 10px;">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    imprimirTicket(ventaData) {
        // Crear ventana de impresión con formato de ticket 80mm
        const ticketWindow = window.open('', '_blank', 'width=302,height=600');
        
        const ticketHTML = this.generarTicketHTML(ventaData);
        
        ticketWindow.document.write(ticketHTML);
        ticketWindow.document.close();
        
        // Esperar a que cargue y luego imprimir
        setTimeout(() => {
            ticketWindow.focus();
            ticketWindow.print();
            // Cerrar modal después de imprimir
            const modalImpresion = document.getElementById('modalImpresion');
            if (modalImpresion) {
                modalImpresion.remove();
            }
        }, 500);
    }

    generarTicketHTML(ventaData) {
        const usuario = JSON.parse(localStorage.getItem('user')) || {};
        const fecha = new Date(ventaData.fecha);
        
        return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket #${ventaData.id}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            padding: 10px;
            width: 80mm;
            max-width: 80mm;
        }
        
        .ticket {
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .items {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }
        
        .item {
            margin-bottom: 8px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        
        .totals {
            margin-bottom: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11px;
        }
        
        .total-row.grand-total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- HEADER -->
        <div class="header">
            <h1>DASH4 CRM</h1>
            <p>Sistema de Punto de Venta</p>
            <p>═══════════════════════════</p>
        </div>
        
        <!-- INFO -->
        <div class="info">
            <div class="info-row">
                <span>Ticket:</span>
                <span><strong>#${String(ventaData.id).padStart(6, '0')}</strong></span>
            </div>
            <div class="info-row">
                <span>Fecha:</span>
                <span>${fecha.toLocaleDateString('es-AR')}</span>
            </div>
            <div class="info-row">
                <span>Hora:</span>
                <span>${fecha.toLocaleTimeString('es-AR')}</span>
            </div>
            <div class="info-row">
                <span>Cajero:</span>
                <span>${usuario.nombre || 'Usuario'}</span>
            </div>
            <div class="info-row">
                <span>Pago:</span>
                <span>${this.formatMetodoPago(ventaData.metodo_pago)}</span>
            </div>
        </div>
        
        <!-- ITEMS -->
        <div class="items">
            ${ventaData.items.map(item => `
                <div class="item">
                    <div class="item-name">${item.nombre}</div>
                    <div class="item-details">
                        <span>${item.cantidad} x ${formatCurrency(item.precio_unitario)}</span>
                        <span><strong>${formatCurrency(item.precio_unitario * item.cantidad)}</strong></span>
                    </div>
                </div>
            `).join('')}
        </div>
        
        <!-- TOTALS -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${formatCurrency(ventaData.subtotal)}</span>
            </div>
            ${ventaData.descuento > 0 ? `
                <div class="total-row">
                    <span>Descuento:</span>
                    <span>-${formatCurrency(ventaData.descuento)}</span>
                </div>
            ` : ''}
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>${formatCurrency(ventaData.total)}</span>
            </div>
        </div>
        
        <!-- FOOTER -->
        <div class="footer">
            <p>═══════════════════════════</p>
            <p><strong>¡Gracias por su compra!</strong></p>
            <p>Vuelva pronto</p>
            <p style="margin-top: 10px; font-size: 9px;">
                Powered by DASH4
            </p>
        </div>
    </div>
</body>
</html>
        `;
    }

    formatMetodoPago(metodo) {
        const metodos = {
            'efectivo': 'Efectivo',
            'tarjeta_debito': 'Tarjeta Débito',
            'tarjeta_credito': 'Tarjeta Crédito',
            'tarjeta': 'Tarjeta',
            'transferencia': 'Transferencia',
            'mercadopago': 'MercadoPago',
            'otro': 'Otro',
            'mixto': 'Mixto'
        };
        return metodos[metodo] || metodo;
    }
}

// Variable global
let puntoVentaModule;
