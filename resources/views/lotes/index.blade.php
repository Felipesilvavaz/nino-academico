@extends('layouts.app')
@section('title', 'Lotes')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 titulo-secao mb-0">Lotes de frango de corte</h1>
    @can('operar')<a href="{{ route('lotes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Alojar lote</a>@endcan
</div>
<form method="GET" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small">Código</label><input class="form-control form-control-sm" name="busca" value="{{ request('busca') }}"></div>
        <div class="col-md-4"><label class="form-label small">Granja</label>
            <select class="form-select form-select-sm" name="granja_id"><option value="">Todas</option>@foreach ($granjas as $g)<option value="{{ $g->id }}" @selected(request('granja_id') == $g->id)>{{ $g->nome }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small">Status</label>
            <select class="form-select form-select-sm" name="status"><option value="">Todos</option>@foreach (\App\Enums\StatusLote::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-sm btn-primary w-100">Filtrar</button><a href="{{ route('lotes.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a></div>
    </div>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Código</th><th>Granja / Aviário</th><th>Linhagem</th><th>Alojamento</th><th class="text-end">Alojadas</th><th class="text-end">Idade</th><th class="text-end">Viabilidade</th><th>Status</th><th>Técnico</th></tr></thead>
            <tbody>
            @forelse ($lotes as $l)
                <tr>
                    <td><a href="{{ route('lotes.show', $l) }}" class="fw-semibold">{{ $l->codigo }}</a></td>
                    <td>{{ $l->aviario->nomeCompleto() }}</td>
                    <td>{{ $l->linhagem }}</td>
                    <td>{{ $l->data_alojamento->format('d/m/Y') }}</td>
                    <td class="text-end">{{ number_format($l->qtd_alojada, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $l->idadeAtual() }} d</td>
                    <td class="text-end">{{ number_format($l->viabilidade(), 2, ',', '.') }}%</td>
                    <td><span class="badge text-bg-{{ $l->status->cor() }}">{{ $l->status->label() }}</span></td>
                    <td>{{ $l->tecnico->name }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Nenhum lote encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $lotes->links() }}</div>
@endsection
