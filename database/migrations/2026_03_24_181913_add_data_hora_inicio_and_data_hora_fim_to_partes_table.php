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
        Schema::table('partes', function (Blueprint $table) {
            $table->dateTime('data_hora_inicio')->nullable()->after('postes_projetados');
            $table->dateTime('data_hora_fim')->nullable()->after('data_hora_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes', function (Blueprint $table) {
            $table->dropColumn(['data_hora_inicio', 'data_hora_fim']);
        });
    }
};
