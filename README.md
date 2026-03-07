# DASH CRM - Sistema de Gestión para Comercios

Sistema completo de gestión para comercios con inventario, ventas, caja, pedidos y más.

## 🚀 Características

- **Gestión de Inventario**: Control de productos, categorías y stock
- **Punto de Venta**: Sistema de ventas rápido y eficiente
- **Control de Caja**: Apertura y cierre de caja por usuario
- **Pedidos Online**: Catálogo virtual con carrito de compras
- **Gestión de Empleados**: Control de usuarios y permisos
- **Reportes y Estadísticas**: Dashboard con métricas del negocio
- **Responsive Design**: Funciona en computadoras, tablets y móviles

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- XAMPP, WAMP o similar

## 🛠️ Instalación

1. Clonar o descargar el proyecto en la carpeta `htdocs` de XAMPP

2. Importar la base de datos:
   - Abrir phpMyAdmin (http://localhost/phpmyadmin)
   - Crear una nueva base de datos llamada `dash_crm`
   - Importar el archivo `config/database_schema.sql`

3. Configurar la conexión a la base de datos en `config/Database.php`:
   ```php
   private $host = "localhost";
   private $db_name = "dash_crm";
   private $username = "root";
   private $password = "";
   ```

4. Acceder a la aplicación:
   - URL: http://localhost/DASH
   - Credenciales: Se proporcionarán al momento de la instalación

## 📁 Estructura del Proyecto

```
/DASH
├── /api                    # Backend PHP
│   ├── /auth              # Autenticación
│   ├── /utils             # Utilidades
├── /config                # Configuración
│   ├── Database.php       # Conexión a BD
│   ├── config.php         # Configuración general
│   └── database_schema.sql # Esquema de BD
├── /public                # Recursos públicos
│   ├── /css               # Estilos
│   ├── /js                # JavaScript
│   └── /uploads           # Archivos subidos
├── /views                 # Vistas HTML
│   └── /dashboard         # Panel de control
├── .htaccess             # Configuración Apache
└── index.php             # Login
```

## 🎨 Tecnologías

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+ con PDO
- **Base de Datos**: MySQL
- **Diseño**: Estilo iOS moderno
- **API**: REST con JSON

## 🔐 Seguridad

- Contraseñas encriptadas con bcrypt
- Protección contra SQL Injection con PDO
- Validación de sesiones
- Control de permisos por rol

## 📱 Próximas Funcionalidades

- App móvil con Capacitor
- Integración con WhatsApp
- Métodos de pago online
- Multi-negocios
- Reportes avanzados

## 👤 Acceso al Sistema

Las credenciales de acceso se configuran durante la instalación.

⚠️ **Importante**: Cambia las credenciales después del primer inicio de sesión.

## 📄 Licencia

Proyecto privado - Todos los derechos reservados

## 🤝 Soporte

Para soporte y consultas, contactar al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Fecha**: Octubre 2025
