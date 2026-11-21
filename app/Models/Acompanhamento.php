<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acompanhamento extends Model
{
    protected $fillable = [
        'lote_id', 'user_id', 'data', 'idade_dias', 'mortalidade', 'eliminados',
        'consumo_racao_kg', 'peso_medio_g', 'temperatura_c', 'observacao',
        'checklist_agua', 'checklist_racao', 'checklist_ventilacao', 'checklist_cama', 'tipo_racao',
    ];

    public const TIPOS_RACAO = ['pelet' => 'Pelet', 'farelo' => 'Farelo'];

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
            'checklist_agua' => 'boolean',
            'checklist_racao' => 'boolean',
            'checklist_ventilacao' => 'boolean',
            'checklist_cama' => 'boolean',
        ];
    }

    /** RF20: quantos dos 4 itens do checklist de visita foram conferidos. */
    public function checklistConcluido(): int
    {
        return (int) $this->checklist_agua + (int) $this->checklist_racao
            + (int) $this->checklist_ventilacao + (int) $this->checklist_cama;
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
