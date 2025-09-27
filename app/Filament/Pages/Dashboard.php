<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        $user = Auth::user();
        $shops = $user->shops->pluck('name', 'id')->toArray();
        $options = array_merge(['all' => 'Todas las tiendas'], $shops);

        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('shop_id')
                            ->label('Shop')
                            ->options($options)
                            ->default('all')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

}
