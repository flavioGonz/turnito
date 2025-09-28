# api/migrate.php

Ruta: api/migrate.php
Tipo: PHP (migración de base de datos)

Resumen:
- Script idempotente que crea/ajusta las tablas `queues` y `tickets` y sus columnas/índices en MySQL usando PDO (`db.php`).
- No utiliza transacciones para DDL complejas pero intenta añadir columnas/índices si faltan.
- Inserta seed para la cola #1 si no existe.

Notas y sugerencias:
- Ejecutarlo en despliegues iniciales; revisar privilegios de DB.
- Mantener copia de seguridad antes de cambios en producción.

---
Generado automáticamente.