<?php

namespace App\Http\Controllers;

use App\Http\Requests\OcorrenciaSanitariaRequest;
use App\Models\Lote;
use App\Models\OcorrenciaSanitaria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

/** RF19: Chamados sanitários (inspirado na seção "Operação" do módulo Frango de Corte). */
class OcorrenciaSanitariaController extends Controller
{
    public function store(OcorrenciaSanitariaRequest $request, Lote $lote): RedirectResponse
    {
        if (! $lote->estaAberto()) {
            return back()->with('error', 'Só é possível abrir chamado sanitário para um lote aberto.');
        }

        $lote->ocorrenciasSanitarias()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => 'aberta',
            'data_abertura' => Carbon::today(),
        ]);

        return back()->with('success', 'Chamado sanitário aberto.');
    }

    public function resolver(OcorrenciaSanitaria $ocorrencia): RedirectResponse
    {
        abort_unless(request()->user()->can('operar'), 403);

        if (! $ocorrencia->estaAberta()) {
            return back()->with('error', 'Este chamado já está resolvido.');
        }

        $ocorrencia->resolver();

        return redirect()->route('lotes.show', $ocorrencia->lote)->with('success', 'Chamado sanitário resolvido.');
    }
}
