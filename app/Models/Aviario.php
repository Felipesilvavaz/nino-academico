<?php

namespace App\Models;

use App\Enums\StatusLote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aviario extends Model
{
    public const TIPOS = [
        'convencional' => 'Convencional',
        'dark_house' => 'Dark house',
    ];

    protected $fillable = ['granja_id', 'identificacao', 'area_m2', 'capacidade_aves', 'tipo'];

    protected function casts(): array
    {
        return [
            'area_m2' => 'float',
            'capacidade_aves' => 'integer',
        ];
    }

    public function granja(): BelongsTo
    {
        return $this->belongsTo(Granja::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    /** RN01: um aviário só pode ter um lote aberto por vez. */
    public function possuiLoteAberto(?int $ignorarLoteId = null): bool
    {
        return $this->lotes()
            ->where('status', StatusLote::ABERTO)
            ->when($ignorarLoteId, fn (Builder $q) => $q->where('id', '!=', $ignorarLoteId))
            ->exists();
    }

    public function scopeLivres(Builder $query): Builder
    {
        return $query->whereDoesntHave('lotes', fn (Builder $q) => $q->where('status', StatusLote::ABERTO));
    }

    public function nomeCompleto(): string
    {
        return ($this->granja?->nome ?? 'Granja').' – Aviário '.$this->identificacao;
    }
}
