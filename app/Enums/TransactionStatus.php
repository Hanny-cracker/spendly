<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;

enum TransactionStatus: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;

    case Pending = 'pending';

    case Completed = 'completed';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public function affectsBalance(): bool
    {
        return $this->isCompleted();
    }

    protected function metadata(): array
    {
        return match ($this) {

            self::Pending => [
                'label' => 'Pending',
                'color' => 'amber',
                'icon' => 'heroicon-o-clock',
                'description' => 'Transaction has not been completed yet.',
            ],

            self::Completed => [
                'label' => 'Completed',
                'color' => 'green',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'Transaction completed successfully.',
            ],
        };
    }
}
