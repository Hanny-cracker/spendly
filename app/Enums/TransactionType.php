<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;

enum TransactionType: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;


    case Income = 'income';

    case Expense = 'expense';

    case Transfer = 'transfer';

    public function isIncome(): bool
    {
        return $this === self::Income;
    }

    public function isExpense(): bool
    {
        return $this === self::Expense;
    }

    public function multiplier(): int
    {
        return $this->isIncome() ? 1 : -1;
    }

    // public function affectsBalance(): bool
    // {
    //     return true;
    // }
    public function affectsBalance(): bool
    {
        return $this !== self::Transfer;
    }

    protected function metadata(): array
    {
        return match ($this) {

            self::Income => [
                'label' => 'Income',
                'color' => 'green',
                'icon' => 'heroicon-o-arrow-trending-up',
                'description' => 'Money received into an account.',
            ],


            self::Expense => [
                'label' => 'Expense',
                'color' => 'red',
                'icon' => 'heroicon-o-arrow-trending-down',
                'description' => 'Money spent from an account.',
            ],
        };
    }
}
