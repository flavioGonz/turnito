GPIO test utility

This folder contains a standalone script `gpio_test.py` to test GPIO pins on a Raspberry Pi or in mock mode on non-Pi machines.

Usage examples:

# On Raspberry Pi (use sudo)

sudo python3 tools/gpio_test.py read --pin 17
sudo python3 tools/gpio_test.py write --pin 17 --value high
sudo python3 tools/gpio_test.py blink --pin 17 --count 5 --interval 0.25

# On development machine without GPIO (mock)

python3 tools/gpio_test.py --mock read --pin 17

Notes:

- The script will print actions in mock mode. In real mode it uses RPi.GPIO.
- Run the script from the repository root for paths to work as shown.

Reporting events to the monitor
--------------------------------
You can have `gpio_test.py` POST JSON events to the monitoring endpoint `tools/gpio_report.php` by using `--report-url`.

Example (mock):

python3 tools/gpio_test.py --mock read --pin 17 --report-url http://localhost/turnero/tools/gpio_report.php

On a Raspberry Pi (real):

sudo python3 tools/gpio_test.py read --pin 17 --report-url http://localhost/turnero/tools/gpio_report.php
