<?php

namespace App\Actions\Goals;

use App\Models\Goal;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleteGoal
{
    public function handle(Goal $goal, int $userId): void
    {
        DB::transaction(function () use ($goal, $userId): void {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->id);
            if ($lockedGoal->user_id !== $userId) {
                throw new AuthorizationException;
            }
            $lockedGoal->delete();
        });
    }
}
