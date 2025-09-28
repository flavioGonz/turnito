# includes/auth.php

Ruta: includes/auth.php
Tipo: PHP (autenticación sencilla para admin)

Resumen:
- Implementa una verificación de sesión simple para acceso admin.
- Comprueba `$_SESSION['ok']` y permite login mediante `POST` con `password` comparado contra `config.php` `ADMIN_PASSWORD`.
- Muestra formulario de login si no autenticado.

Elementos detectados:
- `ensure_auth()` función que controla el flujo de autenticación.
- Uso de `session_start()` y `require __DIR__.'/config.php'`.

Notas y sugerencias:
- Usar hashing y comparar con `hash_equals` para mitigar timing attacks (en parte ya recomendado en readme).
- Considerar implementación más robusta (usuarios, roles, tokens) o protección por .htaccess en entornos compartidos.

---
Generado automáticamente.