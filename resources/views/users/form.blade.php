@extends('layouts.app')
@section('title', $usuario->exists ? 'Editar usuário' : 'Novo usuário')
@section('content')
<h1 class="h4 titulo-secao mb-3">{{ $usuario->exists ? 'Editar usuário' : 'Novo usuário' }}</h1>
<div class="card p-4" style="max-width: 760px">
    <form method="POST" action="{{ $usuario->exists ? route('users.update', $usuario) : route('users.store') }}">
        @csrf
        @if ($usuario->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome *</label><input class="form-control" name="name" value="{{ old('name', $usuario->name) }}" required></div>
            <div class="col-md-6"><label class="form-label">E-mail *</label><input type="email" class="form-control" name="email" value="{{ old('email', $usuario->email) }}" required></div>
            <div class="col-md-6"><label class="form-label">Perfil *</label>
                <select class="form-select" name="perfil">
                    @foreach (\App\Models\User::PERFIS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(old('perfil', $usuario->perfil) === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check"><input type="hidden" name="ativo" value="0"><input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo', $usuario->ativo))><label class="form-check-label" for="ativo">Usuário ativo</label></div>
            </div>
            <div class="col-md-6"><label class="form-label">Senha {{ $usuario->exists ? '(deixe em branco para manter)' : '*' }}</label><input type="password" class="form-control" name="password" autocomplete="new-password"></div>
            <div class="col-md-6"><label class="form-label">Confirmar senha</label><input type="password" class="form-control" name="password_confirmation" autocomplete="new-password"></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Salvar</button><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
    </form>
</div>
@endsection
