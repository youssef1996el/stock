<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Unite;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unite>
 */
class UniteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Unite::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'iduser' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
