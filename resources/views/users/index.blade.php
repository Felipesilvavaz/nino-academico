@extends('layouts.app')
@section('title', 'Usuários')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 titulo-secao mb-0">Usuários</h1>
    <div class="d-flex gap-2">
        <form class="d-flex" method="GET"><input class="form-control form-control-sm" name="busca" value="{{ request('busca') }}" placeholder="Buscar nome ou e-mail"></form>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Novo usuário</a>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            @forelse ($usuarios as $u)
                <tr class="{{ $u->ativo ? '' : 'text-muted' }}">
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->perfilLabel() }}</td>
                    <td><span class="badge text-bg-{{ $u->ativo ? 'success' : 'secondary' }}">{{ $u->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @unless ($u->is(auth()->user()))
                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Confirmar alteração da situação do usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-{{ $u->ativo ? 'danger' : 'success' }}" title="{{ $u->ativo ? 'Inativar' : 'Reativar' }}"><i class="bi bi-{{ $u->ativo ? 'person-x' : 'person-check' }}"></i></button>
                        </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $usuarios->links() }}</div>
@endsection
