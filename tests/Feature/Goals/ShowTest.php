<?php

use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;

it('protects goal details and renders progress and owned history', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create(['name' => 'Emergency Fund', 'target_amount' => 1000000, 'current_amount' => 350000]);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $owner->id, 'amount' => 50000, 'note' => 'September savings']);
    GoalContribution::factory()->create(['goal_id' => $goal->id, 'user_id' => $other->id, 'amount' => 12345, 'note' => 'Private contribution']);

    $this->get(route('goals.show', $goal))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('goals.show', $goal))->assertNotFound();
    $this->actingAs($owner)->get(route('goals.show', $goal))->assertOk()->assertSee('Emergency Fund')->assertSee('350,000')->assertSee('650,000')->assertSee('35.0%')->assertSee('September savings')->assertDontSee('Private contribution')->assertSee('Add Contribution')->assertSee(route('goals.edit', $goal));
});

it('hides contribution action when a goal is complete', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 100000, 'current_amount' => 100000]);
    $this->actingAs($user)->get(route('goals.show', $goal))->assertOk()->assertSee('Goal reached')->assertDontSee('+ Add Contribution');
});
