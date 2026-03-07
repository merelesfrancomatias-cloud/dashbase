# Database Migrations

Las migraciones están numeradas secuencialmente y deben ejecutarse **en orden**.

## Cómo ejecutar

```bash
# Usando MySQL CLI
mysql -h HOST -u USER -p DATABASE < database/migrations/001_schema_base.sql
mysql -h HOST -u USER -p DATABASE < database/migrations/002_perfil_negocio_tenant.sql

# O con el PHP de XAMPP (desde la raíz del proyecto)
/Applications/XAMPP/bin/php database/migrate.php
```

## Listado de migraciones

| Archivo | Descripción |
|---|---|
| `001_schema_base.sql` | Schema canónico completo — reemplaza todos los .sql anteriores |
| `002_perfil_negocio_tenant.sql` | Agrega `negocio_id` a `perfil_negocio` para multi-tenant |

## Reglas

1. **Nunca modificar** una migración ya ejecutada en producción.
2. Para cambios nuevos, crear `003_...sql`, `004_...sql`, etc.
3. El bloque `[B]` en cada migración es para bases de datos **existentes** — leer los comentarios antes de ejecutar.
