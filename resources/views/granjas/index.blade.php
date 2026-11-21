@extends('layouts.app')
@section('title', 'Granjas')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 titulo-secao mb-0">Granjas</h1>
    <div class="d-flex gap-2">
        <form method="GET"><input class="form-control form-control-sm" name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome"></form>
        <a href="{{ route('granjas.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nova granja</a>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Granja</th><th>Produtor</th><th>Cidade/UF</th><th class="text-end">Aviários</th><th>Situação</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($granjas as $g)
                <tr>
                    <td><a href="{{ route('granjas.show', $g) }}" class="fw-semibold">{{ $g->nome }}</a></td>
                    <td>{{ $g->produtor->nome }}</td><td>{{ $g->cidade }}/{{ $g->uf }}</td>
                    <td class="text-end">{{ $g->aviarios_count }}</td>
                    <td><span class="badge text-bg-{{ $g->ativo ? 'success' : 'secondary' }}">{{ $g->ativo ? 'Ativa' : 'Inativa' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('granjas.edit', $g) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('granjas.destroy', $g) }}" class="d-inline" onsubmit="return confirm('Excluir esta granja e seus aviários?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma granja cadastrada.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $granjas->links() }}</div>
@endsection
