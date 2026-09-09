<?php

use App\Enums\RecurringStatus;
use App\Livewire\Recurring\Show;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Livewire\Livewire;

it('pauses and resumes without generating missed transactions or changing balances', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $category = Category::factory()->for($user)->expense()->create();
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['next_run' => now()->subMonths(3)]);
    $this->actingAs($user);
    Livewire::test(Show::class, ['recurringTransaction' => $schedule])->call('pause')->assertHasNoErrors();
    $this->artisan('transactions:generate-recurring')->assertSuccessful();
    expect(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(0)->and($account->refresh()->current_balance)->toBe(500000.0);
    Livewire::test(Show::class, ['recurringTransaction' => $schedule->refresh()])->call('resume')->assertHasNoErrors();
    expect($schedule->refresh()->status)->toBe(RecurringStatus::Active)->and($schedule->next_run->isFuture())->toBeTrue()->and(Transaction::query()->where('recurring_transaction_id', $schedule->id)->count())->toBe(0);
});

it('deletes a schedule while preserving generated transactions and balances', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $category = Category::factory()->for($user)->expense()->create();
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create();
    $transaction = app(RecurringTransactionService::class)->generate($schedule);
    $balance = $account->refresh()->current_balance;
    $this->actingAs($user);
    Livewire::test(Show::class, ['recurringTransaction' => $schedule->refresh()])->set('confirmingDeletion', true)->call('delete')->assertRedirect(route('recurring'));
    $this->assertModelMissing($schedule);
    expect($transaction->refresh())->not->toBeNull()->and($transaction->recurring_transaction_id)->toBeNull()->and($account->refresh()->current_balance)->toBe($balance);
});
