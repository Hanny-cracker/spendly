<?php

namespace App\Actions\Analysis\Insights;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Data\Report\DateRangeData;
use App\Models\Transaction;

class SavingsRate
{
    public function handle(DateRangeData $data): array
    {
        $data->validate();

        $income = $this->total(
            $data,
            TransactionType::Income
        );

        $expenses = $this->total(
            $data,
            TransactionType::Expense
        );

        $savings = $income - $expenses;

        $rate = $income > 0
            ? ($savings / $income) * 100
            : 0;

        return [
            'income' => (float) $income,
            'expenses' => (float) $expenses,
            'savings' => (float) $savings,
            'rate' => (float) $rate,
        ];
    }

    private function total(
        DateRangeData $data,
        TransactionType $type
    ): float {
        return (float) Transaction::query()
            ->where('user_id', $data->userId)
            ->where('type', $type)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->sum('amount');
    }
}