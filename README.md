# DASHBASE

Sistema de gestión comercial multi-rubro (inventario, ventas, caja, gastos, empleados, reportes y módulos verticales como restaurant, gym, peluquería, hospedaje, etc.).

## Requisitos

- PHP 8.x (recomendado 8.1+)
- MySQL/MariaDB
- Apache (XAMPP o similar)

## Puesta en marcha (local)

1. Copiar el proyecto en `htdocs` (ej: `C:/xampp/htdocs/dashbase`).
2. Crear base de datos (ej: `dashbase_local`).
3. Importar un esquema inicial desde `config/` (según entorno) o usar migraciones.
4. Ajustar conexión en `config/database.php`.
5. Abrir en navegador: `http://localhost/dashbase`.

## Migraciones

- Directorio: `database/migrations/`
- Runner: `database/migrate.php`

Reglas:

1. Ejecutar en orden numérico.
2. No modificar migraciones ya aplicadas.
3. Nuevos cambios: crear archivos `00X_nombre.sql`.

## Estructura principal

- `api/`: endpoints por módulo
- `views/`: interfaces por módulo
- `public/css`, `public/js`: assets frontend
- `config/`: conexión y SQL base
- `database/`: migraciones y utilidades
- `superadmin/`: panel administrativo global

## Módulos funcionales

- Dashboard
- Productos y categorías
- Ventas e historial
- Caja
- Gastos
- Empleados y permisos
- Reportes
- Restaurant (mesas, cocina, reservas)
- Módulos verticales: gym, peluquería, hospedaje, farmacia, etc.

## Permisos y seguridad

- Roles: `admin` y `empleado`
- Permisos granulares por módulo (tabla `permisos`)
- Sesión obligatoria en APIs protegidas
- Hash de contraseñas
- Queries preparadas (PDO)

## Convenciones importantes

- Métodos de pago usan valores normalizados (`efectivo`, `tarjeta_debito`, `tarjeta_credito`, `transferencia`, etc.).
- En gastos se usa `fecha_gasto` y categoría por enum/código (no `categoria_id` en implementaciones legacy).
- En ventas/historial se usa `fecha_venta`.

## Impresión térmica 80mm

- Compatible con impresoras térmicas estándar ESC/POS.
- Configurar papel de 80mm en el driver del sistema.
- Recomendado imprimir desde Chrome/Edge con márgenes mínimos y sin encabezados/pies.
- Configuración del ticket: `public/js/ticket-config.js`.

## UI móvil / dark mode

- Header y navegación móvil optimizados.
- Componentes principales preparados para modo oscuro.
- En restaurant/mesas, el estado ocupada usa paleta adaptada a dark mode.

## Mantenimiento

- Logs: revisar carpeta `logs/` cuando sea necesario.
- Archivos de subida: `public/uploads/`.
- Antes de despliegue, validar conexión DB, sesiones y permisos de escritura.

## Nota

Este archivo es la única documentación activa del proyecto.
