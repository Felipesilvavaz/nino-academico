<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Granja extends Model
{
    protected $fillable = ['produtor_id', 'nome', 'endereco', 'cidade', 'uf', 'latitude', 'longitude', 'ativo'];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function produtor(): BelongsTo
    {
        return $this->belongsTo(Produtor::class);
    }

    public function aviarios(): HasMany
    {
        return $this->hasMany(Aviario::class)->orderBy('identificacao');
    }

    public function lotes(): HasManyThrough
    {
        return $this->hasManyThrough(Lote::class, Aviario::class);
    }
}
