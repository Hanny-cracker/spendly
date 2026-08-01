<?php

use App\Models\User;
use App\Models\RecurringTransaction;
use App\Actions\RecurringTransactions\DeleteRecurringTransaction;


it('can delete recurring transaction',function(){

    $user=User::factory()->create();


    $recurring=RecurringTransaction::factory()
        ->for($user)
        ->create();


    app(DeleteRecurringTransaction::class)
        ->handle($recurring);


    expect(
        RecurringTransaction::find($recurring->id)
    )
    ->toBeNull();

});