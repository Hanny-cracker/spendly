<?php

use App\Actions\Transfers\CreateTransfer;
use App\Actions\Transfers\UpdateTransfer;
use App\Data\Transfer\CreateTransferData;
use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates both transfer transactions and applies the balance difference once', function () {
    $user = User::factory()->create();
    $source = Account::factory()->for($user)->create(['current_balance' => 1000]);
    $destination = Account::factory()->for($user)->create(['current_balance' => 200]);

    $transfer = app(CreateTransfer::class)->handle(new CreateTransferData(
        userId: $user->id,
        fromAccountId: $source->id,
        toAccountId: $destination->id,
        amount: 100,
        description: 'Initial transfer',
        date: Carbon::parse('2026-09-09'),
    ));

    app(UpdateTransfer::class)->handle($transfer, new CreateTransferData(
        userId: $user->id,
        fromAccountId: $source->id,
        toAccountId: $destination->id,
        amount: 250,
        description: 'Updated transfer',
        date: Carbon::parse('2026-09-10'),
    ));

    expect((float) $source->fresh()->current_balance)->toBe(750.0)
        ->and((float) $destination->fresh()->current_balance)->toBe(450.0)
        ->and($transfer->transactions()->where('amount', 250)->count())->toBe(2)
        ->and($transfer->transactions()->count())->toBe(2);
});

it('rejects a transfer update that supplies another users accounts without changing balances', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $source = Account::factory()->for($owner)->create(['current_balance' => 1000]);
    $destination = Account::factory()->for($owner)->create(['current_balance' => 200]);
    $foreignSource = Account::factory()->for($otherUser)->create(['current_balance' => 900]);
    $foreignDestination = Account::factory()->for($otherUser)->create(['current_balance' => 100]);
    $transfer = app(CreateTransfer::class)->handle(new CreateTransferData(
        $owner->id,
        $source->id,
        $destination->id,
        100,
        null,
        Carbon::parse('2026-09-09'),
    ));

    expect(fn () => app(UpdateTransfer::class)->handle($transfer, new CreateTransferData(
        $owner->id,
        $foreignSource->id,
        $foreignDestination->id,
        200,
        null,
        Carbon::parse('2026-09-10'),
    )))->toThrow(ValidationException::class);

    expect((float) $source->fresh()->current_balance)->toBe(900.0)
        ->and((float) $destination->fresh()->current_balance)->toBe(300.0)
        ->and((float) $foreignSource->fresh()->current_balance)->toBe(900.0)
        ->and((float) $foreignDestination->fresh()->current_balance)->toBe(100.0);
});

it('rejects updating an incomplete transfer without applying a second balance change', function () {
    $user = User::factory()->create();
    $source = Account::factory()->for($user)->create(['current_balance' => 1000]);
    $destination = Account::factory()->for($user)->create(['current_balance' => 200]);
    $transfer = app(CreateTransfer::class)->handle(new CreateTransferData(
        $user->id,
        $source->id,
        $destination->id,
        100,
        null,
        Carbon::parse('2026-09-09'),
    ));
    $transfer->incomingTransaction()->delete();

    expect(fn () => app(UpdateTransfer::class)->handle($transfer, new CreateTransferData(
        $user->id,
        $source->id,
        $destination->id,
        200,
        null,
        Carbon::parse('2026-09-10'),
    )))->toThrow(ValidationException::class, 'This transfer is incomplete and cannot be updated.');

    expect((float) $source->fresh()->current_balance)->toBe(900.0)
        ->and((float) $destination->fresh()->current_balance)->toBe(300.0)
        ->and((float) $transfer->fresh()->amount)->toBe(100.0);
});
