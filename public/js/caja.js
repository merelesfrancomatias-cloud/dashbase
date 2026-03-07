// ══════════════════════════════════════════
//  Módulo de Caja — DASH CRM
// ══════════════════════════════════════════

class CajaModule {
    constructor() {
        this.cajaActiva = null;
        this.historial  = [];
        this.init();
    }

    async init() {
        await this.checkCajaActiva();
        this.setupEventListeners();
        await this.loadHistorial(); // cargar historial automáticamente
    }

    /* ─────────────────── EVENT LISTENERS ─────────────────── */
    setupEventListeners() {
        document.getElementById('formAbrirCaja')?.addEventListener('submit', e => {
            e.preventDefault(); this.abrirCaja();
        });
        document.getElementById('formCerrarCaja')?.addEventListener('submit', e => {
            e.preventDefault(); this.cerrarCaja();
        });
        document.querySelectorAll('.btn-cancelar-modal, .modal-close').forEach(btn => {
            btn.addEventListener('click', () => this.closeAllModals());
        });
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) this.closeAllModals();
            });
        });
    }

    closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.classList.remove('show');
            m.classList.add('hidden');
        });
    }

    /* ─────────────────── CHECK CAJA ─────────────────── */
    async checkCajaActiva() {
        try {
            const res  = await fetch('../../api/caja/index.php?activa=true', { credentials:'include' });
            const data = await res.json();
            if (data.success && data.data) {
                this.cajaActiva = data.data;
                this.renderCajaActiva();
            } else {
                this.renderSinCaja();
            }
        } catch (err) {
            console.error('Error al verificar caja:', err);
            this.renderSinCaja();
        }
    }

    /* ─────────────────── RENDER CAJA ACTIVA ─────────────────── */
    renderCajaActiva() {
        const c   = this.cajaActiva;
        const now = new Date(c.fecha_apertura);
        const hora  = now.toLocaleString('es-AR', { hour:'2-digit', minute:'2-digit' });
        const fecha = now.toLocaleString('es-AR', { day:'numeric', month:'long' });

        const totalVentas = parseFloat(c.monto_ventas) || 0;
        const metodoColors = {
            efectivo:        { bg:'rgba(16,185,129,.12)', color:'#10b981', icon:'fa-money-bill-wave', label:'Efectivo'      },
            tarjeta_debito:  { bg:'rgba(59,130,246,.12)', color:'#3b82f6', icon:'fa-credit-card',     label:'Débito'         },
            tarjeta_credito: { bg:'rgba(139,92,246,.12)', color:'#8b5cf6', icon:'fa-credit-card',     label:'Crédito'        },
            transferencia:   { bg:'rgba(245,158,11,.12)', color:'#f59e0b', icon:'fa-exchange-alt',    label:'Transferencia'  },
            mercadopago:     { bg:'rgba(6,182,212,.12)',  color:'#06b6d4', icon:'fa-mobile-alt',      label:'MercadoPago'    },
            otro:            { bg:'rgba(107,114,128,.12)',color:'#6b7280', icon:'fa-question-circle', label:'Otro'           },
        };

        const totalTxs = c.detalle_pagos ? c.detalle_pagos.reduce((a,b) => a + parseInt(b.cantidad||0), 0) : 0;

        const metodosHTML = (c.detalle_pagos && c.detalle_pagos.length > 0)
            ? c.detalle_pagos.map(m => {
                const cfg = metodoColors[m.metodo_pago] || metodoColors.otro;
                const pct = totalVentas > 0 ? Math.round((m.total / totalVentas) * 100) : 0;
                return `
                <div class="metodo-item">
                    <div class="metodo-dot" style="background:${cfg.bg};color:${cfg.color};">
                        <i class="fas ${cfg.icon}"></i>
                    </div>
                    <div class="metodo-info">
                        <div class="metodo-name">${cfg.label}</div>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:${pct}%;background:${cfg.color};"></div>
                        </div>
                        <div class="metodo-count">${m.cantidad} venta${m.cantidad != 1 ? 's' : ''} · ${pct}%</div>
                    </div>
                    <div class="metodo-amount" style="color:${cfg.color};">${formatCurrency(m.total)}</div>
                </div>`;
            }).join('')
            : `<div style="text-align:center;color:var(--text-secondary);padding:30px 0;">
                   <i class="fas fa-inbox" style="display:block;font-size:28px;margin-bottom:8px;opacity:.3;"></i>
                   <p style="font-size:14px;margin:0;">Sin ventas registradas aún</p>
               </div>`;

        document.getElementById('cajaContainer').innerHTML = `

            <!-- HERO -->
            <div class="caja-hero">
                <div class="caja-hero-left">
                    <div class="caja-hero-icon open"><i class="fas fa-cash-register"></i></div>
                    <div class="caja-hero-info">
                        <h2>Caja Abierta</h2>
                        <p>Apertura: ${fecha} a las ${hora} · por <strong>${c.usuario_nombre} ${c.usuario_apellido}</strong></p>
                        <span class="caja-status-badge open"><span class="dot"></span> En operación</span>
                    </div>
                </div>
                <div class="caja-hero-right">
                    <button class="btn btn-danger" id="btnCerrarCaja" style="gap:8px;">
                        <i class="fas fa-lock"></i> Cerrar Caja
                    </button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-grid">
                <div class="kpi-card blue">
                    <div class="kpi-card-top">
                        <div>
                            <div class="kpi-label">Monto Inicial</div>
                            <div class="kpi-value">${formatCurrency(c.monto_inicial)}</div>
                        </div>
                        <div class="kpi-icon blue"><i class="fas fa-wallet"></i></div>
                    </div>
                    <div class="kpi-sub"><i class="fas fa-clock" style="margin-right:4px;"></i>Al abrir la caja</div>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-card-top">
                        <div>
                            <div class="kpi-label">Ventas del Día</div>
                            <div class="kpi-value">${formatCurrency(c.monto_ventas)}</div>
                        </div>
                        <div class="kpi-icon green"><i class="fas fa-arrow-up"></i></div>
                    </div>
                    <div class="kpi-sub"><i class="fas fa-receipt" style="margin-right:4px;"></i>${totalTxs} transaccion${totalTxs !== 1 ? 'es' : ''}</div>
                </div>
                <div class="kpi-card red">
                    <div class="kpi-card-top">
                        <div>
                            <div class="kpi-label">Gastos del Día</div>
                            <div class="kpi-value">${formatCurrency(c.monto_gastos)}</div>
                        </div>
                        <div class="kpi-icon red"><i class="fas fa-arrow-down"></i></div>
                    </div>
                    <div class="kpi-sub" style="color:#ef4444;">Salidas registradas</div>
                </div>
                <div class="kpi-card purple">
                    <div class="kpi-card-top">
                        <div>
                            <div class="kpi-label">Monto Esperado</div>
                            <div class="kpi-value">${formatCurrency(c.monto_esperado)}</div>
                        </div>
                        <div class="kpi-icon purple"><i class="fas fa-coins"></i></div>
                    </div>
                    <div class="kpi-sub"><i class="fas fa-calculator" style="margin-right:4px;"></i>Inicial + Ventas − Gastos</div>
                </div>
            </div>

            <!-- GRID DOS COLUMNAS -->
            <div class="caja-grid">

                <!-- Historial -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fas fa-history"></i> Historial de Cajas</div>
                        <button class="btn btn-secondary" id="btnVerHistorial" style="font-size:13px;padding:7px 16px;">
                            <i class="fas fa-list"></i> Cargar historial
                        </button>
                    </div>
                    <div class="panel-body" id="historialContainer">
                        <div style="text-align:center;padding:40px;color:var(--text-secondary);">
                            <i class="fas fa-database" style="display:block;font-size:28px;opacity:.3;margin-bottom:10px;"></i>
                            <p style="font-size:14px;margin:0;">Hacé clic para ver registros anteriores</p>
                        </div>
                    </div>
                </div>

                <!-- Métodos de pago -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-title"><i class="fas fa-chart-pie"></i> Métodos de Pago</div>
                        <span style="font-size:12px;color:var(--text-secondary);font-weight:500;">Hoy</span>
                    </div>
                    <div class="panel-body">${metodosHTML}</div>
                </div>

            </div>`;

        document.getElementById('btnCerrarCaja')?.addEventListener('click',  () => this.showModalCerrarCaja());
        document.getElementById('btnVerHistorial')?.addEventListener('click', () => this.loadHistorial());
    }

    /* ─────────────────── RENDER SIN CAJA ─────────────────── */
    renderSinCaja() {
        document.getElementById('cajaContainer').innerHTML = `

            <!-- HERO -->
            <div class="caja-hero">
                <div class="caja-hero-left">
                    <div class="caja-hero-icon closed"><i class="fas fa-cash-register"></i></div>
                    <div class="caja-hero-info">
                        <h2>Caja Cerrada</h2>
                        <p>No hay ninguna caja abierta en este momento</p>
                        <span class="caja-status-badge closed"><span class="dot"></span> Inactiva</span>
                    </div>
                </div>
            </div>

            <!-- ACCIÓN -->
            <div class="panel-card" style="margin-bottom:20px;">
                <div class="empty-caja">
                    <div class="empty-caja-icon"><i class="fas fa-lock-open"></i></div>
                    <h3>¿Listo para empezar?</h3>
                    <p>Abrí la caja para comenzar a registrar ventas y gastos del día</p>
                    <button class="btn btn-primary" id="btnAbrirCaja" style="gap:10px;padding:14px 32px;font-size:15px;">
                        <i class="fas fa-unlock"></i> Abrir Caja
                    </button>
                </div>
            </div>

            <!-- HISTORIAL -->
            <div class="panel-card">
                <div class="panel-header">
                    <div class="panel-title"><i class="fas fa-history"></i> Historial de Cajas</div>
                    <button class="btn btn-secondary" id="btnVerHistorial" style="font-size:13px;padding:7px 16px;">
                        <i class="fas fa-list"></i> Cargar historial
                    </button>
                </div>
                <div class="panel-body" id="historialContainer">
                    <div style="text-align:center;padding:40px;color:var(--text-secondary);">
                        <i class="fas fa-database" style="display:block;font-size:28px;opacity:.3;margin-bottom:10px;"></i>
                        <p style="font-size:14px;margin:0;">Hacé clic para ver registros anteriores</p>
                    </div>
                </div>
            </div>`;

        document.getElementById('btnAbrirCaja')?.addEventListener('click',   () => this.showModalAbrirCaja());
        document.getElementById('btnVerHistorial')?.addEventListener('click', () => this.loadHistorial());
    }

    /* ─────────────────── MODALES ─────────────────── */
    showModalAbrirCaja() {
        document.getElementById('monto_inicial').value = '';
        const modal = document.getElementById('modalAbrirCaja');
        modal.classList.remove('hidden');
        modal.classList.add('show');
        setTimeout(() => document.getElementById('monto_inicial').focus(), 100);
    }

    showModalCerrarCaja() {
        const c = this.cajaActiva;
        document.getElementById('cierreMontoInicial').textContent  = formatCurrency(c.monto_inicial);
        document.getElementById('cierreMontoVentas').textContent   = formatCurrency(c.monto_ventas);
        document.getElementById('cierreMontoGastos').textContent   = formatCurrency(c.monto_gastos);
        document.getElementById('cierreMontoEsperado').textContent = formatCurrency(c.monto_esperado);
        document.getElementById('observaciones_cierre').value = '';

        // Reset diff indicator
        const ind = document.getElementById('diffIndicator');
        ind.className = 'diff-indicator';
        ind.querySelector('i').className = 'fas fa-equals';
        document.getElementById('diffLabel').textContent        = 'Diferencia';
        document.getElementById('cierreDiferencia').textContent = '—';

        // Reemplazar input para limpiar listeners anteriores
        const inputReal = document.getElementById('monto_real');
        const newInput  = inputReal.cloneNode(true);
        newInput.value  = '';
        inputReal.parentNode.replaceChild(newInput, inputReal);

        newInput.addEventListener('input', e => {
            const real = parseFloat(e.target.value) || 0;
            const diff = real - parseFloat(c.monto_esperado);
            const ind  = document.getElementById('diffIndicator');
            ind.className = `diff-indicator${diff > 0 ? ' positive' : diff < 0 ? ' negative' : ''}`;
            ind.querySelector('i').className = `fas fa-${diff > 0 ? 'arrow-up' : diff < 0 ? 'arrow-down' : 'equals'}`;
            document.getElementById('diffLabel').textContent        = diff > 0 ? '¡Sobrante!' : diff < 0 ? 'Faltante' : 'Sin diferencia';
            document.getElementById('cierreDiferencia').textContent = diff !== 0 ? formatCurrency(Math.abs(diff)) : '✓ Cuadra exacto';
        });

        // Botón "Usar monto esperado"
        document.getElementById('btnUsarEsperado')?.addEventListener('click', () => {
            const esperado = parseFloat(c.monto_esperado) || 0;
            newInput.value = esperado.toFixed(2);
            newInput.dispatchEvent(new Event('input'));
            newInput.focus();
        });

        const modal = document.getElementById('modalCerrarCaja');
        modal.classList.remove('hidden');
        modal.classList.add('show');
        setTimeout(() => newInput.focus(), 100);
    }

    /* ─────────────────── ABRIR CAJA ─────────────────── */
    async abrirCaja() {
        const monto = parseFloat(document.getElementById('monto_inicial').value);
        if (isNaN(monto) || monto < 0) { showAlert('Ingresá un monto válido', 'error'); return; }

        const btn = document.getElementById('btnGuardarAbrirCaja');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<div class="spin" style="width:18px;height:18px;border-width:2px;display:inline-block;"></div> Abriendo...';

        try {
            const res  = await fetch('../../api/caja/index.php', {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type':'application/json' },
                body: JSON.stringify({ monto_inicial: monto })
            });
            const data = await res.json();
            if (data.success) {
                showAlert('¡Caja abierta exitosamente! 🎉', 'success');
                this.closeAllModals();
                await this.checkCajaActiva();
            } else {
                showAlert(data.message || 'Error al abrir la caja', 'error');
            }
        } catch { showAlert('Error de conexión', 'error'); }
        finally   { btn.disabled = false; btn.innerHTML = orig; }
    }

    /* ─────────────────── CERRAR CAJA ─────────────────── */
    async cerrarCaja() {
        const montoReal     = parseFloat(document.getElementById('monto_real').value);
        const observaciones = document.getElementById('observaciones_cierre').value.trim();

        if (isNaN(montoReal) || montoReal < 0) { showAlert('Ingresá un monto válido', 'error'); return; }
        if (!confirm('¿Confirmar el cierre de caja? Esta acción no se puede deshacer.')) return;

        const btn  = document.getElementById('btnGuardarCerrarCaja');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<div class="spin" style="width:18px;height:18px;border-width:2px;display:inline-block;"></div> Cerrando...';

        try {
            const res  = await fetch('../../api/caja/index.php', {
                method: 'PUT', credentials: 'include',
                headers: { 'Content-Type':'application/json' },
                body: JSON.stringify({ caja_id: this.cajaActiva.id, monto_real: montoReal, observaciones })
            });
            const data = await res.json();
            if (data.success) {
                showAlert('Caja cerrada correctamente', 'success');
                this.closeAllModals();
                this.cajaActiva = null;
                await this.checkCajaActiva();
                await this.loadHistorial(); // refrescar historial tras el cierre
            } else {
                showAlert(data.message || 'Error al cerrar la caja', 'error');
            }
        } catch { showAlert('Error de conexión', 'error'); }
        finally   { btn.disabled = false; btn.innerHTML = orig; }
    }

    /* ─────────────────── HISTORIAL ─────────────────── */
    async loadHistorial() {
        const container = document.getElementById('historialContainer');
        if (!container) return;
        container.innerHTML = `<div style="text-align:center;padding:40px;"><div class="spin" style="margin:0 auto;"></div></div>`;

        try {
            const res  = await fetch('../../api/caja/index.php?historial=true&limit=10', { credentials:'include' });
            const data = await res.json();
            if (data.success) {
                this.historial = data.data;
                this.renderHistorial();
            } else {
                container.innerHTML = `<p style="text-align:center;color:var(--text-secondary);padding:20px;">No se pudo cargar el historial</p>`;
            }
        } catch {
            container.innerHTML = `<p style="text-align:center;color:var(--text-secondary);padding:20px;">Error de conexión</p>`;
        }
    }

    renderHistorial() {
        const container = document.getElementById('historialContainer');
        if (!container) return;

        if (!this.historial || this.historial.length === 0) {
            container.innerHTML = `
                <div style="text-align:center;padding:40px;color:var(--text-secondary);">
                    <i class="fas fa-inbox" style="font-size:32px;opacity:.3;margin-bottom:12px;display:block;"></i>
                    No hay historial de cajas registrado
                </div>`;
            return;
        }

        const fmtDate = d => d
            ? new Date(d).toLocaleString('es-AR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' })
            : '—';

        const rows = this.historial.map(caja => {
            const initials   = `${caja.usuario_nombre?.[0]||''}${caja.usuario_apellido?.[0]||''}`.toUpperCase();
            const estadoBadge = caja.estado === 'abierta'
                ? `<span style="background:rgba(16,185,129,.12);color:#10b981;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;display:inline-block;">● Abierta</span>`
                : `<span style="background:rgba(107,114,128,.1);color:var(--text-secondary);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;display:inline-block;">Cerrada</span>`;
            const diferencia  = caja.diferencia != null
                ? `<span style="color:${parseFloat(caja.diferencia)>=0?'#10b981':'#ef4444'};font-weight:600;">${parseFloat(caja.diferencia)>=0?'+':''}${formatCurrency(caja.diferencia)}</span>`
                : `<span style="color:var(--text-secondary);">—</span>`;

            return `
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-sm">${initials}</div>
                            <span>${caja.usuario_nombre} ${caja.usuario_apellido}</span>
                        </div>
                    </td>
                    <td style="color:var(--text-secondary);font-size:12px;">${fmtDate(caja.fecha_apertura)}</td>
                    <td style="color:var(--text-secondary);font-size:12px;">${fmtDate(caja.fecha_cierre)}</td>
                    <td>${formatCurrency(caja.monto_inicial)}</td>
                    <td>${formatCurrency(caja.monto_ventas||0)}</td>
                    <td style="color:#ef4444;font-weight:600;">${formatCurrency(caja.monto_gastos||0)}</td>
                    <td>${caja.monto_final != null ? formatCurrency(caja.monto_final) : '<span style="color:var(--text-secondary)">—</span>'}</td>
                    <td>${diferencia}</td>
                    <td>${estadoBadge}</td>
                </tr>`;
        }).join('');

        container.innerHTML = `
            <div class="table-container">
                <table class="hist-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Inicial</th>
                            <th>Ventas</th>
                            <th>Gastos</th>
                            <th>Final</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    }
}
