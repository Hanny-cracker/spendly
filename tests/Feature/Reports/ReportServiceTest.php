<?php

use App\Data\Report\DateRangeData;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Reports\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a complete report summary', function () {

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    $food = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Food',
        'type' => CategoryType::Expense,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 50000,
        'type' => TransactionType::Income,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 20000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $summary = app(ReportService::class)
        ->summary($data);

    expect($summary)
        ->toHaveKeys([
            'expense',
            'income',
            'cash_flow',
            'categories',
        ]);

    expect($summary['expense']->total)
        ->toBe(20000.0);

    expect($summary['income']->total)
        ->toBe(50000.0);

    expect($summary['cash_flow']->income)
        ->toBe(50000.0);

    expect($summary['cash_flow']->expenses)
        ->toBe(20000.0);

    expect($summary['cash_flow']->netCashFlow)
        ->toBe(30000.0);

    expect($summary['categories'])
        ->toHaveCount(1);

    expect($summary['categories'][0]->categoryName)
        ->toBe('Food');

    expect($summary['categories'][0]->total)
        ->toBe(20000.0);
});
