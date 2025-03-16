<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Local;
use App\Models\Rayon;
use Faker\Factory as Faker;
use App\Models\Tva;
use App\Models\Unite;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Seed the base tables
    $this->call([
        PermissionSeeder::class,
        RoleSeeder::class,
        DefaultUserSeeder::class,
    ]);

    // Seed additional tables
    User::factory(5)->create();

    // Create Categories and Subcategories
    $categories = Category::factory(5)->create();
    $categories->each(function ($category) {
        SubCategory::factory(3)->create(['id_categorie' => $category->id]);
    });

    // Create Locals, Rayons, TVA, and Unite
    $locals = Local::factory(5)->create();
    $rayons = Rayon::factory(5)->create();
    $tvas = Tva::factory(3)->create(); // Ensure you have at least 3 entries in the TVA table
    $unites = Unite::factory(3)->create(); // Ensure you have at least 3 entries in the Unite table

    // Create Products with Stock
    $products = Product::factory(20)->create();
    $products->each(function ($product) {
         // Ensure quantite is always greater than seuil
        $seuil = rand(1, 100); // Random value for seuil
        $quantite = rand($seuil + 1, 300); // quantite should always be greater than seuil
        Stock::factory()->create([
            'id_product' => $product->id,
            'id_tva' => rand(1, 3), // Ensure these IDs exist in the tvas table
            'id_unite' => rand(1, 3), // Ensure these IDs exist in the unites table
            'quantite' => $quantite,
            'seuil' => $seuil,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    $this->command->info('Database seeding completed successfully!');
}

}