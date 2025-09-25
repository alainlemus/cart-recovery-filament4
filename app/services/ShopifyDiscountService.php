<?php

namespace App\services;

use App\Models\Coupon;
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


    public function createDiscountCode(string $prefix = 'CART', float $amount = 10.0): ?array
    {
        Log::info('Creating discount code', [
            'shop' => $this->shop,
            'prefix' => $prefix,
            'amount' => $amount,
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        $priceRuleResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->post("https://{$this->shop}/admin/api/".config('services.shopify.api_version')."/price_rules.json", [
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

        if (!$priceRuleResponse->successful()) {
            return null;
        }

        $priceRuleId = $priceRuleResponse->json('price_rule.id');
        $code = strtoupper($prefix) . '-' . substr(uniqid(), -6);

        // Garantizar unicidad del código
        while (Coupon::where('code', $code)->exists()) {
            $code = strtoupper($prefix) . '-' . substr(uniqid('', true), -6);
        }

        Log::info('Creating discount code', [
            'price_rule_id' => $priceRuleId,
            'code' => $code,
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        $discountResponse = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
        ])->post("https://{$this->shop}/admin/api/".config('services.shopify.api_version')."/price_rules/{$priceRuleId}/discount_codes.json", [
            'discount_code' => [
                'code' => $code
            ]
        ]);

        Log::info('Discount code response', [
            'body' => $discountResponse->body(),
            'status' => $discountResponse->status(),
            'context' => 'ShopifyDiscountService::createDiscountCode'
        ]);

        if (!$discountResponse->successful()) {
            return null;
        }

        $shopifyCode = $discountResponse->json('discount_code.code') ?? $code;

        return [
            'shopify_id' => $discountResponse->json('discount_code.id'),
            'code' => $shopifyCode,
            'title' => $priceRuleResponse->json('price_rule.title'),
            'value' => $amount,
            'value_type' => 'percentage',
            'shop_id' => auth()->user()?->shops()->where('shopify_domain', $this->shop)->first()?->id,
            'user_id' => auth()->id(),
            'starts_at' => $priceRuleResponse->json('price_rule.starts_at'),
            'ends_at' => $priceRuleResponse->json('price_rule.ends_at'),
            'response' => $discountResponse->body(),
        ];

    }

    public function deleteDiscountCode(string $priceRuleId, string $discountCodeId): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->token,
            ])->delete("https://{$this->shop}/admin/api/" . config('services.shopify.api_version') . "/price_rules/{$priceRuleId}/discount_codes/{$discountCodeId}.json");

            if ($response->successful()) {
                Log::info('Discount code deleted successfully', [
                    'discountCodeId' => $discountCodeId,
                    'priceRuleId' => $priceRuleId,
                    'status' => $response->status(),
                    'context' => 'ShopifyDiscountService::deleteDiscountCode'
                ]);
                return true;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('Exception while deleting discount code from Shopify', [
                'discountCodeId' => $discountCodeId,
                'priceRuleId' => $priceRuleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => 'ShopifyDiscountService::deleteDiscountCode'
            ]);
            return false;
        }
    }

    public function deletePriceRule(string $priceRuleId): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->token,
            ])->delete("https://{$this->shop}/admin/api/" . config('services.shopify.api_version') . "/price_rules/{$priceRuleId}.json");

            if ($response->successful()) {
                Log::info('Price rule deleted successfully', [
                    'priceRuleId' => $priceRuleId,
                    'status' => $response->status(),
                    'context' => 'ShopifyDiscountService::deletePriceRule'
                ]);
                return true;
            }

            Log::error('Failed to delete price rule from Shopify', [
                'priceRuleId' => $priceRuleId,
                'status' => $response->status(),
                'body' => $response->body(),
                'context' => 'ShopifyDiscountService::deletePriceRule'
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('Exception while deleting price rule from Shopify', [
                'priceRuleId' => $priceRuleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => 'ShopifyDiscountService::deletePriceRule'
            ]);
            return false;
        }
    }

}
