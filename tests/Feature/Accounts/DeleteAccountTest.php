<?php


it('can delete an account without transactions', function () {


    $user = $this->createUser();


    $account = $this->createAccount(
        user:$user
    );


    $account->delete();


    expect(
        \App\Models\Account::find($account->id)
    )
    ->toBeNull();


});

it('cannot delete an account with transactions', function () {

    $user = $this->createUser();

    $account = $this->createAccount(
        user:$user
    );


    $this->createTransaction(
        user:$user,
        account:$account
    );


    $action = app(\App\Actions\Accounts\DeleteAccount::class);


    expect(fn()=> $action->handle($account))
        ->toThrow(Exception::class);


});