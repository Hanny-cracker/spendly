<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAccount
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $userId = $user->getKey();

            DB::table('notifications')
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $userId)
                ->delete();

            DB::table('transactions')->where('user_id', $userId)->delete();
            DB::table('transfers')->where('user_id', $userId)->delete();
            DB::table('budgets')->where('user_id', $userId)->delete();
            DB::table('recurring_transactions')->where('user_id', $userId)->delete();
            DB::table('goal_contributions')->where('user_id', $userId)->delete();
            DB::table('goals')->where('user_id', $userId)->delete();
            DB::table('user_preferences')->where('user_id', $userId)->delete();
            DB::table('accounts')->where('user_id', $userId)->delete();
            DB::table('categories')->where('user_id', $userId)->delete();
            DB::table('sessions')->where('user_id', $userId)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            $user->delete();
        });
    }
}
