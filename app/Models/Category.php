<?php

namespace App\Models;


use App\Concerns\HasPublicIdentifier;
use App\Enums\CategoryType;
use App\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[Fillable(['public_id','user_id', 'name', 'type', 'icon', 'color'])]
class Category extends Model
{
    use BelongsToUser;
    use HasFactory;
    use HasPublicIdentifier;
    protected const PUBLIC_ID_PREFIX = 'cat';

    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
        ];
    }

    public static function publicIdPrefix(): string
    {
        return 'cat';
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
