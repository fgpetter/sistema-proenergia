<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Queries\DashboardMetrics;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetrics $metrics) {}

    public function index(): View
    {
        return view('painel.dashboard', [
            'totais' => $this->metrics->totaisGlobais(),
            'estatisticasProjetos' => $this->metrics->estatisticasPorProjeto(),
        ]);
    }
}
