<?php

namespace App\Models;

use App\Services\ShopifyBillingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifySubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shopify_charge_id',
        'name',
        'price',
        'status',
        'billing_on',
        'activated_on',
        'cancelled_on',
        'trial_days',
        'trial_ends_on',
        'test',
        'shopify_response',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'activated_on' => 'datetime',
            'cancelled_on' => 'datetime',
            'trial_ends_on' => 'datetime',
            'test' => 'boolean',
            'shopify_response' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the subscription is on trial.
     */
    public function isOnTrial(): bool
    {
        if (! $this->trial_ends_on) {
            return false;
        }

        return $this->trial_ends_on->isFuture();
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'declined', 'expired']);
    }

    /**
     * Cancel the subscription via Shopify API.
     */
    public function cancel(): bool
    {
        $service = new ShopifyBillingService($this->shop);
        $result = $service->cancelRecurringCharge($this->shopify_charge_id);

        if ($result) {
            $this->update([
                'status' => 'cancelled',
                'cancelled_on' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Sync the subscription status with Shopify.
     */
    public function syncWithShopify(): bool
    {
        $service = new ShopifyBillingService($this->shop);
        $charge = $service->getRecurringCharge($this->shopify_charge_id);

        if (! $charge) {
            return false;
        }

        $this->update([
            'status' => $charge['status'],
            'billing_on' => $charge['billing_on'] ?? null,
            'activated_on' => isset($charge['activated_on']) ? $charge['activated_on'] : null,
            'cancelled_on' => isset($charge['cancelled_on']) ? $charge['cancelled_on'] : null,
            'trial_ends_on' => isset($charge['trial_ends_on']) ? $charge['trial_ends_on'] : null,
            'shopify_response' => $charge,
        ]);

        return true;
    }

    /**
     * Create a new Shopify subscription for a shop.
     */
    public static function createForShop(Shop $shop, string $planName, float $price, string $returnUrl, ?int $trialDays = null): ?self
    {
        $service = new ShopifyBillingService($shop);
        $charge = $service->createRecurringCharge($planName, $price, $returnUrl, $trialDays);

        if (! $charge) {
            return null;
        }

        return self::create([
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
