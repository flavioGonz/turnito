# admin/logo_upload.php

Ruta: admin/logo_upload.php
Tipo: PHP (subida de logos)

Resumen:
- Procesa la subida de un único archivo `logo` y lo guarda en `storage/branding/logos` y copia a `public/media/logos`.
- Realiza validación básica de extensión (`png`, `jpg`, `jpeg`, `webp`, `svg`) y normaliza el nombre.
- Redirige a `index.php#tab-logos` al finalizar.

Notas y sugerencias:
- Actualmente `session` está iniciado pero `ensure_auth()` está comentado; revisar si se desea proteger este endpoint.
- Añadir comprobación de tipo MIME y límites de tamaño.
- Manejar colisiones de nombres (puede sobrescribir archivos existentes).

---
Generado automáticamente.