<?php

use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Livewire\Analytics\Index;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows an authenticated user to render analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analytics'))
        ->assertOk()
        ->assertSee('Understand where your money goes')
        ->assertSee('Financial Health');
});

it('protects the analytics route from guests', function () {
    $this->get(route('analytics'))
        ->assertRedirect(route('login'));
});

it('defaults to the current month', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet('startDate', now()->startOfMonth()->toDateString())
        ->assertSet('endDate', now()->endOfMonth()->toDateString());
});

it('refreshes analytics when the date range changes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-01-31')
        ->assertSet('analyticsData.start_date', '2026-01-01')
        ->assertSet('analyticsData.end_date', '2026-01-31');
});

it('uses only the authenticated users financial data', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $otherAccount = Account::factory()->for($otherUser)->create();

    Transaction::factory()->for($user)->for($account)->income()->create([
        'amount' => 120000,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);
    Transaction::factory()->for($otherUser)->for($otherAccount)->income()->create([
        'amount' => 900000,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet('analyticsData.reports.income.total', 120000.0)
        ->assertSee('120,000')
        ->assertDontSee('900,000');
});

it('renders safely with no transactions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertOk()
        ->assertSee('No expense categories yet')
        ->assertSee('No income recorded');
});

it('renders financial health from the existing insight', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet('analyticsData.insights.financial_health.status', 'poor')
        ->assertSee('Financial Health')
        ->assertSee('poor');
});

it('renders category analysis for expense data', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create([
        'name' => 'Groceries',
        'type' => CategoryType::Expense,
    ]);

    Transaction::factory()->for($user)->for($account)->for($category)->expense()->create([
        'amount' => 75000,
        'status' => TransactionStatus::Completed,
        'date' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('Groceries')
        ->assertSee('75,000 FCFA');
});
