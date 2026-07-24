<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Livewire\Admin\ColaboradorForm;
use App\Models\Colaborador;
use App\Models\User;
use App\Notifications\SendPasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ColaboradorRemuneracaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_colaborador_com_remuneracao_em_centavos(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(ColaboradorForm::class)
            ->call('openModal')
            ->set('nome', 'Colaborador Remunerado')
            ->set('email', 'remunerado@test.com')
            ->set('role', UserRole::Projetistas->value)
            ->set('contrato', 'clt')
            ->set('remuneracao', '1.245,60')
            ->call('save')
            ->assertHasNoErrors();

        $colaborador = Colaborador::query()->where('nome', 'Colaborador Remunerado')->first();

        $this->assertNotNull($colaborador);
        $this->assertSame(124560, $colaborador->remuneracao);
        Notification::assertSentTo($colaborador->user, SendPasswordResetNotification::class);
    }

    public function test_atualiza_remuneracao_do_colaborador(): void
    {
        $admin = $this->createAdmin();
        $colaborador = Colaborador::factory()->create([
            'remuneracao' => 100000,
        ]);

        Livewire::actingAs($admin)
            ->test(ColaboradorForm::class)
            ->call('openModal', $colaborador->id)
            ->assertSet('remuneracao', '1.000,00')
            ->set('remuneracao', '5.000,00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(500000, $colaborador->fresh()->remuneracao);
    }

    public function test_permite_salvar_sem_remuneracao(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(ColaboradorForm::class)
            ->call('openModal')
            ->set('nome', 'Sem Remuneracao')
            ->set('email', 'sem-remuneracao@test.com')
            ->set('role', UserRole::Levantadores->value)
            ->set('contrato', 'clt')
            ->set('remuneracao', '')
            ->call('save')
            ->assertHasNoErrors();

        $colaborador = Colaborador::query()->where('nome', 'Sem Remuneracao')->first();

        $this->assertNotNull($colaborador);
        $this->assertNull($colaborador->remuneracao);
    }

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin Remuneracao',
            'email' => 'admin-remuneracao@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Administrativos,
        ]);
    }
}
