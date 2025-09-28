# api/tickets.php

Ruta: api/tickets.php
Tipo: PHP (API - emitir ticket)

Resumen:
- Inserta un nuevo ticket con número `MAX(number)+1` por `queue_id` en estado `waiting`, de forma atómica.
- Devuelve `{ok:true, queue_id, number, prefix, pad, label}`.

Notas y sugerencias:
- Requiere bloqueo de la fila de `queues` para evitar duplicados; el script hace `FOR UPDATE` y transacción.
- Asegurar que la tabla `tickets` tenga índice y UNIQUE (queue_id, number).

---
Generado automáticamente.