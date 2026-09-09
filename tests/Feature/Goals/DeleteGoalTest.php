<?php

use App\Livewire\Goals\Show;
use App\Models\Account;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('deletes an empty goal', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create();
    $this->actingAs($user);
    Livewire::test(Show::class, ['goal' => $goal])->set('confirmingGoalDeletion', true)->call('deleteGoal')->assertRedirect(route('goals'));
    $this->assertModelMissing($goal);
});

it('deletes a funded goal and history without financial side effects', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 500000]);
    $transaction = Transaction::factory()->for($user)->for($account)->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 1000000, 'current_amount' => 350000]);
    $contribution = GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $user->id, 'account_id' => $account->id, 'amount' => 350000]);
    $this->actingAs($user);
    Livewire::test(Show::class, ['goal' => $goal])->call('deleteGoal')->assertRedirect(route('goals'));

    $this->assertModelMissing($goal);
    $this->assertModelMissing($contribution);
    expect($account->refresh()->current_balance)->toBe(500000.0)->and($transaction->fresh())->not->toBeNull();
});

it('does not let another user delete a goal', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create();
    $contribution = GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $owner->id]);
    $this->actingAs($other);
    Livewire::test(Show::class, ['goal' => $goal])->assertForbidden();
    expect($goal->fresh())->not->toBeNull()->and($contribution->fresh())->not->toBeNull();
});
