<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('huespedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->enum('tipo_documento', ['CC', 'CE', 'PAS', 'NIT', 'TI'])->default('CC');
            $table->string('documento', 30)->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->string('pais', 80)->default('Colombia');
            $table->timestamps();
        });

        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->nullable()->unique(); // RES-000001, se asigna al crear
            $table->foreignId('huesped_id')->constrained('huespedes');
            $table->foreignId('habitacion_id')->constrained('habitaciones');
            $table->date('fecha_entrada');
            $table->date('fecha_salida');
            $table->unsignedTinyInteger('adultos')->default(1);
            $table->unsignedTinyInteger('ninos')->default(0);
            $table->decimal('tarifa_noche', 12, 2); // se congela al reservar
            $table->enum('estado', ['reservada', 'en_curso', 'finalizada', 'cancelada'])->default('reservada');
            $table->timestamp('checkin_at')->nullable();
            $table->timestamp('checkout_at')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creada_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['habitacion_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
        Schema::dropIfExists('huespedes');
    }
};
