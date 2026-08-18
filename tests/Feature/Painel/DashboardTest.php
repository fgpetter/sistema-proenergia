<?php

namespace Tests\Feature\Painel;

use App\Enums\UserRole;
use App\Livewire\Painel\Dashboard;
use App\Livewire\Painel\PerformanceColaboradores;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\RelatorioColaboradoresProdutividade;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_administrativo_ve_dashboard_com_dados_globais(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = $this->makeColaboradorProdutividade([
            'nome' => 'Colaborador Teste',
            'total_projetos' => 2,
            'total_extensao_desenho' => 300,
            'total_extensao_projeto' => 130,
            'total_postes_projeto_cad' => 20,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 10800,
            'remuneracao' => 500000,
        ]);

        Projeto::factory()->create([
            'nome' => 'Projeto Recente',
            'created_at' => '2026-08-17 10:00:00',
            'updated_at' => '2026-08-17 10:00:00',
        ]);
        Projeto::factory()->create([
            'nome' => 'Projeto Sem Atividades',
            'created_at' => '2026-08-16 10:00:00',
            'updated_at' => '2026-08-16 10:00:00',
        ]);
        Projeto::factory()->create([
            'nome' => 'Projeto Antigo',
            'created_at' => '2026-08-12 10:00:00',
            'updated_at' => '2026-08-12 10:00:00',
        ]);

        $this->mockProdutividadeAgregada($colaborador);

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSeeLivewire(Dashboard::class);
        $response->assertSeeLivewire(PerformanceColaboradores::class);
        $response->assertSee('3', false);
        $response->assertSee($colaborador->nome);
        $response->assertSee('Projeto Recente', false);
        $response->assertSee('Projeto Antigo', false);
        $response->assertSee('Projeto Sem Atividades', false);
        $response->assertSee('Mês/Ano', false);

        $posRecente = strpos($response->getContent(), 'Projeto Recente');
        $posAntigo = strpos($response->getContent(), 'Projeto Antigo');
        $this->assertNotFalse($posRecente);
        $this->assertNotFalse($posAntigo);
        $this->assertLessThan($posAntigo, $posRecente);
        $this->assertSame(1, substr_count($response->getContent(), 'Mês/Ano'));
    }

    public function test_dashboard_exibe_meta_por_tipo_de_projeto_na_performance_de_colaborador(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $colaborador = $this->makeColaboradorProdutividade([
            'nome' => 'Colaborador Meta',
            'total_projetos' => 1,
            'total_postes_desenho_cad' => 0,
            'total_postes_projeto_cad' => 500,
            'total_postes_projetados_proj' => 300,
            'total_segundos' => 7200,
            'remuneracao' => 500000,
        ]);

        $this->mockProdutividadeAgregada($colaborador);

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSeeLivewire(PerformanceColaboradores::class);
        $response->assertSee('Desenho CAD');
        $response->assertSee('Projeto CAD');
        $response->assertSee('Projeto PROJ');
        $response->assertSee('0 / 400');
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
            'total_postes_projeto_cad' => 2000,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
            'remuneracao' => 500000,
        ]);

        $this->mockProdutividadeAgregada($colaborador);

        Livewire::actingAs($admin)
            ->test(PerformanceColaboradores::class)
            ->assertSee('R$ 3.500,00')
            ->assertDontSee('R$ 3.700,00');
    }

    public function test_dashboard_usa_mes_calendario_atual_sem_query(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $this->mockProdutividadeAgregada();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSet('mesAno', '2026-08')
            ->assertSee('Agosto - 2026');
    }

    public function test_dashboard_mes_atual_sem_projeto_zera_cards_e_tabela(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $this->mockProdutividadeAgregada();

        Projeto::factory()->create([
            'nome' => 'Projeto Junho',
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSet('mesAno', '2026-08')
            ->assertSee('Agosto - 2026')
            ->assertSee('Nenhum projeto cadastrado foi encontrado.')
            ->assertDontSee('Projeto Junho');
    }

    public function test_dashboard_todas_as_competencias_permanece_na_url(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $this->mockProdutividadeAgregada();

        Projeto::factory()->create([
            'nome' => 'Projeto Junho',
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Livewire::actingAs($admin)
            ->withQueryParams(['mes' => 'todas'])
            ->test(Dashboard::class)
            ->assertSet('mesAno', 'todas')
            ->assertSee('Projeto Junho');

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->set('mesAno', 'todas')
            ->assertSet('mesAno', 'todas')
            ->assertSee('Projeto Junho');
    }

    public function test_dashboard_filtra_por_competencia_de_junho(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $this->mockProdutividadeAgregada();

        Projeto::factory()->create([
            'nome' => 'Projeto Junho',
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);
        Projeto::factory()->create([
            'nome' => 'Projeto Julho',
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->set('mesAno', '2026-06')
            ->assertSet('mesAno', '2026-06')
            ->assertSee('Junho - 2026')
            ->assertSee('Projeto Junho')
            ->assertDontSee('Projeto Julho');
    }

    public function test_dashboard_pagina_estatisticas_com_quinze_itens_e_reseta_ao_filtrar(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $this->mockProdutividadeAgregada();

        foreach (range(1, 16) as $indice) {
            Projeto::factory()->create([
                'nome' => sprintf('Projeto-%02d', $indice),
                'created_at' => Carbon::parse('2026-08-01 00:00:00')->addHours($indice),
                'updated_at' => Carbon::parse('2026-08-01 00:00:00')->addHours($indice),
            ]);
        }

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('Projeto-16')
            ->assertDontSee('Projeto-01')
            ->call('nextPage')
            ->assertSee('Projeto-01')
            ->set('mesAno', 'todas')
            ->assertSee('Projeto-16')
            ->assertDontSee('Projeto-01');
    }

    public function test_coordenador_acessa_dashboard(): void
    {
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
     * @param  array{
     *     id?: int,
     *     nome?: string,
     *     total_projetos?: int,
     *     total_extensao_desenho?: int,
     *     total_extensao_projeto?: int,
     *     total_postes_desenho_cad?: int,
     *     total_postes_projeto_cad?: int,
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
        $colaborador->total_postes_desenho_cad = $atributos['total_postes_desenho_cad'] ?? 0;
        $colaborador->total_postes_projeto_cad = $atributos['total_postes_projeto_cad'] ?? 0;
        $colaborador->total_postes_projetados_proj = $atributos['total_postes_projetados_proj'] ?? 0;
        $colaborador->total_postes_projetados = $colaborador->total_postes_desenho_cad
            + $colaborador->total_postes_projeto_cad
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
