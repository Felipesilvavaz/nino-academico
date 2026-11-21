<?php /** RF17 / RF21: Rota do Técnico. */ ?>
@extends('layouts.app')
@section('title', 'Rota do técnico')
@section('content')
<h1 class="h4 titulo-secao mb-1">Rota do técnico</h1>
<p class="text-muted">Lotes abertos ordenados pelo tempo sem acompanhamento — os mais urgentes para visitar aparecem primeiro.</p>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Lote</th><th>Granja / Aviário</th><th>Técnico</th><th class="text-end">Dias sem acompanhamento</th><th>Prioridade</th></tr></thead>
            <tbody>
            @forelse ($lotes as $l)
                <tr>
                    <td><a href="{{ route('lotes.show', $l) }}" class="fw-semibold">{{ $l->codigo }}</a></td>
                    <td>{{ $l->aviario->nomeCompleto() }}</td>
                    <td>{{ $l->tecnico->name }}</td>
                    <td class="text-end">{{ $l->diasSemAcompanhamento() }}</td>
                    <td>
                        @if ($l->precisaVisita())<span class="badge text-bg-danger">Urgente</span>
                        @elseif ($l->diasSemAcompanhamento() >= 3)<span class="badge text-bg-warning text-dark">Atenção</span>
                        @else<span class="badge text-bg-success">Em dia</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum lote aberto no momento.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">RN12: lote aberto sem acompanhamento há mais de 7 dias é sinalizado como urgente.</p>
@endsection
