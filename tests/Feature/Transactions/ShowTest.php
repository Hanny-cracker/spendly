<?php

use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transactions\DeleteTransaction;
use App\Data\Transaction\CreateTransactionData;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Livewire\Transactions\Show;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createFinancialTransaction(User $user, TransactionType $type, TransactionStatus $status, float $amount, float $balance = 100000): array
{
    $account = Account::factory()->for($user)->create(['current_balance' => $balance, 'currency' => 'FCFA']);
    $category = Category::factory()->for($user)->create(['name' => $type->isIncome() ? 'Salary' : 'Food', 'type' => $type->isIncome() ? CategoryType::Income : CategoryType::Expense]);
    $transaction = app(CreateTransaction::class)->handle(new CreateTransactionData(userId: $user->id, accountId: $account->id, categoryId: $category->id, title: 'Test transaction', description: 'Transaction description', amount: $amount, type: $type, status: $status, date: Carbon::parse('2026-09-08')));

    return [$transaction, $account, $category];
}

it('allows the owner to view transaction details and edit action', function () {
    $user = User::factory()->create();
    [$transaction, $account, $category] = createFinancialTransaction($user, TransactionType::Expense, TransactionStatus::Completed, 25000);

    $this->actingAs($user)->get(route('transactions.show', $transaction))
        ->assertOk()->assertSee('Transaction Details')->assertSee('Test transaction')->assertSee('25,000')->assertSee($account->name)->assertSee($category->name)
        ->assertSee('href="'.route('transactions.edit', $transaction).'"', false);
});

it('protects transaction details from guests and other users', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    [$transaction] = createFinancialTransaction($owner, TransactionType::Expense, TransactionStatus::Completed, 1000);

    $this->get(route('transactions.show', $transaction))->assertRedirect(route('login'));
    $this->actingAs($otherUser)->get(route('transactions.show', $transaction))->assertNotFound();
});

it('reverses a completed expense exactly once when deleted', function () {
    $user = User::factory()->create();
    [$transaction, $account] = createFinancialTransaction($user, TransactionType::Expense, TransactionStatus::Completed, 25000);
    expect($account->fresh()->current_balance)->toBe(75000.0);
    $this->actingAs($user);

    Livewire::test(Show::class, ['transaction' => $transaction])->call('confirmDeletion')->assertSet('confirmingDeletion', true)->call('delete')->assertRedirect(route('transactions'));

    expect($account->fresh()->current_balance)->toBe(100000.0)->and(Transaction::withoutGlobalScopes()->find($transaction->id))->toBeNull();
});

it('reverses a completed income exactly once when deleted', function () {
    $user = User::factory()->create();
    [$transaction, $account] = createFinancialTransaction($user, TransactionType::Income, TransactionStatus::Completed, 50000);
    expect($account->fresh()->current_balance)->toBe(150000.0);
    $this->actingAs($user);

    Livewire::test(Show::class, ['transaction' => $transaction])->call('delete');

    expect($account->fresh()->current_balance)->toBe(100000.0);
});

it('does not adjust balance when deleting a pending transaction', function () {
    $user = User::factory()->create();
    [$transaction, $account] = createFinancialTransaction($user, TransactionType::Expense, TransactionStatus::Pending, 25000);
    $this->actingAs($user);

    Livewire::test(Show::class, ['transaction' => $transaction])->call('delete');

    expect($account->fresh()->current_balance)->toBe(100000.0);
});

it('prevents ordinary deletion of a transfer transaction', function () {
    $user = User::factory()->create();
    [$transaction, $account] = createFinancialTransaction($user, TransactionType::Expense, TransactionStatus::Completed, 25000);
    $destination = Account::factory()->for($user)->create();
    $transfer = Transfer::create(['user_id' => $user->id, 'from_account_id' => $account->id, 'to_account_id' => $destination->id, 'amount' => 25000, 'reference' => 'transfer-test', 'date' => now()]);
    $transaction->update(['transfer_id' => $transfer->id]);

    expect(fn () => app(DeleteTransaction::class)->handle($transaction->fresh()))->toThrow(DomainException::class);
    expect($transaction->fresh())->not->toBeNull()->and($account->fresh()->current_balance)->toBe(75000.0);
});

it('reauthorizes a malicious delete call', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    [$transaction] = createFinancialTransaction($owner, TransactionType::Expense, TransactionStatus::Completed, 1000);
    $this->actingAs($otherUser);

    Livewire::test(Show::class, ['transaction' => $transaction])->assertForbidden();
});
