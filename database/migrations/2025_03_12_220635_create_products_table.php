<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_article');//->unique();
            $table->string('unite')->nullable();
            $table->decimal('price_achat', 10, 2);
            $table->decimal('price_vente', 10, 2);
            $table->string('code_barre')->nullable();
            $table->text('emplacement')->nullable();
            $table->foreignId('id_categorie')->constrained('categories');
            $table->foreignId('id_subcategorie')->constrained('sub_categories');
            $table->foreignId('id_local')->constrained('locals');
            $table->foreignId('id_rayon')->constrained('rayons');
            $table->foreignId('id_user')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}