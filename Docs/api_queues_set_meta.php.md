# api/queues_set_meta.php

Ruta: api/queues_set_meta.php
Tipo: PHP (API - actualizar metadatos de la cola)

Resumen:
- Permite actualizar `prefix`, `pad` y `logo` de una cola (`queues`), creando la cola si no existe.
- Devuelve la metadata actualizada.

Notas y sugerencias:
- Validaciones: `prefix` se normaliza a A-Z0-9 y `pad` se limita entre 2 y 4.
- Endpoint útil para configurar la apariencia de la pantalla desde UI.

---
Generado automáticamente.