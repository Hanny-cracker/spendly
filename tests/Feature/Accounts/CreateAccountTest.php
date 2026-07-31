<?php

use App\Models\Account;
use App\Models\User;


it('can create an account for a user', function () {

    $user = $this->createUser();


    $account = Account::factory()
        ->for($user)
        ->create([
            'name' => 'Bank Account',
            'opening_balance' => 500,
            'current_balance' => 500,
        ]);


    expect($account)
        ->toBeInstanceOf(Account::class);


    expect($account->user_id)
        ->toBe($user->id);


    expect((float) $account->current_balance)
        ->toBe(500.0);
});

it('sets current balance equal to opening balance when created', function () {

    $user = $this->createUser();


    $account = Account::factory()
        ->for($user)
        ->create([
            'opening_balance' => 1000,
            'current_balance' => 1000,
        ]);


    expect($account->current_balance)
        ->toBe($account->opening_balance);
});
