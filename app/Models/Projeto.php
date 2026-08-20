<?php

namespace App\Models;

use Database\Factories\ProjetoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projeto extends Model
{
    /** @use HasFactory<ProjetoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'nome',
        'colaborador_responsavel_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Projeto $projeto): void {
            $projeto->atividades()->delete();
        });
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_responsavel_id')->withTrashed();
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
