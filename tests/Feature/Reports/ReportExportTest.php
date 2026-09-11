<?php

use App\Data\Report\DateRangeData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\BudgetExceededException;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Services\RecurringTransactions\RecurringTransactionService;
use App\Services\Reports\FinancialReportService;
use Carbon\Carbon;

function reportExportUrl(string $route): string
{
    return route($route, ['start_date' => '2026-08-01', 'end_date' => '2026-08-31']);
}

it('requires authentication for report exports', function () {
    $this->get(reportExportUrl('reports.pdf'))->assertRedirect(route('login'));
    $this->get(reportExportUrl('reports.csv'))->assertRedirect(route('login'));
    $this->get(reportExportUrl('reports.print'))->assertRedirect(route('login'));
});

it('exports a pdf for the selected period including an empty report', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(reportExportUrl('reports.pdf'));

    $response->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('spendly-financial-report-2026-08-01-to-2026-08-31.pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

it('rejects invalid export dates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('reports.pdf', ['start_date' => '2026-09-30', 'end_date' => '2026-09-01']))->assertSessionHasErrors(['start_date', 'end_date']);
    $this->actingAs($user)->get(route('reports.csv', ['start_date' => 'invalid', 'end_date' => '2026-09-01']))->assertSessionHasErrors(['start_date']);
    $this->actingAs($user)->get(route('reports.print', ['start_date' => '2026-09-30', 'end_date' => '2026-09-01']))->assertSessionHasErrors(['start_date', 'end_date']);
});

it('exports only owned completed financial transactions as escaped csv rows', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $account = Account::factory()->for($user)->create(['name' => 'MTN MoMo', 'currency' => 'FCFA']);
    $category = Category::factory()->for($user)->create(['name' => 'Food', 'type' => CategoryType::Expense]);
    $recurring = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create();

    Transaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Groceries, "weekly"', 'description' => 'Rice, beans and "oil"', 'amount' => 25000, 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'date' => '2026-08-10']);
    Transaction::factory()->for($user)->for($account)->for($category)->create(['recurring_transaction_id' => $recurring->id, 'title' => 'Generated subscription', 'amount' => 12000, 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'date' => '2026-08-15']);
    Transaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Pending record', 'status' => TransactionStatus::Pending, 'date' => '2026-08-12']);
    Transaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Outside period', 'status' => TransactionStatus::Completed, 'date' => '2026-07-31']);
    Transaction::factory()->for($other)->create(['title' => 'Private transaction', 'status' => TransactionStatus::Completed, 'date' => '2026-08-12']);
    $destination = Account::factory()->for($user)->create();
    $transfer = Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $account->id, 'to_account_id' => $destination->id, 'amount' => 5000, 'reference' => 'CSV-TRANSFER', 'date' => '2026-08-20']);
    Transaction::factory()->for($user)->for($account)->for($category)->create(['transfer_id' => $transfer->id, 'title' => 'Internal transfer leg', 'amount' => 5000, 'status' => TransactionStatus::Completed, 'date' => '2026-08-20']);

    $goal = Goal::factory()->for($user)->create();
    GoalContribution::factory()->for($goal)->for($user)->create(['account_id' => $account->id, 'note' => 'Goal earmark marker']);

    $response = $this->actingAs($user)->get(reportExportUrl('reports.csv'));
    $content = $response->streamedContent();

    $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8')->assertDownload('spendly-transactions-2026-08-01-to-2026-08-31.csv');
    expect($content)
        ->toStartWith("\xEF\xBB\xBFDate,Type,Title,Description,Category,Account,Amount,Currency,Status,Recurring")
        ->toContain('"Groceries, ""weekly"""')
        ->toContain('"Rice, beans and ""oil"""')
        ->toContain('Generated subscription')
        ->toContain(',25000,XAF,completed,No')
        ->toContain(',12000,XAF,completed,Yes')
        ->not->toContain('Pending record')
        ->not->toContain('Outside period')
        ->not->toContain('Private transaction')
        ->not->toContain('Internal transfer leg')
        ->not->toContain('Goal earmark marker');
});

it('shows responsive print csv and pdf actions using the selected period', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('reports'))
        ->assertOk()
        ->assertSee('Print')
        ->assertSee('Export CSV')
        ->assertSee('Export PDF')
        ->assertSee(e(route('reports.print', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()])), false)
        ->assertSee('report-controls', false);
});

it('renders a print report from authoritative owned period data', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $income = Category::factory()->for($user)->income()->create();
    $expense = Category::factory()->for($user)->expense()->create();
    Transaction::factory()->for($user)->for($account)->for($income)->income()->create(['title' => 'September Salary', 'amount' => 500000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-05']);
    Transaction::factory()->for($user)->for($account)->for($expense)->expense()->create(['title' => 'September Food', 'amount' => 150000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-06']);
    Transaction::factory()->for($user)->for($account)->for($expense)->expense()->create(['title' => 'August Food', 'amount' => 50000, 'status' => TransactionStatus::Completed, 'date' => '2026-08-20']);
    Transaction::factory()->for($other)->income()->create(['title' => 'Private Salary', 'amount' => 900000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-05']);

    $response = $this->actingAs($user)->get(route('reports.print', ['start_date' => '2026-09-01', 'end_date' => '2026-09-30']));

    $response->assertOk()->assertSee('Print Report')->assertSee('500,000')->assertSee('150,000')->assertSee('350,000')->assertDontSee('Private Salary')->assertSee('window.print()', false)->assertSee('@media print', false);
});

it('renders pdf presentation values from the exact financial report result', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $income = Category::factory()->for($user)->income()->create();
    $expense = Category::factory()->for($user)->expense()->create();
    Transaction::factory()->for($user)->for($account)->for($income)->income()->create(['amount' => 500000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-05']);
    Transaction::factory()->for($user)->for($account)->for($expense)->expense()->create(['amount' => 150000, 'status' => TransactionStatus::Completed, 'date' => '2026-09-06']);
    $report = app(FinancialReportService::class)->summary(new DateRangeData($user->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30')))->toArray();

    $rendered = view('reports.pdf', ['report' => $report])->render();

    expect($report['cash_flow']['net_cash_flow'])->toBe(350000.0)
        ->and($rendered)->toContain('500,000 FCFA')->toContain('150,000 FCFA')->toContain('350,000 FCFA');
});

it('exports a generated recurring occurrence but never its future schedule', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000, 'currency' => 'FCFA']);
    $category = Category::factory()->for($user)->expense()->create();
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Future Internet', 'amount' => 25000, 'next_run' => '2026-09-10 08:00:00']);

    expect($this->actingAs($user)->get(reportExportUrl('reports.csv'))->streamedContent())->not->toContain('Future Internet');

    $schedule->update(['next_run' => '2026-08-15 08:00:00']);
    app(RecurringTransactionService::class)->generate($schedule->refresh());

    $csv = $this->actingAs($user)->get(reportExportUrl('reports.csv'))->streamedContent();
    expect($csv)->toContain('Future Internet')->toContain(',25000,XAF,completed,Yes');
});

it('keeps every export unchanged when a recurring expense fails its budget', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->expense()->create();
    Budget::factory()->for($user)->for($category)->create(['amount' => 10000, 'period' => BudgetPeriod::Monthly, 'start_date' => '2026-08-01', 'end_date' => '2026-08-31', 'is_active' => true]);
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Blocked Internet', 'amount' => 25000, 'next_run' => '2026-08-15 08:00:00']);

    expect(fn () => app(RecurringTransactionService::class)->generate($schedule))->toThrow(BudgetExceededException::class);

    $report = app(FinancialReportService::class)->summary(new DateRangeData($user->id, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31')))->toArray();
    expect($report['cash_flow']['total_expenses'])->toBe(0.0)
        ->and($this->actingAs($user)->get(reportExportUrl('reports.csv'))->streamedContent())->not->toContain('Blocked Internet');
    $this->actingAs($user)->get(reportExportUrl('reports.print'))->assertOk()->assertSee('0 FCFA');
});

it('uses corrected savings analysis including goal double-count protection in exports', function () {
    $user = User::factory()->create();
    $cash = Account::factory()->for($user)->cash()->create();
    $savings = Account::factory()->for($user)->savings()->create();
    $income = Category::factory()->for($user)->income()->create();
    Transaction::factory()->for($user)->for($cash)->for($income)->income()->create(['amount' => 500000, 'status' => TransactionStatus::Completed, 'date' => '2026-08-05']);
    Transfer::query()->create(['user_id' => $user->id, 'from_account_id' => $cash->id, 'to_account_id' => $savings->id, 'amount' => 100000, 'reference' => 'REPORT-SAVE', 'date' => '2026-08-10']);
    $savingsGoal = Goal::factory()->for($user)->create(['target_amount' => 100000]);
    $cashGoal = Goal::factory()->for($user)->create(['target_amount' => 100000]);
    GoalContribution::factory()->for($savingsGoal)->for($user)->create(['account_id' => $savings->id, 'amount' => 40000, 'contributed_at' => '2026-08-12']);
    GoalContribution::factory()->for($cashGoal)->for($user)->create(['account_id' => $cash->id, 'amount' => 20000, 'contributed_at' => '2026-08-12']);
    $report = app(FinancialReportService::class)->summary(new DateRangeData($user->id, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31')))->toArray();

    expect(data_get($report, 'insights.savings_rate.qualifying_period_savings'))->toBe(120000.0)
        ->and($report['cash_flow']['savings_rate'])->toBe(24.0);
    $this->actingAs($user)->get(reportExportUrl('reports.print'))->assertOk()->assertSee('24.0%');
    expect(view('reports.pdf', ['report' => $report])->render())->toContain('24.0%');
});
