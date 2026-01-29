<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Coupon Code')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('title')
                    ->label('Title'),

                TextInput::make('value')
                    ->label('Value')
                    ->numeric()
                    ->required(),

                Select::make('value_type')
                    ->label('Value Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                    ])
                    ->required(),

                Select::make('shop_id')
                    ->label('Shop')
                    ->relationship('shop', 'name')
                    ->required(),

                DateTimePicker::make('starts_at')
                    ->label('Starts At'),

                DateTimePicker::make('ends_at')
                    ->label('Ends At'),
            ]);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected static function mutateFormDataBeforeUpdate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
