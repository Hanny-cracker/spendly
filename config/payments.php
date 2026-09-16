<?php

return [
    'provider' => env('PAYMENT_PROVIDER', 'aggregator'),
    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    'plans' => [
        'monthly' => ['amount' => (int) env('PREMIUM_MONTHLY_PRICE_XAF', 5000), 'currency' => 'XAF', 'days' => 30],
        'annual' => ['amount' => (int) env('PREMIUM_ANNUAL_PRICE_XAF', 50000), 'currency' => 'XAF', 'days' => 365],
    ],
];
