<?php /** RF23 / RF25: Acompanhamento de Lançamentos + comparativo pelet x farelo. */ ?>
@extends('layouts.app')
@section('title', 'Acompanhamento de lançamentos')
@section('content')
@php($fmt = fn ($v, $d = 0) => is_null($v) ? '—' : number_format($v, $d, ',', '.'))
<h1 class="h4 titulo-secao mb-1">Acompanhamento de lançamentos</h1>
<p class="text-muted">Últimos registros de acompanhamento diário lançados em todos os lotes.</p>

@if ($comparativoRacao->isNotEmpty())
<div class="card p-3 mb-3">
    <h2 class="h6 titulo-secao">Pelet x farelo: ganho médio diário (GMD)</h2>
    <div class="d-flex gap-4 flex-wrap">
        @foreach (\App\Models\Acompanhamento::TIPOS_RACAO as $v => $r)
            <div><span class="text-muted small">{{ $r }}</span><div class="fs-4 fw-semibold">{{ isset($comparativoRacao[$v]) ? $fmt($comparativoRacao[$v], 2).' g/dia' : '—' }}</div></div>
        @endforeach
    </div>
    <p class="text-muted small mb-0 mt-2">Comparação do GMD dos lotes, agrupados pelo tipo de ração mais recente informado no acompanhamento (RF23).</p>
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr><th>Data</th><th>Lote</th><th>Granja</th><th class="text-end">Mortalidade</th><th class="text-end">Eliminados</th><th class="text-end">Ração (kg)</th><th>Tipo</th><th class="text-end">Peso (g)</th><th>Registrado por</th></tr></thead>
            <tbody>
            @forelse ($lancamentos as $a)
                <tr>
                    <td>{{ $a->data->format('d/m/Y') }}</td>
                    <td><a href="{{ route('lotes.show', $a->lote) }}">{{ $a->lote->codigo }}</a></td>
                    <td>{{ $a->lote->aviario->granja->nome }}</td>
                    <td class="text-end">{{ $a->mortalidade }}</td>
                    <td class="text-end">{{ $a->eliminados }}</td>
                    <td class="text-end">{{ $fmt($a->consumo_racao_kg, 2) }}</td>
                    <td>{{ $a->tipo_racao ? \App\Models\Acompanhamento::TIPOS_RACAO[$a->tipo_racao] : '—' }}</td>
                    <td class="text-end">{{ $fmt($a->peso_medio_g) }}</td>
                    <td>{{ $a->usuario->name }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Nenhum acompanhamento lançado ainda.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
