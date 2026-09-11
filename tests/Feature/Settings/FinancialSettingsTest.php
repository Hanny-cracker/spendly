<?php

use App\Livewire\Recurring\Create as RecurringCreate;
use App\Livewire\Settings\Index;
use App\Livewire\Transactions\Create as TransactionCreate;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserPreference;
use Livewire\Livewire;

function saveFinancialDefaults(User $user, Account $account, Category $expense, Category $income): void
{
    Livewire::actingAs($user)->test(Index::class)
        ->set('section', 'financial')
        ->set('defaultAccountId', (string) $account->id)
        ->set('defaultExpenseCategoryId', (string) $expense->id)
        ->set('defaultIncomeCategoryId', (string) $income->id)
        ->call('saveFinancialSettings')
        ->assertHasNoErrors();
}

it('shows only owned accounts and correctly typed categories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Account::factory()->for($user)->create(['name' => 'My Wallet']);
    Account::factory()->for($other)->create(['name' => 'Private Wallet']);
    Category::factory()->for($user)->expense()->create(['name' => 'My Food']);
    Category::factory()->for($user)->income()->create(['name' => 'My Salary']);
    Category::factory()->for($other)->expense()->create(['name' => 'Private Food']);

    Livewire::actingAs($user)->test(Index::class)->set('section', 'financial')
        ->assertSee('Financial Settings')->assertSee('My Wallet')->assertSee('My Food')->assertSee('My Salary')
        ->assertDontSee('Private Wallet')->assertDontSee('Private Food');
});

it('shows a useful empty state when the user has no accounts', function () {
    $user = User::factory()->create();
    Account::query()->where('user_id', $user->id)->delete();

    Livewire::actingAs($user)->test(Index::class)->set('section', 'financial')
        ->assertSee('No accounts available.')
        ->assertSee('Create an account before choosing a default.')
        ->assertSee(route('accounts.create'), false);
});

it('atomically changes the users default account and saves category defaults', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $oldDefault = Account::factory()->for($user)->create(['is_default' => true]);
    $newDefault = Account::factory()->for($user)->create(['is_default' => false]);
    $otherDefault = Account::factory()->for($other)->create(['is_default' => true]);
    $expense = Category::factory()->for($user)->expense()->create();
    $income = Category::factory()->for($user)->income()->create();

    saveFinancialDefaults($user, $newDefault, $expense, $income);

    $preference = $user->preference()->firstOrFail();
    expect($oldDefault->refresh()->is_default)->toBeFalse()
        ->and($newDefault->refresh()->is_default)->toBeTrue()
        ->and($otherDefault->refresh()->is_default)->toBeTrue()
        ->and($preference->default_expense_category_id)->toBe($expense->id)
        ->and($preference->default_income_category_id)->toBe($income->id);
});

it('rejects another users account and mismatched categories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $originalDefault = Account::query()->where('user_id', $user->id)->where('is_default', true)->firstOrFail();
    $foreignAccount = Account::factory()->for($other)->create(['is_default' => true]);
    $income = Category::factory()->for($user)->income()->create();
    $expense = Category::factory()->for($user)->expense()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->set('defaultAccountId', (string) $foreignAccount->id)
        ->set('defaultExpenseCategoryId', (string) $income->id)
        ->set('defaultIncomeCategoryId', (string) $expense->id)
        ->call('saveFinancialSettings')
        ->assertHasErrors(['defaultAccountId', 'defaultExpenseCategoryId', 'defaultIncomeCategoryId']);

    expect($originalDefault->refresh()->is_default)->toBeTrue()->and($foreignAccount->refresh()->is_default)->toBeTrue();
});

it('initializes transaction forms from financial defaults and switches categories by type', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['is_default' => false]);
    $expense = Category::factory()->for($user)->expense()->create();
    $income = Category::factory()->for($user)->income()->create();
    saveFinancialDefaults($user, $account, $expense, $income);

    Livewire::actingAs($user)->withQueryParams(['type' => 'expense'])->test(TransactionCreate::class)
        ->assertSet('accountId', (string) $account->id)->assertSet('categoryId', (string) $expense->id)
        ->set('type', 'income')->assertSet('categoryId', (string) $income->id)
        ->set('type', 'expense')->assertSet('categoryId', (string) $expense->id);

    Livewire::actingAs($user)->withQueryParams(['type' => 'income'])->test(TransactionCreate::class)
        ->assertSet('accountId', (string) $account->id)->assertSet('categoryId', (string) $income->id);
});

it('lets an explicit owned account override the default transaction account', function () {
    $user = User::factory()->create();
    $default = Account::factory()->for($user)->create(['is_default' => true]);
    $explicit = Account::factory()->for($user)->create(['is_default' => false]);

    Livewire::actingAs($user)->withQueryParams(['account' => $explicit->public_id])->test(TransactionCreate::class)
        ->assertSet('accountId', (string) $explicit->id);

    expect($default->refresh()->is_default)->toBeTrue();
});

it('initializes recurring creation from the default account and category', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['is_default' => false]);
    $expense = Category::factory()->for($user)->expense()->create();
    $income = Category::factory()->for($user)->income()->create();
    saveFinancialDefaults($user, $account, $expense, $income);

    Livewire::actingAs($user)->test(RecurringCreate::class)
        ->assertSet('accountId', (string) $account->id)->assertSet('categoryId', (string) $expense->id)
        ->set('type', 'income')->assertSet('categoryId', (string) $income->id);
});

it('nulls a deleted category preference without blocking deletion', function () {
    $user = User::factory()->create();
    $expense = Category::factory()->for($user)->expense()->create();
    UserPreference::factory()->for($user)->create(['default_expense_category_id' => $expense->id]);

    $expense->delete();

    expect($user->preference()->firstOrFail()->default_expense_category_id)->toBeNull();
});

it('changes defaults without creating financial activity or mutating history', function () {
    $user = User::factory()->create();
    $oldAccount = Account::factory()->for($user)->create(['is_default' => true, 'current_balance' => 500000]);
    $newAccount = Account::factory()->for($user)->savings()->create(['is_default' => false, 'current_balance' => 100000]);
    $expense = Category::factory()->for($user)->expense()->create();
    $income = Category::factory()->for($user)->income()->create();
    $transaction = Transaction::factory()->for($user)->for($oldAccount)->for($expense)->expense()->create(['amount' => 25000]);
    $budget = Budget::factory()->for($user)->for($expense)->create(['amount' => 100000]);
    $goal = Goal::factory()->for($user)->create(['current_amount' => 50000]);
    $contribution = GoalContribution::factory()->for($user)->for($goal)->create(['account_id' => $oldAccount->id, 'amount' => 50000]);
    $transactionCount = Transaction::query()->where('user_id', $user->id)->count();

    saveFinancialDefaults($user, $newAccount, $expense, $income);

    expect(Transaction::query()->where('user_id', $user->id)->count())->toBe($transactionCount)
        ->and($transaction->refresh()->amount)->toBe(25000.0)
        ->and($oldAccount->refresh()->current_balance)->toBe(500000.0)
        ->and($newAccount->refresh()->current_balance)->toBe(100000.0)
        ->and($budget->refresh()->amount)->toBe(100000.0)
        ->and($goal->refresh()->current_amount)->toBe(50000.0)
        ->and($contribution->refresh()->amount)->toBe(50000.0);
});
