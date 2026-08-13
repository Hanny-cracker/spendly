<?php

use App\Actions\Reports\GenerateCashFlowReport;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a cash flow report', function () {

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 300000,
        'type' => TransactionType::Income,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 100000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 50000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $report = app(GenerateCashFlowReport::class)
        ->handle($data);

    expect($report->income)
        ->toBe(300000.0)
        ->and($report->expenses)
        ->toBe(150000.0)
        ->and($report->netCashFlow)
        ->toBe(150000.0);
});