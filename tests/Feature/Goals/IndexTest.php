<?php

use App\Models\Goal;
use App\Models\User;

it('requires authentication', function () {
    $this->get(route('goals'))->assertRedirect(route('login'));
});

it('renders owned goal summaries progress and states', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Goal::factory()->for($user)->create(['name' => 'Emergency Fund', 'target_amount' => 1000000, 'current_amount' => 350000, 'target_date' => now()->addMonth()]);
    Goal::factory()->for($user)->create(['name' => 'Completed Laptop', 'target_amount' => 500000, 'current_amount' => 500000]);
    Goal::factory()->for($user)->create(['name' => 'Overdue Travel', 'target_amount' => 1000000, 'current_amount' => 0, 'target_date' => now()->subDay()]);
    Goal::factory()->for($user)->create(['name' => 'No Deadline', 'target_amount' => 500000, 'current_amount' => 0, 'target_date' => null]);
    Goal::factory()->for($other)->create(['name' => 'Private Goal', 'target_amount' => 9999999]);
    $this->actingAs($user)->get(route('goals'))->assertOk()->assertSee('Emergency Fund')->assertSee('35.0%')->assertSee('Completed')->assertSee('Overdue')->assertSee('No target date')->assertSee('3,000,000')->assertSee('850,000')->assertSee('2,150,000')->assertDontSee('Private Goal')->assertSee(route('goals.create'));
});

it('renders the empty state', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('goals'))->assertOk()->assertSee('No savings goals yet')->assertSee('Create your first goal');
});
