<?php

namespace App\Http\Requests;

use App\Models\OcorrenciaSanitaria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** RF19: abertura de chamado sanitário. */
class OcorrenciaSanitariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('operar');
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(OcorrenciaSanitaria::TIPOS))],
            'descricao' => ['required', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return ['descricao' => 'descrição'];
    }
}
