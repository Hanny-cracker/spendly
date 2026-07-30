<?php

namespace App\Actions\Accounts;

use App\Data\Account\CreateAccountData;
use App\Models\Account;


class CreateAccount
{

    public function handle(
        CreateAccountData $data
    ): Account {

        return Account::firstOrCreate(

            [
                'user_id' => $data->userId,
                'name' => $data->name,
            ],

            [

                'type' => $data->type,
                'currency' => $data->currency,
                'opening_balance' => $data->openingBalance,
                'current_balance' => $data->openingBalance,
                'color' => $data->color,
                'is_default' => $data->isDefault,

            ]

        );
    }
}
