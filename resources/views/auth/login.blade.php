@extends('layouts.app')
@section('title', 'Entrar')
@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh">
    <div class="col-sm-9 col-md-6 col-lg-4">
        <div class="text-center mb-4">
            <div style="font-size:2.6rem;font-weight:800;letter-spacing:.08em;color:#2D358F">N<span style="color:#ED1C24">I</span>NO</div>
            <div class="text-muted">Núcleo de Inteligência, Nutrição e Operação</div>
        </div>
        <div class="card p-4">
            <h1 class="h5 mb-3">Acessar o sistema</h1>
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">E-mail</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                    <label class="form-check-label" for="remember">Manter conectado</label>
                </div>
                <button class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>
@endsection
