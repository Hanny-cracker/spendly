<?php

namespace App\Services\Budgets;

use App\Data\Budget\BudgetAvailabilityData;
use App\Exceptions\BudgetExceededException;
use Carbon\CarbonInterface;

class BudgetSpendingGuard
{
    public function __construct(private BudgetSpendingService $spendingService) {}

    public function inspect(int $userId, int $categoryId, CarbonInterface $date, ?int $excludingTransactionId = null): ?BudgetAvailabilityData
    {
        $budget = $this->spendingService->applicableBudget($userId, $categoryId, $date);

        return $budget ? $this->spendingService->availability($budget, $excludingTransactionId) : null;
    }

    public function assertCanSpend(int $userId, int $categoryId, CarbonInterface $date, float $amount, ?int $excludingTransactionId = null): ?BudgetAvailabilityData
    {
        $budget = $this->spendingService->applicableBudget($userId, $categoryId, $date, lock: true);
        if (! $budget) {
            return null;
        }

        $current = $this->spendingService->availability($budget, $excludingTransactionId, lock: true);
        $result = new BudgetAvailabilityData(
            budgetId: $current->budgetId,
            budgetName: $current->budgetName,
            categoryName: $current->categoryName,
            limit: $current->limit,
            spent: $current->spent,
            remaining: $current->remaining,
            requested: $amount,
            shortfall: max($amount - $current->remaining, 0),
        );

        if ($result->shortfall > 0) {
            throw new BudgetExceededException($result);
        }

        return $result;
    }
}
