<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use App\Models\Shop;
use App\Services\ShopifyDiscountService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                Coupon::query()->where('user_id', Auth::id())
            )
            ->columns([
                TextColumn::make('shopify_id')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('value_type'),
                TextColumn::make('shop.name')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //EditAction::make(),
                DeleteAction::make()
                    ->before(function($record) {
                        $shop = Shop::find($record->shop_id);
                        $response = is_array($record->response) ? $record->response : json_decode($record->response, true);

                        $priceRuleId = $response['discount_code']['price_rule_id'] ?? null;
                        $discountCodeId = $record->shopify_id;

                        if ($shop && $priceRuleId && $discountCodeId) {
                            $service = new ShopifyDiscountService($shop->shopify_domain, $shop->access_token);
                            $service->deleteDiscountCode($priceRuleId, $discountCodeId);

                            $deletedCode = $service->deleteDiscountCode($priceRuleId, $discountCodeId);

                            if ($deletedCode) {
                                // 2. Verificar si ya no quedan más discount codes en esa price rule
                                $codesResponse = Http::withHeaders([
                                    'X-Shopify-Access-Token' => $shop->access_token,
                                ])->get("https://{$shop->shopify_domain}/admin/api/" . config('services.shopify.api_version') . "/price_rules/{$priceRuleId}/discount_codes.json");

                                $codes = $codesResponse->json('discount_codes') ?? [];

                                // 3. Si ya no hay códigos, eliminamos también la price rule
                                if (empty($codes)) {
                                    $service->deletePriceRule($priceRuleId);
                                }
                            }
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
