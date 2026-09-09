<?php

use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use App\Models\User;

it('requires authentication', function () {
    $this->get(route('recurring'))->assertRedirect(route('login'));
});

it('renders only owned schedules with summary and schedule information', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    RecurringTransaction::factory()->for($user)->create(['title' => 'Internet Subscription', 'type' => TransactionType::Expense, 'amount' => 25000, 'next_run' => now()->addWeek(), 'last_generated_at' => now()->subMonth()]);
    RecurringTransaction::factory()->for($user)->create(['title' => 'Salary Payment', 'type' => TransactionType::Income, 'amount' => 450000, 'next_run' => now()->addMonth(), 'last_generated_at' => null]);
    RecurringTransaction::factory()->for($user)->create(['title' => 'Finished Plan', 'status' => RecurringStatus::Completed, 'next_run' => now()->subDay()]);
    RecurringTransaction::factory()->for($other)->create(['title' => 'Private Schedule']);

    $this->actingAs($user)->get(route('recurring'))->assertOk()->assertSee('Internet Subscription')->assertSee('−25,000')->assertSee('Salary Payment')->assertSee('+450,000')->assertSee('Never generated')->assertSee('Finished Plan')->assertSee('Completed')->assertDontSee('Private Schedule')->assertSee(route('recurring.create'));
});

it('renders the empty state', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('recurring'))->assertOk()->assertSee('No recurring transactions yet')->assertSee('Create recurring transaction');
});
