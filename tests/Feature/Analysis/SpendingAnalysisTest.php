<?php

use App\Actions\Analysis\AnalyzeSpending;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function spendingDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );
}

function createExpense(
    User $user,
    Account $account,
    Category $category,
    float $amount,
    $date = null,
    array $attributes = []
): Transaction {
    return Transaction::factory()->create(array_merge([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => $amount,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'title' => 'Test Expense',
        'date' => $date ?? now(),
    ], $attributes));
}


it('calculates total spending', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    createExpense($user, $account, $food, 10000);
    createExpense($user, $account, $food, 20000);
    createExpense($user, $account, $food, 30000);

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->totalSpent)
        ->toBe(60000.0);
});


it('calculates transaction count', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    createExpense($user, $account, $food, 10000);
    createExpense($user, $account, $food, 20000);

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->transactionCount)
        ->toBe(2);
});


it('calculates average transaction amount', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    createExpense($user, $account, $food, 10000);
    createExpense($user, $account, $food, 30000);

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->averageTransaction)
        ->toBe(20000.0);
});


it('identifies the top spending category', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

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

    createExpense($user, $account, $food, 50000);
    createExpense($user, $account, $transport, 20000);

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->topCategory)
        ->toBe('Food')
        ->and($analysis->topCategoryAmount)
        ->toBe(50000.0);
});


it('calculates top category percentage', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

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

    createExpense($user, $account, $food, 50000);
    createExpense($user, $account, $transport, 50000);

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->topCategoryPercentage)
        ->toBe(50.0);
});


it('identifies the lowest spending category', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

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

    createExpense($user, $account, $food, 50000);
    createExpense($user, $account, $transport, 10000);

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->lowestCategory)
        ->toBe('Transport');
});


it('excludes pending transactions', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    createExpense(
        $user,
        $account,
        $food,
        10000
    );

    createExpense(
        $user,
        $account,
        $food,
        50000,
        now(),
        [
            'status' => TransactionStatus::Pending,
        ]
    );

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->totalSpent)
        ->toBe(10000.0);
});


it('filters transactions outside the date range', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    createExpense(
        $user,
        $account,
        $food,
        10000,
        now()->startOfMonth()->addDays(5)
    );

    createExpense(
        $user,
        $account,
        $food,
        50000,
        now()->subMonths(2)
    );

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->totalSpent)
        ->toBe(10000.0);
});


it('detects increasing spending trend', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    /*
     * Previous period: 10,000
     */
    createExpense(
        $user,
        $account,
        $food,
        10000,
        now()->subMonth()->startOfMonth()->addDays(2)
    );

    /*
     * Current period: 20,000
     */
    createExpense(
        $user,
        $account,
        $food,
        20000,
        now()->startOfMonth()->addDays(2)
    );

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->trend)
        ->toBe('increasing');
});


it('detects decreasing spending trend', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    /*
     * Previous period: 20,000
     */
    createExpense(
        $user,
        $account,
        $food,
        20000,
        now()->subMonth()->startOfMonth()->addDays(2)
    );

    /*
     * Current period: 10,000
     */
    createExpense(
        $user,
        $account,
        $food,
        10000,
        now()->startOfMonth()->addDays(2)
    );

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->trend)
        ->toBe('decreasing');
});


it('detects stable spending trend', function () {

    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $food = Category::factory()
        ->for($user)
        ->create([
            'name' => 'Food',
            'type' => 'expense',
        ]);

    /*
     * Previous period: 10,000
     */
    createExpense(
        $user,
        $account,
        $food,
        10000,
        now()->subMonth()->startOfMonth()->addDays(2)
    );

    /*
     * Current period: 10,000
     */
    createExpense(
        $user,
        $account,
        $food,
        10000,
        now()->startOfMonth()->addDays(2)
    );

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->trend)
        ->toBe('stable');
});


it('handles a period with no spending', function () {

    $user = User::factory()->create();

    $analysis = app(AnalyzeSpending::class)
        ->handle(spendingDateRange($user));

    expect($analysis->totalSpent)
        ->toBe(0.0)
        ->and($analysis->transactionCount)
        ->toBe(0)
        ->and($analysis->averageTransaction)
        ->toBe(0.0)
        ->and($analysis->topCategory)
        ->toBeNull()
        ->and($analysis->topCategoryAmount)
        ->toBe(0.0)
        ->and($analysis->topCategoryPercentage)
        ->toBe(0.0)
        ->and($analysis->trend)
        ->toBe('stable');
});