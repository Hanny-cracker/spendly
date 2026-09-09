<?php

use App\Actions\Analysis\Insights\FinancialHealth;
use App\Data\Analysis\CashFlowAnalysisData;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function financialHealthDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );
}

function recordFinancialHealthSaving(User $user, float $amount): void
{
    $cash = Account::factory()->for($user)->cash()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    Transfer::query()->create([
        'user_id' => $user->id,
        'from_account_id' => $cash->id,
        'to_account_id' => $savings->id,
        'amount' => $amount,
        'reference' => fake()->uuid(),
        'date' => '2026-01-20',
    ]);
}

it('returns excellent financial health for strong finances', function () {

    $user = User::factory()->create();
    recordFinancialHealthSaving($user, 300000);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 500000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 200000,
            'date' => '2026-01-20',
        ]);

    /*
     * Previous period has higher spending so
     * current spending is decreasing.
     */
    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 400000,
            'date' => '2025-12-15',
        ]);

    $cashFlow = new CashFlowAnalysisData(
        totalIncome: 500000,
        totalExpenses: 200000,
        netCashFlow: 300000,
        incomeTransactionCount: 1,
        expenseTransactionCount: 1,
        savingsRate: 60.0,
        status: 'positive',
        trend: 'increasing',
    );

    $concentration = [
        'concentration_level' => 'low',
    ];

    $result = app(FinancialHealth::class)
        ->handle(
            financialHealthDateRange($user),
            $cashFlow,
            $concentration
        );

    expect($result['score'])
        ->toBeGreaterThanOrEqual(80);

    expect($result['status'])
        ->toBe('excellent');

    expect($result['savings_rate'])
        ->toBe(60.0);

    expect($result['cash_flow_status'])
        ->toBe('positive');

    expect($result['spending_trend'])
        ->toBe('decreasing');
});

it('returns poor financial health when spending exceeds income', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 200000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 400000,
            'date' => '2026-01-20',
        ]);

    $cashFlow = new CashFlowAnalysisData(
        totalIncome: 200000,
        totalExpenses: 400000,
        netCashFlow: -200000,
        incomeTransactionCount: 1,
        expenseTransactionCount: 1,
        savingsRate: -100.0,
        status: 'negative',
        trend: 'increasing',
    );
    $concentration = [
        'concentration_level' => 'high',
    ];

    $result = app(FinancialHealth::class)
        ->handle(
            financialHealthDateRange($user),
            $cashFlow,
            $concentration
        );

    expect($result['score'])
        ->toBeLessThan(50.0);

    expect($result['status'])
        ->toBe('poor');

    expect($result['cash_flow_status'])
        ->toBe('negative');
});

it('returns fair health for moderate finances', function () {

    $user = User::factory()->create();
    recordFinancialHealthSaving($user, 100000);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 500000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 450000,
            'date' => '2026-01-20',
        ]);

    $cashFlow = new CashFlowAnalysisData(
        totalIncome: 500000,
        totalExpenses: 200000,
        netCashFlow: 300000,
        incomeTransactionCount: 1,
        expenseTransactionCount: 1,
        savingsRate: 60.0,
        status: 'positive',
        trend: 'increasing',
    );

    $concentration = [
        'concentration_level' => 'medium',
    ];

    $result = app(FinancialHealth::class)
        ->handle(
            financialHealthDateRange($user),
            $cashFlow,
            $concentration
        );

    expect($result['score'])
        ->toBeGreaterThanOrEqual(65);

    expect($result['status'])
        ->toBe('good');
});

it('handles zero income safely', function () {

    $user = User::factory()->create();

    $cashFlow = new CashFlowAnalysisData(
        totalIncome: 0,
        totalExpenses: 0,
        netCashFlow: 0,
        incomeTransactionCount: 0,
        expenseTransactionCount: 0,
        savingsRate: 0.0,
        status: 'neutral',
        trend: 'stable',
    );

    $result = app(FinancialHealth::class)
        ->handle(
            financialHealthDateRange($user),
            $cashFlow,
            [
                'concentration_level' => 'low',
            ]
        );

    expect($result['savings_rate'])
        ->toBe(0.0);

    expect($result['cash_flow'])
        ->toBe(0.0);

    expect($result['status'])
        ->toBe('poor');
});
