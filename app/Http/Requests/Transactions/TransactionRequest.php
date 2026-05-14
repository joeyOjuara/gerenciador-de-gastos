<?php

namespace App\Http\Requests\Transactions;

use App\Models\Category;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    private const NEW_OPTION_VALUE = '__new__';
    private const NEW_NAME_MAX_LENGTH = 40;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $categoryName = trim((string) $this->category_name);
        $paymentName = trim((string) $this->payment_name);

        if ($this->category_id === self::NEW_OPTION_VALUE && filled($categoryName) && mb_strlen($categoryName) <= self::NEW_NAME_MAX_LENGTH) {
            $category = Category::firstOrCreate(
                [
                    'user_id' => $this->user()->id,
                    'name' => $categoryName,
                ]
            );

            $this->merge(['category_id' => $category->id]);
        }

        if ($this->payment_id === self::NEW_OPTION_VALUE && filled($paymentName) && mb_strlen($paymentName) <= self::NEW_NAME_MAX_LENGTH) {
            $payment = Payment::firstOrCreate(
                [
                    'user_id' => $this->user()->id,
                    'name' => $paymentName,
                ]
            );

            $this->merge(['payment_id' => $payment->id]);
        }
    }

    public function rules(): array
    {
        return [
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'date'         => 'required|date',
            'type'         => 'required|in:income,expense',
            'payment_method' => 'required|in:bank_account,credit_card',
            'category_id'  => ['required', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
            'category_name' => ['nullable', 'string', 'max:40'],
            'payment_id'   => ['nullable', Rule::exists('payments', 'id')->where('user_id', $this->user()->id)],
            'payment_name' => ['nullable', 'string', 'max:40'],
            'account_id'   => ['required_if:payment_method,bank_account', 'nullable', Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)],
            'credit_card_id' => ['required_if:payment_method,credit_card', 'nullable', Rule::exists('credit_cards', 'id')->where('user_id', $this->user()->id)],
            'installments_total' => ['nullable', 'integer', 'min:1', 'max:48'],
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
            'payment_method.required' => 'A forma de lançamento é necessária',
            'category_id.required'  => 'A categoria é necessária',
            'category_name.max'     => 'O nome da categoria deve ter no máximo 40 caracteres',
            'payment_id.required'   => 'A forma de pagamento é necessária',
            'payment_name.max'      => 'O nome da forma de pagamento deve ter no máximo 40 caracteres',
            'account_id.required_if' => 'A conta é necessária para lançamentos em conta bancária',
            'credit_card_id.required_if' => 'O cartão é necessário para lançamentos no crédito',
        ];
    }
}
