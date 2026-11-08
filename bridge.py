import time
import pyautogui
import serial

PORT = 'COM3'  # Update to your Arduino port
BAUD = 9600

print(f'Listening for RFID scans on {PORT}...')
arduino = serial.Serial(PORT, BAUD, timeout=1)
time.sleep(2)

while True:
  if arduino.in_waiting > 0:
    line = arduino.readline().decode('utf-8', errors='ignore').strip()
    if line.startswith('Card UID:'):
      uid = line.split(':')[1].strip()
      print(f'Card tapped: {uid}')

      # Automatically type the UID into the browser's focused input and hit Enter!
      pyautogui.write(uid)
      pyautogui.press('enter')