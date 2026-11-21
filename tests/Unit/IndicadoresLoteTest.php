<?php

namespace Tests\Unit;

use App\Models\Acompanhamento;
use App\Models\Lote;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** RN07, RN08 e RN09 – cálculo dos indicadores zootécnicos */
class IndicadoresLoteTest extends TestCase
{
    private function lote(): Lote
    {
        $lote = new Lote(['qtd_alojada' => 10000, 'peso_inicial_g' => 42]);
        $lote->data_alojamento = Carbon::create(2026, 1, 1);
        $lote->setRelation('acompanhamentos', collect([
            new Acompanhamento(['idade_dias' => 20, 'mortalidade' => 300, 'eliminados' => 0, 'consumo_racao_kg' => 20000, 'peso_medio_g' => 900]),
            new Acompanhamento(['idade_dias' => 42, 'mortalidade' => 200, 'eliminados' => 0, 'consumo_racao_kg' => 26550, 'peso_medio_g' => 2800]),
        ])->each(function ($a, $i) { $a->data = Carbon::create(2026, 1, 1)->addDays([20, 42][$i]); }));

        return $lote;
    }

    public function test_viabilidade(): void
    {
        $this->assertSame(9500, $this->lote()->avesVivas());
        $this->assertSame(95.0, $this->lote()->viabilidade());
    }

    public function test_conversao_alimentar(): void
    {
        // 46.550 kg / (9.500 aves × 2,8 kg) = 1,75
        $this->assertSame(1.75, $this->lote()->conversaoAlimentar());
    }

    public function test_iep(): void
    {
        // (95 × 2,8) / (42 × 1,75) × 100 = 361,9 → 362
        $this->assertSame(362.0, $this->lote()->iep());
    }

    public function test_idade_em_dias(): void
    {
        $this->assertSame(10, $this->lote()->idadeAtual(Carbon::create(2026, 1, 11)));
    }
}
