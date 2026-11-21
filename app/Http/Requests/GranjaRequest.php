<?php

namespace App\Http\Requests;

use App\Models\Aviario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GranjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cadastros');
    }

    protected function prepareForValidation(): void
    {
        // Remove linhas de aviário deixadas em branco no formulário.
        $aviarios = collect($this->input('aviarios', []))
            ->filter(fn ($a) => filled($a['identificacao'] ?? null))
            ->values()
            ->all();

        $this->merge(['aviarios' => $aviarios, 'ativo' => $this->boolean('ativo')]);
    }

    public function rules(): array
    {
        $criando = $this->isMethod('post');

        return [
            'produtor_id' => ['required', 'exists:produtores,id'],
            'nome' => ['required', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:100'],
            'uf' => ['required', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'ativo' => ['boolean'],
            'aviarios' => [$criando ? 'required' : 'nullable', 'array', $criando ? 'min:1' : 'min:0'],
            'aviarios.*.identificacao' => ['required', 'string', 'max:50', 'distinct'],
            'aviarios.*.area_m2' => ['required', 'numeric', 'min:1'],
            'aviarios.*.capacidade_aves' => ['required', 'integer', 'min:1'],
            'aviarios.*.tipo' => ['required', Rule::in(array_keys(Aviario::TIPOS))],
        ];
    }

    public function attributes(): array
    {
        return [
            'produtor_id' => 'produtor',
            'uf' => 'UF',
            'aviarios' => 'aviários',
            'aviarios.*.identificacao' => 'identificação do aviário',
            'aviarios.*.area_m2' => 'área do aviário',
            'aviarios.*.capacidade_aves' => 'capacidade do aviário',
            'aviarios.*.tipo' => 'tipo do aviário',
        ];
    }
}
