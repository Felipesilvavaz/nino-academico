@extends('layouts.app')
@section('title', 'Lote '.$lote->codigo)
@section('content')
@php($fmt = fn ($v, $d = 0) => is_null($v) ? '—' : number_format($v, $d, ',', '.'))
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h4 titulo-secao mb-1">Lote {{ $lote->codigo }} <span class="badge text-bg-{{ $lote->status->cor() }} fs-6">{{ $lote->status->label() }}</span></h1>
        <div class="text-muted">{{ $lote->aviario->nomeCompleto() }} · Produtor: {{ $lote->aviario->granja->produtor->nome }} · Técnico: {{ $lote->tecnico->name }}</div>
        <div class="mt-2 d-flex flex-wrap gap-2">
            @if ($lote->alertaMortalidade())<span class="badge text-bg-danger"><i class="bi bi-exclamation-triangle"></i> Mortalidade acima do esperado (RN11)</span>@endif
            @if ($lote->alertaPeso())<span class="badge text-bg-warning text-dark"><i class="bi bi-arrow-down-short"></i> Peso abaixo da curva de referência (RN10)</span>@endif
            @if ($lote->precisaVisita())<span class="badge text-bg-info text-dark"><i class="bi bi-geo-alt"></i> Sem acompanhamento há {{ $lote->diasSemAcompanhamento() }} dias (RN12)</span>@endif
        </div>
    </div>
    @can('operar')
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
        @if ($lote->estaAberto())
            <button class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#encerrar"><i class="bi bi-flag"></i> Encerrar</button>
        @endif
        <form method="POST" action="{{ route('lotes.destroy', $lote) }}" onsubmit="return confirm('Excluir este lote?')">@csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Excluir</button></form>
    </div>
    @endcan
</div>

@if ($lote->estaAberto())
<div class="collapse mb-3" id="encerrar">
    <form method="POST" action="{{ route('lotes.encerrar', $lote) }}" class="card p-3 d-flex flex-row flex-wrap gap-2 align-items-end">
        @csrf @method('PATCH')
        <div><label class="form-label small mb-1">Data de encerramento (abate)</label><input type="date" name="data_encerramento" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div>
        <button class="btn btn-success btn-sm">Confirmar encerramento</button>
    </form>
</div>
@endif

<div class="row g-3 mb-4">
    @foreach ([
        ['Alojamento', $lote->data_alojamento->format('d/m/Y')],
        ['Idade', $lote->idadeAtual().' dias'],
        ['Aves alojadas', $fmt($lote->qtd_alojada)],
        ['Aves vivas', $fmt($lote->avesVivas())],
        ['Viabilidade', $fmt($lote->viabilidade(), 2).'%'],
        ['Peso médio', $lote->pesoMedioAtualG() ? $fmt($lote->pesoMedioAtualG()).' g' : '—'],
        ['GMD', $lote->gmd() ? $fmt($lote->gmd(), 1).' g/dia' : '—'],
        ['Ração consumida', $fmt($lote->consumoTotalKg()).' kg'],
        ['Conversão alimentar', $fmt($lote->conversaoAlimentar(), 3)],
        ['IEP', $fmt($lote->iep())],
        ['Projeção de peso no abate', $lote->pesoProjetadoAbateG() ? $fmt($lote->pesoProjetadoAbateG()).' g' : '—'],
    ] as [$rotulo, $valor])
        <div class="col-6 col-md-4 col-xl"><div class="card kpi p-3 h-100"><div class="rotulo">{{ $rotulo }}</div><div class="valor fs-4">{{ $valor }}</div></div></div>
    @endforeach
</div>

@can('operar')
@if ($lote->estaAberto())
<div class="card p-3 mb-4 no-print">
    <h2 class="h6 titulo-secao">Registrar acompanhamento diário</h2>
    <form method="POST" action="{{ route('lotes.acompanhamentos.store', $lote) }}">
        @csrf
        @include('acompanhamentos._campos', ['acompanhamento' => new \App\Models\Acompanhamento(['data' => now(), 'mortalidade' => 0, 'eliminados' => 0])])
        <button class="btn btn-primary btn-sm mt-3"><i class="bi bi-plus-lg"></i> Registrar</button>
    </form>
</div>
@endif
@endcan

<h2 class="h6 titulo-secao">Histórico de acompanhamento</h2>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr><th>Data</th><th class="text-end">Idade</th><th class="text-end">Mortalidade</th><th class="text-end">Eliminados</th><th class="text-end">Ração (kg)</th><th class="text-end">Peso médio (g)</th><th class="text-end">Temp. (°C)</th><th>Observação</th><th>Registrado por</th><th></th></tr></thead>
            <tbody>
            @forelse ($lote->acompanhamentos->sortByDesc('data') as $a)
                <tr>
                    <td>{{ $a->data->format('d/m/Y') }}</td>
                    <td class="text-end">{{ $a->idade_dias }}</td>
                    <td class="text-end">{{ $a->mortalidade }}</td>
                    <td class="text-end">{{ $a->eliminados }}</td>
                    <td class="text-end">{{ $fmt($a->consumo_racao_kg, 2) }}</td>
                    <td class="text-end">{{ $fmt($a->peso_medio_g) }}</td>
                    <td class="text-end">{{ $fmt($a->temperatura_c, 1) }}</td>
                    <td>{{ $a->observacao }}</td>
                    <td>{{ $a->usuario->name }}</td>
                    <td class="text-end text-nowrap no-print">
                        @can('operar') @if ($lote->estaAberto())
                        <a href="{{ route('acompanhamentos.edit', $a) }}" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('acompanhamentos.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Excluir este registro?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button></form>
                        @endif @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Nenhum acompanhamento registrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<a href="{{ route('lotes.index') }}" class="btn btn-link mt-2 px-0 no-print"><i class="bi bi-arrow-left"></i> Voltar para lotes</a>
@endsection
