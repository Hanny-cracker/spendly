<?php

use App\Models\User;
use App\Models\Account;
use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Data\RecurringTransaction\CreateRecurringTransactionData;
use App\Actions\RecurringTransactions\CreateRecurringTransaction;
use App\Enums\RecurringStatus;
use App\Models\Category;

it('can create a recurring transaction rule', function () {


    $user = User::factory()->create();

    $account = Account::factory()
        ->for($user)
        ->create();
    $category = Category::factory()
    ->for($user)
    ->create();

    $data = new CreateRecurringTransactionData(

        // publicId: fake()->uuid(),
        userId: $user->id,
        accountId: $account->id,
        categoryId: $category->id,
        title: 'Netflix',
        description: 'Monthly subscription',
        amount: 5000,
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
        ->toBe('Netflix')

        ->amount
        ->toBe (5000.0);


    expect($recurring->account_id)
        ->toBe($account->id);


    expect($recurring->category_id)
        ->toBe($category->id);


});