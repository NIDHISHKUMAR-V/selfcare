<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:income,expense'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'income_category_id' => ['required_if:type,income', 'nullable', 'exists:income_categories,id'],
            'expense_category_id' => ['required_if:type,expense', 'nullable', 'exists:expense_categories,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,pdf', 'max:3072'],
        ];
    }
}
