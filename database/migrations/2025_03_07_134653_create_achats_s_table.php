<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('achats_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_fournisseur')
                ->constrained('fournisseur')
                ->onDelete('cascade');
            $table->foreignId('id_user')
                ->constrained('users')
                ->onDelete('cascade');
            $table->date('date_achat');
            $table->decimal('total', 10, 2);
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('achats_s');
    }
};