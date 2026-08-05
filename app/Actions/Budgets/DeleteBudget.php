<?php

namespace App\Actions\Budgets;

use App\Models\Budget;

class DeleteBudget
{
    public function handle(Budget $budget): bool
    {
        return (bool) $budget->delete();
    }
}