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

    public function test_peso_esperado_interpola_a_curva_de_referencia(): void
    {
        // entre os pontos de 14 dias (430g) e 21 dias (850g): 430 + 420*(3/7) = 610
        $this->assertSame(610.0, $this->lote()->pesoEsperadoG(17));
    }

    public function test_alerta_peso_quando_abaixo_de_85_por_cento_da_curva(): void
    {
        $lote = new Lote(['qtd_alojada' => 10000, 'peso_inicial_g' => 42]);
        $lote->data_alojamento = Carbon::create(2026, 1, 1);
        $lote->setRelation('acompanhamentos', collect([
            new Acompanhamento(['data' => Carbon::create(2026, 1, 22), 'idade_dias' => 21, 'mortalidade' => 0, 'eliminados' => 0, 'consumo_racao_kg' => 1000, 'peso_medio_g' => 600]),
        ]));
        // esperado aos 21 dias = 850g; 600g é 70,6% disso -> alerta
        $this->assertTrue($lote->alertaPeso());
    }

    public function test_sem_alerta_peso_quando_dentro_da_curva(): void
    {
        $this->assertFalse($this->lote()->alertaPeso()); // 900g aos 20 dias está acima da curva
    }

    public function test_alerta_mortalidade_quando_perda_do_dia_passa_de_meio_por_cento(): void
    {
        $lote = new Lote(['qtd_alojada' => 10000, 'peso_inicial_g' => 42]);
        $lote->data_alojamento = Carbon::create(2026, 1, 1);
        $lote->setRelation('acompanhamentos', collect([
            new Acompanhamento(['data' => Carbon::create(2026, 1, 8), 'idade_dias' => 7, 'mortalidade' => 20, 'eliminados' => 0, 'consumo_racao_kg' => 500, 'peso_medio_g' => 175]),
            new Acompanhamento(['data' => Carbon::create(2026, 1, 15), 'idade_dias' => 14, 'mortalidade' => 80, 'eliminados' => 0, 'consumo_racao_kg' => 1000, 'peso_medio_g' => 430]),
        ]));
        // vivas antes do 2º registro: 10000-20=9980; perda do dia: 80 -> 0,80% > 0,5%
        $this->assertTrue($lote->alertaMortalidade());
    }

    public function test_dias_sem_acompanhamento_e_precisa_visita(): void
    {
        $lote = new Lote(['qtd_alojada' => 10000, 'peso_inicial_g' => 42, 'status' => \App\Enums\StatusLote::ABERTO]);
        $lote->data_alojamento = Carbon::today()->subDays(15);
        $lote->setRelation('acompanhamentos', collect([
            new Acompanhamento(['data' => Carbon::today()->subDays(10), 'idade_dias' => 5, 'mortalidade' => 0, 'eliminados' => 0, 'consumo_racao_kg' => 100, 'peso_medio_g' => 150]),
        ]));
        $this->assertSame(10, $lote->diasSemAcompanhamento());
        $this->assertTrue($lote->precisaVisita());
    }

    public function test_projecao_de_peso_no_abate(): void
    {
        $lote = new Lote(['qtd_alojada' => 10000, 'peso_inicial_g' => 42, 'status' => \App\Enums\StatusLote::ABERTO]);
        $lote->data_alojamento = Carbon::create(2026, 1, 1);
        $lote->data_abate_prevista = Carbon::create(2026, 2, 12); // idade 42 no abate
        $lote->setRelation('acompanhamentos', collect([
            new Acompanhamento(['data' => Carbon::create(2026, 1, 21), 'idade_dias' => 20, 'mortalidade' => 0, 'eliminados' => 0, 'consumo_racao_kg' => 500, 'peso_medio_g' => 900]),
        ]));
        // GMD = (900-42)/20 = 42,9 g/dia; faltam 22 dias até o abate (42-20)
        // projeção = 900 + 42,9*22 = 1843,8 -> arredonda pra 1844
        $this->assertSame(1844.0, $lote->pesoProjetadoAbateG());
    }
}
