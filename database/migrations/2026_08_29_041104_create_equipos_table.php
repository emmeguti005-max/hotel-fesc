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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->enum('tipo', ['pc', 'impresora', 'proyector', 'telefono', 'red', 'otro']);
            $table->string('codigo', 50)->nullable(); // ej: 'PC-RAD-01'
            $table->string('nombre', 100); // descripción corta visible
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('serie', 100)->nullable();
            $table->enum('estado', ['activo', 'de_baja'])->default('activo');
            $table->decimal('pos_x', 5, 2)->nullable(); // posición relativa (%) dentro del plano del piso
            $table->decimal('pos_y', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
