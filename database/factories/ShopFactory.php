<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'domain' => $this->faker->unique()->domainName,
            'access_token' => $this->faker->sha256,
            'shopify_domain' => $this->faker->domainName,
            'user_id' => \App\Models\User::factory(),
            'subscription_id' => \App\Models\Subscription::factory(),
            'product_id' => \App\Models\Product::factory(),
            'shopify_api_key' => $this->faker->uuid,
            'shopify_api_secret' => $this->faker->sha256,
        ];
    }
}
