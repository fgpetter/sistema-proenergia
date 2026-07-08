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
            $table->string('tipo_projeto', 10)->default('CAD')->after('postes_projetados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partes', function (Blueprint $table) {
            $table->dropColumn('tipo_projeto');
        });
    }
};
