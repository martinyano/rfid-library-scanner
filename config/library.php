<?php

return [
    'rfid_api_key' => env('RFID_API_KEY'),
    'kiosk_session_seconds' => (int) env('KIOSK_SESSION_SECONDS', 120),
];
