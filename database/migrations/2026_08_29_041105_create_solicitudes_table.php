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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->string('nombre_solicitante', 100)->nullable(); // texto libre y opcional, sin cuenta
            $table->enum('tipo', ['impresora', 'instalacion', 'red', 'hardware', 'software', 'otro']);
            $table->text('descripcion');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('estado', ['pendiente', 'en_proceso', 'resuelto', 'no_resuelto'])->default('pendiente');
            $table->foreignId('tecnico_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('ip_origen', 45)->nullable(); // control de abuso, formulario público
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
