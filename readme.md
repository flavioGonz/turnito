# 📌 Sistema de Turnos — Turnero

Sistema **moderno, modular y liviano** para gestionar turnos en locales físicos.  
Diseñado para funcionar tanto en **PC con XAMPP** como en **Raspberry Pi en modo kiosco** con pulsadores e impresora térmica.

Incluye:

* **Pantalla pública** con turnos + publicidad (imágenes/videos) + marquesina.
* **Panel de control** con atajos de teclado y estadísticas.
* **API REST en PHP/MySQL** (JSON limpio, sin warnings).
* **Admin dark** para subir medios y editar la marquesina.
* **Soporte Raspberry Pi + GPIO + impresora ESC/POS**.

> Ruta base: `http://localhost/turnero` (ejemplo XAMPP: `C:\xampp\htdocs\turnero`).

---

## ⚙️ Arquitectura del proyecto

```
/turnero
├─ pantalla.php          # Pantalla pública (turnos + publicidad + marquesina)
├─ control.php           # Panel de operador (acciones + stats + atajos)
├─ print_ticket.php      # Plantilla impresión ticket (ESC/POS)
├─ db.php                # Conexión PDO a MySQL
├─ api/                  # API REST JSON
│  ├─ migrate.php        # Migración DB (idempotente)
│  ├─ queues_state.php   # Estado actual de la cola
│  ├─ tickets.php        # Emitir ticket
│  ├─ queues_next.php    # Llamar siguiente
│  ├─ tickets_serve.php  # Servir y avanzar
│  ├─ queues_prev.php    # Retroceder número
│  ├─ reset_queue.php    # Resetear cola
│  ├─ ads.php            # Publicidad (lista JSON)
│  └─ marquee.php        # Texto marquesina
├─ admin/                # Admin dark (Bootstrap)
├─ includes/             # Config y helpers
├─ public/media/ads/     # Publicidad servida (copias públicas)
├─ storage/active        # Activos (fuente de verdad)
├─ storage/archive       # Archivados
├─ storage/marquee.txt   # Texto marquesina
└─ assets/css/           # Estilos (pantalla/base)
```

---

## 🧩 Componentes

### Pantalla (`pantalla.php`)
* **Diseño responsive** (funciona en monitores, tablets antiguas como iPad 2, pantallas 9:16 y 16:9).
* Dividida en 2 sectores:
  - **Izquierda (blanco)** → Turnos.
    - Atendiendo (número actual, grande y centrado).
    - En fila (lista centrada bajo el número).
  - **Derecha (negro)** → Publicidad (imágenes/videos en loop).
* **Marquesina animada** (texto desplazable abajo).
* **Logo opcional** con parámetro `?logo=`.
* Variantes:
  - **Con animación** (efectos rápidos).
  - **Sin animación** (para dispositivos limitados).

### Panel de control (`control.php`)
* Acciones: Sacar número, Llamar, Servido, Atrás, Reset.
* Atajos: Espacio (nuevo), L (llamar), S (servido), B (atrás), R (reset).
* Feedback: toasts, log de actividad, stats, estado de conexión.
* Preferencias guardadas en localStorage.

### Admin (`/admin`)
* Subida múltiple de imágenes/videos.
* Archivado, restauración y eliminación.
* Resincronización de publicidad.
* Edición de marquesina en `storage/marquee.txt`.
* Acceso con `ADMIN_PASSWORD` en `includes/config.php`.

### API REST (`/api/*`)
* `migrate.php` → crea/ajusta tablas.
* `queues_state.php` → estado actual.
* `tickets.php` → emitir ticket.
* `queues_next.php` → llamar siguiente.
* `tickets_serve.php` → marcar servido.
* `queues_prev.php` → retroceder número.
* `reset_queue.php` → resetear cola.
* `ads.php` → lista JSON de medios.
* `marquee.php` → texto de marquesina.

---

## 🧱 Esquema de base de datos

```sql
CREATE TABLE ads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  media_type ENUM('image','video') NOT NULL,
  url VARCHAR(255) NOT NULL,
  duration_sec INT DEFAULT 8,
  enabled TINYINT(1) DEFAULT 1
);

CREATE TABLE queues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  prefix VARCHAR(10) DEFAULT 'C',
  pad TINYINT NOT NULL DEFAULT 3,
  logo VARCHAR(255) DEFAULT NULL,
  current_number INT DEFAULT 0,
  last_number INT DEFAULT 0,
  reset_daily TINYINT(1) DEFAULT 1
);

CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  queue_id INT NOT NULL,
  number INT NOT NULL,
  printed_at DATETIME DEFAULT current_timestamp(),
  status ENUM('waiting','called','served','cancelled') DEFAULT 'waiting',
  called_at DATETIME DEFAULT NULL,
  served_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT current_timestamp(),
  UNIQUE KEY uq_ticket (queue_id, number),
  CONSTRAINT tickets_ibfk_1 FOREIGN KEY (queue_id) REFERENCES queues (id)
);
```

---

## 🔌 Integración Raspberry Pi — GPIO en Python

Script `turnero_buttons.py` para manejar botones:

```python
#!/usr/bin/env python3
import RPi.GPIO as GPIO, requests, subprocess, time

API_BASE = "http://localhost/turnero/api"
QUEUE_ID = 1
PRINTER_IP = "192.168.99.134"

BTN_CLIENTE, BTN_SIGUIENTE, BTN_ATRAS = 17, 27, 22
GPIO.setmode(GPIO.BCM)
GPIO.setup([BTN_CLIENTE, BTN_SIGUIENTE, BTN_ATRAS], GPIO.IN, pull_up_down=GPIO.PUD_UP)

def print_ticket(label):
    data = f"=== TURNO ===\nNúmero: {label}\n\n\n\x1B@\x1DV\x00"
    subprocess.run(["nc", PRINTER_IP, "9100"], input=data.encode())

def emitir_ticket(c): r=requests.post(f"{API_BASE}/tickets.php",params={"queue_id":QUEUE_ID});print_ticket(r.json()["label"])
def siguiente(c): r=requests.post(f"{API_BASE}/queues_next.php",params={"queue_id":QUEUE_ID});print("Siguiente:",r.json()["label"])
def atras(c): r=requests.post(f"{API_BASE}/queues_prev.php",params={"queue_id":QUEUE_ID});print("Atrás:",r.json()["label"])

GPIO.add_event_detect(BTN_CLIENTE, GPIO.FALLING, callback=emitir_ticket, bouncetime=500)
GPIO.add_event_detect(BTN_SIGUIENTE, GPIO.FALLING, callback=siguiente, bouncetime=500)
GPIO.add_event_detect(BTN_ATRAS, GPIO.FALLING, callback=atras, bouncetime=500)

print("GPIO Turnero corriendo... Ctrl+C para salir")
try: 
    while True: time.sleep(1)
except KeyboardInterrupt: GPIO.cleanup()
```

Instalar dependencias:
```bash
sudo apt install -y python3-rpi.gpio python3-requests netcat
```

Servicio systemd:
```ini
[Unit]
Description=Turnero GPIO Buttons
After=network.target
[Service]
ExecStart=/usr/bin/python3 /home/pi/turnero_buttons.py
Restart=always
User=pi
[Install]
WantedBy=multi-user.target
```

---

## 🛠 Instalación de PHP + MySQL en FullPageOS

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 php php-mysql libapache2-mod-php mariadb-server unzip
sudo systemctl enable apache2
sudo systemctl enable mariadb
sudo mysql_secure_installation
```

Crear DB:
```bash
mysql -u root -p
CREATE DATABASE turnero CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;
```

Importar estructura:
```bash
mysql -u root -p turnero < /home/pi/turnero.sql
```

Copiar proyecto:
```bash
sudo mkdir -p /var/www/html/turnero
sudo cp -r ~/turnero/* /var/www/html/turnero/
sudo chown -R www-data:www-data /var/www/html/turnero
```

---

## 🧪 Ejemplos rápidos (curl)

```bash
curl "http://localhost/turnero/api/queues_state.php?queue_id=1&next_limit=5"
curl -X POST "http://localhost/turnero/api/tickets.php?queue_id=1"
curl -X POST "http://localhost/turnero/api/queues_next.php?queue_id=1"
curl -X POST "http://localhost/turnero/api/tickets_serve.php?queue_id=1"
curl -X POST "http://localhost/turnero/api/queues_prev.php?queue_id=1"
curl -X POST "http://localhost/turnero/api/reset_queue.php?queue_id=1"
```

---

## 🚀 Roadmap

- Multi-cola (varias cajas).
- Roles (operador/admin).
- Publicidad con tiempo configurable.
- WebSockets/SSE para tiempo real.
- Estadísticas y reportes.
- Integración total con hardware (GPIO + impresora).


---

## 📂 Carpeta `/pi`

Dentro de la carpeta `pi` se centralizan los scripts y servicios necesarios para que el sistema corra en Raspberry Pi.

Estructura:

```
/pi
├─ turnero_buttons.py         # Script principal GPIO (emite tickets, siguiente, atrás)
├─ turnero-buttons.service    # Unidad systemd para habilitar el servicio al inicio
├─ requirements.txt           # Dependencias Python (RPi.GPIO, requests, netcat)
└─ README.md                  # Guía rápida de instalación en Raspberry Pi
```

### Instalación rápida en la Raspberry Pi

```bash
cd ~/turnero/pi
sudo cp turnero-buttons.service /etc/systemd/system/
sudo systemctl enable --now turnero-buttons
```

Esto asegura que los botones físicos funcionen automáticamente al iniciar la Raspberry Pi y queden integrados con el sistema de turnos.
