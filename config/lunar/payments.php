<?php

return [

    'default' => env('PAYMENTS_TYPE', 'cash-in-hand'),

    'types' => [
        'cash-in-hand' => [
            'driver' => 'offline',
            'authorized' => 'payment-offline',
        ],

        'bizum' => [
            'driver' => 'offline',
            'released' => 'payment-offline',
        ],

        'card' => [
            'driver' => 'stripe',
            'released' => 'payment-received',
        ],
    ],

];
