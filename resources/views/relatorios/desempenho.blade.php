@extends('layouts.app')
@section('title', 'Desempenho dos lotes')
@section('content')
@php($fmt = fn ($v, $d = 0) => is_null($v) ? '—' : number_format($v, $d, ',', '.'))
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 titulo-secao mb-0">Relatório de desempenho dos lotes</h1>
    <button class="btn btn-outline-primary btn-sm no-print" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / salvar em PDF</button>
</div>
<form method="GET" class="card p-3 mb-3 no-print">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small">Granja</label>
            <select class="form-select form-select-sm" name="granja_id"><option value="">Todas</option>@foreach ($granjas as $g)<option value="{{ $g->id }}" @selected(($filtros['granja_id'] ?? '') == $g->id)>{{ $g->nome }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small">Status</label>
            <select class="form-select form-select-sm" name="status"><option value="">Todos</option>@foreach (\App\Enums\StatusLote::cases() as $s)<option value="{{ $s->value }}" @selected(($filtros['status'] ?? '') === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small">Alojados de</label><input type="date" class="form-control form-control-sm" name="inicio" value="{{ $filtros['inicio'] ?? '' }}"></div>
        <div class="col-md-2"><label class="form-label small">até</label><input type="date" class="form-control form-control-sm" name="fim" value="{{ $filtros['fim'] ?? '' }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-sm btn-primary w-100">Filtrar</button><a href="{{ route('relatorios.desempenho') }}" class="btn btn-sm btn-outline-secondary">Limpar</a></div>
    </div>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Lote</th><th>Granja / Aviário</th><th>Status</th><th class="text-end">Idade (d)</th><th class="text-end">Alojadas</th><th class="text-end">Vivas</th><th class="text-end">Viabilidade</th><th class="text-end">Peso (g)</th><th class="text-end">GMD</th><th class="text-end">CA</th><th class="text-end">IEP</th><th class="text-end">Projeção abate (g)</th><th class="no-print">Alertas</th></tr></thead>
            <tbody>
            @forelse ($linhas as $r)
                <tr>
                    <td><a href="{{ route('lotes.show', $r['lote']) }}">{{ $r['lote']->codigo }}</a></td>
                    <td>{{ $r['lote']->aviario->nomeCompleto() }}</td>
                    <td><span class="badge text-bg-{{ $r['lote']->status->cor() }}">{{ $r['lote']->status->label() }}</span></td>
                    <td class="text-end">{{ $r['idade'] }}</td>
                    <td class="text-end">{{ $fmt($r['lote']->qtd_alojada) }}</td>
                    <td class="text-end">{{ $fmt($r['vivas']) }}</td>
                    <td class="text-end">{{ $fmt($r['viabilidade'], 2) }}%</td>
                    <td class="text-end">{{ $fmt($r['peso']) }}</td>
                    <td class="text-end">{{ $fmt($r['gmd'], 1) }}</td>
                    <td class="text-end">{{ $fmt($r['ca'], 3) }}</td>
                    <td class="text-end fw-semibold">{{ $fmt($r['iep']) }}</td>
                    <td class="text-end">{{ $fmt($r['projecao']) }}</td>
                    <td class="no-print">
                        @if ($r['alertaMortalidade'])<span class="badge text-bg-danger" title="RN11: perda do último registro acima de 0,5% das aves vivas"><i class="bi bi-exclamation-triangle"></i> Mortalidade</span>@endif
                        @if ($r['alertaPeso'])<span class="badge text-bg-warning text-dark" title="RN10: peso abaixo de 85% da curva de referência"><i class="bi bi-arrow-down-short"></i> Peso</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="text-center text-muted py-4">Nenhum resultado para os filtros informados.</td></tr>
            @endforelse
            </tbody>
            @if ($linhas->isNotEmpty())
            <tfoot class="table-light fw-semibold">
                <tr>
                    <td colspan="4">Média dos {{ $linhas->count() }} lote(s)</td>
                    <td class="text-end">{{ $fmt($linhas->avg(fn ($r) => $r['lote']->qtd_alojada)) }}</td>
                    <td class="text-end">{{ $fmt($linhas->avg('vivas')) }}</td>
                    <td class="text-end">{{ $fmt($linhas->avg('viabilidade'), 2) }}%</td>
                    <td class="text-end">{{ $fmt($linhas->whereNotNull('peso')->avg('peso')) }}</td>
                    <td class="text-end">{{ $fmt($linhas->whereNotNull('gmd')->avg('gmd'), 1) }}</td>
                    <td class="text-end">{{ $fmt($linhas->whereNotNull('ca')->avg('ca'), 3) }}</td>
                    <td class="text-end">{{ $fmt($linhas->whereNotNull('iep')->avg('iep')) }}</td>
                    <td class="text-end">{{ $fmt($linhas->whereNotNull('projecao')->avg('projecao')) }}</td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
<p class="text-muted small mt-2">Viabilidade = vivas ÷ alojadas × 100 · CA = ração consumida (kg) ÷ peso vivo total (kg) · IEP = (viabilidade × peso kg) ÷ (idade × CA) × 100 · Projeção = peso do último registro + GMD atual × dias até o abate previsto (RN13)</p>

<h2 class="h6 titulo-secao mt-4">Relatório de peso ao abate</h2>
<p class="text-muted small">Lotes já encerrados, comparando o peso inicial com o peso registrado no encerramento.</p>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Lote</th><th>Granja / Aviário</th><th class="text-end">Idade ao abate</th><th class="text-end">Peso inicial (g)</th><th class="text-end">Peso ao abate (g)</th><th class="text-end">Ganho total (g)</th><th class="text-end">GMD médio</th><th class="text-end">Viabilidade final</th></tr></thead>
            <tbody>
            @forelse ($abatidos as $r)
                <tr>
                    <td><a href="{{ route('lotes.show', $r['lote']) }}">{{ $r['lote']->codigo }}</a></td>
                    <td>{{ $r['lote']->aviario->nomeCompleto() }}</td>
                    <td class="text-end">{{ $r['idadeAbate'] }} d</td>
                    <td class="text-end">{{ $fmt($r['pesoInicial']) }}</td>
                    <td class="text-end fw-semibold">{{ $fmt($r['pesoAbate']) }}</td>
                    <td class="text-end">{{ $fmt($r['ganhoTotal']) }}</td>
                    <td class="text-end">{{ $fmt($r['gmd'], 1) }}</td>
                    <td class="text-end">{{ $fmt($r['viabilidade'], 2) }}%</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Nenhum lote encerrado para os filtros informados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
