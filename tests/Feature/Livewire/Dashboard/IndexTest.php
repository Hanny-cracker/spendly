<?php

use App\Livewire\Dashboard\Index;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\Category;

uses(RefreshDatabase::class);

it('renders the dashboard', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.dashboard.index');
});


it('uses the authenticated user when loading the dashboard', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet(
            'dashboardData',
            function ($dashboard) {

                expect($dashboard)
                    ->toBeArray()
                    ->not->toBeEmpty();

                return true;
            }
        );
});


it('uses the current month as the default date range', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet(
            'startDate',
            now()->startOfMonth()->toDateString()
        )
        ->assertSet(
            'endDate',
            now()->endOfMonth()->toDateString()
        );
});


it('can change the date range', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-01-31')
        ->assertSet(
            'startDate',
            '2026-01-01'
        )
        ->assertSet(
            'endDate',
            '2026-01-31'
        );
});


it('reloads dashboard data when the date range changes', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('startDate', '2026-01-01')
        ->set('endDate', '2026-01-31')
        ->assertSet(
            'dashboardData',
            function ($dashboard) {

                expect($dashboard)
                    ->toBeArray()
                    ->not->toBeEmpty();

                expect($dashboard)
                    ->toHaveKeys([
                        'start_date',
                        'end_date',
                        'reports',
                        'insights',
                        'budgets',
                        'recent_transactions',
                    ]);

                expect($dashboard['start_date'])
                    ->toBe('2026-01-01');

                expect($dashboard['end_date'])
                    ->toBe('2026-01-31');

                return true;
            }
        );
});


it('does not expose another users dashboard data', function () {

    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    $userCategory = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    $otherUserCategory = Category::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $userCategory->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $otherUserCategory->id,
    ]);

    $this->actingAs($user);

    $dashboard = Livewire::test(Index::class)
        ->get('dashboardData');

    expect($dashboard)
        ->toBeArray()
        ->not->toBeEmpty();

    $transactions = collect(
        $dashboard['recent_transactions']
    );

    expect($transactions)
        ->toHaveCount(1);

    expect($transactions->first()['user_id'])
        ->toBe($user->id);

    expect($transactions->pluck('user_id'))
        ->not->toContain($otherUser->id);
});