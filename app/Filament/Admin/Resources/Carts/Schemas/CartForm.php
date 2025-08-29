<?php

namespace App\Filament\Admin\Resources\Carts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('shop_id')
                    ->required()
                    ->numeric(),
                TextInput::make('items'),
                TextInput::make('total_price')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                DateTimePicker::make('abandoned_at'),
                TextInput::make('email_client')
                    ->email(),
                TextInput::make('phone_client')
                    ->tel(),
            ]);
    }
}
