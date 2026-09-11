<?php

namespace App\Services\Budgets;

use App\Data\Budget\BudgetAvailabilityData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonInterface;

class BudgetSpendingService
{
    public function applicableBudget(int $userId, int $categoryId, CarbonInterface $date, bool $lock = false): ?Budget
    {
        return Budget::query()
            ->where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->with('category:id,name')
            ->first();
    }

    public function spent(Budget $budget, ?int $excludingTransactionId = null, bool $lock = false): float
    {
        return (float) Transaction::query()
            ->where('user_id', $budget->user_id)
            ->where('category_id', $budget->category_id)
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->whereNull('transfer_id')
            ->whereBetween('date', [$budget->start_date, $budget->end_date])
            ->when($excludingTransactionId, fn ($query) => $query->whereKeyNot($excludingTransactionId))
            ->sum('amount');
    }

    public function availability(Budget $budget, ?int $excludingTransactionId = null, bool $lock = false): BudgetAvailabilityData
    {
        $spent = $this->spent($budget, $excludingTransactionId, $lock);
        $category = $budget->category;

        return new BudgetAvailabilityData(
            budgetId: $budget->id,
            budgetName: $budget->name,
            categoryName: $category instanceof Category ? $category->name : 'Unavailable category',
            limit: (float) $budget->amount,
            spent: $spent,
            remaining: (float) $budget->amount - $spent,
        );
    }
}
