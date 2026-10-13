<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Atividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreencherDataConclusaoAtividadesTest extends TestCase
{
    use RefreshDatabase;

    public function test_preenche_data_de_atualizacao_ou_criacao_anterior_ao_corte_sem_sobrescrever_conclusao(): void
    {
        $this->travelTo('2026-10-05 12:00:00');

        $atualizadaAntesDoCorte = Atividade::factory()->create([
            'created_at' => '2026-10-01 08:00:00',
            'updated_at' => '2026-10-04 15:20:00',
        ]);
        $criadaAntesEAtualizadaNoCorte = Atividade::factory()->create([
            'created_at' => '2026-10-02 09:30:00',
            'updated_at' => '2026-10-05 00:00:00',
        ]);
        $criadaNoCorte = Atividade::factory()->create([
            'created_at' => '2026-10-05 00:00:00',
            'updated_at' => '2026-10-05 00:00:00',
        ]);
        $conclusaoJaPreenchida = Atividade::factory()->create([
            'created_at' => '2026-10-01 08:00:00',
            'updated_at' => '2026-10-04 15:20:00',
            'data_conclusao' => '2026-09-30',
        ]);
        $atividadeExcluida = Atividade::factory()->create([
            'created_at' => '2026-10-01 08:00:00',
            'updated_at' => '2026-10-04 15:20:00',
        ]);
        $atividadeExcluida->delete();

        $this->artisan('atividades:preencher-data-conclusao')
            ->expectsOutput('2 atividade(s) atualizadas.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('atividades', [
            'id' => $atualizadaAntesDoCorte->id,
            'data_conclusao' => '2026-10-04',
        ]);
        $this->assertDatabaseHas('atividades', [
            'id' => $criadaAntesEAtualizadaNoCorte->id,
            'data_conclusao' => '2026-10-02',
        ]);
        $this->assertDatabaseHas('atividades', [
            'id' => $criadaNoCorte->id,
            'data_conclusao' => null,
        ]);
        $this->assertSame('2026-09-30', $conclusaoJaPreenchida->fresh()->data_conclusao->format('Y-m-d'));
        $this->assertSoftDeleted('atividades', ['id' => $atividadeExcluida->id]);
    }

    public function test_dry_run_conta_atividades_sem_preencher_data_conclusao(): void
    {
        $atividade = Atividade::factory()->create([
            'created_at' => '2026-10-04 23:59:59',
            'updated_at' => '2026-10-05 12:00:00',
        ]);

        $this->artisan('atividades:preencher-data-conclusao', ['--dry-run' => true])
            ->expectsOutput('1 atividade(s) seriam atualizadas.')
            ->expectsOutput('Nenhuma alteração foi gravada.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('atividades', [
            'id' => $atividade->id,
            'data_conclusao' => null,
        ]);
    }

    public function test_rejeita_data_de_corte_invalida(): void
    {
        $this->artisan('atividades:preencher-data-conclusao', ['data_corte' => '2026-02-30'])
            ->expectsOutput('A data de corte deve estar no formato AAAA-MM-DD e ser uma data válida.')
            ->assertExitCode(1);
    }
}
