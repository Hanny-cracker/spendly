<?php

use App\Actions\Reports\GenerateIncomeReport;
use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates an income report', function () {

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 100000,
        'type' => TransactionType::Income,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'amount' => 50000,
        'type' => TransactionType::Income,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    $data = new DateRangeData(
        userId: $user->id,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $report = app(GenerateIncomeReport::class)
        ->handle($data);

    expect($report->total)
        ->toBe(150000.0)
        ->and($report->transactionCount)
        ->toBe(2)
        ->and($report->average)
        ->toBe(75000.0);
});