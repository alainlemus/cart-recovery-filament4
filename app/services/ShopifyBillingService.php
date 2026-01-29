<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyBillingService
{
    protected Shop $shop;

    protected string $apiVersion;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
        $this->apiVersion = config('services.shopify.api_version', '2024-01');
    }

    /**
     * Create a recurring application charge (subscription) in Shopify.
     */
    public function createRecurringCharge(string $name, float $price, string $returnUrl, ?int $trialDays = null, bool $test = false): ?array
    {
        $payload = [
            'recurring_application_charge' => [
                'name' => $name,
                'price' => $price,
                'return_url' => $returnUrl,
                'test' => $test || config('services.shopify.test_charges', false),
            ],
        ];

        if ($trialDays !== null && $trialDays > 0) {
            $payload['recurring_application_charge']['trial_days'] = $trialDays;
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
            'Content-Type' => 'application/json',
        ])->post(
            "https://{$this->shop->shopify_domain}/admin/api/{$this->apiVersion}/recurring_application_charges.json",
            $payload
        );

        if ($response->failed()) {
            Log::error('Failed to create Shopify recurring charge', [
                'shop_id' => $this->shop->id,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        }

        $data = $response->json('recurring_application_charge');

        Log::info('Shopify recurring charge created', [
            'shop_id' => $this->shop->id,
            'charge_id' => $data['id'] ?? null,
            'confirmation_url' => $data['confirmation_url'] ?? null,
        ]);

        return $data;
    }

    /**
     * Get a recurring application charge by ID.
     */
    public function getRecurringCharge(int $chargeId): ?array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
        ])->get(
            "https://{$this->shop->shopify_domain}/admin/api/{$this->apiVersion}/recurring_application_charges/{$chargeId}.json"
        );

        if ($response->failed()) {
            Log::error('Failed to get Shopify recurring charge', [
                'shop_id' => $this->shop->id,
                'charge_id' => $chargeId,
                'response' => $response->json(),
            ]);

            return null;
        }

        return $response->json('recurring_application_charge');
    }

    /**
     * Activate a recurring application charge.
     */
    public function activateRecurringCharge(int $chargeId): bool
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
            'Content-Type' => 'application/json',
        ])->post(
            "https://{$this->shop->shopify_domain}/admin/api/{$this->apiVersion}/recurring_application_charges/{$chargeId}/activate.json"
        );

        if ($response->failed()) {
            Log::error('Failed to activate Shopify recurring charge', [
                'shop_id' => $this->shop->id,
                'charge_id' => $chargeId,
                'response' => $response->json(),
            ]);

            return false;
        }

        Log::info('Shopify recurring charge activated', [
            'shop_id' => $this->shop->id,
            'charge_id' => $chargeId,
        ]);

        return true;
    }

    /**
     * Cancel a recurring application charge.
     */
    public function cancelRecurringCharge(int $chargeId): bool
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
        ])->delete(
            "https://{$this->shop->shopify_domain}/admin/api/{$this->apiVersion}/recurring_application_charges/{$chargeId}.json"
        );

        if ($response->failed()) {
            Log::error('Failed to cancel Shopify recurring charge', [
                'shop_id' => $this->shop->id,
                'charge_id' => $chargeId,
                'response' => $response->json(),
            ]);

            return false;
        }

        Log::info('Shopify recurring charge cancelled', [
            'shop_id' => $this->shop->id,
            'charge_id' => $chargeId,
        ]);

        return true;
    }

    /**
     * Get all recurring application charges for the shop.
     */
    public function getAllRecurringCharges(): array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
        ])->get(
            "https://{$this->shop->shopify_domain}/admin/api/{$this->apiVersion}/recurring_application_charges.json"
        );

        if ($response->failed()) {
            Log::error('Failed to get all Shopify recurring charges', [
                'shop_id' => $this->shop->id,
                'response' => $response->json(),
            ]);

            return [];
        }

        return $response->json('recurring_application_charges') ?? [];
    }

    /**
     * Check if shop has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        $charges = $this->getAllRecurringCharges();

        foreach ($charges as $charge) {
            if ($charge['status'] === 'active') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the current active subscription.
     */
    public function getActiveSubscription(): ?array
    {
        $charges = $this->getAllRecurringCharges();

        foreach ($charges as $charge) {
            if ($charge['status'] === 'active') {
                return $charge;
            }
        }

        return null;
    }

    /**
     * Create a usage charge for usage-based billing.
     */
    public function createUsageCharge(int $recurringChargeId, string $description, float $price): ?array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
            'Content-Type' => 'application/json',
        ])->post(
            "https://{$this->shop->shopify_domain}/admin/api/{$this->apiVersion}/recurring_application_charges/{$recurringChargeId}/usage_charges.json",
            [
                'usage_charge' => [
                    'description' => $description,
                    'price' => $price,
                ],
            ]
        );

        if ($response->failed()) {
            Log::error('Failed to create Shopify usage charge', [
                'shop_id' => $this->shop->id,
                'recurring_charge_id' => $recurringChargeId,
                'response' => $response->json(),
            ]);

            return null;
        }

        return $response->json('usage_charge');
    }
}
