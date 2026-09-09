<?php

use App\Livewire\Transactions\Create;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the create transaction page for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('transactions.create'))
        ->assertOk()
        ->assertSee('Add Transaction')
        ->assertSee('Save Transaction');
});

it('protects the create transaction page from guests', function () {
    $this->get(route('transactions.create'))->assertRedirect(route('login'));
});

it('safely preselects a transaction type from the query string', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::withQueryParams(['type' => 'income'])->test(Create::class)->assertSet('type', 'income');
    Livewire::withQueryParams(['type' => 'invalid'])->test(Create::class)->assertSet('type', 'expense');
});
