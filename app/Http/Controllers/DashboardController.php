<?php

namespace App\Http\Controllers;

use App\Enums\StatusLote;
use App\Models\Aviario;
use App\Models\Granja;
use App\Models\Lote;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $abertos = Lote::with(['aviario.granja', 'acompanhamentos'])
            ->where('status', StatusLote::ABERTO)
            ->orderBy('data_alojamento')
            ->get();

        return view('dashboard', [
            'lotesAbertos' => $abertos,
            'totalGranjas' => Granja::where('ativo', true)->count(),
            'aviariosLivres' => Aviario::livres()->count(),
            'avesAlojadas' => $abertos->sum(fn (Lote $l) => $l->avesVivas()),
        ]);
    }
}
