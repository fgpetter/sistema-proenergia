<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Livewire\Admin\ProjetoChecklist;
use App\Livewire\Admin\ProjetosList;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProjetoChecklistTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Admin Checklist',
            'email' => 'admin-checklist@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);
    }

    /**
     * @return array{admin: User, coordenador: Colaborador, colaborador: Colaborador, projeto: Projeto}
     */
    private function projetoComAtribuicao(): array
    {
        $admin = $this->adminUser();
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        return compact('admin', 'coordenador', 'colaborador', 'projeto');
    }

    public function test_guest_e_redirecionado_ao_login(): void
    {
        $projeto = Projeto::factory()->create();

        $this->get(route('admin.projetos.checklist', $projeto))
            ->assertRedirect('/login');
    }

    public function test_colaborador_sem_atribuicao_recebe_403(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $this->actingAs($colaborador->user)
            ->get(route('admin.projetos.checklist', $projeto))
            ->assertForbidden();
    }

    public function test_colaborador_atribuido_acessa_checklist(): void
    {
        $data = $this->projetoComAtribuicao();

        $this->actingAs($data['colaborador']->user)
            ->get(route('admin.projetos.checklist', $data['projeto']))
            ->assertOk()
            ->assertSee('CHECKLIST DE ANÁLISE DE PROJETOS — REDES URBANAS');
    }

    public function test_admin_acessa_checklist_com_tabela_urbana(): void
    {
        $data = $this->projetoComAtribuicao();

        $this->actingAs($data['admin'])
            ->get(route('admin.projetos.checklist', $data['projeto']))
            ->assertOk()
            ->assertSee('CHECKLIST DE ANÁLISE DE PROJETOS — REDES URBANAS')
            ->assertSee('Item a Verificar')
            ->assertSee('75 kW')
            ->assertSee('Reset')
            ->assertSee('name="conformidade-urbano-1"', false);
    }

    public function test_projeto_excluido_retorna_404(): void
    {
        $data = $this->projetoComAtribuicao();
        $data['projeto']->delete();

        $this->actingAs($data['admin'])
            ->get(route('admin.projetos.checklist', $data['projeto']))
            ->assertNotFound();
    }

    public function test_lista_exibe_botao_checklist_para_quem_pode_ver(): void
    {
        $data = $this->projetoComAtribuicao();

        Livewire::actingAs($data['admin'])
            ->test(ProjetosList::class)
            ->assertSee(route('admin.projetos.checklist', $data['projeto']), false)
            ->assertSee('title="Checklist"', false);
    }

    public function test_aba_rural_exibe_titulo_e_item_especifico(): void
    {
        $data = $this->projetoComAtribuicao();

        Livewire::actingAs($data['admin'])
            ->test(ProjetoChecklist::class, ['projeto' => $data['projeto']])
            ->call('setAba', ProjetoChecklist::ABA_RURAL)
            ->assertSee('CHECKLIST DE ANÁLISE DE PROJETOS — REDES RURAIS')
            ->assertSee('Redes rurais monofásicas (MRT) ou bifásicas: trecho de conexão com a concessionária em rede nua, demais trechos trifásicos em RDC?');
    }

    public function test_aba_urbana_tem_64_linhas_na_tabela(): void
    {
        $data = $this->projetoComAtribuicao();

        $html = Livewire::actingAs($data['admin'])
            ->test(ProjetoChecklist::class, ['projeto' => $data['projeto']])
            ->html();

        $this->assertSame(64, substr_count($html, 'data-tipo="conformidade"') / 3);
    }

    public function test_aba_rural_tem_72_linhas_na_tabela(): void
    {
        $data = $this->projetoComAtribuicao();

        $html = Livewire::actingAs($data['admin'])
            ->test(ProjetoChecklist::class, ['projeto' => $data['projeto']])
            ->call('setAba', ProjetoChecklist::ABA_RURAL)
            ->html();

        $this->assertSame(72, substr_count($html, 'data-tipo="conformidade"') / 3);
    }
}
