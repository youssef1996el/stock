<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temp_Achat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('idproduit')->references('id')->on('products')->onDelete('cascade');
            $table->foreignId('id_fournisseur')->nullable()->references('id')->on('fournisseurs')->onDelete('cascade');
            $table->integer('qte');
            $table->timestamps();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_Achat');
    }
};