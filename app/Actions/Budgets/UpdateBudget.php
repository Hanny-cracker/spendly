<?php

namespace App\Actions\Budgets;

use App\Data\Budget\UpdateBudgetData;
use App\Enums\CategoryType;
use App\Models\Budget;
use App\Models\Category;
use App\Services\Budgets\BudgetOverlapChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBudget
{
    public function __construct(private BudgetOverlapChecker $overlapChecker) {}

    public function handle(
        Budget $budget,
        UpdateBudgetData $data
    ): Budget {

        // Use existing values when none are supplied
        $startDate = $data->startDate ?? $budget->start_date;
        $endDate = $data->endDate ?? $budget->end_date;
        $period = $data->period ?? $budget->period;
        $categoryId = $data->categoryId ?? $budget->category_id;

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

        return DB::transaction(function () use ($budget, $data, $startDate, $endDate, $period, $categoryId): Budget {
            $category = Category::query()->whereKey($categoryId)->where('user_id', $budget->user_id)->lockForUpdate()->first();
            if (! $category || $category->type !== CategoryType::Expense) {
                throw ValidationException::withMessages(['category' => 'Invalid category.']);
            }

            if ($data->isActive && $this->overlapChecker->exists($budget->user_id, $categoryId, $startDate, $endDate, $budget->id)) {
                throw ValidationException::withMessages(['budget' => "A budget already exists for {$category->name} during this period."]);
            }

            $budget->update(['name' => $data->name, 'category_id' => $categoryId, 'amount' => $data->amount, 'period' => $period, 'start_date' => $startDate, 'end_date' => $endDate, 'alert_percentage' => $data->alertPercentage, 'is_active' => $data->isActive]);

            return $budget->refresh();
        });
    }
}
