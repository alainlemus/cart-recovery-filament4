<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'product_id' => \App\Models\Product::factory(),
            'type' => $this->faker->randomElement(['basic', 'premium', 'enterprise']),
            'stripe_id' => $this->faker->uuid,
            'stripe_status' => $this->faker->randomElement(['active', 'past_due', 'canceled', 'unpaid']),
            'stripe_price' => $this->faker->randomFloat(2, 5, 100),
            'quantity' => $this->faker->numberBetween(1, 10),
            'trial_ends_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'start_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'ends_at' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
