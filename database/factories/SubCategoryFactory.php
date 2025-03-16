<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubCategory>
 */
class SubCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SubCategory::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'id_categorie' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'iduser' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
