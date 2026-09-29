<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PaginationWindowTest extends TestCase
{
    public function test_primeira_pagina_exibe_janela_parcial_com_reticencias(): void
    {
        $html = $this->renderPagination(currentPage: 1, total: 165, perPage: 10);

        $this->assertStringContainsString('Exibindo', $html);
        $this->assertStringContainsString('165', $html);
        $this->assertStringContainsString('>...</span>', $html);
        $this->assertStringContainsString('aria-current="page">1</button>', $html);
        $this->assertStringContainsString('>6</button>', $html);
        $this->assertStringContainsString('>17</button>', $html);
        $this->assertStringNotContainsString('>7</button>', $html);
        $this->assertStringNotContainsString('>15</button>', $html);
        $this->assertStringNotContainsString('&laquo;', $html);
    }

    public function test_pagina_intermediaria_mostra_atual_entre_reticencias(): void
    {
        $html = $this->renderPagination(currentPage: 9, total: 165, perPage: 10);

        $this->assertStringContainsString('aria-current="page">9</button>', $html);
        $this->assertSame(2, substr_count($html, '>...</span>'));
        $this->assertStringContainsString('>1</button>', $html);
        $this->assertStringContainsString('>17</button>', $html);
        $this->assertStringNotContainsString('>5</button>', $html);
        $this->assertStringNotContainsString('>12</button>', $html);
    }

    public function test_ultima_pagina_omite_o_meio_da_lista(): void
    {
        $html = $this->renderPagination(currentPage: 17, total: 165, perPage: 10);

        $this->assertStringContainsString('aria-current="page">17</button>', $html);
        $this->assertStringContainsString('>...</span>', $html);
        $this->assertStringContainsString('>1</button>', $html);
        $this->assertStringNotContainsString('>8</button>', $html);
    }

    public function test_poucas_paginas_lista_todas_sem_reticencias(): void
    {
        $html = $this->renderPagination(currentPage: 1, total: 30, perPage: 10);

        $this->assertStringNotContainsString('>...</span>', $html);
        $this->assertStringContainsString('aria-current="page">1</button>', $html);
        $this->assertStringContainsString('>2</button>', $html);
        $this->assertStringContainsString('>3</button>', $html);
    }

    private function renderPagination(int $currentPage, int $total, int $perPage): string
    {
        $paginator = new LengthAwarePaginator(
            items: range(1, min($perPage, $total)),
            total: $total,
            perPage: $perPage,
            currentPage: $currentPage,
            options: ['path' => '/'],
        );

        return Blade::render(
            '<x-pagination :paginator="$paginator" />',
            ['paginator' => $paginator],
        );
    }
}
