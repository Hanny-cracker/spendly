<?php

use App\Models\User;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;


it('prevents users from viewing another users transaction', function () {

    $john = User::factory()->create();

    $mary = User::factory()->create();


    $account = Account::factory()
        ->forUser($mary)
        ->create();


    $category = Category::factory()
        ->forUser($mary)
        ->create();


    $transaction = Transaction::factory()
        ->forUser($mary)
        ->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);
        


    expect(
        $john->can('view', $transaction)
    )->toBeFalse();

});