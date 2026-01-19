<?php

namespace App\Enums;

enum TipoColaborador: string
{
    case Levantadores = 'levantadores';
    case Projetistas = 'projetistas';
    case Orcamentistas = 'orcamentistas';

    public function label(): string
    {
        return match ($this) {
            self::Levantadores => 'Levantadores',
            self::Projetistas => 'Projetistas',
            self::Orcamentistas => 'Orçamentistas',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $tipo) => [
            $tipo->value => $tipo->label(),
        ])->toArray();
    }
}
