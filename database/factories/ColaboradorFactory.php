<?php

namespace Database\Factories;

use App\Enums\TipoContrato;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Colaborador>
 */
class ColaboradorFactory extends Factory
{
    protected $model = Colaborador::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        $email = $this->faker->unique()->safeEmail();
        $role = $this->faker->randomElement([UserRole::Levantadores, UserRole::Projetistas, UserRole::Orcamentistas]);

        return [
            'nome' => $name,
            'contrato' => TipoContrato::CLT,
            'user_id' => User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
            ])->id,
        ];
    }

    // create a coordenador user
    public function coordenador(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::create([
                    'name' => $this->faker->name(),
                    'email' => $this->faker->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => UserRole::Coordenadores,
                ])->id,
            ];
        });
    }

    public function administrativo(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::create([
                    'name' => $this->faker->name(),
                    'email' => $this->faker->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => UserRole::Administrativos,
                ])->id,
            ];
        });
    }
}
