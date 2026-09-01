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
        Schema::create('pisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torre_id')->constrained('torres')->cascadeOnDelete();
            $table->integer('numero'); // -1 = sótano, 1..8
            $table->string('plano_imagen')->nullable(); // ruta al plano real; NULL = usar esquema genérico
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pisos');
    }
};
