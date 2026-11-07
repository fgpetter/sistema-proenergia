<div class="flex flex-col gap-5">
    @if (session('status'))
        <div class="rounded bg-success/10 px-4 py-3 text-sm text-success" role="status">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <h6 class="card-title mb-0">Parâmetros de produtividade PJ</h6>
            <div class="text-sm">Competência editada: {{ \Carbon\Carbon::createFromFormat('Y-m', $competencia)->format('m/Y') }}</div>
        </div>
        <form wire:submit="salvar" class="card-body flex flex-col gap-5">
            @php
                $grupos = [
                    'Desenho' => ['desenho'],
                    'Projeto CAD' => ['projeto_1_10', 'projeto_11_20', 'projeto_21_mais'],
                    'Projeto PROJ' => ['proj_1_10', 'proj_11_20', 'proj_21_mais'],
                    'Relatório' => ['relatorio'],
                    'Análise PROJ' => ['analise_proj'],
                ];
            @endphp
            @foreach ($grupos as $titulo => $prefixos)
                <section class="flex flex-col gap-3" wire:key="grupo-{{ str($titulo)->slug() }}">
                    <h2 class="font-semibold">{{ $titulo }}</h2>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($prefixos as $prefixo)
                            @php
                                $rotulo = match (true) {
                                    $prefixo === 'desenho' => 'Desenho',
                                    str_starts_with($prefixo, 'projeto_') || str_starts_with($prefixo, 'proj_') => str_contains($prefixo, '1_10') ? 'Até 10 postes' : (str_contains($prefixo, '11_20') ? '11 a 20 postes' : 'Acima de 20 postes'),
                                    $prefixo === 'relatorio' => 'Relatório',
                                    default => 'Análise PROJ',
                                };
                            @endphp
                            <div class="rounded border border-default-200 p-3 dark:border-default-700">
                                <h3 class="mb-3 text-sm font-medium">{{ $rotulo }}</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs">Fixo (R$)
                                        <input type="text" inputmode="decimal" wire:model="valores.{{ $prefixo }}_fixo" class="form-input">
                                        @error('valores.'.$prefixo.'_fixo') <span class="text-danger">{{ $message }}</span> @enderror
                                    </label>
                                    @if (in_array($prefixo, ['relatorio', 'analise_proj'], true))
                                        <div class="text-xs">Variável: R$ 0,0000</div>
                                    @else
                                        <label class="text-xs">Variável por metro (R$)
                                            <input type="text" inputmode="decimal" wire:model="valores.{{ $prefixo }}_variavel" class="form-input">
                                            @error('valores.'.$prefixo.'_variavel') <span class="text-danger">{{ $message }}</span> @enderror
                                        </label>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
            <div class="flex justify-end">
                <button type="submit" class="btn bg-primary text-white" wire:loading.attr="disabled">Salvar parâmetros</button>
            </div>
        </form>
    </div>
</div>
