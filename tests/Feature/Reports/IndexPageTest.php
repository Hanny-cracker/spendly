<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Livewire\Reports\Index;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('requires authentication and defaults to the current month', function () {
    $this->get(route('reports'))->assertRedirect(route('login'));
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('reports'))->assertOk()->assertSee('Financial Report')->assertSee(now()->startOfMonth()->format('d M'))->assertSee(now()->endOfMonth()->format('d M'))->assertDontSee('Closing Balance');
});

it('applies a valid custom range and rejects an invalid range', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test(Index::class)->set('period', 'custom')->set('startDate', '2026-08-01')->set('endDate', '2026-08-31')->call('applyCustom')->assertHasNoErrors()->assertSet('reportData.start_date', '2026-08-01')->assertSet('reportData.end_date', '2026-08-31')->assertSee('start_date=2026-08-01', false)->assertSee('end_date=2026-08-31', false);
    Livewire::test(Index::class)->set('period', 'custom')->set('startDate', '2026-09-30')->set('endDate', '2026-09-01')->call('applyCustom')->assertHasErrors(['startDate', 'endDate']);
});

it('renders owned completed activity and excludes other users and pending activity', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Transaction::factory()->for($user)->create(['title' => 'Owned Expense', 'amount' => 25000, 'type' => TransactionType::Expense, 'status' => TransactionStatus::Completed, 'date' => now()]);
    Transaction::factory()->for($user)->create(['title' => 'Pending Income', 'amount' => 700000, 'type' => TransactionType::Income, 'status' => TransactionStatus::Pending, 'date' => now()]);
    Transaction::factory()->for($other)->create(['title' => 'Private Income', 'amount' => 900000, 'type' => TransactionType::Income, 'status' => TransactionStatus::Completed, 'date' => now()]);
    $this->actingAs($user)->get(route('reports'))->assertOk()->assertSee('25,000')->assertDontSee('700,000')->assertDontSee('900,000')->assertDontSee('Private Income');
});

it('renders the no activity state', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('reports'))->assertOk()->assertSee('No financial activity for this period')->assertSee('Try another date range');
});
