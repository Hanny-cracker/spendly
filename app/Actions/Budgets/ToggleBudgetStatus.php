<?php

namespace App\Actions\Budgets;

use App\Data\Budget\UpdateBudgetData;
use App\Models\Budget;

class ToggleBudgetStatus
{
    public function __construct(private UpdateBudget $updateBudget) {}

    public function handle(Budget $budget): Budget
    {
        return $this->updateBudget->handle($budget, new UpdateBudgetData(
            name: $budget->name,
            categoryId: $budget->category_id,
            amount: $budget->amount,
            alertPercentage: $budget->alert_percentage,
            isActive: ! $budget->is_active,
            period: $budget->period,
            startDate: $budget->start_date,
            endDate: $budget->end_date,
        ));
    }
}
