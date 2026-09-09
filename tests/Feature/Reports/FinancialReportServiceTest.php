<?php

use App\Data\Report\DateRangeData;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Reports\FinancialReportService;

it('builds reconciled completed financial activity', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $accountA = Account::factory()->for($user)->create(['name' => 'MTN MoMo']);
    $accountB = Account::factory()->for($user)->create(['name' => 'Bank']);
    $food = Category::factory()->for($user)->create(['name' => 'Food', 'type' => CategoryType::Expense]);
    $salary = Category::factory()->for($user)->create(['name' => 'Salary', 'type' => CategoryType::Income]);
    Transaction::factory()->for($user)->for($accountA)->for($salary)->create(['amount' => 100000, 'type' => TransactionType::Income, 'status' => TransactionStatus::Completed, 'date' => now()]);
    Transaction::factory()->for($user)->for($accountA)->for($food)->create(['amount' => 40000, 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'date' => now()]);
    Transaction::factory()->for($user)->for($accountB)->for($food)->create(['amount' => 10000, 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'date' => now()]);
    Transaction::factory()->for($user)->for($accountA)->for($salary)->create(['amount' => 999999, 'type' => TransactionType::Income, 'status' => TransactionStatus::Pending, 'date' => now()]);
    Transaction::factory()->for($other)->create(['amount' => 888888, 'type' => TransactionType::Income, 'status' => TransactionStatus::Completed, 'date' => now()]);
    $transfer = Transfer::query()->create([
        'user_id' => $user->id,
        'from_account_id' => $accountA->id,
        'to_account_id' => $accountB->id,
        'amount' => 77777,
        'reference' => 'TRF-REPORT-TEST',
        'date' => now(),
    ]);
    Transaction::factory()->for($user)->for($accountA)->for($food)->create(['transfer_id' => $transfer->id, 'amount' => 77777, 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'date' => now()]);
    Transaction::factory()->for($user)->for($accountB)->for($salary)->create(['transfer_id' => $transfer->id, 'amount' => 77777, 'type' => TransactionType::Income, 'status' => TransactionStatus::Completed, 'date' => now()]);

    $report = app(FinancialReportService::class)->summary(new DateRangeData($user->id, now()->startOfMonth(), now()->endOfMonth()))->toArray();
    $momoActivity = collect($report['account_activity'])->firstWhere('id', $accountA->id);
    expect($report['cash_flow']['total_income'])->toBe(100000.0)->and($report['cash_flow']['total_expenses'])->toBe(50000.0)->and($report['cash_flow']['net_cash_flow'])->toBe(50000.0)->and($report['cash_flow']['savings_rate'])->toBe(0.0)
        ->and($report['expense_categories'][0]['category_name'])->toBe('Food')->and($report['expense_categories'][0]['total'])->toBe(50000.0)->and($report['expense_categories'][0]['transaction_count'])->toBe(2)
        ->and($report['income_categories'][0]['category_name'])->toBe('Salary')->and($momoActivity['net_activity'])->toBe(60000.0)->and($report['transaction_summary']['total_transactions'])->toBe(3);
});

it('returns livewire safe arrays for an empty period', function () {
    $user = User::factory()->create();
    $report = app(FinancialReportService::class)->summary(new DateRangeData($user->id, now()->startOfMonth(), now()->endOfMonth()))->toArray();
    expect($report)->toBeArray()->and($report['transaction_summary']['total_transactions'])->toBe(0)->and($report['cash_flow']['total_income'])->toBe(0.0);
});
