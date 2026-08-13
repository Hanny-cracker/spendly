<?php

use App\Actions\Analysis\IncomeAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function incomeDateRange(User $user): DateRangeData
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
            'amount' => 1000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 2000,
            'date' => '2026-01-15',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->totalIncome)
        ->toBe(3000.0);
});

it('calculates income transaction count', function () {

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

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->transactionCount)
        ->toBe(3);
});

it('calculates average income', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
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

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->averageIncome)
        ->toBe(2000.0);
});

it('identifies largest and smallest income', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 500,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-15',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->largestIncome)
        ->toBe(5000.0)
        ->and($result->smallestIncome)
        ->toBe(500.0);
});

it('identifies the top income category', function () {

    $user = User::factory()->create();

    $salary = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Salary',
            'type' => 'income',
        ]);

    $business = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Business',
            'type' => 'income',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($salary, 'category')
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($salary, 'category')
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($business, 'category')
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-20',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->topCategoryId)
        ->toBe($salary->id)
        ->and($result->topCategoryName)
        ->toBe('Salary');
});

it('calculates top income category percentage', function () {

    $user = User::factory()->create();

    $salary = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Salary',
            'type' => 'income',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($salary, 'category')
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 7500,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 2500,
            'date' => '2026-01-15',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->topCategoryPercentage)
        ->toBe(75.0);
});

it('excludes pending income transactions', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Pending,
            'amount' => 5000,
            'date' => '2026-01-15',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->totalIncome)
        ->toBe(1000.0)
        ->and($result->transactionCount)
        ->toBe(1);
});

it('filters income outside the date range', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-02-10',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->totalIncome)
        ->toBe(1000.0);
});

it('detects increasing income trend', function () {

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
            'amount' => 3000,
            'date' => '2026-01-25',
        ]);

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->trend)
        ->toBe('increasing');
});

it('detects decreasing income trend', function () {

    $user = User::factory()->create();

    Transaction::factory()
        ->for($user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
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

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->trend)
        ->toBe('decreasing');
});

it('handles a period with no income', function () {

    $user = User::factory()->create();

    $result = app(IncomeAnalysis::class)
        ->handle(incomeDateRange($user));

    expect($result->totalIncome)
        ->toBe(0.0)
        ->and($result->transactionCount)
        ->toBe(0)
        ->and($result->averageIncome)
        ->toBe(0.0)
        ->and($result->largestIncome)
        ->toBe(0.0)
        ->and($result->smallestIncome)
        ->toBe(0.0)
        ->and($result->topCategoryId)
        ->toBeNull()
        ->and($result->trend)
        ->toBe('stable');
});