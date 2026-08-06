<div class="lg:col-span-2 card">
    <div class="card-header flex flex-wrap items-center justify-between gap-3">
        <h6 class="card-title">Performance de Colaborador</h6>
        <div class="w-full max-w-xs">
            <label class="mb-1 block text-sm font-medium text-default-700">Mês/Ano</label>
            <select wire:model.live="mesAno" class="form-input form-input-sm w-full">
                <option value="">Todas as competências</option>
                @foreach ($this->competenciasDisponiveis as $valor => $rotulo)
                    <option value="{{ $valor }}">{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-default-200">
                    <thead class="bg-default-150">
                        <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                            <th class="px-3.5 py-3 text-start" scope="col">Colaborador</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Projetos</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Extensão total</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Postes CAD</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Postes PROJ</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Postes Total</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Bônus</th>
                            <th class="px-3.5 py-3 text-start" scope="col">Horas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-default-200">
                        @forelse ($this->produtividadeColaboradores as $colaborador)
                            @php
                                $interval = \Carbon\CarbonInterval::seconds((int) $colaborador->total_segundos)->cascade();
                            @endphp
                            <tr wire:key="performance-colaborador-{{ $colaborador->id }}" class="text-default-800 font-normal text-sm whitespace-nowrap">
                                <td class="px-3.5 py-3">{{ $colaborador->nome }}</td>
                                <td class="px-3.5 py-3">{{ (int) $colaborador->total_projetos }}</td>
                                <td class="px-3.5 py-3">{{ (int) $colaborador->total_extensao_desenho + (int) $colaborador->total_extensao_projeto }}</td>
                                <td class="px-3.5 py-3">{{ $colaborador->meta_cad }}</td>
                                <td class="px-3.5 py-3">{{ $colaborador->meta_proj }}</td>
                                <td class="px-3.5 py-3">{{ (int) $colaborador->total_postes }}</td>
                                <td class="px-3.5 py-3">R$ {{ number_format((float) $colaborador->total_bonus, 2, ',', '.') }}</td>
                                <td class="px-3.5 py-3">{{ (int) $interval->totalHours }}h {{ $interval->minutes }}min</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3.5 py-8 text-center text-default-500">
                                    Nenhum colaborador com atividades registradas foi encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="px-3.5 pb-3 text-sm text-primary">
        * a meta para projetos no PROJ é de 230 postes, para projetos no CAD é de 300 postes.
    </p>
</div>
