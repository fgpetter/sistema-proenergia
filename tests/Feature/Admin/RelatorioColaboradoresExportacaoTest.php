<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoParte;
use App\Enums\UserRole;
use App\Livewire\Admin\RelatorioColaboradores;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\RelatorioColaboradoresProdutividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class RelatorioColaboradoresExportacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_baixa_exportacao_com_competencia_selecionada(): void
    {
        [$user, $colaborador] = $this->createPrestadorComColaborador([
            'remuneracao' => 500000,
        ]);
        $coordenador = Colaborador::factory()->coordenador()->create();

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Exportacao',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte A',
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_projetados' => 320,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 10:30:00',
        ]);

        $this->mockAgregarParaTela($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 320,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 9000,
        ]);

        Livewire::actingAs($user)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->call('exportarProdutividade')
            ->assertHasNoErrors()
            ->assertFileDownloaded('exportacao-produtividade-2026-06.xlsx');
    }

    public function test_exportacao_exige_competencia(): void
    {
        [$user, $colaborador] = $this->createPrestadorComColaborador();

        $this->mockAgregarParaTela($colaborador, []);

        Livewire::actingAs($user)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', null)
            ->call('exportarProdutividade')
            ->assertHasErrors(['mesAno'])
            ->assertNoFileDownloaded();
    }

    public function test_admin_nao_pode_exportar_produtividade(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Administrativos,
        ]);

        $this->mockAgregarVazio();

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertDontSee('Baixar XLSX')
            ->call('exportarProdutividade')
            ->assertForbidden();
    }

    public function test_coordenador_nao_pode_exportar_produtividade(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();

        $this->mockAgregarVazio();

        Livewire::actingAs($coordenador->user)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertDontSee('Baixar XLSX')
            ->call('exportarProdutividade')
            ->assertForbidden();
    }

    public function test_exportacao_respeita_escopo_do_prestador_e_competencia(): void
    {
        [$user, $colaborador] = $this->createPrestadorComColaborador([
            'remuneracao' => 500000,
        ]);
        $outroColaborador = Colaborador::factory()->create();
        $coordenador = Colaborador::factory()->coordenador()->create();

        $projetoJunho = Projeto::factory()->create([
            'nome' => 'Projeto Junho',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        $projetoJulho = Projeto::factory()->create([
            'nome' => 'Projeto Julho',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoJunho->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte Propria',
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_projetados' => 50,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoJunho->id,
            'colaborador_id' => $outroColaborador->id,
            'nome' => 'Parte Alheia',
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_projetados' => 999,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoJulho->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte Outra Competencia',
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_projetados' => 80,
            'data_hora_inicio' => '2026-07-06 08:00:00',
            'data_hora_fim' => '2026-07-06 09:00:00',
        ]);

        $this->mockAgregarParaTela($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 50,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        Livewire::actingAs($user)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->call('exportarProdutividade')
            ->assertFileDownloaded('exportacao-produtividade-2026-06.xlsx');

        $partes = app(RelatorioColaboradoresProdutividade::class)->listarPartes(
            colaboradorId: $colaborador->id,
            mesAno: '2026-06',
        );

        $this->assertCount(1, $partes);
        $this->assertSame('Parte Propria', $partes->first()->nome);
    }

    public function test_prestador_ve_botao_de_exportacao(): void
    {
        [$user, $colaborador] = $this->createPrestadorComColaborador();

        $this->mockAgregarParaTela($colaborador, []);

        Livewire::actingAs($user)
            ->test(RelatorioColaboradores::class)
            ->assertSee('Baixar XLSX');
    }

    /**
     * @param  array{remuneracao?: int|null}  $atributosColaborador
     * @return array{0: User, 1: Colaborador}
     */
    private function createPrestadorComColaborador(array $atributosColaborador = []): array
    {
        $colaborador = Colaborador::factory()->create($atributosColaborador);
        $user = $colaborador->user;
        $user->update(['role' => UserRole::Projetistas]);

        return [$user->fresh(), $colaborador];
    }

    /**
     * @param  array{
     *     total_projetos?: int,
     *     total_postes_projetados_cad?: int,
     *     total_postes_projetados_proj?: int,
     *     total_segundos?: int
     * }  $atributos
     */
    private function mockAgregarParaTela(Colaborador $colaborador, array $atributos): void
    {
        $linha = Colaborador::factory()->make([
            'id' => $colaborador->id,
            'nome' => $colaborador->nome,
            'remuneracao' => $colaborador->remuneracao,
        ]);
        $linha->id = $colaborador->id;
        $linha->remuneracao = $colaborador->remuneracao;
        $linha->total_projetos = $atributos['total_projetos'] ?? 0;
        $linha->total_extensao_desenho = 0;
        $linha->total_extensao_projeto = 0;
        $linha->total_postes_desenhados = 0;
        $linha->total_postes_projetados = ($atributos['total_postes_projetados_cad'] ?? 0)
            + ($atributos['total_postes_projetados_proj'] ?? 0);
        $linha->total_postes_projetados_cad = $atributos['total_postes_projetados_cad'] ?? 0;
        $linha->total_postes_projetados_proj = $atributos['total_postes_projetados_proj'] ?? 0;
        $linha->total_segundos = $atributos['total_segundos'] ?? 0;

        $resultado = ($atributos === [])
            ? collect()
            : collect([$linha]);

        $this->partialMock(RelatorioColaboradoresProdutividade::class, function (MockInterface $mock) use ($resultado): void {
            $mock->shouldReceive('agregar')->andReturn($resultado);
        });
    }

    private function mockAgregarVazio(): void
    {
        $this->partialMock(RelatorioColaboradoresProdutividade::class, function (MockInterface $mock): void {
            $mock->shouldReceive('agregar')->andReturn(collect());
        });
    }
}
