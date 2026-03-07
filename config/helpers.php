<?php
/**
 * Devuelve la URL de un asset con ?v=<filemtime> para cache-busting automático.
 * Uso: asset('public/css/ventas.css')  →  /DASHBASE/public/css/ventas.css?v=1741234567
 *
 * @param string $path  Ruta relativa desde la raíz del proyecto (sin barra inicial)
 * @param string $base  Prefijo base (APP_BASE), por defecto vacío
 */
if (!function_exists('asset')) {
    function asset(string $path, string $base = ''): string
    {
        $root    = dirname(__DIR__); // raíz del proyecto
        $absPath = $root . '/' . ltrim($path, '/');
        $version = file_exists($absPath) ? filemtime($absPath) : time();
        return $base . '/' . ltrim($path, '/') . '?v=' . $version;
    }
}
