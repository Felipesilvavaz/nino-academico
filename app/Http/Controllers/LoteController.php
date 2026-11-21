<?php

namespace App\Http\Controllers;

use App\Enums\StatusLote;
use App\Http\Requests\LoteRequest;
use App\Models\Aviario;
use App\Models\Granja;
use App\Models\Lote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** UC04 – Manter lotes. */
class LoteController extends Controller
{
    public function index(Request $request): View
    {
        $lotes = Lote::with(['aviario.granja', 'tecnico', 'acompanhamentos'])
            ->when($request->filled('granja_id'), fn ($q) => $q->whereHas('aviario', fn ($a) => $a->where('granja_id', $request->granja_id)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('busca'), fn ($q) => $q->where('codigo', 'like', '%'.$request->busca.'%'))
            ->orderByDesc('data_alojamento')
            ->paginate(15)
            ->withQueryString();

        return view('lotes.index', [
            'lotes' => $lotes,
            'granjas' => Granja::orderBy('nome')->get(),
        ]);
    }

    public function create(): View
    {
        return view('lotes.form', [
            'lote' => new Lote(['data_alojamento' => Carbon::today(), 'peso_inicial_g' => 42, 'linhagem' => 'Cobb 500']),
            'aviarios' => Aviario::with('granja')->livres()->get()->sortBy(fn ($a) => $a->nomeCompleto()),
        ]);
    }

    public function store(LoteRequest $request): RedirectResponse
    {
        $lote = Lote::create([
            ...$request->validated(),
            'tecnico_id' => $request->user()->id,
            'status' => StatusLote::ABERTO,
        ]);

        return redirect()->route('lotes.show', $lote)->with('success', 'Lote alojado com sucesso.');
    }

    public function show(Lote $lote): View
    {
        $lote->load(['aviario.granja.produtor', 'tecnico', 'acompanhamentos.usuario', 'ocorrenciasSanitarias.usuario']);

        return view('lotes.show', compact('lote'));
    }

    public function edit(Lote $lote): View
    {
        return view('lotes.form', [
            'lote' => $lote,
            'aviarios' => Aviario::with('granja')
                ->where(fn ($q) => $q->livres()->orWhere('id', $lote->aviario_id))
                ->get()->sortBy(fn ($a) => $a->nomeCompleto()),
        ]);
    }

    public function update(LoteRequest $request, Lote $lote): RedirectResponse
    {
        $lote->update($request->validated());

        return redirect()->route('lotes.show', $lote)->with('success', 'Lote atualizado com sucesso.');
    }

    /** Encerra o lote (abate) e libera o aviário para um novo alojamento. */
    public function encerrar(Request $request, Lote $lote): RedirectResponse
    {

        if (! $lote->estaAberto()) {
            return back()->with('error', 'Somente lotes abertos podem ser encerrados.');
        }

        $dados = $request->validate([
            'data_encerramento' => ['required', 'date', 'after_or_equal:'.$lote->data_alojamento->format('Y-m-d'), 'before_or_equal:today'],
            // RF24: informe de rendimento e de monitoramento de Salmonella, preenchidos no abate.
            'rendimento_pct' => ['nullable', 'numeric', 'between:0,100'],
            'resultado_salmonella' => ['nullable', Rule::in(array_keys(Lote::RESULTADOS_SALMONELLA))],
        ], [], [
            'data_encerramento' => 'data de encerramento',
            'rendimento_pct' => 'rendimento (%)',
            'resultado_salmonella' => 'resultado do monitoramento de Salmonella',
        ]);

        $lote->encerrar(
            Carbon::parse($dados['data_encerramento']),
            rendimentoPct: $dados['rendimento_pct'] ?? null,
            resultadoSalmonella: $dados['resultado_salmonella'] ?? null,
        );

        return redirect()->route('lotes.show', $lote)->with('success', 'Lote encerrado. O aviário está liberado.');
    }

    /** Exclusão lógica (soft delete). */
    public function destroy(Lote $lote): RedirectResponse
    {
        $lote->delete();

        return redirect()->route('lotes.index')->with('success', 'Lote excluído.');
    }
}
