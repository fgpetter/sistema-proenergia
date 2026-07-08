<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoProjetoParte;
use App\Enums\UserRole;
use App\Livewire\Admin\ProjetoEditDrawer;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProjetoEditDrawerBonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_drawer_nao_exibe_card_de_bonus_estimado_do_projeto(): void
    {
        $admin = User::create([
            'name' => 'Admin Bonus Drawer',
            'email' => 'admin-bonus-drawer@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);

        $coordenador = Colaborador::factory()->coordenador()->create();
        $colaborador = Colaborador::factory()->create();

        $projeto = Projeto::factory()->create([
            'colaborador_responsavel_id' => $coordenador->id,
        ]);

        Parte::factory()->create([
            'projeto_id' => $projeto->id,
            'colaborador_id' => $colaborador->id,
            'tipo_projeto' => TipoProjetoParte::Cad,
            'postes_desenhados' => 280,
            'postes_projetados' => 320,
        ]);

        Livewire::actingAs($admin)
            ->test(ProjetoEditDrawer::class)
            ->call('open', $projeto->id)
            ->assertDontSee('Bônus estimado do projeto')
            ->assertDontSee('A extensão não entra no cálculo do bônus.');
    }
}
