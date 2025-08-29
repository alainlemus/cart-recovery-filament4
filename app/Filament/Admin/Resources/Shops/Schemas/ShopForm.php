<?php

namespace App\Filament\Admin\Resources\Shops\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('domain')
                    ->required(),
                TextInput::make('shopify_api_key')
                    ->required(),
                TextInput::make('shopify_api_secret')
                    ->required(),
            ]);
    }
}
