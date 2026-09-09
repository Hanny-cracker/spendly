<?php

use App\Livewire\Recurring\Edit;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Livewire\Livewire;

it('edits future configuration without changing history or balances', function () {
    $user = User::factory()->create();
    $accountA = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $accountB = Account::factory()->for($user)->create(['current_balance' => 200000]);
    $category = Category::factory()->for($user)->expense()->create();
    $schedule = RecurringTransaction::factory()->for($user)->for($accountA)->for($category)->create(['amount' => 25000]);
    $oldTransaction = app(RecurringTransactionService::class)->generate($schedule);
    $balanceA = $accountA->refresh()->current_balance;
    $this->actingAs($user);

    Livewire::test(Edit::class, ['recurringTransaction' => $schedule->refresh()])->set('amount', '30000')->set('accountId', (string) $accountB->id)->call('save')->assertHasNoErrors()->assertRedirect(route('recurring.show', $schedule));
    expect($oldTransaction->refresh()->amount)->toBe(25000.0)->and($oldTransaction->account_id)->toBe($accountA->id)->and($accountA->refresh()->current_balance)->toBe($balanceA)->and($accountB->refresh()->current_balance)->toBe(200000.0);
    $newTransaction = app(RecurringTransactionService::class)->generate($schedule->refresh());
    expect($newTransaction->amount)->toBe(30000.0)->and($newTransaction->account_id)->toBe($accountB->id);
});

it('blocks type and start date changes after history exists', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $expense = Category::factory()->for($user)->expense()->create();
    $income = Category::factory()->for($user)->income()->create();
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($expense)->create();
    app(RecurringTransactionService::class)->generate($schedule);
    $this->actingAs($user);
    Livewire::test(Edit::class, ['recurringTransaction' => $schedule->refresh()])->set('type', 'income')->set('categoryId', (string) $income->id)->call('save')->assertHasErrors(['type']);
});
