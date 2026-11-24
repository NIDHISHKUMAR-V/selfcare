<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingsGoalRequest extends FormRequest
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
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:999999999.99'],
            'current_saved_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'target_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:active,reached,cancelled'],
        ];
    }
}
