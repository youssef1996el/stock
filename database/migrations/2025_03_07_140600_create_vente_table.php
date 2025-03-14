<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')
                ->constrained('users')
                ->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->date('date_vente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vente');
    }
};