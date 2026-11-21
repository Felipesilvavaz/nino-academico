<?php /** RF26: visão geral dos aviários (inspirado no "Mapa de Aviários" do módulo real, sem mapa geográfico). */ ?>
@extends('layouts.app')
@section('title', 'Visão geral dos aviários')
@section('content')
@php($fmt = fn ($v, $d = 0) => is_null($v) ? '—' : number_format($v, $d, ',', '.'))
<h1 class="h4 titulo-secao mb-1">Visão geral dos aviários</h1>
<p class="text-muted">Status e indicadores de todos os aviários cadastrados, com o lote atual quando houver.</p>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Granja</th><th>Aviário</th><th>Tipo</th><th class="text-end">Capacidade</th><th>Status</th><th>Lote atual</th><th class="text-end">Viabilidade</th><th class="text-end">Idade</th></tr></thead>
            <tbody>
            @forelse ($aviarios as $a)
                @php($lote = $a->lotes->first())
                <tr>
                    <td>{{ $a->granja->nome }}</td>
                    <td>{{ $a->identificacao }}</td>
                    <td>{{ \App\Models\Aviario::TIPOS[$a->tipo] }}</td>
                    <td class="text-end">{{ $fmt($a->capacidade_aves) }}</td>
                    <td>@if ($lote)<span class="badge text-bg-warning text-dark">Ocupado</span>@else<span class="badge text-bg-success">Livre</span>@endif</td>
                    <td>@if ($lote)<a href="{{ route('lotes.show', $lote) }}">{{ $lote->codigo }}</a>@else—@endif</td>
                    <td class="text-end">{{ $lote ? $fmt($lote->viabilidade(), 2).'%' : '—' }}</td>
                    <td class="text-end">{{ $lote ? $lote->idadeAtual().' d' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Nenhum aviário cadastrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
