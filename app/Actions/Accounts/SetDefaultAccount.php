<?php

namespace App\Actions\Accounts;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class SetDefaultAccount
{
    public function handle(Account $account): Account
    {
        return DB::transaction(function () use ($account): Account {
            Account::withoutGlobalScopes()
                ->where('user_id', $account->user_id)
                ->whereKeyNot($account->id)
                ->update(['is_default' => false]);

            $account->update(['is_default' => true]);

            return $account->refresh();
        });
    }
}
