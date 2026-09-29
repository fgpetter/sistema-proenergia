<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Livewire\Admin\ProjetosList;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProjetosListTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtra_projetos_por_responsavel(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenadorA = Colaborador::factory()->coordenador()->create(['nome' => 'Coord A']);
        $coordenadorB = Colaborador::factory()->coordenador()->create(['nome' => 'Coord B']);

        $projetoA = Projeto::factory()->create([
            'nome' => 'Projeto Coord A',
            'colaborador_responsavel_id' => $coordenadorA->id,
        ]);
        $projetoB = Projeto::factory()->create([
            'nome' => 'Projeto Coord B',
            'colaborador_responsavel_id' => $coordenadorB->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetosList::class)
            ->set('responsavelId', $coordenadorA->id)
            ->assertSee($projetoA->nome)
            ->assertDontSee($projetoB->nome);
    }

    public function test_filtra_projetos_por_colaborador_atribuido_a_atividade(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaboradorA = Colaborador::factory()->projetista()->create(['nome' => 'Ana Projetista']);
        $colaboradorB = Colaborador::factory()->projetista()->create(['nome' => 'Bruno Projetista']);

        $projetoA = Projeto::factory()->create([
            'nome' => 'Projeto Ana',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);
        $projetoB = Projeto::factory()->create([
            'nome' => 'Projeto Bruno',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoA->id,
            'colaborador_id' => $colaboradorA->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);
        Atividade::factory()->create([
            'projeto_id' => $projetoB->id,
            'colaborador_id' => $colaboradorB->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetosList::class)
            ->set('colaboradorId', $colaboradorA->id)
            ->assertSee($projetoA->nome)
            ->assertDontSee($projetoB->nome);
    }

    public function test_combina_filtros_de_responsavel_e_colaborador(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenadorA = Colaborador::factory()->coordenador()->create();
        $coordenadorB = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projetoMatch = Projeto::factory()->create([
            'nome' => 'Projeto Match',
            'colaborador_responsavel_id' => $coordenadorA->id,
        ]);
        $projetoOutroResponsavel = Projeto::factory()->create([
            'nome' => 'Projeto Outro Responsavel',
            'colaborador_responsavel_id' => $coordenadorB->id,
        ]);
        $projetoSemColaborador = Projeto::factory()->create([
            'nome' => 'Projeto Sem Colaborador',
            'colaborador_responsavel_id' => $coordenadorA->id,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoMatch->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);
        Atividade::factory()->create([
            'projeto_id' => $projetoOutroResponsavel->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);
        Atividade::factory()->create([
            'projeto_id' => $projetoSemColaborador->id,
            'colaborador_id' => null,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetosList::class)
            ->set('responsavelId', $coordenadorA->id)
            ->set('colaboradorId', $colaborador->id)
            ->assertSee($projetoMatch->nome)
            ->assertDontSee($projetoOutroResponsavel->nome)
            ->assertDontSee($projetoSemColaborador->nome);
    }

    public function test_filtro_por_colaborador_ignora_atividade_sem_atribuicao(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->projetista()->create();

        $projetoComAtribuicao = Projeto::factory()->create([
            'nome' => 'Projeto Com Atribuicao',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);
        $projetoSemAtribuicao = Projeto::factory()->create([
            'nome' => 'Projeto Sem Atribuicao',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoComAtribuicao->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);
        Atividade::factory()->create([
            'projeto_id' => $projetoSemAtribuicao->id,
            'colaborador_id' => null,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetosList::class)
            ->set('colaboradorId', $colaborador->id)
            ->assertSee($projetoComAtribuicao->nome)
            ->assertDontSee($projetoSemAtribuicao->nome);
    }

    public function test_alterar_filtro_reseta_paginacao(): void
    {
        $admin = $this->createUser(UserRole::Administrativos);
        $coordenador = Colaborador::factory()->coordenador()->create();

        foreach (range(1, 11) as $indice) {
            Projeto::factory()->create([
                'nome' => sprintf('Projeto-%02d', $indice),
                'colaborador_responsavel_id' => $coordenador->id,
            ]);
        }

        Livewire::actingAs($admin)
            ->test(ProjetosList::class)
            ->assertSee('Projeto-01')
            ->assertDontSee('Projeto-11')
            ->call('nextPage')
            ->assertSee('Projeto-11')
            ->assertDontSee('Projeto-01')
            ->set('responsavelId', $coordenador->id)
            ->assertSee('Projeto-01')
            ->assertDontSee('Projeto-11');
    }

    public function test_filtro_nao_amplia_visibilidade_de_prestador(): void
    {
        $coordenador = Colaborador::factory()->coordenador()->create();
        $prestador = Colaborador::factory()->projetista()->create();
        $outroColaborador = Colaborador::factory()->projetista()->create();

        $projetoDoPrestador = Projeto::factory()->create([
            'nome' => 'Projeto Do Prestador',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);
        $projetoAlheio = Projeto::factory()->create([
            'nome' => 'Projeto Alheio',
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Atividade::factory()->create([
            'projeto_id' => $projetoDoPrestador->id,
            'colaborador_id' => $prestador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);
        Atividade::factory()->create([
            'projeto_id' => $projetoAlheio->id,
            'colaborador_id' => $outroColaborador->id,
            'tipo_projeto' => TipoProjetoAtividade::Cad,
        ]);

        Livewire::actingAs($prestador->user)
            ->test(ProjetosList::class)
            ->set('colaboradorId', $outroColaborador->id)
            ->assertDontSee($projetoAlheio->nome)
            ->assertDontSee($projetoDoPrestador->nome)
            ->set('colaboradorId', $prestador->id)
            ->assertSee($projetoDoPrestador->nome)
            ->assertDontSee($projetoAlheio->nome);
    }

    private function createUser(UserRole $role): User
    {
        return User::create([
            'name' => 'Usuário '.$role->value,
            'email' => $role->value.'-projetos-list@test.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
