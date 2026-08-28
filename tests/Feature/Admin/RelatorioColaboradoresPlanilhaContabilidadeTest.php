<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Exports\PlanilhaContabilidadeExport;
use App\Livewire\Admin\RelatorioColaboradores;
use App\Livewire\Painel\PerformanceColaboradores;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\RelatorioColaboradoresProdutividade;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class RelatorioColaboradoresPlanilhaContabilidadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-17 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_seletor_vazio_exclui_projeto_de_competencia_antiga(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaboradorAtual = Colaborador::factory()->create(['nome' => 'Colaborador Atual']);
        $colaboradorAntigo = Colaborador::factory()->create(['nome' => 'Colaborador Antigo']);

        $projetoAtual = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ]);

        $projetoAntigo = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoAtual->id,
            'colaborador_id' => $colaboradorAtual->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoAntigo->id,
            'colaborador_id' => $colaboradorAntigo->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->assertSee('Colaborador Atual')
            ->assertDontSee('Colaborador Antigo');
    }

    public function test_mes_corrente_exclui_projeto_criado_no_mes_seguinte(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaboradorAgosto = Colaborador::factory()->create(['nome' => 'Agosto']);
        $colaboradorSetembro = Colaborador::factory()->create(['nome' => 'Maria Setembro Folha']);

        $projetoAgosto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-05 10:00:00',
            'updated_at' => '2026-08-05 10:00:00',
        ]);

        $projetoSetembro = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-09-01 10:00:00',
            'updated_at' => '2026-09-01 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoAgosto->id,
            'colaborador_id' => $colaboradorAgosto->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoSetembro->id,
            'colaborador_id' => $colaboradorSetembro->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-08')
            ->assertSee('Agosto')
            ->assertDontSee('Maria Setembro Folha');
    }

    public function test_mes_passado_inclui_atividade_lancada_depois_do_mes(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 320,
            'created_at' => '2026-09-01 10:00:00',
            'updated_at' => '2026-09-01 10:00:00',
        ]);

        $linhas = app(RelatorioColaboradoresProdutividade::class)->agregar(
            mesAno: '2026-06',
        );

        $this->assertCount(1, $linhas);
        $this->assertSame(320, (int) $linhas->first()->total_postes_projeto_cad);
    }

    public function test_limitar_ao_hoje_exclui_projeto_criado_apos_hoje(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projetoFuturo = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoFuturo->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 50,
        ]);

        $linhas = app(RelatorioColaboradoresProdutividade::class)->agregar(
            mesAno: '2026-08',
            limitarAoHoje: true,
        );

        $this->assertCount(0, $linhas);
    }

    public function test_admin_baixa_planilha_de_contabilidade(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->assertSee('Baixar planilha de contabilidade')
            ->call('exportarPlanilhaContabilidade')
            ->assertFileDownloaded('planilha-contabilidade-2026-08.xlsx');
    }

    public function test_coordenador_nao_pode_baixar_planilha_de_contabilidade(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();

        Livewire::actingAs($coordenador->user)
            ->test(RelatorioColaboradores::class)
            ->assertDontSee('Baixar planilha de contabilidade')
            ->call('exportarPlanilhaContabilidade')
            ->assertForbidden();
    }

    public function test_prestador_nao_pode_baixar_planilha_de_contabilidade(): void
    {
        $colaborador = Colaborador::factory()->create();
        $user = $colaborador->user;
        $user->update(['role' => UserRole::Projetistas]);

        Livewire::actingAs($user->fresh())
            ->test(RelatorioColaboradores::class)
            ->assertDontSee('Baixar planilha de contabilidade')
            ->call('exportarPlanilhaContabilidade')
            ->assertForbidden();
    }

    public function test_planilha_contabilidade_ignora_filtro_de_projeto(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaboradorA = Colaborador::factory()->create([
            'nome' => 'Colaborador A',
            'remuneracao' => 500000,
        ]);
        $colaboradorB = Colaborador::factory()->create([
            'nome' => 'Colaborador B',
            'remuneracao' => 500000,
        ]);

        $projetoA = Projeto::factory()->create([
            'nome' => 'Projeto A',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-05 10:00:00',
            'updated_at' => '2026-08-05 10:00:00',
        ]);

        $projetoB = Projeto::factory()->create([
            'nome' => 'Projeto B',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoA->id,
            'colaborador_id' => $colaboradorA->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 320,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoB->id,
            'colaborador_id' => $colaboradorB->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 320,
        ]);

        Excel::fake();

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('projetoId', $projetoA->id)
            ->call('exportarPlanilhaContabilidade');

        Excel::assertDownloaded('planilha-contabilidade-2026-08.xlsx', function (PlanilhaContabilidadeExport $export): bool {
            $linhas = $export->array();
            $nomes = array_column(array_slice($linhas, 1, -1), 1);

            $this->assertContains('Colaborador A', $nomes);
            $this->assertContains('Colaborador B', $nomes);

            return true;
        });
    }

    public function test_planilha_contabilidade_inclui_colaborador_excluido_com_atividade(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'nome' => 'Excluido Com Atividade',
            'remuneracao' => 500000,
        ]);

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-05 10:00:00',
            'updated_at' => '2026-08-05 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 320,
        ]);

        $colaborador->delete();

        Excel::fake();

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->call('exportarPlanilhaContabilidade');

        Excel::assertDownloaded('planilha-contabilidade-2026-08.xlsx', function (PlanilhaContabilidadeExport $export): bool {
            $linhas = $export->array();

            $this->assertSame(
                ['', 'Nome', 'VA', 'VT', 'Co-Participação Plano Saúde', 'Bonificações', 'Ajuda Custo Home', 'Prêmio Produtivid.', 'Horas Extras 50%', 'Horas Extras 70%', 'Horas Extras 130%', 'Faltas', 'Obs:'],
                $linhas[0],
            );
            $this->assertSame('Excluido Com Atividade', $linhas[1][1]);
            $this->assertSame(340.0, $linhas[1][7]);
            $this->assertSame('=SUM(H2:H2)', $linhas[2][7]);

            return true;
        });
    }

    public function test_planilha_contabilidade_tem_formula_sum_no_rodape(): void
    {
        $export = new PlanilhaContabilidadeExport([
            ['nome' => 'A', 'premio' => 100.0],
            ['nome' => 'B', 'premio' => 200.0],
        ]);

        $conteudo = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $caminho = tempnam(sys_get_temp_dir(), 'planilha-contabilidade-').'.xlsx';
        file_put_contents($caminho, $conteudo);

        try {
            $spreadsheet = IOFactory::load($caminho);
            $sheet = $spreadsheet->getActiveSheet();

            $this->assertSame('=SUM(H2:H3)', $sheet->getCell('H4')->getValue());
        } finally {
            @unlink($caminho);
        }
    }

    public function test_dashboard_performance_colaboradores_sem_mes_mantem_todas_competencias(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaboradorJunho = Colaborador::factory()->create(['nome' => 'Junho']);
        $colaboradorAgosto = Colaborador::factory()->create(['nome' => 'Agosto']);

        $projetoJunho = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        $projetoAgosto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoJunho->id,
            'colaborador_id' => $colaboradorJunho->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoAgosto->id,
            'colaborador_id' => $colaboradorAgosto->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        $linhas = app(RelatorioColaboradoresProdutividade::class)->agregar();

        $this->assertCount(2, $linhas);

        Livewire::test(PerformanceColaboradores::class, ['mesAno' => null])
            ->assertSee('Junho')
            ->assertSee('Agosto');
    }

    private function createUser(UserRole $role): User
    {
        return User::create([
            'name' => 'Usuário '.$role->value,
            'email' => $role->value.'-planilha@test.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
