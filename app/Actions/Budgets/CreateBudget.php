<?php

namespace App\Actions\Budgets;

use App\Data\Budget\CreateBudgetData;
use App\Models\Budget;
use App\Models\Category;
use App\Services\Budgets\BudgetOverlapChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBudget
{
    public function __construct(private BudgetOverlapChecker $overlapChecker) {}

    public function handle(CreateBudgetData $data): Budget
    {
        if ($data->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Budget amount must be greater than zero.',
            ]);
        }

        if ($data->endDate->lt($data->startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be after the start date.',
            ]);
        }

        return DB::transaction(function () use ($data): Budget {
            /** @var Category|null $category */
            $category = Category::query()->whereKey($data->categoryId)->where('user_id', $data->userId)->lockForUpdate()->first();
            if (! $category || ! $category->type->isExpense()) {
                throw ValidationException::withMessages(['category' => 'Invalid category.']);
            }

            if ($data->isActive && $this->overlapChecker->exists($data->userId, $data->categoryId, $data->startDate, $data->endDate)) {
                throw ValidationException::withMessages(['budget' => "A budget already exists for {$category->name} during this period."]);
            }

            return Budget::create($data->toArray());
        });
    }
}
