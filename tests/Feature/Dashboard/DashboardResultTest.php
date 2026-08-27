<?php

use App\Data\Report\DateRangeData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the complete dashboard result', function () {

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    $user = User::factory()->create();

    /*
    |--------------------------------------------------------------------------
    | Accounts
    |--------------------------------------------------------------------------
    */

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'name' => 'Main Account',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    $food = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Food',
    ]);

    $transport = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Transport',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Income
    |--------------------------------------------------------------------------
    */

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => null,
        'type' => TransactionType::Income,
        'status' => TransactionStatus::Completed,
        'amount' => 500000,
        'date' => '2026-01-05',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    */

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'amount' => 200000,
        'date' => '2026-01-10',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $transport->id,
        'type' => TransactionType::Expense,
        'status' => TransactionStatus::Completed,
        'amount' => 120000,
        'date' => '2026-01-15',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Budget
    |--------------------------------------------------------------------------
    */

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'name' => 'Food Budget',
        'amount' => 300000,
        'period' => 'monthly',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'alert_percentage' => 80,
        'is_active' => true,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $transport->id,
        'name' => 'Transport Budget',
        'amount' => 240000,
        'period' => 'monthly',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'alert_percentage' => 80,
        'is_active' => true,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Date Range
    |--------------------------------------------------------------------------
    */

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    /*
    |--------------------------------------------------------------------------
    | Generate Dashboard
    |--------------------------------------------------------------------------
    */

    $result = app(DashboardService::class)
        ->summary($data);

    /*
    |--------------------------------------------------------------------------
    | Inspect complete result
    |--------------------------------------------------------------------------
    */

    dd($result);
});