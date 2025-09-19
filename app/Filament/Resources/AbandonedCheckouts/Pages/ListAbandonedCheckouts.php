<?php

namespace App\Filament\Resources\AbandonedCheckouts\Pages;

use App\Filament\Resources\AbandonedCheckouts\AbandonedCheckoutResource;
use App\Models\Cart;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ListAbandonedCheckouts extends ListRecords
{
    protected static string $resource = AbandonedCheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sincronizarCheckouts')
                ->label('Synchronize Checkouts')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {

                    $user = Auth::user();

                    foreach ($user->shops as $shop) {
                        $checkouts = AbandonedCheckoutResource::fetchAbandonedCheckouts($shop);
                        Log::info('Sincronizando checkouts abandonados para el usuario', [
                            'user_id' => $user->id,
                            'shop_id' => $shop->id,
                            'checkouts' => $checkouts,
                            'context' => 'ListAbandonedCheckouts::sincronizarCheckouts'
                        ]);
                        foreach ($checkouts as $checkout) {
                            Cart::updateOrCreate(
                                ['shopify_id' => $checkout['id']],
                                [
                                    'id_cart' => $checkout['id_cart'],
                                    'user_id' => $user->id,
                                    'shop_id' => $shop->id,
                                    'email_client' => $checkout['email'],
                                    'phone_client' => '+525531293712',
                                    'response' => json_encode($checkout),
                                    'total_price' => $checkout['total_price'],
                                    'created_at' => $checkout['created_at'],
                                    'abandoned_at' => $checkout['abandoned_at'],
                                    'abandoned_checkout_url' => $checkout['abandoned_checkout_url'],
                                ]
                            );
                        }
                    }

                }),
        ];
    }

}
