<?php

use App\Enums\AccountType;
use App\Livewire\Accounts\Create;
use App\Livewire\Accounts\Edit;
use App\Livewire\Accounts\Show;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

function clearManagedAccounts(User $user): void
{
    Account::withoutGlobalScopes()->where('user_id', $user->id)->delete();
}

it('protects create show and edit pages from guests', function () {
    $user = User::factory()->create();
    $account = Account::withoutGlobalScopes()->where('user_id', $user->id)->firstOrFail();

    $this->get(route('accounts.create'))->assertRedirect(route('login'));
    $this->get(route('accounts.show', $account))->assertRedirect(route('login'));
    $this->get(route('accounts.edit', $account))->assertRedirect(route('login'));
});

it('creates an owned account with its opening balance as current balance', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'MTN MoMo')
        ->set('type', AccountType::MobileMoney->value)
        ->set('currency', 'FCFA')
        ->set('openingBalance', '-15000')
        ->set('color', '#2563EB')
        ->call('save')
        ->assertHasNoErrors();

    $account = Account::query()->where('name', 'MTN MoMo')->firstOrFail();
    expect($account->user_id)->toBe($user->id)
        ->and($account->opening_balance)->toBe(-15000.0)
        ->and($account->current_balance)->toBe(-15000.0)
        ->and($account->currency)->toBe('FCFA');
});

it('validates required create fields and valid account types', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', '')
        ->set('type', 'invalid')
        ->call('save')
        ->assertHasErrors(['name', 'type']);
});

it('atomically makes a created account default without changing another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $previous = Account::withoutGlobalScopes()->where('user_id', $user->id)->firstOrFail();
    $otherDefault = Account::withoutGlobalScopes()->where('user_id', $otherUser->id)->firstOrFail();
    $previous->update(['is_default' => true]);
    $otherDefault->update(['is_default' => true]);
    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('name', 'New Primary')
        ->set('type', 'bank')
        ->set('currency', 'FCFA')
        ->set('openingBalance', '0')
        ->set('isDefault', true)
        ->call('save');

    expect($previous->fresh()->is_default)->toBeFalse()
        ->and(Account::query()->where('name', 'New Primary')->first()->is_default)->toBeTrue()
        ->and($otherDefault->fresh()->is_default)->toBeTrue();
});

it('shows account details and only that accounts transactions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    clearManagedAccounts($user);
    $account = Account::factory()->for($user)->create(['name' => 'Main Wallet', 'current_balance' => 425000, 'currency' => 'FCFA']);
    $secondAccount = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['name' => 'Food']);
    Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id, 'title' => 'Visible Groceries']);
    Transaction::factory()->for($user)->create(['account_id' => $secondAccount->id, 'category_id' => $category->id, 'title' => 'Other Account Activity']);
    Transaction::factory()->for($otherUser)->create(['title' => 'Private Activity']);

    $this->actingAs($user)->get(route('accounts.show', $account))
        ->assertOk()->assertSee('Main Wallet')->assertSee('425,000')->assertSee('Visible Groceries')->assertSee('Food')
        ->assertDontSee('Other Account Activity')->assertDontSee('Private Activity');
});

it('paginates account transaction history newest first', function () {
    $user = User::factory()->create();
    clearManagedAccounts($user);
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();
    foreach (range(1, 11) as $day) {
        Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id, 'title' => "Activity {$day}", 'date' => now()->subDays(11 - $day)]);
    }

    $this->actingAs($user);
    Livewire::test(Show::class, ['account' => $account])
        ->assertSeeInOrder(['Activity 11', 'Activity 10'])
        ->assertDontSee('>Activity 1<', false);
});

it('prevents another user from viewing editing or setting an account as default', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $account = Account::withoutGlobalScopes()->where('user_id', $owner->id)->firstOrFail();

    $this->actingAs($otherUser)->get(route('accounts.show', $account))->assertNotFound();
    $this->actingAs($otherUser)->get(route('accounts.edit', $account))->assertNotFound();
    $this->actingAs($otherUser);
    Livewire::test(Show::class, ['account' => $account])->assertForbidden();
});

it('updates allowed fields without changing account balances', function () {
    $user = User::factory()->create();
    $account = Account::withoutGlobalScopes()->where('user_id', $user->id)->firstOrFail();
    $account->update(['opening_balance' => 1000, 'current_balance' => 7500]);
    $this->actingAs($user);

    Livewire::test(Edit::class, ['account' => $account])
        ->set('name', 'Renamed Account')
        ->set('type', AccountType::Savings->value)
        ->set('currency', 'FCFA')
        ->set('color', '#7C3AED')
        ->call('save')
        ->assertHasNoErrors();

    $account->refresh();
    expect($account->name)->toBe('Renamed Account')->and($account->type)->toBe(AccountType::Savings)
        ->and($account->opening_balance)->toBe(1000.0)->and($account->current_balance)->toBe(7500.0);
});

it('switches the default account without affecting another users default', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    clearManagedAccounts($user);
    $first = Account::factory()->for($user)->create(['is_default' => true]);
    $second = Account::factory()->for($user)->create(['is_default' => false]);
    $other = Account::withoutGlobalScopes()->where('user_id', $otherUser->id)->firstOrFail();
    $other->update(['is_default' => true]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $second])->call('setDefault');

    expect($first->fresh()->is_default)->toBeFalse()->and($second->fresh()->is_default)->toBeTrue()->and($other->fresh()->is_default)->toBeTrue();
});
