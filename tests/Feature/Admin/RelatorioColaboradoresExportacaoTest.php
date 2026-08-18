<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Exports\ExportacaoProdutividadeExport;
use App\Livewire\Admin\RelatorioColaboradores;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\RelatorioColaboradoresProdutividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
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

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade A',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 320,
            'duracao_minutos' => 150,
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

        Atividade::factory()->create([
            'projeto_id' => $projetoJunho->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade Propria',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 50,
            'duracao_minutos' => 60,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoJunho->id,
            'colaborador_id' => $outroColaborador->id,
            'nome' => 'Atividade Alheia',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 999,
            'duracao_minutos' => 60,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoJulho->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade Outra Competencia',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 80,
            'duracao_minutos' => 60,
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

        $atividades = app(RelatorioColaboradoresProdutividade::class)->listarAtividades(
            colaboradorId: $colaborador->id,
            mesAno: '2026-06',
        );

        $this->assertCount(1, $atividades);
        $this->assertSame('Atividade Propria', $atividades->first()->nome);
    }

    public function test_exportacao_inclui_desenhados_no_detalhe_e_soma_na_meta_cad(): void
    {
        [$user, $colaborador] = $this->createPrestadorComColaborador([
            'remuneracao' => 500000,
        ]);
        $coordenador = Colaborador::factory()->coordenador()->create();

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Exportacao CAD',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade CAD',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 200,
            'postes_projetados' => 400,
            'duracao_minutos' => 120,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade PROJ',
            'tipo_projeto' => TipoProjetoAtividade::Proj,
            'postes_desenhados' => 50,
            'postes_projetados' => 230,
            'duracao_minutos' => 60,
        ]);

        $this->mockAgregarParaTela($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 600,
            'total_postes_projetados_proj' => 230,
            'total_segundos' => 10800,
        ]);

        Excel::fake();

        Livewire::actingAs($user)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->call('exportarProdutividade')
            ->assertHasNoErrors();

        Excel::assertDownloaded('exportacao-produtividade-2026-06.xlsx', function (ExportacaoProdutividadeExport $export): bool {
            $linhas = $export->array();

            $this->assertSame(
                ['Projeto', 'Atividade', 'Data', 'Tipo de Projeto', 'Postes Desenhados', 'Postes Projetados', 'Horas'],
                $linhas[0],
            );
            $this->assertSame('CAD', $linhas[1][3]);
            $this->assertSame(200, $linhas[1][4]);
            $this->assertSame(400, $linhas[1][5]);
            $this->assertSame(['Competência', 'Projetos', 'Postes CAD', 'Postes PROJ', 'Postes Total', 'Bônus'], $linhas[4]);
            $this->assertSame(600, $linhas[5][2]);
            $this->assertSame(230, $linhas[5][3]);
            $this->assertSame(830, $linhas[5][4]);

            return true;
        });
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
