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

class ProjetoEditDrawerCamposNumericosTest extends TestCase
{
    use RefreshDatabase;

    public function test_campos_numericos_nulos_nao_causam_excecao_ao_alterar(): void
    {
        [$admin, $projeto] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.extensao_desenho', null)
            ->set('atividades.0.extensao_projeto', null)
            ->set('atividades.0.postes_desenhados', null)
            ->set('atividades.0.postes_projetados', null)
            ->assertOk()
            ->assertSee('Total Extensão')
            ->assertSee('Total Postes');
    }

    public function test_campos_numericos_vazios_nao_causam_excecao_ao_alterar(): void
    {
        [$admin, $projeto] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.extensao_desenho', '')
            ->set('atividades.0.extensao_projeto', '')
            ->set('atividades.0.postes_desenhados', '')
            ->set('atividades.0.postes_projetados', '')
            ->assertOk()
            ->assertSee('Total Extensão')
            ->assertSee('Total Postes');
    }

    public function test_salvar_atividade_com_campos_numericos_nulos_grava_zero(): void
    {
        [$admin, $projeto, $atividade] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.extensao_desenho', null)
            ->set('atividades.0.extensao_projeto', null)
            ->set('atividades.0.postes_desenhados', null)
            ->set('atividades.0.postes_projetados', null)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $atividade->refresh();

        $this->assertSame(0, $atividade->extensao_desenho);
        $this->assertSame(0, $atividade->extensao_projeto);
        $this->assertSame(0, $atividade->postes_desenhados);
        $this->assertSame(0, $atividade->postes_projetados);
    }

    public function test_salvar_atividade_com_campos_numericos_vazios_grava_zero(): void
    {
        [$admin, $projeto, $atividade] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.extensao_desenho', '')
            ->set('atividades.0.extensao_projeto', '')
            ->set('atividades.0.postes_desenhados', '')
            ->set('atividades.0.postes_projetados', '')
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $atividade->refresh();

        $this->assertSame(0, $atividade->extensao_desenho);
        $this->assertSame(0, $atividade->extensao_projeto);
        $this->assertSame(0, $atividade->postes_desenhados);
        $this->assertSame(0, $atividade->postes_projetados);
    }

    public function test_campo_vazio_nao_zera_os_demais_ao_salvar(): void
    {
        [$admin, $projeto, $atividade] = $this->criarCenarioComAtividade();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.extensao_desenho', '')
            ->set('atividades.0.extensao_projeto', 50)
            ->set('atividades.0.postes_desenhados', null)
            ->set('atividades.0.postes_projetados', 12)
            ->call('saveAtividade', 0)
            ->assertHasNoErrors();

        $atividade->refresh();

        $this->assertSame(0, $atividade->extensao_desenho);
        $this->assertSame(50, $atividade->extensao_projeto);
        $this->assertSame(0, $atividade->postes_desenhados);
        $this->assertSame(12, $atividade->postes_projetados);
    }

    /**
     * @return array{0: User, 1: Projeto, 2: Atividade}
     */
    private function criarCenarioComAtividade(): array
    {
        $admin = User::create([
            'name' => 'Admin Campos Numericos',
            'email' => 'admin-campos-numericos@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);

        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $atividade = Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Parte 01',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'extensao_desenho' => 100,
            'extensao_projeto' => 80,
            'postes_desenhados' => 10,
            'postes_projetados' => 20,
        ]);

        return [$admin, $projeto, $atividade];
    }
}
