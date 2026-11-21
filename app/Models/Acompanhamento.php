<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acompanhamento extends Model
{
    protected $fillable = [
        'lote_id', 'user_id', 'data', 'idade_dias', 'mortalidade', 'eliminados',
        'consumo_racao_kg', 'peso_medio_g', 'temperatura_c', 'observacao',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'idade_dias' => 'integer',
            'mortalidade' => 'integer',
            'eliminados' => 'integer',
            'consumo_racao_kg' => 'float',
            'peso_medio_g' => 'float',
            'temperatura_c' => 'float',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
