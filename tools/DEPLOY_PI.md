Deploying GPIO monitor to Raspberry Pi

This guide shows minimal steps to deploy `tools/gpio_test.py` as a systemd service on a Raspberry Pi.

Assumptions
- You're running Raspberry Pi OS or similar Debian-based distro.
- Your web server serves the `turnero` app at `/var/www/html/turnero` (adjust paths if different).
- You have root / sudo access on the Pi.

Steps

1) Install Python and RPi.GPIO

```bash
sudo apt update
sudo apt install -y python3 python3-venv python3-pip python3-rpi.gpio
```

2) Create a dedicated user (optional but recommended)

```bash
sudo adduser --system --group --no-create-home turnero
# or you'll run as 'pi'
```

3) Copy the project files to the Pi

From your dev machine, copy the `turnero` folder to `/var/www/html/` on the Pi (adjust if your webroot differs):

```bash
# example using scp from your machine
scp -r /path/to/turnero pi@raspberrypi:/home/pi/
# then move to webroot
ssh pi@raspberrypi
sudo mv /home/pi/turnero /var/www/html/turnero
sudo chown -R www-data:www-data /var/www/html/turnero
```

4) Give the service user access to GPIO (if not running as root)

On recent Raspberry Pi OS, the gpio group controls access. Add the service user (e.g., pi or turnero) to the gpio group:

```bash
sudo usermod -aG gpio pi
# or
sudo usermod -aG gpio turnero
```

5) Install the systemd unit

Copy `tools/gpio_monitor.service` into `/etc/systemd/system/` and edit `ExecStart` / `WorkingDirectory` to match your paths and chosen pins.

```bash
sudo cp /var/www/html/turnero/tools/gpio_monitor.service /etc/systemd/system/turnero-gpio-monitor.service
sudo systemctl daemon-reload
sudo systemctl enable --now turnero-gpio-monitor.service
sudo journalctl -u turnero-gpio-monitor -f
```

6) Confirm monitoring and reporting

- Check `tools/gpio_events.json` (served by web server) or open the monitor page at `http://<pi>/turnero/tools/gpio_monitor.php`.

7) Troubleshooting

- If the service fails to start, run `sudo journalctl -u turnero-gpio-monitor -b` and inspect permissions.
- If you used a non-root user and see permission errors accessing GPIO, confirm the user is in the `gpio` group and reboot or re-login.
- Ensure your web server (Apache/Nginx) can serve `tools/gpio_events.json` and that file permissions allow the web user to read it.

Security notes
- `gpio_report.php` and `gpio_clear.php` only accept requests from localhost by default. If you need remote reporting, secure with a token or restrict via firewall.

That's it — after deployment the Pi will run the monitor as a service and post events to the web app locally.
