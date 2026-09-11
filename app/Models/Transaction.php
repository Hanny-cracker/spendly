<?php

namespace App\Models;

use App\Concerns\BelongsToUser;
use App\Concerns\HasPublicIdentifier;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property-read Category|null $category */
#[Fillable(['public_id', 'user_id', 'account_id', 'category_id', 'transfer_id', 'recurring_transaction_id', 'scheduled_for', 'title', 'description', 'amount', 'type', 'date', 'status', 'receipt_path', 'notes'])]
class Transaction extends Model
{
    use BelongsToUser;
    use HasFactory;
    use HasPublicIdentifier;

    protected const string PUBLIC_ID_PREFIX = 'txn';

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'float',
            'date' => 'date',
            'scheduled_for' => 'datetime',
        ];
    }

    public static function publicIdPrefix(): string
    {
        return self::PUBLIC_ID_PREFIX;
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

    public function transfer()
    {
        return $this->belongsTo(
            Transfer::class
        );
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(
            RecurringTransaction::class
        );
    }
    // public function parentTransaction(): BelongsTo
    // {
    //     return $this->belongsTo(
    //         Transaction::class,
    //         'parent_transaction_id'
    //     );
    // }

    public function scopeExpenses(Builder $query): Builder
    {
        return $query->where(
            'type',
            TransactionType::Expense
        );
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where(
            'type',
            TransactionType::Income
        );
    }

    public function getDisplayTitleAttribute(): string
    {
        if (! $this->transfer) {
            return $this->title;
        }

        if ($this->type->isExpense()) {

            return 'Transfer to '.
                $this->transfer
                    ->toAccount
                    ->name;
        }

        return 'Transfer from '.
            $this->transfer
                ->fromAccount
                ->name;
    }
}
