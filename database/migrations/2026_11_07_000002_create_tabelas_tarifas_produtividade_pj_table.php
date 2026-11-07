<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabelas_tarifas_produtividade_pj', function (Blueprint $table): void {
            $table->id();
            $table->date('competencia_inicio')->unique();
            $table->json('tarifas');
            $table->foreignId('salvo_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();
        $tarifas = [
            'desenho_fixo' => 270000,
            'desenho_variavel' => 250,
            'projeto_1_10_fixo' => 400000,
            'projeto_1_10_variavel' => 400,
            'projeto_11_20_fixo' => 500000,
            'projeto_11_20_variavel' => 500,
            'projeto_21_mais_fixo' => 600000,
            'projeto_21_mais_variavel' => 600,
            'proj_1_10_fixo' => 520000,
            'proj_1_10_variavel' => 520,
            'proj_11_20_fixo' => 620000,
            'proj_11_20_variavel' => 620,
            'proj_21_mais_fixo' => 720000,
            'proj_21_mais_variavel' => 720,
            'relatorio_fixo' => 50000,
            'relatorio_variavel' => 0,
            'analise_proj_fixo' => 350000,
            'analise_proj_variavel' => 0,
        ];

        \Illuminate\Support\Facades\DB::table('tabelas_tarifas_produtividade_pj')->insert([
            'competencia_inicio' => $now->copy()->startOfMonth()->toDateString(),
            'tarifas' => json_encode($tarifas, JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tabelas_tarifas_produtividade_pj');
    }
};
