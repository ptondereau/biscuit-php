<?php

declare(strict_types=1);

return [
    'public_key' => env('BISCUIT_PUBLIC_KEY'),
    'public_key_file' => env('BISCUIT_PUBLIC_KEY_FILE'),
    'private_key' => env('BISCUIT_PRIVATE_KEY'),
    'private_key_file' => env('BISCUIT_PRIVATE_KEY_FILE'),
    'cookie' => env('BISCUIT_COOKIE'),
    'user_fact' => 'user',
    'policies' => [],
];
