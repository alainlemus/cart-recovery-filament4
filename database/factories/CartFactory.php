<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
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
            'shop_id' => \App\Models\Shop::factory(),
            'items' => json_encode([
                ['product_id' => 1, 'quantity' => 2, 'price' => 19.99],
                ['product_id' => 2, 'quantity' => 1, 'price' => 9.99],
            ]),
            'total_price' => 49.97,
            'status' => $this->faker->randomElement(['active', 'abandoned', 'completed']),
            'abandoned_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'email_client' => $this->faker->randomElement(['Mailgun', 'SendGrid', 'Amazon SES']),
            'phone_client' => $this->faker->randomElement(['Twilio', 'Nexmo', 'Plivo']),
        ];
    }
}
