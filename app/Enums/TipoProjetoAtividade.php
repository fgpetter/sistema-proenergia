<?php

namespace App\Enums;

enum TipoProjetoAtividade: string
{
    case Cad = 'CAD';
    case Proj = 'PROJ';

    public function label(): string
    {
        return match ($this) {
            self::Cad => 'CAD',
            self::Proj => 'PROJ',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $tipo) => [
            $tipo->value => $tipo->label(),
        ])->toArray();
    }
}
