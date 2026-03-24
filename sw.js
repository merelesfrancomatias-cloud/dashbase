/**
 * sw.js — Service Worker de DASH
 * Estrategia:
 *   - JS/CSS: Network-first (siempre busca versión nueva, cache como fallback offline)
 *   - Imágenes/fuentes: Cache-first (cambian poco)
 *   - PHP/APIs: Network-only (nunca cachear)
 */

const CACHE_NAME  = 'dash-v4';
const CACHE_ASSETS = [
    '/DASHBASE/offline.html',
    '/DASHBASE/public/img/DASHLOGOSF.png',
    '/DASHBASE/public/img/Splash.png',
    '/DASHBASE/public/img/logo.png',
    '/DASHBASE/public/img/no-image.svg',
    '/DASHBASE/manifest.json',
];

// ── Install: cachear solo assets esenciales (imágenes, offline page) ─────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return Promise.allSettled(
                CACHE_ASSETS.map(url =>
                    cache.add(url).catch(() => { /* ignorar si falla */ })
                )
            );
        }).then(() => self.skipWaiting())
    );
});

// ── Activate: limpiar caches viejos y tomar control inmediato ─────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// ── Fetch: estrategia por tipo de recurso ─────────────────────────────────────
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Solo interceptar mismo origen
    if (url.origin !== location.origin) return;

    const isAPI    = url.pathname.includes('/api/');
    const isPHP    = url.pathname.endsWith('.php');
    const isJSCSS  = /\.(css|js)$/.test(url.pathname);
    const isImage  = /\.(png|jpg|jpeg|svg|gif|ico|woff2?)$/.test(url.pathname);

    if (isAPI || isPHP) {
        // Network-only para PHP y APIs — nunca cachear respuestas del servidor
        event.respondWith(
            fetch(event.request).catch(() => {
                if (isAPI) {
                    return new Response(
                        JSON.stringify({ success: false, message: 'Sin conexión al servidor' }),
                        { headers: { 'Content-Type': 'application/json' } }
                    );
                }
                return caches.match('/DASHBASE/offline.html');
            })
        );

    } else if (isJSCSS) {
        // Network-first para JS y CSS — siempre busca la versión más nueva
        // Solo usa cache si no hay red (modo offline)
        event.respondWith(
            fetch(event.request).then(res => {
                // Guardar copia fresca en cache para uso offline
                const clone = res.clone();
                caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                return res;
            }).catch(() => {
                // Sin red: servir desde cache si existe
                return caches.match(event.request);
            })
        );

    } else if (isImage) {
        // Cache-first para imágenes (cambian poco)
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request).then(res => {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return res;
                });
            })
        );
    }
});
