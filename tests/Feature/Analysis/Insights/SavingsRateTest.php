<?php

use App\Actions\Analysis\Insights\SavingsRate;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function savingsRateDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );
}

it('calculates savings and savings rate', function () {

    $user = User::factory()->create();

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
            'amount' => 300000,
            'date' => '2026-01-20',
        ]);

    $result = app(SavingsRate::class)
        ->handle(savingsRateDateRange($user));

    expect($result['income'])
        ->toBe(500000.0);

    expect($result['expenses'])
        ->toBe(300000.0);

    expect($result['savings'])
        ->toBe(200000.0);

    expect($result['rate'])
        ->toBe(40.0);
});

it('returns zero savings rate when there is no income', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 100000,
            'date' => '2026-01-15',
        ]);

    $result = app(SavingsRate::class)
        ->handle(savingsRateDateRange($user));

    expect($result['income'])
        ->toBe(0.0);

    expect($result['savings'])
        ->toBe(-100000.0);

    expect($result['rate'])
        ->toBe(0.0);
});

it('excludes pending transactions', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Pending,
            'amount' => 500000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Pending,
            'amount' => 200000,
            'date' => '2026-01-15',
        ]);

    $result = app(SavingsRate::class)
        ->handle(savingsRateDateRange($user));

    expect($result['income'])
        ->toBe(0.0);

    expect($result['expenses'])
        ->toBe(0.0);

    expect($result['savings'])
        ->toBe(0.0);

    expect($result['rate'])
        ->toBe(0.0);
});

it('ignores transactions outside the date range', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 500000,
            'date' => '2025-12-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 200000,
            'date' => '2026-02-15',
        ]);

    $result = app(SavingsRate::class)
        ->handle(savingsRateDateRange($user));

    expect($result['income'])
        ->toBe(0.0);

    expect($result['expenses'])
        ->toBe(0.0);

    expect($result['savings'])
        ->toBe(0.0);

    expect($result['rate'])
        ->toBe(0.0);
});