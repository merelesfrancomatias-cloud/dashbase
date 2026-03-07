// Módulo de Gestión de Empleados
class EmpleadosModule {
    constructor() {
        this.empleados = [];
        this.editingId = null;
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadEmpleados();
    }

    setupEventListeners() {
        // Búsqueda
        document.getElementById('searchInput')?.addEventListener('input', 
            this.debounce(() => this.aplicarFiltros(), 300)
        );

        // Filtros
        document.getElementById('filtroRol')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });

        document.getElementById('filtroEstado')?.addEventListener('change', () => {
            this.aplicarFiltros();
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    aplicarFiltros() {
        this.loadEmpleados();
    }

    async loadEmpleados() {
        try {
            const search = document.getElementById('searchInput')?.value || '';
            const rol = document.getElementById('filtroRol')?.value || '';
            const estado = document.getElementById('filtroEstado')?.value || '';
            
            let url = '../../api/empleados/index.php?';
            if (search) url += `search=${encodeURIComponent(search)}&`;
            if (rol) url += `rol=${rol}&`;
            if (estado) url += `estado=${estado}&`;

            const response = await fetch(url, {
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success) {
                this.empleados = data.data;
                this.renderEmpleados();
                this.renderStats();
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al cargar empleados', 'error');
        }
    }

    renderStats() {
        const total = this.empleados.length;
        const activos = this.empleados.filter(e => e.estado === 'activo').length;
        const admins = this.empleados.filter(e => e.rol === 'admin').length;
        const vendedores = this.empleados.filter(e => e.rol === 'vendedor').length;

        document.getElementById('totalEmpleados').textContent = total;
        document.getElementById('empleadosActivos').textContent = activos;
        document.getElementById('administradores').textContent = admins;
        document.getElementById('vendedores').textContent = vendedores;
    }

    renderEmpleados() {
        const tbody = document.getElementById('empleadosTableBody');
        const conteoSpan = document.getElementById('conteoEmpleados');
        
        if (conteoSpan) {
            conteoSpan.textContent = `${this.empleados.length} empleado${this.empleados.length !== 1 ? 's' : ''}`;
        }
        
        if (this.empleados.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 50px; color: #999;">
                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3; display: block;"></i>
                        <p style="margin: 0; font-size: 14px;">No hay empleados registrados</p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #bbb;">Agrega tu primer empleado</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        this.empleados.forEach(empleado => {
            const fecha = new Date(empleado.fecha_creacion);
            const rolBadge = this.getRolBadge(empleado.rol);
            const estadoBadge = this.getEstadoBadge(empleado.estado);
            
            html += `
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="text-align: center; font-weight: 600; color: var(--primary); padding: 12px 10px;">#${empleado.id}</td>
                    <td style="font-weight: 500; padding: 12px 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                ${empleado.nombre.charAt(0).toUpperCase()}
                            </div>
                            <span>${empleado.nombre}</span>
                        </div>
                    </td>
                    <td style="color: #666; font-size: 13px; padding: 12px 10px;">
                        <i class="fas fa-envelope" style="color: #999; margin-right: 5px;"></i>
                        ${empleado.email}
                    </td>
                    <td style="color: #666; font-size: 13px; padding: 12px 10px;">
                        ${empleado.telefono ? 
                            `<i class="fas fa-phone" style="color: #999; margin-right: 5px;"></i>${empleado.telefono}` : 
                            '<span style="color: #ccc;">-</span>'
                        }
                    </td>
                    <td style="text-align: center; padding: 12px 10px;">${rolBadge}</td>
                    <td style="text-align: center; padding: 12px 10px;">${estadoBadge}</td>
                    <td style="color: #666; font-size: 12px; padding: 12px 10px;">
                        ${fecha.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })}
                    </td>
                    <td style="text-align: center; padding: 12px 10px;">
                        <button class="btn-icon" onclick="empleadosModule.editEmpleado(${empleado.id})" 
                                title="Editar" style="background: var(--primary); color: white; padding: 6px 10px; border-radius: 6px; margin-right: 5px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" onclick="empleadosModule.deleteEmpleado(${empleado.id})" 
                                title="Eliminar" style="background: #FF4444; color: white; padding: 6px 10px; border-radius: 6px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    getRolBadge(rol) {
        const badges = {
            'admin': '<span class="badge" style="background: #E7E9FC; color: #6366F1; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-user-shield"></i> Admin</span>',
            'vendedor': '<span class="badge" style="background: #FFF3CD; color: #FFC107; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-user-tie"></i> Vendedor</span>'
        };
        return badges[rol] || `<span class="badge" style="background: #f0f0f0; color: #666; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;">${rol}</span>`;
    }

    getEstadoBadge(estado) {
        const badges = {
            'activo': '<span class="badge" style="background: #D1F2EB; color: #00C9A7; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-check-circle"></i> Activo</span>',
            'inactivo': '<span class="badge" style="background: #FFE5E5; color: #FF4444; padding: 5px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;"><i class="fas fa-times-circle"></i> Inactivo</span>'
        };
        return badges[estado] || estado;
    }

    togglePermisos() {
        const rol = document.getElementById('rol').value;
        const permisosSection = document.getElementById('permisosSection');
        
        if (rol === 'empleado') {
            permisosSection.style.display = 'block';
        } else {
            permisosSection.style.display = 'none';
        }
    }

    openModal() {
        this.editingId = null;
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Empleado';
        document.getElementById('empleadoForm').reset();
        document.getElementById('password').required = true;
        document.getElementById('passwordLabel').textContent = '*';
        document.getElementById('passwordHelp').textContent = '* Campo obligatorio para nuevos empleados. Dejar vacío para no cambiar.';
        document.getElementById('empleadoModal').classList.add('show');
        this.togglePermisos();
        
        // Establecer permisos por defecto para empleados nuevos
        this.setDefaultPermisos();
    }

    setDefaultPermisos() {
        // Permisos básicos activados por defecto
        document.getElementById('perm_ver_productos').checked = true;
        document.getElementById('perm_crear_productos').checked = false;
        document.getElementById('perm_editar_productos').checked = false;
        document.getElementById('perm_eliminar_productos').checked = false;
        
        document.getElementById('perm_ver_ventas').checked = true;
        document.getElementById('perm_crear_ventas').checked = true;
        document.getElementById('perm_cancelar_ventas').checked = false;
        
        document.getElementById('perm_ver_gastos').checked = false;
        document.getElementById('perm_crear_gastos').checked = false;
        
        document.getElementById('perm_gestionar_caja').checked = true;
        
        document.getElementById('perm_ver_empleados').checked = false;
        document.getElementById('perm_crear_empleados').checked = false;
        
        document.getElementById('perm_ver_reportes').checked = false;

        // Resetear todos los permisos de módulos específicos (solo si el elemento existe)
        const chk = id => { const el = document.getElementById(id); if (el) el.checked = false; };
        // Restaurant
        chk('perm_ver_mesas'); chk('perm_gestionar_mesas');
        chk('perm_ver_reservas'); chk('perm_gestionar_reservas');
        chk('perm_ver_cocina'); chk('perm_gestionar_cocina');
        chk('perm_gestionar_carta');
        // Ferretería
        chk('perm_ver_presupuestos'); chk('perm_crear_presupuestos');
        chk('perm_aprobar_presupuestos'); chk('perm_ver_stock');
        chk('perm_ver_proveedores'); chk('perm_gestionar_proveedores');
        chk('perm_ver_ordenes'); chk('perm_gestionar_ordenes');
        // Supermercado
        chk('perm_gestionar_stock'); chk('perm_imprimir_etiquetas');
        // Peluquería
        chk('perm_ver_agenda'); chk('perm_gestionar_agenda');
        chk('perm_ver_servicios'); chk('perm_gestionar_servicios');
        chk('perm_ver_clientes_pelu'); chk('perm_gestionar_clientes_pelu');
        // Gimnasio
        chk('perm_ver_socios'); chk('perm_gestionar_socios');
        chk('perm_ver_clases'); chk('perm_gestionar_clases');
        chk('perm_registrar_asistencias');
        chk('perm_ver_pagos_gym'); chk('perm_registrar_pagos_gym');
        // Canchas
        chk('perm_ver_reservas_canchas'); chk('perm_gestionar_reservas_canchas');
        chk('perm_ver_canchas'); chk('perm_gestionar_canchas');
        chk('perm_ver_clientes_canchas'); chk('perm_gestionar_clientes_canchas');
        chk('perm_caja_canchas');
    }
    closeModal() {
        document.getElementById('empleadoModal').classList.remove('show');
        this.editingId = null;
    }

    async editEmpleado(id) {
        try {
            const response = await fetch(`../../api/empleados/index.php?id=${id}`, {
                credentials: 'include'
            });
            const data = await response.json();

            if (data.success) {
                const empleado = data.data;
                this.editingId = id;
                
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Editar Empleado';
                document.getElementById('empleadoId').value = empleado.id;
                document.getElementById('nombre').value = empleado.nombre || '';
                document.getElementById('apellido').value = empleado.apellido || '';
                document.getElementById('usuario').value = empleado.usuario || '';
                document.getElementById('email').value = empleado.email || '';
                document.getElementById('telefono').value = empleado.telefono || '';
                document.getElementById('rol').value = empleado.rol;
                document.getElementById('activo').value = empleado.activo ? '1' : '0';
                document.getElementById('password').value = '';
                document.getElementById('password').required = false;
                document.getElementById('passwordLabel').textContent = '';
                document.getElementById('passwordHelp').textContent = 'Dejar en blanco para mantener la contraseña actual';
                
                // Cargar permisos si es empleado
                if (empleado.rol === 'empleado' && empleado.permisos) {
                    const permisos = empleado.permisos;
                    const set = (id, val) => { const el = document.getElementById(id); if (el) el.checked = Boolean(val); };

                    set('perm_ver_productos',    permisos.ver_productos);
                    set('perm_crear_productos',  permisos.crear_productos);
                    set('perm_editar_productos', permisos.editar_productos);
                    set('perm_eliminar_productos',permisos.eliminar_productos);
                    
                    set('perm_ver_ventas',    permisos.ver_ventas);
                    set('perm_crear_ventas',  permisos.crear_ventas);
                    set('perm_cancelar_ventas',permisos.cancelar_ventas);
                    
                    set('perm_ver_gastos',   permisos.ver_gastos);
                    set('perm_crear_gastos', permisos.crear_gastos);
                    
                    set('perm_gestionar_caja',permisos.gestionar_caja);
                    
                    set('perm_ver_empleados',  permisos.ver_empleados);
                    set('perm_crear_empleados',permisos.crear_empleados);
                    
                    set('perm_ver_reportes', permisos.ver_reportes);

                    // Restaurant (solo si está presente en el DOM)
                    set('perm_ver_mesas',          permisos.ver_mesas);
                    set('perm_gestionar_mesas',     permisos.gestionar_mesas);
                    set('perm_ver_reservas',        permisos.ver_reservas);
                    set('perm_gestionar_reservas',  permisos.gestionar_reservas);
                    set('perm_ver_cocina',          permisos.ver_cocina);
                    set('perm_gestionar_cocina',    permisos.gestionar_cocina);

                    // Ferretería (solo si está presente en el DOM)
                    set('perm_ver_presupuestos',    permisos.ver_presupuestos);
                    set('perm_crear_presupuestos',  permisos.crear_presupuestos);
                    set('perm_aprobar_presupuestos',permisos.aprobar_presupuestos);
                    set('perm_ver_stock',           permisos.ver_stock);
                    set('perm_ver_proveedores',     permisos.ver_proveedores);
                    set('perm_gestionar_proveedores',permisos.gestionar_proveedores);
                    set('perm_ver_ordenes',         permisos.ver_ordenes);
                    set('perm_gestionar_ordenes',   permisos.gestionar_ordenes);
                    // Supermercado
                    set('perm_gestionar_stock',     permisos.gestionar_stock);
                    set('perm_imprimir_etiquetas',  permisos.imprimir_etiquetas);
                    // Peluquería
                    set('perm_ver_agenda',          permisos.ver_agenda);
                    set('perm_gestionar_agenda',    permisos.gestionar_agenda);
                    set('perm_ver_servicios',       permisos.ver_servicios);
                    set('perm_gestionar_servicios', permisos.gestionar_servicios);
                    set('perm_ver_clientes_pelu',   permisos.ver_clientes_pelu);
                    set('perm_gestionar_clientes_pelu', permisos.gestionar_clientes_pelu);
                    // Gimnasio
                    set('perm_ver_socios',          permisos.ver_socios);
                    set('perm_gestionar_socios',    permisos.gestionar_socios);
                    set('perm_ver_clases',          permisos.ver_clases);
                    set('perm_gestionar_clases',    permisos.gestionar_clases);
                    set('perm_registrar_asistencias',permisos.registrar_asistencias);
                    set('perm_ver_pagos_gym',       permisos.ver_pagos_gym);
                    set('perm_registrar_pagos_gym', permisos.registrar_pagos_gym);
                    // Canchas
                    set('perm_ver_reservas_canchas',    permisos.ver_reservas_canchas);
                    set('perm_gestionar_reservas_canchas', permisos.gestionar_reservas_canchas);
                    set('perm_ver_canchas',             permisos.ver_canchas);
                    set('perm_gestionar_canchas',       permisos.gestionar_canchas);
                    set('perm_ver_clientes_canchas',    permisos.ver_clientes_canchas);
                    set('perm_gestionar_clientes_canchas', permisos.gestionar_clientes_canchas);
                    set('perm_caja_canchas',            permisos.caja_canchas);
                    // Restaurant — carta
                    set('perm_gestionar_carta',     permisos.gestionar_carta);
                }
                
                this.togglePermisos();
                document.getElementById('empleadoModal').classList.add('show');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al cargar el empleado', 'error');
        }
    }

    async saveEmpleado() {
        const nombre = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const usuario = document.getElementById('usuario').value.trim();
        const rol = document.getElementById('rol').value;
        const password = document.getElementById('password').value;

        if (!nombre || !apellido || !usuario || !rol) {
            this.showAlert('Por favor completa todos los campos obligatorios', 'error');
            return;
        }

        if (!this.editingId && !password) {
            this.showAlert('La contraseña es obligatoria para nuevos empleados', 'error');
            return;
        }

        if (password && password.length < 6) {
            this.showAlert('La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }

        const empleadoData = {
            nombre,
            apellido,
            usuario,
            email: document.getElementById('email').value.trim(),
            telefono: document.getElementById('telefono').value.trim(),
            rol,
            activo: document.getElementById('activo').value
        };

        if (password) {
            empleadoData.password = password;
        }

        // Si es empleado, agregar permisos
        if (rol === 'empleado') {
            const val = id => { const el = document.getElementById(id); return (el && el.checked) ? 1 : 0; };
            empleadoData.permisos = {
                ver_productos:     val('perm_ver_productos'),
                crear_productos:   val('perm_crear_productos'),
                editar_productos:  val('perm_editar_productos'),
                eliminar_productos:val('perm_eliminar_productos'),
                
                ver_ventas:    val('perm_ver_ventas'),
                crear_ventas:  val('perm_crear_ventas'),
                cancelar_ventas:val('perm_cancelar_ventas'),
                
                ver_gastos:   val('perm_ver_gastos'),
                crear_gastos: val('perm_crear_gastos'),
                
                gestionar_caja: val('perm_gestionar_caja'),
                
                ver_empleados:  val('perm_ver_empleados'),
                crear_empleados:val('perm_crear_empleados'),
                
                ver_reportes: val('perm_ver_reportes'),

                // Restaurant (0 si no existe el elemento)
                ver_mesas:          val('perm_ver_mesas'),
                gestionar_mesas:    val('perm_gestionar_mesas'),
                ver_reservas:       val('perm_ver_reservas'),
                gestionar_reservas: val('perm_gestionar_reservas'),
                ver_cocina:         val('perm_ver_cocina'),
                gestionar_cocina:   val('perm_gestionar_cocina'),
                gestionar_carta:    val('perm_gestionar_carta'),
                // Ferretería (0 si no existe el elemento)
                ver_presupuestos:    val('perm_ver_presupuestos'),
                crear_presupuestos:  val('perm_crear_presupuestos'),
                aprobar_presupuestos:val('perm_aprobar_presupuestos'),
                ver_stock:           val('perm_ver_stock'),
                ver_proveedores:     val('perm_ver_proveedores'),
                gestionar_proveedores:val('perm_gestionar_proveedores'),
                ver_ordenes:         val('perm_ver_ordenes'),
                gestionar_ordenes:   val('perm_gestionar_ordenes'),
                // Supermercado
                gestionar_stock:     val('perm_gestionar_stock'),
                imprimir_etiquetas:  val('perm_imprimir_etiquetas'),
                // Peluquería
                ver_agenda:          val('perm_ver_agenda'),
                gestionar_agenda:    val('perm_gestionar_agenda'),
                ver_servicios:       val('perm_ver_servicios'),
                gestionar_servicios: val('perm_gestionar_servicios'),
                ver_clientes_pelu:   val('perm_ver_clientes_pelu'),
                gestionar_clientes_pelu: val('perm_gestionar_clientes_pelu'),
                // Gimnasio
                ver_socios:          val('perm_ver_socios'),
                gestionar_socios:    val('perm_gestionar_socios'),
                ver_clases:          val('perm_ver_clases'),
                gestionar_clases:    val('perm_gestionar_clases'),
                registrar_asistencias: val('perm_registrar_asistencias'),
                ver_pagos_gym:       val('perm_ver_pagos_gym'),
                registrar_pagos_gym: val('perm_registrar_pagos_gym'),
                // Canchas
                ver_reservas_canchas:    val('perm_ver_reservas_canchas'),
                gestionar_reservas_canchas: val('perm_gestionar_reservas_canchas'),
                ver_canchas:             val('perm_ver_canchas'),
                gestionar_canchas:       val('perm_gestionar_canchas'),
                ver_clientes_canchas:    val('perm_ver_clientes_canchas'),
                gestionar_clientes_canchas: val('perm_gestionar_clientes_canchas'),
                caja_canchas:            val('perm_caja_canchas'),
            };
        }
        try {
            const url = '../../api/empleados/index.php';
            const method = this.editingId ? 'PUT' : 'POST';
            
            if (this.editingId) {
                empleadoData.id = this.editingId;
            }

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(empleadoData)
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(
                    this.editingId ? 'Empleado actualizado correctamente' : 'Empleado creado correctamente',
                    'success'
                );
                this.closeModal();
                await this.loadEmpleados();
            } else {
                this.showAlert(data.message || 'Error al guardar el empleado', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al guardar el empleado', 'error');
        }
    }

    async deleteEmpleado(id) {
        if (!confirm('¿Estás seguro de que deseas eliminar este empleado? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const response = await fetch('../../api/empleados/index.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ id })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Empleado eliminado correctamente', 'success');
                await this.loadEmpleados();
            } else {
                this.showAlert(data.message || 'Error al eliminar el empleado', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error al eliminar el empleado', 'error');
        }
    }

    showAlert(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass}`;
        alertDiv.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 10000; min-width: 300px; animation: slideIn 0.3s ease;';
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alertDiv.remove(), 300);
        }, 3000);
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('empleadoModal');
    if (event.target === modal) {
        empleadosModule.closeModal();
    }
}
