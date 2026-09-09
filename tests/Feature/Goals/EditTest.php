<?php

use App\Enums\GoalStatus;
use App\Livewire\Goals\Edit;
use App\Models\Goal;
use App\Models\User;
use Livewire\Livewire;

it('allows only the owner to edit goal configuration', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->for($owner)->create(['current_amount' => 100000]);
    $this->get(route('goals.edit', $goal))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('goals.edit', $goal))->assertNotFound();
    $this->actingAs($owner)->get(route('goals.edit', $goal))->assertOk()->assertDontSee('current_amount');
});

it('updates fields while preserving saved progress', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['current_amount' => 100000]);
    $date = now()->addYear()->toDateString();
    $this->actingAs($user);
    Livewire::test(Edit::class, ['goal' => $goal])->set('name', 'New Name')->set('description', 'New description')->set('targetAmount', '800000')->set('targetDate', $date)->call('save')->assertHasNoErrors()->assertRedirect(route('goals.show', $goal));
    expect($goal->refresh()->name)->toBe('New Name')->and($goal->description)->toBe('New description')->and($goal->target_amount)->toBe(800000.0)->and($goal->current_amount)->toBe(100000.0)->and($goal->target_date->toDateString())->toBe($date);
});

it('rejects a target below savings and reopens when the target increases', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user)->create(['target_amount' => 500000, 'current_amount' => 500000, 'status' => GoalStatus::Completed]);
    $this->actingAs($user);
    Livewire::test(Edit::class, ['goal' => $goal])->set('targetAmount', '400000')->call('save')->assertHasErrors(['targetAmount']);
    Livewire::test(Edit::class, ['goal' => $goal])->set('targetAmount', '700000')->call('save')->assertHasNoErrors();
    expect($goal->refresh()->status)->toBe(GoalStatus::Active)->and($goal->current_amount)->toBe(500000.0);
});
