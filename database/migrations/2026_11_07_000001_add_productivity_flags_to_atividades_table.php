<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atividades', function (Blueprint $table): void {
            $table->boolean('relatorio_realizado')->default(false);
            $table->boolean('analise_proj_realizada')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('atividades', function (Blueprint $table): void {
            $table->dropColumn(['relatorio_realizado', 'analise_proj_realizada']);
        });
    }
};
