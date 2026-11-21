<?php

namespace App\Http\Requests;

use App\Models\Aviario;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('operar');
    }

    public function rules(): array
    {
        $lote = $this->route('lote');

        return [
            'aviario_id' => ['required', 'exists:aviarios,id', function (string $attr, mixed $value, Closure $fail) use ($lote) {
                $aviario = Aviario::find($value);
                // RN01: um aviário só pode ter um lote aberto por vez.
                if ($aviario && $aviario->possuiLoteAberto($lote?->id)) {
                    $fail('Este aviário já possui um lote aberto.');
                }
            }],
            'codigo' => ['required', 'string', 'max:30', Rule::unique('lotes', 'codigo')->ignore($lote?->id)],
            'linhagem' => ['required', 'string', 'max:50'],
            'data_alojamento' => ['required', 'date', 'before_or_equal:today'],
            'qtd_alojada' => ['required', 'integer', 'min:1', function (string $attr, mixed $value, Closure $fail) {
                $aviario = Aviario::find($this->input('aviario_id'));
                if ($aviario && (int) $value > $aviario->capacidade_aves) {
                    $fail("A quantidade alojada ultrapassa a capacidade do aviário ({$aviario->capacidade_aves} aves).");
                }
            }],
            'peso_inicial_g' => ['required', 'numeric', 'min:1', 'max:200'],
            'data_abate_prevista' => ['nullable', 'date', 'after:data_alojamento'],
            // RF22: destino do lote na programação de abate (opcional, pode ser definido depois).
            'frigorifico_destino' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'aviario_id' => 'aviário',
            'codigo' => 'código',
            'data_alojamento' => 'data de alojamento',
            'qtd_alojada' => 'quantidade alojada',
            'peso_inicial_g' => 'peso inicial (g)',
            'data_abate_prevista' => 'data prevista de abate',
            'frigorifico_destino' => 'frigorífico de destino',
        ];
    }
}
