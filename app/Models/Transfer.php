<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'from_account_id', 'to_account_id', 'amount', 'reference', 'description', 'date'])]
class Transfer extends Model
{

    protected $casts = [
        'amount' => 'float',
        'date' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function fromAccount()
    {
        return $this->belongsTo(
            Account::class,
            'from_account_id'
        );
    }


    public function toAccount()
    {
        return $this->belongsTo(
            Account::class,
            'to_account_id'
        );
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgoingTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)
            ->where('type', TransactionType::Expense);
    }

    public function incomingTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)
            ->where('type', TransactionType::Income);
    }
}
