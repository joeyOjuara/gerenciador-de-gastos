<?php

namespace App\Http\Requests\CreditCards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('credit_cards', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($this->route('id')),
            ],
            'limit_amount' => ['required', 'numeric', 'min:0.01'],
            'closing_day' => ['required', 'integer', 'min:1', 'max:31'],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
        ];
    }
}
