<?php

namespace App\Actions\Budgets;

use App\Models\Budget;
use Illuminate\Support\Facades\DB;

class DeleteBudget
{
    public function handle(Budget $budget): bool
    {
        return DB::transaction(fn (): bool => (bool) $budget->delete());
    }
}
