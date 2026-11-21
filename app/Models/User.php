<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const PERFIS = [
        'admin' => 'Administrador',
        'tecnico' => 'Técnico de Campo',
        'gestor' => 'Gestor',
    ];

    protected $fillable = ['name', 'email', 'password', 'perfil', 'ativo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->perfil === 'admin';
    }

    public function isTecnico(): bool
    {
        return $this->perfil === 'tecnico';
    }

    public function perfilLabel(): string
    {
        return self::PERFIS[$this->perfil] ?? $this->perfil;
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'tecnico_id');
    }

    public function acompanhamentos(): HasMany
    {
        return $this->hasMany(Acompanhamento::class);
    }
}
