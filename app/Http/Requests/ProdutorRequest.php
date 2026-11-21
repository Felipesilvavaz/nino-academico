<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cadastros');
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => ['required', 'string', 'max:18', Rule::unique('produtores', 'cpf_cnpj')->ignore($this->route('produtor')?->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'cidade' => ['required', 'string', 'max:100'],
            'uf' => ['required', 'string', 'size:2'],
        ];
    }

    public function attributes(): array
    {
        return ['cpf_cnpj' => 'CPF/CNPJ', 'uf' => 'UF'];
    }
}
