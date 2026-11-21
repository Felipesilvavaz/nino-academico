<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') · NINO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --btz-azul: #2D358F; --btz-vermelho: #ED1C24; --btz-azul-claro: #4451DB; --btz-cinza: #65698F; --btz-lavanda: #EEF0FB; }
        body { background: #F6F7FB; color: #1F2247; }
        .navbar-nino { background: var(--btz-azul); }
        .navbar-nino .nav-link { color: rgba(255,255,255,.85); }
        .navbar-nino .nav-link.active, .navbar-nino .nav-link:hover { color: #fff; }
        .navbar-nino .nav-link.active { border-bottom: 3px solid var(--btz-vermelho); }
        .logo { font-weight: 800; letter-spacing: .08em; color: #fff; font-size: 1.35rem; }
        .logo span { color: var(--btz-vermelho); }
        .btn-primary { background: var(--btz-azul); border-color: var(--btz-azul); }
        .btn-primary:hover { background: var(--btz-azul-claro); border-color: var(--btz-azul-claro); }
        .titulo-secao { display: flex; align-items: center; gap: .6rem; font-weight: 700; color: var(--btz-azul); }
        .titulo-secao::before { content: ''; width: 18px; height: 4px; background: var(--btz-vermelho); border-radius: 2px; }
        .card { border: 1px solid #E3E5F1; box-shadow: 0 1px 2px rgba(45,53,143,.05); }
        .kpi .valor { font-size: 1.8rem; font-weight: 700; color: var(--btz-azul); }
        .kpi .rotulo { color: var(--btz-cinza); font-size: .85rem; }
        .table thead th { background: var(--btz-lavanda); color: var(--btz-azul); font-weight: 600; white-space: nowrap; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .card { box-shadow: none; } }
    </style>
    @stack('head')
</head>
<body>
@auth
<nav class="navbar navbar-expand-lg navbar-dark navbar-nino no-print">
    <div class="container-fluid px-4">
        <a class="navbar-brand logo" href="{{ route('dashboard') }}">N<span>I</span>NO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Painel</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('lotes.*')) active @endif" href="{{ route('lotes.index') }}"><i class="bi bi-egg"></i> Lotes</a></li>
                @can('cadastros')
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('granjas.*', 'aviarios.*')) active @endif" href="{{ route('granjas.index') }}"><i class="bi bi-house"></i> Granjas</a></li>
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('produtores.*')) active @endif" href="{{ route('produtores.index') }}"><i class="bi bi-person-vcard"></i> Produtores</a></li>
                @endcan
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('relatorios.*')) active @endif" href="{{ route('relatorios.desempenho') }}"><i class="bi bi-graph-up"></i> Desempenho</a></li>
                @can('admin')
                <li class="nav-item"><a class="nav-link @if(request()->routeIs('users.*')) active @endif" href="{{ route('users.index') }}"><i class="bi bi-people"></i> Usuários</a></li>
                @endcan
            </ul>
            <div class="d-flex align-items-center gap-3 text-white">
                <div class="text-end lh-sm">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <small class="opacity-75">{{ auth()->user()->perfilLabel() }}</small>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn btn-sm btn-outline-light" title="Sair"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endauth

<main class="container-fluid px-4 py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print"><i class="bi bi-check-circle"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show no-print"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger no-print">
            <strong>Verifique os campos abaixo:</strong>
            <ul class="mb-0">@foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="text-center text-muted small pb-4 no-print">NINO – Módulo Frango de Corte · versão acadêmica · UniFil {{ date('Y') }}</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
