<?php

use App\Enums\CategoryType;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Livewire\Recurring\Create;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('protects the create page', function () {
    $this->get(route('recurring.create'))->assertRedirect(route('login'));
});

it('creates an owned expense schedule without financial side effects', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $startDate = now()->addDay()->toDateString();
    $this->actingAs($user);

    Livewire::test(Create::class)->set('type', 'expense')->set('title', 'Internet Subscription')->set('amount', '25000')->set('accountId', (string) $account->id)->set('categoryId', (string) $category->id)->set('frequency', 'monthly')->set('startDate', $startDate)->set('scheduledTime', '14:30')->call('save')->assertHasNoErrors()->assertRedirect(route('recurring'));
    $schedule = RecurringTransaction::query()->where('title', 'Internet Subscription')->firstOrFail();
    expect($schedule->user_id)->toBe($user->id)->and($schedule->next_run->toDateString())->toBe($startDate)->and($schedule->next_run->format('H:i'))->toBe('14:30')->and(substr($schedule->scheduled_time, 0, 5))->toBe('14:30')->and($schedule->status)->toBe(RecurringStatus::Active)->and($account->refresh()->current_balance)->toBe(500000.0)->and(Transaction::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('creates an income schedule', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    $this->actingAs($user);
    Livewire::test(Create::class)->set('type', 'income')->set('title', 'Salary')->set('amount', '450000')->set('accountId', (string) $account->id)->set('categoryId', (string) $category->id)->set('frequency', 'monthly')->set('startDate', now()->addDay()->toDateString())->call('save')->assertHasNoErrors();
    expect(RecurringTransaction::query()->where('title', 'Salary')->firstOrFail()->type)->toBe(TransactionType::Income);
});

it('validates schedule fields and ownership', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $account = Account::factory()->for($other)->create();
    $category = Category::factory()->for($other)->create(['type' => CategoryType::Expense]);
    $this->actingAs($user);
    Livewire::test(Create::class)->set('title', 'Invalid')->set('amount', '0')->set('accountId', (string) $account->id)->set('categoryId', (string) $category->id)->set('frequency', 'invalid')->set('startDate', now()->subDay()->toDateString())->set('endDate', now()->subDays(2)->toDateString())->call('save')->assertHasErrors(['amount', 'accountId', 'categoryId', 'frequency', 'startDate', 'endDate']);
});

it('rejects a category that does not match the transaction type', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $incomeCategory = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    $this->actingAs($user);
    Livewire::test(Create::class)->set('type', 'expense')->set('title', 'Invalid category')->set('amount', '1000')->set('accountId', (string) $account->id)->set('categoryId', (string) $incomeCategory->id)->set('startDate', now()->addDay()->toDateString())->call('save')->assertHasErrors(['categoryId']);
});

it('requires a valid scheduled time', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)->set('scheduledTime', '')->call('save')->assertHasErrors(['scheduledTime']);
    $this->get(route('recurring.create'))->assertSee('type="time"', false);
});
