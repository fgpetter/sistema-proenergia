<?php

namespace App\Console\Commands;

use App\Models\Atividade;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

#[Signature('atividades:preencher-data-conclusao {data_corte=2026-10-05 : Data limite no formato AAAA-MM-DD; datas anteriores serão consideradas} {--dry-run : Exibe quantas atividades seriam atualizadas sem gravar alterações}')]
#[Description('Preenche data_conclusao usando created_at e updated_at anteriores à data de corte')]
class PreencherDataConclusaoAtividades extends Command
{
    public function handle(): int
    {
        $dataCorteTexto = (string) $this->argument('data_corte');

        try {
            Validator::make(
                ['data_corte' => $dataCorteTexto],
                ['data_corte' => ['required', 'date_format:Y-m-d']],
            )->validate();
        } catch (ValidationException) {
            $this->error('A data de corte deve estar no formato AAAA-MM-DD e ser uma data válida.');

            return self::FAILURE;
        }

        $dataCorte = Carbon::createFromFormat('!Y-m-d', $dataCorteTexto);
        $atividades = $this->atividadesParaAtualizar($dataCorte);
        $quantidade = (clone $atividades)->count();

        if ($this->option('dry-run')) {
            $this->info("{$quantidade} atividade(s) seriam atualizadas.");
            $this->comment('Nenhuma alteração foi gravada.');

            return self::SUCCESS;
        }

        $atualizadas = 0;

        $atividades->chunkById(500, function (Collection $lote) use ($dataCorte, &$atualizadas): void {
            foreach ($lote as $atividade) {
                $dataConclusao = $this->resolverDataConclusao($atividade, $dataCorte);

                if ($dataConclusao === null) {
                    continue;
                }

                $atualizadas += DB::table('atividades')
                    ->where('id', $atividade->id)
                    ->whereNull('data_conclusao')
                    ->update(['data_conclusao' => $dataConclusao]);
            }
        });

        $this->info("{$atualizadas} atividade(s) atualizadas.");

        return self::SUCCESS;
    }

    /**
     * @return Builder<Atividade>
     */
    private function atividadesParaAtualizar(Carbon $dataCorte): Builder
    {
        return Atividade::query()
            ->whereNull('data_conclusao')
            ->where(function (Builder $query) use ($dataCorte): void {
                $query
                    ->where('created_at', '<', $dataCorte)
                    ->orWhere('updated_at', '<', $dataCorte);
            });
    }

    private function resolverDataConclusao(Atividade $atividade, Carbon $dataCorte): ?string
    {
        if ($atividade->updated_at !== null && $atividade->updated_at->lt($dataCorte)) {
            return $atividade->updated_at->toDateString();
        }

        if ($atividade->created_at !== null && $atividade->created_at->lt($dataCorte)) {
            return $atividade->created_at->toDateString();
        }

        return null;
    }
}
