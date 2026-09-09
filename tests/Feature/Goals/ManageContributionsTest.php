<?php

use App\Enums\GoalStatus;
use App\Livewire\Goals\Show;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use Livewire\Livewire;

it('replaces an edited contribution and keeps account balances unchanged', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 1000000, 'current_amount' => 150000]);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'amount' => 100000]);
    $contribution = GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'account_id' => $account->id, 'amount' => 50000]);

    $this->actingAs($user);
    Livewire::test(Show::class, ['goal' => $goal])->call('editContribution', $contribution->id)->set('amount', '25000')->set('note', 'Corrected')->call('updateContribution')->assertHasNoErrors();

    expect($goal->refresh()->current_amount)->toBe(125000.0)->and($contribution->refresh()->amount)->toBe(25000.0)->and($contribution->note)->toBe('Corrected')->and($account->refresh()->current_balance)->toBe(500000.0);
});

it('rejects an edited aggregate above the target and foreign accounts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 1000000, 'current_amount' => 950000]);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'amount' => 900000]);
    $contribution = GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'amount' => 50000]);
    $foreignAccount = Account::factory()->for($other)->create();
    $this->actingAs($user);

    Livewire::test(Show::class, ['goal' => $goal])->call('editContribution', $contribution->id)->set('amount', '150000')->call('updateContribution')->assertHasErrors(['amount']);
    Livewire::test(Show::class, ['goal' => $goal])->call('editContribution', $contribution->id)->set('accountId', (string) $foreignAccount->id)->call('updateContribution')->assertHasErrors(['accountId']);
    expect($contribution->refresh()->amount)->toBe(50000.0);
});

it('removes a contribution and reopens a completed goal', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 1000000, 'current_amount' => 1000000, 'status' => GoalStatus::Completed]);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'amount' => 900000]);
    $contribution = GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'account_id' => $account->id, 'amount' => 100000]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['goal' => $goal])->call('confirmContributionRemoval', $contribution->id)->call('removeContribution')->assertSee('90.0%');
    expect($goal->refresh()->current_amount)->toBe(900000.0)->and($goal->status)->toBe(GoalStatus::Active)->and($account->refresh()->current_balance)->toBe(500000.0);
    $this->assertModelMissing($contribution);
});

it('prevents another user from managing a contribution', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create(['current_amount' => 50000]);
    $contribution = GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $owner->id, 'amount' => 50000]);
    $this->actingAs($other);
    Livewire::test(Show::class, ['goal' => $goal])->assertForbidden();
    expect($contribution->fresh())->not->toBeNull()->and($goal->refresh()->current_amount)->toBe(50000.0);
});
