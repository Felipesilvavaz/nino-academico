<?php

namespace Tests\Feature;

use App\Enums\StatusLote;
use App\Models\Aviario;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\Produtor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** UC03, UC04 e UC05 – granjas, lotes e acompanhamento */
class LoteTest extends TestCase
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

    private function dadosLote(Aviario $aviario, string $codigo = 'FC-1'): array
    {
        return ['aviario_id' => $aviario->id, 'codigo' => $codigo, 'linhagem' => 'Cobb 500',
            'data_alojamento' => Carbon::today()->subDays(10)->format('Y-m-d'), 'qtd_alojada' => 10000, 'peso_inicial_g' => 42];
    }

    public function test_cadastra_granja_com_aviarios(): void
    {
        $produtor = Produtor::create(['nome' => 'P', 'cpf_cnpj' => '1', 'cidade' => 'Londrina', 'uf' => 'PR']);

        $this->actingAs($this->tecnico)->post('/granjas', [
            'produtor_id' => $produtor->id, 'nome' => 'Granja Nova', 'cidade' => 'Londrina', 'uf' => 'PR', 'ativo' => '1',
            'aviarios' => [
                ['identificacao' => '01', 'area_m2' => 1000, 'capacidade_aves' => 12000, 'tipo' => 'convencional'],
                ['identificacao' => '02', 'area_m2' => 1000, 'capacidade_aves' => 12000, 'tipo' => 'dark_house'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Granja::firstWhere('nome', 'Granja Nova')->aviarios()->count());
    }

    public function test_aloja_lote_em_aviario_livre(): void
    {
        $aviario = $this->aviario();

        $this->actingAs($this->tecnico)->post('/lotes', $this->dadosLote($aviario))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lotes', ['codigo' => 'FC-1', 'status' => 'aberto', 'tecnico_id' => $this->tecnico->id]);
    }

    public function test_nao_aloja_segundo_lote_no_mesmo_aviario(): void // RN01
    {
        $aviario = $this->aviario();
        $this->actingAs($this->tecnico)->post('/lotes', $this->dadosLote($aviario));

        $this->actingAs($this->tecnico)->post('/lotes', $this->dadosLote($aviario, 'FC-2'))->assertSessionHasErrors('aviario_id');
    }

    public function test_registra_acompanhamento_e_calcula_idade(): void
    {
        $this->actingAs($this->tecnico)->post('/lotes', $this->dadosLote($this->aviario()));
        $lote = Lote::first();

        $this->actingAs($this->tecnico)->post("/lotes/{$lote->id}/acompanhamentos", [
            'data' => Carbon::today()->format('Y-m-d'), 'mortalidade' => 10, 'eliminados' => 2, 'consumo_racao_kg' => 500, 'peso_medio_g' => 300,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('acompanhamentos', ['lote_id' => $lote->id, 'idade_dias' => 10]);
        $this->assertSame(9988, $lote->fresh()->avesVivas());
    }

    public function test_nao_registra_acompanhamento_em_lote_encerrado(): void // RN02
    {
        $this->actingAs($this->tecnico)->post('/lotes', $this->dadosLote($this->aviario()));
        $lote = Lote::first();
        $lote->encerrar(Carbon::today());

        $this->actingAs($this->tecnico)->post("/lotes/{$lote->id}/acompanhamentos", [
            'data' => Carbon::today()->format('Y-m-d'), 'consumo_racao_kg' => 500,
        ])->assertSessionHas('error');

        $this->assertSame(0, $lote->acompanhamentos()->count());
    }

    public function test_encerrar_libera_o_aviario(): void
    {
        $aviario = $this->aviario();
        $this->actingAs($this->tecnico)->post('/lotes', $this->dadosLote($aviario));
        $lote = Lote::first();

        $this->actingAs($this->tecnico)->patch("/lotes/{$lote->id}/encerrar", ['data_encerramento' => Carbon::today()->format('Y-m-d')]);

        $this->assertSame(StatusLote::ENCERRADO, $lote->fresh()->status);
        $this->assertFalse($aviario->possuiLoteAberto());
    }

    public function test_gestor_ve_relatorio_mas_nao_aloja_lote(): void
    {
        $gestor = User::factory()->gestor()->create();

        $this->actingAs($gestor)->get('/relatorios/desempenho')->assertOk();
        $this->actingAs($gestor)->get('/lotes/create')->assertForbidden();
    }
}
