<?php

use App\Actions\Analysis\BudgetProgressAnalysis;
use App\Data\Report\DateRangeData;
use App\Enums\BudgetPeriod;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

it('calculates budget progress', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Food',
    ]);

    $budget = Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'name' => 'Food Budget',
        'amount' => 100000,
        'alert_percentage' => 70,
        'period' => BudgetPeriod::Monthly,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'is_active' => true,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 30000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-10',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 40000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-15',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(BudgetProgressAnalysis::class)
        ->handle($data);

    expect($result)
        ->toHaveCount(1);

    expect($result[0]->budgetId)
        ->toBe($budget->id);

    expect($result[0]->budgetName)
        ->toBe('Food Budget');

    expect($result[0]->budgetAmount)
        ->toBe(100000.0);

    expect($result[0]->spentAmount)
        ->toBe(70000.0);

    expect($result[0]->remainingAmount)
        ->toBe(30000.0);

    expect($result[0]->percentageUsed)
        ->toBe(70.0);

    expect($result[0]->status)
        ->toBe('warning');
});

it('excludes pending transactions', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 100000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'is_active' => true,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 30000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-10',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 50000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Pending,
        'date' => '2026-01-10',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(BudgetProgressAnalysis::class)
        ->handle($data);

    expect($result[0]->spentAmount)
        ->toBe(30000.0);
});

it('excludes income transactions', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 100000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'is_active' => true,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 30000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-10',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 200000,
        'type' => TransactionType::Income,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-10',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(BudgetProgressAnalysis::class)
        ->handle($data);

    expect($result[0]->spentAmount)
        ->toBe(30000.0);
});

it('detects an over budget category', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 100000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'is_active' => true,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 120000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-10',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(BudgetProgressAnalysis::class)
        ->handle($data);

    expect($result[0]->percentageUsed)
        ->toBe(120.0);

    expect($result[0]->remainingAmount)
        ->toBe(-20000.0);

    expect($result[0]->status)
        ->toBe('over_budget');
});

it('handles a zero budget safely', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 0,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'is_active' => true,
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(BudgetProgressAnalysis::class)
        ->handle($data);

    expect($result[0]->percentageUsed)
        ->toBe(0.0);

    expect($result[0]->status)
        ->toBe('on_track');
});

it('excludes transactions outside the date range', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 100000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'is_active' => true,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 30000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 50000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => '2026-02-15',
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(BudgetProgressAnalysis::class)
        ->handle($data);

    expect($result[0]->spentAmount)
        ->toBe(30000.0);
});
