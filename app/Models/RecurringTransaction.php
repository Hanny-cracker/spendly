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
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    'scheduled_time',
    'next_run',
    'end_date',
    'status',
    'last_generated_at',
    'last_24h_notified_at',
    'last_6h_notified_at',
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
            'amount' => 'float',
            'start_date' => 'date',
            'next_run' => 'datetime',
            'end_date' => 'date',
            'last_generated_at' => 'datetime',
            'type' => TransactionType::class,
            'frequency' => RecurringFrequency::class,
            'status' => RecurringStatus::class,
            'last_24h_notified_at' => 'datetime',
            'last_6h_notified_at' => 'datetime',
        ];
    }


    public function user(): BelongsTo
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


    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === RecurringStatus::Active;
    }

    public function shouldGenerate(): bool
    {
        return $this->status === RecurringStatus::Active
            && $this->next_run->lte(now())
            && (! $this->end_date || $this->next_run->startOfDay()->lte($this->end_date));
    }

    // public function isPaused(): bool
    // {
    //     return $this->status->isPaused();
    // }

    // public function hasEnded(): bool
    // {
    //     return $this->end_date?->isPast() ?? false;
    // }

    // public function isDue(): bool
    // {
    //     return $this->next_run->isPast()
    //         || $this->next_run->isNow();
    // }
    // public function isCompleted(): bool
    // {
    //     return $this->status === RecurringStatus::Completed;
    // }
    // public function shouldGenerate(): bool
    // {
    //     return $this->isActive()
    //         && ! $this->hasEnded()
    //         && $this->isDue();
    // }
}
