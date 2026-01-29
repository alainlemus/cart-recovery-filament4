<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $table = 'shops';

    protected $fillable = [
        'name',
        'access_token',
        'shopify_domain',
        'user_id',
        'subscription_id',
        'product_id',
        'stripe_price_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the Shopify subscription for this shop.
     */
    public function shopifySubscription()
    {
        return $this->hasOne(ShopifySubscription::class)->latest();
    }

    /**
     * Get all Shopify subscriptions for this shop.
     */
    public function shopifySubscriptions()
    {
        return $this->hasMany(ShopifySubscription::class);
    }

    /**
     * Check if the shop has an active Shopify subscription.
     */
    public function hasActiveShopifySubscription(): bool
    {
        $subscription = $this->shopifySubscription;

        return $subscription && $subscription->isActive();
    }

    /**
     * Check if the shop has any active subscription (Shopify or Stripe).
     */
    public function hasActiveSubscription(): bool
    {
        // Check Shopify subscription
        if ($this->hasActiveShopifySubscription()) {
            return true;
        }

        // Check Stripe subscription via user
        $stripeSubscription = $this->user->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        return $stripeSubscription !== null;
    }
}
