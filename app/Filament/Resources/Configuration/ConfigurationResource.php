<?php

namespace App\Filament\Resources\Configuration;

use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Configuration\Pages;
use App\Filament\Resources\Configuration\Pages\ViewConfiguration;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class ConfigurationResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog;
    protected static ?string $navigationLabel = 'Configuration';
    protected static ?string $model = null;

    public static function getPages(): array
    {
        return [
            'index' => ViewConfiguration::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Account';
    }
}
