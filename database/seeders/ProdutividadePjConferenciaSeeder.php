<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Atividade;
use App\Models\Colaborador;
use App\Models\Projeto;
use App\Models\TabelaTarifasProdutividadePj;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class ProdutividadePjConferenciaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new LogicException('Os dados de conferência PJ são exclusivos dos ambientes local e testing.');
        }

        $hoje = now();
        $competencia = $hoje->format('Y-m');
        $dataConclusao = $hoje->toDateString();

        if (TabelaTarifasProdutividadePj::vigenteEm($competencia) === null) {
            throw new LogicException('Cadastre uma tabela de tarifas PJ vigente antes de gerar os dados de conferência.');
        }

        DB::transaction(function () use ($competencia, $dataConclusao): void {
            $emailCoordenador = "conferencia-pj-coordenador-{$competencia}@example.test";
            if (User::query()->where('email', $emailCoordenador)->exists()) {
                return;
            }

            $coordenador = Colaborador::factory()
                ->for($this->usuario($emailCoordenador, UserRole::Coordenadores))
                ->create(['nome' => 'Conferência PJ - Coordenador']);

            $prestadorCad = Colaborador::factory()->prestador()->create([
                'nome' => 'Conferência PJ - CAD',
                'user_id' => $this->usuario("conferencia-pj-cad-{$competencia}@example.test"),
            ]);
            $prestadorProj = Colaborador::factory()->prestador()->create([
                'nome' => 'Conferência PJ - PROJ',
                'user_id' => $this->usuario("conferencia-pj-proj-{$competencia}@example.test"),
            ]);
            $colaboradorClt = Colaborador::factory()->projetista()->create([
                'nome' => 'Conferência PJ - Controle CLT',
                'user_id' => $this->usuario("conferencia-pj-clt-{$competencia}@example.test"),
            ]);

            $projetoCad = Projeto::factory()->for($coordenador, 'responsavel')
                ->create(['nome' => "Conferência PJ - CAD ({$competencia})"]);
            $projetoProj = Projeto::factory()->for($coordenador, 'responsavel')
                ->create(['nome' => "Conferência PJ - PROJ ({$competencia})"]);

            $atividadeCad = Atividade::factory()->cad()->concluida($dataConclusao)
                ->for($projetoCad)->for($prestadorCad, 'colaborador');
            $atividadeProj = Atividade::factory()->proj()->concluida($dataConclusao)
                ->for($projetoProj)->for($prestadorProj, 'colaborador');

            foreach ([10, 11, 20, 21] as $postes) {
                $atividadeCad->comProducaoProjetada(100, $postes)->create(['nome' => "CAD - {$postes} postes"]);
                $atividadeProj->comProducaoProjetada(100, $postes)->create([
                    'nome' => "PROJ - {$postes} postes",
                    'extensao_desenho' => $postes <= 11 ? 1 : 0,
                    'relatorio_realizado' => $postes === 10,
                    'analise_proj_realizada' => $postes === 10,
                ]);
            }

            $atividadeCad->create(['nome' => 'Relatório sem metragem', 'relatorio_realizado' => true]);
            $atividadeCad->create(['nome' => 'Análise Proj sem metragem', 'analise_proj_realizada' => true]);
            $atividadeCad->create(['nome' => 'Sem metragem e sem serviços fixos']);
            $atividadeCad->comProducaoProjetada(1000, 21)->create([
                'nome' => 'Controle PJ - em revisão',
                'em_revisao' => $dataConclusao,
            ]);
            $atividadeCad->comProducaoProjetada(1000, 21)->create([
                'nome' => 'Controle PJ - pendente',
                'data_conclusao' => null,
            ]);
            $atividadeCad->comProducaoProjetada(1000, 21)->create([
                'nome' => 'Controle CLT - concluída',
                'colaborador_id' => $colaboradorClt->id,
            ]);
        });

        $this->command?->info("Produtividade PJ · competência {$competencia} · filtre pelo coordenador Conferência PJ - Coordenador.");
        $this->command?->line('Com as tarifas originais da planilha: CAD R$ 260,00 e PROJ R$ 366,85.');
    }

    private function usuario(string $email, UserRole $role = UserRole::Projetistas): UserFactory
    {
        return User::factory()->role($role)->state([
            'email' => $email,
            'password' => Str::random(40),
        ]);
    }
}
