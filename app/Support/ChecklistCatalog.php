<?php

namespace App\Support;

use InvalidArgumentException;

class ChecklistCatalog
{
    public const string ABA_URBANO = 'urbano';

    public const string ABA_RURAL = 'rural';

    /**
     * @return list<array{
     *     numero: int,
     *     categoria: string,
     *     item: string,
     *     normas: string,
     *     tipo: string
     * }>
     */
    public function items(string $aba): array
    {
        $path = match ($aba) {
            self::ABA_URBANO => resource_path('data/checklists/redes_urbanas.csv'),
            self::ABA_RURAL => resource_path('data/checklists/redes_rurais.csv'),
            default => throw new InvalidArgumentException("Aba de checklist inválida: {$aba}"),
        };

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException("Não foi possível ler o catálogo: {$path}");
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return [];
        }

        $items = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5 || trim($row[0]) === '') {
                continue;
            }

            $items[] = [
                'numero' => (int) $row[0],
                'categoria' => $row[1],
                'item' => $row[2],
                'normas' => $row[3],
                'tipo' => $row[4],
            ];
        }

        fclose($handle);

        return $items;
    }

    public function count(string $aba): int
    {
        return count($this->items($aba));
    }
}
