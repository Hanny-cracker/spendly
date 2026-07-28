<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;
use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;

#[Fillable([ 'user_id','account_id','category_id', 'title','description','amount','type','frequency',
            'interval','start_date','next_run','end_date','status','last_generated_at',])]
class RecurringTransaction extends Model
{
protected function casts(): array
{
    return [
        'type' => TransactionType::class,
        'frequency' => RecurringFrequency::class,
        'status' => RecurringStatus::class,
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'next_run' => 'date',
        'end_date' => 'date',
        'last_generated_at' => 'datetime',
    ];
}



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }



    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}