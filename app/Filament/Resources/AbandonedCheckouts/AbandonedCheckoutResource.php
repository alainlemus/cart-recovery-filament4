<?php

namespace App\Filament\Resources\AbandonedCheckouts;

use App\Filament\Resources\AbandonedCheckouts\Schemas\AbandonedCheckoutForm;
use App\Filament\Resources\AbandonedCheckouts\Tables\AbandonedCheckoutsTable;
use App\Models\Cart;
use App\Models\Shop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AbandonedCheckoutResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingCart;

    protected static ?string $recordTitleAttribute = 'Abandoned Checkout ';

    public static function form(Schema $schema): Schema
    {
        return AbandonedCheckoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbandonedCheckoutsTable::configure($table);
    }

    // Método estático para obtener los checkouts de Shopify
    public static function fetchAbandonedCheckouts(Shop $shop)
    {
        $user = Auth::user();

        Log::info('Obteniendo checkouts abandonados para el usuario', [
            'user_id' => $user ? $user->id : null,
            'context' => 'AbandonedCheckoutResource::fetchAbandonedCheckouts'
        ]);

        if (!$shop) {
            Log::warning('No se encontró tienda para el usuario', ['user_id' => $user->id]);
            return collect();
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->access_token,
        ])->get("https://{$shop->shopify_domain}/admin/api/2025-07/checkouts.json", [
            'limit' => 10, // Ajusta el límite según necesites
        ]);

        Log::info('Respuesta checkouts abandonados', [
            'body' => $response->body(),
            'status' => $response->status(),
        ]);

        if ($response->failed()) {
            Log::error('Error al obtener checkouts', ['error' => $response->json()]);
            return collect();
        }

        $checkouts = $response->json('checkouts') ?? [];

        Log::info('Checkouts obtenidos', ['productos' => $checkouts]);

        return collect($checkouts)->map(function ($checkout) {
            return [
                'id' => $checkout['id'],
                'shopify_id' => $checkout['id'],
                'email' => $checkout['email'],
                'id_cart' => $checkout['id'],
                'response' => $checkout,
                'total_price' => $checkout['total_price'] ?? '0.00',
                'created_at' => $checkout['created_at'] ?? null,
                'abandoned_at' => $checkout['created_at'],
                'abandoned_checkout_url' => $checkout['abandoned_checkout_url'] ?? null,
            ];
        });
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
            'index' => Pages\ListAbandonedCheckouts::route('/'),
            /*'index' => ListAbandonedCheckouts::route('/'),
            'create' => CreateAbandonedCheckout::route('/create'),
            'edit' => EditAbandonedCheckout::route('/{record}/edit'),*/
        ];
    }
}
