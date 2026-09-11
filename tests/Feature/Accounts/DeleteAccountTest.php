<?php

use App\Actions\Accounts\DeleteAccount;
use App\Models\Account;

it('can delete an account without transactions', function () {

    $user = $this->createUser();

    $account = $this->createAccount(
        user: $user
    );

    $account->delete();

    expect(
        Account::find($account->id)
    )
        ->toBeNull();

});

it('cannot delete an account with transactions', function () {

    $user = $this->createUser();

    $account = $this->createAccount(
        user: $user
    );

    $this->createTransaction(
        user: $user,
        account: $account
    );

    $action = app(DeleteAccount::class);

    expect(fn () => $action->handle($account))
        ->toThrow(Exception::class);

});
