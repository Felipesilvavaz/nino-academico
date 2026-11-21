@extends('layouts.app')
@section('title', $produtor->exists ? 'Editar produtor' : 'Novo produtor')
@section('content')
<h1 class="h4 titulo-secao mb-3">{{ $produtor->exists ? 'Editar produtor' : 'Novo produtor' }}</h1>
<div class="card p-4" style="max-width: 760px">
    <form method="POST" action="{{ $produtor->exists ? route('produtores.update', $produtor) : route('produtores.store') }}">
        @csrf
        @if ($produtor->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nome *</label><input class="form-control" name="nome" value="{{ old('nome', $produtor->nome) }}" required></div>
            <div class="col-md-4"><label class="form-label">CPF/CNPJ *</label><input class="form-control" name="cpf_cnpj" value="{{ old('cpf_cnpj', $produtor->cpf_cnpj) }}" required></div>
            <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control" name="telefone" value="{{ old('telefone', $produtor->telefone) }}"></div>
            <div class="col-md-6"><label class="form-label">Cidade *</label><input class="form-control" name="cidade" value="{{ old('cidade', $produtor->cidade) }}" required></div>
            <div class="col-md-2"><label class="form-label">UF *</label><input class="form-control text-uppercase" name="uf" maxlength="2" value="{{ old('uf', $produtor->uf) }}" required></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Salvar</button><a href="{{ route('produtores.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
    </form>
</div>
@endsection
