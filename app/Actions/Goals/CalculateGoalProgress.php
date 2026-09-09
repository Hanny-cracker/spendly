<?php

namespace App\Actions\Goals;

use App\Data\Goal\GoalProgressData;
use App\Enums\GoalStatus;
use App\Models\Goal;

class CalculateGoalProgress
{
    public function handle(Goal $goal): GoalProgressData
    {
        $completed = $goal->current_amount >= $goal->target_amount;
        $remaining = max($goal->target_amount - $goal->current_amount, 0);
        $percentage = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;

        return new GoalProgressData(
            goalId: $goal->id, publicId: $goal->public_id, name: $goal->name, description: $goal->description,
            targetAmount: $goal->target_amount, currentAmount: $goal->current_amount, remainingAmount: $remaining, percentage: $percentage,
            status: $completed ? GoalStatus::Completed->value : GoalStatus::Active->value,
            targetDate: $goal->target_date?->format('d M Y'), isOverdue: ! $completed && $goal->target_date?->isPast() === true,
        );
    }
}
