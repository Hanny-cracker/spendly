<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\BelongsToUser;
use App\Enums\TransactionType;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use \App\Concerns\HasPublicIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'public_id',
    'user_id',
    'account_id',
    'category_id',
    'title',
    'description',
    'amount',
    'type',
    'frequency',
    'interval',
    'start_date',
    'next_run',
    'end_date',
    'status',
    'last_generated_at',
])]
class RecurringTransaction extends Model
{
    use BelongsToUser;
    use HasFactory;
    use HasPublicIdentifier;

    protected const PUBLIC_ID_PREFIX = 'rec';
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'start_date' => 'date',
            'next_run' => 'date',
            'end_date' => 'date',
            'last_generated_at' => 'datetime',
            'type' => TransactionType::class,
            'frequency' => RecurringFrequency::class,
            'status' => RecurringStatus::class,
        ];
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function account()
    {
        return $this->belongsTo(Account::class);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isPaused(): bool
    {
        return $this->status->isPaused();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at?->isPast() ?? false;
    }

    public function isDue(): bool
    {
        return $this->next_run_at->isPast()
            || $this->next_run_at->isNow();
    }
    public function isCompleted(): bool
    {
        return $this->status === RecurringStatus::Completed;
    }
    public function shouldGenerate(): bool
    {
        return $this->isActive()
            && ! $this->hasEnded()
            && $this->isDue();
    }
}
