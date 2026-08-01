<?php

use App\Models\User;
use App\Models\Account;
use App\Models\RecurringTransaction;

use App\Data\RecurringTransaction\UpdateRecurringTransactionData;

use App\Actions\RecurringTransactions\UpdateRecurringTransaction;


it('can update a recurring transaction', function () {


    $user = User::factory()->create();

    $account = Account::factory()
        ->for($user)
        ->create();

    $recurring = RecurringTransaction::factory()
        ->for($user)
        ->for($account)
        ->create([
            'title' => 'Netflix',
            'amount' => 15,
        ]);



    $data = new UpdateRecurringTransactionData(
        amount: 20,
        title: 'Netflix Premium',

    );



    $updated = app(UpdateRecurringTransaction::class)
        ->handle(
            $recurring,
            $data
        );



    expect($updated->amount)
        ->toBe('20.00');


    expect($updated->title)
        ->toBe('Netflix Premium');

});