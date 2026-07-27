<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

#[Fillable(['user_id', 'account_id', 'category_id', 'parent_transaction_id', 'title', 'description', 'amount', 'type', 'date', 'status', 'receipt_path', 'notes', 0])]

class Transaction extends Model
{

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'date' => 'date',
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



    // For recurring transactions
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'parent_transaction_id'
        );
    }
    /*
     * Scope: expenses only
     */
    public function scopeExpenses($query)
    {
        return $query->where(
            'type',
            TransactionType::Expense
        );
    }

    /*
     * Scope: income only
     */
    public function scopeIncome($query)
    {
        return $query->where(
            'type',
            TransactionType::Income
        );
    }
}
