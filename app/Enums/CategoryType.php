<?php

declare(strict_types=1);

namespace App\Enums;

use App\Concerns\HasEnumHelpers;
use App\Concerns\HasEnumMetadata;


enum CategoryType: string
{
    use HasEnumHelpers;
    use HasEnumMetadata;


    case Income = 'income';

    case Expense = 'expense';

    public function isIncome(): bool
    {
        return $this === self::Income;
    }

    public function isExpense(): bool
    {
        return $this === self::Expense;
    }


    protected function metadata(): array
    {
        return match ($this) {

            self::Income => [
                'label' => 'Income',
                'color' => 'green',
                'icon' => 'heroicon-o-arrow-trending-up',
                'description' => 'Money coming into your accounts.',
            ],


            self::Expense => [
                'label' => 'Expense',
                'color' => 'red',
                'icon' => 'heroicon-o-arrow-trending-down',
                'description' => 'Money leaving your accounts.',
            ],
        };
    }
}
