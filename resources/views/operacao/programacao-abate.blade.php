<?php /** RF16 / RF22: Programação de Abate. */ ?>
@extends('layouts.app')
@section('title', 'Programação de abate')
@section('content')
@php($fmt = fn ($v, $d = 0) => is_null($v) ? '—' : number_format($v, $d, ',', '.'))
<h1 class="h4 titulo-secao mb-1">Programação de abate</h1>
<p class="text-muted">Lotes abertos ordenados pela data de abate prevista, com o peso projetado a partir do ganho médio diário (GMD) atual.</p>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Lote</th><th>Granja / Aviário</th><th>Abate previsto</th><th class="text-end">Dias restantes</th><th>Frigorífico de destino</th><th class="text-end">Peso atual (g)</th><th class="text-end">Peso projetado (g)</th></tr></thead>
            <tbody>
            @forelse ($lotes as $l)
                <tr>
                    <td><a href="{{ route('lotes.show', $l) }}" class="fw-semibold">{{ $l->codigo }}</a></td>
                    <td>{{ $l->aviario->nomeCompleto() }}</td>
                    <td>{{ $l->data_abate_prevista->format('d/m/Y') }}</td>
                    <td class="text-end">{{ max(0, now()->startOfDay()->diffInDays($l->data_abate_prevista, false)) }}</td>
                    <td>{{ $l->frigorifico_destino ?? '—' }}</td>
                    <td class="text-end">{{ $fmt($l->pesoMedioAtualG()) }}</td>
                    <td class="text-end fw-semibold">{{ $fmt($l->pesoProjetadoAbateG()) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum lote com data de abate prevista.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">RN13: projeção = peso do último registro + GMD atual × dias restantes até o abate previsto.</p>
@endsection
