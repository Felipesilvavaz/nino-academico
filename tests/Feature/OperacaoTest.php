<?php

namespace Tests\Feature;

use App\Enums\StatusLote;
use App\Models\Aviario;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\OcorrenciaSanitaria;
use App\Models\Produtor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** RF16 a RF27 – alertas, chamados sanitários, checklist, programação de abate e telas de Operação (UC07) */
class OperacaoTest extends TestCase
{
    use RefreshDatabase;

    private User $tecnico;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tecnico = User::factory()->create();
    }

    private function aviario(): Aviario
    {
        $produtor = Produtor::create(['nome' => 'Produtor', 'cpf_cnpj' => '000.000.000-00', 'cidade' => 'Londrina', 'uf' => 'PR']);
        $granja = Granja::create(['produtor_id' => $produtor->id, 'nome' => 'Granja', 'cidade' => 'Londrina', 'uf' => 'PR']);

        return $granja->aviarios()->create(['identificacao' => '01', 'area_m2' => 1200, 'capacidade_aves' => 15000, 'tipo' => 'convencional']);
    }

    private function lote(): Lote
    {
        return Lote::create([
            'aviario_id' => $this->aviario()->id, 'tecnico_id' => $this->tecnico->id, 'codigo' => 'FC-1', 'linhagem' => 'Cobb 500',
            'data_alojamento' => Carbon::today()->subDays(10), 'qtd_alojada' => 10000, 'peso_inicial_g' => 42, 'status' => StatusLote::ABERTO,
        ]);
    }

    public function test_abre_chamado_sanitario_para_lote_aberto(): void // RF19
    {
        $lote = $this->lote();

        $this->actingAs($this->tecnico)->post("/lotes/{$lote->id}/ocorrencias", [
            'tipo' => 'doenca', 'descricao' => 'Aves com sintomas respiratórios no aviário.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ocorrencias_sanitarias', ['lote_id' => $lote->id, 'status' => 'aberta']);
    }

    public function test_nao_abre_chamado_para_lote_encerrado(): void // RN02 aplicada ao chamado
    {
        $lote = $this->lote();
        $lote->encerrar(Carbon::today());

        $this->actingAs($this->tecnico)->post("/lotes/{$lote->id}/ocorrencias", [
            'tipo' => 'outro', 'descricao' => 'Teste',
        ])->assertSessionHas('error');

        $this->assertSame(0, $lote->ocorrenciasSanitarias()->count());
    }

    public function test_resolve_chamado_sanitario(): void // RN14
    {
        $lote = $this->lote();
        $ocorrencia = OcorrenciaSanitaria::create([
            'lote_id' => $lote->id, 'user_id' => $this->tecnico->id, 'tipo' => 'doenca',
            'descricao' => 'Teste', 'status' => 'aberta', 'data_abertura' => Carbon::today(),
        ]);

        $this->actingAs($this->tecnico)->patch("/ocorrencias/{$ocorrencia->id}/resolver")->assertRedirect();

        $this->assertSame('resolvida', $ocorrencia->fresh()->status);
        $this->assertNotNull($ocorrencia->fresh()->data_resolucao);
    }

    public function test_registra_checklist_e_tipo_racao_no_acompanhamento(): void // RF20, RF23
    {
        $lote = $this->lote();

        $this->actingAs($this->tecnico)->post("/lotes/{$lote->id}/acompanhamentos", [
            'data' => Carbon::today()->format('Y-m-d'), 'consumo_racao_kg' => 500, 'tipo_racao' => 'pelet',
            'checklist_agua' => '1', 'checklist_racao' => '1',
        ])->assertSessionHasNoErrors();

        $registro = $lote->acompanhamentos()->first();
        $this->assertSame('pelet', $registro->tipo_racao);
        $this->assertTrue($registro->checklist_agua);
        $this->assertFalse($registro->checklist_ventilacao);
        $this->assertSame(2, $registro->checklistConcluido());
    }

    public function test_encerrar_grava_rendimento_e_resultado_salmonella(): void // RF24
    {
        $lote = $this->lote();

        $this->actingAs($this->tecnico)->patch("/lotes/{$lote->id}/encerrar", [
            'data_encerramento' => Carbon::today()->format('Y-m-d'),
            'rendimento_pct' => 76.5, 'resultado_salmonella' => 'negativo',
        ])->assertSessionHasNoErrors();

        $lote->refresh();
        $this->assertSame(76.5, $lote->rendimento_pct);
        $this->assertSame('negativo', $lote->resultado_salmonella);
    }

    public function test_telas_de_operacao_respondem_para_tecnico(): void // UC07
    {
        $this->lote();

        $this->actingAs($this->tecnico)->get('/operacao/rota-tecnico')->assertOk();
        $this->actingAs($this->tecnico)->get('/operacao/programacao-abate')->assertOk();
        $this->actingAs($this->tecnico)->get('/operacao/lancamentos')->assertOk();
        $this->actingAs($this->tecnico)->get('/operacao/aviarios')->assertOk();
        $this->actingAs($this->tecnico)->get('/operacao/mapa-sanitario')->assertOk();
    }

    public function test_gestor_tambem_consulta_telas_de_operacao(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->get('/operacao/rota-tecnico')->assertOk();
        $this->actingAs($gestor)->get('/operacao/aviarios')->assertOk();
    }
}
