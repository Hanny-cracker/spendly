<?php

use App\Actions\Transactions\CreateTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;



test('creating an expense decreases account balance', function(){



    // Arrange

    $user = User::factory()->create();


    $account = Account::factory()
        ->for($user)
        ->create([
            'current_balance'=>1000
        ]);



    $data = new CreateTransactionData(
        userId:$user->id,
        accountId:$account->id,
        categoryId:null,
        parentTransactionId:null,
        title:'Food',
        description:null,
        amount:500,
        type:TransactionType::Expense,
        date:Carbon::today(),
        status:TransactionStatus::Completed
    );



    // Act

    app(CreateTransaction::class)
        ->handle($data);



    // Refresh database data

    $account->refresh();



    // Assert

    expect(
        $account->current_balance
    )->toBe(500.0);



});

test('creating an expense increes account balance', function(){



    // Arrange

    $user = User::factory()->create();


    $account = Account::factory()
        ->for($user)
        ->create([
            'current_balance'=>1000
        ]);



    $data = new CreateTransactionData(
        userId:$user->id,
        accountId:$account->id,
        categoryId:null,
        parentTransactionId:null,
        title:'Food',
        description:null,
        amount:500,
        type:TransactionType::Income,
        date:Carbon::today(),
        status:TransactionStatus::Completed
    );



    // Act

    app(CreateTransaction::class)
        ->handle($data);



    // Refresh database data

    $account->refresh();



    // Assert

    expect(
        $account->current_balance
    )->toBe(1500.0);



});