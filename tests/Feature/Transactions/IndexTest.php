<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Livewire\Transactions\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function transactionFor(User $user, array $attributes = []): Transaction
{
    $account = $attributes['account'] ?? Account::factory()->for($user)->create();
    $category = $attributes['category'] ?? Category::factory()->for($user)->create();
    unset($attributes['account'], $attributes['category']);

    return Transaction::factory()->for($user)->for($account)->for($category)->create($attributes);
}

it('allows an authenticated user to render the transactions page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('transactions'))
        ->assertOk()
        ->assertSee('Track and manage your financial activity.')
        ->assertSee('Add Transaction')
        ->assertSee('href="'.route('transactions.create').'"', false);
});

it('redirects guests from the transactions page', function () {
    $this->get(route('transactions'))->assertRedirect(route('login'));
});

it('renders only the users transactions newest first', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    transactionFor($user, ['title' => 'Older purchase', 'date' => '2026-01-01']);
    $newest = transactionFor($user, ['title' => 'Newest purchase', 'date' => '2026-01-20']);
    transactionFor($otherUser, ['title' => 'Private purchase']);
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSeeInOrder(['Newest purchase', 'Older purchase'])
        ->assertSee('href="'.route('transactions.show', $newest).'"', false)
        ->assertSee('href="'.route('transactions.edit', $newest).'"', false)
        ->assertSee('View')
        ->assertSee('Edit')
        ->assertDontSee('Private purchase');
});

it('searches transaction titles and descriptions', function () {
    $user = User::factory()->create();
    transactionFor($user, ['title' => 'Grocery market']);
    transactionFor($user, ['title' => 'Bus ticket', 'description' => 'Morning commute']);
    $this->actingAs($user);

    Livewire::test(Index::class)->set('search', 'Grocery')->assertSee('Grocery market')->assertDontSee('Bus ticket')
        ->set('search', 'commute')->assertSee('Bus ticket')->assertDontSee('Grocery market');
});

it('filters transactions by type', function () {
    $user = User::factory()->create();
    transactionFor($user, ['title' => 'Salary payment', 'type' => TransactionType::Income]);
    transactionFor($user, ['title' => 'Food purchase', 'type' => TransactionType::Expense]);
    $this->actingAs($user);

    Livewire::test(Index::class)->set('type', 'income')->assertSee('Salary payment')->assertDontSee('Food purchase');
});

it('filters by an owned account and rejects another users account filter', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $wallet = Account::factory()->for($user)->create(['name' => 'Daily Wallet']);
    $savings = Account::factory()->for($user)->create(['name' => 'Savings Vault']);
    $private = Account::factory()->for($otherUser)->create(['name' => 'Private Account']);
    transactionFor($user, ['account' => $wallet, 'title' => 'Wallet item']);
    transactionFor($user, ['account' => $savings, 'title' => 'Savings item']);
    transactionFor($otherUser, ['account' => $private, 'title' => 'Private item']);
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertDontSee('Private Account')
        ->set('account', (string) $wallet->id)->assertSee('Wallet item')->assertDontSee('Savings item')
        ->set('account', (string) $private->id)->assertDontSee('Wallet item')->assertDontSee('Private item');
});

it('filters by an owned category', function () {
    $user = User::factory()->create();
    $food = Category::factory()->for($user)->create(['name' => 'Food']);
    $travel = Category::factory()->for($user)->create(['name' => 'Travel']);
    transactionFor($user, ['category' => $food, 'title' => 'Lunch']);
    transactionFor($user, ['category' => $travel, 'title' => 'Train']);
    $this->actingAs($user);

    Livewire::test(Index::class)->set('category', (string) $food->id)->assertSee('Lunch')->assertDontSee('Train');
});

it('filters by an optional date range', function () {
    $user = User::factory()->create();
    transactionFor($user, ['title' => 'January item', 'date' => '2026-01-15']);
    transactionFor($user, ['title' => 'February item', 'date' => '2026-02-15']);
    $this->actingAs($user);

    Livewire::test(Index::class)->set('startDate', '2026-02-01')->set('endDate', '2026-02-28')->assertSee('February item')->assertDontSee('January item');
});

it('clears every filter', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('search', 'food')->set('type', 'expense')->set('account', '1')->set('category', '2')->set('startDate', '2026-01-01')->set('endDate', '2026-01-31')
        ->call('clearFilters')
        ->assertSet('search', '')->assertSet('type', '')->assertSet('account', '')->assertSet('category', '')->assertSet('startDate', '')->assertSet('endDate', '');
});

it('paginates transactions fifteen at a time', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();
    Transaction::factory()->count(16)->for($user)->for($account)->for($category)->create();
    $this->actingAs($user);

    Livewire::test(Index::class)->assertViewHas('transactions', fn ($transactions) => $transactions->count() === 15 && $transactions->total() === 16);
});

it('calculates summaries across the filtered result set and excludes transfers', function () {
    $user = User::factory()->create();
    transactionFor($user, ['title' => 'Salary', 'type' => TransactionType::Income, 'status' => TransactionStatus::Completed, 'amount' => 100000]);
    transactionFor($user, ['title' => 'Rent', 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'amount' => 30000]);
    transactionFor($user, ['title' => 'Other expense', 'type' => TransactionType::Expense, 'amount' => 5000]);
    $this->actingAs($user);

    Livewire::test(Index::class)->set('search', 'Salary')->assertViewHas('summary', ['income' => 100000.0, 'expenses' => 0.0, 'net' => 100000.0]);
});

it('renders the empty state for a user without transactions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test(Index::class)->assertSee('No transactions yet')->assertSee('Your income and expenses will appear here.');
    Livewire::test(Index::class)
        ->assertSee('Add your first transaction')
        ->assertSee('href="'.route('transactions.create').'"', false);
});

it('renders a distinct state when filters have no matches', function () {
    $user = User::factory()->create();
    transactionFor($user, ['title' => 'Existing transaction']);
    $this->actingAs($user);
    Livewire::test(Index::class)
        ->set('search', 'missing')
        ->assertSee('No matching transactions')
        ->assertSee('Try changing or clearing your filters.')
        ->assertSee('Clear filters')
        ->assertDontSee('Add your first transaction');
});
