<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoParte;
use App\Enums\UserRole;
use App\Livewire\Admin\RelatorioColaboradores;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RelatorioColaboradoresBonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_relatorio_soma_bonus_por_colaborador_no_mesmo_mes_sem_separar_projetos(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

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

        // Mesmo mês: (500-400)=100 → 182
        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee($colaborador->nome)
            ->assertSee('R$ 182,00')
            ->assertSee('Junho - 2026');
    }

    public function test_relatorio_exclui_projetos_de_outra_competencia(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

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
            'postes_projetados' => 200,
            'data_hora_inicio' => '2026-07-06 08:00:00',
            'data_hora_fim' => '2026-07-06 09:00:00',
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->assertSee('R$ 182,00')
            ->assertDontSee('R$ 72,80');
    }

    public function test_relatorio_respeita_filtro_de_projeto_no_bonus(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

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
            'postes_projetados' => 200,
            'data_hora_inicio' => '2026-06-16 08:00:00',
            'data_hora_fim' => '2026-06-16 09:00:00',
        ]);

        Livewire::actingAs($admin)
            ->test(RelatorioColaboradores::class)
            ->set('mesAno', '2026-06')
            ->set('projetoId', $projetoA->id)
            ->assertSee('R$ 182,00')
            ->assertDontSee('R$ 72,80');
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
