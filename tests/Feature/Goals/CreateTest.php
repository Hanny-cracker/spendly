<?php

use App\Enums\GoalStatus;
use App\Livewire\Goals\Create;
use App\Models\Goal;
use App\Models\User;
use Livewire\Livewire;

it('protects create from guests', function () {
    $this->get(route('goals.create'))->assertRedirect(route('login'));
});

it('creates an owned active goal starting at zero', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test(Create::class)->set('name', 'Emergency Fund')->set('targetAmount', '1000000')->set('targetDate', now()->addMonth()->toDateString())->set('description', 'Safety net')->call('save')->assertHasNoErrors()->assertRedirect(route('goals'));
    $goal = Goal::query()->where('name', 'Emergency Fund')->firstOrFail();
    expect($goal->user_id)->toBe($user->id)->and($goal->current_amount)->toBe(0.0)->and($goal->status)->toBe(GoalStatus::Active);
});

it('validates name amount and target date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test(Create::class)->set('name', '')->set('targetAmount', '0')->set('targetDate', now()->subDay()->toDateString())->call('save')->assertHasErrors(['name', 'targetAmount', 'targetDate']);
});
