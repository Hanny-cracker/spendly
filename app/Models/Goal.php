<?php

namespace App\Models;

use App\Concerns\BelongsToUser;
use App\Concerns\HasPublicIdentifier;
use App\Enums\GoalStatus;
use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['public_id', 'user_id', 'name', 'description', 'target_amount', 'current_amount', 'target_date', 'status'])]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use BelongsToUser, HasFactory, HasPublicIdentifier;

    protected const PUBLIC_ID_PREFIX = 'gol';

    protected function casts(): array
    {
        return ['target_amount' => 'float', 'current_amount' => 'float', 'target_date' => 'date', 'status' => GoalStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }
}
