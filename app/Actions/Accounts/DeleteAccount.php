<?php

namespace App\Actions\Accounts;

use App\Models\Account;
use Exception;


class DeleteAccount
{

    public function handle(Account $account): void
    {

        if ($account->transactions()->exists()) {

            throw new Exception(
                'Cannot delete account with transactions'
            );

        }


        $account->delete();

    }

}