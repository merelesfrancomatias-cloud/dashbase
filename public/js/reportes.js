/**
 * ReportesModule — Dashboard de Reportes Premium
 * Chart.js 4.4.0
 */
class ReportesModule {
    constructor() {
        this.base = window.APP_BASE || '../..';
        this.charts = {};
        this.data   = null;

        this._bindEvents();
        this.cargarDatos();
    }

    /* ─── EVENTS ────────────────────────────────── */
    _bindEvents() {
        document.getElementById('filtroPeriodo')?.addEventListener('change', () => this.cambiarPeriodo());
        document.getElementById('btnActualizar')?.addEventListener('click',  () => this.aplicarFiltros());
    }

    /* ─── PERÍODO ───────────────────────────────── */
    cambiarPeriodo() {
        const val  = document.getElementById('filtroPeriodo')?.value;
        const show = val === 'personalizado';
        document.getElementById('wrapFechaDesde')?.style && (document.getElementById('wrapFechaDesde').style.display = show ? 'flex' : 'none');
        document.getElementById('wrapFechaHasta')?.style && (document.getElementById('wrapFechaHasta').style.display = show ? 'flex' : 'none');
        if (!show) this.cargarDatos();
    }

    aplicarFiltros() {
        this.cargarDatos();
    }

    _getPeriodo() {
        const p = document.getElementById('filtroPeriodo')?.value || 'mes';
        if (p === 'personalizado') {
            const desde = document.getElementById('fechaDesde')?.value;
            const hasta = document.getElementById('fechaHasta')?.value;
            if (desde && hasta) return `personalizado&fecha_inicio=${desde}&fecha_fin=${hasta}`;
        }
        return p;
    }

    _labelPeriodo(p) {
        const map = {
            hoy: 'Hoy', ayer: 'Ayer', semana: 'Esta Semana',
            mes: 'Este Mes', mes_anterior: 'Mes Anterior',
            trimestre: 'Este Trimestre', anio: 'Este Año', personalizado: 'Personalizado'
        };
        return map[p] || p;
    }

    /* ─── LOAD DATA ─────────────────────────────── */
    async cargarDatos() {
        const periodo = this._getPeriodo();
        const labelP  = this._labelPeriodo(periodo.split('&')[0]);

        // Update hero badge
        const heroTxt = document.getElementById('heroPeriodoText');
        if (heroTxt) heroTxt.textContent = labelP;

        // Update btn loading state
        const btn = document.getElementById('btnActualizar');
        if (btn) btn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Cargando...';

        try {
            const [resRep, resTop] = await Promise.all([
                fetch(`${this.base}/api/reportes/index.php?periodo=${periodo}`).then(r => r.json()),
                fetch(`${this.base}/api/ventas/index.php?top_productos=1&periodo=${periodo}`).then(r => r.json()).catch(() => ({ success: false }))
            ]);

            if (resRep.success) {
                this.data = resRep.data;
                this._renderKPIs(resRep.data.metricas);
                this._renderChartVentas(resRep.data.ventas_por_dia || []);
                this._renderChartMetodos(resRep.data.ventas_por_metodo || []);
                this._renderChartCategorias(resRep.data.ventas_por_categoria || []);
            } else {
                // Sin datos (plan, sin ventas, etc) → mostrar ceros, no "Error"
                this._renderKPIs({ total_ventas:0, ganancias_netas:0, tickets_vendidos:0, ticket_promedio:0 });
                this._renderChartVentas([]);
                this._renderChartMetodos([]);
                this._renderChartCategorias([]);
                if (!resRep.success && resRep.message) {
                    console.warn('Reportes API:', resRep.message);
                }
            }

            // Top productos — response is direct array (no .productos wrapper)
            const topData = resTop.success ? (resTop.data || []) : [];
            this._renderTopProductos(Array.isArray(topData) ? topData : []);

            // Timestamp
            const lu = document.getElementById('lastUpdated');
            if (lu) lu.textContent = 'Actualizado: ' + new Date().toLocaleTimeString('es-AR', {hour:'2-digit', minute:'2-digit'});

        } catch (err) {
            console.error('Error cargando reportes:', err);
            this._renderKPIs({ total_ventas:0, ganancias_netas:0, tickets_vendidos:0, ticket_promedio:0 });
            this._renderChartVentas([]);
            this._renderChartMetodos([]);
            this._renderChartCategorias([]);
            document.getElementById('topProductosContainer').innerHTML =
                '<div class="rep-empty"><i class="fas fa-box-open"></i><p>Sin ventas registradas aún</p></div>';
        }

        if (btn) btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar';
    }

    /* ─── KPIs ──────────────────────────────────── */
    _fmt(n) {
        if (n === null || n === undefined) return '$0';
        const num = parseFloat(n);
        if (isNaN(num)) return '$0';
        if (num >= 1000000) return '$' + (num/1000000).toFixed(1) + 'M';
        if (num >= 1000)    return '$' + (num/1000).toFixed(1) + 'k';
        return '$' + num.toLocaleString('es-AR', {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    _trend(pct, el) {
        if (!el) return;
        const v = parseFloat(pct);
        if (pct === null || pct === undefined || isNaN(v) || v === 0) {
            el.innerHTML = '<span class="trend-flat"><i class="fas fa-minus"></i> Sin datos previos</span>';
            return;
        }
        if (v > 0)  el.innerHTML = `<span class="trend-up"><i class="fas fa-arrow-up"></i> +${v.toFixed(1)}% vs período anterior</span>`;
        else        el.innerHTML = `<span class="trend-down"><i class="fas fa-arrow-down"></i> ${v.toFixed(1)}% vs período anterior</span>`;
    }

    _renderKPIs(m) {
        if (!m) return;
        document.getElementById('kpiTotalVentas').textContent = this._fmt(m.total_ventas ?? 0);
        document.getElementById('kpiGanancia').textContent    = this._fmt(m.ganancias_netas ?? 0);
        document.getElementById('kpiTickets').textContent     = parseInt(m.tickets_vendidos || 0).toLocaleString('es-AR');
        document.getElementById('kpiPromedio').textContent    = this._fmt(m.ticket_promedio ?? 0);

        this._trend(m.trend_ventas,    document.getElementById('kpiTrendVentas'));
        this._trend(m.trend_ganancias, document.getElementById('kpiTrendGanancia'));
        this._trend(m.trend_tickets,   document.getElementById('kpiTrendTickets'));
        this._trend(m.trend_promedio,  document.getElementById('kpiTrendPromedio'));
    }

    _showKPIError() {
        ['kpiTotalVentas','kpiGanancia','kpiTickets','kpiPromedio'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = 'Error';
        });
    }

    /* ─── CHART: VENTAS POR DÍA (ÁREA) ──────────── */
    _renderChartVentas(rows) {
        const ctx = document.getElementById('chartVentas');
        if (!ctx) return;
        if (this.charts.ventas) { this.charts.ventas.destroy(); }

        const label = document.getElementById('labelEvolucion');

        if (!rows.length) {
            if (label) label.textContent = '0 días';
            // Mostrar gráfico vacío bonito con un solo punto en 0
            rows = [{ fecha: 'Hoy', total: 0 }];
        }

        const labels = rows.map(r => r.fecha);
        const vals   = rows.map(r => parseFloat(r.total || 0));
        if (label) label.textContent = rows.length + (rows.length === 1 ? ' día' : ' días');

        const grad = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(59,130,246,.35)');
        grad.addColorStop(1, 'rgba(59,130,246,.01)');

        this.charts.ventas = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Ventas',
                    data: vals,
                    fill: true,
                    backgroundColor: grad,
                    borderColor: '#3b82f6',
                    borderWidth: 2.5,
                    tension: 0.45,
                    pointRadius: vals.length > 20 ? 0 : 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        padding: 12,
                        callbacks: {
                            label: ctx => ' ' + this._fmt(ctx.raw)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 }, maxTicksLimit: 8 }
                    },
                    y: {
                        grid: { color: 'rgba(148,163,184,.1)' },
                        ticks: {
                            color: '#94a3b8', font: { size: 11 },
                            callback: v => this._fmt(v)
                        }
                    }
                }
            }
        });
    }

    /* ─── CHART: MÉTODOS DE PAGO (DOUGHNUT) ─────── */
    _renderChartMetodos(rows) {
        const ctx = document.getElementById('chartMetodosPago');
        if (!ctx) return;
        if (this.charts.metodos) { this.charts.metodos.destroy(); }

        if (!rows.length) {
            // Mostrar doughnut vacío gris con label
            this.charts.metodos = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sin ventas'],
                    datasets: [{ data: [1], backgroundColor: ['#e2e8f0'], borderWidth: 0 }]
                },
                options: {
                    responsive: true, cutout: '72%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
            const leg = document.getElementById('metodosLegend');
            if (leg) leg.innerHTML = '<div class="rep-empty" style="padding:10px 0;"><i class="fas fa-info-circle"></i> Sin ventas en este período</div>';
            return;
        }

        const COLORS = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899'];
        const labels = rows.map(r => r.metodo || 'Otro');
        const vals   = rows.map(r => parseFloat(r.total || 0));
        const total  = vals.reduce((a,b) => a+b, 0);

        this.charts.metodos = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: vals,
                    backgroundColor: COLORS.slice(0, labels.length),
                    borderWidth: 3,
                    borderColor: 'var(--surface)',
                    hoverBorderWidth: 4,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        padding: 12,
                        callbacks: {
                            label: ctx => ` ${this._fmt(ctx.raw)}  (${total ? (ctx.raw/total*100).toFixed(1) : 0}%)`
                        }
                    }
                }
            }
        });

        // Leyenda personalizada
        const leg = document.getElementById('metodosLegend');
        if (leg) {
            leg.innerHTML = labels.map((l, i) => `
                <div class="metodo-row">
                    <div class="metodo-left">
                        <div class="metodo-dot" style="background:${COLORS[i]};"></div>
                        <span>${l}</span>
                    </div>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <span class="metodo-pct">${total ? (vals[i]/total*100).toFixed(1) : 0}%</span>
                        <span style="font-weight:700;">${this._fmt(vals[i])}</span>
                    </div>
                </div>
            `).join('');
        }
    }

    /* ─── CHART: CATEGORÍAS (BAR HORIZONTAL) ─────── */
    _renderChartCategorias(rows) {
        const ctx = document.getElementById('chartCategorias');
        if (!ctx) return;
        if (this.charts.categorias) { this.charts.categorias.destroy(); }

        if (!rows.length) {
            // Mostrar barra vacía con placeholder
            this.charts.categorias = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sin datos'],
                    datasets: [{ label: '', data: [0], backgroundColor: ['#e2e8f0'], borderRadius: 8, borderSkipped: false }]
                },
                options: {
                    indexAxis: 'y', responsive: true,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: {
                        x: { grid: { color: 'rgba(148,163,184,.1)' }, ticks: { color: '#94a3b8' } },
                        y: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                    }
                }
            });
            return;
        }

        const sorted = [...rows].sort((a,b) => parseFloat(b.total||0) - parseFloat(a.total||0)).slice(0,8);
        const COLORS = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899','#84cc16'];

        this.charts.categorias = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sorted.map(r => r.categoria || 'Sin categoría'),
                datasets: [{
                    label: 'Ventas',
                    data: sorted.map(r => parseFloat(r.total || 0)),
                    backgroundColor: COLORS.slice(0, sorted.length),
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        padding: 12,
                        callbacks: {
                            label: ctx => ' ' + this._fmt(ctx.raw)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(148,163,184,.1)' },
                        ticks: { color: '#94a3b8', font: { size: 11 }, callback: v => this._fmt(v) }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 12 } }
                    }
                }
            }
        });
    }

    /* ─── TOP PRODUCTOS TABLE ────────────────────── */
    _renderTopProductos(productos) {
        const container = document.getElementById('topProductosContainer');
        if (!container) return;

        if (!productos.length) {
            container.innerHTML = '<div class="rep-empty"><i class="fas fa-box-open"></i>Sin datos de productos</div>';
            return;
        }

        const top = productos.slice(0, 8);
        const maxQty = Math.max(...top.map(p => parseInt(p.total_vendido || p.cantidad_vendida || 1)));

        const rankClass = i => ['rank-1','rank-2','rank-3'][i] || 'rank-n';
        const rankLabel = i => ['1°','2°','3°'][i] || (i+1)+'°';

        const imgHTML = p => {
            const src = p.foto ? `${this.base}/public/uploads/productos/${p.foto}` : null;
            if (src) return `<img src="${src}" class="prod-thumb" onerror="this.parentElement.innerHTML='<div class=\\"prod-thumb-placeholder\\"><i class=\\"fas fa-image\\"></i></div>'">`;
            return `<div class="prod-thumb-placeholder"><i class="fas fa-box"></i></div>`;
        };

        const rows = top.map((p, i) => {
            const qty   = parseInt(p.total_vendido || p.cantidad_vendida || 0);
            const pct   = maxQty > 0 ? (qty / maxQty * 100).toFixed(1) : 0;
            const total = parseFloat(p.precio_venta || 0) * qty;
            return `
                <tr>
                    <td><span class="rank-badge ${rankClass(i)}">${rankLabel(i)}</span></td>
                    <td>${imgHTML(p)}</td>
                    <td style="max-width:160px;">
                        <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${p.nombre || 'Producto'}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">${p.unidad_medida || ''}</div>
                    </td>
                    <td>
                        <div class="bar-row">
                            <span style="min-width:32px;font-weight:700;">${qty}</span>
                            <div class="bar-wrap"><div class="bar-fill" style="width:${pct}%"></div></div>
                        </div>
                    </td>
                    <td style="text-align:right;font-weight:700;color:var(--primary);">${this._fmt(total)}</td>
                </tr>
            `;
        }).join('');

        container.innerHTML = `
            <div style="overflow-x:auto;">
                <table class="top-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Producto</th>
                            <th>Unidades</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    /* ─── ACCESOS RÁPIDOS ────────────────────────── */
    verReporte(tipo) {
        const mensajes = {
            ventas:       'Abriendo reporte de ventas detallado...',
            gastos:       'Abriendo reporte de gastos...',
            rentabilidad: 'Abriendo análisis de rentabilidad...',
            inventario:   'Abriendo reporte de inventario...',
            caja:         'Abriendo historial de caja...',
        };
        // Mostrar toast o navegar
        const msg = mensajes[tipo] || 'Abriendo reporte...';
        this._toast(msg, 'info');
    }

    /* ─── EXPORT PDF ─────────────────────────────── */
    exportarPDF() {
        this._toast('Preparando PDF...', 'info');
        setTimeout(() => window.print(), 400);
    }

    /* ─── TOAST ──────────────────────────────────── */
    _toast(msg, type = 'info') {
        const existing = document.getElementById('repToast');
        if (existing) existing.remove();

        const colors = { info: '#3b82f6', success: '#10b981', error: '#ef4444' };
        const t = document.createElement('div');
        t.id = 'repToast';
        t.style.cssText = `
            position:fixed; bottom:24px; right:24px; z-index:9999;
            background:${colors[type]||colors.info}; color:#fff;
            padding:12px 20px; border-radius:12px; font-size:13px; font-weight:600;
            box-shadow:0 8px 24px rgba(0,0,0,.2); animation: fadeInUp .3s ease;
        `;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
}
