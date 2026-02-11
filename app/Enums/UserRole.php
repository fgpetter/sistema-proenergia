<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Levantadores = 'levantadores';
    case Projetistas = 'projetistas';
    case Orcamentistas = 'orcamentistas';
    case Coordenadores = 'coordenadores';
    case Administrativos = 'administrativos';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Levantadores => 'Levantadores',
            self::Projetistas => 'Projetistas',
            self::Orcamentistas => 'Orçamentistas',
            self::Coordenadores => 'Coordenadores',
            self::Administrativos => 'Administrativos',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Administrativos => 'warning',
            self::Coordenadores => 'primary',
            self::Levantadores, self::Projetistas, self::Orcamentistas => 'success',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $role) => [
            $role->value => $role->label(),
        ])->toArray();
    }

    public static function perfisColaborador(): array
    {
        return collect(self::cases())
            ->filter(fn (self $role) => $role !== self::SuperAdmin)
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->toArray();
    }
}
