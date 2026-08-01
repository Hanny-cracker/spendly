<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;
use Carbon\Carbon;

enum RecurringFrequency: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;

    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    /**
     * Calculate the next execution date.
     */
    public function nextRun(Carbon $date): Carbon
    {
        return match ($this) {
            self::Daily => $date->copy()->addDay(),
            self::Weekly => $date->copy()->addWeek(),
            self::Monthly => $date->copy()->addMonth(),
            self::Quarterly => $date->copy()->addMonths(3),
            self::Yearly => $date->copy()->addYear(),
        };
    }

    protected function metadata(): array
    {
        return match ($this) {

            self::Daily => [
                'label' => 'Daily',
                'color' => 'green',
                'icon' => 'heroicon-o-calendar-days',
                'description' => 'Runs every day.',
            ],

            self::Weekly => [
                'label' => 'Weekly',
                'color' => 'blue',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every week.',
            ],

            self::Monthly => [
                'label' => 'Monthly',
                'color' => 'amber',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every month.',
            ],

            self::Quarterly => [
                'label' => 'Quarterly',
                'color' => 'purple',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every three months.',
            ],

            self::Yearly => [
                'label' => 'Yearly',
                'color' => 'red',
                'icon' => 'heroicon-o-calendar',
                'description' => 'Runs every year.',
            ],
        };
    }
}
