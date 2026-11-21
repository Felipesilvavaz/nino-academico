@extends('layouts.app')
@section('title', 'Produtores')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 titulo-secao mb-0">Produtores integrados</h1>
    <div class="d-flex gap-2">
        <form method="GET"><input class="form-control form-control-sm" name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome"></form>
        <a href="{{ route('produtores.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Novo produtor</a>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Nome</th><th>CPF/CNPJ</th><th>Telefone</th><th>Cidade/UF</th><th class="text-end">Granjas</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($produtores as $p)
                <tr>
                    <td>{{ $p->nome }}</td><td>{{ $p->cpf_cnpj }}</td><td>{{ $p->telefone ?: '—' }}</td><td>{{ $p->cidade }}/{{ $p->uf }}</td>
                    <td class="text-end">{{ $p->granjas_count }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('produtores.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('produtores.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Excluir este produtor?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum produtor cadastrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $produtores->links() }}</div>
@endsection
