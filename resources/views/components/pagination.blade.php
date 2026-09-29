@props(['paginator'])

@if ($paginator->hasPages())
    <div class="card-footer">
        <p class="text-default-500 text-sm">
            Exibindo <b>{{ $paginator->firstItem() ?? 0 }}</b> a <b>{{ $paginator->lastItem() ?? 0 }}</b> de <b>{{ $paginator->total() }}</b> resultados
        </p>
        <nav aria-label="Pagination" class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <button disabled class="btn btn-sm border bg-transparent border-default-200 text-default-400 cursor-not-allowed" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg> Anterior
                </button>
            @else
                <button wire:click="previousPage" class="btn btn-sm border bg-transparent border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="15 18 9 12 15 6"/></svg> Anterior
                </button>
            @endif

            @foreach ($paginator->onEachSide(1)->linkCollection()->slice(1, -1) as $link)
                @if ($link['label'] === '...')
                    <span class="px-1 text-sm text-default-400 select-none" aria-hidden="true">...</span>
                @elseif ($link['active'])
                    <button class="btn size-7.5 bg-primary text-white" type="button" aria-current="page">{{ $link['label'] }}</button>
                @else
                    <button wire:click="gotoPage({{ $link['page'] }})" class="btn size-7.5 bg-transparent border border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">{{ $link['label'] }}</button>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" class="btn btn-sm border bg-transparent border-default-200 text-default-600 hover:bg-primary/10 hover:text-primary hover:border-primary/10" type="button">
                    Próximo <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-1"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            @else
                <button disabled class="btn btn-sm border bg-transparent border-default-200 text-default-400 cursor-not-allowed" type="button">
                    Próximo <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-1"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            @endif
        </nav>
    </div>
@endif
