<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcompanhamentoRequest;
use App\Models\Acompanhamento;
use App\Models\Lote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** UC05 – Registrar acompanhamento diário. A listagem fica na ficha do lote. */
class AcompanhamentoController extends Controller
{
    public function store(AcompanhamentoRequest $request, Lote $lote): RedirectResponse
    {
        // RN02: não registrar em lote encerrado ou cancelado.
        if (! $lote->estaAberto()) {
            return back()->with('error', 'Não é possível registrar acompanhamento em um lote encerrado.');
        }

        $dados = $request->validated();
        $lote->acompanhamentos()->create([
            ...$dados,
            'user_id' => $request->user()->id,
            'idade_dias' => $lote->idadeAtual(Carbon::parse($dados['data'])),
        ]);

        return redirect()->route('lotes.show', $lote)->with('success', 'Acompanhamento registrado.');
    }

    public function edit(Acompanhamento $acompanhamento): View
    {
        return view('acompanhamentos.form', ['acompanhamento' => $acompanhamento, 'lote' => $acompanhamento->lote]);
    }

    public function update(AcompanhamentoRequest $request, Acompanhamento $acompanhamento): RedirectResponse
    {
        $lote = $acompanhamento->lote;
        if (! $lote->estaAberto()) {
            return redirect()->route('lotes.show', $lote)->with('error', 'Lote encerrado: os registros não podem ser alterados.');
        }

        $dados = $request->validated();
        $acompanhamento->update([
            ...$dados,
            'idade_dias' => $lote->idadeAtual(Carbon::parse($dados['data'])),
        ]);

        return redirect()->route('lotes.show', $lote)->with('success', 'Acompanhamento atualizado.');
    }

    public function destroy(Acompanhamento $acompanhamento): RedirectResponse
    {
        $lote = $acompanhamento->lote;
        if (! $lote->estaAberto()) {
            return back()->with('error', 'Lote encerrado: os registros não podem ser excluídos.');
        }

        $acompanhamento->delete();

        return redirect()->route('lotes.show', $lote)->with('success', 'Acompanhamento excluído.');
    }
}
