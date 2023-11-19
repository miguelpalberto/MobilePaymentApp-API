<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePiggyBankBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return
            [
                'piggy_bank_balance' => 'required|decimal:2|min:0',
            ];
    }

    public function messages(): array
    {
        return [
            'piggy_bank_balance.required' => 'The piggy bank balance is required',
            'piggy_bank_balance.decimal' => 'The piggy bank balance must be a decimal number with 2 decimal places',
            'piggy_bank_balance.min' => 'The piggy bank balance must be greater than or equal to 0',
        ];
    }
}
