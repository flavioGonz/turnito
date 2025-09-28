# api/logo.php

Ruta: api/logo.php
Tipo: PHP (API pública - logo por cola)

Resumen:
- Lee `storage/branding/config.json` para devolver `global_logo` o `per_queue` según `queue_id`.
- Construye URL pública hacia `/public/media/logos` respetando `basePath`.

Salida JSON:
- { ok: true, queue_id: N, url: '/turnero/public/media/logos/logo.png' }

Notas y sugerencias:
- Si el archivo configurado no existe, devuelve `url: null`.
- Añadir headers `no-store` si se requiere siempre datos frescos.

---
Generado automáticamente.