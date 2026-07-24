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

        Parte::factory()->create([
            'projeto_id' => $projetoA->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 140,
            'postes_projetados' => 250,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 10:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoB->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 140,
            'postes_projetados' => 250,
            'data_hora_inicio' => '2026-06-21 08:00:00',
            'data_hora_fim' => '2026-06-21 09:00:00',
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 2,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 10800,
        ]);

        // Mesmo mês: (500-300)=200 → 364 (abaixo do teto de 30% de R$ 5.000)
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee($colaborador->nome)
            ->assertSee('R$ 364,00')
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

        Parte::factory()->create([
            'projeto_id' => $projetoJunho->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 280,
            'postes_projetados' => 500,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoJulho->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Proj,
            'postes_desenhados' => 280,
            'postes_projetados' => 300,
            'data_hora_inicio' => '2026-07-06 08:00:00',
            'data_hora_fim' => '2026-07-06 09:00:00',
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        // Junho CAD: (500-300)=200 → 364; julho PROJ (300-230)=70 → 127,40 não entra
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('R$ 364,00')
            ->assertDontSee('R$ 127,40');
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

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 100,
            'postes_projetados' => 500,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Proj,
            'postes_desenhados' => 50,
            'postes_projetados' => 300,
            'data_hora_inicio' => '2026-06-12 08:00:00',
            'data_hora_fim' => '2026-06-12 09:00:00',
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
            ->assertSee('Projetos CAD')
            ->assertSee('Projetos PROJ')
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

        Parte::factory()->create([
            'projeto_id' => $projetoA->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 280,
            'postes_projetados' => 500,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoB->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Proj,
            'postes_desenhados' => 280,
            'postes_projetados' => 300,
            'data_hora_inicio' => '2026-06-16 08:00:00',
            'data_hora_fim' => '2026-06-16 09:00:00',
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 500,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        // Projeto A CAD: (500-300)=200 → 364; B PROJ (300-230)=70 → 127,40 não entra
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->set('projetoId', $projetoA->id)
            ->assertSee('R$ 364,00')
            ->assertDontSee('R$ 127,40');
    }

    public function test_relatorio_limita_bonus_a_trinta_por_cento_da_remuneracao(): void
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

        // (1400-300)=1100 → 1100 * 1.82 = 2002 (acima do teto de R$ 1.500)
        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 100,
            'postes_projetados' => 1400,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
        ]);

        $this->mockProdutividadeAgregada($colaborador, [
            'total_projetos' => 1,
            'total_postes_projetados_cad' => 1400,
            'total_postes_projetados_proj' => 0,
            'total_segundos' => 3600,
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('R$ 1.500,00')
            ->assertDontSee('R$ 2.002,00');
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

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 100,
            'postes_projetados' => 500,
            'data_hora_inicio' => '2026-06-11 08:00:00',
            'data_hora_fim' => '2026-06-11 09:00:00',
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
            ->assertDontSee('R$ 364,00');
    }

    /**
     * Mocka a query de agregação (TIMESTAMPDIFF no MySQL) para manter testes estáveis no SQLite.
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
