<?php

namespace App\Models;

use App\Enums\TipoProjetoAtividade;
use Database\Factories\AtividadeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atividade extends Model
{
    /** @use HasFactory<AtividadeFactory> */
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'nome',
        'colaborador_id',
        'extensao_desenho',
        'extensao_projeto',
        'postes_desenhados',
        'postes_projetados',
        'tipo_projeto',
        'duracao_minutos',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'extensao_desenho' => 'integer',
            'extensao_projeto' => 'integer',
            'postes_desenhados' => 'integer',
            'postes_projetados' => 'integer',
            'tipo_projeto' => TipoProjetoAtividade::class,
            'duracao_minutos' => 'integer',
        ];
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function getExtensaoTotalAttribute(): int
    {
        return $this->extensao_desenho + $this->extensao_projeto;
    }

    public function getTotalPostesAttribute(): int
    {
        return $this->postes_desenhados + $this->postes_projetados;
    }
}
