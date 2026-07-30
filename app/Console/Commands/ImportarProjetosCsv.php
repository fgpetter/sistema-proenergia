<?php

namespace App\Console\Commands;

use App\Actions\CreateOrUpdateParte;
use App\Actions\CreateOrUpdateProjeto;
use App\Enums\TipoProjetoParte;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportarProjetosCsv extends Command
{
    private const int COLABORADOR_RESPONSAVEL_ID = 4;

    private const int COLABORADOR_PARTE_ID = 1;

    private const string PARTE_NOME = 'Parte 1';

    /**
     * @var string
     */
    protected $signature = 'projetos:importar-csv {caminho : Caminho absoluto ou relativo do arquivo CSV}';

    /**
     * @var string
     */
    protected $description = 'Importa projetos e partes a partir de um CSV de produtividade';

    public function handle(CreateOrUpdateProjeto $projetoAction, CreateOrUpdateParte $parteAction): int
    {
        $caminho = $this->argument('caminho');
        $caminhoResolvido = $this->resolverCaminho($caminho);

        if ($caminhoResolvido === null) {
            $this->error("Arquivo não encontrado ou ilegível: {$caminho}");
            $this->line('Use um path acessível ao container Sail, por exemplo:');
            $this->line('  vendor/bin/sail artisan projetos:importar-csv storage/app/imports/planilha.csv');

            return self::FAILURE;
        }

        $this->info("Lendo CSV: {$caminhoResolvido}");

        try {
            $linhas = $this->lerCsv($caminhoResolvido);
        } catch (Throwable $e) {
            $this->error('Falha ao ler o CSV: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($linhas === []) {
            $this->warn('Nenhuma linha de dados encontrada no CSV.');

            return self::SUCCESS;
        }

        $ator = $this->resolverUsuarioAtor();

        if ($ator === null) {
            $this->error('Não foi possível resolver um usuário para executar a criação das partes.');

            return self::FAILURE;
        }

        $criados = 0;
        $erros = 0;

        foreach ($linhas as $linha) {
            $nome = $linha['projeto.nome'];

            $tipoProjeto = TipoProjetoParte::tryFrom($linha['partes.tipo_projeto']);

            if ($tipoProjeto === null) {
                $this->error("  [erro] tipo_projeto inválido em \"{$nome}\": {$linha['partes.tipo_projeto']}");
                $erros++;

                continue;
            }

            try {
                DB::transaction(function () use ($projetoAction, $parteAction, $linha, $nome, $tipoProjeto, $ator): void {
                    $projeto = $projetoAction->create($nome, self::COLABORADOR_RESPONSAVEL_ID);

                    $parteAction->create(
                        $projeto->id,
                        self::PARTE_NOME,
                        self::COLABORADOR_PARTE_ID,
                        [
                            'extensao_desenho' => (int) $linha['partes.extensao_desenho'],
                            'extensao_projeto' => (int) $linha['partes.extensao_projeto'],
                            'postes_desenhados' => (int) $linha['partes.postes_desenhados'],
                            'postes_projetados' => (int) $linha['partes.postes_projetados'],
                            'tipo_projeto' => $tipoProjeto->value,
                        ],
                        $ator,
                    );
                });

                $this->info("  [ok] Criado: {$nome}");
                $criados++;
            } catch (Throwable $e) {
                $this->error("  [erro] {$nome}: ".$e->getMessage());
                $erros++;
            }
        }

        $this->newLine();
        $this->info('Resumo da importação:');
        $this->line("  Criados: {$criados}");
        $this->line("  Erros:   {$erros}");
        $this->newLine();
        $this->comment('Exemplo de uso:');
        $this->line('  vendor/bin/sail artisan projetos:importar-csv storage/app/imports/planilha.csv');

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolverCaminho(string $caminho): ?string
    {
        $candidatos = [
            $caminho,
            base_path($caminho),
            storage_path('app/'.$caminho),
        ];

        foreach ($candidatos as $candidato) {
            if (is_file($candidato) && is_readable($candidato)) {
                return $candidato;
            }
        }

        return null;
    }

    /**
     * @return list<array{
     *     projeto.nome: string,
     *     partes.extensao_desenho: string,
     *     partes.extensao_projeto: string,
     *     partes.tipo_projeto: string,
     *     partes.postes_desenhados: string,
     *     partes.postes_projetados: string
     * }>
     */
    private function lerCsv(string $caminho): array
    {
        $handle = fopen($caminho, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Não foi possível abrir o arquivo.');
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false || $header === [null] || $header === []) {
                throw new \RuntimeException('Cabeçalho do CSV ausente.');
            }

            $header = array_map(static fn (?string $coluna): string => trim((string) $coluna), $header);

            $obrigatorias = [
                'projeto.nome',
                'partes.extensao_desenho',
                'partes.extensao_projeto',
                'partes.tipo_projeto',
                'partes.postes_desenhados',
                'partes.postes_projetados',
            ];

            foreach ($obrigatorias as $coluna) {
                if (! in_array($coluna, $header, true)) {
                    throw new \RuntimeException("Coluna obrigatória ausente no CSV: {$coluna}");
                }
            }

            $linhas = [];

            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || $this->linhaVazia($row)) {
                    continue;
                }

                $associativa = [];

                foreach ($header as $index => $coluna) {
                    $associativa[$coluna] = trim((string) ($row[$index] ?? ''));
                }

                if ($associativa['projeto.nome'] === '') {
                    continue;
                }

                $linhas[] = $associativa;
            }

            return $linhas;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  list<string|null>  $row
     */
    private function linhaVazia(array $row): bool
    {
        foreach ($row as $valor) {
            if (trim((string) $valor) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolverUsuarioAtor(): ?User
    {
        $colaborador = Colaborador::query()->with('user')->find(self::COLABORADOR_RESPONSAVEL_ID);

        if ($colaborador?->user !== null) {
            return $colaborador->user;
        }

        return User::query()->orderBy('id')->first();
    }
}
