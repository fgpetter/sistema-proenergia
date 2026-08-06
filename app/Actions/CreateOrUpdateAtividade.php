<?php

namespace App\Actions;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\LogAtividade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateAtividade
{
    public function create(int $projetoId, string $nome, ?int $colaboradorId, array $dados, User $user): Atividade
    {
        return DB::transaction(function () use ($projetoId, $nome, $colaboradorId, $dados, $user) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            // Apenas o colaborador atribuído pode preencher o primeiro par de datas; criação de atividade é feita por admin/coordenador.
            unset($dados['data_hora_inicio'], $dados['data_hora_fim']);

            $atividade = Atividade::create([
                'projeto_id' => $projetoId,
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $dados['extensao_desenho'] ?? 0,
                'extensao_projeto' => $dados['extensao_projeto'] ?? 0,
                'postes_desenhados' => $dados['postes_desenhados'] ?? 0,
                'postes_projetados' => $dados['postes_projetados'] ?? 0,
                'tipo_projeto' => $dados['tipo_projeto'] ?? TipoProjetoAtividade::Cad->value,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            if (($dados['postes_desenhados'] ?? 0) > 0) {
                $this->registrarLogAtividade($projetoId, $user->id, $atividade->id, 'adicionou', 'postes_desenhados', (int) ($dados['postes_desenhados'] ?? 0));
            }

            if (($dados['postes_projetados'] ?? 0) > 0) {
                $this->registrarLogAtividade($projetoId, $user->id, $atividade->id, 'adicionou', 'postes_projetados', (int) ($dados['postes_projetados'] ?? 0));
            }

            return $atividade;
        });
    }

    public function update(Atividade $atividade, string $nome, ?int $colaboradorId, array $dados, User $user): Atividade
    {
        return DB::transaction(function () use ($atividade, $nome, $colaboradorId, $dados, $user) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            $datetimes = $this->resolveDatetimesForUpdate($atividade, $dados, $user);

            $payload = [
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $dados['extensao_desenho'] ?? 0,
                'extensao_projeto' => $dados['extensao_projeto'] ?? 0,
                'postes_desenhados' => $dados['postes_desenhados'] ?? 0,
                'postes_projetados' => $dados['postes_projetados'] ?? 0,
                'tipo_projeto' => $dados['tipo_projeto'] ?? TipoProjetoAtividade::Cad->value,
                'observacoes' => $dados['observacoes'] ?? null,
            ];

            if ($datetimes !== null) {
                $payload = array_merge($payload, $datetimes);
            }

            $postesDesenhadosAntes = $atividade->postes_desenhados;
            $postesProjetadosAntes = $atividade->postes_projetados;

            $atividade->update($payload);

            if ($payload['postes_desenhados'] != $postesDesenhadosAntes) {
                $this->registrarLogAtividade($atividade->projeto_id, $user->id, $atividade->id, 'alterou', 'postes_desenhados', (int) $payload['postes_desenhados']);
            }

            if ($payload['postes_projetados'] != $postesProjetadosAntes) {
                $this->registrarLogAtividade($atividade->projeto_id, $user->id, $atividade->id, 'alterou', 'postes_projetados', (int) $payload['postes_projetados']);
            }

            return $atividade->fresh();
        });
    }

    private function registrarLogAtividade(int $projetoId, int $userId, int $atividadeId, string $acao, string $item, int $valor): void
    {
        LogAtividade::create([
            'projeto_id' => $projetoId,
            'user_id' => $userId,
            'atividade_id' => $atividadeId,
            'acao' => $acao,
            'item' => $item,
            'valor' => $valor,
        ]);
    }

    /**
     * @return array<string, Carbon|null>|null null = não alterar colunas de data/hora
     */
    private function resolveDatetimesForUpdate(Atividade $atividade, array $dados, User $user): ?array
    {
        $inicioInput = $this->normalizeDatetimeInput($dados['data_hora_inicio'] ?? null);
        $fimInput = $this->normalizeDatetimeInput($dados['data_hora_fim'] ?? null);

        $hasBothStored = $atividade->data_hora_inicio !== null && $atividade->data_hora_fim !== null;
        $hasNeitherStored = $atividade->data_hora_inicio === null && $atividade->data_hora_fim === null;

        $isAdminOrCoord = $user->isAdminOrSuperAdmin() || $user->isCoordenador();
        $isAssignedCollaborator = $atividade->colaborador_id === $user->colaborador?->id;

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
