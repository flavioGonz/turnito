# admin/upload.php

Ruta: admin/upload.php
Tipo: PHP (subida de archivos para publicidad)

Resumen:
- Procesa `$_FILES['files']` para mover archivos a `storage/active` y copiar a `public/media/ads`.
- Usa utilidades de `includes/helpers.php`: `slugify`, `ext`, `is_allowed`, `unique_path`, `ensure_dirs`.
- Respeta un tamaño máximo definido en `MAX_UPLOAD_MB` desde config.

Validaciones implementadas:
- Comprueba errores de subida `UPLOAD_ERR_OK`.
- Normaliza nombre con `slugify`.
- Filtra extensiones con `ALLOWED_EXT` y tamaño máximo.

Notas y sugerencias:
- Considerar reportar feedback al usuario (errores, archivos rechazados) en vez de redirigir sin mensaje.
- Validar tipos MIME además de la extensión para mayor seguridad.

---
Generado automáticamente.