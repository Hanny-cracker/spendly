<?php

use App\Enums\Feature;

return [
    'free' => [
        Feature::Accounts->value => ['enabled' => true, 'limit' => null],
        Feature::RecurringTransactions->value => ['enabled' => true, 'limit' => null],
        Feature::AIInsights->value => ['enabled' => true, 'limit' => null],
        Feature::ReceiptScanner->value => ['enabled' => true, 'limit' => null],
        Feature::AdvancedAnalytics->value => ['enabled' => true, 'limit' => null],
        Feature::Exports->value => ['enabled' => true, 'limit' => null],
    ],
    'premium' => [
        Feature::Accounts->value => ['enabled' => true, 'limit' => null],
        Feature::RecurringTransactions->value => ['enabled' => true, 'limit' => null],
        Feature::AIInsights->value => ['enabled' => true, 'limit' => null],
        Feature::ReceiptScanner->value => ['enabled' => true, 'limit' => null],
        Feature::AdvancedAnalytics->value => ['enabled' => true, 'limit' => null],
        Feature::Exports->value => ['enabled' => true, 'limit' => null],
    ],
];
