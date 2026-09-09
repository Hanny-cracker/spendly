<?php

use App\Actions\Analysis\SavingsAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\AccountType;
use App\Enums\TransactionStatus;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;

function savingsRange(User $user): DateRangeData
{
    return new DateRangeData($user->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30'));
}

it('separates current savings balance from selected-period savings activity', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->savings()->create(['opening_balance' => 1000000, 'current_balance' => 1100000]);
    Account::factory()->for($user)->create(['name' => 'My Savings', 'type' => AccountType::Bank, 'current_balance' => 900000]);

    $result = app(SavingsAnalysis::class)->handle(savingsRange($user));

    expect($result->currentSavingsBalance)->toBe(1100000.0)
        ->and($result->netSavingsAccountActivity)->toBe(0.0)
        ->and($result->savingsRate)->toBe(0.0);
});

it('sums multiple savings account balances including negative balances', function () {
    $user = User::factory()->create();
    Account::factory()->for($user)->savings()->create(['current_balance' => 100000]);
    Account::factory()->for($user)->savings()->create(['current_balance' => 250000]);
    Account::factory()->for($user)->savings()->create(['current_balance' => -50000]);

    expect(app(SavingsAnalysis::class)->handle(savingsRange($user))->currentSavingsBalance)->toBe(300000.0);
});

it('calculates rate from direct savings income and boundary-crossing net transfers', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    $savings = Account::factory()->for($user)->savings()->create(['opening_balance' => 1000000, 'current_balance' => 1000000]);
    Transaction::factory()->for($user)->for($cash)->income()->create(['amount' => 400000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-10']);
    Transaction::factory()->for($user)->for($savings)->income()->create(['amount' => 100000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-11']);
    Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $cash->id, 'to_account_id' => $savings->id, 'amount' => 50000, 'reference' => 'SAVE-IN', 'date' => '2026-09-12']);
    Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $savings->id, 'to_account_id' => $cash->id, 'amount' => 50000, 'reference' => 'SAVE-OUT', 'date' => '2026-09-13']);

    $result = app(SavingsAnalysis::class)->handle(savingsRange($user));

    expect($result->completedIncome)->toBe(500000.0)
        ->and($result->periodSavingsAccountDeposits)->toBe(150000.0)
        ->and($result->periodSavingsAccountWithdrawals)->toBe(50000.0)
        ->and($result->netSavingsAccountActivity)->toBe(100000.0)
        ->and($result->qualifyingPeriodSavings)->toBe(100000.0)
        ->and($result->savingsRate)->toBe(20.0);
});

it('reports goal earmarks separately without double-counting them as monetary saving', function () {
    $user = User::factory()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 200000]);
    Transaction::factory()->for($user)->for($savings)->income()->create(['amount' => 100000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-10']);
    GoalContribution::factory()->for($goal)->for($user)->create(['account_id' => $savings->id, 'amount' => 100000, 'contributed_at' => '2026-09-10']);

    $result = app(SavingsAnalysis::class)->handle(savingsRange($user));

    expect($result->periodGoalContributions)->toBe(100000.0)
        ->and($result->goalContributionsFromSavingsAccounts)->toBe(100000.0)
        ->and($result->qualifyingAdditionalGoalSaving)->toBe(0.0)
        ->and($result->qualifyingPeriodSavings)->toBe(100000.0)
        ->and($result->savingsRate)->toBe(100.0);
});

it('returns zero rate with no income and does not cap rates above one hundred', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $cash->id, 'to_account_id' => $savings->id, 'amount' => 100000, 'reference' => 'NO-INCOME', 'date' => '2026-09-10']);
    expect(app(SavingsAnalysis::class)->handle(savingsRange($user))->savingsRate)->toBe(0.0);

    Transaction::factory()->for($user)->for($cash)->income()->create(['amount' => 50000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-11']);
    expect(app(SavingsAnalysis::class)->handle(savingsRange($user))->savingsRate)->toBe(200.0);
});

it('counts goal saving from non-savings accounts without double-counting savings-linked goals', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    Transaction::factory()->for($user)->for($cash)->income()->create(['amount' => 400000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-10']);
    Transaction::factory()->for($user)->for($savings)->income()->create(['amount' => 100000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-10']);
    $savingsGoal = Goal::factory()->for($user)->create(['target_amount' => 100000]);
    $cashGoal = Goal::factory()->for($user)->create(['target_amount' => 100000]);
    GoalContribution::factory()->for($savingsGoal)->for($user)->create(['account_id' => $savings->id, 'amount' => 30000, 'contributed_at' => '2026-09-12']);
    GoalContribution::factory()->for($cashGoal)->for($user)->create(['account_id' => $cash->id, 'amount' => 20000, 'contributed_at' => '2026-09-12']);

    $result = app(SavingsAnalysis::class)->handle(savingsRange($user));

    expect($result->periodGoalContributions)->toBe(50000.0)
        ->and($result->goalContributionsFromSavingsAccounts)->toBe(30000.0)
        ->and($result->qualifyingAdditionalGoalSaving)->toBe(20000.0)
        ->and($result->qualifyingPeriodSavings)->toBe(120000.0)
        ->and($result->savingsRate)->toBe(24.0);
});

it('counts a goal contribution without a savings account as additional saving', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    Transaction::factory()->for($user)->for($cash)->income()->create(['amount' => 500000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-10']);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 100000]);
    GoalContribution::factory()->for($goal)->for($user)->create(['account_id' => $cash->id, 'amount' => 50000, 'contributed_at' => '2026-09-12']);

    $result = app(SavingsAnalysis::class)->handle(savingsRange($user));

    expect($result->netSavingsAccountActivity)->toBe(0.0)
        ->and($result->qualifyingAdditionalGoalSaving)->toBe(50000.0)
        ->and($result->qualifyingPeriodSavings)->toBe(50000.0)
        ->and($result->savingsRate)->toBe(10.0);
});

it('preserves negative net savings activity and returns zero rate without income', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $cash->id, 'to_account_id' => $savings->id, 'amount' => 20000, 'reference' => 'SAVE-IN', 'date' => '2026-09-10']);
    Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $savings->id, 'to_account_id' => $cash->id, 'amount' => 50000, 'reference' => 'SAVE-OUT', 'date' => '2026-09-11']);

    $result = app(SavingsAnalysis::class)->handle(savingsRange($user));

    expect($result->netSavingsAccountActivity)->toBe(-30000.0)
        ->and($result->qualifyingPeriodSavings)->toBe(-30000.0)
        ->and($result->savingsRate)->toBe(0.0);
});
