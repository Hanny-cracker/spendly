<?php

use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;

function reportExportUrl(string $route): string
{
    return route($route, ['start_date' => '2026-08-01', 'end_date' => '2026-08-31']);
}

it('requires authentication for report exports', function () {
    $this->get(reportExportUrl('reports.pdf'))->assertRedirect(route('login'));
    $this->get(reportExportUrl('reports.csv'))->assertRedirect(route('login'));
});

it('exports a pdf for the selected period including an empty report', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(reportExportUrl('reports.pdf'));

    $response->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('spendly-report-2026-08-01-to-2026-08-31.pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

it('rejects invalid export dates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('reports.pdf', ['start_date' => '2026-09-30', 'end_date' => '2026-09-01']))->assertSessionHasErrors(['start_date', 'end_date']);
    $this->actingAs($user)->get(route('reports.csv', ['start_date' => 'invalid', 'end_date' => '2026-09-01']))->assertSessionHasErrors(['start_date']);
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
        ->toStartWith("\xEF\xBB\xBFDate,\"Public ID\",Title,Type,Category,Account,Amount,Currency,Status,Description")
        ->toContain('"Groceries, ""weekly"""')
        ->toContain('"Rice, beans and ""oil"""')
        ->toContain('Generated subscription')
        ->toContain(',25000,XAF,completed,')
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
        ->assertSee('CSV')
        ->assertSee('PDF')
        ->assertSee('window.print()', false)
        ->assertSee('report-controls', false);
});
