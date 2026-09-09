<?php

namespace App\Models;

use App\Concerns\HasPublicIdentifier;
use Database\Factories\GoalContributionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['public_id', 'goal_id', 'user_id', 'account_id', 'amount', 'contributed_at', 'note'])]
class GoalContribution extends Model
{
    /** @use HasFactory<GoalContributionFactory> */
    use HasFactory, HasPublicIdentifier;

    protected const PUBLIC_ID_PREFIX = 'gct';

    protected function casts(): array
    {
        return ['amount' => 'float', 'contributed_at' => 'date'];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
