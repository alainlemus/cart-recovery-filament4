<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShopifySubscription>
 */
class ShopifySubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => \App\Models\Shop::factory(),
            'shopify_charge_id' => $this->faker->unique()->numberBetween(1000000000, 9999999999),
            'name' => $this->faker->randomElement(['Basic', 'Pro', 'Enterprise']),
            'price' => $this->faker->randomElement([9.99, 29.99, 99.99]),
            'status' => 'active',
            'billing_on' => now()->addMonth()->toDateString(),
            'activated_on' => now(),
            'cancelled_on' => null,
            'trial_days' => 7,
            'trial_ends_on' => now()->addDays(7),
            'test' => false,
            'shopify_response' => [
                'id' => $this->faker->unique()->numberBetween(1000000000, 9999999999),
                'name' => 'Cart Recovery Pro',
                'price' => '29.99',
                'status' => 'active',
                'test' => false,
            ],
        ];
    }

    /**
     * Indicate that the subscription is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'activated_on' => null,
        ]);
    }

    /**
     * Indicate that the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_on' => now(),
        ]);
    }

    /**
     * Indicate that the subscription is a test charge.
     */
    public function test(): static
    {
        return $this->state(fn (array $attributes) => [
            'test' => true,
        ]);
    }
}
