<?php

namespace App\Models;

use App\Concerns\BelongsToUser;
use App\Concerns\HasPublicIdentifier;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['public_id', 'user_id', 'name', 'type', 'currency', 'opening_balance', 'current_balance', 'color', 'is_default'])]
class Account extends Model
{
    use BelongsToUser;
    use HasFactory;
    use HasPublicIdentifier;

    protected const PUBLIC_ID_PREFIX = 'acc';

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'opening_balance' => 'float',
            'current_balance' => 'float',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(
            Transfer::class,
            'from_account_id'
        );
    }

    public function incomingTransfers()
    {
        return $this->hasMany(
            Transfer::class,
            'to_account_id'
        );
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(
            RecurringTransaction::class
        );
    }

    public function goalContributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }
}
