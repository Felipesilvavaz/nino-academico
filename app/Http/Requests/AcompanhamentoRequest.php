<?php

namespace App\Http\Requests;

use App\Models\Acompanhamento;
use App\Models\Lote;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcompanhamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('operar');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mortalidade' => $this->input('mortalidade') ?? 0,
            'eliminados' => $this->input('eliminados') ?? 0,
        ]);
    }

    public function lote(): Lote
    {
        /** @var Acompanhamento|null $registro */
        $registro = $this->route('acompanhamento');

        return $registro?->lote ?? $this->route('lote');
    }

    public function rules(): array
    {
        $lote = $this->lote();
        $registro = $this->route('acompanhamento');

        return [
            'data' => ['required', 'date', 'before_or_equal:today',
                'after_or_equal:'.$lote->data_alojamento->format('Y-m-d'),
                // RN03: um registro por lote em cada data.
                Rule::unique('acompanhamentos')->where('lote_id', $lote->id)->ignore($registro?->id)],
            'mortalidade' => ['required', 'integer', 'min:0'],
            'eliminados' => ['required', 'integer', 'min:0', function (string $attr, mixed $value, Closure $fail) use ($lote, $registro) {
                // RN04: perdas do dia não podem ultrapassar as aves vivas.
                $disponiveis = $lote->avesVivas() + ($registro ? $registro->mortalidade + $registro->eliminados : 0);
                if ((int) $this->input('mortalidade') + (int) $value > $disponiveis) {
                    $fail("Mortalidade + eliminados ultrapassa as aves vivas do lote ({$disponiveis}).");
                }
            }],
            'consumo_racao_kg' => ['required', 'numeric', 'min:0'],
            'peso_medio_g' => ['nullable', 'numeric', 'min:1', 'max:6000'],
            'temperatura_c' => ['nullable', 'numeric', 'between:-10,60'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'consumo_racao_kg' => 'consumo de ração (kg)',
            'peso_medio_g' => 'peso médio (g)',
            'temperatura_c' => 'temperatura (°C)',
            'observacao' => 'observação',
        ];
    }

    public function messages(): array
    {
        return ['data.unique' => 'Já existe um acompanhamento deste lote nesta data.'];
    }
}
