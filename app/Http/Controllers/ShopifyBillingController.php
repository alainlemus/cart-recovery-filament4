<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopifySubscription;
use App\Services\ShopifyBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShopifyBillingController extends Controller
{
    /**
     * Show available plans for the shop.
     */
    public function plans(Request $request)
    {
        $user = Auth::user();
        $shop = null;
        $currentSubscription = null;

        // If user is authenticated, get their shop
        if ($user) {
            $shop = $user->shops()->first();
            $currentSubscription = $shop?->shopifySubscription;
        }

        $products = Product::where('is_active', true)->get();

        return view('shopify.billing.plans', [
            'shop' => $shop,
            'products' => $products,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    /**
     * Initiate a subscription for a plan.
     */
    public function subscribe(Request $request, Product $product)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('shopify.register', ['product' => $product->id])
                ->with('info', 'Please create an account to subscribe.');
        }

        $shop = $user->shops()->first();

        if (! $shop) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'You need to create a shop first.');
        }

        if (! $shop->access_token) {
            return redirect()->route('shopify.auth', ['shop_id' => $shop->id])
                ->with('error', 'Please connect your Shopify store first.');
        }

        // Check if shop already has an active subscription
        $existingSubscription = $shop->shopifySubscription;
        if ($existingSubscription && $existingSubscription->isActive()) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'You already have an active subscription. Please cancel it first to change plans.');
        }

        // Create the return URL for after Shopify billing confirmation
        $returnUrl = route('shopify.billing.callback', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
        ]);

        // Create the subscription via Shopify Billing API
        $subscription = ShopifySubscription::createForShop(
            $shop,
            $product->name,
            $product->price,
            $returnUrl,
            config('services.shopify.trial_days', 7)
        );

        if (! $subscription) {
            Log::error('Failed to create Shopify subscription', [
                'shop_id' => $shop->id,
                'product_id' => $product->id,
            ]);

            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Failed to create subscription. Please try again.');
        }

        // Get the confirmation URL from the Shopify response
        $confirmationUrl = $subscription->shopify_response['confirmation_url'] ?? null;

        if (! $confirmationUrl) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Failed to get confirmation URL. Please try again.');
        }

        Log::info('Redirecting to Shopify billing confirmation', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'confirmation_url' => $confirmationUrl,
        ]);

        // Redirect to Shopify's billing confirmation page
        return redirect()->away($confirmationUrl);
    }

    /**
     * Handle the callback from Shopify after billing confirmation.
     */
    public function callback(Request $request)
    {
        $shopId = $request->query('shop_id');
        $productId = $request->query('product_id');
        $chargeId = $request->query('charge_id');

        $shop = Shop::findOrFail($shopId);
        $product = Product::findOrFail($productId);

        Log::info('Shopify billing callback received', [
            'shop_id' => $shopId,
            'product_id' => $productId,
            'charge_id' => $chargeId,
        ]);

        if (! $chargeId) {
            // User declined the charge
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Subscription was declined or cancelled.');
        }

        // Get the subscription from our database
        $subscription = ShopifySubscription::where('shop_id', $shopId)
            ->where('shopify_charge_id', $chargeId)
            ->first();

        if (! $subscription) {
            // Create the subscription record if it doesn't exist
            $service = new ShopifyBillingService($shop);
            $charge = $service->getRecurringCharge($chargeId);

            if ($charge) {
                $subscription = ShopifySubscription::create([
                    'shop_id' => $shop->id,
                    'shopify_charge_id' => $charge['id'],
                    'name' => $charge['name'],
                    'price' => $charge['price'],
                    'status' => $charge['status'],
                    'billing_on' => $charge['billing_on'] ?? null,
                    'trial_days' => $charge['trial_days'] ?? null,
                    'trial_ends_on' => $charge['trial_ends_on'] ?? null,
                    'test' => $charge['test'] ?? false,
                    'shopify_response' => $charge,
                ]);
            }
        }

        if (! $subscription) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Failed to process subscription. Please try again.');
        }

        // Check if the charge was accepted
        $service = new ShopifyBillingService($shop);
        $charge = $service->getRecurringCharge($chargeId);

        if (! $charge) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Failed to verify subscription status.');
        }

        // If the charge is accepted, activate it
        if ($charge['status'] === 'accepted') {
            $activated = $service->activateRecurringCharge($chargeId);

            if ($activated) {
                $subscription->update([
                    'status' => 'active',
                    'activated_on' => now(),
                ]);

                // Update the shop with the product
                $shop->update([
                    'product_id' => $product->id,
                ]);

                Log::info('Shopify subscription activated', [
                    'shop_id' => $shop->id,
                    'subscription_id' => $subscription->id,
                ]);

                return redirect()->route('filament.admin-shop.pages.dashboard')
                    ->with('success', 'Subscription activated successfully! Welcome to '.$product->name.'.');
            }
        } elseif ($charge['status'] === 'active') {
            // Already active
            $subscription->update([
                'status' => 'active',
                'activated_on' => $charge['activated_on'] ?? now(),
            ]);

            $shop->update([
                'product_id' => $product->id,
            ]);

            return redirect()->route('filament.admin-shop.pages.dashboard')
                ->with('success', 'Subscription is already active! Welcome to '.$product->name.'.');
        } else {
            // Charge was declined or has another status
            $subscription->update([
                'status' => $charge['status'],
            ]);

            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Subscription was not approved. Status: '.$charge['status']);
        }

        return redirect()->route('shopify.billing.plans')
            ->with('error', 'Failed to activate subscription.');
    }

    /**
     * Cancel the current subscription.
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shops()->first();

        if (! $shop) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Shop not found.');
        }

        $subscription = $shop->shopifySubscription;

        if (! $subscription || ! $subscription->isActive()) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'No active subscription to cancel.');
        }

        $result = $subscription->cancel();

        if ($result) {
            return redirect()->route('shopify.billing.plans')
                ->with('success', 'Subscription cancelled successfully.');
        }

        return redirect()->route('shopify.billing.plans')
            ->with('error', 'Failed to cancel subscription. Please try again.');
    }

    /**
     * Sync subscription status with Shopify.
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shops()->first();

        if (! $shop) {
            return redirect()->route('shopify.billing.plans')
                ->with('error', 'Shop not found.');
        }

        $subscription = $shop->shopifySubscription;

        if (! $subscription) {
            return redirect()->route('shopify.billing.plans')
                ->with('info', 'No subscription found to sync.');
        }

        $result = $subscription->syncWithShopify();

        if ($result) {
            return redirect()->route('shopify.billing.plans')
                ->with('success', 'Subscription synced successfully.');
        }

        return redirect()->route('shopify.billing.plans')
            ->with('error', 'Failed to sync subscription.');
    }
}
