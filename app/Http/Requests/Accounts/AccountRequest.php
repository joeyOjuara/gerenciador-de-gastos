<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $accountId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($accountId),
            ],
            'initial_balance' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da conta é necessário',
            'name.unique' => 'Você já possui uma conta com este nome',
            'initial_balance.required' => 'O saldo inicial é necessário',
            'initial_balance.numeric' => 'O saldo inicial precisa ser um número válido',
        ];
    }
}
