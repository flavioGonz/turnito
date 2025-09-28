# control.php

Ruta: control.php
Tipo: PHP (interfaz web - Control)

Resumen:
- Archivo que genera la interfaz de control (administración en tiempo real) para el sistema "Turnero".
- Contiene HTML, CSS inline y JavaScript embebido (parte visible en las primeras 200 líneas).
- Define la estructura de la UI: topbar, columna izquierda para acciones, columna central con estado de cola, columna derecha con actividad, modal de preferencias.

Comentarios / Bloques de cabecera:
- No hay comentarios PHP de cabecera visibles en las líneas leídas.

Funciones / elementos detectados:
- Multiples botones con IDs: `nuevo`, `llamar`, `servido`, `atras`, `reset`, `help`.
- Elementos DOM con IDs para actualizar estado: `qName`, `qPrefix`, `qPad`, `connDot`, `connText`, `curDigits`, `chips`, `log`, `modalPrefs`, `inQueue`, `inPrefix`, `inPad`, `inNextLimit`.

Notas y sugerencias:
- El archivo mezcla presentación y lógica; si se desea escalabilidad, recomendar separar JS y CSS en archivos externos (`assets/`) y mantener PHP para endpoints/API.
- Revisar accesibilidad (etiquetas ARIA) para modales y botones.
- Validar la internacionalización si se planea soporte multi-idioma.


---
Generado automáticamente.