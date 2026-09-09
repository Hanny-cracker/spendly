<?php

use App\Data\Analysis\BudgetProgressData;
use App\Data\Dashboard\AccountSummaryData;
use App\Data\Dashboard\TransactionSummaryData;
use App\Data\Report\DateRangeData;
use App\Enums\AccountType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('generates complete dashboard data', function () {

    $user = User::factory()->create();

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->startDate->toDateString())
        ->toBe('2026-01-01');

    expect($result->endDate->toDateString())
        ->toBe('2026-01-31');

    expect($result->reports)
        ->toHaveKeys([
            'expense',
            'income',
            'cash_flow',
            'categories',
        ]);

    expect($result->insights)
        ->toHaveKeys([
            'spending',
            'income',
            'cash_flow',
            'categories',
            'savings_rate',
            'spending_trend',
            'spending_concentration',
            'financial_health',
        ]);

    expect($result->budgets)
        ->toBeArray();

    expect($result->accounts)
        ->toBeArray();

    expect($result->totalBalance)
        ->toBeFloat();

    expect($result->recentTransactions)
        ->toBeArray();
});

it('only returns budget progress belonging to the user', function () {

    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    Budget::factory()
        ->count(2)
        ->create([
            'user_id' => $user->id,
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

    Budget::factory()->create([
        'user_id' => $otherUser->id,
        'is_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->budgets)
        ->toHaveCount(2);

    expect(
        collect($result->budgets)
            ->every(
                fn (BudgetProgressData $budget) => $budget instanceof BudgetProgressData
            )
    )->toBeTrue();
});

it('excludes inactive budgets', function () {

    $user = User::factory()->create();

    Budget::factory()->create([
        'user_id' => $user->id,
        'is_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'is_active' => false,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->budgets)
        ->toHaveCount(1);

    expect($result->budgets[0])
        ->toBeInstanceOf(BudgetProgressData::class);
});

it('returns calculated budget progress', function () {

    $user = User::factory()->create();

    $budget = Budget::factory()->create([
        'user_id' => $user->id,
        'name' => 'Food Budget',
        'amount' => 100000,
        'alert_percentage' => 70,
        'is_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $budget->category_id,
        'amount' => 70000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-15',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->budgets)
        ->toHaveCount(1);

    expect($result->budgets[0]->budgetId)
        ->toBe($budget->id);

    expect($result->budgets[0]->budgetName)
        ->toBe('Food Budget');

    expect($result->budgets[0]->budgetAmount)
        ->toBe(100000.0);

    expect($result->budgets[0]->spentAmount)
        ->toBe(70000.0);

    expect($result->budgets[0]->remainingAmount)
        ->toBe(30000.0);

    expect($result->budgets[0]->percentageUsed)
        ->toBe(70.0);

    expect($result->budgets[0]->status)
        ->toBe('warning');
});

it('only returns recent transactions belonging to the user', function () {

    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    $userTransactions = Transaction::factory()
        ->count(3)
        ->create([
            'user_id' => $user->id,
        ]);

    $otherTransactions = Transaction::factory()
        ->count(2)
        ->create([
            'user_id' => $otherUser->id,
        ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->recentTransactions)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(TransactionSummaryData::class);

    $resultIds = collect($result->recentTransactions)->pluck('id')->sort()->values()->all();

    expect($resultIds)
        ->toBe($userTransactions->pluck('id')->sort()->values()->all())
        ->and(array_intersect($resultIds, $otherTransactions->pluck('id')->all()))
        ->toBe([]);
});

it('limits recent transactions to ten', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->count(15)
        ->create([
            'user_id' => $user->id,
        ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->recentTransactions)
        ->toHaveCount(10);
});

it('orders recent transactions newest first', function () {

    $user = User::factory()->create();

    $old = Transaction::factory()->create([
        'user_id' => $user->id,
        'date' => '2026-01-01',
    ]);

    $new = Transaction::factory()->create([
        'user_id' => $user->id,
        'date' => '2026-01-20',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(DashboardService::class)
        ->summary($data);

    expect($result->recentTransactions[0]->id)
        ->toBe($new->id);

    expect($result->recentTransactions[1]->id)
        ->toBe($old->id);
});

it('rejects an invalid date range', function () {

    $user = User::factory()->create();

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-02-01'),
        endDate: Carbon::parse('2026-01-01'),
    );

    expect(
        fn () => app(DashboardService::class)
            ->summary($data)
    )->toThrow(ValidationException::class);
});

it('calculates the total balance from the users accounts', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Account::factory()->create([
        'user_id' => $user->id,
        'current_balance' => 150000,
    ]);

    Account::factory()->create([
        'user_id' => $user->id,
        'current_balance' => 85000,
    ]);

    Account::factory()->create([
        'user_id' => $otherUser->id,
        'current_balance' => 900000,
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $dashboard = app(DashboardService::class)
        ->summary($data);

    expect($dashboard->totalBalance)
        ->toBe(235000.0);
});

it('only includes accounts belonging to the user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->accounts()->delete();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'My Bank',
        'current_balance' => 100000,
    ]);

    Account::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other Bank',
        'current_balance' => 900000,
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $dashboard = app(DashboardService::class)
        ->summary($data);

    expect($dashboard->accounts)
        ->toHaveCount(1)
        ->and($dashboard->accounts[0])
        ->toBeInstanceOf(AccountSummaryData::class)
        ->and($dashboard->accounts[0]->id)
        ->toBe($account->id)
        ->and($dashboard->totalBalance)
        ->toBe(100000.0);
});

it('returns a zero balance and no accounts when the user has no accounts', function () {
    $user = User::factory()->create();

    $user->accounts()->delete();

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $dashboard = app(DashboardService::class)
        ->summary($data);

    expect($dashboard->accounts)
        ->toBe([])
        ->and($dashboard->totalBalance)
        ->toBe(0.0);
});

it('orders the default account first', function () {
    $user = User::factory()->create();

    $user->accounts()->delete();

    Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Alpha Wallet',
        'is_default' => false,
    ]);

    $defaultAccount = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Zulu Wallet',
        'is_default' => true,
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $dashboard = app(DashboardService::class)
        ->summary($data);

    expect($dashboard->accounts[0]->id)
        ->toBe($defaultAccount->id);
});

it('uses only savings account balances for dashboard savings', function () {
    $user = User::factory()->create();
    $user->accounts()->delete();

    Account::factory()->for($user)->create(['name' => 'Cash', 'type' => AccountType::Cash, 'current_balance' => 100000]);
    Account::factory()->for($user)->create(['name' => 'Bank', 'type' => AccountType::Bank, 'current_balance' => 300000]);
    Account::factory()->for($user)->create(['name' => 'Savings', 'type' => AccountType::Savings, 'current_balance' => 175000]);
    Account::factory()->for($user)->create(['name' => 'My Savings', 'type' => AccountType::Bank, 'current_balance' => 25000]);

    $dashboard = app(DashboardService::class)->summary(new DateRangeData($user->id, now()->startOfMonth(), now()->endOfMonth()));

    expect($dashboard->totalBalance)->toBe(600000.0)
        ->and($dashboard->savingsBalance)->toBe(175000.0);
});

it('returns goal savings separately from account savings', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->savings()->create(['current_balance' => 100000]);
    Goal::factory()->for($user)->create(['name' => 'Laptop', 'target_amount' => 300000, 'current_amount' => 100000]);
    Goal::factory()->for($user)->create(['name' => 'Emergency Fund', 'target_amount' => 200000, 'current_amount' => 50000]);

    $dashboard = app(DashboardService::class)->summary(new DateRangeData($user->id, now()->startOfMonth(), now()->endOfMonth()));

    expect($dashboard->savingsBalance)->toBe(100000.0)
        ->and($dashboard->goalSavings['total'])->toBe(150000.0)
        ->and($dashboard->goalSavings['goals'])->toHaveCount(2);
});
