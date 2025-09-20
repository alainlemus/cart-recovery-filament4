<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use App\Models\Shop;
use App\Services\ShopifyDiscountService;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $shop = Shop::find($data['shop_id']);
        $user = auth()->user();

        $service = new ShopifyDiscountService($shop->shopify_domain, $shop->access_token);
        $shopifyCoupon = $service->createDiscountCode(
            $data['code'] ?? 'CART',
            (float) ($data['value'] ?? 10)
        );

        if ($shopifyCoupon) {
            $data = array_merge($data, $shopifyCoupon);
        }

        // Asigna el usuario autenticado
        $data['user_id'] = $user->id;

        return $data;
    }
}
