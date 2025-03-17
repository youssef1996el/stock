<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tva;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tva>
 */
class TvaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Tva::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['tva 0', 'tva 7', 'tva 14', 'tva 20']),
            'value' => match ($this->faker->randomElement(['tva 0', 'tva 7', 'tva 14', 'tva 20'])) {
                'tva 0' => 0,
                'tva 7' => 7,
                'tva 14' => 14,
                'tva 20' => 20,
                default => 0,
            },
            'iduser' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
