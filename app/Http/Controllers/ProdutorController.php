<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdutorRequest;
use App\Models\Produtor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** UC03 – Manter granjas e aviários (cadastro de produtores integrados). */
class ProdutorController extends Controller
{
    public function index(Request $request): View
    {
        $produtores = Produtor::withCount('granjas')
            ->when($request->filled('busca'), fn ($q) => $q->where('nome', 'like', '%'.$request->busca.'%'))
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('produtores.index', compact('produtores'));
    }

    public function create(): View
    {
        return view('produtores.form', ['produtor' => new Produtor()]);
    }

    public function store(ProdutorRequest $request): RedirectResponse
    {
        Produtor::create($request->validated());

        return redirect()->route('produtores.index')->with('success', 'Produtor cadastrado com sucesso.');
    }

    public function edit(Produtor $produtor): View
    {
        return view('produtores.form', compact('produtor'));
    }

    public function update(ProdutorRequest $request, Produtor $produtor): RedirectResponse
    {
        $produtor->update($request->validated());

        return redirect()->route('produtores.index')->with('success', 'Produtor atualizado com sucesso.');
    }

    public function destroy(Produtor $produtor): RedirectResponse
    {
        if ($produtor->granjas()->exists()) {
            return back()->with('error', 'Não é possível excluir: o produtor possui granjas cadastradas.');
        }

        $produtor->delete();

        return redirect()->route('produtores.index')->with('success', 'Produtor excluído.');
    }
}
