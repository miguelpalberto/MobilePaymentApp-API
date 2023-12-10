<?php

namespace App\Http\Requests;

use App\Rules\PaymentReferenceValidationRule;
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
            'vcard' => 'required|string|exists:vcards,phone_number,deleted_at,NULL',
            'confirmation_code' => 'required|string',
            'value' => 'required|decimal:0,2|min:0.01',
            'pair_vcard' => 'required|regex:/^9\d{8}$/|string|different:vcard|exists:vcards,phone_number,deleted_at,NULL',
            'autoSave' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'vcard.required' => 'The vcard field is required',
            'vcard.string' => 'The vcard field must be a string',
            'vcard.exists' => 'The vcard does not exist',
            'confirmation_code.required' => 'The confirmation code field is required',
            'confirmation_code.string' => 'The confirmation code field must be a string',
            'value.required' => 'The value field is required',
            'value.decimal' => 'The value field must be a decimal number',
            'value.min' => 'The value field must be greater than 0',
            'pair_vcard.required' => 'The pair vcard field is required',
            'pair_vcard.regex' => 'The pair vcard field must be a valid phone number',
            'pair_vcard.string' => 'The pair vcard field must be a string',
            'pair_vcard.different' => 'The pair vcard field must be different from vcard',
            'pair_vcard.exists' => 'The pair vcard does not exist'
        ];
    }
}
