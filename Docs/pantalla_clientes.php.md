# pantalla_clientes.php

Ruta: pantalla_clientes.php
Tipo: PHP (pantalla para clientes)

Resumen:
- Versión pensada para clientes con tablas de "siguientes" y "llamados" y mayor foco en lectura (contraste claro o base.css).
- Usa `assets/js/pantalla_clientes.js` como módulo para la lógica.
- Parámetros: `queue_id`, `title`, `hist`, `next`.

Elementos detectados:
- Sprite SVG inline para iconos.
- Exporta `window.PANTALLA_CONFIG` al JS modular.
- Estructura HTML con `#turno`, `#tblNext`, `#tblHist`, y `#ads`.

Notas y sugerencias:
- Asegurarse que `assets/js/pantalla_clientes.js` exista y sea importable por navegadores usados en dispositivos de la pantalla.
- Considerar fallback para navegadores antiguos (no soportan modules).

---
Generado automáticamente.