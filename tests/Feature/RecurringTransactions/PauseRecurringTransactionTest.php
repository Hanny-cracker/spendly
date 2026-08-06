<?php

use App\Actions\RecurringTransactions\PauseRecurringTransaction;
use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);



it('can pause a recurring transaction', function(){


    $recurring = RecurringTransaction::factory()
        ->create([
            'status'=>RecurringStatus::Active
        ]);



    $paused = app(PauseRecurringTransaction::class)
        ->handle($recurring);



    expect($paused->fresh()->status)

        ->toBe(RecurringStatus::Paused);


});