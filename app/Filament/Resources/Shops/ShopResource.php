<?php

namespace App\Filament\Resources\Shops;

use App\Filament\Resources\Shops\Pages\CreateShop;
use App\Filament\Resources\Shops\Pages\EditShop;
use App\Filament\Resources\Shops\Pages\ListShops;
use App\Filament\Resources\Shops\Schemas\ShopForm;
use App\Filament\Resources\Shops\Tables\ShopsTable;
use App\Models\Shop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Shop';

    public static function form(Schema $schema): Schema
    {
        return ShopForm::configure($schema);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Iniciando mutateFormDataBeforeCreate en ShopResource', [
            'data' => $data,
            'context' => 'ShopResource::mutateFormDataBeforeCreate'
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::error('No hay usuario autenticado al intentar crear una tienda', [
                'data' => $data,
                'context' => 'ShopResource::mutateFormDataBeforeCreate'
            ]);
            throw new \Exception('No hay usuario autenticado. Por favor, inicia sesión.');
        }

        $lastSubscription = $user->subscriptions()->where('stripe_status', 'active')->latest()->first(['id', 'product_id']);

        $data['user_id'] = $user->id;
        $data['subscription_id'] = $lastSubscription?->id;
        $data['product_id'] = $lastSubscription?->product_id; // Ajusta según el campo correcto

        Log::info('Datos preparados para crear la tienda:', [
            'data' => $data,
            'user_id' => $user->id,
            'context' => 'ShopResource::mutateFormDataBeforeCreate'
        ]);

        return $data;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->where('user_id', $user->id);
    }

    public static function table(Table $table): Table
    {
        return ShopsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShops::route('/'),
            'create' => CreateShop::route('/create'),
            'edit' => EditShop::route('/{record}/edit'),
        ];
    }
}
