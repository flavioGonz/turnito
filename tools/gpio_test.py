#!/usr/bin/env python3
"""
GPIO test utility for Raspberry Pi (safe testing script)

Features:
- Supports real RPi.GPIO when available and a mock mode for development on non-Pi machines.
- Subcommands: read, write, blink
- Options: --mode (bcm|board), --mock to force mock mode

Examples:
  # Read a pin (BCM mode)
  sudo python3 tools/gpio_test.py read --pin 17

  # Set pin high
  sudo python3 tools/gpio_test.py write --pin 17 --value high

  # Blink pin 5 times, 0.5s interval
  sudo python3 tools/gpio_test.py blink --pin 17 --count 5 --interval 0.5

Notes:
- Run as root (sudo) when using real GPIO on Raspberry Pi.
- If you run this on Windows or a machine without RPi.GPIO it will use a mock mode and only print actions.
"""

import argparse
import sys
import time
import os
import json
import urllib.request
import urllib.error

# Try to import RPi.GPIO; fall back to mock if unavailable
REAL_GPIO = False
try:
    import RPi.GPIO as GPIO
    REAL_GPIO = True
except Exception:
    REAL_GPIO = False


class MockGPIO:
    BCM = 'BCM'
    BOARD = 'BOARD'
    OUT = 'OUT'
    IN = 'IN'
    HIGH = 1
    LOW = 0

    def __init__(self):
        self._mode = None
        self._pins = {}

    def setwarnings(self, flag):
        print(f"[mock] setwarnings({flag})")

    def setmode(self, mode):
        self._mode = mode
        print(f"[mock] setmode({mode})")

    def setup(self, pin, mode):
        self._pins[pin] = {'mode': mode, 'value': 0}
        print(f"[mock] setup(pin={pin}, mode={mode})")

    def output(self, pin, value):
        if pin not in self._pins:
            self.setup(pin, MockGPIO.OUT)
        self._pins[pin]['value'] = value
        print(f"[mock] output(pin={pin}, value={value})")

    def input(self, pin):
        val = self._pins.get(pin, {}).get('value', 0)
        print(f"[mock] input(pin={pin}) -> {val}")
        return val

    def cleanup(self):
        print("[mock] cleanup()")
        self._pins = {}


GPIO_LIB = GPIO if REAL_GPIO else MockGPIO()


def check_root_if_needed(force_mock: bool):
    if force_mock:
        return
    if REAL_GPIO:
        # On Unix-like systems ensure running as root for GPIO access
        try:
            euid = os.geteuid()
            if euid != 0:
                print("WARNING: Running with RPi.GPIO but not as root: GPIO access may fail. Use sudo.")
        except AttributeError:
            # os.geteuid not available on Windows
            pass


def do_read(pin: int):
    GPIO_LIB.setwarnings(False)
    GPIO_LIB.setmode(GPIO_LIB.BCM if args.mode == 'bcm' else GPIO_LIB.BOARD)
    GPIO_LIB.setup(pin, GPIO_LIB.IN)
    val = GPIO_LIB.input(pin)
    print(f"PIN {pin} value: {val}")
    GPIO_LIB.cleanup()
    # optional report
    if getattr(args, 'report_url', None):
        report_event(args.report_url, pin, 'read:' + str(val))


def do_write(pin: int, value: str):
    v = GPIO_LIB.HIGH if value.lower() in ('1', 'true', 'high', 'on') else GPIO_LIB.LOW
    GPIO_LIB.setwarnings(False)
    GPIO_LIB.setmode(GPIO_LIB.BCM if args.mode == 'bcm' else GPIO_LIB.BOARD)
    GPIO_LIB.setup(pin, GPIO_LIB.OUT)
    GPIO_LIB.output(pin, v)
    print(f"Set pin {pin} -> {value}")
    GPIO_LIB.cleanup()
    if getattr(args, 'report_url', None):
        report_event(args.report_url, pin, 'write:' + value)


def do_blink(pin: int, count: int, interval: float):
    GPIO_LIB.setwarnings(False)
    GPIO_LIB.setmode(GPIO_LIB.BCM if args.mode == 'bcm' else GPIO_LIB.BOARD)
    GPIO_LIB.setup(pin, GPIO_LIB.OUT)
    print(f"Blinking pin {pin} {count} times (interval={interval}s)")
    try:
        for i in range(count):
            GPIO_LIB.output(pin, GPIO_LIB.HIGH)
            time.sleep(interval)
            GPIO_LIB.output(pin, GPIO_LIB.LOW)
            time.sleep(interval)
    finally:
        GPIO_LIB.cleanup()
    if getattr(args, 'report_url', None):
        report_event(args.report_url, pin, f'blink:{count}')


def report_event(url: str, pin: int, value: str):
    payload = json.dumps({'pin': pin, 'value': value}).encode('utf-8')
    req = urllib.request.Request(url, data=payload, headers={'Content-Type': 'application/json'})
    try:
        with urllib.request.urlopen(req, timeout=5) as resp:
            resp_data = resp.read().decode('utf-8')
            print(f"Reported event to {url}: {resp.status} {getattr(resp,'reason', '')} -> {resp_data}")
    except urllib.error.URLError as e:
        print(f"Failed to report event to {url}: {e}")


def parse_args(argv):
    # Parent parser contains options that should be accepted both before and after the subcommand
    parent = argparse.ArgumentParser(add_help=False)
    parent.add_argument('--mode', choices=('bcm', 'board'), default=None, help='Pin numbering mode (bcm|board)')
    parent.add_argument('--mock', action='store_true', default=None, help='Force mock mode (do not use real GPIO)')
    parent.add_argument('--report-url', help='If provided, POST a JSON event to this URL after actions (local endpoint)')

    parser = argparse.ArgumentParser(description='GPIO test utility (safe/mode mock)', parents=[parent])
    sub = parser.add_subparsers(dest='cmd', required=True)

    r = sub.add_parser('read', parents=[parent], help='Read a pin')
    r.add_argument('--pin', type=int, required=True, help='Pin number')

    w = sub.add_parser('write', parents=[parent], help='Write a pin (high/low)')
    w.add_argument('--pin', type=int, required=True, help='Pin number')
    w.add_argument('--value', choices=('high', 'low', 'on', 'off', '1', '0'), required=True, help='Value to write')

    b = sub.add_parser('blink', parents=[parent], help='Blink a pin N times')
    b.add_argument('--pin', type=int, required=True, help='Pin number')
    b.add_argument('--count', type=int, default=3, help='How many times to blink')
    b.add_argument('--interval', type=float, default=0.5, help='Seconds between toggles')

    m = sub.add_parser('monitor', parents=[parent], help='Monitor one or more pins and report changes')
    m.add_argument('--pin', type=int, nargs='+', required=True, help='Pin number(s) to monitor')
    m.add_argument('--interval', type=float, default=0.2, help='Polling interval for mock mode (seconds)')
    m.add_argument('--debounce', type=int, default=50, help='Debounce window in ms for change suppression')
    m.add_argument('--duration', type=float, default=None, help='Optional duration to run in seconds (useful for tests)')

    # Parse and normalize: prefer subparser-provided mode/mock, fall back to top-level, then default values.
    args = parser.parse_args(argv)
    if getattr(args, 'mode', None) is None:
        args.mode = 'bcm'
    if getattr(args, 'mock', None) is None:
        args.mock = False
    return args


def do_monitor(pins, interval=0.2, debounce_ms=50, duration=None, report_url=None):
    """Monitor pins for changes. In real mode uses RPi.GPIO event detect; in mock mode polls."""
    print(f"Starting monitor on pins: {pins} (mock={args.mock}) interval={interval}s debounce={debounce_ms}ms duration={duration})")
    if not isinstance(pins, (list, tuple)):
        pins = [pins]

    if not args.mock and REAL_GPIO:
        # Real GPIO event detection
        try:
            GPIO_LIB.setwarnings(False)
            GPIO_LIB.setmode(GPIO_LIB.BCM if args.mode == 'bcm' else GPIO_LIB.BOARD)
            for p in pins:
                GPIO_LIB.setup(p, GPIO_LIB.IN)

            def cb_factory(pin):
                def cb(channel):
                    try:
                        val = GPIO_LIB.input(pin)
                        print(f"Event pin {pin}: {val}")
                        if report_url:
                            report_event(report_url, pin, f'event:{val}')
                    except Exception as e:
                        print('Callback error:', e)
                return cb

            use_polling = False
            for p in pins:
                # add both edge detection
                try:
                    GPIO_LIB.add_event_detect(p, GPIO_LIB.BOTH if hasattr(GPIO_LIB, 'BOTH') else GPIO_LIB.RISING, callback=cb_factory(p), bouncetime=debounce_ms)
                except RuntimeError as e:
                    # fallback to polling if kernel or permissions prevent event detect
                    print(f"add_event_detect failed for pin {p}: {e}; falling back to polling")
                    use_polling = True
                    break

            if not use_polling:
                # run until duration or KeyboardInterrupt (event-detect mode)
                start = time.time()
                try:
                    while True:
                        time.sleep(1)
                        if duration and (time.time() - start) >= duration:
                            break
                except KeyboardInterrupt:
                    pass
            else:
                # Switch to polling mode using GPIO input reads
                print('Switching to polling mode due to add_event_detect failure')
                last = {}
                try:
                    for p in pins:
                        try:
                            last[p] = GPIO_LIB.input(p)
                        except Exception:
                            last[p] = 0
                    start = time.time()
                    while True:
                        for p in pins:
                            try:
                                v = GPIO_LIB.input(p)
                            except Exception:
                                v = 0
                            if last.get(p) is None:
                                last[p] = v
                            elif v != last[p]:
                                print(f"Change detected pin {p}: {last[p]} -> {v}")
                                last[p] = v
                                if report_url:
                                    report_event(report_url, p, f'poll:{v}')
                        if duration and (time.time() - start) >= duration:
                            break
                        time.sleep(interval)
                except KeyboardInterrupt:
                    pass

        finally:
            try:
                for p in pins:
                    if hasattr(GPIO_LIB, 'remove_event_detect'):
                        GPIO_LIB.remove_event_detect(p)
            except Exception:
                pass
            GPIO_LIB.cleanup()
            print('Monitor stopped')
    else:
        # Mock or no RPi.GPIO available: polling loop
        last = {p: None for p in pins}
        start = time.time()
        try:
            while True:
                for p in pins:
                    try:
                        v = GPIO_LIB.input(p)
                    except Exception:
                        v = 0
                    if last[p] is None:
                        last[p] = v
                    elif v != last[p]:
                        # simple debounce by time
                        print(f"Change detected pin {p}: {last[p]} -> {v}")
                        last[p] = v
                        if report_url:
                            report_event(report_url, p, f'mock:{v}')
                if duration and (time.time() - start) >= duration:
                    break
                time.sleep(interval)
        except KeyboardInterrupt:
            pass
        print('Monitor stopped (mock)')



if __name__ == '__main__':
    args = parse_args(sys.argv[1:])

    # If user forced mock, override
    if args.mock and REAL_GPIO:
        print("Forcing mock mode (--mock) despite having RPi.GPIO available.")
        GPIO_LIB = MockGPIO()

    check_root_if_needed(force_mock=args.mock)

    # If REAL_GPIO but GPIO_LIB is Mock due to above reassignment, ensure reference exists
    if isinstance(GPIO_LIB, MockGPIO):
        # already ok
        pass

    if args.cmd == 'read':
        do_read(args.pin)
    elif args.cmd == 'write':
        do_write(args.pin, args.value)
    elif args.cmd == 'blink':
        do_blink(args.pin, args.count, args.interval)
    elif args.cmd == 'monitor':
        # monitor one or more pins
        do_monitor(args.pin, interval=args.interval, debounce_ms=args.debounce, duration=args.duration, report_url=args.report_url)
    else:
        print('Unknown command')
        sys.exit(2)

