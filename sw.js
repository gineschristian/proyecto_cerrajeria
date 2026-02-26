const CACHE_NAME = 'pinos-v1';

// No ponemos lista de archivos por ahora para evitar el error 404
self.addEventListener('install', (event) => {
    console.log('SW instalado correctamente');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('SW activado');
});

self.addEventListener('fetch', (event) => {
    // Vacío para permitir navegación normal
});