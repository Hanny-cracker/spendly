<?php

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function removeOnboardingAccounts(User $user): void
{
    Account::withoutGlobalScopes()->where('user_id', $user->id)->delete();
}

it('allows an authenticated user to render the accounts page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('accounts'))
        ->assertOk()
        ->assertSee('Accounts')
        ->assertSee('Add Account');
});

it('redirects guests to login', function () {
    $this->get(route('accounts'))->assertRedirect(route('login'));
});

it('renders only the users accounts and calculates their summary', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    removeOnboardingAccounts($user);
    removeOnboardingAccounts($otherUser);

    Account::factory()->for($user)->create([
        'name' => 'Daily Cash',
        'type' => AccountType::Cash,
        'currency' => 'FCFA',
        'current_balance' => 125000,
    ]);
    Account::factory()->for($user)->create([
        'name' => 'Savings Vault',
        'type' => AccountType::Savings,
        'currency' => 'FCFA',
        'current_balance' => 75000,
    ]);
    Account::factory()->for($otherUser)->create([
        'name' => 'Private Offshore Account',
        'currency' => 'FCFA',
        'current_balance' => 987654,
    ]);

    $this->actingAs($user)
        ->get(route('accounts'))
        ->assertOk()
        ->assertSee('Daily Cash')
        ->assertSee('Savings Vault')
        ->assertSee('200,000')
        ->assertSee('>2<', false)
        ->assertDontSee('Private Offshore Account')
        ->assertDontSee('987,654');
});

it('identifies and orders the default account first', function () {
    $user = User::factory()->create();
    removeOnboardingAccounts($user);

    Account::factory()->for($user)->create(['name' => 'Alpha Cash', 'is_default' => false]);
    Account::factory()->for($user)->create(['name' => 'Primary Wallet', 'is_default' => true]);

    $this->actingAs($user)
        ->get(route('accounts'))
        ->assertOk()
        ->assertSee('Default')
        ->assertSeeInOrder(['Primary Wallet', 'Alpha Cash']);
});

it('renders the empty state when the user has no accounts', function () {
    $user = User::factory()->create();
    removeOnboardingAccounts($user);

    $this->actingAs($user)
        ->get(route('accounts'))
        ->assertOk()
        ->assertSee('No accounts yet')
        ->assertSee('Add your first account to start tracking your money.')
        ->assertSee('>0<', false);
});

it('renders zero and negative balances safely', function () {
    $user = User::factory()->create();
    removeOnboardingAccounts($user);

    Account::factory()->for($user)->create([
        'name' => 'Empty Wallet',
        'currency' => 'FCFA',
        'current_balance' => 0,
    ]);
    Account::factory()->for($user)->create([
        'name' => 'Credit Balance',
        'currency' => 'FCFA',
        'current_balance' => -15000,
    ]);

    $this->actingAs($user)
        ->get(route('accounts'))
        ->assertOk()
        ->assertSee('Empty Wallet')
        ->assertSee('Credit Balance')
        ->assertSee('-15,000')
        ->assertSee('0');
});
