<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_converte_mascara_pt_br_para_centavos(): void
    {
        $this->assertSame(124560, Money::toCents('1.245,60'));
        $this->assertSame(500000, Money::toCents('5.000,00'));
        $this->assertSame(150, Money::toCents('1,50'));
    }

    public function test_retorna_null_para_valor_vazio(): void
    {
        $this->assertNull(Money::toCents(null));
        $this->assertNull(Money::toCents(''));
        $this->assertNull(Money::toCents('   '));
    }

    public function test_formata_centavos_para_mascara_pt_br(): void
    {
        $this->assertSame('1.245,60', Money::fromCents(124560));
        $this->assertSame('5.000,00', Money::fromCents(500000));
        $this->assertSame('', Money::fromCents(null));
    }
}
