# admin/logo_save.php

Ruta: admin/logo_save.php
Tipo: PHP (guardar configuración de logos)

Resumen:
- Recibe `POST` con `global_logo` y `q_logo` (por-cola) y escribe `storage/branding/config.json` con la selección.
- Valida que los archivos existan en `storage/branding/logos` antes de guardarlos.

Notas y sugerencias:
- Proteger endpoint con autenticación (requiere session pero actualmente commented auth lines).
- Añadir validaciones y feedback para el usuario (por ejemplo, mostrar si algún logo fue inválido).

---
Generado automáticamente.