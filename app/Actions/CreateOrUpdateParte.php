<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Models\Parte;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateParte
{
    public function create(int $projetoId, string $nome, ?int $colaboradorId, array $dados, User $user): Parte
    {
        return DB::transaction(function () use ($projetoId, $nome, $colaboradorId, $dados) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            // Apenas o colaborador atribuído pode preencher o primeiro par de datas; criação de parte é feita por admin/coordenador.
            unset($dados['data_hora_inicio'], $dados['data_hora_fim']);

            return Parte::create([
                'projeto_id' => $projetoId,
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $dados['extensao_desenho'] ?? 0,
                'extensao_projeto' => $dados['extensao_projeto'] ?? 0,
                'postes_desenhados' => $dados['postes_desenhados'] ?? 0,
                'postes_projetados' => $dados['postes_projetados'] ?? 0,
            ]);
        });
    }

    public function update(Parte $parte, string $nome, ?int $colaboradorId, array $dados, User $user): Parte
    {
        return DB::transaction(function () use ($parte, $nome, $colaboradorId, $dados, $user) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            $datetimes = $this->resolveDatetimesForUpdate($parte, $dados, $user);

            $payload = [
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $dados['extensao_desenho'] ?? 0,
                'extensao_projeto' => $dados['extensao_projeto'] ?? 0,
                'postes_desenhados' => $dados['postes_desenhados'] ?? 0,
                'postes_projetados' => $dados['postes_projetados'] ?? 0,
            ];

            if ($datetimes !== null) {
                $payload = array_merge($payload, $datetimes);
            }

            $parte->update($payload);

            return $parte->fresh();
        });
    }

    /**
     * @return array<string, Carbon|null>|null null = não alterar colunas de data/hora
     */
    private function resolveDatetimesForUpdate(Parte $parte, array $dados, User $user): ?array
    {
        $inicioInput = $this->normalizeDatetimeInput($dados['data_hora_inicio'] ?? null);
        $fimInput = $this->normalizeDatetimeInput($dados['data_hora_fim'] ?? null);

        $hasBothStored = $parte->data_hora_inicio !== null && $parte->data_hora_fim !== null;
        $hasNeitherStored = $parte->data_hora_inicio === null && $parte->data_hora_fim === null;

        $isAdminOrCoord = $user->isAdminOrSuperAdmin() || $user->isCoordenador();
        $isAssignedCollaborator = $parte->colaborador_id === $user->colaborador?->id;

        if ($hasNeitherStored) {
            if ($isAdminOrCoord) {
                return null;
            }

            if ($isAssignedCollaborator) {
                return [
                    'data_hora_inicio' => $this->parseDatetimeInput($inicioInput),
                    'data_hora_fim' => $this->parseDatetimeInput($fimInput),
                ];
            }

            return null;
        }

        if ($hasBothStored) {
            if ($isAssignedCollaborator && ! $isAdminOrCoord) {
                return null;
            }

            if ($isAdminOrCoord) {
                return [
                    'data_hora_inicio' => $this->parseDatetimeInput($inicioInput),
                    'data_hora_fim' => $this->parseDatetimeInput($fimInput),
                ];
            }

            return null;
        }

        if ($isAdminOrCoord) {
            return [
                'data_hora_inicio' => $this->parseDatetimeInput($inicioInput),
                'data_hora_fim' => $this->parseDatetimeInput($fimInput),
            ];
        }

        return null;
    }

    private function normalizeDatetimeInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function parseDatetimeInput(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function validateColaboradorCanBeAssigned(int $colaboradorId): void
    {
        $colaborador = Colaborador::with('user')->findOrFail($colaboradorId);

        $allowedRoles = [
            UserRole::Levantadores,
            UserRole::Orcamentistas,
            UserRole::Projetistas,
        ];

        if (! $colaborador->user || ! in_array($colaborador->user->role, $allowedRoles, true)) {
            throw new \InvalidArgumentException(
                'O colaborador deve ter perfil Levantador, Orçamentista ou Projetista.'
            );
        }
    }
}
