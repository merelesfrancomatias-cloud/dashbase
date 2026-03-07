// Módulo de Gestión de Gastos
class GastosModule {
    constructor() {
        this.gastos = [];
        this.editingId = null;
        this.cajaActivaId = null;
        this.base = window.APP_BASE || '../..';
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadCajaActiva();
        await this.loadGastos();
        this.setFechaActual();
    }

    async loadCajaActiva() {
        try {
            const res  = await fetch(`${this.base}/api/caja/index.php?activa=true`, { credentials: 'include' });
            const data = await res.json();
            if (data.success && data.data?.id) {
                this.cajaActivaId = data.data.id;
            }
        } catch (e) {
            // Sin caja activa — gastos sin vincular a caja
        }
    }

    setupEventListeners() {
        document.getElementById('fechaInicio')?.addEventListener('change', () => this.aplicarFiltros());
        document.getElementById('fechaFin')?.addEventListener('change', () => this.aplicarFiltros());
        document.getElementById('metodoPago')?.addEventListener('change', () => this.aplicarFiltros());
        document.getElementById('categoriaFiltro')?.addEventListener('change', () => this.aplicarFiltros());
    }

    setFechaActual() {
        const today = new Date().toISOString().split('T')[0];
        const el = document.getElementById('fecha');
        if (el) el.value = today;
    }

    aplicarFiltros() {
        this.loadGastos();
    }

    async loadGastos() {
        try {
            const fechaInicio = document.getElementById('fechaInicio')?.value || '';
            const fechaFin    = document.getElementById('fechaFin')?.value    || '';
            const metodoPago  = document.getElementById('metodoPago')?.value  || '';
            const categoria   = document.getElementById('categoriaFiltro')?.value || '';

            let url = `${this.base}/api/gastos/index.php?`;
            if (fechaInicio) url += `fecha_inicio=${fechaInicio}&`;
            if (fechaFin)    url += `fecha_fin=${fechaFin}&`;
            if (metodoPago)  url += `metodo_pago=${metodoPago}&`;
            if (categoria)   url += `categoria=${categoria}&`;

            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                this.gastos = data.data;
                this.renderGastos();
                this.renderStats();
            } else {
                this.showAlert(data.message || 'Error al cargar gastos', 'error');
            }
        } catch (error) {
            console.error('Error loadGastos:', error);
            this.showAlert('Error al cargar gastos', 'error');
        }
    }

    renderStats() {
        const total  = this.gastos.reduce((s, g) => s + parseFloat(g.monto), 0);
        const count  = this.gastos.length;
        const promedio = count > 0 ? total / count : 0;

        const now = new Date();
        const gastosMes = this.gastos
            .filter(g => {
                const f = new Date(g.fecha_gasto + 'T00:00:00');
                return f.getMonth() === now.getMonth() && f.getFullYear() === now.getFullYear();
            })
            .reduce((s, g) => s + parseFloat(g.monto), 0);

        document.getElementById('totalGastos').textContent  = count;
        document.getElementById('totalMonto').textContent   = this.formatCurrency(total);
        document.getElementById('promedioGasto').textContent = this.formatCurrency(promedio);
        document.getElementById('gastosMes').textContent    = this.formatCurrency(gastosMes);
    }

    renderGastos() {
        const tbody      = document.getElementById('gastosTableBody');
        const conteoSpan = document.getElementById('conteoGastos');

        if (conteoSpan) {
            const n = this.gastos.length;
            conteoSpan.textContent = `${n} gasto${n !== 1 ? 's' : ''} registrado${n !== 1 ? 's' : ''}`;
        }

        if (this.gastos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center;padding:50px;color:#999;">
                        <i class="fas fa-money-bill-wave" style="font-size:48px;margin-bottom:15px;opacity:.3;display:block;"></i>
                        <p style="margin:0;font-size:14px;">No hay gastos registrados</p>
                        <p style="margin:5px 0 0;font-size:12px;color:#bbb;">Comienza registrando tu primer gasto</p>
                    </td>
                </tr>`;
            return;
        }

        const categoriaLabels = {
            compra_mercaderia: 'Compra Mercadería',
            servicios:  'Servicios',
            salarios:   'Salarios',
            alquiler:   'Alquiler',
            impuestos:  'Impuestos',
            otros:      'Otros'
        };

        let html = '';
        this.gastos.forEach(gasto => {
            const fecha         = new Date(gasto.fecha_gasto + 'T00:00:00');
            const metodoBadge   = this.getMetodoBadge(gasto.metodo_pago);
            const categoriaLabel = categoriaLabels[gasto.categoria] || gasto.categoria || 'Otros';
            const desc = gasto.descripcion || '';

            html += `
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="text-align:center;font-weight:600;color:var(--primary);padding:12px 10px;">#${gasto.id}</td>
                    <td style="font-size:13px;padding:12px 10px;">
                        ${fecha.toLocaleDateString('es-AR', {day:'2-digit',month:'short',year:'numeric'})}
                    </td>
                    <td style="font-weight:500;padding:12px 10px;">${this.esc(desc.length > 60 ? desc.substring(0,60)+'…' : desc) || '-'}</td>
                    <td style="padding:12px 10px;">
                        <span style="background:#e8f4fd;color:var(--primary);padding:4px 10px;border-radius:12px;font-size:11px;font-weight:500;">
                            <i class="fas fa-tag"></i> ${categoriaLabel}
                        </span>
                    </td>
                    <td style="text-align:right;font-weight:600;color:#FF4444;font-size:15px;padding:12px 10px;">
                        ${this.formatCurrency(gasto.monto)}
                    </td>
                    <td style="text-align:center;padding:12px 10px;">${metodoBadge}</td>
                    <td style="text-align:center;padding:12px 10px;font-size:12px;color:#888;">${this.esc(gasto.comprobante || '-')}</td>
                    <td style="text-align:center;padding:12px 10px;">
                        <button class="btn-icon" onclick="gastosModule.editGasto(${gasto.id})"
                                title="Editar" style="background:var(--primary);color:white;padding:6px 10px;border-radius:6px;margin-right:5px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" onclick="gastosModule.deleteGasto(${gasto.id})"
                                title="Eliminar" style="background:#FF4444;color:white;padding:6px 10px;border-radius:6px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
    }

    getMetodoBadge(metodo) {
        const badges = {
            efectivo:      '<span class="badge" style="background:#D1F2EB;color:#00C9A7;padding:5px 12px;border-radius:12px;font-size:11px;font-weight:600;"><i class="fas fa-money-bill-wave"></i> Efectivo</span>',
            tarjeta:       '<span class="badge" style="background:#e8f4fd;color:var(--primary);padding:5px 12px;border-radius:12px;font-size:11px;font-weight:600;"><i class="fas fa-credit-card"></i> Tarjeta</span>',
            transferencia: '<span class="badge" style="background:#FFF3CD;color:#FFC107;padding:5px 12px;border-radius:12px;font-size:11px;font-weight:600;"><i class="fas fa-exchange-alt"></i> Transfer.</span>'
        };
        return badges[metodo] || `<span class="badge" style="background:#f0f0f0;color:#666;padding:5px 12px;border-radius:12px;font-size:11px;font-weight:600;">${this.esc(metodo || '-')}</span>`;
    }

    openModal() {
        this.editingId = null;
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-money-bill-wave"></i> Registrar Gasto';
        document.getElementById('gastoForm').reset();
        this.setFechaActual();
        document.getElementById('gastoModal').classList.add('show');
    }

    closeModal() {
        document.getElementById('gastoModal').classList.remove('show');
        this.editingId = null;
    }

    async editGasto(id) {
        try {
            const response = await fetch(`${this.base}/api/gastos/index.php?id=${id}`, { credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                const g = data.data;
                this.editingId = id;

                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Gasto';
                document.getElementById('gastoId').value        = g.id;
                document.getElementById('descripcion').value    = g.descripcion || '';
                document.getElementById('monto').value          = g.monto;
                document.getElementById('fecha').value          = (g.fecha_gasto || '').substring(0, 10);
                document.getElementById('categoriaId').value    = g.categoria || 'otros';
                document.getElementById('metodoPagoForm').value = g.metodo_pago || '';
                document.getElementById('comprobante').value    = g.comprobante || '';

                document.getElementById('gastoModal').classList.add('show');
            } else {
                this.showAlert(data.message || 'Error al cargar el gasto', 'error');
            }
        } catch (error) {
            console.error('Error editGasto:', error);
            this.showAlert('Error al cargar el gasto', 'error');
        }
    }

    async saveGasto() {
        const descripcion = document.getElementById('descripcion').value.trim();
        const monto       = document.getElementById('monto').value;
        const fecha       = document.getElementById('fecha').value;
        const metodoPago  = document.getElementById('metodoPagoForm').value;

        if (!descripcion || !monto || !fecha || !metodoPago) {
            this.showAlert('Por favor completa todos los campos obligatorios', 'error');
            return;
        }

        const gastoData = {
            descripcion,
            monto:       parseFloat(monto),
            fecha_gasto: fecha,
            categoria:   document.getElementById('categoriaId').value || 'otros',
            metodo_pago: metodoPago,
            comprobante: document.getElementById('comprobante').value.trim() || null,
            caja_id:     this.cajaActivaId || null
        };

        const method = this.editingId ? 'PUT' : 'POST';
        if (this.editingId) gastoData.id = this.editingId;

        try {
            const response = await fetch(`${this.base}/api/gastos/index.php`, {
                method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(gastoData)
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(this.editingId ? 'Gasto actualizado correctamente' : 'Gasto registrado correctamente', 'success');
                this.closeModal();
                await this.loadGastos();
            } else {
                this.showAlert(data.message || 'Error al guardar el gasto', 'error');
            }
        } catch (error) {
            console.error('Error saveGasto:', error);
            this.showAlert('Error al guardar el gasto', 'error');
        }
    }

    async deleteGasto(id) {
        if (!confirm('¿Estás seguro de que deseas eliminar este gasto?')) return;

        try {
            const response = await fetch(`${this.base}/api/gastos/index.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Gasto eliminado correctamente', 'success');
                await this.loadGastos();
            } else {
                this.showAlert(data.message || 'Error al eliminar el gasto', 'error');
            }
        } catch (error) {
            console.error('Error deleteGasto:', error);
            this.showAlert('Error al eliminar el gasto', 'error');
        }
    }

    esc(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value);
    }

    showAlert(message, type = 'info') {
        const colors = { success: '#22c55e', error: '#ef4444', info: '#3b82f6' };
        const icons  = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };

        const el = document.createElement('div');
        el.style.cssText = `position:fixed;top:80px;right:20px;z-index:10000;min-width:300px;
            background:${colors[type]||colors.info};color:white;padding:14px 20px;
            border-radius:10px;font-size:14px;font-weight:500;
            box-shadow:0 4px 20px rgba(0,0,0,0.15);
            display:flex;align-items:center;gap:10px;
            animation:slideIn .3s ease;`;
        el.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i> ${message}`;
        document.body.appendChild(el);
        setTimeout(() => { el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(() => el.remove(), 300); }, 3000);
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('gastoModal');
    if (event.target === modal) modal.classList.remove('show');
};
