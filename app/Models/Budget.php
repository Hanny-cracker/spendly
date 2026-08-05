<?php

namespace App\Models;

use \App\Concerns\HasPublicIdentifier;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\BelongsToUser;
use App\Concerns\BudgetPeriod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable([
    'public_id',
    'user_id',
    'category_id',
    'name',
    'amount',
    'period',
    'start_date',
    'end_date',
    'alert_percentage',
    'is_active',
])]
class Budget extends Model
{
    use BelongsToUser;
    use HasFactory;
    use HasPublicIdentifier;
    // use HasUuids;
    protected const PUBLIC_ID_PREFIX = 'bud';

    public $incrementing = false;

    /**
     * UUID key type.
     */
    // protected $keyType = 'string';

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'period' => BudgetPeriod::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'alert_percentage' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    //  Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

//  Query Scopes

    /**
     * Only active budgets.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Budgets currently in effect.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today());
    }

    /**
     * Budgets for a given user.
     */
    public function scopeForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('user_id', $userId);
    }

    /**
     * Budgets for a category.
     */
    public function scopeForCategory(
        Builder $query,
        int $categoryId
    ): Builder {
        return $query->where('category_id', $categoryId);
    }
}
