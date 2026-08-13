<?php

use App\Actions\Analysis\CategoryAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function categoryAnalysisDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );
}

it('analyzes spending by category', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    $transport = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Transport',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 2000,
            'date' => '2026-01-15',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($transport, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-20',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result)
        ->toHaveCount(2);

    $categories = collect($result)
        ->keyBy('categoryName');

    expect($categories['Food']->total)
        ->toBe(5000.0);

    expect($categories['Food']->transactionCount)
        ->toBe(2);

    expect($categories['Transport']->total)
        ->toBe(5000.0);

    expect($categories['Transport']->transactionCount)
        ->toBe(1);
});

it('calculates category transaction count', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->count(3)
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 1000,
            'date' => '2026-01-10',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result[0]->transactionCount)
        ->toBe(3);
});

it('calculates category percentage', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    $transport = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Transport',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 7500,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($transport, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 2500,
            'date' => '2026-01-15',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result[0]->percentage)
        ->toBe(75.0);

    expect($result[1]->percentage)
        ->toBe(25.0);
});

it('calculates average transaction amount', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-15',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result[0]->averageTransaction)
        ->toBe(4000.0);
});

it('orders categories by highest spending', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    $transport = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Transport',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 10000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($transport, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 3000,
            'date' => '2026-01-15',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result[0]->categoryName)
        ->toBe('Food')
        ->and($result[1]->categoryName)
        ->toBe('Transport');
});

it('excludes income transactions', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 50000,
            'date' => '2026-01-10',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result)
        ->toBeEmpty();
});

it('excludes pending transactions', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Pending,
            'amount' => 50000,
            'date' => '2026-01-10',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result)
        ->toBeEmpty();
});

it('filters transactions outside the date range', function () {

    $user = User::factory()->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

    Transaction::factory()
        ->for($user)
        ->for($food, 'category')
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 20000,
            'date' => '2026-02-10',
        ]);

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result)
        ->toHaveCount(1);

    expect($result[0]->total)
        ->toBe(5000.0);
});

it('handles a period with no spending', function () {

    $user = User::factory()->create();

    $result = app(CategoryAnalysis::class)
        ->handle(categoryAnalysisDateRange($user));

    expect($result)
        ->toBeEmpty();
});
