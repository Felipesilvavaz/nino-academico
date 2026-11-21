<?php

namespace App\Http\Controllers;

use App\Http\Requests\GranjaRequest;
use App\Models\Granja;
use App\Models\Produtor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** UC03 – Manter granjas e aviários. */
class GranjaController extends Controller
{
    public function index(Request $request): View
    {
        $granjas = Granja::with('produtor')
            ->withCount('aviarios')
            ->when($request->filled('busca'), fn ($q) => $q->where('nome', 'like', '%'.$request->busca.'%'))
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('granjas.index', compact('granjas'));
    }

    public function create(): View
    {
        return view('granjas.form', [
            'granja' => new Granja(['ativo' => true]),
            'produtores' => Produtor::orderBy('nome')->get(),
        ]);
    }

    public function store(GranjaRequest $request): RedirectResponse
    {
        // A granja e seus aviários são gravados na mesma transação.
        $granja = DB::transaction(function () use ($request) {
            $granja = Granja::create(Arr::except($request->validated(), 'aviarios'));
            foreach ($request->validated('aviarios') as $aviario) {
                $granja->aviarios()->create($aviario);
            }

            return $granja;
        });

        return redirect()->route('granjas.show', $granja)->with('success', 'Granja cadastrada com sucesso.');
    }

    public function show(Granja $granja): View
    {
        $granja->load(['produtor', 'aviarios.lotes' => fn ($q) => $q->latest('data_alojamento')]);

        return view('granjas.show', compact('granja'));
    }

    public function edit(Granja $granja): View
    {
        return view('granjas.form', [
            'granja' => $granja,
            'produtores' => Produtor::orderBy('nome')->get(),
        ]);
    }

    public function update(GranjaRequest $request, Granja $granja): RedirectResponse
    {
        $granja->update(Arr::except($request->validated(), 'aviarios'));

        return redirect()->route('granjas.show', $granja)->with('success', 'Granja atualizada com sucesso.');
    }

    public function destroy(Granja $granja): RedirectResponse
    {
        // RN05: granja com lotes vinculados não pode ser excluída, apenas inativada.
        if ($granja->lotes()->withTrashed()->exists()) {
            return back()->with('error', 'Não é possível excluir: a granja possui lotes vinculados. Inative-a pela edição.');
        }

        $granja->delete();

        return redirect()->route('granjas.index')->with('success', 'Granja excluída.');
    }
}
