<?php

return [
    'login' => env('FIB_BASE_URL') . '/auth/realms/fib-online-shop/protocol/openid-connect/token',
    'base_url' => env('FIB_BASE_URL', 'https://api.fibpayment.com') . '/protected/v1',
    'grant' => env('FIB_GRANT_TYPE', 'client_credentials'),
    'refundable_for' => env('FIB_REFUNDABLE_FOR', 'P7D'),
    'currency' => env('FIB_CURRENCY', 'IQD'),
    'callback' => env('FIB_CALLBACK_URL'),
    'default_auth_account' => env('FIB_DEFAULT_ACCOUNT', 'default'),
    'auth_accounts' => [
        'default' => [
            'client_id' => env('FIB_CLIENT_ID'),
            'secret' => env('FIB_CLIENT_SECRET'),
        ],
    ],
];
