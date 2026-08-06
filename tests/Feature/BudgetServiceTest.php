<?php

use App\Enums\BudgetPeriod;
use App\Enums\BudgetStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates total spent for a budget', function () {

    $user = User::factory()->create();

    $account = Account::factory()
        ->for($user)
        ->create();

    $category = Category::factory()
        ->for($user)
        ->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 200,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => now(),
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 300,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => now(),
        ]);

    expect(
        app(BudgetService::class)->spent($budget)
    )->toBe(500.0);

});

it('calculates remaining budget', function () {

    $user = User::factory()->create();

    $account = Account::factory()->for($user)->create();

    $category = Category::factory()->for($user)->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 400,
            'type' => TransactionType::Expense,
        ]);

    expect(
        app(BudgetService::class)
            ->remaining($budget)
    )->toBe(600.0);

});

it('calculates percentage used', function () {

    $user = User::factory()->create();

    $account = Account::factory()->for($user)->create();

    $category = Category::factory()->for($user)->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 250,
            'type' => TransactionType::Expense,
        ]);

    expect(
        app(BudgetService::class)
            ->percentageUsed($budget)
    )->toBe(25.0);

});

it('returns safe status', function () {

    $user = User::factory()->create();

    $account = Account::factory()->for($user)->create();

    $category = Category::factory()->for($user)->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
            'alert_percentage' => 80,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 300,
            'type' => TransactionType::Expense,
        ]);

    expect(
        app(BudgetService::class)
            ->status($budget)
    )->toBe(BudgetStatus::Safe);

});

it('returns warning status', function () {

    $user = User::factory()->create();

    $account = Account::factory()->for($user)->create();

    $category = Category::factory()->for($user)->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
            'alert_percentage' => 80,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 850,
            'type' => TransactionType::Expense,
        ]);

    expect(
        app(BudgetService::class)
            ->status($budget)
    )->toBe(BudgetStatus::Warning);

});

it('returns exceeded status', function () {

    $user = User::factory()->create();

    $account = Account::factory()->for($user)->create();

    $category = Category::factory()->for($user)->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 1500,
            'type' => TransactionType::Expense,
        ]);

    expect(
        app(BudgetService::class)
            ->status($budget)
    )->toBe(BudgetStatus::Exceeded);

});

it('knows when a budget should notify', function () {

    $user = User::factory()->create();

    $account = Account::factory()->for($user)->create();

    $category = Category::factory()->for($user)->create();

    $budget = Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'amount' => 1000,
            'alert_percentage' => 80,
        ]);

    Transaction::factory()
        ->for($user)
        ->for($account)
        ->for($category)
        ->create([
            'amount' => 900,
            'type' => TransactionType::Expense,
        ]);

    expect(
        app(BudgetService::class)
            ->shouldNotify($budget)
    )->toBeTrue();

});
it('returns a complete budget summary', function () {

    $budget = Budget::factory()->create();

    $summary = app(BudgetService::class)
        ->summary($budget);

    expect($summary)

        ->toHaveKeys([
            'budget',
            'spent',
            'remaining',
            'percentage',
            'status',
            'notify',
        ]);

});