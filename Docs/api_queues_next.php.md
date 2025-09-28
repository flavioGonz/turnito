# api/queues_next.php

Ruta: api/queues_next.php
Tipo: PHP (API - llamar siguiente)

Resumen:
- Busca el próximo ticket `waiting` mayor que `current_number` o el menor `waiting` si no hay mayor.
- Actualiza `queues.current_number` y marca el ticket como `called` con `called_at`.
- Usa transacción y `FOR UPDATE` para evitar condiciones de carrera.
- Devuelve `{ok:true,current: N, prefix, pad, label}`.

Notas y sugerencias:
- Buen uso de bloqueo para garantizar consistencia.
- Manejar respuestas cuando no hay siguientes disponibles (`ok:false` con error).

---
Generado automáticamente.