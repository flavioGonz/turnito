# admin/marquee_save.php

Ruta: admin/marquee_save.php
Tipo: PHP (guardar marquesina)

Resumen:
- Recibe `POST[text]`, escribe su contenido en `storage/marquee.txt` y redirige a `index.php#tab-marquee`.
- Protegido con `includes/auth.php`.

Notas y sugerencias:
- Validar/sanitizar el texto si se planea permitir marcados especiales; actualmente guarda el texto tal cual.
- Considerar versionado o backup del texto anterior al sobrescribir.

---
Generado automáticamente.