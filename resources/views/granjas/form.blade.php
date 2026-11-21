@extends('layouts.app')
@section('title', $granja->exists ? 'Editar granja' : 'Nova granja')
@section('content')
<h1 class="h4 titulo-secao mb-3">{{ $granja->exists ? 'Editar granja' : 'Nova granja' }}</h1>
<form method="POST" action="{{ $granja->exists ? route('granjas.update', $granja) : route('granjas.store') }}">
    @csrf
    @if ($granja->exists) @method('PUT') @endif
    <div class="card p-4 mb-3" style="max-width: 980px">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome da granja *</label><input class="form-control" name="nome" value="{{ old('nome', $granja->nome) }}" required></div>
            <div class="col-md-6"><label class="form-label">Produtor integrado *</label>
                <select class="form-select" name="produtor_id" required>
                    <option value="">Selecione…</option>
                    @foreach ($produtores as $p)<option value="{{ $p->id }}" @selected(old('produtor_id', $granja->produtor_id) == $p->id)>{{ $p->nome }}</option>@endforeach
                </select>
                @if ($produtores->isEmpty())<small class="text-danger">Cadastre um produtor antes.</small>@endif
            </div>
            <div class="col-md-6"><label class="form-label">Endereço</label><input class="form-control" name="endereco" value="{{ old('endereco', $granja->endereco) }}"></div>
            <div class="col-md-4"><label class="form-label">Cidade *</label><input class="form-control" name="cidade" value="{{ old('cidade', $granja->cidade) }}" required></div>
            <div class="col-md-2"><label class="form-label">UF *</label><input class="form-control text-uppercase" maxlength="2" name="uf" value="{{ old('uf', $granja->uf) }}" required></div>
            <div class="col-md-3"><label class="form-label">Latitude</label><input class="form-control" name="latitude" value="{{ old('latitude', $granja->latitude) }}"></div>
            <div class="col-md-3"><label class="form-label">Longitude</label><input class="form-control" name="longitude" value="{{ old('longitude', $granja->longitude) }}"></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="ativo" value="0"><input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo', $granja->ativo))><label class="form-check-label" for="ativo">Granja ativa</label></div></div>
        </div>
    </div>

    @unless ($granja->exists)
    <div class="card p-4 mb-3" style="max-width: 980px">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 titulo-secao mb-0">Aviários da granja</h2>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-aviario"><i class="bi bi-plus"></i> Adicionar aviário</button>
        </div>
        <table class="table align-middle mb-0" id="tabela-aviarios">
            <thead><tr><th>Identificação *</th><th>Área (m²) *</th><th>Capacidade (aves) *</th><th>Tipo *</th><th></th></tr></thead>
            <tbody>
            @php($linhas = old('aviarios', [['identificacao' => '01', 'area_m2' => '', 'capacidade_aves' => '', 'tipo' => 'convencional']]))
            @foreach ($linhas as $i => $a)
                <tr>
                    <td><input class="form-control" name="aviarios[{{ $i }}][identificacao]" value="{{ $a['identificacao'] ?? '' }}"></td>
                    <td><input class="form-control" type="number" step="0.01" name="aviarios[{{ $i }}][area_m2]" value="{{ $a['area_m2'] ?? '' }}"></td>
                    <td><input class="form-control" type="number" name="aviarios[{{ $i }}][capacidade_aves]" value="{{ $a['capacidade_aves'] ?? '' }}"></td>
                    <td><select class="form-select" name="aviarios[{{ $i }}][tipo]">@foreach (\App\Models\Aviario::TIPOS as $v => $r)<option value="{{ $v }}" @selected(($a['tipo'] ?? '') === $v)>{{ $r }}</option>@endforeach</select></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remover"><i class="bi bi-x"></i></button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endunless

    <div class="d-flex gap-2"><button class="btn btn-primary">Salvar</button><a href="{{ $granja->exists ? route('granjas.show', $granja) : route('granjas.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
</form>
@endsection

@push('scripts')
<script>
    const corpo = document.querySelector('#tabela-aviarios tbody');
    if (corpo) {
        let indice = corpo.rows.length;
        document.getElementById('add-aviario').addEventListener('click', () => {
            const nova = corpo.rows[0].cloneNode(true);
            nova.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/aviarios\[\d+\]/, `aviarios[${indice}]`);
                if (el.tagName === 'INPUT') el.value = '';
            });
            nova.querySelector('input').value = String(indice + 1).padStart(2, '0');
            corpo.appendChild(nova);
            indice++;
        });
        corpo.addEventListener('click', e => {
            const botao = e.target.closest('.remover');
            if (botao && corpo.rows.length > 1) botao.closest('tr').remove();
        });
    }
</script>
@endpush
