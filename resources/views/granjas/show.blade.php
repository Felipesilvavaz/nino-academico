@extends('layouts.app')
@section('title', $granja->nome)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h4 titulo-secao mb-1">{{ $granja->nome }}</h1>
        <div class="text-muted">{{ $granja->produtor->nome }} · {{ $granja->cidade }}/{{ $granja->uf }} @if($granja->endereco) · {{ $granja->endereco }} @endif</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('granjas.edit', $granja) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar granja</a>
        <a href="{{ route('granjas.aviarios.create', $granja) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Novo aviário</a>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Aviário</th><th>Tipo</th><th class="text-end">Área (m²)</th><th class="text-end">Capacidade</th><th>Lote atual</th><th class="text-end">Lotes</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($granja->aviarios as $a)
                @php($aberto = $a->lotes->firstWhere('status', \App\Enums\StatusLote::ABERTO))
                <tr>
                    <td class="fw-semibold">{{ $a->identificacao }}</td>
                    <td>{{ \App\Models\Aviario::TIPOS[$a->tipo] ?? $a->tipo }}</td>
                    <td class="text-end">{{ number_format($a->area_m2, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($a->capacidade_aves, 0, ',', '.') }}</td>
                    <td>@if ($aberto)<a href="{{ route('lotes.show', $aberto) }}">{{ $aberto->codigo }}</a> <span class="badge text-bg-success">Aberto</span>@else<span class="badge text-bg-light">Livre</span>@endif</td>
                    <td class="text-end">{{ $a->lotes->count() }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('aviarios.edit', $a) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('aviarios.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Excluir este aviário?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum aviário cadastrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<a href="{{ route('granjas.index') }}" class="btn btn-link mt-2 px-0"><i class="bi bi-arrow-left"></i> Voltar para granjas</a>
@endsection
