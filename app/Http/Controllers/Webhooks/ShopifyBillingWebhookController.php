<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopifySubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyBillingWebhookController extends Controller
{
    /**
     * Handle incoming Shopify billing webhooks.
     */
    public function handle(Request $request)
    {
        $topic = $request->header('X-Shopify-Topic');
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $hmac = $request->header('X-Shopify-Hmac-Sha256');

        // Verify webhook signature
        if (! $this->verifyWebhook($request->getContent(), $hmac)) {
            Log::warning('Shopify billing webhook signature verification failed', [
                'shop_domain' => $shopDomain,
                'topic' => $topic,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        Log::info('Shopify billing webhook received', [
            'topic' => $topic,
            'shop_domain' => $shopDomain,
            'charge_id' => $payload['id'] ?? null,
        ]);

        return match ($topic) {
            'app_subscriptions/update' => $this->handleSubscriptionUpdate($shopDomain, $payload),
            'app/uninstalled' => $this->handleAppUninstalled($shopDomain, $payload),
            default => response()->json(['message' => 'Webhook received']),
        };
    }

    /**
     * Handle subscription update events.
     */
    protected function handleSubscriptionUpdate(string $shopDomain, array $payload)
    {
        $shop = Shop::where('shopify_domain', $shopDomain)->first();

        if (! $shop) {
            Log::warning('Shop not found for billing webhook', [
                'shop_domain' => $shopDomain,
            ]);

            return response()->json(['error' => 'Shop not found'], 404);
        }

        $chargeId = $payload['id'] ?? $payload['app_subscription']['admin_graphql_api_id'] ?? null;

        if (! $chargeId) {
            Log::warning('No charge ID in billing webhook payload', [
                'shop_domain' => $shopDomain,
            ]);

            return response()->json(['error' => 'No charge ID'], 400);
        }

        // Find the subscription by charge ID
        $subscription = ShopifySubscription::where('shopify_charge_id', $chargeId)
            ->orWhere('shopify_charge_id', 'like', '%'.$this->extractNumericId($chargeId).'%')
            ->first();

        if (! $subscription) {
            Log::info('Creating new subscription from webhook', [
                'shop_id' => $shop->id,
                'charge_id' => $chargeId,
            ]);

            // Create new subscription record
            $subscription = new ShopifySubscription;
            $subscription->shop_id = $shop->id;
            $subscription->shopify_charge_id = $chargeId;
        }

        // Update subscription based on payload
        $status = $payload['status'] ?? $payload['app_subscription']['status'] ?? null;

        if ($status) {
            $subscription->status = strtolower($status);

            // Update timestamps based on status
            if ($subscription->status === 'active' && ! $subscription->activated_on) {
                $subscription->activated_on = now();
            }

            if (in_array($subscription->status, ['cancelled', 'declined', 'expired'])) {
                $subscription->cancelled_on = $subscription->cancelled_on ?? now();
            }
        }

        // Update other fields from payload
        if (isset($payload['name']) || isset($payload['app_subscription']['name'])) {
            $subscription->name = $payload['name'] ?? $payload['app_subscription']['name'];
        }

        if (isset($payload['price']) || isset($payload['app_subscription']['line_items'])) {
            $price = $payload['price'] ?? null;
            if (! $price && isset($payload['app_subscription']['line_items'][0]['plan']['pricingDetails']['price']['amount'])) {
                $price = $payload['app_subscription']['line_items'][0]['plan']['pricingDetails']['price']['amount'];
            }
            if ($price) {
                $subscription->price = $price;
            }
        }

        if (isset($payload['test'])) {
            $subscription->test = $payload['test'];
        }

        $subscription->shopify_response = $payload;
        $subscription->save();

        Log::info('Shopify subscription updated from webhook', [
            'subscription_id' => $subscription->id,
            'shop_id' => $shop->id,
            'status' => $subscription->status,
        ]);

        return response()->json(['message' => 'Subscription updated']);
    }

    /**
     * Handle app uninstall events.
     */
    protected function handleAppUninstalled(string $shopDomain, array $payload)
    {
        $shop = Shop::where('shopify_domain', $shopDomain)->first();

        if (! $shop) {
            return response()->json(['message' => 'Shop not found']);
        }

        // Cancel all active subscriptions for this shop
        $shop->shopifySubscriptions()
            ->where('status', 'active')
            ->update([
                'status' => 'cancelled',
                'cancelled_on' => now(),
            ]);

        Log::info('App uninstalled, subscriptions cancelled', [
            'shop_id' => $shop->id,
            'shop_domain' => $shopDomain,
        ]);

        return response()->json(['message' => 'Subscriptions cancelled']);
    }

    /**
     * Verify the Shopify webhook signature.
     */
    protected function verifyWebhook(string $data, ?string $hmacHeader): bool
    {
        if (! $hmacHeader) {
            return false;
        }

        $secret = config('services.shopify.api_secret');

        if (! $secret) {
            Log::error('Shopify API secret not configured for webhook verification');

            return false;
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));

        return hash_equals($calculatedHmac, $hmacHeader);
    }

    /**
     * Extract numeric ID from GraphQL ID.
     */
    protected function extractNumericId(string $graphqlId): string
    {
        // GraphQL IDs look like: gid://shopify/AppSubscription/12345678
        if (preg_match('/(\d+)$/', $graphqlId, $matches)) {
            return $matches[1];
        }

        return $graphqlId;
    }
}
