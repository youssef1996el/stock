<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Product::class;
    public function definition(): array
    {
        // Get a random category and subcategory
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();
        $subcategory = SubCategory::where('id_categorie', $category->id)->inRandomOrder()->first() ?? SubCategory::factory()->create(['id_categorie' => $category->id]);

        // Generate the product code using the method and ensure it is unique
        $code_article = Product::generateCodeArticle($category->name, $subcategory->name);

        return [
            'name' => $this->faker->word(),
            'code_article' => $code_article,
            'unite' => $this->faker->randomElement(['kg']),
            'price_achat' => $this->faker->randomFloat(2, 10, 500),
            'price_vente' => $this->faker->randomFloat(2, 15, 600),
            'code_barre' => $this->faker->unique()->ean13(),
            'emplacement' => $this->faker->sentence(),
            'id_categorie' => rand(1, 5), // Adjust based on your categories
            'id_subcategorie' => rand(1, 5),
            'id_local' => rand(1, 5),
            'id_rayon' => rand(1, 5),
            'id_user' => rand(1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }



}
