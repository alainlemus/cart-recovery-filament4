<?php

use App\Models\Shop;
use App\Models\ShopifySubscription;
use App\Models\User;
use App\Services\ShopifyBillingService;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'role' => 'user',
    ]);

    $this->shop = Shop::factory()->create([
        'user_id' => $this->user->id,
        'access_token' => 'test_access_token',
        'shopify_domain' => 'test-shop.myshopify.com',
    ]);
});

describe('ShopifyBillingService', function () {
    it('creates a recurring charge successfully', function () {
        Http::fake([
            '*' => Http::response([
                'recurring_application_charge' => [
                    'id' => 123456789,
                    'name' => 'Basic Plan',
                    'price' => '9.99',
                    'status' => 'pending',
                    'confirmation_url' => 'https://test-shop.myshopify.com/admin/charges/123456789/confirm',
                    'test' => true,
                ],
            ], 201),
        ]);

        $service = new ShopifyBillingService($this->shop);
        $result = $service->createRecurringCharge('Basic Plan', 9.99, '/callback');

        // Service returns the inner data, not the wrapper
        expect($result)->toBeArray()
            ->and($result['id'])->toBe(123456789)
            ->and($result['status'])->toBe('pending')
            ->and($result['confirmation_url'])->toContain('confirm');
    });

    it('gets a recurring charge by ID', function () {
        Http::fake([
            '*' => Http::response([
                'recurring_application_charge' => [
                    'id' => 123456789,
                    'name' => 'Basic Plan',
                    'price' => '9.99',
                    'status' => 'active',
                ],
            ], 200),
        ]);

        $service = new ShopifyBillingService($this->shop);
        $result = $service->getRecurringCharge(123456789);

        // Service returns the inner data, not the wrapper
        expect($result)->toBeArray()
            ->and($result['id'])->toBe(123456789)
            ->and($result['status'])->toBe('active');
    });

    it('activates a recurring charge', function () {
        Http::fake([
            '*' => Http::response([
                'recurring_application_charge' => [
                    'id' => 123456789,
                    'name' => 'Basic Plan',
                    'price' => '9.99',
                    'status' => 'active',
                    'activated_on' => now()->toISOString(),
                ],
            ], 200),
        ]);

        $service = new ShopifyBillingService($this->shop);
        $result = $service->activateRecurringCharge(123456789);

        // activateRecurringCharge returns boolean true on success
        expect($result)->toBeTrue();
    });

    it('cancels a recurring charge', function () {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $service = new ShopifyBillingService($this->shop);
        $result = $service->cancelRecurringCharge(123456789);

        expect($result)->toBeTrue();
    });

    it('checks if shop has active subscription', function () {
        Http::fake([
            '*' => Http::response([
                'recurring_application_charges' => [
                    [
                        'id' => 123456789,
                        'name' => 'Basic Plan',
                        'status' => 'active',
                    ],
                ],
            ], 200),
        ]);

        $service = new ShopifyBillingService($this->shop);
        $result = $service->hasActiveSubscription();

        expect($result)->toBeTrue();
    });

    it('returns false when no active subscription exists', function () {
        Http::fake([
            '*' => Http::response([
                'recurring_application_charges' => [
                    [
                        'id' => 123456789,
                        'name' => 'Basic Plan',
                        'status' => 'cancelled',
                    ],
                ],
            ], 200),
        ]);

        $service = new ShopifyBillingService($this->shop);
        $result = $service->hasActiveSubscription();

        expect($result)->toBeFalse();
    });
});

describe('ShopifySubscription Model', function () {
    it('creates subscription for shop via factory', function () {
        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'shopify_charge_id' => '123456789',
            'name' => 'Pro Plan',
            'price' => 29.99,
            'status' => 'active',
        ]);

        expect($subscription)->toBeInstanceOf(ShopifySubscription::class)
            ->and($subscription->shop_id)->toBe($this->shop->id)
            ->and($subscription->shopify_charge_id)->toBe('123456789')
            ->and($subscription->name)->toBe('Pro Plan')
            ->and((float) $subscription->price)->toBe(29.99)
            ->and($subscription->status)->toBe('active');
    });

    it('creates subscription for shop via API', function () {
        Http::fake([
            '*' => Http::response([
                'recurring_application_charge' => [
                    'id' => 123456789,
                    'name' => 'Pro Plan',
                    'price' => '29.99',
                    'status' => 'pending',
                    'confirmation_url' => 'https://test-shop.myshopify.com/admin/charges/123456789/confirm',
                    'trial_days' => 7,
                    'test' => false,
                ],
            ], 201),
        ]);

        $subscription = ShopifySubscription::createForShop(
            $this->shop,
            'Pro Plan',
            29.99,
            'https://example.com/callback',
            7
        );

        expect($subscription)->toBeInstanceOf(ShopifySubscription::class)
            ->and($subscription->shop_id)->toBe($this->shop->id)
            ->and($subscription->name)->toBe('Pro Plan')
            ->and($subscription->status)->toBe('pending');
    });

    it('checks if subscription is active', function () {
        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        expect($subscription->isActive())->toBeTrue();
    });

    it('checks if subscription is cancelled', function () {
        $subscription = ShopifySubscription::factory()->cancelled()->create([
            'shop_id' => $this->shop->id,
        ]);

        expect($subscription->isCancelled())->toBeTrue()
            ->and($subscription->isActive())->toBeFalse();
    });

    it('checks if subscription is on trial', function () {
        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
            'trial_days' => 7,
            'trial_ends_on' => now()->addDays(5),
        ]);

        expect($subscription->isOnTrial())->toBeTrue();
    });

    it('returns false for expired trial', function () {
        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
            'trial_days' => 7,
            'trial_ends_on' => now()->subDays(2),
        ]);

        expect($subscription->isOnTrial())->toBeFalse();
    });

    it('cancels subscription via API', function () {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        $result = $subscription->cancel();

        expect($result)->toBeTrue()
            ->and($subscription->status)->toBe('cancelled')
            ->and($subscription->cancelled_on)->not->toBeNull();
    });
});

describe('Shop Model Shopify Subscription', function () {
    it('has shopify subscription relationship', function () {
        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        expect($this->shop->shopifySubscription)->toBeInstanceOf(ShopifySubscription::class)
            ->and($this->shop->shopifySubscription->id)->toBe($subscription->id);
    });

    it('checks if shop has active shopify subscription', function () {
        ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        expect($this->shop->hasActiveShopifySubscription())->toBeTrue();
    });

    it('returns false when no active subscription', function () {
        ShopifySubscription::factory()->cancelled()->create([
            'shop_id' => $this->shop->id,
        ]);

        expect($this->shop->hasActiveShopifySubscription())->toBeFalse();
    });

    it('checks combined subscription status (Stripe or Shopify)', function () {
        // No subscriptions
        expect($this->shop->hasActiveSubscription())->toBeFalse();

        // Add Shopify subscription
        ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        $this->shop->refresh();
        expect($this->shop->hasActiveSubscription())->toBeTrue();
    });
});

describe('Shopify Billing Webhook', function () {
    it('handles subscription update webhook', function () {
        $subscription = ShopifySubscription::factory()->pending()->create([
            'shop_id' => $this->shop->id,
            'shopify_charge_id' => '123456789',
        ]);

        $payload = [
            'id' => '123456789',
            'status' => 'active',
            'name' => 'Pro Plan',
            'price' => '29.99',
        ];

        $hmac = base64_encode(hash_hmac('sha256', json_encode($payload), config('services.shopify.api_secret'), true));

        $response = $this->postJson('/webhooks/shopify/billing', $payload, [
            'X-Shopify-Topic' => 'app_subscriptions/update',
            'X-Shopify-Shop-Domain' => $this->shop->shopify_domain,
            'X-Shopify-Hmac-Sha256' => $hmac,
        ]);

        $subscription->refresh();

        expect($subscription->status)->toBe('active');
    });

    it('handles app uninstalled webhook', function () {
        $subscription = ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        $payload = [
            'id' => $this->shop->shopify_shop_id,
        ];

        $hmac = base64_encode(hash_hmac('sha256', json_encode($payload), config('services.shopify.api_secret'), true));

        $response = $this->postJson('/webhooks/shopify/billing', $payload, [
            'X-Shopify-Topic' => 'app/uninstalled',
            'X-Shopify-Shop-Domain' => $this->shop->shopify_domain,
            'X-Shopify-Hmac-Sha256' => $hmac,
        ]);

        $subscription->refresh();

        expect($subscription->status)->toBe('cancelled')
            ->and($subscription->cancelled_on)->not->toBeNull();
    });

    it('rejects webhook with invalid signature', function () {
        $payload = ['id' => '123456789'];

        $response = $this->postJson('/webhooks/shopify/billing', $payload, [
            'X-Shopify-Topic' => 'app_subscriptions/update',
            'X-Shopify-Shop-Domain' => $this->shop->shopify_domain,
            'X-Shopify-Hmac-Sha256' => 'invalid_hmac',
        ]);

        $response->assertStatus(401);
    });
});

describe('User Panel Access with Shopify Subscription', function () {
    it('allows panel access with active shopify subscription', function () {
        ShopifySubscription::factory()->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        // Create a mock panel with admin-shop ID
        $panel = \Filament\Panel::make()->id('admin-shop');

        expect($this->user->canAccessPanel($panel))->toBeTrue();
    });

    it('denies panel access without subscription', function () {
        $panel = \Filament\Panel::make()->id('admin-shop');

        expect($this->user->canAccessPanel($panel))->toBeFalse();
    });

    it('allows super-admin access to admin panel', function () {
        $admin = User::factory()->create([
            'role' => 'super-admin',
        ]);

        $panel = \Filament\Panel::make()->id('admin');

        expect($admin->canAccessPanel($panel))->toBeTrue();
    });

    it('denies regular user access to admin panel', function () {
        $panel = \Filament\Panel::make()->id('admin');

        expect($this->user->canAccessPanel($panel))->toBeFalse();
    });
});
