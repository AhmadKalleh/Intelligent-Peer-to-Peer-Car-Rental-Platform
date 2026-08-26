<?php
// config/paymera.php

return [
    'base_url'     => env('PAYMERA_BASE_URL', 'https://egate-t.paymera.cc'),
    'username'     => env('PAYMERA_USERNAME', 'syrodrive'),
    'password'     => env('PAYMERA_PASSWORD', 'syrodrive@123'),
    'terminal_id'  => env('PAYMERA_TERMINAL_ID', '14740248'),
    'callback_url' => env('PAYMERA_CALLBACK_URL'),  // رابط الفرونت
    'trigger_url'  => env('PAYMERA_TRIGGER_URL'),   // webhook url
];
