<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Livewire\Admin\ColaboradoresList;
use App\Livewire\Admin\ProjetoEditDrawer;
use App\Livewire\Admin\ProjetosList;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use App\Queries\DashboardMetrics;
use App\Queries\RelatorioColaboradoresProdutividade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ExclusaoSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_excluir_projeto_soft_deleta_projeto_e_atividades_em_cascata(): void
    {
        $admin = $this->createAdmin();
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $atividade = Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 100,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetosList::class)
            ->call('confirmDelete', $projeto->id)
            ->call('delete');

        $this->assertSoftDeleted('projetos', ['id' => $projeto->id]);
        $this->assertSoftDeleted('atividades', ['id' => $atividade->id]);
        $this->assertDatabaseHas('projetos', ['id' => $projeto->id]);
        $this->assertDatabaseHas('atividades', ['id' => $atividade->id]);

        $this->assertNull(Projeto::find($projeto->id));
        $this->assertNull(Atividade::find($atividade->id));

        $totais = app(DashboardMetrics::class)->totaisGlobais();
        $this->assertSame(0, $totais->totalProjetos);
        $this->assertSame(0, $totais->totalPostesProjetados);
    }

    public function test_excluir_atividade_soft_deleta_somente_a_atividade(): void
    {
        $admin = $this->createAdmin();
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $atividade = Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 80,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->call('confirmRemoveAtividade', 0)
            ->call('removeAtividadeConfirmed');

        $this->assertSoftDeleted('atividades', ['id' => $atividade->id]);
        $this->assertNotSoftDeleted('projetos', ['id' => $projeto->id]);

        $totais = app(DashboardMetrics::class)->totaisGlobais();
        $this->assertSame(0, $totais->totalProjetos);
        $this->assertSame(0, $totais->totalPostesProjetados);
    }

    public function test_excluir_colaborador_preserva_atribuicao_historica_e_corta_acesso(): void
    {
        $admin = $this->createAdmin();
        $coordenador = Colaborador::factory()->coordenador()->create(['nome' => 'Maria Coord']);
        $colaborador = Colaborador::factory()->projetista()->create(['nome' => 'Joao Projetista']);
        $user = $colaborador->user;
        $email = $user->email;
        $password = 'password';
        $user->forceFill(['password' => Hash::make($password)])->save();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $atividade = Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade Joao',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 200,
        ]);

        Livewire::actingAs($admin)
            ->test(ColaboradoresList::class)
            ->call('confirmDelete', $colaborador->id)
            ->call('delete');

        $this->assertSoftDeleted('colaboradores', ['id' => $colaborador->id]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNull(Colaborador::find($colaborador->id));
        $this->assertNull(User::find($user->id));

        $this->assertSame($colaborador->id, $atividade->fresh()->colaborador_id);
        $this->assertSame('Joao Projetista', $atividade->fresh()->colaborador->nome);
        $this->assertSame('Maria Coord', $projeto->fresh()->responsavel->nome);

        $this->assertFalse(Auth::attempt(['email' => $email, 'password' => $password]));

        $producao = app(DashboardMetrics::class)->producaoPorColaborador();
        $this->assertTrue($producao->contains(fn (object $linha): bool => $linha->nome === 'Joao Projetista' && $linha->total === 200));

        $relatorio = app(RelatorioColaboradoresProdutividade::class)->agregar();
        $this->assertTrue($relatorio->contains(fn (Colaborador $linha): bool => $linha->nome === 'Joao Projetista'));

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->assertSee('Joao Projetista')
            ->assertSet('atividades.0.colaborador_id', $colaborador->id);

        $component = Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id);

        $opcoes = $component->instance()->colaboradoresParaAtividades;
        $this->assertArrayHasKey($colaborador->id, $opcoes);

        $outroProjeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        $componentNovo = Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $outroProjeto->id)
            ->call('addAtividade');

        $opcoesNovo = $componentNovo->instance()->colaboradoresParaAtividades;
        $this->assertArrayNotHasKey($colaborador->id, $opcoesNovo);
    }

    public function test_nao_permite_nova_atribuicao_a_colaborador_excluido(): void
    {
        $admin = $this->createAdmin();
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaboradorAtivo = Colaborador::factory()->projetista()->create();
        $colaboradorExcluido = Colaborador::factory()->projetista()->create();
        $colaboradorExcluido->user->delete();
        $colaboradorExcluido->delete();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaboradorAtivo->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('atividades.0.colaborador_id', $colaboradorExcluido->id)
            ->call('save')
            ->assertHasErrors(['atividades.0.colaborador_id']);
    }

    public function test_permite_salvar_projeto_com_atribuicao_historica_de_colaborador_excluido(): void
    {
        $admin = $this->createAdmin();
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
            'nome' => 'Projeto Historico',
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'nome' => 'Atividade Historica',
            'tipo_projeto' => TipoProjetoAtividade::Cad,
            'postes_projetados' => 10,
        ]);

        $colaborador->user->delete();
        $colaborador->delete();

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->set('nome', 'Projeto Historico Atualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Projeto Historico Atualizado', $projeto->fresh()->nome);
        $this->assertSame($colaborador->id, $projeto->fresh()->atividades->first()->colaborador_id);
    }

    public function test_permite_excluir_colaborador_que_e_responsavel_de_projeto_vivo(): void
    {
        $admin = $this->createAdmin();
        $coordenador = Colaborador::factory()->coordenador()->create(['nome' => 'Coord Excluido']);

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ColaboradoresList::class)
            ->call('confirmDelete', $coordenador->id)
            ->call('delete');

        $this->assertSoftDeleted('colaboradores', ['id' => $coordenador->id]);
        $this->assertSame($coordenador->id, $projeto->fresh()->colaborador_responsavel_id);
        $this->assertSame('Coord Excluido', $projeto->fresh()->responsavel->nome);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Soft Delete',
            'email' => 'admin-soft-delete@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);
    }
}
