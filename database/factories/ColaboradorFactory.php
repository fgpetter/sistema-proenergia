<?php

namespace Database\Factories;

use App\Enums\TipoContrato;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Colaborador>
 */
class ColaboradorFactory extends Factory
{
    protected $model = Colaborador::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->faker->randomElement([
            UserRole::Levantadores,
            UserRole::Projetistas,
            UserRole::Orcamentistas,
        ]);

        return [
            'nome' => $this->faker->name(),
            'contrato' => TipoContrato::CLT,
            'user_id' => User::factory()->role($role),
        ];
    }

    public function projetista(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory()->role(UserRole::Projetistas),
        ]);
    }

    public function coordenador(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory()->role(UserRole::Coordenadores),
        ]);
    }

    public function administrativo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory()->role(UserRole::Administrativos),
        ]);
    }
}
