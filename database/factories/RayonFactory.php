<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rayon;
use App\Models\User;
use App\Models\Local;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rayon>
 */
class RayonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Rayon::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'iduser' => User::inRandomOrder()->first()->id ?? User::factory(),
            'id_local' => Local::inRandomOrder()->first()->id ?? Local::factory(),
        ];
    }
}
