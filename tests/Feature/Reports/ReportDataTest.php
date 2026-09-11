<?php

// use App\Data\Report\CashFlowReportData;
// use App\Data\Report\CategoryReportData;
// use App\Data\Report\DateRangeData;
// use App\Data\Report\ExpenseReportData;
// use Illuminate\Validation\ValidationException;

// it('creates a valid date range', function () {

//     $data = new DateRangeData(
//         userId: 1,
//         startDate: now()->startOfMonth(),
//         endDate: now()->endOfMonth(),
//     );

//     $data->validate();

//     expect($data->userId)
//         ->toBe(1);
// });

// it('rejects an invalid date range', function () {

//     $data = new DateRangeData(
//         userId: 1,
//         startDate: now()->endOfMonth(),
//         endDate: now()->startOfMonth(),
//     );

//     $data->validate();

// })->throws(ValidationException::class);

// it('creates an expense report', function () {

//     $report = new ExpenseReportData(
//         total: 100000,
//         transactionCount: 10,
//         average: 10000,
//     );

//     expect($report->toArray())
//         ->toBe([
//             'total' => 100000,
//             'transaction_count' => 10,
//             'average' => 10000,
//         ]);
// });

// it('creates a cash flow report', function () {

//     $report = new CashFlowReportData(
//         income: 500000,
//         expenses: 300000,
//         netCashFlow: 200000,
//     );

//     expect($report->netCashFlow)
//         ->toBe(200000.0);
// });

// it('creates a category report', function () {

//     $report = new CategoryReportData(
//         categoryId: 1,
//         categoryName: 'Food',
//         total: 75000,
//         transactionCount: 15,
//     );

//     expect($report->categoryName)
//         ->toBe('Food')
//         ->and($report->total)
//         ->toBe(75000.0);
// });

use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Reports\TransactionQueries;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->queries = app(TransactionQueries::class);

    $this->startDate = Carbon::parse('2026-08-01');
    $this->endDate = Carbon::parse('2026-08-31');

    $this->dateRange = new DateRangeData(
        userId: $this->user->id,
        startDate: $this->startDate,
        endDate: $this->endDate,
    );
});

it('returns expense transactions', function () {

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 500,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 1000,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->expenses($this->dateRange);

    expect($transactions)
        ->toHaveCount(1)
        ->and($transactions->first()->amount)
        ->toBe(500.0);
});

it('returns income transactions', function () {

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 500,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 1000,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->income($this->dateRange);

    expect($transactions)
        ->toHaveCount(1)
        ->and($transactions->first()->amount)
        ->toBe(1000.0);
});

it('can calculate total expenses from returned transactions', function () {

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 500,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 1500,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-15'),
        ]);

    $transactions = $this->queries->expenses($this->dateRange);

    expect($transactions->sum('amount'))
        ->toBe(2000.0);
});

it('can calculate total income from returned transactions', function () {

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 2000,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 3000,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-15'),
        ]);

    $transactions = $this->queries->income($this->dateRange);

    expect($transactions->sum('amount'))
        ->toBe(5000.0);
});

it('returns the correct expense count', function () {

    Transaction::factory()
        ->count(3)
        ->for($this->user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->expenses($this->dateRange);

    expect($transactions)->toHaveCount(3);
});

it('returns the correct income count', function () {

    Transaction::factory()
        ->count(2)
        ->for($this->user)
        ->create([
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->income($this->dateRange);

    expect($transactions)->toHaveCount(2);
});

it('returns transactions with categories', function () {

    $category = Category::factory()
        ->for($this->user)
        ->create();

    Transaction::factory()
        ->for($this->user)
        ->for($category)
        ->create([
            'amount' => 500,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->withCategories(
        $this->dateRange,
        TransactionType::Expense
    );

    expect($transactions)
        ->toHaveCount(1)
        ->and($transactions->first()->relationLoaded('category'))
        ->toBeTrue()
        ->and($transactions->first()->category->id)
        ->toBe($category->id);
});

it('filters transactions by date range', function () {

    Transaction::factory()
        ->for($this->user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    Transaction::factory()
        ->for($this->user)
        ->create([
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-09-10'),
        ]);

    $transactions = $this->queries->expenses($this->dateRange);

    expect($transactions)->toHaveCount(1);
});

it('excludes pending transactions', function () {

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 500,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    Transaction::factory()
        ->for($this->user)
        ->create([
            'amount' => 1000,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Pending,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->expenses($this->dateRange);

    expect($transactions)
        ->toHaveCount(1)
        ->and($transactions->first()->amount)
        ->toBe(500.0);
});

it('does not return transactions belonging to another user', function () {

    $otherUser = User::factory()->create();

    Transaction::factory()
        ->for($otherUser)
        ->create([
            'amount' => 1000,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'date' => Carbon::parse('2026-08-10'),
        ]);

    $transactions = $this->queries->expenses($this->dateRange);

    expect($transactions)->toBeEmpty();
});
