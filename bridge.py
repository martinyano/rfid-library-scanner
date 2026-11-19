import os
import time

import requests
import serial

PORT = os.getenv('RFID_PORT', 'COM3')
BAUD = int(os.getenv('RFID_BAUD', '9600'))
API_URL = os.getenv('RFID_API_URL', 'https://mylibraryapp.test').rstrip('/')
API_KEY = os.getenv('RFID_API_KEY', 'dev-rfid-key-change-me')
DEVICE_ID = os.getenv('RFID_DEVICE_ID', 'reader-1')
MODE = os.getenv('RFID_MODE', 'circulation').lower()

ENDPOINTS = {
    'circulation': '/api/rfid/circulation',
    'door': '/api/rfid/scan',
}

if MODE not in ENDPOINTS:
    raise SystemExit(f"Unknown RFID_MODE '{MODE}'. Use 'circulation' or 'door'.")

print(f'Listening for RFID scans on {PORT} ({MODE})...')
arduino = serial.Serial(PORT, BAUD, timeout=1)
time.sleep(2)

while True:
    if arduino.in_waiting > 0:
        line = arduino.readline().decode('utf-8', errors='ignore').strip()
        if not line.startswith('Card UID:'):
            continue

        uid = line.split(':', 1)[1].strip()
        payload = {'rfid_uid': uid}
        if MODE == 'circulation':
            payload['device_id'] = DEVICE_ID

        try:
            response = requests.post(
                f"{API_URL}{ENDPOINTS[MODE]}",
                json=payload,
                headers={'X-RFID-Key': API_KEY, 'Accept': 'application/json'},
                timeout=5,
                verify=False,
            )
            print(f'{uid}: {response.status_code} {response.text}')
        except requests.RequestException as error:
            print(f'{uid}: request failed - {error}')
