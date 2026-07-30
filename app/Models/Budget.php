<?php

namespace App\Models;

use \App\Concerns\HasPublicIdentifier;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Concerns\BelongsToUser;

#[Fillable(['public_id','user_id', 'category_id', 'amount', 'month', 'year',])]
class Budget extends Model
{
    use BelongsToUser; 
    use HasFactory;
    use HasPublicIdentifier;

    protected const PUBLIC_ID_PREFIX = 'bud';

    protected $casts = [
        'amount' => 'decimal:2',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
