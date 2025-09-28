# pantalla.php

Ruta: pantalla.php
Tipo: PHP (pantalla pública - cartelería)

Resumen:
- Genera la vista pública que muestra el "turno actual", la lista de "esperando" y la galería rotativa de publicidad.
- Incluye estilos y animaciones CSS para muchas variantes (kenburns, pan, zoom, efectos "Minimamente-like").
- Consume APIs JSON: `/api/queues_state.php`, `/api/ads.php`, `/api/marquee.php`, `/api/logo.php`.
- Contiene lógica JS para rotación de anuncios, reproducción de chime, actualización periódica del estado, y manejo de audio fallback.

Elementos detectados:
- Parámetros GET: `queue_id`, `title`, `next`, `logo`, `marquee`.
- Constantes JS: `QUEUE_ID`, `NEXT_MAX`, `API_BASE`.
- Funciones JS: `bootTicker`, `fetchLogo`, `fetchMarquee`, `loadState`, `playChime`, entre otras.

Notas y sugerencias:
- El CSS y JS están embebidos; extraerlos a `assets/` ayuda a cache y mantenimiento.
- Verificar políticas de autoplay en navegadores (audio). Ya incluye fallback por beep si play falla.
- Asegurarse que las rutas a `/turnero/assets/...` sean correctas en despliegues que no usen esa base.

---
Generado automáticamente.