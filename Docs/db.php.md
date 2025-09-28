# db.php

Ruta: db.php
Tipo: PHP (conexión a base de datos)

Resumen:
- Define la conexión PDO a MySQL usando dsn `mysql:host=127.0.0.1;dbname=turnero;charset=utf8mb4`.
- Usuario `root` y contraseña vacía (por defecto XAMPP).
- Configura opciones PDO: excepciones, FETCH_ASSOC, sin emulación de prepares y comando de inicialización `SET NAMES utf8mb4`.
- Crea `$pdo = new PDO($dsn, $user, $pass, $options);`.

Comentarios / Bloques de cabecera:
- No hay cabecera, pero se recomienda proteger este archivo (no incluirlo directamente desde web root si fuera posible).

Funciones / elementos detectados:
- Variable `$pdo` (instancia PDO) exportada a scope global cuando se incluye el archivo.

Notas y sugerencias:
- En producción, no usar `root` ni contraseña vacía; usar credenciales enviadas desde variables de entorno o un archivo de configuración fuera del webroot.
- Considerar manejo de errores al instanciar PDO (try/catch) y logging seguro.
- Asegurar permisos correctos y no exponer este archivo públicamente.

---
Generado automáticamente.