<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AtividadesSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tabela_de_atividades_existe(): void
    {
        $this->assertTrue(Schema::hasTable('atividades'));
    }

    public function test_tabela_de_log_atividades_existe(): void
    {
        $this->assertTrue(Schema::hasTable('log_atividades'));
    }

    public function test_tabelas_antigas_de_partes_nao_existem(): void
    {
        $this->assertFalse(Schema::hasTable('partes'));
        $this->assertFalse(Schema::hasTable('atividades_projeto'));
    }

    public function test_atividades_tem_duracao_minutos_e_nao_tem_intervalo_de_relogio(): void
    {
        $this->assertTrue(Schema::hasColumn('atividades', 'duracao_minutos'));
        $this->assertFalse(Schema::hasColumn('atividades', 'data_hora_inicio'));
        $this->assertFalse(Schema::hasColumn('atividades', 'data_hora_fim'));
    }
}
