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
            'id_cart' => (string) $this->faker->uuid(),
            'user_id' => \App\Models\User::factory(),
            'shop_id' => \App\Models\Shop::factory(),
            'total_price' => 49.97,
            'response' => json_encode([
                ['algo' => 1],
                ['algo2' => 1],
            ]),
            'status' => $this->faker->randomElement(['abandoned', 'complete']),
            'abandoned_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'email_client' => $this->faker->randomElement(['Mailgun', 'SendGrid', 'Amazon SES']),
            'phone_client' => $this->faker->randomElement(['Twilio', 'Nexmo', 'Plivo']),
        ];
    }
}
