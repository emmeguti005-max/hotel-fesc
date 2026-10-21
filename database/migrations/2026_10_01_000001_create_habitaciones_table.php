<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piso_id')->constrained('pisos')->cascadeOnDelete();
            $table->string('numero', 20)->unique(); // ej: 'A-201'
            $table->enum('tipo', ['sencilla', 'doble', 'suite', 'familiar']);
            $table->unsignedTinyInteger('capacidad')->default(2);
            $table->decimal('tarifa_noche', 12, 2);
            $table->enum('estado', ['disponible', 'ocupada', 'sucia', 'en_limpieza', 'mantenimiento', 'bloqueada'])->default('disponible');
            $table->string('descripcion', 255)->nullable();
            $table->string('token', 40)->unique(); // URL pública del QR de la habitación
            $table->decimal('pos_x', 5, 2)->nullable(); // posición relativa (%) dentro del plano del piso
            $table->decimal('pos_y', 5, 2)->nullable();
            $table->timestamps();
        });

        // Activos y reportes de mantenimiento pueden estar en una habitación en vez de una zona común.
        Schema::table('equipos', function (Blueprint $table) {
            $table->foreignId('habitacion_id')->nullable()->after('ubicacion_id')->constrained('habitaciones')->cascadeOnDelete();
        });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->foreignId('habitacion_id')->nullable()->after('ubicacion_id')->constrained('habitaciones');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('habitacion_id');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('habitacion_id');
        });

        Schema::dropIfExists('habitaciones');
    }
};
