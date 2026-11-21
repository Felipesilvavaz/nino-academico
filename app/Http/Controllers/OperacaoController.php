<?php

namespace App\Http\Controllers;

use App\Enums\StatusLote;
use App\Models\Acompanhamento;
use App\Models\Aviario;
use App\Models\Granja;
use App\Models\Lote;
use Illuminate\View\View;

/**
 * UC07 – Acompanhar a operação do módulo. Telas inspiradas na seção "Operação" e
 * "Relatórios" do módulo Frango de Corte: rota do técnico, programação de abate,
 * lançamentos recentes, visão geral dos aviários e mapa sanitário.
 */
class OperacaoController extends Controller
{
    /** RF17 / RF21: lotes abertos ordenados pelo tempo sem acompanhamento (mais urgente primeiro). */
    public function rotaTecnico(): View
    {
        $lotes = Lote::with(['aviario.granja', 'tecnico', 'acompanhamentos'])
            ->where('status', StatusLote::ABERTO)
            ->get()
            ->sortByDesc(fn (Lote $l) => $l->diasSemAcompanhamento())
            ->values();

        return view('operacao.rota-tecnico', compact('lotes'));
    }

    /** RF16 / RF22: lotes abertos ordenados pela data de abate prevista, com peso projetado. */
    public function programacaoAbate(): View
    {
        $lotes = Lote::with(['aviario.granja', 'tecnico'])
            ->where('status', StatusLote::ABERTO)
            ->whereNotNull('data_abate_prevista')
            ->orderBy('data_abate_prevista')
            ->get();

        return view('operacao.programacao-abate', compact('lotes'));
    }

    /** RF25: últimos acompanhamentos lançados em todos os lotes. */
    public function lancamentos(): View
    {
        $lancamentos = Acompanhamento::with(['lote.aviario.granja', 'usuario'])
            ->latest('data')
            ->latest('id')
            ->take(30)
            ->get();

        // RF23: ganho médio diário (GMD) dos lotes, agrupado pelo tipo de ração mais recente.
        $comparativoRacao = Lote::with('acompanhamentos')
            ->get()
            ->map(function (Lote $l) {
                $ultimoComRacao = $l->acompanhamentos->whereNotNull('tipo_racao')->sortBy('data')->last();

                return $ultimoComRacao ? ['tipo' => $ultimoComRacao->tipo_racao, 'gmd' => $l->gmd()] : null;
            })
            ->filter(fn ($r) => $r && $r['gmd'] !== null)
            ->groupBy('tipo')
            ->map(fn ($grupo) => round($grupo->avg('gmd'), 2));

        return view('operacao.lancamentos', compact('lancamentos', 'comparativoRacao'));
    }

    /** RF26: visão geral de todos os aviários (status, lote atual e indicadores). */
    public function aviarios(): View
    {
        $aviarios = Aviario::with(['granja', 'lotes' => fn ($q) => $q->where('status', StatusLote::ABERTO)->with('acompanhamentos')])
            ->get()
            ->sortBy(fn (Aviario $a) => $a->nomeCompleto());

        return view('operacao.aviarios', compact('aviarios'));
    }

    /** RF27: contagem de chamados sanitários abertos por granja. */
    public function mapaSanitario(): View
    {
        $granjas = Granja::with('aviarios.lotes.ocorrenciasSanitarias')->orderBy('nome')->get();

        return view('operacao.mapa-sanitario', compact('granjas'));
    }
}
