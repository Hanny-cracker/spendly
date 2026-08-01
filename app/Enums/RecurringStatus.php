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

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isPaused(): bool
    {
        return $this === self::Paused;
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    protected function metadata(): array
    {
        return match ($this) {

            self::Active => [
                'label' => 'Active',
                'color' => 'green',
                'icon' => 'heroicon-o-play',
                'description' => 'Recurring transaction is active.',
            ],

            self::Paused => [
                'label' => 'Paused',
                'color' => 'amber',
                'icon' => 'heroicon-o-pause',
                'description' => 'Recurring transaction is paused.',
            ],

            self::Completed => [
                'label' => 'Completed',
                'color' => 'gray',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'Recurring transaction has finished.',
            ],
        };
    }
}