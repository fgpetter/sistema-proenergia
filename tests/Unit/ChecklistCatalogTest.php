<?php

namespace Tests\Unit;

use App\Support\ChecklistCatalog;
use Tests\TestCase;

class ChecklistCatalogTest extends TestCase
{
    public function test_catalogo_urbano_tem_64_itens(): void
    {
        $catalog = new ChecklistCatalog;

        $this->assertSame(64, $catalog->count(ChecklistCatalog::ABA_URBANO));
    }

    public function test_catalogo_rural_tem_72_itens(): void
    {
        $catalog = new ChecklistCatalog;

        $this->assertSame(72, $catalog->count(ChecklistCatalog::ABA_RURAL));
    }

    public function test_primeiro_item_urbano_e_conhecido(): void
    {
        $catalog = new ChecklistCatalog;
        $items = $catalog->items(ChecklistCatalog::ABA_URBANO);

        $this->assertSame(1, $items[0]['numero']);
        $this->assertSame('1. Enquadramento e Tensão de Fornecimento', $items[0]['categoria']);
        $this->assertStringContainsString('75 kW', $items[0]['item']);
        $this->assertSame('Geral', $items[0]['tipo']);
    }

    public function test_item_13_urbano_difere_do_rural(): void
    {
        $catalog = new ChecklistCatalog;
        $urbano = collect($catalog->items(ChecklistCatalog::ABA_URBANO))->firstWhere('numero', 13);
        $rural = collect($catalog->items(ChecklistCatalog::ABA_RURAL))->firstWhere('numero', 13);

        $this->assertNotNull($urbano);
        $this->assertNotNull($rural);
        $this->assertSame('Urbano', $urbano['tipo']);
        $this->assertSame('Rural', $rural['tipo']);
        $this->assertNotSame($urbano['item'], $rural['item']);
    }
}
