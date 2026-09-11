<?php

namespace App\Models;

use Database\Factories\UserPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property string $timezone
 * @property string $date_format
 * @property string $week_starts_on
 * @property int|null $default_expense_category_id
 * @property int|null $default_income_category_id
 * @property bool $notify_recurring_24h
 * @property bool $notify_recurring_6h
 * @property bool $notify_recurring_success
 * @property bool $notify_recurring_failure
 */
#[Fillable(['user_id', 'currency', 'timezone', 'date_format', 'week_starts_on', 'default_expense_category_id', 'default_income_category_id', 'notify_recurring_24h', 'notify_recurring_6h', 'notify_recurring_success', 'notify_recurring_failure'])]
class UserPreference extends Model
{
    /** @use HasFactory<UserPreferenceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'notify_recurring_24h' => 'boolean',
            'notify_recurring_6h' => 'boolean',
            'notify_recurring_success' => 'boolean',
            'notify_recurring_failure' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function defaultExpenseCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'default_expense_category_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function defaultIncomeCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'default_income_category_id');
    }
}
