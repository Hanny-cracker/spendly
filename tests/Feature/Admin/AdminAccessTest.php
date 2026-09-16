<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the Filament admin login', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('rejects non administrators from the admin panel', function (): void {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin')
        ->assertForbidden();
});

it('allows administrators to access the admin panel', function (): void {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin')
        ->assertOk();
});

it('allows administrators to view an existing user overview', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $target = User::factory()->create(['name' => 'Overview User']);

    $this->actingAs($admin)
        ->get('/admin/users/'.$target->getRouteKey())
        ->assertOk()
        ->assertSee('Overview User');
});

it('shows the selected users financial records to administrators', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $target = User::factory()->create(['name' => 'Financial User']);
    Account::factory()->for($target)->create(['current_balance' => 125000]);

    $this->actingAs($admin)
        ->get('/admin/users/'.$target->getRouteKey())
        ->assertOk()
        ->assertSee('125,000.00 XAF');
});
