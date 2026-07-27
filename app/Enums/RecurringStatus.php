<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;

enum RecurringStatus: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;


    case Active = 'active';

    case Paused = 'paused';

    case Completed = 'completed';



    protected function metadata(): array
    {
        return match ($this) {

            self::Active => [
                'label' => 'Active',
                'color' => 'green',
                'icon' => 'heroicon-o-play-circle',
                'description' => 'Recurring transaction is running.',
            ],


            self::Paused => [
                'label' => 'Paused',
                'color' => 'amber',
                'icon' => 'heroicon-o-pause-circle',
                'description' => 'Recurring transaction is temporarily stopped.',
            ],


            self::Completed => [
                'label' => 'Completed',
                'color' => 'gray',
                'icon' => 'heroicon-o-check-badge',
                'description' => 'Recurring transaction has ended.',
            ],

        };
    }
}