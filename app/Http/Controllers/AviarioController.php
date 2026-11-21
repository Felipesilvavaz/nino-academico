<?php

namespace App\Http\Controllers;

use App\Http\Requests\AviarioRequest;
use App\Models\Aviario;
use App\Models\Granja;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** UC03 – Manter granjas e aviários (aviários de uma granja). */
class AviarioController extends Controller
{
    public function create(Granja $granja): View
    {
        return view('aviarios.form', ['granja' => $granja, 'aviario' => new Aviario(['tipo' => 'convencional'])]);
    }

    public function store(AviarioRequest $request, Granja $granja): RedirectResponse
    {
        $granja->aviarios()->create($request->validated());

        return redirect()->route('granjas.show', $granja)->with('success', 'Aviário cadastrado com sucesso.');
    }

    public function edit(Aviario $aviario): View
    {
        return view('aviarios.form', ['granja' => $aviario->granja, 'aviario' => $aviario]);
    }

    public function update(AviarioRequest $request, Aviario $aviario): RedirectResponse
    {
        $aviario->update($request->validated());

        return redirect()->route('granjas.show', $aviario->granja_id)->with('success', 'Aviário atualizado com sucesso.');
    }

    public function destroy(Aviario $aviario): RedirectResponse
    {
        // RN05: aviário com lotes vinculados não pode ser excluído.
        if ($aviario->lotes()->withTrashed()->exists()) {
            return back()->with('error', 'Não é possível excluir: o aviário possui lotes vinculados.');
        }

        $granjaId = $aviario->granja_id;
        $aviario->delete();

        return redirect()->route('granjas.show', $granjaId)->with('success', 'Aviário excluído.');
    }
}
