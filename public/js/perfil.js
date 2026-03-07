// ══════════════════════════════════════════
//  Módulo de Perfil del Negocio — DASH CRM
// ══════════════════════════════════════════

console.log('🏢 Cargando módulo perfil.js...');

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

class PerfilModule {
    constructor() {
        this.perfilData = null;
        this.init();
    }

    async init() {
        await this.cargarPerfil();
    }

    /* ─────────────── CARGAR ─────────────── */
    async cargarPerfil() {
        try {
            const res  = await fetch('../../api/perfil/index.php', { credentials:'include' });
            const data = await res.json();

            if (data.success && data.data) {
                this.perfilData = data.data;
                this.llenarFormulario(data.data);
            } else {
                this.llenarFormulario({});
            }
        } catch (err) {
            console.error('Error al cargar perfil:', err);
            showAlert('Error al cargar datos del perfil', 'error');
            this.llenarFormulario({});
        }
    }

    /* ─────────────── LLENAR FORM ─────────────── */
    llenarFormulario(p) {
        p = p || {};

        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val || '';
        };
        const chk = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.checked = Boolean(val);
        };

        // General
        set('nombre_negocio', p.nombre_negocio);
        set('razon_social',   p.razon_social);
        set('cuit',           p.cuit);
        set('condicion_iva',  p.condicion_iva);
        set('rubro',          p.rubro);

        // Contacto
        set('telefono',  p.telefono);
        set('whatsapp',  p.whatsapp);
        set('email',     p.email);
        set('sitio_web', p.sitio_web);
        set('instagram', p.instagram);
        set('facebook',  p.facebook);

        // Ubicación
        set('direccion',     p.direccion);
        set('ciudad',        p.ciudad);
        set('provincia',     p.provincia);
        set('codigo_postal', p.codigo_postal);
        set('pais',          p.pais || 'Argentina');

        // Tickets
        set('mensaje_ticket', p.mensaje_ticket);
        chk('mostrar_logo_ticket',      p.mostrar_logo_ticket      ?? 1);
        chk('mostrar_direccion_ticket', p.mostrar_direccion_ticket ?? 1);
        chk('mostrar_cuit_ticket',      p.mostrar_cuit_ticket      ?? 1);

        // Hero
        const heroNombre = document.getElementById('heroNombre');
        const heroRubro  = document.getElementById('heroRubro');
        if (heroNombre) heroNombre.textContent = p.nombre_negocio || 'Mi Negocio';
        if (heroRubro)  heroRubro.textContent  = p.rubro          || 'Completá tu perfil para que los datos aparezcan en los tickets';

        // Logo
        if (p.logo) {
            const logoSrc = `../../public/uploads/logos/${p.logo}`;
            const logoImg  = document.getElementById('logoImg');
            const logoHero = document.getElementById('logoHero');
            if (logoImg)  logoImg.src  = logoSrc;
            if (logoHero) logoHero.src = logoSrc;
        }

        // Portada
        const portadaImg         = document.getElementById('portadaImg');
        const portadaPlaceholder = document.getElementById('portadaPlaceholder');
        const btnEliminarPortada = document.getElementById('btnEliminarPortada');
        if (p.imagen_portada && portadaImg) {
            portadaImg.src          = `../../public/uploads/portadas/${p.imagen_portada}`;
            portadaImg.style.display = 'block';
            if (portadaPlaceholder) portadaPlaceholder.style.display = 'none';
            if (btnEliminarPortada) btnEliminarPortada.style.display = 'inline-flex';
        }

        // Carta Digital — QR + link + toggle
        if (p.carta_token) {
            this.actualizarCarta(p.carta_token, p.carta_activa);
        }

        // Link carta digital (legado)
        const linkCarta = document.getElementById('linkCartaDigital');
        if (linkCarta && p.id) {
            linkCarta.href = `../../views/tienda/index.php?negocio_id=${p.id}`;
        }

        // Horarios
        if (p.horarios) {
            try {
                const horarios = typeof p.horarios === 'string' ? JSON.parse(p.horarios) : p.horarios;
                Object.keys(horarios).forEach(dia => {
                    const h    = horarios[dia];
                    const actv = document.getElementById(`${dia}_activo`);
                    const dsd  = document.getElementById(`${dia}_desde`);
                    const hst  = document.getElementById(`${dia}_hasta`);
                    if (actv) {
                        actv.checked = Boolean(h.activo);
                        this.toggleHorario(dia, Boolean(h.activo));
                    }
                    if (dsd) dsd.value = h.desde || '09:00';
                    if (hst) hst.value = h.hasta || '18:00';
                });
            } catch(e) { console.error('Error al parsear horarios:', e); }
        }
    }

    /* ─────────────── TOGGLE HORARIO ─────────────── */
    toggleHorario(dia, activo) {
        const row = document.getElementById(`horario-row-${dia}`);
        if (!row) return;
        if (activo) {
            row.classList.add('active-day');
        } else {
            row.classList.remove('active-day');
        }
    }

    /* ─────────────── GUARDAR ─────────────── */
    async guardarCambios() {
        const nombreNegocio = document.getElementById('nombre_negocio')?.value.trim();
        if (!nombreNegocio) {
            showAlert('El nombre del negocio es requerido', 'error');
            // Activar tab general si no está activo
            document.querySelector('[data-tab="general"]')?.click();
            return;
        }

        const dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
        const horarios = {};
        dias.forEach(dia => {
            horarios[dia] = {
                activo: document.getElementById(`${dia}_activo`)?.checked ?? false,
                desde:  document.getElementById(`${dia}_desde`)?.value   ?? '09:00',
                hasta:  document.getElementById(`${dia}_hasta`)?.value   ?? '18:00',
            };
        });

        const formData = {
            nombre_negocio:           nombreNegocio,
            razon_social:             document.getElementById('razon_social')?.value    || null,
            cuit:                     document.getElementById('cuit')?.value            || null,
            condicion_iva:            document.getElementById('condicion_iva')?.value   || null,
            rubro:                    document.getElementById('rubro')?.value           || null,
            telefono:                 document.getElementById('telefono')?.value        || null,
            whatsapp:                 document.getElementById('whatsapp')?.value        || null,
            email:                    document.getElementById('email')?.value           || null,
            sitio_web:                document.getElementById('sitio_web')?.value       || null,
            instagram:                document.getElementById('instagram')?.value       || null,
            facebook:                 document.getElementById('facebook')?.value        || null,
            direccion:                document.getElementById('direccion')?.value       || null,
            ciudad:                   document.getElementById('ciudad')?.value          || null,
            provincia:                document.getElementById('provincia')?.value       || null,
            codigo_postal:            document.getElementById('codigo_postal')?.value   || null,
            pais:                     document.getElementById('pais')?.value            || 'Argentina',
            mensaje_ticket:           document.getElementById('mensaje_ticket')?.value  || null,
            mostrar_logo_ticket:      document.getElementById('mostrar_logo_ticket')?.checked      ? 1 : 0,
            mostrar_direccion_ticket: document.getElementById('mostrar_direccion_ticket')?.checked ? 1 : 0,
            mostrar_cuit_ticket:      document.getElementById('mostrar_cuit_ticket')?.checked      ? 1 : 0,
            horarios:                 JSON.stringify(horarios),
        };

        const btn = document.getElementById('btnGuardar');
        const orig = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<div class="spin" style="width:16px;height:16px;border-width:2px;display:inline-block;"></div> Guardando...'; }

        try {
            const res  = await fetch('../../api/perfil/index.php', {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type':'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();

            if (data.success) {
                showAlert('¡Perfil actualizado correctamente! ✓', 'success');
                this.perfilData = data.data;
                // Actualizar hero
                const heroNombre = document.getElementById('heroNombre');
                const heroRubro  = document.getElementById('heroRubro');
                if (heroNombre) heroNombre.textContent = nombreNegocio;
                if (heroRubro && formData.rubro) heroRubro.textContent = formData.rubro;
            } else {
                showAlert('Error: ' + (data.message || 'No se pudo guardar'), 'error');
            }
        } catch (err) {
            console.error('Error al guardar:', err);
            showAlert('Error de conexión al guardar', 'error');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        }
    }

    /* ─────────────── CARTA DIGITAL ─────────────── */

    actualizarCarta(token, activa) {
        // URL pública de la carta — detectar el base path desde la URL actual
        // views/perfil/index.php → subimos 3 niveles para llegar a la raíz del proyecto
        const pathParts = window.location.pathname.split('/');
        // Buscar el segmento del proyecto (ej: "DASHBASE") en la URL actual
        const scriptIdx = pathParts.indexOf('views');
        const basePath  = scriptIdx > 0
            ? pathParts.slice(0, scriptIdx).join('/')
            : '';
        const cartaUrl = `${window.location.origin}${basePath}/carta.php?t=${token}`;

        // Input con el link
        const input = document.getElementById('cartaLink');
        if (input) input.value = cartaUrl;

        // Link "Abrir carta"
        const linkCarta = document.getElementById('linkCartaDigital');
        if (linkCarta) linkCarta.href = cartaUrl;

        // Toggle activa
        const toggle = document.getElementById('cartaActivaToggle');
        if (toggle) toggle.checked = Boolean(activa);

        // Generar QR
        const qrContainer = document.getElementById('qrContainer');
        if (qrContainer && typeof QRCode !== 'undefined') {
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: cartaUrl,
                width: 156,
                height: 156,
                colorDark: '#1A202C',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    }

    async toggleCarta(activa) {
        try {
            const res  = await fetch('../../api/perfil/toggle_carta.php', {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ activa: activa ? 1 : 0 })
            });
            const data = await res.json();
            if (data.success) {
                showAlert(activa ? '✓ Carta activada' : 'Carta desactivada', activa ? 'success' : 'warning');
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch {
            showAlert('Error de conexión', 'error');
        }
    }

    async regenerarToken() {
        if (!confirm('¿Generar un nuevo QR?\n\nEl QR anterior dejará de funcionar inmediatamente.')) return;
        try {
            const res  = await fetch('../../api/perfil/regenerar_carta_token.php', {
                method: 'POST', credentials: 'include'
            });
            const data = await res.json();
            if (data.success) {
                const toggle = document.getElementById('cartaActivaToggle');
                this.actualizarCarta(data.data.carta_token, toggle?.checked ?? 1);
                showAlert('✓ Nuevo QR generado. El anterior ya no funciona.', 'success');
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch {
            showAlert('Error de conexión', 'error');
        }
    }

    copiarLink() {
        const input = document.getElementById('cartaLink');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(() => {
            showAlert('✓ Enlace copiado al portapapeles', 'success');
        }).catch(() => {
            input.select();
            document.execCommand('copy');
            showAlert('✓ Enlace copiado', 'success');
        });
    }

    /* ─────────────── SUBIR LOGO ─────────────── */
    async subirLogo(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            showAlert('Por favor seleccioná una imagen', 'error');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showAlert('La imagen no debe superar 2 MB', 'error');
            return;
        }

        const status = document.getElementById('logoStatus');
        if (status) status.textContent = 'Subiendo...';

        try {
            const formData = new FormData();
            formData.append('logo', file);

            const res  = await fetch('../../api/perfil/upload_logo.php', {
                method: 'POST', credentials: 'include', body: formData
            });
            const data = await res.json();

            if (data.success) {
                const reader = new FileReader();
                reader.onload = e => {
                    const src = e.target.result;
                    const logoImg  = document.getElementById('logoImg');
                    const logoHero = document.getElementById('logoHero');
                    if (logoImg)  logoImg.src  = src;
                    if (logoHero) logoHero.src = src;
                };
                reader.readAsDataURL(file);
                showAlert('Logo subido correctamente ✓', 'success');
                if (status) status.textContent = `Logo actualizado · ${(file.size/1024).toFixed(0)} KB`;
            } else {
                showAlert('Error: ' + (data.message || 'No se pudo subir'), 'error');
                if (status) status.textContent = '';
            }
        } catch {
            showAlert('Error de conexión al subir logo', 'error');
            if (status) status.textContent = '';
        }
        // Limpiar input para permitir volver a subir el mismo archivo
        event.target.value = '';
    }

    /* ─────────────── ELIMINAR LOGO ─────────────── */
    async eliminarLogo() {
        if (!confirm('¿Eliminar el logo del negocio?')) return;

        try {
            const res  = await fetch('../../api/perfil/delete_logo.php', {
                method: 'DELETE', credentials: 'include'
            });
            const data = await res.json();

            if (data.success) {
                const noImg = '../../public/img/no-image.svg';
                const logoImg  = document.getElementById('logoImg');
                const logoHero = document.getElementById('logoHero');
                if (logoImg)  logoImg.src  = noImg;
                if (logoHero) logoHero.src = noImg;
                const status = document.getElementById('logoStatus');
                if (status) status.textContent = '';
                showAlert('Logo eliminado correctamente', 'success');
            } else {
                showAlert('Error: ' + (data.message || 'No se pudo eliminar'), 'error');
            }
        } catch {
            showAlert('Error de conexión al eliminar logo', 'error');
        }
    }

    /* ─────────────── SUBIR PORTADA ─────────────── */
    async subirPortada(event) {
        const file = event.target.files[0];
        if (!file) return;

        const allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
        if (!allowed.includes(file.type)) {
            showAlert('Formato no permitido. Usá JPG, PNG o WebP', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showAlert('La imagen no debe superar 5 MB', 'error');
            return;
        }

        const status = document.getElementById('portadaStatus');
        if (status) status.textContent = 'Subiendo...';

        try {
            const formData = new FormData();
            formData.append('portada', file);

            const res  = await fetch('../../api/perfil/upload_portada.php', {
                method: 'POST', credentials: 'include', body: formData
            });
            const data = await res.json();

            if (data.success) {
                const reader = new FileReader();
                reader.onload = e => {
                    const portadaImg         = document.getElementById('portadaImg');
                    const portadaPlaceholder = document.getElementById('portadaPlaceholder');
                    const btnEliminar        = document.getElementById('btnEliminarPortada');
                    if (portadaImg) {
                        portadaImg.src          = e.target.result;
                        portadaImg.style.display = 'block';
                    }
                    if (portadaPlaceholder) portadaPlaceholder.style.display = 'none';
                    if (btnEliminar)        btnEliminar.style.display = 'inline-flex';
                };
                reader.readAsDataURL(file);
                showAlert('¡Imagen de portada subida correctamente! ✓', 'success');
                if (status) status.textContent = `Portada actualizada · ${(file.size/1024).toFixed(0)} KB`;
            } else {
                showAlert('Error: ' + (data.message || 'No se pudo subir'), 'error');
                if (status) status.textContent = '';
            }
        } catch {
            showAlert('Error de conexión al subir portada', 'error');
            if (status) status.textContent = '';
        }
        event.target.value = '';
    }

    /* ─────────────── ELIMINAR PORTADA ─────────────── */
    async eliminarPortada() {
        if (!confirm('¿Eliminar la imagen de portada?')) return;

        try {
            const res  = await fetch('../../api/perfil/delete_portada.php', {
                method: 'POST', credentials: 'include'
            });
            const data = await res.json();

            if (data.success) {
                const portadaImg         = document.getElementById('portadaImg');
                const portadaPlaceholder = document.getElementById('portadaPlaceholder');
                const btnEliminar        = document.getElementById('btnEliminarPortada');
                if (portadaImg) { portadaImg.src = ''; portadaImg.style.display = 'none'; }
                if (portadaPlaceholder) portadaPlaceholder.style.display = 'flex';
                if (btnEliminar)        btnEliminar.style.display = 'none';
                const status = document.getElementById('portadaStatus');
                if (status) status.textContent = '';
                showAlert('Imagen de portada eliminada', 'success');
            } else {
                showAlert('Error: ' + (data.message || 'No se pudo eliminar'), 'error');
            }
        } catch {
            showAlert('Error de conexión al eliminar portada', 'error');
        }
    }
}

console.log('✅ Módulo perfil.js cargado completamente.');
