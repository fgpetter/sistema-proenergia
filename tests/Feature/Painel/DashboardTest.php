<?php

namespace Tests\Feature\Painel;

use App\Enums\TipoProjetoParte;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrativo_ve_dashboard_com_dados_globais(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projetoAntigo = Projeto::factory()->create([
            'nome' => 'Projeto Antigo',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => now()->subDays(5),
        ]);

        $projetoRecente = Projeto::factory()->create([
            'nome' => 'Projeto Recente',
            'colaborador_responsavel_id' => $coordenador->id,
            'created_at' => now(),
        ]);

        Projeto::factory()->create([
            'nome' => 'Projeto Sem Partes',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoAntigo->id,
            'colaborador_id' => $colaborador->id,
            'extensao_desenho' => 100,
            'extensao_projeto' => 50,
            'postes_desenhados' => 10,
            'postes_projetados' => 5,
            'data_hora_inicio' => now()->subHours(2),
            'data_hora_fim' => now(),
        ]);

        Parte::factory()->create([
            'projeto_id' => $projetoRecente->id,
            'colaborador_id' => $colaborador->id,
            'extensao_desenho' => 200,
            'extensao_projeto' => 80,
            'postes_desenhados' => 20,
            'postes_projetados' => 15,
            'data_hora_inicio' => now()->subHour(),
            'data_hora_fim' => now(),
        ]);

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
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_projetados' => 500,
            'data_hora_inicio' => now()->subHour(),
            'data_hora_fim' => now(),
        ]);

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Proj,
            'postes_projetados' => 300,
            'data_hora_inicio' => now()->subHour(),
            'data_hora_fim' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('painel.dashboard'));

        $response->assertOk();
        $response->assertSee('Meta');
        $response->assertSee('500/400 - 300/230');
    }

    public function test_coordenador_acessa_dashboard(): void
    {
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
