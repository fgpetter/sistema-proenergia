<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAtividade extends Model
{
    use HasFactory;

    protected $table = 'log_atividades';

    protected $fillable = [
        'projeto_id',
        'user_id',
        'atividade_id',
        'acao',
        'item',
        'valor',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atividade(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Atividade::class, 'atividade_id');
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }
}
