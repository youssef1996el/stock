<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Local;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Local>
 */
class LocalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Local::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'iduser' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
