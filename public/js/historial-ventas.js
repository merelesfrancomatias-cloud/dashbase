// Módulo de Historial de Ventas
class HistorialVentasModule {
    constructor() {
        this.ventas = [];
        this.filtros = {
            fecha_inicio: '',
            fecha_fin: '',
            metodo_pago: ''
        };
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadVentas();
    }

    setupEventListeners() {
        // Filtros
        document.getElementById('fecha_inicio')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('fecha_fin')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('metodo_pago')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        // Limpiar filtros
        document.getElementById('btnLimpiarFiltros')?.addEventListener('click', () => {
            this.limpiarFiltros();
        });
    }

    aplicarFiltros() {
        this.filtros.fecha_inicio = document.getElementById('fecha_inicio')?.value || '';
        this.filtros.fecha_fin = document.getElementById('fecha_fin')?.value || '';
        this.filtros.metodo_pago = document.getElementById('metodo_pago')?.value || '';
        
        this.loadVentas();
    }

    limpiarFiltros() {
        document.getElementById('fecha_inicio').value = '';
        document.getElementById('fecha_fin').value = '';
        document.getElementById('metodo_pago').value = '';
        
        this.filtros = {
            fecha_inicio: '',
            fecha_fin: '',
            metodo_pago: ''
        };
        
        this.loadVentas();
    }

    async loadVentas() {
        try {
            let url = '../../api/ventas/index.php?';
            
            if (this.filtros.fecha_inicio) {
                url += `fecha_inicio=${this.filtros.fecha_inicio}&`;
            }
            if (this.filtros.fecha_fin) {
                url += `fecha_fin=${this.filtros.fecha_fin}&`;
            }
            if (this.filtros.metodo_pago) {
                url += `metodo_pago=${this.filtros.metodo_pago}&`;
            }

            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success) {
                this.ventas = data.data;
                this.renderVentas();
                this.renderStats();
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error al cargar ventas', 'error');
        }
    }

    renderStats() {
        const total = this.ventas.reduce((sum, v) => sum + parseFloat(v.total), 0);
        const count = this.ventas.length;
        const promedio = count > 0 ? total / count : 0;

        document.getElementById('totalVentas').textContent = count;
        document.getElementById('totalMonto').textContent = formatCurrency(total);
        document.getElementById('promedioVenta').textContent = formatCurrency(promedio);

        // Por método de pago
        const porMetodo = {};
        this.ventas.forEach(v => {
            porMetodo[v.metodo_pago] = (porMetodo[v.metodo_pago] || 0) + parseFloat(v.total);
        });

        let metodosHTML = '';
        if (Object.keys(porMetodo).length > 0) {
            for (const [metodo, total] of Object.entries(porMetodo)) {
                const iconos = {
                    efectivo: '<i class="fas fa-money-bill-wave"></i>',
                    tarjeta: '<i class="fas fa-credit-card"></i>',
                    tarjeta_debito: '<i class="fas fa-credit-card"></i>',
                    tarjeta_credito: '<i class="fas fa-credit-card"></i>',
                    transferencia: '<i class="fas fa-exchange-alt"></i>',
                    mercadopago: '<i class="fas fa-mobile-alt"></i>',
                    mixto: '<i class="fas fa-layer-group"></i>'
                };
                const nombres = {
                    efectivo: 'Efectivo',
                    tarjeta: 'Tarjeta',
                    tarjeta_debito: 'T. Débito',
                    tarjeta_credito: 'T. Crédito',
                    transferencia: 'Transfer.',
                    mercadopago: 'MP',
                    mixto: 'Mixto'
                };
                metodosHTML += `
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px;">
                        <span style="text-transform: capitalize;">
                            ${iconos[metodo] || ''} ${nombres[metodo] || metodo}:
                        </span>
                        <strong>${formatCurrency(total)}</strong>
                    </div>
                `;
            }
        } else {
            metodosHTML = '<span style="color: #999; font-size: 11px;">Sin datos</span>';
        }
        document.getElementById('ventasPorMetodo').innerHTML = metodosHTML;
    }

    renderVentas() {
        const tbody = document.getElementById('ventasTableBody');
        const conteoSpan = document.getElementById('conteoVentas');
        
        if (conteoSpan) {
            conteoSpan.textContent = `${this.ventas.length} venta${this.ventas.length !== 1 ? 's' : ''} encontrada${this.ventas.length !== 1 ? 's' : ''}`;
        }
        
        if (this.ventas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 50px; color: #999;">
                        <i class="fas fa-receipt" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3; display: block;"></i>
                        <p style="margin: 0; font-size: 14px;">No se encontraron ventas</p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #bbb;">Intenta ajustar los filtros</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        this.ventas.forEach(venta => {
            const fecha = new Date(venta.fecha_venta);
            const metodoBadge = this.getMetodoBadge(venta.metodo_pago);
            const subtotal = parseFloat(venta.total) + parseFloat(venta.descuento || 0);
            
            html += `
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="text-align: center; font-weight: 600; color: var(--primary); padding: 12px 10px;">#${venta.id}</td>
                    <td style="font-size: 13px; padding: 12px 10px;">
                        <div>${fecha.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })}</div>
                        <div style="color: #999; font-size: 11px;">${fecha.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}</div>
                    </td>
                    <td style="padding: 12px 10px;">${venta.usuario_nombre || 'N/A'}</td>
                    <td style="text-align: center; padding: 12px 10px;">
                        <span style="background: #e8f4fd; color: var(--primary); padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                            ${venta.items_count || 0} <i class="fas fa-box"></i>
                        </span>
                    </td>
                    <td style="text-align: right; color: #666; padding: 12px 10px;">${formatCurrency(subtotal)}</td>
                    <td style="text-align: right; color: #00C9A7; padding: 12px 10px;">
                        ${venta.descuento > 0 ? '-' + formatCurrency(venta.descuento) : formatCurrency(0)}
                    </td>
                    <td style="text-align: right; font-weight: 600; color: var(--primary); font-size: 15px; padding: 12px 10px;">${formatCurrency(venta.total)}</td>
                    <td style="text-align: center; padding: 12px 10px;">${metodoBadge}</td>
                    <td style="text-align: center; padding: 12px 10px;">
                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                            <button class="btn-icon" onclick="historialVentasModule.verDetalle(${venta.id})" 
                                    title="Ver detalle" style="background:var(--primary);color:white;padding:7px 10px;border-radius:8px;border:none;cursor:pointer;">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${venta.estado !== 'cancelada' ? `
                            <button class="btn-icon" onclick="historialVentasModule.editarVenta(${venta.id})"
                                    title="Editar" style="background:#f59e0b;color:white;padding:7px 10px;border-radius:8px;border:none;cursor:pointer;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="historialVentasModule.anularVenta(${venta.id})"
                                    title="Anular venta" style="background:#ef4444;color:white;padding:7px 10px;border-radius:8px;border:none;cursor:pointer;">
                                <i class="fas fa-ban"></i>
                            </button>
                            ` : `<span style="background:#fee2e2;color:#ef4444;padding:4px 10px;border-radius:10px;font-size:11px;font-weight:600;">Anulada</span>`}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    getMetodoBadge(metodo) {
        const badges = {
            'efectivo': '<span class="badge" style="background: #D1F2EB; color: #00C9A7; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-money-bill-wave"></i> Efectivo</span>',
            'tarjeta': '<span class="badge" style="background: #e8f4fd; color: var(--primary); padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-credit-card"></i> Tarjeta</span>',
            'tarjeta_debito': '<span class="badge" style="background: #e8f4fd; color: #3b82f6; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-credit-card"></i> Débito</span>',
            'tarjeta_credito': '<span class="badge" style="background: #f3e8ff; color: #8b5cf6; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-credit-card"></i> Crédito</span>',
            'transferencia': '<span class="badge" style="background: #FFF3CD; color: #FFC107; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-exchange-alt"></i> Transfer.</span>',
            'mercadopago': '<span class="badge" style="background: #cffafe; color: #06b6d4; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-mobile-alt"></i> MP</span>',
            'mixto': '<span class="badge" style="background: #E7E9FC; color: #6366F1; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-layer-group"></i> Mixto</span>'
        };
        return badges[metodo] || `<span class="badge" style="background: #f0f0f0; color: #666; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;">${metodo}</span>`;
    }

    async verDetalle(ventaId) {
        try {
            const response = await fetch(`../../api/ventas/index.php?id=${ventaId}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success && data.data) {
                this.mostrarModalDetalle(data.data);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error al cargar detalle', 'error');
        }
    }

    mostrarModalDetalle(venta) {
        const fecha = new Date(venta.fecha_venta);
        const metodoBadge = this.getMetodoBadge(venta.metodo_pago);
        const esAnulada = venta.estado === 'cancelada';
        
        let itemsHTML = '';
        venta.items.forEach(item => {
            itemsHTML += `
                <tr>
                    <td>${item.producto_nombre}</td>
                    <td>${item.cantidad}</td>
                    <td>${formatCurrency(item.precio_unitario)}</td>
                    <td style="font-weight: 600;">${formatCurrency(item.subtotal)}</td>
                </tr>
            `;
        });

        const modalContent = `
            <div class="modal-overlay" id="detalleModal" onclick="if(event.target === this) this.remove()">
                <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <h2><i class="fas fa-receipt"></i> Detalle de Venta #${venta.id}
                            ${esAnulada ? '<span style="background:#fee2e2;color:#ef4444;font-size:12px;padding:3px 10px;border-radius:10px;font-weight:600;margin-left:8px;">ANULADA</span>' : ''}
                        </h2>
                        <button class="modal-close" onclick="document.getElementById('detalleModal').remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; padding: 15px; background: var(--background); border-radius: 8px;">
                            <div>
                                <label style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; display: block;">Fecha y Hora</label>
                                <strong>${fecha.toLocaleDateString('es-MX')} ${fecha.toLocaleTimeString('es-MX')}</strong>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; display: block;">Usuario</label>
                                <strong>${venta.usuario_nombre}</strong>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; display: block;">Método de Pago</label>
                                ${metodoBadge}
                            </div>
                            <div>
                                <label style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; display: block;">Caja</label>
                                <strong>#${venta.caja_id}</strong>
                            </div>
                            ${venta.observaciones ? `
                            <div style="grid-column:1/-1;">
                                <label style="font-size: 12px; color: var(--text-secondary); margin-bottom: 5px; display: block;">Observaciones</label>
                                <span style="font-size:13px;">${venta.observaciones}</span>
                            </div>` : ''}
                        </div>

                        <h3 style="margin-bottom: 15px; font-size: 16px;">Productos</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHTML}
                            </tbody>
                        </table>

                        <div style="margin-top: 20px; padding: 15px; background: var(--background); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Subtotal:</span>
                                <strong>${formatCurrency(venta.subtotal)}</strong>
                            </div>
                            ${venta.descuento > 0 ? `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: var(--error);">
                                <span>Descuento:</span>
                                <strong>-${formatCurrency(venta.descuento)}</strong>
                            </div>
                            ` : ''}
                            <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid var(--border-color); font-size: 18px;">
                                <span style="font-weight: 600;">Total:</span>
                                <strong style="color: var(--primary);">${formatCurrency(venta.total)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalContent);
    }

    async editarVenta(ventaId) {
        try {
            const response = await fetch(`../../api/ventas/index.php?id=${ventaId}`, {
                credentials: 'include'
            });
            const data = await response.json();
            if (!data.success) { showAlert('Error al cargar la venta', 'error'); return; }
            const venta = data.data;

            const metodosOptions = ['efectivo','tarjeta','tarjeta_debito','tarjeta_credito','transferencia','mercadopago','mixto']
                .map(m => `<option value="${m}" ${venta.metodo_pago === m ? 'selected' : ''}>${m.replace('_',' ')}</option>`)
                .join('');

            const modal = `
                <div class="modal-overlay" id="editarVentaModal" onclick="if(event.target===this)this.remove()">
                    <div class="modal-content" style="max-width:480px;" onclick="event.stopPropagation()">
                        <div class="modal-header">
                            <h2><i class="fas fa-edit"></i> Editar Venta #${venta.id}</h2>
                            <button class="modal-close" onclick="document.getElementById('editarVentaModal').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body" style="padding:24px;">
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600;display:block;margin-bottom:6px;">Método de Pago</label>
                                <select id="editMetodoPago" class="form-select" style="padding:10px 14px;border-radius:8px;border:1px solid var(--border-color);width:100%;text-transform:capitalize;">
                                    ${metodosOptions}
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600;display:block;margin-bottom:6px;">Descuento ($)</label>
                                <input type="number" id="editDescuento" class="form-input" min="0" step="0.01"
                                       value="${venta.descuento || 0}"
                                       style="padding:10px 14px;border-radius:8px;border:1px solid var(--border-color);width:100%;">
                                <small style="color:var(--text-secondary);font-size:12px;">Subtotal: ${formatCurrency(venta.subtotal)}</small>
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600;display:block;margin-bottom:6px;">Observaciones</label>
                                <textarea id="editObservaciones" class="form-textarea"
                                          style="padding:10px 14px;border-radius:8px;border:1px solid var(--border-color);width:100%;min-height:80px;resize:vertical;"
                                          placeholder="Opcional...">${venta.observaciones || ''}</textarea>
                            </div>
                            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                                <button onclick="document.getElementById('editarVentaModal').remove()"
                                        style="padding:10px 20px;border-radius:8px;border:1px solid var(--border-color);background:transparent;cursor:pointer;">
                                    Cancelar
                                </button>
                                <button id="btnGuardarEdicion" onclick="historialVentasModule.guardarEdicionVenta(${venta.id})"
                                        style="padding:10px 20px;border-radius:8px;border:none;background:var(--primary);color:white;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modal);
        } catch (e) {
            console.error(e);
            showAlert('Error al cargar la venta', 'error');
        }
    }

    async guardarEdicionVenta(ventaId) {
        const btn = document.getElementById('btnGuardarEdicion');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...'; }

        const body = {
            id:            ventaId,
            metodo_pago:   document.getElementById('editMetodoPago')?.value,
            descuento:     parseFloat(document.getElementById('editDescuento')?.value || 0),
            observaciones: document.getElementById('editObservaciones')?.value || '',
        };

        try {
            const response = await fetch('../../api/ventas/index.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(body)
            });
            const data = await response.json();
            if (data.success) {
                showAlert('Venta actualizada correctamente', 'success');
                document.getElementById('editarVentaModal')?.remove();
                await this.loadVentas();
            } else {
                showAlert('Error: ' + data.message, 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar'; }
            }
        } catch (e) {
            showAlert('Error de conexión', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar'; }
        }
    }

    async anularVenta(ventaId) {
        const venta = this.ventas.find(v => v.id == ventaId);
        const nombreRef = venta ? `#${venta.id} — ${formatCurrency(venta.total)}` : `#${ventaId}`;

        const modal = `
            <div class="modal-overlay" id="anularVentaModal" onclick="if(event.target===this)this.remove()">
                <div class="modal-content" style="max-width:420px;" onclick="event.stopPropagation()">
                    <div class="modal-header" style="border-bottom:2px solid #fee2e2;">
                        <h2 style="color:#ef4444;"><i class="fas fa-ban"></i> Anular Venta ${nombreRef}</h2>
                        <button class="modal-close" onclick="document.getElementById('anularVentaModal').remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body" style="padding:24px;">
                        <p style="margin-bottom:16px;color:var(--text-secondary);font-size:14px;">
                            Esta acción <strong>anulará la venta</strong>, restaurará el stock de los productos y 
                            <strong>descontará el monto de la caja</strong>. No se puede deshacer.
                        </p>
                        <div class="form-group" style="margin-bottom:18px;">
                            <label style="font-weight:600;display:block;margin-bottom:6px;">Motivo (opcional)</label>
                            <input type="text" id="anularMotivo" class="form-input" placeholder="Ej: error en cobro, devolución..."
                                   style="padding:10px 14px;border-radius:8px;border:1px solid var(--border-color);width:100%;">
                        </div>
                        <div style="display:flex;gap:10px;justify-content:flex-end;">
                            <button onclick="document.getElementById('anularVentaModal').remove()"
                                    style="padding:10px 20px;border-radius:8px;border:1px solid var(--border-color);background:transparent;cursor:pointer;">
                                Cancelar
                            </button>
                            <button id="btnConfirmarAnular" onclick="historialVentasModule.confirmarAnulacion(${ventaId})"
                                    style="padding:10px 20px;border-radius:8px;border:none;background:#ef4444;color:white;font-weight:600;cursor:pointer;">
                                <i class="fas fa-ban"></i> Anular Venta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modal);
    }

    async confirmarAnulacion(ventaId) {
        const btn = document.getElementById('btnConfirmarAnular');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Anulando...'; }

        const motivo = document.getElementById('anularMotivo')?.value || '';

        try {
            const response = await fetch('../../api/ventas/index.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: ventaId, motivo })
            });
            const data = await response.json();
            if (data.success) {
                showAlert('Venta anulada. Stock restaurado y caja actualizada.', 'success');
                document.getElementById('anularVentaModal')?.remove();
                await this.loadVentas();
            } else {
                showAlert('Error: ' + data.message, 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-ban"></i> Anular Venta'; }
            }
        } catch (e) {
            showAlert('Error de conexión', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-ban"></i> Anular Venta'; }
        }
    }
}

// Variable global
let historialVentasModule;
