@extends('layouts.app')
@section('title', 'Painel')
@section('content')
<h1 class="h4 mb-1">Olá, {{ strtok(auth()->user()->name, ' ') }}</h1>
<p class="text-muted">Resumo do Módulo Frango de Corte</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card kpi p-3"><div class="rotulo">Lotes abertos</div><div class="valor">{{ $lotesAbertos->count() }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card kpi p-3"><div class="rotulo">Aves alojadas (vivas)</div><div class="valor">{{ number_format($avesAlojadas, 0, ',', '.') }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card kpi p-3"><div class="rotulo">Granjas ativas</div><div class="valor">{{ $totalGranjas }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card kpi p-3"><div class="rotulo">Aviários livres</div><div class="valor">{{ $aviariosLivres }}</div></div></div>
</div>

@if ($lotesComAlerta > 0 || $lotesPendentesVisita > 0)
<div class="d-flex flex-wrap gap-2 mb-4">
    @if ($lotesComAlerta > 0)<span class="badge text-bg-danger fs-6"><i class="bi bi-exclamation-triangle"></i> {{ $lotesComAlerta }} lote(s) com alerta de mortalidade ou peso</span>@endif
    @if ($lotesPendentesVisita > 0)<span class="badge text-bg-info text-dark fs-6"><i class="bi bi-geo-alt"></i> {{ $lotesPendentesVisita }} lote(s) sem visita há mais de 7 dias</span>@endif
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h6 titulo-secao mb-0">Lotes em andamento</h2>
    @can('operar')<a href="{{ route('lotes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Alojar lote</a>@endcan
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Lote</th><th>Granja / Aviário</th><th class="text-end">Idade</th><th class="text-end">Aves vivas</th><th class="text-end">Viabilidade</th><th class="text-end">Peso médio</th><th class="text-end">CA</th></tr></thead>
            <tbody>
            @forelse ($lotesAbertos as $lote)
                <tr>
                    <td><a href="{{ route('lotes.show', $lote) }}" class="fw-semibold">{{ $lote->codigo }}</a></td>
                    <td>{{ $lote->aviario->nomeCompleto() }}</td>
                    <td class="text-end">{{ $lote->idadeAtual() }} dias</td>
                    <td class="text-end">{{ number_format($lote->avesVivas(), 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($lote->viabilidade(), 2, ',', '.') }}%</td>
                    <td class="text-end">{{ $lote->pesoMedioAtualG() ? number_format($lote->pesoMedioAtualG(), 0, ',', '.').' g' : '—' }}</td>
                    <td class="text-end">{{ $lote->conversaoAlimentar() ? number_format($lote->conversaoAlimentar(), 3, ',', '.') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum lote aberto no momento.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
