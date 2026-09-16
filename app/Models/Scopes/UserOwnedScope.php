<?php

namespace App\Models\Scopes;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserOwnedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('filament') && Filament::getCurrentPanel()?->getId() === 'admin') {
            return;
        }

        if (Auth::check()) {
            $builder->where(
                $model->getTable().'.user_id',
                Auth::id()
            );
        }
    }
}
