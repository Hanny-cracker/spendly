<?php

use App\Actions\Reports\GenerateExpenseReport;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates an expense report', function () {

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 1000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 2000,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $report = app(GenerateExpenseReport::class)
        ->handle($data);

    expect($report->total)
        ->toBe(3000.0)
        ->and($report->transactionCount)
        ->toBe(2)
        ->and($report->average)
        ->toBe(1500.0);
});
