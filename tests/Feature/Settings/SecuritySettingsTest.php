<?php

use App\Actions\Users\DeleteUserAccount;
use App\Enums\CategoryType;
use App\Enums\RecurringTransactionNotificationType;
use App\Livewire\Settings\Index;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\RecurringTransactionNotification;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('deletes a user through the account erasure action', function () {
    $user = User::factory()->create();
    app(DeleteUserAccount::class)->handle($user);

    expect(User::query()->find($user->id))->toBeNull()
        ->and(DB::table('accounts')->where('user_id', $user->id)->count())->toBe(0);
});

it('updates only the authenticated users password with the current password', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $otherPassword = $other->password;

    Livewire::actingAs($user)->test(Index::class)
        ->set('section', 'security')
        ->set('currentPassword', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertSet('currentPassword', '')
        ->assertSet('password', '')
        ->assertSee('Password updated successfully.');

    expect(Hash::check('password', $user->refresh()->password))->toBeFalse()
        ->and(Hash::check('new-password', $user->password))->toBeTrue()
        ->and($other->refresh()->password)->toBe($otherPassword);
});

it('rejects an incorrect current password and mismatched confirmation', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->set('currentPassword', 'incorrect')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['currentPassword']);

    Livewire::actingAs($user)->test(Index::class)
        ->set('currentPassword', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'different-password')
        ->call('updatePassword')
        ->assertHasErrors(['password']);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

it('requires the current password and DELETE phrase without removing data', function () {
    $user = User::factory()->create();
    $accountId = $user->accounts()->valueOrFail('id');

    Livewire::actingAs($user)->test(Index::class)
        ->set('deletePassword', 'incorrect')
        ->set('deleteConfirmation', 'DELETE')
        ->call('deleteAccount')
        ->assertHasErrors(['deletePassword']);

    Livewire::actingAs($user)->test(Index::class)
        ->set('deletePassword', 'password')
        ->set('deleteConfirmation', 'delete')
        ->call('deleteAccount')
        ->assertHasErrors(['deleteConfirmation']);

    expect($user->fresh())->not->toBeNull()
        ->and(DB::table('accounts')->where('id', $accountId)->exists())->toBeTrue();
});

it('erases the complete owned dataset and preserves another users data', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    foreach ([$user, $other] as $owner) {
        if ($owner->accounts()->count() < 2) {
            Account::factory()->for($owner)->create();
        }
        $accounts = $owner->accounts()->limit(2)->get();
        $expenseCategory = $owner->categories()->where('type', CategoryType::Expense)->firstOrFail();
        $transaction = Transaction::factory()->for($owner)->for($accounts->first())->for($expenseCategory)->expense()->create();
        Budget::factory()->for($owner)->for($expenseCategory)->create();
        $goal = Goal::factory()->for($owner)->create();
        GoalContribution::factory()->for($owner)->for($goal)->create(['account_id' => $accounts->first()->id]);
        RecurringTransaction::factory()->for($owner)->create(['account_id' => $accounts->first()->id, 'category_id' => $expenseCategory->id]);
        UserPreference::factory()->for($owner)->create();

        if ($accounts->count() === 2) {
            $transfer = Transfer::query()->create(['user_id' => $owner->id, 'from_account_id' => $accounts[0]->id, 'to_account_id' => $accounts[1]->id, 'amount' => 10, 'reference' => Str::uuid()->toString(), 'date' => today()]);
            $transaction->update(['transfer_id' => $transfer->id]);
        }

        DB::table('notifications')->insert(['id' => Str::uuid(), 'type' => 'test', 'notifiable_type' => $owner->getMorphClass(), 'notifiable_id' => $owner->id, 'data' => '{}', 'created_at' => now(), 'updated_at' => now()]);
    }

    Livewire::actingAs($user)->test(Index::class)
        ->set('deletePassword', 'password')
        ->set('deleteConfirmation', 'DELETE')
        ->call('deleteAccount')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $ownedTables = ['accounts', 'categories', 'transactions', 'transfers', 'budgets', 'goals', 'goal_contributions', 'recurring_transactions', 'user_preferences'];
    foreach ($ownedTables as $table) {
        expect(DB::table($table)->where('user_id', $user->id)->count())->toBe(0, $table)
            ->and(DB::table($table)->where('user_id', $other->id)->count())->toBeGreaterThan(0);
    }

    expect($user->fresh())->toBeNull()
        ->and($other->fresh())->not->toBeNull()
        ->and(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(0)
        ->and(DB::table('notifications')->where('notifiable_id', $other->id)->count())->toBe(1);
    $this->assertGuest();
});

it('renders account menus and logout preserves financial data', function () {
    $user = User::factory()->create(['name' => 'Menu Person', 'email' => 'menu@example.com']);
    $account = $user->accounts()->firstOrFail();
    $category = $user->categories()->where('type', CategoryType::Expense)->firstOrFail();
    Transaction::factory()->for($user)->for($account)->for($category)->expense()->create();
    Budget::factory()->for($user)->for($category)->create();
    $goal = Goal::factory()->for($user)->create();
    GoalContribution::factory()->for($user)->for($goal)->create(['account_id' => $account->id]);
    RecurringTransaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id]);
    UserPreference::factory()->for($user)->create();
    DB::table('notifications')->insert(['id' => Str::uuid(), 'type' => 'test', 'notifiable_type' => $user->getMorphClass(), 'notifiable_id' => $user->id, 'data' => '{}', 'created_at' => now(), 'updated_at' => now()]);

    $tables = ['accounts', 'categories', 'transactions', 'budgets', 'goals', 'goal_contributions', 'recurring_transactions', 'user_preferences'];
    $countsBeforeLogout = collect($tables)->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->where('user_id', $user->id)->count()]);
    $notificationsBeforeLogout = DB::table('notifications')->where('notifiable_id', $user->id)->count();

    $response = $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('Menu Person')
        ->assertSee('Settings')
        ->assertSee('Log out');

    expect(substr_count($response->getContent(), route('logout')))->toBeGreaterThanOrEqual(2);

    $this->post(route('logout'))->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->not->toBeNull();
    foreach ($countsBeforeLogout as $table => $count) {
        expect(DB::table($table)->where('user_id', $user->id)->count())->toBe($count, $table);
    }
    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe($notificationsBeforeLogout);
});

it('does not render an account menu for a guest', function () {
    expect(trim(Blade::render('<x-account-menu />')))->toBe('');
});

it('removes a deleted users schedules before later background processing', function () {
    Queue::fake();
    $user = User::factory()->create();
    $other = User::factory()->create();

    $userAccount = $user->accounts()->firstOrFail();
    $userCategory = $user->categories()->where('type', CategoryType::Expense)->firstOrFail();
    $deletedSchedule = RecurringTransaction::factory()->for($user)->create([
        'account_id' => $userAccount->id,
        'category_id' => $userCategory->id,
        'next_run' => now()->subMinute(),
    ]);

    $otherAccount = $other->accounts()->firstOrFail();
    $otherAccount->update(['current_balance' => 100000]);
    $otherCategory = $other->categories()->where('type', CategoryType::Expense)->firstOrFail();
    $workingSchedule = RecurringTransaction::factory()->for($other)->create([
        'account_id' => $otherAccount->id,
        'category_id' => $otherCategory->id,
        'next_run' => now()->subMinute(),
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('deletePassword', 'password')
        ->set('deleteConfirmation', 'DELETE')
        ->call('deleteAccount')
        ->assertHasNoErrors();

    expect(RecurringTransaction::query()->withoutGlobalScopes()->find($deletedSchedule->id))->toBeNull();

    $this->artisan('transactions:generate-recurring')->assertSuccessful();

    expect(Transaction::query()->withoutGlobalScopes()->where('user_id', $user->id)->count())->toBe(0)
        ->and(Transaction::query()->withoutGlobalScopes()->where('recurring_transaction_id', $workingSchedule->id)->count())->toBe(1);

    Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($other, $workingSchedule): bool {
        return $job->notifiables->contains(fn (User $notifiable): bool => $notifiable->is($other))
            && $job->notification instanceof RecurringTransactionNotification
            && $job->notification->recurringTransaction->is($workingSchedule);
    });
    Queue::assertNotPushed(SendQueuedNotifications::class, fn (SendQueuedNotifications $job): bool => $job->notifiables->contains(fn (User $notifiable): bool => $notifiable->id === $user->id));
});

it('silently discards an already queued recurring notification when its models are deleted', function () {
    $user = User::factory()->create();
    $schedule = RecurringTransaction::factory()->for($user)->create();
    $notification = new RecurringTransactionNotification(
        recurringTransaction: $schedule,
        type: RecurringTransactionNotificationType::Generated,
        scheduledFor: now(),
    );

    $queuedNotification = new SendQueuedNotifications($user, $notification);

    expect($queuedNotification->deleteWhenMissingModels)->toBeTrue();
});

it('does not render authenticated account menus for guests', function () {
    expect(trim(Blade::render('<x-account-menu />')))->toBe('');
});
