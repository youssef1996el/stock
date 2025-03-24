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
        Schema::create('ligne_Achat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('idachat')->references('id')->on('achats')->onDelete('cascade');
            $table->foreignId('idproduit')->references('id')->on('products')->onDelete('cascade');
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
        Schema::dropIfExists('ligne_Achat');
    }
};