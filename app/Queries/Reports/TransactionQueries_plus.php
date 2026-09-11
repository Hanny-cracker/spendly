<?php

namespace App\Queries\Reports;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class TransactionQueries_plus
{
    /**
     * Base query for a user's transactions within a date range.
     */
    protected function base(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): Builder {
        return Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [
                $startDate,
                $endDate,
            ])
            ->where('status', TransactionStatus::Completed);
    }

    /**
     * Get total expenses.
     */
    public function totalExpenses(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): float {
        return (float) $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->where('type', TransactionType::Expense)
            ->sum('amount');
    }

    /**
     * Get total income.
     */
    public function totalIncome(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): float {
        return (float) $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->where('type', TransactionType::Income)
            ->sum('amount');
    }

    /**
     * Count expenses.
     */
    public function expenseCount(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): int {
        return $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->where('type', TransactionType::Expense)
            ->count();
    }

    /**
     * Count income transactions.
     */
    public function incomeCount(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): int {
        return $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->where('type', TransactionType::Income)
            ->count();
    }

    /**
     * Get expenses grouped by category.
     */
    public function expensesByCategory(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ) {
        return $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->where('type', TransactionType::Expense)
            ->whereNotNull('category_id')
            ->with('category')
            ->selectRaw(
                'category_id, SUM(amount) as total, COUNT(*) as transaction_count'
            )
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Get income grouped by category.
     */
    public function incomeByCategory(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ) {
        return $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->where('type', TransactionType::Income)
            ->whereNotNull('category_id')
            ->with('category')
            ->selectRaw(
                'category_id, SUM(amount) as total, COUNT(*) as transaction_count'
            )
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Get transactions for a user within a date range.
     */
    public function forDateRange(
        int $userId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ) {
        return $this->base(
            $userId,
            $startDate,
            $endDate
        )
            ->with(['category', 'account'])
            ->latest('date')
            ->get();
    }
}
