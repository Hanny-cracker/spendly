<?php

namespace App\Concerns;

use Illuminate\Support\Str;
use LogicException;

trait HasPublicIdentifier
{
    /**
     * @mixin \Illuminate\Database\Eloquent\Model
     */
    protected static function bootHasPublicIdentifier(): void
    {
        static::creating(function ($model) {
            if (! empty($model->public_id)) {
                return;
            }

            if (! defined(static::class . '::PUBLIC_ID_PREFIX')) {
                throw new LogicException(
                    sprintf(
                        'Model [%s] must define a PUBLIC_ID_PREFIX constant.',
                        static::class
                    )
                );
            }

            $model->public_id = static::PUBLIC_ID_PREFIX . '_' . Str::ulid();
        });
    }

    /**
     * Use public_id for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
