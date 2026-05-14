<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $accountRule = Rule::exists('accounts', 'id')->where('user_id', $this->user()->id);

        return [
            'from_account_id' => ['required', $accountRule, 'different:to_account_id'],
            'to_account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $this->user()->id)],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_account_id.required' => 'Selecione a conta de origem',
            'to_account_id.required' => 'Selecione a conta de destino',
            'from_account_id.different' => 'As contas de origem e destino devem ser diferentes',
            'amount.required' => 'Informe o valor da transferência',
            'amount.min' => 'O valor deve ser maior que zero',
        ];
    }
}
