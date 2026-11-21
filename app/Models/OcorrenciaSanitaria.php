<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** RF19: chamado sanitário aberto pelo técnico, vinculado a um lote. */
class OcorrenciaSanitaria extends Model
{
    public const TIPOS = [
        'doenca' => 'Doença',
        'mortalidade_anormal' => 'Mortalidade anormal',
        'outro' => 'Outro',
    ];

    protected $table = 'ocorrencias_sanitarias';

    protected $fillable = [
        'lote_id', 'user_id', 'tipo', 'descricao', 'status', 'data_abertura', 'data_resolucao',
    ];

    protected function casts(): array
    {
        return [
            'data_abertura' => 'date',
            'data_resolucao' => 'date',
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

    public function estaAberta(): bool
    {
        return $this->status === 'aberta';
    }

    /** RN14: resolver um chamado grava a data de resolução e fecha o status. */
    public function resolver(): void
    {
        $this->update(['status' => 'resolvida', 'data_resolucao' => Carbon::today()]);
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
