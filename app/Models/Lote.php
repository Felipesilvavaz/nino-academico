<?php

namespace App\Models;

use App\Enums\StatusLote;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Lote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'aviario_id', 'tecnico_id', 'codigo', 'linhagem', 'data_alojamento', 'qtd_alojada',
        'peso_inicial_g', 'data_abate_prevista', 'data_encerramento', 'status',
    ];

    protected function casts(): array
    {
        return [
            'data_alojamento' => 'date',
            'data_abate_prevista' => 'date',
            'data_encerramento' => 'date',
            'qtd_alojada' => 'integer',
            'peso_inicial_g' => 'float',
            'status' => StatusLote::class,
        ];
    }

    // ---------------- Relacionamentos ----------------

    public function aviario(): BelongsTo
    {
        return $this->belongsTo(Aviario::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function acompanhamentos(): HasMany
    {
        return $this->hasMany(Acompanhamento::class)->orderBy('data');
    }

    // ---------------- Regras de negócio ----------------

    public function estaAberto(): bool
    {
        return $this->status === StatusLote::ABERTO;
    }

    /** Idade do lote em dias na data informada (padrão: encerramento ou hoje). */
    public function idadeAtual(?CarbonInterface $data = null): int
    {
        $referencia = $data ?? $this->data_encerramento ?? Carbon::today();

        return max(0, (int) round($this->data_alojamento->copy()->startOfDay()->diffInDays($referencia->copy()->startOfDay(), false)));
    }

    public function totalPerdas(): int
    {
        return (int) $this->acompanhamentos->sum(fn ($a) => $a->mortalidade + $a->eliminados);
    }

    public function avesVivas(): int
    {
        return max(0, $this->qtd_alojada - $this->totalPerdas());
    }

    /** RN07: viabilidade (%). */
    public function viabilidade(): float
    {
        if ($this->qtd_alojada === 0) {
            return 0.0;
        }

        return round($this->avesVivas() / $this->qtd_alojada * 100, 2);
    }

    public function consumoTotalKg(): float
    {
        return round((float) $this->acompanhamentos->sum('consumo_racao_kg'), 2);
    }

    /** Último registro que possui pesagem. */
    public function ultimaPesagem(): ?Acompanhamento
    {
        return $this->acompanhamentos->whereNotNull('peso_medio_g')->sortBy('data')->last();
    }

    public function pesoMedioAtualG(): ?float
    {
        return $this->ultimaPesagem()?->peso_medio_g;
    }

    /** Ganho médio diário (g/ave/dia). */
    public function gmd(): ?float
    {
        $pesagem = $this->ultimaPesagem();
        if (! $pesagem || $pesagem->idade_dias === 0) {
            return null;
        }

        return round(($pesagem->peso_medio_g - $this->peso_inicial_g) / $pesagem->idade_dias, 2);
    }

    /** RN08: conversão alimentar = ração consumida (kg) / peso vivo total (kg). */
    public function conversaoAlimentar(): ?float
    {
        $peso = $this->pesoMedioAtualG();
        $pesoVivoKg = $peso ? $this->avesVivas() * $peso / 1000 : 0;
        if ($pesoVivoKg <= 0 || $this->consumoTotalKg() <= 0) {
            return null;
        }

        return round($this->consumoTotalKg() / $pesoVivoKg, 3);
    }

    /** RN09: IEP = (viabilidade × peso médio kg) / (idade × CA) × 100. */
    public function iep(): ?float
    {
        $ca = $this->conversaoAlimentar();
        $pesagem = $this->ultimaPesagem();
        if (! $ca || ! $pesagem || $pesagem->idade_dias === 0) {
            return null;
        }

        return round(($this->viabilidade() * ($pesagem->peso_medio_g / 1000)) / ($pesagem->idade_dias * $ca) * 100, 0);
    }

    public function encerrar(CarbonInterface $data): void
    {
        $this->update([
            'status' => StatusLote::ENCERRADO,
            'data_encerramento' => $data,
        ]);
    }
}
