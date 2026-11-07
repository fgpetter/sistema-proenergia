<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TabelaTarifasProdutividadePj extends Model
{
    protected $table = 'tabelas_tarifas_produtividade_pj';

    protected $fillable = ['competencia_inicio', 'tarifas', 'salvo_por'];

    protected function casts(): array
    {
        return ['competencia_inicio' => 'date', 'tarifas' => 'array', 'salvo_por' => 'integer'];
    }

    public function scopeVigenteEm(Builder $query, string $competencia): Builder
    {
        return $query->where('competencia_inicio', '<=', $competencia.'-01')->orderByDesc('competencia_inicio');
    }

    public static function vigenteEm(string $competencia): ?self
    {
        return static::query()->vigenteEm($competencia)->first();
    }
}
