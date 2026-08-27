<?php

use App\Data\Report\DateRangeData;
use App\Data\Analysis\BudgetProgressData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Budget;
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
                fn (BudgetProgressData $budget) =>
                    $budget instanceof BudgetProgressData
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

    Transaction::factory()
        ->count(3)
        ->create([
            'user_id' => $user->id,
        ]);

    Transaction::factory()
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
        ->toHaveCount(3);

    expect(
        collect($result->recentTransactions)
            ->every(
                fn ($transaction) =>
                    $transaction->user_id === $user->id
            )
    )->toBeTrue();
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