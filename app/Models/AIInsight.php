<?php

namespace App\Models;

use App\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'title', 'summary', 'health_score', 'data', 'period_start', 'period_end', 'generated_at'])]
class AIInsight extends Model
{
    use BelongsToUser;

    protected $table = 'ai_insights';

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'health_score' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
