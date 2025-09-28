# api/reset_queue.php

Ruta: api/reset_queue.php
Tipo: PHP (API - resetear cola)

Resumen:
- Borra todos los tickets de una cola `DELETE FROM tickets WHERE queue_id=?` y pone `queues.current_number=0`.
- Retorna `{ok:true}`.

Notas y sugerencias:
- Proveer protección administrativa para evitar borrados accidentales (ej. token o autenticación).

---
Generado automáticamente.