# api/queues_prev.php

Ruta: api/queues_prev.php
Tipo: PHP (API - volver al anterior)

Resumen:
- Selecciona el `MAX(number)` menor que el `current_number` y lo establece como nuevo `current_number`, marcándolo `called`.
- Usa `FOR UPDATE` y transacciones para evitar condiciones de carrera.

Notas y sugerencias:
- Retorna `ok:false` si no hay anterior.
- Consistencia similar a `queues_next.php`.

---
Generado automáticamente.