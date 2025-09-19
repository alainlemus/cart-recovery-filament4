<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyDiscountService
{
    protected string $shop;
    protected string $token;

    public function __construct(string $shop, string $token)
    {
        $this->shop = $shop;
        $this->token = $token;
    }


    public function createDiscountCode(string $prefix = 'CART', float $amount = 10.0): ?string
    {
        Log::info('Creating discount code', [
            'shop' => $this->shop,
            'prefix' => $prefix,
            'amount' => $amount,
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        $priceRuleResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->post("https://{$this->shop}/admin/api/2024-10/price_rules.json", [
            'price_rule' => [
                'title' => "{$prefix}-" . strtoupper(uniqid()),
                'target_type' => 'line_item',
                'target_selection' => 'all',
                'allocation_method' => 'across',
                'value_type' => 'percentage',
                'value' => "-{$amount}",
                'customer_selection' => 'all',
                'starts_at' => now()->toIso8601String(),
            ]
        ]);

        Log::info('Price rule response', [
            'body' => $priceRuleResponse->body(),
            'status' => $priceRuleResponse->status(),
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        if (!$priceRuleResponse->successful()) return null;

        $priceRuleId = $priceRuleResponse->json('price_rule.id');
        $code = strtoupper($prefix) . '-' . substr(uniqid(), -6);

        Log::info('Creating discount code', [
            'price_rule_id' => $priceRuleId,
            'code' => $code,
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        $discountResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->post("https://{$this->shop}/admin/api/2024-10/price_rules/{$priceRuleId}/discount_codes.json", [
            'discount_code' => [
                'code' => $code
            ]
        ]);

        Log::info('Discount code response', [
            'body' => $discountResponse->body(),
            'status' => $discountResponse->status(),
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        if (!$discountResponse->successful()) return null;

        return $code;
    }
}
