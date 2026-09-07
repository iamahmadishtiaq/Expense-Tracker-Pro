<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower(trim((string) $this->type)),
            ]);
        }
    }

    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'type' => ['required', 'string', Rule::in(['income', 'expense', 'transfer'])],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'to_account_id' => [
                'nullable',
                Rule::requiredIf($type === 'transfer'),
                'different:account_id',
                'exists:accounts,id',
            ],
            'category_id' => [
                'nullable',
                Rule::requiredIf($type !== 'transfer'),
                'exists:categories,id',
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}