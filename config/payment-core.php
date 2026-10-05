<?php

return [
    'base_url' => env('PAYMENT_CORE_URL', 'https://avaztek.ir'),
    'key_id' => env('PAYMENT_CORE_KEY_ID', ''),
    'secret' => env('PAYMENT_CORE_SECRET', ''),
    'timeout' => (int) env('PAYMENT_CORE_TIMEOUT', 15),
    'connect_timeout' => (int) env('PAYMENT_CORE_CONNECT_TIMEOUT', 5),
];
