<?php

namespace App\Actions\Analysis;

use App\Data\Analysis\BudgetProgressData;
use App\Data\Report\DateRangeData;
use App\Models\Budget;
use App\Services\Budgets\BudgetSpendingService;

class BudgetProgressAnalysis
{
    public function __construct(private BudgetSpendingService $spendingService) {}

    /**
     * Analyze budget progress for a date range.
     *
     * @return array<int, BudgetProgressData>
     */
    public function handle(
        DateRangeData $data
    ): array {
        $data->validate();

        $budgets = Budget::query()
            ->where('user_id', $data->userId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $data->endDate)
            ->whereDate('end_date', '>=', $data->startDate)
            ->with('category')
            ->get();

        if ($budgets->isEmpty()) {
            return [];
        }

        $results = [];

        foreach ($budgets as $budget) {
            $spentAmount = $this->spendingService->spent($budget);

            $budgetAmount = (float) $budget->amount;

            $remainingAmount = $budgetAmount - $spentAmount;

            $percentageUsed = $budgetAmount > 0
                ? ($spentAmount / $budgetAmount) * 100
                : 0.0;

            $status = match (true) {
                $percentageUsed >= 100 => 'over_budget',
                $percentageUsed >= $budget->alert_percentage => 'warning',
                default => 'on_track',
            };

            $results[] = new BudgetProgressData(
                budgetId: $budget->id,
                budgetName: $budget->name,
                budgetAmount: $budgetAmount,
                spentAmount: $spentAmount,
                remainingAmount: $remainingAmount,
                percentageUsed: $percentageUsed,
                status: $status,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Highest budget usage first
        |--------------------------------------------------------------------------
        */

        usort(
            $results,
            fn (
                BudgetProgressData $a,
                BudgetProgressData $b
            ) => $b->percentageUsed <=> $a->percentageUsed
        );

        return $results;
    }
}
