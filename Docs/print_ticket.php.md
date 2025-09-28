# print_ticket.php

Ruta: print_ticket.php
Tipo: PHP (plantilla de impresión de ticket)

Resumen:
- Genera una página minimalista para imprimir tickets con tamaño de papel configurado para impresoras de tickets (58mm).
- Parámetros GET: `prefix` y `n` para componer el ticket (ej: C-027).
- Auto-invoca `window.print()` tras 200ms.

Elementos detectados:
- Estilos inline para tamaño de página y tipografía.
- Contenido: nombre de local (hardcoded como CARNICERÍA), número grande, fecha/hora, mensaje de agradecimiento.

Notas y sugerencias:
- Hacer configurable el nombre de local y el formato desde config o vía parámetros.
- Validar sanitización de GET (usa `preg_replace` y `htmlspecialchars` ya implementados).

---
Generado automáticamente.