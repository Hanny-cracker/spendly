<?php

namespace App\Actions\Budgets;

use App\Data\Budget\UpdateBudgetData;
use App\Models\Budget;
use Illuminate\Validation\ValidationException;

class UpdateBudget
{
    public function handle(
        Budget $budget,
        UpdateBudgetData $data
    ): Budget {

        // Use existing values when none are supplied
        $startDate = $data->startDate ?? $budget->start_date;
        $endDate   = $data->endDate ?? $budget->end_date;
        $period    = $data->period ?? $budget->period;

        if ($data->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Budget amount must be greater than zero.',
            ]);
        }

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be after the start date.',
            ]);
        }

        $budget->update([
            'name' => $data->name,
            'amount' => $data->amount,
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'alert_percentage' => $data->alertPercentage,
            'is_active' => $data->isActive,
        ]);

        return $budget->refresh();
    }
}