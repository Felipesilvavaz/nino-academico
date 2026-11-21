@extends('layouts.app')
@section('title', $aviario->exists ? 'Editar aviário' : 'Novo aviário')
@section('content')
<h1 class="h4 titulo-secao mb-1">{{ $aviario->exists ? 'Editar aviário' : 'Novo aviário' }}</h1>
<p class="text-muted">{{ $granja->nome }}</p>
<div class="card p-4" style="max-width: 760px">
    <form method="POST" action="{{ $aviario->exists ? route('aviarios.update', $aviario) : route('granjas.aviarios.store', $granja) }}">
        @csrf
        @if ($aviario->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Identificação *</label><input class="form-control" name="identificacao" value="{{ old('identificacao', $aviario->identificacao) }}" required></div>
            <div class="col-md-3"><label class="form-label">Área (m²) *</label><input type="number" step="0.01" class="form-control" name="area_m2" value="{{ old('area_m2', $aviario->area_m2) }}" required></div>
            <div class="col-md-3"><label class="form-label">Capacidade (aves) *</label><input type="number" class="form-control" name="capacidade_aves" value="{{ old('capacidade_aves', $aviario->capacidade_aves) }}" required></div>
            <div class="col-md-3"><label class="form-label">Tipo *</label><select class="form-select" name="tipo">@foreach (\App\Models\Aviario::TIPOS as $v => $r)<option value="{{ $v }}" @selected(old('tipo', $aviario->tipo) === $v)>{{ $r }}</option>@endforeach</select></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Salvar</button><a href="{{ route('granjas.show', $granja) }}" class="btn btn-outline-secondary">Cancelar</a></div>
    </form>
</div>
@endsection
