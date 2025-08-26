<?php

namespace App\Filament\Admin\Resources\SubscriptionItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class SubscriptionItemsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('subscription_id')
                    ->relationship('subscription', 'type') // Display 'type' from Subscription model
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Subscription'),
                Forms\Components\TextInput::make('stripe_id')
                    ->required()
                    ->unique()
                    ->maxLength(255)
                    ->label('Stripe ID'),
                Forms\Components\TextInput::make('stripe_product')
                    ->required()
                    ->maxLength(255)
                    ->label('Stripe Product'),
                Forms\Components\TextInput::make('stripe_price')
                    ->required()
                    ->maxLength(255)
                    ->label('Stripe Price'),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->label('Quantity'),
            ]);
    }
}
