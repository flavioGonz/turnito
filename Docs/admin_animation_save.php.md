# admin/animation_save.php

Ruta: admin/animation_save.php
Tipo: PHP (guardar animación por archivo)

Resumen:

- Guarda un mapeo de animaciones por archivo en `storage/ads/animations.json`.
- Valida que la animación solicitada sea una de las permitidas.
- Usa nombres de archivo tal cual y evita path traversal.

Notas y sugerencias:

- Considerar nombres normalizados de archivos (handle case sensitivity según FS).
- Proveer feedback si la animación no es válida o el archivo no existe.

---

Generado automáticamente.
