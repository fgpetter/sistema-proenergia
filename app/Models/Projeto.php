<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projeto extends Model
{
    /** @use HasFactory<\Database\Factories\ProjetoFactory> */
    use HasFactory;

    protected $fillable = [
        'nome',
        'colaborador_responsavel_id',
    ];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_responsavel_id');
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    public function logAtividades(): HasMany
    {
        return $this->hasMany(LogAtividade::class);
    }
}
