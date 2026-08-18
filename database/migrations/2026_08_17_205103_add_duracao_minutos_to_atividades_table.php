<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atividades', function (Blueprint $table) {
            $table->unsignedInteger('duracao_minutos')->nullable()->after('tipo_projeto');
        });

        $this->backfillDuracaoMinutos();

        Schema::table('atividades', function (Blueprint $table) {
            $table->dropColumn(['data_hora_inicio', 'data_hora_fim']);
        });
    }

    /**
     * O rollback recria as colunas de relógio vazias; os instantes originais não voltam.
     */
    public function down(): void
    {
        Schema::table('atividades', function (Blueprint $table) {
            $table->dateTime('data_hora_inicio')->nullable()->after('tipo_projeto');
            $table->dateTime('data_hora_fim')->nullable()->after('data_hora_inicio');
        });

        Schema::table('atividades', function (Blueprint $table) {
            $table->dropColumn('duracao_minutos');
        });
    }

    private function backfillDuracaoMinutos(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE atividades
                SET duracao_minutos = CAST(ROUND((strftime('%s', data_hora_fim) - strftime('%s', data_hora_inicio)) / 60.0) AS INTEGER)
                WHERE data_hora_inicio IS NOT NULL
                  AND data_hora_fim IS NOT NULL
                  AND datetime(data_hora_fim) > datetime(data_hora_inicio)
                  AND ROUND((strftime('%s', data_hora_fim) - strftime('%s', data_hora_inicio)) / 60.0) > 0
            ");

            return;
        }

        DB::statement('
            UPDATE atividades
            SET duracao_minutos = ROUND(TIMESTAMPDIFF(SECOND, data_hora_inicio, data_hora_fim) / 60.0)
            WHERE data_hora_inicio IS NOT NULL
              AND data_hora_fim IS NOT NULL
              AND data_hora_fim > data_hora_inicio
              AND ROUND(TIMESTAMPDIFF(SECOND, data_hora_inicio, data_hora_fim) / 60.0) > 0
        ');
    }
};
