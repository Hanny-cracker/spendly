<?php

use App\Livewire\Accounts\Show;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Livewire\Livewire;

function removeSafeDeleteAccounts(User $user): void
{
    Account::withoutGlobalScopes()->where('user_id', $user->id)->delete();
}

it('deletes an unused account after confirmation', function () {
    $user = User::factory()->create();
    removeSafeDeleteAccounts($user);
    $account = Account::factory()->for($user)->create(['opening_balance' => 50000, 'current_balance' => 50000]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $account])
        ->call('confirmDeletion')->assertSet('confirmingDeletion', true)
        ->call('delete')->assertRedirect(route('accounts'));

    expect(Account::withoutGlobalScopes()->find($account->id))->toBeNull();
});

it('blocks deletion when transaction history exists', function () {
    $user = User::factory()->create();
    removeSafeDeleteAccounts($user);
    $account = Account::factory()->for($user)->create(['current_balance' => 75000]);
    $category = Category::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $account])->call('confirmDeletion')->call('delete')
        ->assertSet('deletionError', "This account can't be deleted because it has transaction history.");

    expect($account->fresh())->not->toBeNull()->and($transaction->fresh())->not->toBeNull()->and($account->fresh()->current_balance)->toBe(75000.0);
});

it('blocks deletion when the account participates in a transfer', function () {
    $user = User::factory()->create();
    removeSafeDeleteAccounts($user);
    $source = Account::factory()->for($user)->create(['current_balance' => 75000]);
    $destination = Account::factory()->for($user)->create(['current_balance' => 25000]);
    $transfer = Transfer::create(['user_id' => $user->id, 'from_account_id' => $source->id, 'to_account_id' => $destination->id, 'amount' => 25000, 'reference' => 'safe-delete-transfer', 'date' => now()]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $source])->call('delete')
        ->assertSet('deletionError', "This account can't be deleted because it is part of a transfer.");

    expect($source->fresh())->not->toBeNull()->and($transfer->fresh())->not->toBeNull()->and($source->fresh()->current_balance)->toBe(75000.0)->and($destination->fresh()->current_balance)->toBe(25000.0);
});

it('blocks deletion when a recurring schedule uses the account', function () {
    $user = User::factory()->create();
    removeSafeDeleteAccounts($user);
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();
    $recurring = RecurringTransaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $account])->call('delete')
        ->assertSet('deletionError', "This account can't be deleted because it is used by a recurring transaction.");

    expect($account->fresh())->not->toBeNull()->and($recurring->fresh())->not->toBeNull();
});

it('reassigns a deleted default account deterministically', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    removeSafeDeleteAccounts($user);
    $default = Account::factory()->for($user)->create(['is_default' => true]);
    $replacement = Account::factory()->for($user)->create(['is_default' => false]);
    $otherDefault = Account::withoutGlobalScopes()->where('user_id', $otherUser->id)->firstOrFail();
    $otherDefault->update(['is_default' => true]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $default])->call('delete');

    expect($default->fresh())->toBeNull()->and($replacement->fresh()->is_default)->toBeTrue()->and($otherDefault->fresh()->is_default)->toBeTrue();
});

it('allows deleting the last safe account', function () {
    $user = User::factory()->create();
    removeSafeDeleteAccounts($user);
    $account = Account::factory()->for($user)->create(['is_default' => true]);
    $this->actingAs($user);

    Livewire::test(Show::class, ['account' => $account])->call('delete');

    expect(Account::withoutGlobalScopes()->where('user_id', $user->id)->count())->toBe(0);
});

it('reauthorizes malicious delete calls', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $account = Account::withoutGlobalScopes()->where('user_id', $owner->id)->firstOrFail();
    $this->actingAs($otherUser);

    Livewire::test(Show::class, ['account' => $account])->assertForbidden();

    expect($account->fresh())->not->toBeNull();
});
