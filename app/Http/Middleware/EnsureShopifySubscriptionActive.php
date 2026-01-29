<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopifySubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $shop = $user->shops()->first();

        if (! $shop) {
            return redirect()->route('filament.admin-shop.resources.shops.create')
                ->with('warning', 'Please create a shop first.');
        }

        // Check for Shopify subscription
        $shopifySubscription = $shop->shopifySubscription;

        if ($shopifySubscription && $shopifySubscription->isActive()) {
            return $next($request);
        }

        // Check for Stripe subscription as fallback
        $stripeSubscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        if ($stripeSubscription) {
            return $next($request);
        }

        // No active subscription found
        return redirect()->route('shopify.billing.plans')
            ->with('warning', 'Please subscribe to a plan to access this feature.');
    }
}
