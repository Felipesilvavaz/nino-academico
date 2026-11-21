<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produtor extends Model
{
    protected $table = 'produtores';

    protected $fillable = ['nome', 'cpf_cnpj', 'telefone', 'cidade', 'uf'];

    public function granjas(): HasMany
    {
        return $this->hasMany(Granja::class);
    }
}
