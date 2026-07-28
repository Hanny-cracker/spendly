<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;


#[Fillable(['user_id', 'name', 'type', 'icon', 'color', 'slug'])]
class Category extends Model
{

    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
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


    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
