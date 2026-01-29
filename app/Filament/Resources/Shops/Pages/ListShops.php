<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ListShops extends ListRecords
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->disabled(function () {
                    return ! $this->canCreate();
                })
                ->tooltip(function () {
                    $user = Auth::user();
                    if (! $user) {
                        return 'No estás autenticado.';
                    }

                    $subscription = $user->subscriptions()->where('stripe_status', 'active')->latest()->first();
                    if (! $subscription) {
                        return 'You need an active subscription to create a store.';
                    }

                    $shopCount = $user->shops()->count();
                    $product = Product::where('id', $subscription->product_id)->first();

                    if (! $product) {
                        return 'Product not found for your subscription.';
                    }

                    if ($product->name === 'Basic Plan' && $shopCount >= 1) {
                        return 'Your Basic plan allows only 1 store.';
                    }

                    if ($product->name === 'Standard Plan' && $shopCount >= 3) {
                        return 'Your Basic plan allows only 3 store.';
                    }

                    return 'Register a new store';
                }),
        ];
    }

    public function canCreate(): bool
    {
        $user = Auth::user();

        if (! $user) {
            Log::error('No hay usuario autenticado al verificar canCreate', [
                'context' => 'ListShops::canCreate',
            ]);

            return false;
        }

        // Obtener la suscripción activa
        $subscription = $user->subscriptions()->where('stripe_status', 'active')->latest()->first();

        if (! $subscription) {
            Log::info('Usuario sin suscripción activa', [
                'user_id' => $user->id,
                'context' => 'ListShops::canCreate',
            ]);

            return false;
        }

        // Contar las tiendas existentes del usuario
        $shopCount = $user->shops()->count();
        Log::info('Conteo de tiendas del usuario', [
            'user_id' => $user->id,
            'shop_count' => $shopCount,
            'context' => 'ListShops::canCreate',
        ]);

        // Obtener el producto asociado al product_id de la suscripción
        $product = Product::where('id', $subscription->product_id)->first();

        if (! $product) {
            Log::error('No se encontró producto para el product_id de la suscripción', [
                'product_id' => $subscription->product_id,
                'context' => 'ListShops::canCreate',
            ]);

            return false;
        }

        if ($shopCount > 0) {

            // Aplicar las reglas de validación según el producto
            switch ($product->name) {
                case 'Basic Plan':
                    if ($shopCount >= 1) {
                        Log::info('Límite de tiendas alcanzado para Basic', [
                            'user_id' => $user->id,
                            'shop_count' => $shopCount,
                            'context' => 'ListShops::canCreate',
                        ]);

                        return false;
                    }
                    break;

                case 'Standard Plan':
                    if ($shopCount >= 3) {
                        Log::info('Límite de tiendas alcanzado para Standard', [
                            'user_id' => $user->id,
                            'shop_count' => $shopCount,
                            'context' => 'ListShops::canCreate',
                        ]);

                        return false;
                    }
                    break;

                case 'Premium Plan':
                    // Sin límite
                    break;

                default:
                    Log::error('Producto desconocido', [
                        'product_name' => $product->name,
                        'context' => 'ListShops::canCreate',
                    ]);

                    return false;
            }

            Log::info('Usuario autorizado para crear tienda', [
                'user_id' => $user->id,
                'product_name' => $product->name,
                'shop_count' => $shopCount,
                'context' => 'ListShops::canCreate',
            ]);

        }

        return true;
    }
}
