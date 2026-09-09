<?php

use App\Enums\GoalStatus;
use App\Livewire\Goals\Show;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use Livewire\Livewire;

it('stores contributions exactly once without changing account balance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 1000000, 'current_amount' => 0]);

    $this->actingAs($user);
    Livewire::test(Show::class, ['goal' => $goal])->set('amount', '50000')->set('accountId', (string) $account->id)->set('contributedAt', now()->toDateString())->set('note', 'First')->call('addContribution')->assertHasNoErrors();
    Livewire::test(Show::class, ['goal' => $goal->refresh()])->set('amount', '25000')->set('contributedAt', now()->subDay()->toDateString())->call('addContribution')->assertHasNoErrors();

    expect(GoalContribution::query()->count())->toBe(2)->and($goal->refresh()->current_amount)->toBe(75000.0)->and($account->refresh()->current_balance)->toBe(500000.0);
    $this->assertDatabaseHas('goal_contributions', ['goal_id' => $goal->id, 'note' => 'First']);
});

it('validates contributions and rejects foreign accounts and over contributions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 100000, 'current_amount' => 90000]);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'amount' => 90000]);
    $foreignAccount = Account::factory()->for($other)->create();
    $this->actingAs($user);

    Livewire::test(Show::class, ['goal' => $goal])->set('amount', '0')->call('addContribution')->assertHasErrors(['amount']);
    Livewire::test(Show::class, ['goal' => $goal])->set('amount', '5000')->set('accountId', (string) $foreignAccount->id)->set('contributedAt', now()->toDateString())->call('addContribution')->assertHasErrors(['accountId']);
    Livewire::test(Show::class, ['goal' => $goal])->set('amount', '11000')->set('contributedAt', now()->toDateString())->call('addContribution')->assertHasErrors(['amount']);
});

it('completes a goal at its target', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 1000000, 'current_amount' => 950000]);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'amount' => 950000]);
    $this->actingAs($user);
    Livewire::test(Show::class, ['goal' => $goal])->set('amount', '50000')->set('contributedAt', now()->toDateString())->call('addContribution')->assertHasNoErrors();
    expect($goal->refresh()->current_amount)->toBe(1000000.0)->and($goal->status)->toBe(GoalStatus::Completed);
});
