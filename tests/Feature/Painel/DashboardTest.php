<?php

namespace Tests\Feature\Painel;

use App\Enums\UserRole;
use App\Livewire\Painel\PerformanceColaboradores;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\DashboardMetrics;
use App\Queries\RelatorioColaboradoresProdutividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrativo_ve_dashboard_com_dados_globais(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = $this->makeColaboradorProdutividade([
            'nome' => 'Colaborador Teste',
            'total_projetos' => 2,
            'total_extensao_desenho' => 300,
            'total_extensao_projeto' => 130,
            'total_postes_projetados_cad' => 20,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 10800,
            'remuneracao' => 500000,
        ]);

        $projetoRecente = Projeto::factory()->make([
            'id' => 1,
            'nome' => 'Projeto Recente',
            'created_at' => now(),
        ]);
        $projetoRecente->total_extensao_projeto = 80;
        $projetoRecente->total_postes_projetados = 15;
        $projetoRecente->total_segundos = 3600;

        $projetoAntigo = Projeto::factory()->make([
            'id' => 2,
            'nome' => 'Projeto Antigo',
            'created_at' => now()->subDays(5),
        ]);
        $projetoAntigo->total_extensao_projeto = 50;
        $projetoAntigo->total_postes_projetados = 5;
        $projetoAntigo->total_segundos = 7200;

        $projetoSemAtividades = Projeto::factory()->make([
            'id' => 3,
            'nome' => 'Projeto Sem Atividades',
            'created_at' => now()->subDay(),
        ]);
        $projetoSemAtividades->total_extensao_projeto = 0;
        $projetoSemAtividades->total_postes_projetados = 0;
        $projetoSemAtividades->total_segundos = 0;

        $this->mockDashboardMetrics(
            totais: (object) [
                'totalProjetos' => 3,
                'totalExtensaoDesenho' => 300,
                'totalExtensaoProjeto' => 130,
                'totalPostesDesenhados' => 30,
                'totalPostesProjetados' => 20,
                'totalSegundos' => 10800,
            ],
            estatisticasProjetos: collect([$projetoRecente, $projetoSemAtividades, $projetoAntigo]),
        );
        $this->mockProdutividadeAgregada($colaborador);

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSeeLivewire(PerformanceColaboradores::class);
        $response->assertSee('3', false);
        $response->assertSee('130', false);
        $response->assertSee('20', false);
        $response->assertSee($colaborador->nome);
        $response->assertSee('Projeto Recente', false);
        $response->assertSee('Projeto Antigo', false);
        $response->assertSee('Projeto Sem Atividades', false);

        $posRecente = strpos($response->getContent(), 'Projeto Recente');
        $posAntigo = strpos($response->getContent(), 'Projeto Antigo');
        $this->assertNotFalse($posRecente);
        $this->assertNotFalse($posAntigo);
        $this->assertLessThan($posAntigo, $posRecente);
    }

    public function test_dashboard_exibe_meta_por_tipo_de_projeto_na_performance_de_colaborador(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = $this->makeColaboradorProdutividade([
            'nome' => 'Colaborador Meta',
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 300,
            'total_segundos' => 7200,
            'remuneracao' => 500000,
        ]);

        $this->mockDashboardMetrics(
            totais: (object) [
                'totalProjetos' => 1,
                'totalExtensaoDesenho' => 0,
                'totalExtensaoProjeto' => 0,
                'totalPostesDesenhados' => 0,
                'totalPostesProjetados' => 800,
                'totalSegundos' => 7200,
            ],
            estatisticasProjetos: collect(),
        );
        $this->mockProdutividadeAgregada($colaborador);

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSeeLivewire(PerformanceColaboradores::class);
        $response->assertSee('Postes CAD');
        $response->assertSee('Postes PROJ');
        $response->assertSee('500 / 300');
        $response->assertSee('300 / 230');
        $response->assertSee('R$ 840,00');
    }

    public function test_dashboard_aplica_teto_de_setenta_por_cento_da_remuneracao(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = $this->makeColaboradorProdutividade([
            'nome' => 'Colaborador Teto',
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 2000,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
            'remuneracao' => 500000,
        ]);

        $this->mockDashboardMetrics();
        $this->mockProdutividadeAgregada($colaborador);

        Livewire::actingAs($admin)
            ->test(PerformanceColaboradores::class)
            ->assertSee('R$ 3.500,00')
            ->assertDontSee('R$ 3.700,00');
    }

    public function test_dashboard_filtra_performance_por_competencia(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);

        Projeto::factory()->create([
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        $this->mockDashboardMetrics();
        $this->mock(RelatorioColaboradoresProdutividade::class, function (MockInterface $mock): void {
            $mock->shouldReceive('agregar')
                ->withArgs(fn (
                    ?int $colaboradorId = null,
                    ?int $projetoId = null,
                    ?string $mesAno = null,
                    ?int $coordenadorId = null,
                ): bool => $colaboradorId === null
                    && $projetoId === null
                    && $coordenadorId === null
                    && ($mesAno === null || $mesAno === '2026-06'))
                ->andReturn(collect());
        });

        Livewire::actingAs($admin)
            ->test(PerformanceColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSet('mesAno', '2026-06')
            ->assertSee('Junho - 2026');
    }

    public function test_coordenador_acessa_dashboard(): void
    {
        $this->mockDashboardMetrics();
        $this->mockProdutividadeAgregada();

        $coordenadorUser = User::create([
            'name' => 'Coordenador',
            'email' => 'coord@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Coordenadores,
        ]);

        $response = $this->actingAs($coordenadorUser)->get(route('painel.dashboard'));

        $response->assertOk();
    }

    public function test_prestador_recebe_403_no_dashboard(): void
    {
        $prestador = User::create([
            'name' => 'Prestador',
            'email' => 'prestador@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Projetistas,
        ]);

        $response = $this->actingAs($prestador)->get(route('painel.dashboard'));

        $response->assertForbidden();
    }

    public function test_raiz_redireciona_prestador_para_projetos(): void
    {
        $prestador = User::create([
            'name' => 'Prestador',
            'email' => 'prestador2@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Projetistas,
        ]);

        $response = $this->actingAs($prestador)->get('/');

        $response->assertRedirect(route('admin.projetos'));
    }

    public function test_raiz_redireciona_admin_para_dashboard(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('painel.dashboard'));
    }

    /**
     * @param  Collection<int, mixed>|null  $estatisticasProjetos
     */
    private function mockDashboardMetrics(
        ?object $totais = null,
        ?Collection $estatisticasProjetos = null,
    ): void {
        $this->mock(DashboardMetrics::class, function (MockInterface $mock) use (
            $totais,
            $estatisticasProjetos,
        ): void {
            $mock->shouldReceive('totaisGlobais')->andReturn($totais ?? (object) [
                'totalProjetos' => 0,
                'totalExtensaoDesenho' => 0,
                'totalExtensaoProjeto' => 0,
                'totalPostesDesenhados' => 0,
                'totalPostesProjetados' => 0,
                'totalSegundos' => 0,
            ]);
            $mock->shouldReceive('estatisticasPorProjeto')->andReturn($estatisticasProjetos ?? collect());
        });
    }

    /**
     * @param  array{
     *     nome?: string,
     *     total_projetos?: int,
     *     total_extensao_desenho?: int,
     *     total_extensao_projeto?: int,
     *     total_postes_projetados_cad?: int,
     *     total_postes_projetados_proj?: int,
     *     total_segundos?: int,
     *     remuneracao?: int|null
     * }  $atributos
     */
    private function makeColaboradorProdutividade(array $atributos = []): Colaborador
    {
        $colaborador = Colaborador::factory()->make([
            'id' => $atributos['id'] ?? 1,
            'nome' => $atributos['nome'] ?? 'Colaborador',
            'remuneracao' => $atributos['remuneracao'] ?? 500000,
        ]);
        $colaborador->id = $atributos['id'] ?? 1;
        $colaborador->remuneracao = $atributos['remuneracao'] ?? 500000;
        $colaborador->total_projetos = $atributos['total_projetos'] ?? 1;
        $colaborador->total_extensao_desenho = $atributos['total_extensao_desenho'] ?? 0;
        $colaborador->total_extensao_projeto = $atributos['total_extensao_projeto'] ?? 0;
        $colaborador->total_postes_projetados_cad = $atributos['total_postes_projetados_cad'] ?? 0;
        $colaborador->total_postes_projetados_proj = $atributos['total_postes_projetados_proj'] ?? 0;
        $colaborador->total_postes_projetados = $colaborador->total_postes_projetados_cad
            + $colaborador->total_postes_projetados_proj;
        $colaborador->total_segundos = $atributos['total_segundos'] ?? 0;

        return $colaborador;
    }

    private function mockProdutividadeAgregada(?Colaborador $colaborador = null): void
    {
        $linhas = $colaborador !== null ? collect([$colaborador]) : collect();

        $this->mock(RelatorioColaboradoresProdutividade::class, function (MockInterface $mock) use ($linhas): void {
            $mock->shouldReceive('agregar')->andReturn($linhas);
        });
    }

    private function createUser(UserRole $role): User
    {
        return User::create([
            'name' => 'Usuário '.$role->value,
            'email' => $role->value.'@test.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
