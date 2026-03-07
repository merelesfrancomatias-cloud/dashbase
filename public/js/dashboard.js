/**
 * Dashboard JS — Resumen del día completo
 * Chart.js 4.4.0
 */
const BASE = window.APP_BASE || '../..';
let chartHoras = null;

const METODO_COLORS = {
    'efectivo':      '#10b981',
    'débito':        '#3b82f6',
    'debito':        '#3b82f6',
    'crédito':       '#8b5cf6',
    'credito':       '#8b5cf6',
    'transferencia': '#f59e0b',
    'mercado pago':  '#06b6d4',
    'mercadopago':   '#06b6d4',
    'qr':            '#06b6d4',
};

function metodoColor(m) {
    return METODO_COLORS[(m||'').toLowerCase()] || '#94a3b8';
}

/* ── FORMATO ── */
function fmt(n) {
    const v = parseFloat(n) || 0;
    if (v >= 1000000) return '$' + (v/1000000).toFixed(1) + 'M';
    if (v >= 1000)    return '$' + (v/1000).toFixed(1) + 'k';
    return '$' + v.toLocaleString('es-AR', {minimumFractionDigits:0, maximumFractionDigits:0});
}
function fmtHora(h) {
    const hh = parseInt(h);
    const ampm = hh >= 12 ? 'pm' : 'am';
    return (hh % 12 || 12) + ampm;
}

/* ── INIT ── */
document.addEventListener('DOMContentLoaded', async () => {
    // Splash
    setTimeout(() => {
        const s = document.getElementById('splashScreen');
        if (s) { s.classList.add('fade-out'); setTimeout(() => s.style.display='none', 500); }
    }, 1000);

    // Nombre desde localStorage
    const stored = localStorage.getItem('user');
    if (stored) {
        try {
            const u = JSON.parse(stored);
            const el = document.getElementById('greetName');
            if (el && u.nombre) el.textContent = u.nombre.split(' ')[0];
        } catch(e){}
    }

    // Logout — manejado por header.php (no registrar aquí para evitar doble handler)
    // document.getElementById('btnLogout')?.addEventListener('click', handleLogout);

    // Cargar datos si estamos en dashboard
    if (window.location.pathname.includes('/dashboard/')) {
        await loadStats();
    }
});

/* ── LOAD STATS ── */
async function loadStats() {
    try {
        const res  = await fetch(`${BASE}/api/dashboard/stats.php`, { credentials:'include' });
        const json = await res.json();
        if (!json.success) return;
        const d = json.data;

        renderKPIs(d);
        renderCajaChip(d.caja_activa);
        renderChartHoras(d.ventas_por_hora || []);
        renderUltimasVentas(d.ultimas_ventas || []);
        renderResumen(d);
        renderMetodosPago(d.metodos_pago_hoy || []);
        renderAlertas(d);

    } catch(e) {
        console.error('Error cargando dashboard:', e);
    }
}

/* ── KPIs ── */
function renderKPIs(d) {
    set('ventasHoy',     fmt(d.ventas_hoy?.monto  || 0));
    set('cantVentasHoy', d.ventas_hoy?.cantidad || 0);
    set('gananciaNeta',  fmt(d.ganancia_neta_hoy  || 0));
    set('gastosHoy',     fmt(d.gastos_hoy || 0));
    set('ventasMes',     fmt(d.ventas_mes?.monto  || 0));
    set('cantVentasMes', d.ventas_mes?.cantidad || 0);
    set('totalProductos', d.productos || 0);
    set('totalClientes',  d.clientes  || 0);

    // Badge stock bajo
    if (d.stock_bajo > 0) {
        const b = document.getElementById('stockBajoBadge');
        if (b) { b.textContent = d.stock_bajo + ' bajo'; b.style.display = ''; }
    }
}

/* ── CAJA CHIP ── */
function renderCajaChip(caja) {
    const chip   = document.getElementById('cajaChip');
    const status = document.getElementById('cajaStatus');
    if (!chip || !status) return;
    if (caja) {
        chip.classList.remove('caja-close');
        chip.classList.add('caja-open');
        const hora = new Date(caja.fecha_apertura).toLocaleTimeString('es-AR', {hour:'2-digit', minute:'2-digit'});
        status.textContent = 'Caja abierta · ' + hora;
    } else {
        chip.classList.remove('caja-open');
        chip.classList.add('caja-close');
        status.textContent = 'Caja cerrada';
    }
}

/* ── CHART HORAS ── */
function renderChartHoras(rows) {
    const ctx = document.getElementById('chartHoras');
    if (!ctx) return;
    if (chartHoras) chartHoras.destroy();

    const label = document.getElementById('labelHoras');

    if (!rows.length) {
        if (label) label.textContent = 'Sin ventas hoy';
        // gráfico vacío
        const horas = Array.from({length:12}, (_,i) => fmtHora(8+i));
        chartHoras = new Chart(ctx, {
            type: 'line',
            data: { labels: horas, datasets: [{ data: Array(12).fill(0), borderColor:'#e2e8f0', fill:true, backgroundColor:'rgba(226,232,240,.2)', borderWidth:2, tension:.4, pointRadius:0 }] },
            options: { responsive:true, plugins:{ legend:{display:false}, tooltip:{enabled:false} }, scales:{ x:{grid:{display:false}, ticks:{color:'#94a3b8',font:{size:11}}}, y:{grid:{color:'rgba(148,163,184,.1)'}, ticks:{color:'#94a3b8',font:{size:11}, callback:v=>fmt(v)}, min:0} } }
        });
        return;
    }

    if (label) label.textContent = rows.length + ' horas activas';

    // Completar todas las horas del día con 0 donde no hay venta
    const mapaHoras = {};
    rows.forEach(r => { mapaHoras[parseInt(r.hora)] = parseFloat(r.total||0); });
    const horaActual = new Date().getHours();
    const horas = [], vals = [];
    for (let h = 0; h <= Math.max(horaActual, ...Object.keys(mapaHoras).map(Number)); h++) {
        horas.push(fmtHora(h));
        vals.push(mapaHoras[h] || 0);
    }

    const grad = ctx.getContext('2d').createLinearGradient(0, 0, 0, 200);
    grad.addColorStop(0, 'rgba(16,185,129,.3)');
    grad.addColorStop(1, 'rgba(16,185,129,.02)');

    chartHoras = new Chart(ctx, {
        type: 'line',
        data: {
            labels: horas,
            datasets: [{
                label: 'Ventas',
                data: vals,
                fill: true,
                backgroundColor: grad,
                borderColor: '#10b981',
                borderWidth: 2.5,
                tension: 0.45,
                pointRadius: vals.length > 15 ? 0 : 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#10b981',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            interaction: { mode:'index', intersect:false },
            plugins: {
                legend: { display:false },
                tooltip: {
                    backgroundColor:'#1e293b', titleColor:'#94a3b8', bodyColor:'#f1f5f9', padding:10,
                    callbacks: { label: c => ' ' + fmt(c.raw) }
                }
            },
            scales: {
                x: { grid:{display:false}, ticks:{color:'#94a3b8', font:{size:11}, maxTicksLimit:8} },
                y: { grid:{color:'rgba(148,163,184,.1)'}, ticks:{color:'#94a3b8', font:{size:11}, callback:v=>fmt(v)}, min:0 }
            }
        }
    });
}

/* ── ÚLTIMAS VENTAS ── */
function renderUltimasVentas(ventas) {
    const el = document.getElementById('ultimasVentasList');
    if (!el) return;
    if (!ventas.length) {
        el.innerHTML = '<div class="empty-state"><i class="fas fa-receipt"></i><p>Sin ventas hoy todavía</p></div>';
        return;
    }
    const colores = { 'efectivo':'#10b981','débito':'#3b82f6','debito':'#3b82f6','crédito':'#8b5cf6','credito':'#8b5cf6','transferencia':'#f59e0b','mercado pago':'#06b6d4','qr':'#06b6d4' };
    el.innerHTML = ventas.map(v => {
        const hora  = new Date(v.fecha_venta).toLocaleTimeString('es-AR', {hour:'2-digit', minute:'2-digit'});
        const c     = colores[(v.metodo_pago||'').toLowerCase()] || '#94a3b8';
        const metodo = v.metodo_pago ? v.metodo_pago.charAt(0).toUpperCase()+v.metodo_pago.slice(1) : 'Efectivo';
        return `
        <div class="venta-row">
            <div class="venta-icon" style="background:${c}20;color:${c};">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="venta-info">
                <div class="vi-top">Venta #${v.id} · ${metodo}</div>
                <div class="vi-bot"><i class="fas fa-clock" style="margin-right:3px;"></i>${hora} &nbsp;·&nbsp; ${v.items} ítem${v.items!=1?'s':''}</div>
            </div>
            <div class="venta-total">${fmt(v.total)}</div>
        </div>`;
    }).join('');
}

/* ── RESUMEN ── */
function renderResumen(d) {
    const ventasH  = d.ventas_hoy?.monto || 0;
    const gastosH  = d.gastos_hoy || 0;
    const ganancia = d.ganancia_neta_hoy || 0;
    const ventasM  = d.ventas_mes?.monto || 0;
    const ventasA  = d.ventas_ayer?.monto || 0;

    set('resVentasBrutas', fmt(ventasH));
    set('resGastos',       fmt(gastosH));
    set('resGanancia',     fmt(ganancia));
    set('resVentasMes',    fmt(ventasM));

    // vs ayer
    const vsEl = document.getElementById('resVsAyer');
    if (vsEl) {
        if (ventasA <= 0) {
            vsEl.innerHTML = '<span style="color:var(--text-secondary);">Sin datos de ayer</span>';
        } else {
            const diff = ventasH - ventasA;
            const pct  = ((diff / ventasA) * 100).toFixed(1);
            const color = diff >= 0 ? '#10b981' : '#ef4444';
            const arrow = diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
            const sign  = diff >= 0 ? '+' : '';
            vsEl.innerHTML = `<span style="color:${color};font-weight:700;"><i class="fas ${arrow}"></i> ${sign}${pct}%</span> <span style="color:var(--text-secondary);font-weight:400;">(${sign}${fmt(diff)})</span>`;
        }
    }
}

/* ── MÉTODOS DE PAGO ── */
function renderMetodosPago(rows) {
    const el = document.getElementById('metodosPagoList');
    if (!el) return;
    if (!rows.length) {
        el.innerHTML = '<div class="empty-state" style="padding:24px 0;"><i class="fas fa-wallet"></i><p>Sin ventas hoy</p></div>';
        return;
    }
    const total = rows.reduce((a,r) => a + parseFloat(r.total||0), 0);
    el.innerHTML = rows.map(r => {
        const c = metodoColor(r.metodo_pago);
        const pct = total > 0 ? (parseFloat(r.total)/total*100).toFixed(0) : 0;
        const nombre = (r.metodo_pago||'Otro').charAt(0).toUpperCase() + (r.metodo_pago||'otro').slice(1);
        return `
        <div class="metodo-item">
            <div class="metodo-left">
                <div class="metodo-dot" style="background:${c};"></div>
                <span>${nombre}</span>
                <span style="font-size:11px;color:var(--text-secondary);margin-left:4px;">(${r.cantidad})</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:11px;color:var(--text-secondary);font-weight:700;">${pct}%</span>
                <span style="font-weight:800;color:var(--text-primary);">${fmt(r.total)}</span>
            </div>
        </div>`;
    }).join('');
}

/* ── ALERTAS ── */
function renderAlertas(d) {
    const el = document.getElementById('alertasList');
    if (!el) return;
    const alertas = [];

    if (d.stock_bajo > 0) {
        alertas.push({
            icon: 'fa-box', bg: 'rgba(245,158,11,.12)', color: '#f59e0b',
            msg: `<strong>${d.stock_bajo} producto${d.stock_bajo>1?'s':''}</strong> con stock bajo (≤ 5 unidades)`,
            link: '../productos/index.php'
        });
    }
    if (!d.caja_activa) {
        alertas.push({
            icon: 'fa-cash-register', bg: 'rgba(239,68,68,.12)', color: '#ef4444',
            msg: 'La caja está <strong>cerrada</strong>. Abrila para registrar ventas.',
            link: '../caja/index.php'
        });
    }
    if (d.ventas_hoy?.cantidad > 0 && d.gastos_hoy > d.ventas_hoy?.monto) {
        alertas.push({
            icon: 'fa-exclamation-triangle', bg: 'rgba(239,68,68,.12)', color: '#ef4444',
            msg: 'Los <strong>gastos superan</strong> a las ventas de hoy.',
            link: '../gastos/index.php'
        });
    }

    if (!alertas.length) {
        el.innerHTML = '<div class="empty-state" style="padding:24px 0;"><i class="fas fa-check-circle" style="opacity:.5;color:#10b981;"></i><p>Todo en orden ✓</p></div>';
        return;
    }

    el.innerHTML = alertas.map(a => `
        <div class="alert-row">
            <div class="alert-icon" style="background:${a.bg};color:${a.color};"><i class="fas ${a.icon}"></i></div>
            <div style="flex:1;font-size:12px;color:var(--text-primary);">${a.msg}</div>
            ${a.link ? `<a href="${a.link}" style="font-size:11px;color:var(--primary);font-weight:700;white-space:nowrap;">Ver →</a>` : ''}
        </div>
    `).join('');
}

/* ── UTILS ── */
function set(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

/* ── LOGOUT ── */
async function handleLogout() {
    if (!confirm('¿Cerrás sesión?')) return;
    try {
        await fetch(`${BASE}/api/auth/logout.php`, { method:'POST', credentials:'include' });
    } catch(e){}
    localStorage.removeItem('user');
    window.location.href = `${BASE}/index.php`;
}
