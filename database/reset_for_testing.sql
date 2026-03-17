-- Reset de base para pruebas
-- Objetivo: dejar la instancia sin negocios, usuarios ni datos operativos.
-- Mantiene tablas de catálogo/base para no romper altas iniciales.

SET FOREIGN_KEY_CHECKS = 0;

SET @db = DATABASE();

-- Tablas a conservar (catálogos/sistema)
SET @keep_tables = '''migrations'',''rubros'',''planes'',''superadmin_usuarios'',''superadmin_planes'',''superadmin_rubros''';

SET @sql = (
  SELECT GROUP_CONCAT(
    CONCAT('TRUNCATE TABLE `', table_name, '`')
    SEPARATOR '; '
  )
  FROM information_schema.tables
  WHERE table_schema = @db
    AND table_type = 'BASE TABLE'
    AND table_name NOT IN (
      'migrations',
      'rubros',
      'planes',
      'superadmin_usuarios',
      'superadmin_planes',
      'superadmin_rubros'
    )
);

SET @sql = IFNULL(CONCAT(@sql, ';'), 'SELECT 1;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- Verificación rápida
SELECT 'negocios' AS tabla, COUNT(*) AS filas FROM negocios
UNION ALL
SELECT 'usuarios', COUNT(*) FROM usuarios;
