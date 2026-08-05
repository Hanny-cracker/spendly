<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\BudgetStatus;

class BudgetService
{
    /**
     * Total amount spent within the budget period.
     */
    public function spent(Budget $budget): float
    {
        return (float) Transaction::query()
            ->where('user_id', $budget->user_id)
            ->where('category_id', $budget->category_id)
            ->where('type', TransactionType::Expense)
            ->whereDate('date', '>=', $budget->start_date)
            ->whereDate('date', '<=', $budget->end_date)
            ->sum('amount');
    }

    /**
     * Remaining budget.
     */
    public function remaining(Budget $budget): float
    {
        return max(
            0,
            (float) $budget->amount - $this->spent($budget)
        );
    }

    /**
     * Percentage used.
     */
    public function percentageUsed(Budget $budget): float
    {
        if ($budget->amount == 0) {
            return 0;
        }

        return round(
            ($this->spent($budget) / $budget->amount) * 100,
            2
        );
    }

    /**
     * Budget exceeded?
     */
    public function isExceeded(Budget $budget): bool
    {
        return $this->spent($budget) > $budget->amount;
    }

    /**
     * Should notify user?
     */
    public function shouldNotify(Budget $budget): bool
    {
        return $this->percentageUsed($budget)
            >= $budget->alert_percentage;
    }

    /**
     * Budget status.
     */
public function status(Budget $budget): BudgetStatus
{
    $percentage = $this->percentageUsed($budget);

    if ($percentage >= 100) {
        return BudgetStatus::Exceeded;
    }

    if ($percentage >= $budget->alert_percentage) {
        return BudgetStatus::Warning;
    }

    return BudgetStatus::Safe;
}

    /**
     * Complete budget summary.
     */
    public function summary(Budget $budget): array
    {
        return [
            'budget' => (float) $budget->amount,
            'spent' => $this->spent($budget),
            'remaining' => $this->remaining($budget),
            'percentage' => $this->percentageUsed($budget),
            'status' => $this->status($budget),
            'notify' => $this->shouldNotify($budget),
        ];
    }
}