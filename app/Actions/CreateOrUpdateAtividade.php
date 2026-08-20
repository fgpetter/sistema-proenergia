<?php

namespace App\Actions;

use App\Enums\TipoProjetoAtividade;
use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\LogAtividade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateAtividade
{
    public const MENSAGEM_REMOCAO_ATRIBUICAO = 'Não é permitido remover a atribuição do colaborador.';

    /**
     * @param  int|string|null  $colaboradorId
     * @param  array<string, mixed>  $dados
     */
    public function create(int $projetoId, string $nome, mixed $colaboradorId, array $dados, User $user): Atividade
    {
        $colaboradorId = $this->normalizeColaboradorId($colaboradorId);

        return DB::transaction(function () use ($projetoId, $nome, $colaboradorId, $dados, $user) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId);
            }

            unset($dados['duracao_horas'], $dados['duracao_minutos']);

            $atividade = Atividade::create([
                'projeto_id' => $projetoId,
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $this->inteiroOuZero($dados['extensao_desenho'] ?? null),
                'extensao_projeto' => $this->inteiroOuZero($dados['extensao_projeto'] ?? null),
                'postes_desenhados' => $this->inteiroOuZero($dados['postes_desenhados'] ?? null),
                'postes_projetados' => $this->inteiroOuZero($dados['postes_projetados'] ?? null),
                'tipo_projeto' => $dados['tipo_projeto'] ?? TipoProjetoAtividade::Cad->value,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            $postesDesenhados = $this->inteiroOuZero($dados['postes_desenhados'] ?? null);
            $postesProjetados = $this->inteiroOuZero($dados['postes_projetados'] ?? null);

            if ($postesDesenhados > 0) {
                $this->registrarLogAtividade($projetoId, $user->id, $atividade->id, 'adicionou', 'postes_desenhados', $postesDesenhados);
            }

            if ($postesProjetados > 0) {
                $this->registrarLogAtividade($projetoId, $user->id, $atividade->id, 'adicionou', 'postes_projetados', $postesProjetados);
            }

            return $atividade;
        });
    }

    /**
     * @param  int|string|null  $colaboradorId
     * @param  array<string, mixed>  $dados
     */
    public function update(Atividade $atividade, string $nome, mixed $colaboradorId, array $dados, User $user): Atividade
    {
        $colaboradorId = $this->normalizeColaboradorId($colaboradorId);

        if ($atividade->colaborador_id !== null && $colaboradorId === null) {
            throw ValidationException::withMessages([
                'colaborador_id' => self::MENSAGEM_REMOCAO_ATRIBUICAO,
            ]);
        }

        return DB::transaction(function () use ($atividade, $nome, $colaboradorId, $dados, $user) {
            if ($colaboradorId) {
                $this->validateColaboradorCanBeAssigned($colaboradorId, $atividade->colaborador_id);
            }

            $postesDesenhadosAntes = $atividade->postes_desenhados;
            $postesProjetadosAntes = $atividade->postes_projetados;

            $payload = [
                'nome' => $nome,
                'colaborador_id' => $colaboradorId,
                'extensao_desenho' => $this->inteiroOuZero($dados['extensao_desenho'] ?? null),
                'extensao_projeto' => $this->inteiroOuZero($dados['extensao_projeto'] ?? null),
                'postes_desenhados' => $this->inteiroOuZero($dados['postes_desenhados'] ?? null),
                'postes_projetados' => $this->inteiroOuZero($dados['postes_projetados'] ?? null),
                'tipo_projeto' => $dados['tipo_projeto'] ?? TipoProjetoAtividade::Cad->value,
                'observacoes' => $dados['observacoes'] ?? null,
                'duracao_minutos' => $this->resolveDuracaoMinutos($atividade, $dados, $user),
            ];

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
     * @param  array<string, mixed>  $dados
     */
    private function resolveDuracaoMinutos(Atividade $atividade, array $dados, User $user): ?int
    {
        $minutos = $this->parseDuracaoMinutos($dados);

        $isAdminOrCoord = $user->isAdminOrSuperAdmin() || $user->isCoordenador();
        $isAssignedCollaborator = $atividade->colaborador_id === $user->colaborador?->id;

        if ($isAssignedCollaborator && ! $isAdminOrCoord && ($minutos === null || $minutos <= 0)) {
            throw ValidationException::withMessages([
                'duracao_horas' => 'Informe a duração maior que zero.',
            ]);
        }

        return $minutos;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function parseDuracaoMinutos(array $dados): ?int
    {
        $horas = $dados['duracao_horas'] ?? null;
        $minutos = $dados['duracao_minutos'] ?? null;

        $horasVazio = $horas === null || $horas === '';
        $minutosVazio = $minutos === null || $minutos === '';

        if ($horasVazio && $minutosVazio) {
            return null;
        }

        return ((int) $horas * 60) + (int) $minutos;
    }

    private function inteiroOuZero(mixed $valor): int
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        return (int) $valor;
    }

    private function normalizeColaboradorId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function validateColaboradorCanBeAssigned(int $colaboradorId, ?int $atribuicaoAtualId = null): void
    {
        $colaborador = Colaborador::withTrashed()
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->findOrFail($colaboradorId);

        if ($colaborador->trashed()) {
            if ($atribuicaoAtualId === null || (int) $atribuicaoAtualId !== $colaboradorId) {
                throw new \InvalidArgumentException(
                    'O colaborador selecionado não está disponível.'
                );
            }

            return;
        }

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
