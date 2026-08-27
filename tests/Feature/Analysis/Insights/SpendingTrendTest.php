<?php

use App\Actions\Analysis\Insights\SpendingTrend;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function spendingTrendDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-02-01'),
        endDate: Carbon::parse('2026-02-28'),
    );
}

it('detects increasing spending', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 20000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 40000,
            'date' => '2026-02-15',
        ]);

    $result = app(SpendingTrend::class)
        ->handle(spendingTrendDateRange($user));

    expect($result['status'])
        ->toBe('increasing');

    expect($result['percentage_change'])
        ->toBe(100.0);
});

it('detects decreasing spending', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 40000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 20000,
            'date' => '2026-02-15',
        ]);

    $result = app(SpendingTrend::class)
        ->handle(spendingTrendDateRange($user));

    expect($result['status'])
        ->toBe('decreasing');

    expect($result['percentage_change'])
        ->toBe(-50.0);
});

it('detects stable spending', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 20000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 20500,
            'date' => '2026-02-15',
        ]);

    $result = app(SpendingTrend::class)
        ->handle(spendingTrendDateRange($user));

    expect($result['status'])
        ->toBe('stable');
});

it('handles no previous spending', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 30000,
            'date' => '2026-02-15',
        ]);

    $result = app(SpendingTrend::class)
        ->handle(spendingTrendDateRange($user));

    expect($result['current_total'])
        ->toBe(30000.0);

    expect($result['previous_total'])
        ->toBe(0.0);
});