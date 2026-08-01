<?php

use App\Models\User;
use App\Models\Account;
use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Data\RecurringTransaction\CreateRecurringTransactionData;
use App\Actions\RecurringTransactions\CreateRecurringTransaction;
use App\Enums\RecurringStatus;


it('can create a recurring transaction rule', function () {


    $user = User::factory()->create();

    $account = Account::factory()
        ->for($user)
        ->create();

    $data = new CreateRecurringTransactionData(

        publicId: fake()->uuid(),
        userId: $user->id,
        accountId: $account->id,
        categoryId: null,
        title: 'Netflix',
        description: 'Monthly subscription',
        amount: 15,
        type: TransactionType::Expense,
        frequency: RecurringFrequency::Monthly,
        interval: 1,
        startDate: now(),
        nextRun: now()->addMonth(),
        endDate: null,
        status: RecurringStatus::Active

    );


    $recurring = app(CreateRecurringTransaction::class)
        ->handle($data);


    expect($recurring)

        ->title
        ->toBe('Netflix');


    expect($recurring->frequency)

        ->toBe(RecurringFrequency::Monthly);


    expect($recurring->status)

        ->toBe(\App\Enums\RecurringStatus::Active);


});