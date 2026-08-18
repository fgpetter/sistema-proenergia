<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Livewire\Admin\RelatorioColaboradores;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\RelatorioColaboradoresProdutividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class RelatorioColaboradoresBonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_relatorio_soma_bonus_por_colaborador_no_mesmo_mes_sem_separar_projetos(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 500000,
        ]);

        $projetoA = Projeto::factory()->create([
            'nome' => 'Projeto A',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        $projetoB = Projeto::factory()->create([
            'nome' => 'Projeto B',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-20 10:00:00',
            'updated_at' => '2026-06-20 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoA->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 140,
            'postes_projetados' => 250,
            'duracao_minutos' => 120,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoB->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 140,
            'postes_projetados' => 250,
            'duracao_minutos' => 60,
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 2,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 10800,
        ]);

        // Mesmo mês: 300 + (500-300)*2 = 700 (abaixo do teto de 70% de R$ 5.000)
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee($colaborador->nome)
            ->assertSee('R$ 700,00')
            ->assertSee('Junho - 2026');
    }

    public function test_relatorio_exclui_projetos_de_outra_competencia(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 500000,
        ]);

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
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 280,
            'postes_projetados' => 500,
            'duracao_minutos' => 60,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoJulho->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Proj,
            'postes_desenhados' => 280,
            'postes_projetados' => 300,
            'duracao_minutos' => 60,
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        // Junho CAD: 300 + 200*2 = 700; julho PROJ 300 + 70*2 = 440 não entra
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('R$ 700,00')
            ->assertDontSee('R$ 440,00');
    }

    public function test_relatorio_exibe_meta_por_tipo_de_projeto(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 500000,
        ]);

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Meta',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 100,
            'postes_projetados' => 500,
            'duracao_minutos' => 60,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Proj,
            'postes_desenhados' => 50,
            'postes_projetados' => 300,
            'duracao_minutos' => 60,
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 300,
            'total_segundos' => 7200,
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('Postes CAD')
            ->assertSee('Postes PROJ')
            ->assertSee('500 / 300')
            ->assertSee('300 / 230');
    }

    public function test_relatorio_respeita_filtro_de_projeto_no_bonus(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 500000,
        ]);

        $projetoA = Projeto::factory()->create([
            'nome' => 'Projeto Filtrado A',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        $projetoB = Projeto::factory()->create([
            'nome' => 'Projeto Filtrado B',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-15 10:00:00',
            'updated_at' => '2026-06-15 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoA->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 280,
            'postes_projetados' => 500,
            'duracao_minutos' => 60,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoB->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Proj,
            'postes_desenhados' => 280,
            'postes_projetados' => 300,
            'duracao_minutos' => 60,
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        // Projeto A CAD: 300 + 200*2 = 700; B PROJ 300 + 70*2 = 440 não entra
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->set('projetoId', $projetoA->id)
            ->assertSee('R$ 700,00')
            ->assertDontSee('R$ 440,00');
    }

    public function test_relatorio_limita_bonus_a_setenta_por_cento_da_remuneracao(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 500000,
        ]);

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Teto',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        // 300 + (2000-300)*2 = 3700 (acima do teto de R$ 3.500)
        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 100,
            'postes_projetados' => 2000,
            'duracao_minutos' => 60,
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 2000,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('R$ 3.500,00')
            ->assertDontSee('R$ 3.700,00');
    }

    public function test_relatorio_zera_bonus_quando_remuneracao_ausente(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => null,
        ]);

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Sem Remuneracao',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 100,
            'postes_projetados' => 500,
            'duracao_minutos' => 60,
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('R$ 0,00')
            ->assertDontSee('R$ 700,00');
    }

    public function test_relatorio_inclui_postes_desenhados_na_meta_cad_e_ignora_em_proj(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 500000,
        ]);

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto CAD Desenhados',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_desenhados' => 200,
            'postes_projetados' => 400,
            'duracao_minutos' => 60,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Proj,
            'postes_desenhados' => 50,
            'postes_projetados' => 230,
            'duracao_minutos' => 60,
        ]);

        $agregado = app(RelatorioColaboradoresProdutividade::class)
            ->agregar(mesAno: '2026-06')
            ->first();

        $this->assertNotNull($agregado);
        $this->assertSame(600, (int) $agregado->total_postes_projetados_cad);
        $this->assertSame(230, (int) $agregado->total_postes_projetados_proj);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee($colaborador->nome)
            ->assertSee('600 / 300')
            ->assertSee('230 / 230')
            ->assertSee('830')
            ->assertSee('R$ 900,00');
    }

    public function test_agregar_soma_duracao_em_segundos(): void
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
            'duracao_minutos' => 90,
        ]);
        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'duracao_minutos' => 30,
        ]);

        $linhas = app(RelatorioColaboradoresProdutividade::class)->agregar(
            colaboradorId: $colaborador->id,
            mesAno: '2026-06',
        );

        $this->assertSame(7200, (int) $linhas->first()->total_segundos);
    }

    /**
     * Mocka a query de agregação para isolar o cálculo de bônus na tela.
     *
     * @param  array{
     *     total_projetos?: int,
     *     total_postes_projetados_cad?: int,
     *     total_postes_projetados_proj?: int,
     *     total_segundos?: int
     * }  $atributos
     */
    private function mockProdutividadeAgregada(Colaborador $colaborador, array $atributos): void
    {
        $linha = Colaborador::factory()->make([
            'id' => $colaborador->id,
            'nome' => $colaborador->nome,
            'remuneracao' => $colaborador->remuneracao,
        ]);
        $linha->id = $colaborador->id;
        $linha->remuneracao = $colaborador->remuneracao;
        $linha->total_projetos = $atributos['total_projetos'] ?? 1;
        $linha->total_extensao_desenho = 0;
        $linha->total_extensao_projeto = 0;
        $linha->total_postes_desenhados = 0;
        $linha->total_postes_projetados = ($atributos['total_postes_projetados_cad'] ?? 0)
            + ($atributos['total_postes_projetados_proj'] ?? 0);
        $linha->total_postes_projetados_cad = $atributos['total_postes_projetados_cad'] ?? 0;
        $linha->total_postes_projetados_proj = $atributos['total_postes_projetados_proj'] ?? 0;
        $linha->total_segundos = $atributos['total_segundos'] ?? 0;

        $this->mock(RelatorioColaboradoresProdutividade::class, function (MockInterface $mock) use ($linha): void {
            $mock->shouldReceive('agregar')->andReturn(collect([$linha]));
        });
    }

    private function createUser(UserRole $role): User
    {
        return User::create([
            'name' => 'Usuário '.$role->value,
            'email' => $role->value.'-bonus@test.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
