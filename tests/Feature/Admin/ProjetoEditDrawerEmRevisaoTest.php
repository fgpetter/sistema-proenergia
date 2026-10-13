<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Livewire\Admin\ProjetoEditDrawer;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProjetoEditDrawerEmRevisaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marca_atividade_em_revisao(): void
    {
        [$admin, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador);

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.em_revisao', true)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $this->assertNotNull($atividade->fresh()->em_revisao);
    }

    public function test_admin_desmarca_revisao_e_atividade_volta_as_metricas(): void
    {
        [$admin, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador, emRevisao: Carbon::parse('2026-06-01 10:00:00'));

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->assertSet('atividades.0.em_revisao', true)
            ->set('atividades.0.em_revisao', false)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $this->assertNull($atividade->fresh()->em_revisao);
    }

    public function test_salvar_novamente_com_revisao_marcada_preserva_o_horario(): void
    {
        [$admin, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador, emRevisao: Carbon::parse('2026-06-01 10:00:00'));

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.observacoes', 'Ajuste de postes')
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $this->assertSame('2026-06-01 10:00:00', $atividade->fresh()->em_revisao->format('Y-m-d H:i:s'));
    }

    public function test_admin_cria_atividade_ja_em_revisao(): void
    {
        [$admin, $projeto, $colaborador] = $this->criarCenario();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->call('addAtividade')
            ->set('atividades.0.nome', 'Parte 01')
            ->set('atividades.0.colaborador_id', $colaborador->id)
            ->set('atividades.0.em_revisao', true)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $atividade = Atividade::query()->where('projeto_id', $projeto->id)->first();

        $this->assertNotNull($atividade->em_revisao);
    }

    public function test_colaborador_nao_ve_nem_altera_a_revisao(): void
    {
        [, $projeto, $colaborador] = $this->criarCenario();
        $atividade = $this->criarAtividade($projeto, $colaborador, emRevisao: Carbon::parse('2026-06-01 10:00:00'));

        Livewire::actingAs($colaborador->user)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->assertDontSee('em-revisao-0')
            ->set('atividades.0.em_revisao', false)
            ->set('atividades.0.duracao_horas', 1)
            ->set('atividades.0.duracao_minutos', 0)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $this->assertSame('2026-06-01 10:00:00', $atividade->fresh()->em_revisao->format('Y-m-d H:i:s'));
    }

    /**
     * @return array{0: User, 1: Projeto, 2: Colaborador}
     */
    private function criarCenario(): array
    {
        $admin = User::create([
            'name' => 'Admin Em Revisao',
            'email' => 'admin-em-revisao@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);

        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        return [$admin, $projeto, $colaborador];
    }

    private function criarAtividade(Projeto $projeto, Colaborador $colaborador, ?Carbon $emRevisao = null): Atividade
    {
        return Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte 01',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'em_revisao' => $emRevisao,
        ]);
    }
}
