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
}
