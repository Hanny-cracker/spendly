<?php

namespace App\Actions\Goals;

use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleteGoalContribution
{
    public function __construct(private SynchronizeGoalProgress $synchronizeGoalProgress) {}

    public function handle(Goal $goal, GoalContribution $contribution, int $userId): void
    {
        DB::transaction(function () use ($goal, $contribution, $userId): void {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->id);
            $lockedContribution = GoalContribution::query()->lockForUpdate()->findOrFail($contribution->id);
            if ($lockedGoal->user_id !== $userId || $lockedContribution->user_id !== $userId || $lockedContribution->goal_id !== $lockedGoal->id) {
                throw new AuthorizationException;
            }
            $lockedContribution->delete();
            $this->synchronizeGoalProgress->handle($lockedGoal);
        });
    }
}
