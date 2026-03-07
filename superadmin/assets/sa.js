// ============================================================
// Super Admin Panel — JS compartido
// ============================================================

// ── Sidebar mobile ──────────────────────────────────────────
function sa_toggleSidebar(force) {
    const sb  = document.querySelector('.sa-sidebar');
    const ov  = document.getElementById('saOverlay');
    const open = force !== undefined ? force : !sb.classList.contains('open');
    if (open) {
        sb.classList.add('open');
        if (ov) { ov.classList.add('show'); }
        document.body.style.overflow = 'hidden';
    } else {
        sb.classList.remove('open');
        if (ov) { ov.classList.remove('show'); }
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Hamburger
    const ham = document.getElementById('saHam');
    if (ham) ham.addEventListener('click', () => sa_toggleSidebar());

    // Overlay click → close
    const ov = document.getElementById('saOverlay');
    if (ov) ov.addEventListener('click', () => sa_toggleSidebar(false));

    // Active nav item
    const path = window.location.pathname;
    document.querySelectorAll('.sa-nav-item').forEach(item => {
        const href = item.getAttribute('href') || '';
        if (href && path.includes(href.split('?')[0].split('/').pop().replace('.php',''))) {
            item.classList.add('active');
        }
    });
});

// ── Toast ───────────────────────────────────────────────────
function sa_toast(msg, type = 'success', duration = 3500) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle' };
    const t = document.createElement('div');
    t.className = `sa-toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span>${msg}</span>`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(60px)'; t.style.transition = '.3s'; setTimeout(() => t.remove(), 350); }, duration);
}

// ── Modal helpers ───────────────────────────────────────────
function sa_openModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('show');
    document.body.style.overflow = 'hidden';
}
function sa_closeModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('show');
    document.body.style.overflow = '';
}

// Close modal on backdrop click
document.addEventListener('click', e => {
    if (e.target.classList.contains('sa-modal-backdrop')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ── Confirm danger ──────────────────────────────────────────
function sa_confirm(msg, onConfirm) {
    const d = document.createElement('div');
    d.className = 'sa-modal-backdrop show';
    d.innerHTML = `
    <div class="sa-modal" style="max-width:380px">
        <div class="sa-modal-header">
            <h4><i class="fas fa-exclamation-triangle" style="color:var(--sa-warning);margin-right:8px"></i>Confirmar acción</h4>
            <button class="sa-modal-close" onclick="this.closest('.sa-modal-backdrop').remove();document.body.style.overflow=''"><i class="fas fa-times"></i></button>
        </div>
        <div class="sa-modal-body"><p style="color:var(--sa-text)">${msg}</p></div>
        <div class="sa-modal-footer">
            <button class="sa-btn ghost" onclick="this.closest('.sa-modal-backdrop').remove();document.body.style.overflow=''">Cancelar</button>
            <button class="sa-btn danger" id="saConfirmOk">Confirmar</button>
        </div>
    </div>`;
    document.body.appendChild(d);
    document.getElementById('saConfirmOk').onclick = () => { d.remove(); document.body.style.overflow = ''; onConfirm(); };
}

// ── Fetch helper ────────────────────────────────────────────
async function sa_fetch(url, data = null, method = null) {
    const opts = {
        method: method || (data ? 'POST' : 'GET'),
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    };
    if (data) opts.body = JSON.stringify(data);
    const r = await fetch(url, opts);
    return r.json();
}

// ── Format money ─────────────────────────────────────────── 
function sa_money(v) {
    return '$' + Number(v).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── Format date ──────────────────────────────────────────── 
function sa_date(str) {
    if (!str) return '—';
    const d = new Date(str + 'T00:00:00');
    return d.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
