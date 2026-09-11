<?php

use App\Livewire\Settings\Index;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserPreference;
use Livewire\Livewire;

it('protects the settings page and renders it for an authenticated user', function () {
    $this->get(route('settings'))->assertRedirect(route('login'));

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('settings'))->assertOk()->assertSee('Save Profile')->assertSee('Preferences');
});

it('loads only the authenticated profile', function () {
    $user = User::factory()->create(['name' => 'Current Person', 'email' => 'current@example.com']);
    User::factory()->create(['name' => 'Private Person', 'email' => 'private@example.com']);

    Livewire::actingAs($user)->test(Index::class)
        ->assertSet('name', 'Current Person')
        ->assertSet('email', 'current@example.com')
        ->assertDontSee('Private Person')
        ->assertDontSee('private@example.com');
});

it('trims and updates only the authenticated users profile', function () {
    $user = User::factory()->create(['name' => 'Before']);
    $other = User::factory()->create(['name' => 'Other']);

    Livewire::actingAs($user)->test(Index::class)
        ->set('name', '  Updated Name  ')
        ->set('email', 'UPDATED@EXAMPLE.COM')
        ->call('saveProfile')
        ->assertHasNoErrors()
        ->assertSee('Profile updated.');

    expect($user->refresh()->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->email_verified_at)->toBeNull()
        ->and($other->refresh()->name)->toBe('Other');
});

it('validates profile email and accepts the current address', function () {
    $user = User::factory()->create(['email' => 'current@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($user)->test(Index::class)->set('email', 'invalid')->call('saveProfile')->assertHasErrors(['email']);
    Livewire::actingAs($user)->test(Index::class)->set('email', 'taken@example.com')->call('saveProfile')->assertHasErrors(['email']);
    Livewire::actingAs($user)->test(Index::class)->set('email', 'current@example.com')->call('saveProfile')->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

it('persists preferences independently for each user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    UserPreference::factory()->for($other)->create(['timezone' => 'UTC', 'week_starts_on' => 'monday']);

    Livewire::actingAs($user)->test(Index::class)
        ->set('section', 'preferences')
        ->set('timezone', 'Africa/Douala')
        ->set('dateFormat', 'Y-m-d')
        ->set('weekStartsOn', 'sunday')
        ->call('savePreferences')
        ->assertHasNoErrors()
        ->assertSee('Preferences saved.');

    Livewire::actingAs($user)->test(Index::class)
        ->assertSet('timezone', 'Africa/Douala')
        ->assertSet('dateFormat', 'Y-m-d')
        ->assertSet('weekStartsOn', 'sunday');

    expect($other->preference()->first()->timezone)->toBe('UTC')
        ->and($other->preference()->first()->week_starts_on)->toBe('monday');
});

it('rejects unsupported preference values', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->set('currency', 'USD')
        ->set('timezone', 'Invalid/Timezone')
        ->set('dateFormat', 'invalid')
        ->set('weekStartsOn', 'friday')
        ->call('savePreferences')
        ->assertHasErrors(['currency', 'timezone', 'dateFormat', 'weekStartsOn']);

    expect(UserPreference::query()->whereBelongsTo($user)->exists())->toBeFalse();
});

it('does not mutate financial records when preferences change', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 450000]);
    $category = Category::factory()->for($user)->expense()->create();
    $transaction = Transaction::factory()->for($user)->for($account)->for($category)->expense()->create(['amount' => 25000]);
    $budget = Budget::factory()->for($user)->for($category)->create(['amount' => 100000]);
    $goal = Goal::factory()->for($user)->create(['target_amount' => 500000, 'current_amount' => 50000]);
    $contribution = GoalContribution::factory()->for($user)->for($goal)->create(['account_id' => $account->id, 'amount' => 50000]);

    Livewire::actingAs($user)->test(Index::class)->set('timezone', 'Africa/Douala')->set('dateFormat', 'Y-m-d')->call('savePreferences')->assertHasNoErrors();

    expect($account->refresh()->current_balance)->toBe(450000.0)
        ->and($transaction->refresh()->amount)->toBe(25000.0)
        ->and($budget->refresh()->amount)->toBe(100000.0)
        ->and($goal->refresh()->target_amount)->toBe(500000.0)
        ->and($goal->current_amount)->toBe(50000.0)
        ->and($contribution->refresh()->amount)->toBe(50000.0);
});

it('removes preferences when their owner is deleted', function () {
    $user = User::factory()->create();
    UserPreference::factory()->for($user)->create();

    $user->delete();

    expect(UserPreference::query()->count())->toBe(0);
});
