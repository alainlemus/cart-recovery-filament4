<?php

namespace App\Filament\Resources\AbandonedCheckouts;

use App\Filament\Resources\AbandonedCheckouts\Pages\CreateAbandonedCheckout;
use App\Filament\Resources\AbandonedCheckouts\Pages\EditAbandonedCheckout;
use App\Filament\Resources\AbandonedCheckouts\Pages\ListAbandonedCheckouts;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Abandoned Checkout ';

    public static function form(Schema $schema): Schema
    {
        return AbandonedCheckoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbandonedCheckoutsTable::configure($table);
    }

    public static function getTableQuery()
    {
        $user = Auth::user();

        // Buscar la tienda asociada al usuario
        $shop = Shop::where('user_id', $user->id)->first();

        if (!$shop) {
            return collect(); // sin tienda
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->access_token,
        ])->get("https://{$shop->shopify_domain}/admin/api/2025-07/checkouts.json");

        Log::info('Respuesta checkouts abandonados', [
            'body' => $response->body(),
            'status' => $response->status(),
        ]);

        if ($response->failed()) {
            return collect();
        }

        $checkouts = $response->json('checkouts') ?? [];

        // Transformar para mostrar en tabla
        return collect($checkouts)->map(function ($checkout) {
            return [
                'email' => $checkout['email'] ?? 'Desconocido',
                'line_items' => collect($checkout['line_items'])->pluck('title')->join(', '),
                'total_price' => $checkout['total_price'] ?? '0.00',
                'created_at' => $checkout['created_at'] ?? null,
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
