<?php

namespace App\Http\Controllers;

use App\Models\Granja;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** UC06 – Consultar desempenho do lote. */
class RelatorioController extends Controller
{
    public function desempenho(Request $request): View
    {
        $filtros = $request->validate([
            'granja_id' => ['nullable', 'exists:granjas,id'],
            'status' => ['nullable', 'in:aberto,encerrado,cancelado'],
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date', 'after_or_equal:inicio'],
        ]);

        // Eager loading evita o problema de consultas N+1.
        $lotes = Lote::with(['aviario.granja', 'acompanhamentos'])
            ->when($filtros['granja_id'] ?? null, fn ($q, $id) => $q->whereHas('aviario', fn ($a) => $a->where('granja_id', $id)))
            ->when($filtros['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filtros['inicio'] ?? null, fn ($q, $d) => $q->whereDate('data_alojamento', '>=', $d))
            ->when($filtros['fim'] ?? null, fn ($q, $d) => $q->whereDate('data_alojamento', '<=', $d))
            ->orderByDesc('data_alojamento')
            ->get();

        $linhas = $lotes->map(fn (Lote $l) => [
            'lote' => $l,
            'idade' => $l->idadeAtual(),
            'vivas' => $l->avesVivas(),
            'viabilidade' => $l->viabilidade(),
            'peso' => $l->pesoMedioAtualG(),
            'gmd' => $l->gmd(),
            'ca' => $l->conversaoAlimentar(),
            'iep' => $l->iep(),
            'projecao' => $l->pesoProjetadoAbateG(),
            'alertaPeso' => $l->alertaPeso(),
            'alertaMortalidade' => $l->alertaMortalidade(),
        ]);

        // RF18: relatório de peso ao abate, só com os lotes já encerrados.
        $abatidos = $lotes->filter(fn (Lote $l) => $l->status === \App\Enums\StatusLote::ENCERRADO)
            ->map(fn (Lote $l) => [
                'lote' => $l,
                'idadeAbate' => $l->idadeAtual(),
                'pesoInicial' => $l->peso_inicial_g,
                'pesoAbate' => $l->pesoMedioAtualG(),
                'ganhoTotal' => $l->pesoMedioAtualG() ? round($l->pesoMedioAtualG() - $l->peso_inicial_g, 0) : null,
                'gmd' => $l->gmd(),
                'viabilidade' => $l->viabilidade(),
            ])->values();

        return view('relatorios.desempenho', [
            'linhas' => $linhas,
            'abatidos' => $abatidos,
            'granjas' => Granja::orderBy('nome')->get(),
            'filtros' => $filtros,
        ]);
    }
}
