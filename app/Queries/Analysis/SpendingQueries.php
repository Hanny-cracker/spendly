<?php

namespace App\Queries\Analysis;

use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class SpendingQueries
{
    /**
     * Get completed expense transactions
     * for a date range.
     *
     * @return Collection<int, Transaction>
     */
    public function expenses(
        DateRangeData $data
    ): Collection {

        $data->validate();

        return Transaction::query()
            ->where('user_id', $data->userId)
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->whereNull('transfer_id')
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->with('category')
            ->get();
    }

    /**
     * Get expenses grouped by category.
     *
     * @return Collection<int, Transaction>
     */
    public function expensesByCategory(
        DateRangeData $data
    ): Collection {

        return $this->expenses($data);
    }
}
