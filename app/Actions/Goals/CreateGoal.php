<?php

namespace App\Actions\Goals;

use App\Data\Goal\CreateGoalData;
use App\Enums\GoalStatus;
use App\Models\Goal;

class CreateGoal
{
    public function handle(CreateGoalData $data): Goal
    {
        return Goal::create(['user_id' => $data->userId, 'name' => $data->name, 'description' => $data->description, 'target_amount' => $data->targetAmount, 'current_amount' => 0, 'target_date' => $data->targetDate, 'status' => GoalStatus::Active]);
    }
}
