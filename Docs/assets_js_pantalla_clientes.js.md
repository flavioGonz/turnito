# assets/js/pantalla_clientes.js

Ruta: assets/js/pantalla_clientes.js
Tipo: JavaScript module (lógica para la pantalla de clientes)

Resumen:
- Exporta `boot()` que inicializa `loadState()` y `loadAds()` y configura intervalos.
- Realiza polling a `/api/queues_state.php` y renderiza tablas `#tblNext` y `#tblHist` y el número actual.
- Gestiona rotación de anuncios: crea elementos `adItem` con `img` o `video`, maneja temporizadores y eventos `onended`.

Notas y sugerencias:
- Fácilmente reutilizable en la pantalla de clientes; buena separación de responsabilidades.
- Manejar fallbacks si `ads.php` devuelve error o si hay muchos elementos.

---
Generado automáticamente.