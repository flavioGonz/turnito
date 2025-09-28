
# 📌 Sistema de Turnos — Turnero (README actualizado)

Este proyecto es un sistema **simple, moderno y modular** para gestionar turnos en locales físicos. Incluye:

* **Pantalla pública** de cartelería con turnos + publicidad + marquesina.
* **Panel de control** para emitir/llamar/servir/resetear turnos con atajos y estadísticas.
* **Backend de archivos** (galería y marquesina) y  **API de turnos en MySQL** .
* **Admin dark** (similar al front) para subir imágenes/videos, archivar y editar el texto de la marquesina.

> Ruta base asumida: `http://localhost/turnero` (carpeta del proyecto: `C:\xampp\htdocs\turnero`).

---

## ⚙️ Arquitectura rápida

<pre class="overflow-visible!" data-start="653" data-end="3043"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre!"><span><span>/turnero
├─ pantalla.php                    </span><span># Pantalla pública (turnos + publicidad + marquee)</span><span>
├─ control.php                     </span><span># Panel de operador (acciones, stats, atajos)</span><span>
├─ print_ticket.php                </span><span># (opcional) Plantilla de impresión del ticket</span><span>
├─ db.php                          </span><span># Conexión PDO a MySQL (una sola vez, para toda la API)</span><span>
├─ api/                            </span><span># Endpoints JSON (turnos + contenido)</span><span>
│  ├─ migrate.php                  </span><span># Migración idempotente (crea/ajusta tablas/índices/seed)</span><span>
│  ├─ queues_state.php             </span><span># Estado actual: {prefix, pad, current, next[]}</span><span>
│  ├─ tickets.php                  </span><span># Emitir ticket (crea waiting N+1)</span><span>
│  ├─ queues_next.php              </span><span># Llamar siguiente (marca called y avanza current)</span><span>
│  ├─ tickets_serve.php            </span><span># Marcar servido (y avanzar si hay waiting)</span><span>
│  ├─ queues_prev.php              </span><span># Volver al anterior (marca called)</span><span>
│  ├─ reset_queue.php              </span><span># Resetear cola (borra tickets, current=0)</span><span>
│  ├─ ads.php                      </span><span># Lista de imágenes/videos para la pantalla</span><span>
│  └─ marquee.php                  </span><span># Texto de la marquesina</span><span>
├─ admin/                          </span><span># Admin dark (galería + marquee + resincronizar)</span><span>
│  ├─ index.php                    </span><span># UI: pestañas Publicidad / Archivo / Marquesina</span><span>
│  ├─ upload.php                   </span><span># Subir imágenes/videos</span><span>
│  ├─ action.php                   </span><span># Archivar / Restaurar / Eliminar</span><span>
│  ├─ sync.php                     </span><span># Copiar activos a /public/media/ads si falta</span><span>
│  ├─ marquee_save.php             </span><span># Guardar texto de marquesina en storage</span><span>
│  └─ (assets Bootstrap via CDN)
├─ includes/
│  ├─ config.php                   </span><span># Config general (incluye ADMIN_PASSWORD)</span><span>
│  ├─ auth.php                     </span><span># Login mínimo del admin</span><span>
│  └─ helpers.php                  </span><span># Asegurar carpetas, utilidades</span><span>
├─ public/
│  ├─ index.php                    </span><span># Redirección a /turnero/api/ads.php</span><span>
│  └─ media/ads/                   </span><span># Copias públicas servibles (img/video)</span><span>
├─ storage/
│  ├─ active/                      </span><span># Activos (fuente de verdad del admin)</span><span>
│  ├─ archive/                     </span><span># Archivados</span><span>
│  ├─ marquee.txt                  </span><span># Texto de marquesina (editado desde admin)</span><span>
│  └─ queues/ (si usas modo file)  </span><span># (No usado en modo MySQL)</span><span>
└─ assets/
   ├─ css/base-dark.css            </span><span># Base visual</span><span>
   └─ css/pantalla-dark.css        </span><span># Tema de pantalla (tu diseño)</span><span>
</span></span></code></div></div></pre>

---

## 🧩 Componentes y responsabilidades

### 1) Pantalla — `pantalla.php`

* **Qué muestra** :
* “Atendiendo” (`current`) con prefijo y pad (`C-001`).
* “Esperando” (`next[]`).
* **Publicidad** (galería rotativa: imágenes y videos).
* **Marquesina** (texto desplazable).
* **Logo** (parámetro `?logo=`).
* **De dónde lee** :
* Turnos: `GET /turnero/api/queues_state.php?queue_id=1&next_limit=10`
* Publicidad: `GET /turnero/api/ads.php`
* Marquesina: `GET /turnero/api/marquee.php`
* **Parámetros útiles** :
* `?queue_id=1` — Cola a mostrar.
* `?title=Carnicería` — Título del panel de turnos.
* `?next=10` — Cantidad de “esperando”.
* `?logo=/turnero/assets/img/logo.png` — Ruta del logo.
* `?marquee=...` — Fallback si no hay texto en `marquee.txt`.

### 2) Panel de control — `control.php` (rediseñado)

* **Acciones** :
* 🧾 `Sacar número` → `POST /api/tickets.php`
* 📣 `Llamar (Siguiente)` → `POST /api/queues_next.php`
* ✅ `Servido (auto-siguiente)` → `POST /api/tickets_serve.php`
* ⬅️ `Atrás (volver número)` → `POST /api/queues_prev.php`
* 🧨 `Reset (a 0)` → `POST /api/reset_queue.php`
* **Atajos** :

  `Espacio` = Nuevo, `L` = Llamar, `S` = Servido, `B` = Atrás, `R` = Reset

* **Preferencias locales (localStorage)** :
* `queueId` (ID de cola), `nextLimit`, `prefix`, `pad`.
* Toggle **Auto-siguiente** y  **Sonido al llamar** .
* **Feedback** :
* **Toasts** ,  **Log de actividad** , **Stats** (emitidos hoy, en espera, último).
* Estado de conexión (Conectado/Desconectado/Error).

### 3) Admin dark — `admin/*`

* **Publicidad (galería)** :
* Subir múltiples archivos (`upload.php`) → van a `storage/active/` y se copian a `public/media/ads/`.
* Ver **Activos** (miniaturas) + **Archivo** (metadatos).
* Acciones:  **Archivar** ,  **Restaurar** , **Eliminar** (`action.php`).
* **Resincronizar** (`sync.php`) para reparar copias públicas.
* **Marquesina** :
* Pestaña **Marquesina** → edita y guarda en `storage/marquee.txt` (`marquee_save.php`).
* **Acceso** :
* `includes/config.php` define `ADMIN_PASSWORD` (default `cambia-esto`).
* Opcional: proteger `/admin` con `.htpasswd`.

### 4) API de turnos (MySQL) — `api/*`

Funciona con **una sola conexión PDO** (`db.php`) para todo el proyecto.

* `migrate.php`

  Crea/ajusta tablas e índices **sin transacciones DDL** y deja seed de cola #1.

  Idempotente (podés ejecutarlo cuantas veces quieras).
* `queues_state.php`

  **Entrada** : `queue_id`, `next_limit`

  **Salida** :

  <pre class="overflow-visible!" data-start="5550" data-end="5711"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>
    </span><span>"ok"</span><span>:</span><span></span><span>true</span><span></span><span>,</span><span>
    </span><span>"queue_id"</span><span>:</span><span></span><span>1</span><span>,</span><span>
    </span><span>"prefix"</span><span>:</span><span></span><span>"C"</span><span>,</span><span>
    </span><span>"pad"</span><span>:</span><span></span><span>3</span><span>,</span><span>
    </span><span>"current"</span><span>:</span><span></span><span>12</span><span>,</span><span>
    </span><span>"next"</span><span>:</span><span></span><span>[</span><span>13</span><span>,</span><span>14</span><span>,</span><span>15</span><span>]</span><span>,</span><span>
    </span><span>"updated_at"</span><span>:</span><span></span><span>1758232834</span><span>
  </span><span>}</span><span>
  </span></span></code></div></div></pre>
* `tickets.php` (emitir)

  **POST** → inserta `waiting` con `number = MAX(number)+1`

  **Salida** :

  <pre class="overflow-visible!" data-start="5818" data-end="5907"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>"ok"</span><span>:</span><span>true</span><span></span><span>,</span><span>"queue_id"</span><span>:</span><span>1</span><span>,</span><span>"number"</span><span>:</span><span>27</span><span>,</span><span>"prefix"</span><span>:</span><span>"C"</span><span>,</span><span>"pad"</span><span>:</span><span>3</span><span>,</span><span>"label"</span><span>:</span><span>"C-027"</span><span>}</span><span>
  </span></span></code></div></div></pre>
* `queues_next.php` (llamar)

  **POST** → busca el primer `waiting > current` (o el más chico) y lo marca **called** + actualiza `current_number`.

  **Salida** :

  <pre class="overflow-visible!" data-start="6076" data-end="6153"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>"ok"</span><span>:</span><span>true</span><span></span><span>,</span><span>"current"</span><span>:</span><span>27</span><span>,</span><span>"prefix"</span><span>:</span><span>"C"</span><span>,</span><span>"pad"</span><span>:</span><span>3</span><span>,</span><span>"label"</span><span>:</span><span>"C-027"</span><span>}</span><span>
  </span></span></code></div></div></pre>
* `tickets_serve.php` (servido)

  **POST** → marca el `current` como **served** y si hay otro `waiting` avanza automáticamente (lo marca  **called** ).

  **Salida** :

  <pre class="overflow-visible!" data-start="6325" data-end="6430"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>"ok"</span><span>:</span><span>true</span><span></span><span>,</span><span>"served"</span><span>:</span><span>27</span><span>,</span><span>"advanced"</span><span>:</span><span>true</span><span></span><span>,</span><span>"current"</span><span>:</span><span>28</span><span>,</span><span>"prefix"</span><span>:</span><span>"C"</span><span>,</span><span>"pad"</span><span>:</span><span>3</span><span>,</span><span>"label"</span><span>:</span><span>"C-027"</span><span>}</span><span>
  </span></span></code></div></div></pre>
* `queues_prev.php` (atrás)

  **POST** → salta al **máximo número existente menor** que el actual y lo marca  **called** .

  **Salida** :

  <pre class="overflow-visible!" data-start="6573" data-end="6650"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>"ok"</span><span>:</span><span>true</span><span></span><span>,</span><span>"current"</span><span>:</span><span>26</span><span>,</span><span>"prefix"</span><span>:</span><span>"C"</span><span>,</span><span>"pad"</span><span>:</span><span>3</span><span>,</span><span>"label"</span><span>:</span><span>"C-026"</span><span>}</span><span>
  </span></span></code></div></div></pre>
* `reset_queue.php`

  **POST** → `DELETE FROM tickets WHERE queue_id=?` y `current_number=0`.

  **Salida** : `{"ok":true}`

> Todos los endpoints envían **JSON limpio** (sin warnings HTML) y cabeceras `no-store`.

### 5) API de contenido (archivos) — `api/ads.php` y `api/marquee.php`

* `ads.php`

  Lee `public/media/ads/` y retorna items  **ordenados por mtime** :

  <pre class="overflow-visible!" data-start="7023" data-end="7386"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>
    </span><span>"items"</span><span>:</span><span>[</span><span>
      </span><span>{</span><span>"url"</span><span>:</span><span>"/turnero/public/media/ads/1.png"</span><span>,</span><span>"type"</span><span>:</span><span>"image"</span><span>,</span><span>"media_type"</span><span>:</span><span>"image"</span><span>,</span><span>"duration_sec"</span><span>:</span><span>8</span><span>,</span><span>"size"</span><span>:</span><span>869137</span><span>,</span><span>"mtime"</span><span>:</span><span>1758228659</span><span>,</span><span>"w"</span><span>:</span><span>1024</span><span>,</span><span>"h"</span><span>:</span><span>1024</span><span>}</span><span>,</span><span>
      </span><span>{</span><span>"url"</span><span>:</span><span>"/turnero/public/media/ads/spot.mp4"</span><span>,</span><span>"type"</span><span>:</span><span>"video"</span><span>,</span><span>"media_type"</span><span>:</span><span>"video"</span><span>,</span><span>"duration_sec"</span><span>:</span><span>null</span><span></span><span>,</span><span>"size"</span><span>:</span><span>123456</span><span>,</span><span>"mtime"</span><span>:</span><span>1758228700</span><span>}</span><span>
    </span><span>]</span><span>,</span><span>
    </span><span>"updated_at"</span><span>:</span><span>1758228895</span><span>
  </span><span>}</span><span>
  </span></span></code></div></div></pre>

  > Detecta automáticamente si el proyecto vive en `/turnero` y **no duplica** `/public/`.
  >
* `marquee.php`

  Lee `storage/marquee.txt` y retorna:

  <pre class="overflow-visible!" data-start="7538" data-end="7628"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-json"><span><span>{</span><span>"marquee"</span><span>:</span><span>"▶ Bienvenido · Ofertas del día · ..."</span><span>,</span><span>"updated_at"</span><span>:</span><span>1758228895</span><span>}</span><span>
  </span></span></code></div></div></pre>

---

## 🛠 Instalación paso a paso

1. **Clonar/copiar** el proyecto a `C:\xampp\htdocs\turnero`.
2. **Base de datos** :

* Crear DB `turnero` en MySQL/MariaDB.
* Abrir y ajustar `turnero/db.php` (si usás otro user/pass).
* Ejecutar en el navegador: `http://localhost/turnero/api/migrate.php`

  Debe devolver: `{"ok": true, "actions": [...], "errors": []}`

1. **Permisos de carpetas** :

* `storage/active`, `storage/archive`, `public/media/ads`, `storage/marquee.txt` → escritura para PHP.

1. **Admin** :

* Entrar a `http://localhost/turnero/admin/`
* **Cambiar** `ADMIN_PASSWORD` en `includes/config.php` (default `cambia-esto`).
* Subir imágenes/videos y **Guardar** la marquesina.

1. **Probar turnos** :

* `http://localhost/turnero/control.php` → Sacar 2 tickets, Llamar, Servir.
* `http://localhost/turnero/pantalla.php` → Debe reflejar en ~1s (“Atendiendo”/“Esperando”).

---

## 🧪 Pruebas rápidas (curl / navegador)

* Estado:

  `GET http://localhost/turnero/api/queues_state.php?queue_id=1&next_limit=10`
* Emitir:

  `POST http://localhost/turnero/api/tickets.php?queue_id=1`
* Llamar:

  `POST http://localhost/turnero/api/queues_next.php?queue_id=1`
* Servir:

  `POST http://localhost/turnero/api/tickets_serve.php?queue_id=1`
* Atrás:

  `POST http://localhost/turnero/api/queues_prev.php?queue_id=1`
* Reset:

  `POST http://localhost/turnero/api/reset_queue.php?queue_id=1`
* Publicidad:

  `GET  http://localhost/turnero/api/ads.php`
* Marquesina:

  `GET  http://localhost/turnero/api/marquee.php`

---

## 🔐 Seguridad y buenas prácticas

* Cambiá `ADMIN_PASSWORD` y, si podés, protegé `/admin` con  **.htpasswd** .
* `db.php` usa **PDO** con:
  * `ERRMODE_EXCEPTION`
  * `ATTR_EMULATE_PREPARES = false` (evita `'10'` en `LIMIT :lim`)
* `api/*` fuerza **JSON limpio** (sin warnings) y `Cache-Control: no-store`.
* El admin copia activos a `/public/media/ads/` (directorio público) para servir estáticos.

---

## 🧯 Solución de problemas (FAQ)

**1) `pantalla.php` muestra C-000 y vacío “Esperando”**

* Mirá `GET /api/queues_state.php?queue_id=1&next_limit=10` → si `next:[]`, la cola está vacía. Emití tickets desde `control.php`.
* Si `ok:false`, tu DB no responde: revisá `db.php`.

**2) `SQL 1064 near '10'` en `queues_state.php`**

* Falta `ATTR_EMULATE_PREPARES=false` en `db.php` **y** bindear `LIMIT` como `PDO::PARAM_INT`. (Ya viene así en este README.)

**3) `Unknown column 'pad' in 'field list'`**

* Ejecutá `api/migrate.php` (agrega la columna). Si persiste, revisá que `db.php` apunte a la **misma DB** que estás mirando.

**4) `There is no active transaction` en `migrate.php`**

* Estabas en la versión con transacciones DDL; ya la reemplazamos por DDL  **sin transacciones** .

**5) `Unexpected token '<'` en `pantalla.php`**

* Estabas viendo tags `<?= ... ?>` sin procesar. Asegurate de abrir por `http://.../pantalla.php` (no `file://`) y que PHP esté ejecutando.

**6) 404 en `/api/ads.php` o URLs `.../public/public/...`**

* Usá el `ads.php` incluido (detecta `/turnero`).
* `public/index.php` debe redirigir a `../api/ads.php`.

---

## 🧱 Esquema de base de datos (final)

<pre class="overflow-visible!" data-start="10790" data-end="11402"><div class="contain-inline-size rounded-2xl relative bg-token-sidebar-surface-primary"><div class="sticky top-9"><div class="absolute end-0 bottom-0 flex h-9 items-center pe-2"><div class="bg-token-bg-elevated-secondary text-token-text-secondary flex items-center gap-4 rounded-sm px-2 font-sans text-xs"></div></div></div><div class="overflow-y-auto p-4" dir="ltr"><code class="whitespace-pre! language-sql"><span><span>CREATE</span><span></span><span>TABLE</span><span> queues (
  id </span><span>INT</span><span></span><span>PRIMARY</span><span> KEY,
  prefix </span><span>VARCHAR</span><span>(</span><span>5</span><span>) </span><span>NOT</span><span></span><span>NULL</span><span></span><span>DEFAULT</span><span></span><span>'C'</span><span>,
  pad TINYINT </span><span>NOT</span><span></span><span>NULL</span><span></span><span>DEFAULT</span><span></span><span>3</span><span>,
  current_number </span><span>INT</span><span></span><span>NOT</span><span></span><span>NULL</span><span></span><span>DEFAULT</span><span></span><span>0</span><span>
) ENGINE</span><span>=</span><span>InnoDB </span><span>DEFAULT</span><span> CHARSET</span><span>=</span><span>utf8mb4;

</span><span>CREATE</span><span></span><span>TABLE</span><span> tickets (
  id </span><span>INT</span><span> AUTO_INCREMENT </span><span>PRIMARY</span><span> KEY,
  queue_id </span><span>INT</span><span></span><span>NOT</span><span></span><span>NULL</span><span>,
  number </span><span>INT</span><span></span><span>NOT</span><span></span><span>NULL</span><span>,
  status ENUM(</span><span>'waiting'</span><span>,</span><span>'called'</span><span>,</span><span>'served'</span><span>) </span><span>NOT</span><span></span><span>NULL</span><span></span><span>DEFAULT</span><span></span><span>'waiting'</span><span>,
  created_at DATETIME </span><span>DEFAULT</span><span></span><span>CURRENT_TIMESTAMP</span><span>,
  called_at DATETIME </span><span>NULL</span><span>,
  served_at DATETIME </span><span>NULL</span><span>,
  </span><span>UNIQUE</span><span> KEY uq_ticket (queue_id, number),
  INDEX idx_qn (queue_id, number)
) ENGINE</span><span>=</span><span>InnoDB </span><span>DEFAULT</span><span> CHARSET</span><span>=</span><span>utf8mb4;
</span></span></code></div></div></pre>

---

## 🚀 Roadmap sugerido

* Multi-cola (varias cajas) y  **pantallas por cola** .
* **Roles** (operador/admin) y analytics diarios.
* **Botones físicos/teclado numérico** y modo kiosco.
* WebSockets (o SSE) para  **tiempo real sin polling** .
* Programar **duración por pieza** en publicidad y  **listas por zonas** .

---

## 💬 Créditos y soporte

Hecho para que sea **instalable en minutos** (XAMPP) y fácil de mantener.

Si duplicás el proyecto en otro subdirectorio, los endpoints ya detectan el  **basePath** .
