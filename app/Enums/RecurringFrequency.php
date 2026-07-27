<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;

enum RecurringFrequency: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;


    case Daily = 'daily';

    case Weekly = 'weekly';

    case Monthly = 'monthly';

    case Yearly = 'yearly';



    protected function metadata(): array
    {
        return match ($this) {

            self::Daily => [
                'label' => 'Daily',
                'color' => 'blue',
                'icon' => 'heroicon-o-calendar-days',
                'description' => 'Runs every day.',
            ],


            self::Weekly => [
                'label' => 'Weekly',
                'color' => 'cyan',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every week.',
            ],


            self::Monthly => [
                'label' => 'Monthly',
                'color' => 'purple',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every month.',
            ],


            self::Yearly => [
                'label' => 'Yearly',
                'color' => 'green',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every year.',
            ],

        };
    }
}