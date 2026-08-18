<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Livewire\Admin\ProjetoEditDrawer;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProjetoEditDrawerDuracaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cria_atividade_sem_duracao(): void
    {
        [$admin, $projeto, $colaborador] = $this->criarCenario();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->call('addAtividade')
            ->set('atividades.0.nome', 'Parte 01')
            ->set('atividades.0.colaborador_id', $colaborador->id)
            ->set('atividades.0.duracao_horas', 1)
            ->set('atividades.0.duracao_minutos', 30)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $atividade = Atividade::query()->where('projeto_id', $projeto->id)->first();

        $this->assertNotNull($atividade);
        $this->assertNull($atividade->duracao_minutos);
    }

    public function test_colaborador_nao_salva_duracao_zero(): void
    {
        [, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador);

        Livewire::actingAs($colaborador->user)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.duracao_horas', 0)
            ->set('atividades.0.duracao_minutos', 0)
            ->call('saveAtividade', 0)
            ->assertHasErrors(['atividades.0.duracao_horas']);

        $this->assertNull($atividade->fresh()->duracao_minutos);
    }

    public function test_colaborador_salva_uma_hora_e_trinta_minutos(): void
    {
        [, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador);

        Livewire::actingAs($colaborador->user)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.duracao_horas', 1)
            ->set('atividades.0.duracao_minutos', 30)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $this->assertSame(90, $atividade->fresh()->duracao_minutos);
    }

    public function test_colaborador_pode_alterar_duracao_depois_do_primeiro_lancamento(): void
    {
        [, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador, duracaoMinutos: 90);

        Livewire::actingAs($colaborador->user)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->assertSet('atividades.0.duracao_horas', 1)
            ->assertSet('atividades.0.duracao_minutos', 30)
            ->set('atividades.0.duracao_horas', 2)
            ->set('atividades.0.duracao_minutos', 0)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $this->assertSame(120, $atividade->fresh()->duracao_minutos);
    }

    public function test_minutos_acima_de_59_sao_invalidos(): void
    {
        [, $projeto, $colaborador] = $this->criarCenario();
        $this->criarAtividade($projeto, $colaborador);

        Livewire::actingAs($colaborador->user)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.duracao_horas', 0)
            ->set('atividades.0.duracao_minutos', 90)
            ->call('saveAtividade', 0)
            ->assertHasErrors(['atividades.0.duracao_minutos']);
    }

    /**
     * @return array{0: User, 1: Projeto, 2: Colaborador}
     */
    private function criarCenario(): array
    {
        $admin = User::create([
            'name' => 'Admin Duracao',
            'email' => 'admin-duracao@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);

        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        return [$admin, $projeto, $colaborador];
    }

    private function criarAtividade(Projeto $projeto, Colaborador $colaborador, ?int $duracaoMinutos = null): Atividade
    {
        return Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte 01',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'duracao_minutos' => $duracaoMinutos,
        ]);
    }
}
