<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'from_account_id', 'to_account_id', 'amount', 'reference', 'description', 'date'])]
class Transfer extends Model
{
    protected $casts = [
        'amount' => 'float',
        'date' => 'date',
    ];

    /** @return BelongsTo<User, self> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Account, self> */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'from_account_id'
        );
    }

    /** @return BelongsTo<Account, self> */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            'to_account_id'
        );
    }

    /** @return HasMany<Transaction, self> */
    public function transactions(): HasMany
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
