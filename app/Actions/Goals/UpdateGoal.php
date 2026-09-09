<?php

namespace App\Actions\Goals;

use App\Data\Goal\UpdateGoalData;
use App\Enums\GoalStatus;
use App\Models\Goal;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateGoal
{
    public function handle(Goal $goal, UpdateGoalData $data): Goal
    {
        return DB::transaction(function () use ($goal, $data): Goal {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->id);

            if ($lockedGoal->user_id !== $data->userId) {
                throw new AuthorizationException;
            }

            if ($data->targetAmount < $lockedGoal->current_amount) {
                throw ValidationException::withMessages(['targetAmount' => 'Target amount cannot be less than the amount already saved.']);
            }

            $lockedGoal->update([
                'name' => $data->name,
                'description' => $data->description,
                'target_amount' => $data->targetAmount,
                'target_date' => $data->targetDate,
                'status' => $lockedGoal->current_amount >= $data->targetAmount ? GoalStatus::Completed : GoalStatus::Active,
            ]);

            return $lockedGoal->refresh();
        });
    }
}
