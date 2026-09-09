<?php

namespace App\Actions\Accounts;

use App\Data\Account\UpdateAccountData;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class UpdateAccount
{
    public function handle(Account $account, UpdateAccountData $data): Account
    {
        return DB::transaction(function () use ($account, $data): Account {
            if ($data->isDefault) {
                Account::withoutGlobalScopes()
                    ->where('user_id', $account->user_id)
                    ->whereKeyNot($account->id)
                    ->update(['is_default' => false]);
            }

            $account->update([
                'name' => $data->name,
                'type' => $data->type,
                'currency' => $data->currency,
                'color' => $data->color,
                'is_default' => $data->isDefault || $account->is_default,
            ]);

            return $account->refresh();
        });
    }
}
