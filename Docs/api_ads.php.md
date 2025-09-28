# api/ads.php

Ruta: api/ads.php
Tipo: PHP (API pública - lista de anuncios)

Resumen:
- Retorna JSON con la lista de archivos en `public/media/ads`, con metadatos como `url`, `type`, `duration_sec`, `size`, `mtime`, `w`, `h`, y `anim` (si aplica).
- Detecta `basePath` automáticamente para construir URLs correctas cuando el proyecto no está en la raíz.
- Ordena ítems por `mtime` (recientes primero).

Notas y sugerencias:
- Llama a `getimagesize()` para imágenes; esto puede ser costoso si hay muchas imágenes grandes.
- Considerar paginación o cache si el directorio es muy grande.

---
Generado automáticamente.