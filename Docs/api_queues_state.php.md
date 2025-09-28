# api/queues_state.php

Ruta: api/queues_state.php
Tipo: PHP (API - estado de la cola)

Resumen:
- Devuelve estado actual de la cola: `prefix`, `pad`, `current`, lista `next[]` de tickets waiting (limit por `next_limit`), `logo`, y `updated_at`.
- Si la cola no existe, la crea con valores por defecto.

Notas y sugerencias:
- Usado por `pantalla.php` y `pantalla_clientes.php` con polling frecuente.
- Asegurar índices en `tickets` para que las consultas `WHERE ... ORDER BY number ASC LIMIT` sean rápidas.

---
Generado automáticamente.