<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\Transfer;
use App\Models\Account;

trait CreatesTransfers
{
    protected function createTransfer(

        User $user,
        Account $from,
        Account $to,
        array $attributes = [],
    ): Transfer {

        return Transfer::factory()
            ->for($user)
            ->for($from, 'fromAccount')
            ->for($to, 'toAccount')
            ->create(array_merge([
                'amount' => 100,
                'date' => now(),
            ], $attributes));

    }
}