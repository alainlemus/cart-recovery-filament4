<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ShopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Shop Name')
                    ->required(),
                TextInput::make('shopify_domain')
                    ->required(),
                TextInput::make('shopify_api_key')
                    ->required(),
                TextInput::make('shopify_api_secret')
                    ->required()
                    ->password()
                    ->revealable(),
            ]);
    }
}
