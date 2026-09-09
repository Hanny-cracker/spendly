<?php

namespace App\Actions\Goals;

use App\Data\Goal\CreateGoalContributionData;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateGoalContribution
{
    public function __construct(private SynchronizeGoalProgress $synchronizeGoalProgress) {}

    public function handle(Goal $goal, CreateGoalContributionData $data): GoalContribution
    {
        return DB::transaction(function () use ($goal, $data): GoalContribution {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->id);

            if ($lockedGoal->user_id !== $data->userId) {
                throw new AuthorizationException;
            }

            if ($data->accountId !== null && ! Account::query()->whereKey($data->accountId)->where('user_id', $data->userId)->exists()) {
                throw ValidationException::withMessages(['accountId' => 'The selected account is invalid.']);
            }

            $contributionTotal = (float) $lockedGoal->contributions()->sum('amount');
            if ($contributionTotal + $data->amount > $lockedGoal->target_amount) {
                throw ValidationException::withMessages(['amount' => 'The contribution cannot exceed the remaining goal amount.']);
            }

            $contribution = GoalContribution::create([
                'goal_id' => $lockedGoal->id,
                'user_id' => $data->userId,
                'account_id' => $data->accountId,
                'amount' => $data->amount,
                'contributed_at' => $data->contributedAt,
                'note' => $data->note,
            ]);

            $this->synchronizeGoalProgress->handle($lockedGoal);

            return $contribution;
        });
    }
}
