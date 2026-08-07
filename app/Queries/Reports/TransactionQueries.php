<?php

namespace App\Queries\Reports;

use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionQueries
{
    /**
     * Get completed transactions for a user
     * within a specific date range.
     */
    public function forDateRange(
        DateRangeData $data,
        ?TransactionType $type = null,
    ): Collection {
        $data->validate();

        return Transaction::query()
            ->where('user_id', $data->userId)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type)
            )
            ->get();
    }

    /**
     * Get completed expense transactions.
     */
    public function expenses(
        DateRangeData $data
    ): Collection {
        return $this->forDateRange(
            $data,
            TransactionType::Expense
        );
    }

    /**
     * Get completed income transactions.
     */
    public function income(
        DateRangeData $data
    ): Collection {
        return $this->forDateRange(
            $data,
            TransactionType::Income
        );
    }

    /**
     * Get completed transactions with their categories.
     */
    public function withCategories(
        DateRangeData $data,
        ?TransactionType $type = null,
    ): Collection {
        $data->validate();

        return Transaction::query()
            ->where('user_id', $data->userId)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('date', [
                $data->startDate,
                $data->endDate,
            ])
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type)
            )
            ->with('category')
            ->get();
    }
}