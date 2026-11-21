@extends('layouts.app')
@section('title', 'Editar acompanhamento')
@section('content')
<h1 class="h4 titulo-secao mb-1">Editar acompanhamento</h1>
<p class="text-muted">Lote {{ $lote->codigo }} · {{ $lote->aviario->nomeCompleto() }}</p>
<div class="card p-4">
    <form method="POST" action="{{ route('acompanhamentos.update', $acompanhamento) }}">
        @csrf @method('PUT')
        @include('acompanhamentos._campos')
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Salvar</button><a href="{{ route('lotes.show', $lote) }}" class="btn btn-outline-secondary">Cancelar</a></div>
    </form>
</div>
@endsection
