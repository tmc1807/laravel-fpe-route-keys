<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Encryption key
    |--------------------------------------------------------------------------
    |
    | The application key is used when this value is not set explicitly.
    | The encoder derives a fixed-length AES key from it.
    |
    */
    'key' => env('FPE_ROUTE_KEYS_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Token length
    |--------------------------------------------------------------------------
    |
    | Tokens use a Base62 alphabet. Eleven characters provide a large domain
    | while keeping links compact for normal integer primary keys.
    |
    */
    'length' => (int) env('FPE_ROUTE_KEYS_LENGTH', 11),

    /*
    |--------------------------------------------------------------------------
    | Tweak
    |--------------------------------------------------------------------------
    |
    | The tweak is not secret. It separates this package from other uses of
    | the same application key. The model class is included automatically.
    |
    */
    'tweak' => env('FPE_ROUTE_KEYS_TWEAK', 'laravel-fpe-route-keys'),
];
