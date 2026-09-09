<?php

namespace App\Actions\Accounts;

use App\Data\Account\CreateAccountData;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class CreateAccount
{
    public function handle(
        CreateAccountData $data
    ): Account {

        return DB::transaction(function () use ($data): Account {
            if ($data->isDefault) {
                Account::withoutGlobalScopes()
                    ->where('user_id', $data->userId)
                    ->update(['is_default' => false]);
            }

            return Account::firstOrCreate(
                ['user_id' => $data->userId, 'name' => $data->name],
                [
                    'type' => $data->type,
                    'currency' => $data->currency,
                    'opening_balance' => $data->openingBalance,
                    'current_balance' => $data->openingBalance,
                    'color' => $data->color,
                    'is_default' => $data->isDefault,
                ],
            );
        });
    }
}
