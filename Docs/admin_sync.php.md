# admin/sync.php

Ruta: admin/sync.php
Tipo: PHP (resincronizar copias públicas de publicidad)

Resumen:
- Copia archivos desde `storage/active` a `public/media/ads` si no existen o si el activo es más nuevo.
- Protegido con `includes/auth.php`.
- Crea directorios si hacen falta via `ensure_dirs()`.

Notas y sugerencias:
- Operación idempotente y útil para reparar copias públicas tras restauraciones o despliegues.
- Agregar logging y/o salida al usuario del resultado (archivos copiados, errores).

---
Generado automáticamente.