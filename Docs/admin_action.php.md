# admin/action.php

Ruta: admin/action.php
Tipo: PHP (acciones CRUD para archivos)

Resumen:

- Controla operaciones sobre archivos de publicidad: `archive`, `restore`, `delete`.
- Usa `includes/auth.php` para proteger accesos y `includes/helpers.php` para utilidades (por ejemplo `unique_path`).
- Opera sobre tres ubicaciones: `STORAGE_ACTIVE`, `STORAGE_ARCHIVE`, `PUBLIC_ADS_PATH`.
- Al finalizar redirige a `index.php`.

Operaciones detectadas:

- `archive`: mueve de `active` a `archive` y elimina la copia pública.
- `restore`: mueve del `archive` a `active`, luego copia a `public`.
- `delete`: borra archivo en `active` (y `public`) o en `archive` según `scope`.

Notas y sugerencias:

- Verificar permisos de filesystem; agregar manejo de errores y logs en caso de fallas.
- Añadir validación extra al parámetro `file` para evitar traversal (usa `basename()` ya, lo cual es bueno).
- Considerar CSRF tokens en formularios que llaman a estas acciones.

---

Generado automáticamente.
