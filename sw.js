const CACHE_NAME = 'pinos-v2'; // Cambiamos a v2 para forzar la actualización
const urlsToCache = [
  './',
  './index.php',
  './manifest.json',
  './css/login.css',
  './img/logo_pwa_192.png',
  './img/logo_pwa_512.png',
  './img/logo.png'
];

// Instalación: Guardamos los iconos y archivos básicos en caché
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache abierta, guardando iconos...');
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting();
});

// Activación: Limpiamos cachés antiguas
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  console.log('SW activo y caché limpia');
});

// Fetch: Respondemos con la caché si existe, si no, vamos a la red
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});