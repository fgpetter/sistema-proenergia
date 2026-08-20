<?php

namespace Tests\Feature\Painel;

use App\Enums\UserRole;
use App\Models\Colaborador;
use App\Queries\DashboardMetrics;
use App\Support\BonusColaboradorCalculator;
use Carbon\Carbon;
use Database\Seeders\DashboardConferenciaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardConferenciaSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-20 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_seeder_cria_tres_projetistas_com_dois_acima_e_um_abaixo_da_meta_cad(): void
    {
        $this->seed(DashboardConferenciaSeeder::class);

        $projetistas = Colaborador::query()
            ->whereHas('user', fn ($query) => $query->where('role', UserRole::Projetistas))
            ->orderBy('nome')
            ->get();

        $this->assertCount(3, $projetistas);
        $this->assertSame(
            ['Gerson A.', 'João P.', 'Valentine N.'],
            $projetistas->pluck('nome')->all(),
        );

        $ranking = app(DashboardMetrics::class)->producaoPorColaborador(now()->format('Y-m'));
        $limite = BonusColaboradorCalculator::LIMITE_POSTES_PROJETO_CAD;

        $this->assertSame(['Valentine N.', 'Gerson A.', 'João P.'], $ranking->pluck('nome')->all());
        $this->assertSame([450, 320, 180], $ranking->pluck('total')->all());
        $this->assertTrue($ranking[0]->total >= $limite);
        $this->assertTrue($ranking[1]->total >= $limite);
        $this->assertTrue($ranking[2]->total < $limite);
        $this->assertSame([true, true, false], $ranking->pluck('acimaDaMeta')->all());
    }
}
