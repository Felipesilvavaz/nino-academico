<?php

namespace App\Http\Requests;

use App\Models\Aviario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AviarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cadastros');
    }

    public function rules(): array
    {
        $aviario = $this->route('aviario');
        $granjaId = $aviario?->granja_id ?? $this->route('granja')?->id;

        return [
            'identificacao' => ['required', 'string', 'max:50',
                Rule::unique('aviarios')->where('granja_id', $granjaId)->ignore($aviario?->id)],
            'area_m2' => ['required', 'numeric', 'min:1'],
            'capacidade_aves' => ['required', 'integer', 'min:1'],
            'tipo' => ['required', Rule::in(array_keys(Aviario::TIPOS))],
        ];
    }

    public function attributes(): array
    {
        return ['identificacao' => 'identificação', 'area_m2' => 'área (m²)', 'capacidade_aves' => 'capacidade de aves'];
    }
}
