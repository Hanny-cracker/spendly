<?php

namespace App\Services\Budgets;

use App\Models\Budget;
use Carbon\CarbonInterface;

class BudgetOverlapChecker
{
    public function exists(
        int $userId,
        int $categoryId,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?int $excludingBudgetId = null,
    ): bool {
        return Budget::query()
            ->where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->when($excludingBudgetId, fn ($query) => $query->whereKeyNot($excludingBudgetId))
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->lockForUpdate()
            ->exists();
    }
}
