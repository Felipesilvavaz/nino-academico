@extends('layouts.app')
@section('title', $lote->exists ? 'Editar lote' : 'Alojar lote')
@section('content')
<h1 class="h4 titulo-secao mb-3">{{ $lote->exists ? 'Editar lote '.$lote->codigo : 'Alojar novo lote' }}</h1>
<div class="card p-4" style="max-width: 900px">
    <form method="POST" action="{{ $lote->exists ? route('lotes.update', $lote) : route('lotes.store') }}">
        @csrf
        @if ($lote->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Aviário *</label>
                <select class="form-select" name="aviario_id" required>
                    <option value="">Selecione um aviário livre…</option>
                    @foreach ($aviarios as $a)
                        <option value="{{ $a->id }}" @selected(old('aviario_id', $lote->aviario_id) == $a->id)>{{ $a->nomeCompleto() }} (cap. {{ number_format($a->capacidade_aves, 0, ',', '.') }})</option>
                    @endforeach
                </select>
                @if ($aviarios->isEmpty())<small class="text-danger">Não há aviários livres. Cadastre um aviário ou encerre um lote.</small>@endif
            </div>
            <div class="col-md-4"><label class="form-label">Código do lote *</label><input class="form-control" name="codigo" value="{{ old('codigo', $lote->codigo) }}" placeholder="ex.: FC-2026-001" required></div>
            <div class="col-md-4"><label class="form-label">Linhagem *</label><input class="form-control" name="linhagem" list="linhagens" value="{{ old('linhagem', $lote->linhagem) }}" required>
                <datalist id="linhagens"><option>Cobb 500</option><option>Ross 308</option><option>Hubbard Flex</option></datalist></div>
            <div class="col-md-4"><label class="form-label">Data de alojamento *</label><input type="date" class="form-control" name="data_alojamento" value="{{ old('data_alojamento', $lote->data_alojamento?->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Abate previsto</label><input type="date" class="form-control" name="data_abate_prevista" value="{{ old('data_abate_prevista', $lote->data_abate_prevista?->format('Y-m-d')) }}"></div>
            <div class="col-md-4"><label class="form-label">Quantidade alojada *</label><input type="number" class="form-control" name="qtd_alojada" value="{{ old('qtd_alojada', $lote->qtd_alojada) }}" required></div>
            <div class="col-md-4"><label class="form-label">Peso inicial (g) *</label><input type="number" step="0.01" class="form-control" name="peso_inicial_g" value="{{ old('peso_inicial_g', $lote->peso_inicial_g) }}" required></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Salvar</button><a href="{{ $lote->exists ? route('lotes.show', $lote) : route('lotes.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
    </form>
</div>
@endsection
