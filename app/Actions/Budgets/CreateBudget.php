<?php

namespace App\Actions\Budgets;

use App\Data\Budget\CreateBudgetData;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

class CreateBudget
{
    public function handle(CreateBudgetData $data): Budget
    {
        // Validation

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

        // Ensure category belongs to user

        $category = Category::query()
            ->whereKey($data->categoryId)
            ->where('user_id', $data->userId)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category' => 'Invalid category.',
            ]);
        }

        // Prevent duplicate budgets

        $exists = Budget::query()
            ->where('user_id', $data->userId)
            ->where('category_id', $data->categoryId)
            ->where('start_date', $data->startDate)
            ->where('end_date', $data->endDate)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'budget' => 'A budget already exists for this period.',
            ]);
        }

        $overlap = Budget::query()
            ->where('user_id', $data->userId)
            ->where('category_id', $data->categoryId)
            ->where(function ($query) use ($data) {
                $query
                    ->whereBetween('start_date', [$data->startDate, $data->endDate])
                    ->orWhereBetween('end_date', [$data->startDate, $data->endDate])
                    ->orWhere(function ($query) use ($data) {
                        $query
                            ->where('start_date', '<=', $data->startDate)
                            ->where('end_date', '>=', $data->endDate);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'budget' => 'A budget already overlaps this period.',
            ]);
        }

        // Create Budget

        return Budget::create($data->toArray());
    }
}
