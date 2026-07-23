<?php

namespace Tests\Feature\Painel;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\DashboardMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrativo_ve_dashboard_com_dados_globais(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = Colaborador::factory()->make([
            'id' => 1,
            'nome' => 'Colaborador Teste',
        ]);
        $colaborador->total_projetos = 2;
        $colaborador->total_extensao_desenho = 300;
        $colaborador->total_extensao_projeto = 130;
        $colaborador->meta_cad = '20 / 300';
        $colaborador->meta_proj = '0 / 230';
        $colaborador->total_postes = 20;
        $colaborador->total_bonus = 0.0;
        $colaborador->total_segundos = 10800;

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

        $projetoSemPartes = Projeto::factory()->make([
            'id' => 3,
            'nome' => 'Projeto Sem Partes',
            'created_at' => now()->subDay(),
        ]);
        $projetoSemPartes->total_extensao_projeto = 0;
        $projetoSemPartes->total_postes_projetados = 0;
        $projetoSemPartes->total_segundos = 0;

        $this->mockDashboardMetrics(
            totais: (object) [
                'totalProjetos' => 3,
                'totalExtensaoDesenho' => 300,
                'totalExtensaoProjeto' => 130,
                'totalPostesDesenhados' => 30,
                'totalPostesProjetados' => 20,
                'totalSegundos' => 10800,
            ],
            estatisticasProjetos: collect([$projetoRecente, $projetoSemPartes, $projetoAntigo]),
            produtividadeColaboradores: collect([$colaborador]),
        );

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSee('3', false);
        $response->assertSee('130', false);
        $response->assertSee('20', false);
        $response->assertSee($colaborador->nome);
        $response->assertSee('Projeto Recente', false);
        $response->assertSee('Projeto Antigo', false);
        $response->assertSee('Projeto Sem Partes', false);

        $posRecente = strpos($response->getContent(), 'Projeto Recente');
        $posAntigo = strpos($response->getContent(), 'Projeto Antigo');
        $this->assertNotFalse($posRecente);
        $this->assertNotFalse($posAntigo);
        $this->assertLessThan($posAntigo, $posRecente);
    }

    public function test_dashboard_exibe_meta_por_tipo_de_projeto_na_performance_de_colaborador(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = Colaborador::factory()->make([
            'id' => 1,
            'nome' => 'Colaborador Meta',
        ]);
        $colaborador->total_projetos = 1;
        $colaborador->total_extensao_desenho = 0;
        $colaborador->total_extensao_projeto = 0;
        $colaborador->meta_cad = '500 / 300';
        $colaborador->meta_proj = '300 / 230';
        $colaborador->total_postes = 800;
        $colaborador->total_bonus = 491.4;
        $colaborador->total_segundos = 7200;

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
            produtividadeColaboradores: collect([$colaborador]),
        );

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSee('Projetos CAD');
        $response->assertSee('Projetos PROJ');
        $response->assertSee('500 / 300');
        $response->assertSee('300 / 230');
    }

    public function test_coordenador_acessa_dashboard(): void
    {
        $this->mockDashboardMetrics();

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
     * @param  Collection<int, mixed>|null  $produtividadeColaboradores
     */
    private function mockDashboardMetrics(
        ?object $totais = null,
        ?Collection $estatisticasProjetos = null,
        ?Collection $produtividadeColaboradores = null,
    ): void {
        $this->mock(DashboardMetrics::class, function (MockInterface $mock) use (
            $totais,
            $estatisticasProjetos,
            $produtividadeColaboradores,
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
            $mock->shouldReceive('produtividadeColaboradores')->andReturn($produtividadeColaboradores ?? collect());
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
