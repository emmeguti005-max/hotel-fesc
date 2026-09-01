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
        Schema::table('equipos', function (Blueprint $table) {
            // Persona que normalmente usa el equipo, para que quien reporta pueda
            // identificarlo por nombre aunque no sepa el código/modelo técnico.
            $table->string('usuario_asignado', 100)->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('usuario_asignado');
        });
    }
};
