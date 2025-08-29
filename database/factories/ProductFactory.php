<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 5, 100),
            'currency' => 'USD',
            'is_active' => $this->faker->boolean(80),
            'features' => $this->faker->randomElements(['Feature A', 'Feature B', 'Feature C', 'Feature D'], 2),
        ];
    }
}
