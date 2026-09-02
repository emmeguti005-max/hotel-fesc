<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('token', 40)->nullable()->after('id');
        });

        DB::table('solicitudes')->whereNull('token')->orderBy('id')->each(function ($solicitud) {
            DB::table('solicitudes')->where('id', $solicitud->id)->update(['token' => Str::random(40)]);
        });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('token', 40)->nullable(false)->change();
        });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
