<?php

use App\Actions\Analysis\CashFlowAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function cashFlowDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );
}

it('calculates total income', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->totalIncome)
        ->toBe(8000.0);
});

it('calculates total expenses', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 2000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->totalExpenses)
        ->toBe(3000.0);
});

it('calculates net cash flow', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 10000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 4000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->netCashFlow)
        ->toBe(6000.0);
});

it('calculates income and expense transaction counts', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->count(3)
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->count(2)
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 500,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->incomeTransactionCount)
        ->toBe(3)
        ->and($result->expenseTransactionCount)
        ->toBe(2);
});

it('calculates savings rate', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 10000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->savingsRate)
        ->toBe(70.0);
});

it('returns positive status when cash flow is positive', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 2000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->status)
        ->toBe('positive');
});

it('returns negative status when cash flow is negative', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 2000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->status)
        ->toBe('negative');
});

it('returns neutral status when cash flow is zero', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->status)
        ->toBe('neutral');
});

it('excludes pending transactions', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Pending,
            'amount' => 10000,
            'date' => '2026-01-15',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->totalIncome)
        ->toBe(5000.0)
        ->and($result->totalExpenses)
        ->toBe(0.0)
        ->and($result->netCashFlow)
        ->toBe(5000.0);
});

it('filters transactions outside the date range', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 20000,
            'date' => '2026-02-10',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->totalIncome)
        ->toBe(5000.0)
        ->and($result->totalExpenses)
        ->toBe(0.0);
});

it('detects increasing cash flow trend', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-05',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-25',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->trend)
        ->toBe('increasing');
});

it('detects decreasing cash flow trend', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-05',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-25',
        ]);

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->trend)
        ->toBe('decreasing');
});

it('handles a period with no transactions', function () {

    $user = User::factory()->create();

    $result = app(CashFlowAnalysis::class)
        ->handle(cashFlowDateRange($user));

    expect($result->totalIncome)
        ->toBe(0.0)
        ->and($result->totalExpenses)
        ->toBe(0.0)
        ->and($result->netCashFlow)
        ->toBe(0.0)
        ->and($result->incomeTransactionCount)
        ->toBe(0)
        ->and($result->expenseTransactionCount)
        ->toBe(0)
        ->and($result->savingsRate)
        ->toBe(0.0)
        ->and($result->status)
        ->toBe('neutral')
        ->and($result->trend)
        ->toBe('stable');
});