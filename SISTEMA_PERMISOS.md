# Sistema de Permisos Granular - DASH4

## 📋 Descripción General

Sistema completo de gestión de permisos granulares para empleados, permitiendo al administrador controlar exactamente qué puede hacer cada empleado en el sistema.

## 🎯 Características Implementadas

### 1. **Roles de Usuario**
- **Administrador**: Acceso completo a todas las funciones
- **Empleado**: Permisos personalizables según lo que defina el admin

### 2. **Módulos de Permisos**

#### 📦 Productos
- ✅ Ver productos
- ✅ Crear productos
- ✅ Editar productos
- ✅ Eliminar productos

#### 🛒 Ventas
- ✅ Ver ventas
- ✅ Crear ventas (POS)
- ✅ Cancelar ventas

#### 💰 Gastos
- ✅ Ver gastos
- ✅ Registrar gastos

#### 💵 Caja
- ✅ Abrir/Cerrar caja

#### 👥 Empleados
- ✅ Ver empleados
- ✅ Crear/Editar empleados

#### 📊 Reportes
- ✅ Ver reportes y análisis

#### 📋 Pedidos
- ✅ Ver pedidos
- ✅ Gestionar pedidos

## 🗄️ Estructura de Base de Datos

### Tabla: `usuarios`
```sql
- id
- negocio_id
- nombre
- apellido
- usuario (para login)
- email
- password (bcrypt)
- telefono
- rol (admin/empleado)
- activo (1/0)
- foto
- ultimo_acceso
- fecha_creacion
- fecha_actualizacion
```

### Tabla: `permisos`
```sql
- id
- usuario_id (FK a usuarios)
- ver_productos (TINYINT 0/1)
- crear_productos (TINYINT 0/1)
- editar_productos (TINYINT 0/1)
- eliminar_productos (TINYINT 0/1)
- ver_ventas (TINYINT 0/1)
- crear_ventas (TINYINT 0/1)
- cancelar_ventas (TINYINT 0/1)
- ver_pedidos (TINYINT 0/1)
- gestionar_pedidos (TINYINT 0/1)
- ver_gastos (TINYINT 0/1)
- crear_gastos (TINYINT 0/1)
- ver_empleados (TINYINT 0/1)
- crear_empleados (TINYINT 0/1)
- ver_reportes (TINYINT 0/1)
- gestionar_caja (TINYINT 0/1)
```

## 🔧 API Endpoints

### GET `/api/empleados/index.php`
**Listar empleados**
```javascript
// Query params opcionales:
- search: Buscar por nombre, apellido, usuario, email, teléfono
- rol: Filtrar por rol (admin/empleado)
- estado: Filtrar por estado

// Response:
{
    "success": true,
    "message": "Empleados obtenidos",
    "data": [...]
}
```

### GET `/api/empleados/index.php?id={id}`
**Obtener empleado específico con permisos**
```javascript
// Response:
{
    "success": true,
    "message": "Empleado encontrado",
    "data": {
        "id": 1,
        "nombre": "Juan",
        "apellido": "Pérez",
        "usuario": "jperez",
        "email": "juan@example.com",
        "rol": "empleado",
        "permisos": {
            "ver_productos": 1,
            "crear_productos": 0,
            // ... todos los permisos
        }
    }
}
```

### POST `/api/empleados/index.php`
**Crear nuevo empleado**
```javascript
{
    "nombre": "Juan",
    "apellido": "Pérez",
    "usuario": "jperez",
    "email": "juan@example.com",
    "telefono": "1234567890",
    "password": "password123",
    "rol": "empleado",
    "activo": 1,
    "permisos": {
        "ver_productos": 1,
        "crear_productos": 0,
        // ... todos los permisos deseados
    }
}
```

### PUT `/api/empleados/index.php`
**Actualizar empleado existente**
```javascript
{
    "id": 1,
    "nombre": "Juan",
    "apellido": "Pérez García",
    "usuario": "jperez",
    // ... otros campos opcionales
    "permisos": {
        // Permisos actualizados
    }
}
```

### DELETE `/api/empleados/index.php`
**Eliminar empleado**
```javascript
{
    "id": 1
}
```

## 💻 Frontend - Modal de Empleados

### Secciones del Formulario

1. **Información Personal**
   - Nombre
   - Apellido
   - Email
   - Teléfono

2. **Acceso al Sistema**
   - Usuario (para login)
   - Contraseña
   - Rol (Admin/Empleado)
   - Estado (Activo/Inactivo)

3. **Permisos** (Solo visible para rol Empleado)
   - Agrupados por módulo con colores distintivos
   - Checkboxes para cada permiso específico

### JavaScript - Funciones Principales

```javascript
// Mostrar/ocultar sección de permisos según el rol
togglePermisos()

// Establecer permisos por defecto para nuevos empleados
setDefaultPermisos()

// Abrir modal para nuevo empleado
openModal()

// Abrir modal para editar empleado (carga permisos existentes)
editEmpleado(id)

// Guardar empleado con permisos
saveEmpleado()
```

## 🎨 Diseño UI

### Colores por Módulo
- 💜 **Productos**: #667eea (Morado)
- 💚 **Ventas**: #00C9A7 (Verde)
- ❤️ **Gastos**: #FF4444 (Rojo)
- 🧡 **Caja**: #FFA500 (Naranja)
- 💗 **Empleados**: #f5576c (Rosa)
- 💙 **Reportes**: #4facfe (Azul)
- 💜 **Pedidos**: #9b59b6 (Morado oscuro)

## 📝 Casos de Uso

### Ejemplo 1: Empleado de Ventas
```javascript
{
    "rol": "empleado",
    "permisos": {
        "ver_productos": 1,
        "crear_productos": 0,
        "editar_productos": 0,
        "eliminar_productos": 0,
        "ver_ventas": 1,
        "crear_ventas": 1,      // ✅ Puede vender
        "cancelar_ventas": 0,
        "gestionar_caja": 1,     // ✅ Puede abrir/cerrar caja
        "ver_gastos": 0,
        "crear_gastos": 0,
        "ver_empleados": 0,
        "crear_empleados": 0,
        "ver_reportes": 0
    }
}
```

### Ejemplo 2: Encargado de Inventario
```javascript
{
    "rol": "empleado",
    "permisos": {
        "ver_productos": 1,
        "crear_productos": 1,    // ✅ Puede agregar productos
        "editar_productos": 1,   // ✅ Puede editar stock/precios
        "eliminar_productos": 1, // ✅ Puede eliminar productos
        "ver_ventas": 1,
        "crear_ventas": 0,
        "ver_gastos": 1,
        "crear_gastos": 1,       // ✅ Puede registrar compras
        "ver_reportes": 1        // ✅ Puede ver reportes de inventario
    }
}
```

### Ejemplo 3: Gerente
```javascript
{
    "rol": "empleado",
    "permisos": {
        // Todos los permisos excepto crear empleados
        "ver_productos": 1,
        "crear_productos": 1,
        "editar_productos": 1,
        "eliminar_productos": 1,
        "ver_ventas": 1,
        "crear_ventas": 1,
        "cancelar_ventas": 1,    // ✅ Puede cancelar ventas
        "ver_gastos": 1,
        "crear_gastos": 1,
        "gestionar_caja": 1,
        "ver_empleados": 1,
        "crear_empleados": 0,    // ❌ Solo admin crea empleados
        "ver_reportes": 1        // ✅ Acceso a reportes
    }
}
```

## 🔒 Seguridad

- ✅ Solo administradores pueden crear/editar empleados
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Validación de usuario único por negocio
- ✅ Verificación de sesión en todas las operaciones
- ✅ No se puede eliminar el usuario actual
- ✅ Admin siempre tiene todos los permisos

## 📱 Responsive

El modal de empleados es completamente responsive:
- Scroll vertical automático para contenido extenso
- Máximo 90vh de altura
- Adaptable a diferentes tamaños de pantalla

## 🚀 Próximas Mejoras Sugeridas

1. **Validación en Frontend**: Verificar permisos antes de mostrar opciones
2. **Middleware de Permisos**: Validar permisos en cada endpoint del API
3. **Logs de Actividad**: Registrar cambios de permisos
4. **Permisos Temporales**: Asignar permisos con fecha de expiración
5. **Plantillas de Permisos**: Crear perfiles predefinidos (Cajero, Vendedor, Gerente, etc.)

---

**Fecha de Implementación**: 31 de Octubre, 2025
**Versión**: 1.0.0
