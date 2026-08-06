<?php

use App\Actions\RecurringTransactions\UpdateRecurringTransaction;
use App\Data\RecurringTransaction\UpdateRecurringTransactionData;
use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);



it('can update a recurring transaction', function(){


    $recurring = RecurringTransaction::factory()
        ->create();



    $data = new UpdateRecurringTransactionData(

        title:'Updated Netflix',

        description:'Updated',

        amount:7000,

        type:TransactionType::Expense,

        frequency:RecurringFrequency::Monthly,

        interval:1,

        endDate:null,

        status:RecurringStatus::Active,

    );



    $updated = app(UpdateRecurringTransaction::class)
        ->handle($recurring,$data);



    expect($updated->fresh())

        ->title
        ->toBe('Updated Netflix')

        ->amount
        ->toBe(7000.0);


});