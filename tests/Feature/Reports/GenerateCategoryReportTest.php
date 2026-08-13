<?php

use App\Actions\Reports\GenerateCategoryReport;
use App\Data\Report\DateRangeData;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates an expense report grouped by category', function () {

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    $food = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Food',
        'type' => CategoryType::Expense,
    ]);

    $transport = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Transport',
        'type' => CategoryType::Expense,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 10000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 15000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $transport->id,
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

    $report = app(GenerateCategoryReport::class)
        ->handle($data, TransactionType::Expense);

    expect($report)
        ->toHaveCount(2);

    $foodReport = collect($report)->first(
        fn ($item) => $item->categoryId === $food->id
    );

    expect($foodReport->categoryName)
        ->toBe('Food')
        ->and($foodReport->total)
        ->toBe(25000.0)
        ->and($foodReport->transactionCount)
        ->toBe(2);

    $transportReport = collect($report)->first(
        fn ($item) => $item->categoryId === $transport->id
        
    );
// dd($transportReport);
    expect($transportReport->categoryName)
        ->toBe('Transport')
        ->and($transportReport->total)
        ->toBe(20000.0)
        ->and($transportReport->transactionCount)
        ->toBe(1);
});