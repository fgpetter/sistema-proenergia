<?php

namespace App\Support;

class Money
{
    /**
     * Converte valor mascarado (ex.: "1.245,60") para centavos inteiros.
     */
    public static function toCents(?string $masked): ?int
    {
        if ($masked === null) {
            return null;
        }

        $normalized = trim($masked);

        if ($normalized === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $normalized);

        if ($digits === null || $digits === '') {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Formata centavos inteiros para máscara pt-BR (ex.: 124560 → "1.245,60").
     */
    public static function fromCents(?int $cents): string
    {
        if ($cents === null) {
            return '';
        }

        return number_format($cents / 100, 2, ',', '.');
    }
}
