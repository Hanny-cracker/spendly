<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;

enum AccountType: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;

    case Cash = 'cash';

    case Bank = 'bank';

    case MobileMoney = 'mobile_money';

    case CreditCard = 'credit_card';

    case Savings = 'savings';

    protected function metadata(): array
    {
        return match ($this) {

            self::Cash => [
                'label' => 'Cash',
                'color' => 'green',
                'icon' => 'heroicon-o-banknotes',
                'description' => 'Physical cash money.',
            ],

            self::Bank => [
                'label' => 'Bank',
                'color' => 'blue',
                'icon' => 'heroicon-o-building-library',
                'description' => 'Money stored in a bank account.',
            ],

            self::MobileMoney => [
                'label' => 'Mobile Money',
                'color' => 'amber',
                'icon' => 'heroicon-o-device-phone-mobile',
                'description' => 'Mobile wallet account.',
            ],

            self::CreditCard => [
                'label' => 'Credit Card',
                'color' => 'purple',
                'icon' => 'heroicon-o-credit-card',
                'description' => 'Credit card account.',
            ],

            self::Savings => [
                'label' => 'Savings',
                'color' => 'cyan',
                'icon' => 'heroicon-o-wallet',
                'description' => 'Money saved for future use.',
            ],

        };
    }
}
