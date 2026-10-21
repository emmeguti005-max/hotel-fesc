<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro de cada limpieza: qué habitación, quién la hizo, cuándo empezó y terminó.
        Schema::create('tareas_aseo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->foreignId('asignada_a')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->enum('tipo', ['salida', 'diaria', 'profunda', 'solicitud_huesped']);
            $table->enum('prioridad', ['normal', 'alta'])->default('normal');
            $table->enum('estado', ['pendiente', 'en_proceso', 'terminada'])->default('pendiente');
            $table->text('notas')->nullable(); // indicaciones o lo que pidió el huésped
            $table->text('observaciones')->nullable(); // lo que reporta la camarera al terminar
            $table->timestamp('inicio_at')->nullable();
            $table->timestamp('fin_at')->nullable();
            $table->foreignId('creada_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['estado', 'asignada_a']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_aseo');
    }
};
