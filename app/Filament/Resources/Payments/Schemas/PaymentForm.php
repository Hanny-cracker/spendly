<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('public_id')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('subscription_plan')
                    ->options(SubscriptionPlan::class)
                    ->required(),
                Select::make('provider')
                    ->options(PaymentProvider::class)
                    ->required(),
                Select::make('payment_method')
                    ->options(PaymentMethod::class)
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('XAF'),
                Select::make('status')
                    ->options(PaymentStatus::class)
                    ->required(),
                TextInput::make('provider_reference'),
                TextInput::make('external_reference')
                    ->required(),
                TextInput::make('phone_number')
                    ->tel(),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('failed_at'),
                DateTimePicker::make('expires_at'),
                Textarea::make('failure_reason')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
