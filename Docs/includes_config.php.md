# includes/config.php

Ruta: includes/config.php
Tipo: PHP (configuración)

Resumen:
- Archivo que retorna un array de configuración con claves como `ADMIN_PASSWORD`, `BASE_URL`, rutas a `PUBLIC_ADS_PATH`, `STORAGE_ACTIVE`, `STORAGE_ARCHIVE`, `MAX_UPLOAD_MB` y `ALLOWED_EXT`.
- `BASE_URL` se construye a partir de `$_SERVER`.

Comentarios / Bloques de cabecera:
- No comentarios visibles; el archivo devuelve el array directamente.

Funciones / elementos detectados:
- Es un archivo de configuración (retorna array). No define funciones o clases.

Notas y sugerencias:
- Evitar incluir contraseñas en repositorio; mover `ADMIN_PASSWORD` a variable de entorno o `.env` fuera del control de versiones.
- Verificar que las rutas generadas con `__DIR__` son correctas en el entorno de despliegue.

---
Generado automáticamente.