# admin/marquee.php

Ruta: admin/marquee.php
Tipo: PHP (UI editar marquesina)

Resumen:

- Página del admin para editar el texto de la marquesina (`storage/marquee.txt`).
- Muestra un formulario con `textarea` que envía a `marquee_save.php`.
- Protegido con `includes/auth.php` y usa Bootstrap desde CDN para estilo.

Notas y sugerencias:

- Añadir validación de longitud o sanitización si se permite HTML (actualmente se muestra con `htmlspecialchars`).
- Ofrecer vista previa en vivo para el admin.

---

Generado automáticamente.
