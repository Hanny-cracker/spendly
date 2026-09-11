<?php

use App\Actions\RecurringTransactions\DeleteRecurringTransaction;
use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can delete paused recurring transaction', function () {

    $recurring = RecurringTransaction::factory()
        ->create([
            'status' => RecurringStatus::Paused,
        ]);

    app(DeleteRecurringTransaction::class)
        ->handle($recurring);

    expect(
        RecurringTransaction::find($recurring->id)
    )
        ->toBeNull();

});
