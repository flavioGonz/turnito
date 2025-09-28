# includes/helpers.php

Ruta: includes/helpers.php
Tipo: PHP (funciones auxiliares)

Resumen:
- Contiene funciones utilitarias usadas por el sistema:
  - `slugify($name)`: genera un slug seguro para nombres de archivo (translitera, limpia, baja a minúsculas).
  - `ext($f)`: devuelve la extensión en minúsculas.
  - `is_allowed($e,$cfg)`: comprueba si una extensión está en `ALLOWED_EXT` del config.
  - `ensure_dirs($cfg)`: crea directorios necesarios si no existen.
  - `unique_path($dir,$filename)`: genera un nombre único si el archivo existe en el directorio.

Comentarios / Bloques de cabecera:
- No hay comentarios; funciones sencillas y autoexplicativas.

Funciones / elementos detectados:
- `slugify`, `ext`, `is_allowed`, `ensure_dirs`, `unique_path`.

Notas y sugerencias:
- Añadir validaciones y manejo de errores para operaciones de filesystem (mkdir, file_exists) por si hay permisos insuficientes.
- Considerar nombres de funciones en un namespace o clase utilitaria para evitar colisiones globales.

---
Generado automáticamente.