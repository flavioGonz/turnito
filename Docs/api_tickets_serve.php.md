# api/tickets_serve.php

Ruta: api/tickets_serve.php
Tipo: PHP (API - marcar ticket servido y avanzar)

Resumen:
- Marca el `current_number` como `served` y, si hay siguiente `waiting`, lo marca `called` y actualiza `current_number` al siguiente.
- Usa transacción y `FOR UPDATE` para consistencia.
- Devuelve `{ok:true, served: N, advanced: bool, current: newCurrent, prefix, pad, label}`.

Notas y sugerencias:
- Buen manejo de concurrencia; validar permisos de quien puede invocar este endpoint.

---
Generado automáticamente.