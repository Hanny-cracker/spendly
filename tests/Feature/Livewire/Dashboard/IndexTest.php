<?php

use App\Livewire\Dashboard\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the expense dashboard at the dashboard route', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Financial overview')
        ->assertSee('My accounts')
        ->assertSee('Recent transactions');

});

it('renders expense and deposit quick actions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Expense')
        ->assertSee('Deposit')
        ->assertSee(route('transactions.create', ['type' => 'expense']), false)
        ->assertSee(route('transactions.create', ['type' => 'income']), false);
});

it('renders the dashboard', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.dashboard.index');
});

it('uses the authenticated user when loading the dashboard', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet(
            'dashboardData',
            function ($dashboard) {

                expect($dashboard)
                    ->toBeArray()
                    ->not->toBeEmpty();

                return true;
            }
        );
});

it('uses the current month as the default date range', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet(
            'startDate',
            now()->startOfMonth()->toDateString()
        )
        ->assertSet(
            'endDate',
            now()->endOfMonth()->toDateString()
        );
});

it('can change the date range', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-01-31')
        ->assertSet(
            'startDate',
            '2026-01-01'
        )
        ->assertSet(
            'endDate',
            '2026-01-31'
        );
});

it('reloads dashboard data when the date range changes', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-01-31')
        ->assertSet(
            'dashboardData',
            function ($dashboard) {

                expect($dashboard)
                    ->toBeArray()
                    ->not->toBeEmpty();

                expect($dashboard)
                    ->toHaveKeys([
                        'start_date',
                        'end_date',
                        'total_balance',
                        'reports',
                        'insights',
                        'budgets',
                        'accounts',
                        'recent_transactions',
                    ]);

                expect($dashboard['start_date'])
                    ->toBe('2026-01-01');

                expect($dashboard['end_date'])
                    ->toBe('2026-01-31');

                return true;
            }
        );
});

it('does not expose another users dashboard data', function () {

    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    $userCategory = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    $otherUserCategory = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $userCategory->id,
        'title' => 'Visible transaction',
    ]);

    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $otherUserCategory->id,
        'title' => 'Private transaction',
    ]);

    $this->actingAs($user);

    $dashboard = Livewire::test(Index::class)
        ->get('dashboardData');

    expect($dashboard)
        ->toBeArray()
        ->not->toBeEmpty();

    $transactions = collect(
        $dashboard['recent_transactions']
    );

    expect($transactions)
        ->toHaveCount(1);

    expect($transactions->first())
        ->not->toHaveKey('user_id');

    Livewire::test(Index::class)
        ->assertSee('Visible transaction')
        ->assertDontSee('Private transaction');
});

it('renders the users accounts and recent transactions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = Account::factory()->for($user)->create([
        'name' => 'Daily Wallet',
        'current_balance' => 125000,
        'currency' => 'FCFA',
    ]);

    Account::factory()->for($otherUser)->create([
        'name' => 'Private Wallet',
    ]);

    $category = Category::factory()->for($user)->create([
        'name' => 'Groceries',
    ]);

    Transaction::factory()->for($user)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Market shopping',
        'amount' => 18500,
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('Daily Wallet')
        ->assertSee('125,000')
        ->assertSeeHtml('scrollbar-hidden flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2')
        ->assertSeeHtml('w-[82%] shrink-0 snap-start')
        ->assertSee('Market shopping')
        ->assertSee('Groceries')
        ->assertDontSee('Private Wallet');
});

it('renders the recent transactions empty state', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('No transactions yet')
        ->assertSee('Your recent financial activity will appear here.');
});
