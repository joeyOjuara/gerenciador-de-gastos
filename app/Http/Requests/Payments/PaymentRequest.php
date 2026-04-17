<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{

    private const TAMANHO_MAX = 40;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:' . self::TAMANHO_MAX,
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'É necessário informar o nome da forma de pagamento.',
            'name.string' => 'O tipo informado não é uma string.',
            'name.max' => 'O nome passou o limite máximo permitido de ' . self::TAMANHO_MAX . '. Por favor, reduza o nome informado.'
        ];
    }
}
