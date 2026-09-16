<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')->relationship('user', 'email')->searchable()->required(),
                Select::make('plan')->options(collect(SubscriptionPlan::cases())->mapWithKeys(fn (SubscriptionPlan $plan): array => [$plan->value => ucfirst($plan->value)])->all())->required(),
                Select::make('status')->options(collect(SubscriptionStatus::cases())->mapWithKeys(fn (SubscriptionStatus $status): array => [$status->value => ucfirst($status->value)])->all())->required(),
                DateTimePicker::make('starts_at')->required(),
                DateTimePicker::make('expires_at'),
                TextInput::make('amount')->numeric(),
                TextInput::make('payment_reference'),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }
}
