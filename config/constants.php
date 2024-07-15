<?php

return [
    'exchange' => [
        'USD' => [
            'TWD' => env('USD_TO_TWD', 31),
        ],
        'TWD' => [
            'USD' => env('TWD_TO_USD', 0.03225806451),
        ],
    ],
];