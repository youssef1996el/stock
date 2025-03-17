<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Stock;
use App\Models\Product;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'id_product' => Product::factory(),
            'id_tva' => rand(1, 3), // Adjust based on available TVA IDs
            'id_unite' => rand(1, 4), // Adjust based on available Unite IDs
            'quantite' => $this->faker->numberBetween(0, 1000),
            'seuil' => $this->faker->numberBetween(0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
