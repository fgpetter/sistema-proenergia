<?php

namespace Tests\Feature\Admin;

use App\Actions\CreateOrUpdateAtividade;
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
use SweetAlert2\Laravel\Swal;
use Tests\TestCase;

class ProjetoEditDrawerAtribuicaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_salvar_alteracoes_nao_remove_colaborador_e_exibe_toast_de_erro(): void
    {
        [$admin, $projeto, $atividade, $colaborador] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.nome', 'Nome Alterado')
            ->set('atividades.0.colaborador_id', '')
            ->call('save')
            ->assertHasErrors(['atividades.0.colaborador_id' => CreateOrUpdateAtividade::MENSAGEM_REMOCAO_ATRIBUICAO])
            ->assertDispatched(Swal::SESSION_KEY)
            ->assertNotDispatched('projeto-saved')
            ->assertSet('showDrawer', true);

        $atividade->refresh();

        $this->assertSame('Parte 01', $atividade->nome);
        $this->assertSame($colaborador->id, $atividade->colaborador_id);
    }

    public function test_salvar_atividade_nao_remove_colaborador_e_exibe_toast_de_erro(): void
    {
        [$admin, $projeto, $atividade, $colaborador] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.colaborador_id', '')
            ->call('saveAtividade', 0)
            ->assertHasErrors(['atividades.0.colaborador_id' => CreateOrUpdateAtividade::MENSAGEM_REMOCAO_ATRIBUICAO])
            ->assertDispatched(Swal::SESSION_KEY);

        $this->assertSame($colaborador->id, $atividade->fresh()->colaborador_id);
    }

    public function test_permite_criar_atividade_sem_colaborador(): void
    {
        [$admin, $projeto] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->call('addAtividade')
            ->set('atividades.1.nome', 'Parte sem atribuição')
            ->set('atividades.1.colaborador_id', '')
            ->call('saveAtividade', 1)
            ->assertHasNoErrors();

        $nova = Atividade::query()
            ->where('projeto_id', $projeto->id)
            ->where('nome', 'Parte sem atribuição')
            ->first();

        $this->assertNotNull($nova);
        $this->assertNull($nova->colaborador_id);
    }

    public function test_permite_trocar_colaborador_atribuido(): void
    {
        [$admin, $projeto, $atividade] = $this->criarCenarioComAtividade();
        $outro = Colaborador::factory()->projetista()->create();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.colaborador_id', (string) $outro->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('projeto-saved');

        $this->assertSame($outro->id, $atividade->fresh()->colaborador_id);
    }

    /**
     * @return array{0: User, 1: Projeto, 2: Atividade, 3: Colaborador}
     */
    private function criarCenarioComAtividade(): array
    {
        $admin = User::create([
            'name' => 'Admin Atribuicao',
            'email' => 'admin-atribuicao@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);

        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $atividade = Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte 01',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        return [$admin, $projeto, $atividade, $colaborador];
    }
}
