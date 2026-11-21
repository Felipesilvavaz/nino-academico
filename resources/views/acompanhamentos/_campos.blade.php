<div class="row g-2">
    <div class="col-6 col-md-2"><label class="form-label small">Data *</label><input type="date" class="form-control form-control-sm" name="data" value="{{ old('data', $acompanhamento->data?->format('Y-m-d')) }}" required></div>
    <div class="col-6 col-md-1"><label class="form-label small">Mortalidade</label><input type="number" min="0" class="form-control form-control-sm" name="mortalidade" value="{{ old('mortalidade', $acompanhamento->mortalidade) }}"></div>
    <div class="col-6 col-md-1"><label class="form-label small">Eliminados</label><input type="number" min="0" class="form-control form-control-sm" name="eliminados" value="{{ old('eliminados', $acompanhamento->eliminados) }}"></div>
    <div class="col-6 col-md-2"><label class="form-label small">Ração consumida (kg) *</label><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="consumo_racao_kg" value="{{ old('consumo_racao_kg', $acompanhamento->consumo_racao_kg) }}" required></div>
    <div class="col-6 col-md-2"><label class="form-label small">Peso médio (g)</label><input type="number" step="0.01" class="form-control form-control-sm" name="peso_medio_g" value="{{ old('peso_medio_g', $acompanhamento->peso_medio_g) }}"></div>
    <div class="col-6 col-md-1"><label class="form-label small">Temp. (°C)</label><input type="number" step="0.1" class="form-control form-control-sm" name="temperatura_c" value="{{ old('temperatura_c', $acompanhamento->temperatura_c) }}"></div>
    <div class="col-12 col-md-3"><label class="form-label small">Observação</label><input class="form-control form-control-sm" name="observacao" value="{{ old('observacao', $acompanhamento->observacao) }}"></div>
</div>
