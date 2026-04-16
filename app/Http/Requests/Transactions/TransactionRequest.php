<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'date'         => 'required|date',
            'type'         => 'required|in:income,expense',
            'recurrence'   => 'nullable|in:none,weekly,monthly,yearly',
            'category_id'  => 'required|exists:categories,id',
            'payment_id'   => 'required|exists:payments,id',
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
