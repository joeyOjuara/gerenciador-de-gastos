<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'date'         => 'required|date',
            'type'         => 'required|in:income,expense',
            'category_id'  => ['required', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
            'payment_id'   => ['required', Rule::exists('payments', 'id')->where('user_id', $this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required'  => 'A descrição é necessária',
            'description.max'       => 'Descrição muito longa. Máximo 255 caracteres.',
            'amount.required'       => 'O valor é necessário',
            'amount.numeric'        => 'O valor precisa ser um número válido',
            'amount.min'            => 'O valor deve ser maior que zero',
            'date.required'         => 'A data é necessária',
            'type.required'         => 'O tipo é necessário',
            'type.in'               => 'Tipo inválido',
            'category_id.required'  => 'A categoria é necessária',
            'payment_id.required'   => 'A forma de pagamento é necessária',
        ];
    }
}
