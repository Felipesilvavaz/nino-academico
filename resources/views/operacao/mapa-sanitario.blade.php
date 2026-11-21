<?php /** RF27: Mapa Sanitário, simplificado como contagem de chamados abertos por granja (sem mapa geográfico). */ ?>
@extends('layouts.app')
@section('title', 'Mapa sanitário')
@section('content')
<h1 class="h4 titulo-secao mb-1">Mapa sanitário</h1>
<p class="text-muted">Chamados sanitários abertos, agrupados por granja.</p>
<div class="row g-3">
    @forelse ($granjas as $g)
        @php($abertos = $g->aviarios->flatMap->lotes->flatMap->ocorrenciasSanitarias->where('status', 'aberta')->count())
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <div class="fw-semibold">{{ $g->nome }}</div>
                <div class="text-muted small mb-2">{{ $g->cidade }}/{{ $g->uf }}</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge fs-6 text-bg-{{ $abertos > 0 ? 'danger' : 'success' }}">{{ $abertos }}</span>
                    <span class="text-muted small">chamado(s) aberto(s)</span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-muted">Nenhuma granja cadastrada.</div>
    @endforelse
</div>
@endsection
