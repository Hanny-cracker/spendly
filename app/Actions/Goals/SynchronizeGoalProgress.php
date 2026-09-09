<?php

namespace App\Actions\Goals;

use App\Enums\GoalStatus;
use App\Models\Goal;

class SynchronizeGoalProgress
{
    public function handle(Goal $goal): Goal
    {
        $currentAmount = (float) $goal->contributions()->sum('amount');
        $goal->forceFill([
            'current_amount' => $currentAmount,
            'status' => $currentAmount >= $goal->target_amount ? GoalStatus::Completed : GoalStatus::Active,
        ])->save();

        return $goal->refresh();
    }
}
