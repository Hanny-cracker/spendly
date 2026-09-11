<?php

use App\Actions\Budgets\CreateBudget;
use App\Actions\Goals\CreateGoal;
use App\Actions\Goals\CreateGoalContribution;
use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transactions\DeleteTransaction;
use App\Actions\Transactions\UpdateTransaction;
use App\Actions\Transfers\CreateTransfer;
use App\Data\Budget\CreateBudgetData;
use App\Data\Goal\CreateGoalContributionData;
use App\Data\Goal\CreateGoalData;
use App\Data\Report\DateRangeData;
use App\Data\Transaction\CreateTransactionData;
use App\Data\Transaction\UpdateTransactionData;
use App\Data\Transfer\CreateTransferData;
use App\Enums\BudgetPeriod;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\BudgetExceededException;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Analytics\AnalyticsService;
use App\Services\Budgets\BudgetSpendingService;
use App\Services\Dashboard\DashboardService;
use App\Services\RecurringTransactions\RecurringTransactionService;
use App\Services\Reports\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

function integrationTransaction(User $user, Account $account, Category $category, float $amount, TransactionType $type, TransactionStatus $status = TransactionStatus::Completed, ?string $title = null): Transaction
{
    return app(CreateTransaction::class)->handle(new CreateTransactionData(
        userId: $user->id,
        accountId: $account->id,
        categoryId: $category->id,
        title: $title ?? $category->name,
        description: null,
        amount: $amount,
        type: $type,
        status: $status,
        date: Carbon::today(),
    ));
}

it('reconciles balances transfers goals budgets savings dashboard analytics and reports', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create(['opening_balance' => 100000, 'current_balance' => 100000]);
    $bank = Account::factory()->for($user)->bank()->create(['opening_balance' => 500000, 'current_balance' => 500000]);
    $savings = Account::factory()->for($user)->savings()->create(['opening_balance' => 200000, 'current_balance' => 200000]);
    $salary = Category::factory()->for($user)->income()->create(['name' => 'Salary']);
    $food = Category::factory()->for($user)->expense()->create(['name' => 'Food']);
    $otherAccount = Account::factory()->for($other)->create(['current_balance' => 900000]);
    $otherCategory = Category::factory()->for($other)->expense()->create();
    integrationTransaction($other, $otherAccount, $otherCategory, 800000, TransactionType::Expense);

    integrationTransaction($user, $bank, $salary, 300000, TransactionType::Income, title: 'Salary payment');
    integrationTransaction($user, $cash, $food, 50000, TransactionType::Expense, title: 'Groceries');
    app(CreateTransfer::class)->handle(new CreateTransferData($user->id, $bank->id, $savings->id, 100000, null, Carbon::today()));

    $goal = app(CreateGoal::class)->handle(new CreateGoalData($user->id, 'Emergency Fund', 500000));
    app(CreateGoalContribution::class)->handle($goal, new CreateGoalContributionData($user->id, 50000, today(), $bank->id));
    app(CreateGoalContribution::class)->handle($goal, new CreateGoalContributionData($user->id, 20000, today(), $savings->id));

    $budget = app(CreateBudget::class)->handle(new CreateBudgetData($user->id, $food->id, 'Food Budget', 100000, BudgetPeriod::Monthly, today()->startOfMonth(), today()->endOfMonth()));
    $range = new DateRangeData($user->id, today()->startOfMonth(), today()->endOfMonth());
    $dashboard = app(DashboardService::class)->summary($range)->toArray();
    $analytics = app(AnalyticsService::class)->summary($range)->toArray();
    $report = app(FinancialReportService::class)->summary($range)->toArray();
    $availability = app(BudgetSpendingService::class)->availability($budget);

    expect($cash->refresh()->current_balance)->toBe(50000.0)
        ->and($bank->refresh()->current_balance)->toBe(700000.0)
        ->and($savings->refresh()->current_balance)->toBe(300000.0)
        ->and($dashboard['total_balance'])->toBe(1050000.0)
        ->and($dashboard['savings_balance'])->toBe(300000.0)
        ->and($dashboard['goal_savings']['total'])->toBe(70000.0)
        ->and($dashboard['reports']['income']['total'])->toBe(300000.0)
        ->and($dashboard['reports']['expense']['total'])->toBe(50000.0)
        ->and($dashboard['reports']['cash_flow']['net_cash_flow'])->toBe(250000.0)
        ->and($analytics['reports']['cash_flow'])->toBe($dashboard['reports']['cash_flow'])
        ->and($analytics['insights']['spending']['total_spent'])->toBe(50000.0)
        ->and($analytics['insights']['income']['total_income'])->toBe(300000.0)
        ->and($analytics['insights']['spending_trend']['current_total'])->toBe(50000.0)
        ->and($report['cash_flow']['total_income'])->toBe(300000.0)
        ->and($report['cash_flow']['total_expenses'])->toBe(50000.0)
        ->and($report['cash_flow']['net_cash_flow'])->toBe(250000.0)
        ->and($report['cash_flow']['savings_rate'])->toBe($analytics['insights']['savings_rate']['savings_rate'])
        ->and($analytics['insights']['savings_rate']['qualifying_period_savings'])->toBe(150000.0)
        ->and($goal->refresh()->current_amount)->toBe(70000.0)
        ->and($availability->spent)->toBe(50000.0)
        ->and($availability->remaining)->toBe(50000.0);

    expect(fn () => integrationTransaction($user, $cash, $food, 60000, TransactionType::Expense))->toThrow(BudgetExceededException::class);
    expect($cash->refresh()->current_balance)->toBe(50000.0)
        ->and(app(BudgetSpendingService::class)->availability($budget)->spent)->toBe(50000.0);

    integrationTransaction($user, $cash, $food, 50000, TransactionType::Expense);
    expect($cash->refresh()->current_balance)->toBe(0.0)
        ->and(app(BudgetSpendingService::class)->availability($budget)->remaining)->toBe(0.0)
        ->and(app(DashboardService::class)->summary($range)->toArray()['reports']['expense']['total'])->toBe(100000.0);
});

it('applies pending update and deletion effects exactly once across every summary', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->expense()->create();
    $pending = integrationTransaction($user, $account, $category, 30000, TransactionType::Expense, TransactionStatus::Pending);
    $range = new DateRangeData($user->id, today()->startOfMonth(), today()->endOfMonth());

    expect($account->refresh()->current_balance)->toBe(100000.0)
        ->and(app(DashboardService::class)->summary($range)->toArray()['reports']['expense']['total'])->toBe(0.0)
        ->and(app(FinancialReportService::class)->summary($range)->toArray()['cash_flow']['total_expenses'])->toBe(0.0);

    $completed = app(UpdateTransaction::class)->handle($pending, new UpdateTransactionData($account->id, $category->id, 'Completed expense', null, null, null, 30000, TransactionType::Expense, Carbon::today(), TransactionStatus::Completed));
    expect($account->refresh()->current_balance)->toBe(70000.0);

    $updated = app(UpdateTransaction::class)->handle($completed, new UpdateTransactionData($account->id, $category->id, 'Adjusted expense', null, null, null, 20000, TransactionType::Expense, Carbon::today(), TransactionStatus::Completed));
    expect($account->refresh()->current_balance)->toBe(80000.0)
        ->and(app(AnalyticsService::class)->summary($range)->toArray()['reports']['expense']['total'])->toBe(20000.0);

    app(DeleteTransaction::class)->handle($updated);
    expect($account->refresh()->current_balance)->toBe(100000.0)
        ->and(app(DashboardService::class)->summary($range)->toArray()['reports']['expense']['total'])->toBe(0.0)
        ->and(app(FinancialReportService::class)->summary($range)->toArray()['cash_flow']['total_expenses'])->toBe(0.0);
});

it('treats generated recurring transactions as financial records and future or failed schedules as nonfinancial', function () {
    Notification::fake();
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $utilities = Category::factory()->for($user)->expense()->create(['name' => 'Utilities']);
    $food = Category::factory()->for($user)->expense()->create(['name' => 'Food']);
    app(CreateBudget::class)->handle(new CreateBudgetData($user->id, $food->id, 'Food Budget', 10000, BudgetPeriod::Monthly, today()->startOfMonth(), today()->endOfMonth()));

    $due = RecurringTransaction::factory()->for($user)->for($account)->for($utilities)->create(['title' => 'Internet', 'amount' => 25000, 'next_run' => now()->subMinute(), 'frequency' => RecurringFrequency::Daily]);
    $future = RecurringTransaction::factory()->for($user)->for($account)->for($utilities)->create(['title' => 'Tomorrow', 'amount' => 5000, 'next_run' => now()->addDay()]);
    $blocked = RecurringTransaction::factory()->for($user)->for($account)->for($food)->create(['title' => 'Over budget', 'amount' => 25000, 'next_run' => now()->subMinute()]);
    $blockedNextRun = $blocked->next_run->toDateTimeString();

    app(RecurringTransactionService::class)->generate($due);
    expect(fn () => app(RecurringTransactionService::class)->generate($blocked))->toThrow(BudgetExceededException::class);

    $range = new DateRangeData($user->id, today()->startOfMonth(), today()->endOfMonth());
    $dashboard = app(DashboardService::class)->summary($range)->toArray();
    $analytics = app(AnalyticsService::class)->summary($range)->toArray();
    $report = app(FinancialReportService::class)->summary($range)->toArray();

    expect($account->refresh()->current_balance)->toBe(75000.0)
        ->and(Transaction::query()->where('recurring_transaction_id', $due->id)->count())->toBe(1)
        ->and(Transaction::query()->where('recurring_transaction_id', $future->id)->count())->toBe(0)
        ->and(Transaction::query()->where('recurring_transaction_id', $blocked->id)->count())->toBe(0)
        ->and($blocked->refresh()->next_run->toDateTimeString())->toBe($blockedNextRun)
        ->and($dashboard['reports']['expense']['total'])->toBe(25000.0)
        ->and(collect($dashboard['recent_transactions'])->pluck('title'))->toContain('Internet')
        ->and($analytics['reports']['expense']['total'])->toBe(25000.0)
        ->and($report['cash_flow']['total_expenses'])->toBe(25000.0);
});
