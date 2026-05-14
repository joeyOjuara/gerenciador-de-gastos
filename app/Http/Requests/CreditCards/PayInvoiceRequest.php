<?php

namespace App\Http\Requests\CreditCards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)],
            'payment_date' => ['nullable', 'date'],
        ];
    }
}
