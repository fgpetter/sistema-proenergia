@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('html_attribute')
data-sidenav-color="dark"
@endsection
@section('css')

@endsection

@section('content')
    {{-- @include('layouts.partials/page-title', ['subtitle' => 'Menu', 'title' => 'Dashboard'] ) --}}
    @include('layouts.partials/page-title', ['title' => 'Dashboard'] )

    <div class="grid lg:grid-cols-5 grid-cols-1 gap-5 mb-5">
        <div class="lg:col-span-2 grid md:grid-cols-3 grid-cols-1 gap-5">
            <div class="card bg-blue-100">
                <div class="card-body">
                    <p class="text-base text-default-500 font-medium">Numero total de projetos</p>
                    <h5 class="text-3xl font-medium mt-4">128</h5>
                </div>
            </div>

            <div class="card">
                <div class="card-body bg-orange-100">
                    <p class="text-base text-default-500 font-medium">Extensao de desenho</p>
                    <h5 class="text-3xl font-medium mt-4">24.560 m</h5>
                </div>
            </div>

            <div class="card">
                <div class="card-body bg-orange-100">
                    <p class="text-base text-default-500 font-medium">Extensao de projeto</p>
                    <h5 class="text-3xl font-medium mt-4">19.740 m</h5>
                </div>
            </div>

            <div class="card">
                <div class="card-body bg-rose-100">
                    <p class="text-base text-default-500 font-medium">Postes desenhados</p>
                    <h5 class="text-3xl font-medium mt-4">3.420</h5>
                </div>
            </div>

            <div class="card">
                <div class="card-body bg-rose-100">
                    <p class="text-base text-default-500 font-medium">Postes projetados</p>
                    <h5 class="text-3xl font-medium mt-4">2.980</h5>
                </div>
            </div>

            <div class="card bg-green-100">
                <div class="card-body">
                    <p class="text-base text-default-500 font-medium ">Total de Horas reportadas</p>
                    <h5 class="text-3xl font-medium mt-4">1.256 h</h5>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 card">
            <div class="card-header">
                <h6 class="card-title">Performance de Colaborador</h6>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-default-200">
                            <thead class="bg-default-150">
                                <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                    <th class="px-3.5 py-3 text-start" scope="col">Nome</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projetos</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Desenho</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Projeto</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">P. Desenhados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">P. Projetados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Hs reportadas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-default-200">
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Ana Luiza Gomes</td>
                                    <td class="px-3.5 py-3">12</td>
                                    <td class="px-3.5 py-3">4.280 m</td>
                                    <td class="px-3.5 py-3">3.910 m</td>
                                    <td class="px-3.5 py-3">560</td>
                                    <td class="px-3.5 py-3">521</td>
                                    <td class="px-3.5 py-3">218 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Carlos Eduardo Lima</td>
                                    <td class="px-3.5 py-3">9</td>
                                    <td class="px-3.5 py-3">3.960 m</td>
                                    <td class="px-3.5 py-3">3.540 m</td>
                                    <td class="px-3.5 py-3">498</td>
                                    <td class="px-3.5 py-3">467</td>
                                    <td class="px-3.5 py-3">193 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Fernanda Ribeiro</td>
                                    <td class="px-3.5 py-3">11</td>
                                    <td class="px-3.5 py-3">4.710 m</td>
                                    <td class="px-3.5 py-3">4.280 m</td>
                                    <td class="px-3.5 py-3">622</td>
                                    <td class="px-3.5 py-3">593</td>
                                    <td class="px-3.5 py-3">246 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Marcos Vinicius Santos</td>
                                    <td class="px-3.5 py-3">8</td>
                                    <td class="px-3.5 py-3">3.340 m</td>
                                    <td class="px-3.5 py-3">3.020 m</td>
                                    <td class="px-3.5 py-3">442</td>
                                    <td class="px-3.5 py-3">410</td>
                                    <td class="px-3.5 py-3">171 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Priscila Almeida</td>
                                    <td class="px-3.5 py-3">10</td>
                                    <td class="px-3.5 py-3">4.120 m</td>
                                    <td class="px-3.5 py-3">3.770 m</td>
                                    <td class="px-3.5 py-3">535</td>
                                    <td class="px-3.5 py-3">499</td>
                                    <td class="px-3.5 py-3">209 h</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensao de desenho</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Extensao de projeto</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes desenhados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Postes projetados</th>
                                    <th class="px-3.5 py-3 text-start" scope="col">Total de Horas reportadas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-default-200">
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Projeto Linha Norte</td>
                                    <td class="px-3.5 py-3">4.250 m</td>
                                    <td class="px-3.5 py-3">3.980 m</td>
                                    <td class="px-3.5 py-3">540</td>
                                    <td class="px-3.5 py-3">490</td>
                                    <td class="px-3.5 py-3">214 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Projeto Serra Azul</td>
                                    <td class="px-3.5 py-3">5.120 m</td>
                                    <td class="px-3.5 py-3">4.760 m</td>
                                    <td class="px-3.5 py-3">710</td>
                                    <td class="px-3.5 py-3">664</td>
                                    <td class="px-3.5 py-3">286 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Projeto Vale Verde</td>
                                    <td class="px-3.5 py-3">3.640 m</td>
                                    <td class="px-3.5 py-3">3.215 m</td>
                                    <td class="px-3.5 py-3">458</td>
                                    <td class="px-3.5 py-3">420</td>
                                    <td class="px-3.5 py-3">172 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Projeto Rota Oeste</td>
                                    <td class="px-3.5 py-3">6.030 m</td>
                                    <td class="px-3.5 py-3">5.780 m</td>
                                    <td class="px-3.5 py-3">820</td>
                                    <td class="px-3.5 py-3">788</td>
                                    <td class="px-3.5 py-3">331 h</td>
                                </tr>
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                                    <td class="px-3.5 py-3">Projeto Parque Sul</td>
                                    <td class="px-3.5 py-3">2.910 m</td>
                                    <td class="px-3.5 py-3">2.640 m</td>
                                    <td class="px-3.5 py-3">392</td>
                                    <td class="px-3.5 py-3">361</td>
                                    <td class="px-3.5 py-3">143 h</td>
                                </tr>
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