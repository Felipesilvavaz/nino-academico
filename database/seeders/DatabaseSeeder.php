<?php

namespace Database\Seeders;

use App\Enums\StatusLote;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\Produtor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dados fictícios para demonstração. Nenhum dado real da empresa é utilizado.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create(['name' => 'Administrador NINO', 'email' => 'admin@nino.test', 'password' => 'password', 'perfil' => 'admin']);
        $tecnico = User::create(['name' => 'Carlos Técnico', 'email' => 'tecnico@nino.test', 'password' => 'password', 'perfil' => 'tecnico']);
        User::create(['name' => 'Gabriela Gestora', 'email' => 'gestor@nino.test', 'password' => 'password', 'perfil' => 'gestor']);

        $p1 = Produtor::create(['nome' => 'João da Silva', 'cpf_cnpj' => '123.456.789-00', 'telefone' => '(43) 99999-0001', 'cidade' => 'Londrina', 'uf' => 'PR']);
        $p2 = Produtor::create(['nome' => 'Avícola Santa Clara Ltda.', 'cpf_cnpj' => '12.345.678/0001-90', 'telefone' => '(43) 3333-0002', 'cidade' => 'Cambé', 'uf' => 'PR']);

        $g1 = Granja::create(['produtor_id' => $p1->id, 'nome' => 'Granja Boa Vista', 'endereco' => 'Estrada Rural, km 12', 'cidade' => 'Londrina', 'uf' => 'PR', 'latitude' => -23.3045, 'longitude' => -51.1696]);
        $g2 = Granja::create(['produtor_id' => $p2->id, 'nome' => 'Granja Santa Clara', 'endereco' => 'PR-445, km 5', 'cidade' => 'Cambé', 'uf' => 'PR']);
        $g3 = Granja::create(['produtor_id' => $p2->id, 'nome' => 'Granja Recanto', 'cidade' => 'Rolândia', 'uf' => 'PR']);

        $av = [];
        foreach ([[$g1, '01', 1500, 20000, 'dark_house'], [$g1, '02', 1500, 20000, 'dark_house'],
                  [$g2, '01', 1200, 15000, 'convencional'], [$g2, '02', 1200, 15000, 'convencional'],
                  [$g3, '01', 1800, 25000, 'dark_house']] as [$g, $id, $area, $cap, $tipo]) {
            $av[] = $g->aviarios()->create(['identificacao' => $id, 'area_m2' => $area, 'capacidade_aves' => $cap, 'tipo' => $tipo]);
        }

        // Lote encerrado (ciclo completo de 45 dias)
        $this->criarLote($av[0], $tecnico, 'FC-2026-001', Carbon::today()->subDays(80), 19500, 45, true);
        // Lotes abertos em andamento
        $this->criarLote($av[1], $tecnico, 'FC-2026-002', Carbon::today()->subDays(28), 19800, 28);
        $this->criarLote($av[2], $tecnico, 'FC-2026-003', Carbon::today()->subDays(15), 14500, 15);
        $this->criarLote($av[4], $admin, 'FC-2026-004', Carbon::today()->subDays(35), 24000, 35);
    }

    private function criarLote($aviario, User $tecnico, string $codigo, Carbon $alojamento, int $qtd, int $dias, bool $encerrar = false): void
    {
        $lote = Lote::create([
            'aviario_id' => $aviario->id, 'tecnico_id' => $tecnico->id, 'codigo' => $codigo, 'linhagem' => 'Cobb 500',
            'data_alojamento' => $alojamento, 'qtd_alojada' => $qtd, 'peso_inicial_g' => 42,
            'data_abate_prevista' => $alojamento->copy()->addDays(45), 'status' => StatusLote::ABERTO,
        ]);

        for ($d = 1; $d <= $dias; $d++) {
            // curva aproximada de crescimento e consumo de frango de corte
            $peso = $d % 7 === 0 || $d === $dias ? round(42 + 7.48 * $d ** 1.576, 0) : null;
            $lote->acompanhamentos()->create([
                'user_id' => $tecnico->id,
                'data' => $alojamento->copy()->addDays($d),
                'idade_dias' => $d,
                'mortalidade' => $d <= 7 ? random_int(5, 25) : random_int(0, 10),
                'eliminados' => random_int(0, 3),
                'consumo_racao_kg' => round($qtd * (10 + 4.3 * $d) / 1000, 2),
                'peso_medio_g' => $peso,
                'temperatura_c' => round(33 - min($d, 28) * 0.4 + random_int(-10, 10) / 10, 1),
            ]);
        }

        if ($encerrar) {
            $lote->encerrar($alojamento->copy()->addDays($dias));
        }
    }
}
