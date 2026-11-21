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
        'peso_inicial_g', 'data_abate_prevista', 'frigorifico_destino',
        'data_encerramento', 'rendimento_pct', 'resultado_salmonella', 'status',
    ];

    public const RESULTADOS_SALMONELLA = [
        'nao_testado' => 'Não testado',
        'negativo' => 'Negativo',
        'positivo' => 'Positivo',
    ];

    protected function casts(): array
    {
        return [
            'data_alojamento' => 'date',
            'data_abate_prevista' => 'date',
            'data_encerramento' => 'date',
            'qtd_alojada' => 'integer',
            'peso_inicial_g' => 'float',
            'rendimento_pct' => 'float',
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

    /** RF19: chamados sanitários abertos pelo técnico para este lote. */
    public function ocorrenciasSanitarias(): HasMany
    {
        return $this->hasMany(OcorrenciaSanitaria::class)->orderByDesc('data_abertura');
    }

    public function ocorrenciasSanitariasAbertas(): int
    {
        return $this->ocorrenciasSanitarias->where('status', 'aberta')->count();
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

    /**
     * RN13: projeta o peso médio (g) na data de abate prevista, extrapolando o GMD atual
     * do lote a partir do último registro. Retorna null se faltar data de abate prevista,
     * GMD ou se o lote já estiver encerrado.
     */
    public function pesoProjetadoAbateG(): ?float
    {
        if (! $this->data_abate_prevista || ! $this->estaAberto()) {
            return null;
        }

        $pesagem = $this->ultimaPesagem();
        $gmdAtual = $this->gmd();
        if (! $pesagem || ! $gmdAtual) {
            return null;
        }

        $idadeNoAbate = $this->idadeAtual($this->data_abate_prevista);
        $diasRestantes = $idadeNoAbate - $pesagem->idade_dias;
        if ($diasRestantes <= 0) {
            return $pesagem->peso_medio_g;
        }

        return round($pesagem->peso_medio_g + $gmdAtual * $diasRestantes, 0);
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

    /**
     * Encerra o lote (abate). $frigorificoDestino, $rendimentoPct e $resultadoSalmonella
     * são os dados do informe de rendimento e do monitoramento de Salmonella (RF24),
     * preenchidos pelo técnico no momento do encerramento.
     */
    public function encerrar(
        CarbonInterface $data,
        ?string $frigorificoDestino = null,
        ?float $rendimentoPct = null,
        ?string $resultadoSalmonella = null,
    ): void {
        $this->update([
            'status' => StatusLote::ENCERRADO,
            'data_encerramento' => $data,
            'frigorifico_destino' => $frigorificoDestino ?? $this->frigorifico_destino,
            'rendimento_pct' => $rendimentoPct,
            'resultado_salmonella' => $resultadoSalmonella ?? 'nao_testado',
        ]);
    }

    // ---------------- Alertas de acompanhamento ----------------

    /**
     * Curva de referência de peso médio por idade (g), usada só para sinalizar
     * desvio no acompanhamento. É uma curva genérica de frango de corte,
     * não um parâmetro de linhagem real de nenhuma empresa.
     */
    private const CURVA_PESO_REFERENCIA = [
        0 => 42, 7 => 175, 14 => 430, 21 => 850, 28 => 1380,
        35 => 1950, 42 => 2450, 49 => 2850, 56 => 3150,
    ];

    /** Peso médio esperado (g) para a idade informada, por interpolação linear na curva de referência. */
    public function pesoEsperadoG(int $idadeDias): float
    {
        $pontos = self::CURVA_PESO_REFERENCIA;
        $idades = array_keys($pontos);

        if ($idadeDias <= $idades[0]) {
            return $pontos[$idades[0]];
        }

        foreach ($idades as $i => $idade) {
            if ($idadeDias <= $idade) {
                $idadeAnterior = $idades[$i - 1];

                return $pontos[$idadeAnterior]
                    + ($pontos[$idade] - $pontos[$idadeAnterior])
                    * (($idadeDias - $idadeAnterior) / ($idade - $idadeAnterior));
            }
        }

        return $pontos[end($idades)];
    }

    /** RN10: sinaliza quando o peso do último registro está abaixo de 85% do esperado para a idade. */
    public function alertaPeso(): bool
    {
        $pesagem = $this->ultimaPesagem();
        if (! $pesagem) {
            return false;
        }

        return $pesagem->peso_medio_g < $this->pesoEsperadoG($pesagem->idade_dias) * 0.85;
    }

    /** RN11: sinaliza quando a perda (mortalidade + eliminados) do último registro passa de 0,5% das aves vivas até então. */
    public function alertaMortalidade(): bool
    {
        $ordenados = $this->acompanhamentos->sortBy('data')->values();
        if ($ordenados->isEmpty()) {
            return false;
        }

        $ultimo = $ordenados->last();
        $perdasAnteriores = $ordenados->slice(0, -1)->sum(fn ($a) => $a->mortalidade + $a->eliminados);
        $vivasAntes = $this->qtd_alojada - $perdasAnteriores;
        $perdaDoDia = $ultimo->mortalidade + $ultimo->eliminados;

        return $vivasAntes > 0 && ($perdaDoDia / $vivasAntes) > 0.005;
    }

    /** Dias desde o último acompanhamento registrado (ou desde o alojamento, se nunca registrou). */
    public function diasSemAcompanhamento(): int
    {
        $ultimo = $this->acompanhamentos->sortBy('data')->last();
        $referencia = $ultimo ? $ultimo->data : $this->data_alojamento;

        return max(0, $referencia->copy()->startOfDay()->diffInDays(Carbon::today(), false));
    }

    /** RN12: lote aberto sem acompanhamento há mais de 7 dias precisa de visita técnica. */
    public function precisaVisita(): bool
    {
        return $this->estaAberto() && $this->diasSemAcompanhamento() > 7;
    }
}
