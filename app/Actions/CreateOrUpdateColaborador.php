<?php

namespace App\Actions;

use App\Enums\TipoContrato;
use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\User;
use App\Notifications\SendPasswordResetNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateOrUpdateColaborador
{
    public function create(
        string $nome,
        string $email,
        UserRole $role,
        TipoContrato $contrato
    ): Colaborador {
        return DB::transaction(function () use ($nome, $email, $role, $contrato) {
            $user = User::create([
                'name' => $nome,
                'email' => $email,
                'password' => Hash::make(Str::random(8)),
                'role' => $role,
            ]);

            $colaborador = Colaborador::create([
                'nome' => $nome,
                'contrato' => $contrato,
                'user_id' => $user->id,
            ]);

            $user->notify(new SendPasswordResetNotification);

            return $colaborador;
        });
    }

    public function update(
        Colaborador $colaborador,
        string $nome,
        UserRole $role,
        TipoContrato $contrato,
        int $userId
    ): Colaborador {
        return DB::transaction(function () use ($colaborador, $nome, $role, $contrato, $userId) {
            $colaborador->update([
                'nome' => $nome,
                'contrato' => $contrato,
                'user_id' => $userId,
            ]);

            $user = $colaborador->user;
            if ($user) {
                $updateData = ['name' => $nome];

                if ($user->role !== $role) {
                    $updateData['role'] = $role;
                }

                $user->update($updateData);
            }

            return $colaborador->fresh();
        });
    }
}
