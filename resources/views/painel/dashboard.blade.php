@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('html_attribute')
data-sidenav-color="dark"
@endsection
@section('css')

@endsection

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Dashboard'] )

    <div class="grid lg:grid-cols-3 grid-cols-1 gap-5 mb-5">
        <div class="lg:col-span-1 grid grid-cols-2 gap-5">
            <div class="card bg-blue-100">
                <div class="card-body">
                    <p class="text-base text-default-500 font-medium">Numero total de projetos</p>
                    <h5 class="text-3xl font-medium mt-4">{{ $totais->totalProjetos }}</h5>
                </div>
            </div>

            <div class="card">
                <div class="card-body bg-orange-100">
                    <p class="text-base text-default-500 font-medium">Extensao de projeto</p>
                    <h5 class="text-3xl font-medium mt-4">{{ (int) $totais->totalExtensaoProjeto }}</h5>
                </div>
            </div>

            <div class="card">
                <div class="card-body bg-rose-100">
                    <p class="text-base text-default-500 font-medium">Postes projetados</p>
                    <h5 class="text-3xl font-medium mt-4">{{ (int) $totais->totalPostesProjetados }}</h5>
                </div>
            </div>

            <div class="card bg-green-100">
                <div class="card-body">
                    <p class="text-base text-default-500 font-medium ">Total de Horas reportadas</p>
                    @php
                        $intervalTotais = \Carbon\CarbonInterval::seconds((int) $totais->totalSegundos)->cascade();
                    @endphp
                    <h5 class="text-3xl font-medium mt-4">{{ (int) $intervalTotais->totalHours }}h {{ $intervalTotais->minutes }}min</h5>
                </div>
            </div>
        </div>

        <livewire:painel.performance-colaboradores />
    </div>

    <div class="grid grid-cols-1 gap-5">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Estatisticas de projetos</h6>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                    <th class="px-3.5 py-3 text-start" scope="col">Nome</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensao de projeto</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes projetados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Total de Horas reportadas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-default-200">
                                @forelse ($estatisticasProjetos as $projeto)
                                    @php
                                        $intervalProjeto = \Carbon\CarbonInterval::seconds((int) $projeto->total_segundos)->cascade();
                                    @endphp
                                    <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                        <td class="px-3.5 py-3">
                                            <div class="hs-tooltip [--placement:right]">
                                                    <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-30 py-1 px-2 bg-default-900 text-xs font-medium text-default-100 rounded-md shadow-2xs" role="tooltip">
                                                        {{ $projeto->nome }}
                                                    </span>
                                                {{ \Illuminate\Support\Str::limit($projeto->nome, 50) }}
                                            </div>
                                        </td>
                                        <td class="px-3.5 py-3">{{ (int) $projeto->total_extensao_projeto }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $projeto->total_postes_projetados }}</td>
                                        <td class="px-3.5 py-3">{{ (int) $intervalProjeto->totalHours }}h {{ $intervalProjeto->minutes }}min</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3.5 py-8 text-center text-default-500">
                                            Nenhum projeto cadastrado foi encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
