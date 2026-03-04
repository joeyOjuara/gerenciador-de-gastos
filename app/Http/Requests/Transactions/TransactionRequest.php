<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id'
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'A descrição é necessária',
            'description.string' => 'A descrição precisa ser do tipo texto',
            'description.max' => 'Descrição muito grande. Reduza o texto.',
            'amount.required' => 'O valor é necessário',
            'amount.numeric' => 'O valor precisa ser um número válido',
            'data.required' => 'A data é necessária',
            'category_id.required' => 'A categoria é necessária'
        ];
    }
}
