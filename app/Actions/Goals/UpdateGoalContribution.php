<?php

namespace App\Actions\Goals;

use App\Data\Goal\UpdateGoalContributionData;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateGoalContribution
{
    public function __construct(private SynchronizeGoalProgress $synchronizeGoalProgress) {}

    public function handle(Goal $goal, GoalContribution $contribution, UpdateGoalContributionData $data): GoalContribution
    {
        return DB::transaction(function () use ($goal, $contribution, $data): GoalContribution {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->id);
            $lockedContribution = GoalContribution::query()->lockForUpdate()->findOrFail($contribution->id);

            if ($lockedGoal->user_id !== $data->userId || $lockedContribution->user_id !== $data->userId || $lockedContribution->goal_id !== $lockedGoal->id) {
                throw new AuthorizationException;
            }
            if ($data->accountId !== null && ! Account::query()->whereKey($data->accountId)->where('user_id', $data->userId)->exists()) {
                throw ValidationException::withMessages(['accountId' => 'The selected account is invalid.']);
            }

            $otherContributions = (float) $lockedGoal->contributions()->whereKeyNot($lockedContribution->id)->sum('amount');
            if ($otherContributions + $data->amount > $lockedGoal->target_amount) {
                throw ValidationException::withMessages(['amount' => 'The contribution cannot exceed the remaining goal amount.']);
            }

            $lockedContribution->update(['account_id' => $data->accountId, 'amount' => $data->amount, 'contributed_at' => $data->contributedAt, 'note' => $data->note]);
            $this->synchronizeGoalProgress->handle($lockedGoal);

            return $lockedContribution->refresh();
        });
    }
}
