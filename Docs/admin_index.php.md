# admin/index.php

Ruta: admin/index.php
Tipo: PHP (panel de administración)

Resumen:

- Panel de administración con autenticación de sesión (login simple que compara contraseña contra `ADMIN_PASSWORD` o `ADMIN_PASSWORD_DEFAULT`).
- Maneja listados de archivos multimedia activos/archivados, marcas de tiempo, logos, configuración de marquesina y animaciones.
- Contiene utilidades internas como `list_media()` y `h()` para escapar HTML.
- Genera HTML con pestañas (Publicidad, Archivo, Marquesina, Logos) y formularios para subir archivos, aplicar animaciones, archivar, restaurar, etc.

Comentarios / Bloques de cabecera:

- Pequeño comentario al inicio describiendo el archivo.

Funciones / elementos detectados:

- `admin_check()` (gestiona login y sesión)
- `list_media($dir)` (lista archivos de un directorio con metadatos)
- `h($s)` (escape HTML)
- Variables: `$adsActive`, `$adsArchive`, `$adsPublic`, `$marqueeTxt`, `$logosDir`, `$logosPub`, `$brandCfgFile`, `$animCfgDir`, `$animCfgFile`.

Notas y sugerencias:

- El sistema de login usa contraseña hardcodeada por defecto; mover a un sistema más seguro (env vars, usuarios, hash de contraseñas).
- Proteger endpoints críticos con CSRF tokens en formularios.
- Separar la lógica de datos (listado, operaciones) y la vista HTML para facilitar mantenimiento.

---

Generado automáticamente.
